<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_transfer_register_admin_page()
{
    global $admin_page_hooks;
    if (empty($admin_page_hooks['cb-company'])) {
        add_menu_page(__('CB Company', 'cb-site-transfer'), __('CB Company', 'cb-site-transfer'), 'manage_options', 'cb-company', 'cb_transfer_render_admin_page', 'dashicons-building', 58);
    }
    add_submenu_page('cb-company', __('Nhập / Xuất website', 'cb-site-transfer'), __('Nhập / Xuất website', 'cb-site-transfer'), 'manage_options', 'cb-site-transfer', 'cb_transfer_render_admin_page');
}

function cb_transfer_enqueue_admin_assets($hook)
{
    if (!str_contains((string) $hook, 'cb-site-transfer')) return;
    wp_enqueue_style('cb-site-transfer-admin', CB_TRANSFER_URL . 'assets/admin.css', [], CB_TRANSFER_VERSION);
    wp_enqueue_script('cb-site-transfer-admin', CB_TRANSFER_URL . 'assets/admin.js', [], CB_TRANSFER_VERSION, true);
    wp_localize_script('cb-site-transfer-admin', 'cbSiteTransfer', [
        'restUrl' => esc_url_raw(rest_url('cb-site-transfer/v1/')),
        'nonce' => wp_create_nonce('wp_rest'),
        'maxPackageSize' => cb_transfer_max_package_size(),
        'i18n' => [
            'working' => __('Đang xử lý...', 'cb-site-transfer'),
            'exporting' => __('Đang tạo package triển khai...', 'cb-site-transfer'),
            'uploading' => __('Đang upload và kiểm tra package...', 'cb-site-transfer'),
            'failed' => __('Thao tác thất bại.', 'cb-site-transfer'),
            'rollbackConfirm' => __('Rollback sẽ khôi phục dữ liệu trước import. Tiếp tục?', 'cb-site-transfer'),
            'rollbackConfirmAgain' => __('Xác nhận lần hai: thực hiện rollback ngay?', 'cb-site-transfer'),
            'downloadPackage' => __('Tải package .cbsite.zip', 'cb-site-transfer'),
            'dryRunDone' => __('Dry run hoàn tất, không có dữ liệu nào bị thay đổi.', 'cb-site-transfer'),
            'source' => __('Nguồn', 'cb-site-transfer'),
            'target' => __('Đích', 'cb-site-transfer'),
            'counts' => __('Số lượng', 'cb-site-transfer'),
            'conflicts' => __('Conflict', 'cb-site-transfer'),
            'importPlan' => __('Kế hoạch nhập', 'cb-site-transfer'),
            'packageVersion' => __('Phiên bản package', 'cb-site-transfer'),
            'exportedAt' => __('Ngày export', 'cb-site-transfer'),
            'packageSize' => __('Dung lượng package', 'cb-site-transfer'),
            'languages' => __('Ngôn ngữ', 'cb-site-transfer'),
            'sourceVersions' => __('Phiên bản nguồn', 'cb-site-transfer'),
            'targetVersions' => __('Phiên bản đích', 'cb-site-transfer'),
            'environment' => __('Môi trường đích', 'cb-site-transfer'),
            'yes' => __('Có', 'cb-site-transfer'),
            'no' => __('Không', 'cb-site-transfer'),
            'noJobs' => __('Chưa có job.', 'cb-site-transfer'),
            'noRollbacks' => __('Chưa có rollback snapshot.', 'cb-site-transfer'),
            'report' => __('Tải report', 'cb-site-transfer'),
            'rollback' => __('Rollback', 'cb-site-transfer'),
            'completed' => __('Hoàn tất', 'cb-site-transfer'),
            'historyHeaders' => [
                __('Ngày', 'cb-site-transfer'),
                __('Người chạy', 'cb-site-transfer'),
                __('Loại', 'cb-site-transfer'),
                __('Package', 'cb-site-transfer'),
                __('Kết quả', 'cb-site-transfer'),
                __('Số lượng entity', 'cb-site-transfer'),
                __('Cảnh báo', 'cb-site-transfer'),
                __('Report', 'cb-site-transfer'),
            ],
            'noWarnings' => __('Không có', 'cb-site-transfer'),
            'stepLabels' => [
                'terms' => __('Đang nhập taxonomy', 'cb-site-transfer'),
                'attachments' => __('Đang nhập media', 'cb-site-transfer'),
                'posts' => __('Đang nhập nội dung', 'cb-site-transfer'),
                'relationships' => __('Đang nối quan hệ dữ liệu', 'cb-site-transfer'),
                'menus' => __('Đang tạo menu', 'cb-site-transfer'),
                'options' => __('Đang nhập cài đặt', 'cb-site-transfer'),
                'finalize' => __('Đang hoàn tất', 'cb-site-transfer'),
                'completed' => __('Hoàn tất', 'cb-site-transfer'),
            ],
        ],
    ]);
}

function cb_transfer_render_admin_page()
{
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap cb-transfer-admin">
        <header class="cb-transfer-header">
            <div><h1><?php esc_html_e('Nhập / Xuất website', 'cb-site-transfer'); ?></h1><p><?php esc_html_e('Triển khai dữ liệu CB Company bằng package JSON có checksum và rollback.', 'cb-site-transfer'); ?></p></div>
            <span class="cb-transfer-version">v<?php echo esc_html(CB_TRANSFER_VERSION); ?></span>
        </header>
        <nav class="cb-transfer-tabs" role="tablist">
            <?php foreach (['export' => __('Export', 'cb-site-transfer'), 'import' => __('Import', 'cb-site-transfer'), 'history' => __('Lịch sử', 'cb-site-transfer'), 'rollback' => __('Rollback', 'cb-site-transfer')] as $key => $label) : ?>
                <button type="button" class="cb-transfer-tab<?php echo $key === 'export' ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr($key); ?>" role="tab"><?php echo esc_html($label); ?></button>
            <?php endforeach; ?>
        </nav>

        <section class="cb-transfer-panel is-active" data-panel="export">
            <div class="cb-transfer-card"><h2><?php esc_html_e('Tạo gói triển khai', 'cb-site-transfer'); ?></h2>
                <div class="cb-transfer-check-grid">
                    <?php
                    $labels = [
                        'pages' => __('Nội dung Page', 'cb-site-transfer'), 'products' => __('Sản phẩm', 'cb-site-transfer'),
                        'news' => __('Tin tức', 'cb-site-transfer'), 'factory' => __('Nhà máy', 'cb-site-transfer'),
                        'cases' => __('Dự án', 'cb-site-transfer'), 'videos' => __('Video', 'cb-site-transfer'),
                        'certificates' => __('Chứng nhận và tài liệu PDF', 'cb-site-transfer'),
                        'media' => __('Media Library', 'cb-site-transfer'), 'menus' => __('Menu', 'cb-site-transfer'),
                        'settings' => __('Cài đặt giao diện', 'cb-site-transfer'), 'seo' => __('SEO', 'cb-site-transfer'),
                        'forms' => __('Biểu mẫu', 'cb-site-transfer'), 'inquiries' => __('Inquiry (dữ liệu cá nhân)', 'cb-site-transfer'),
                        'drafts' => __('Bao gồm draft', 'cb-site-transfer'), 'demo' => __('Bao gồm dữ liệu demo', 'cb-site-transfer'),
                    ];
                    $defaults = cb_transfer_export_defaults();
                    foreach ($labels as $key => $label) : ?>
                        <label><input type="checkbox" name="cb_export_<?php echo esc_attr($key); ?>" value="1" <?php checked($defaults[$key]); ?>> <?php echo esc_html($label); ?></label>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-primary" id="cb-transfer-export"><?php esc_html_e('Tạo gói triển khai', 'cb-site-transfer'); ?></button>
                <div class="cb-transfer-result" id="cb-export-result" aria-live="polite"></div>
            </div>
        </section>

        <section class="cb-transfer-panel" data-panel="import">
            <div class="cb-transfer-card"><h2><?php esc_html_e('Nhập package', 'cb-site-transfer'); ?></h2>
                <div class="cb-transfer-dropzone" id="cb-transfer-dropzone" tabindex="0">
                    <span class="dashicons dashicons-upload" aria-hidden="true"></span>
                    <strong><?php esc_html_e('Kéo thả file .cbsite.zip vào đây', 'cb-site-transfer'); ?></strong>
                    <span><?php esc_html_e('hoặc chọn file từ máy tính', 'cb-site-transfer'); ?></span>
                    <input type="file" id="cb-transfer-package" accept=".zip,.cbsite.zip">
                </div>
                <div class="cb-transfer-import-controls">
                    <label><?php esc_html_e('Chế độ', 'cb-site-transfer'); ?><select id="cb-transfer-mode"><option value="upsert"><?php esc_html_e('Tạo mới và cập nhật', 'cb-site-transfer'); ?></option><option value="create_only"><?php esc_html_e('Chỉ tạo mới', 'cb-site-transfer'); ?></option><option value="overwrite"><?php esc_html_e('Ghi đè dữ liệu CB Company', 'cb-site-transfer'); ?></option></select></label>
                    <button type="button" class="button" id="cb-transfer-dry-run" disabled><?php esc_html_e('Dry run', 'cb-site-transfer'); ?></button>
                    <button type="button" class="button button-primary" id="cb-transfer-start" disabled><?php esc_html_e('Import', 'cb-site-transfer'); ?></button>
                    <button type="button" class="button" id="cb-transfer-pause" disabled><?php esc_html_e('Tạm dừng', 'cb-site-transfer'); ?></button>
                    <button type="button" class="button" id="cb-transfer-resume" disabled><?php esc_html_e('Tiếp tục', 'cb-site-transfer'); ?></button>
                    <button type="button" class="button-link-delete" id="cb-transfer-cancel" disabled><?php esc_html_e('Hủy', 'cb-site-transfer'); ?></button>
                </div>
                <div id="cb-transfer-preflight"></div>
                <div class="cb-transfer-progress" id="cb-transfer-progress" hidden><div><span></span></div><p></p></div>
            </div>
        </section>

        <section class="cb-transfer-panel" data-panel="history"><div class="cb-transfer-card"><h2><?php esc_html_e('Lịch sử job', 'cb-site-transfer'); ?></h2><div id="cb-transfer-history"></div></div></section>
        <section class="cb-transfer-panel" data-panel="rollback"><div class="cb-transfer-card cb-transfer-danger"><h2><?php esc_html_e('Rollback snapshot', 'cb-site-transfer'); ?></h2><p><?php esc_html_e('Chỉ giữ tối đa ba snapshot gần nhất.', 'cb-site-transfer'); ?></p><div id="cb-transfer-rollbacks"></div></div></section>
    </div>
    <?php
}
