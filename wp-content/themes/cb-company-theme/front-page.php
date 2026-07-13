<?php get_header(); ?>
<?php
$language = cb_get_current_language();
$special_pages = function_exists('cb_get_group_options')
    ? cb_get_group_options('cb_special_pages', ['en' => [], 'zh' => []])
    : [];
$front_id = absint($special_pages[$language]['home'] ?? 0) ?: get_queried_object_id();
$mode = cb_theme_page_render_mode($front_id);
if (in_array($mode, ['editor', 'editor_and_builder'], true)) {
    $content = get_post_field('post_content', $front_id);
    if (trim((string) $content) !== '') {
        echo '<section class="cb-page-band"><div class="cb-container cb-content">';
        echo apply_filters('the_content', $content);
        echo '</div></section>';
    }
}
if (in_array($mode, ['builder', 'editor_and_builder'], true)) {
    cb_render_page_sections($front_id);
}
?>
<?php get_footer(); ?>
