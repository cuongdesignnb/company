<?php
if (!$post_id) return;
?>
<section <?php echo cb_theme_section_attrs($section, 'content_editor'); ?>>
    <div class="cb-container cb-content"><?php echo apply_filters('the_content', get_post_field('post_content', $post_id)); ?></div>
</section>
