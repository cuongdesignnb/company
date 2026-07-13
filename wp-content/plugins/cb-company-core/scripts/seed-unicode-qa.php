<?php
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI-only.\n");
    exit(1);
}

require '/var/www/html/wp-load.php';

function cb_qa_page($title, $slug, $language, $content)
{
    $existing = get_page_by_path($slug, OBJECT, 'page');
    $post_id = wp_insert_post([
        'ID' => $existing ? $existing->ID : 0,
        'post_type' => 'page',
        'post_status' => 'draft',
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
    ]);
    update_post_meta($post_id, '_cb_language', $language);
    update_post_meta($post_id, '_cb_translation_group', 'unicode-qa');
    update_post_meta($post_id, '_cb_page_render_mode', 'editor_and_builder');
    return $post_id;
}

$vi = "Công ty sản xuất thiết bị nhà bếp chất lượng cao.\nThiết kế hiện đại, bền vững và dễ tùy chỉnh.\nHỗ trợ nghiên cứu, sản xuất OEM/ODM và xuất khẩu quốc tế.\nĐiện thoại – địa chỉ – báo giá – giới thiệu – sản phẩm.\n“Chất lượng” – Đổi mới – Bền vững";
$zh = "专业厨房电器制造商\n支持 OEM/ODM 定制服务\n严格的质量控制和准时交付\n联系我们获取产品报价\n产品、工厂、案例、新闻、联系我们";

$en_id = cb_qa_page('Unicode QA - Tiếng Việt', 'unicode-qa-vi', 'en', $vi);
$zh_id = cb_qa_page('Unicode QA - 中文', 'unicode-qa-zh', 'zh', $zh);

$source = [[
    'enable' => '1', 'type' => 'company_intro', 'layout_style' => 'split',
    'title' => 'Thiết kế hiện đại, bền vững và dễ tùy chỉnh.',
    'description' => 'Hỗ trợ nghiên cứu, sản xuất OEM/ODM và xuất khẩu quốc tế.',
    'background_color' => '#fff6f6', 'columns_desktop' => '2',
    'items' => [['title' => '“Chất lượng” – Đổi mới – Bền vững', 'description' => $vi, 'image_id' => 0, 'image_url' => '', 'url' => '']],
]];
$target = [[
    'enable' => '1', 'type' => 'company_intro', 'layout_style' => 'centered',
    'title' => '专业厨房电器制造商', 'description' => '支持 OEM/ODM 定制服务',
    'background_color' => '#ffffff',
    'items' => [['title' => '联系我们获取产品报价', 'description' => $zh, 'image_id' => 0, 'image_url' => '', 'url' => '']],
]];

$source = cb_sanitize_page_sections($source);
$synced = cb_sync_page_sections($source, cb_sanitize_page_sections($target), 'layout');
update_post_meta($en_id, '_cb_page_sections', $source);
update_post_meta($zh_id, '_cb_page_sections', $synced);
update_post_meta($en_id, '_cb_translated_post_zh', $zh_id);
update_post_meta($zh_id, '_cb_translated_post_en', $en_id);

$json = wp_json_encode(['sections' => $synced], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
$roundtrip = json_decode($json, true);
update_option('cb_unicode_json_qa', ['json' => $json, 'valid' => is_array($roundtrip) ? '1' : '0'], false);

add_filter('pre_wp_mail', static function ($return, $atts) {
    update_option('cb_unicode_mail_qa', $atts, false);
    return true;
}, 10, 2);
wp_mail('qa@example.com', '“Chất lượng” – 专业厨房电器制造商', '<p>' . esc_html($vi . "\n" . $zh) . '</p>', ['Content-Type: text/html; charset=UTF-8']);

$stream = fopen('php://temp', 'w+');
fwrite($stream, "\xEF\xBB\xBF");
fputcsv($stream, ['Tiếng Việt', $vi]);
fputcsv($stream, ['中文', $zh]);
rewind($stream);
$csv = stream_get_contents($stream);
fclose($stream);
update_option('cb_unicode_csv_qa', ['has_bom' => str_starts_with($csv, "\xEF\xBB\xBF") ? '1' : '0', 'content' => base64_encode($csv)], false);

echo wp_json_encode([
    'en_page' => $en_id,
    'zh_page' => $zh_id,
    'layout_synced' => $synced[0]['layout_style'] === 'split',
    'zh_title_preserved' => $synced[0]['title'] === '专业厨房电器制造商',
    'json_roundtrip' => is_array($roundtrip),
    'csv_bom' => str_starts_with($csv, "\xEF\xBB\xBF"),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), PHP_EOL;
