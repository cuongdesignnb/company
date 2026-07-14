<?php
$slides = array_values(array_filter((array) ($section['slides'] ?? []), static fn($slide) => ($slide['enable'] ?? '1') === '1'));
if (!$slides) {
    return;
}
$has_slider = count($slides) > 1;
$hero_style = sprintf(
    '--cb-hero-min-desktop:%s;--cb-hero-min-mobile:%s;--cb-hero-content-width:%s',
    esc_attr(cb_sanitize_css_size($section['min_height_desktop'] ?? '560px', '560px')),
    esc_attr(cb_sanitize_css_size($section['min_height_mobile'] ?? '460px', '460px')),
    esc_attr(cb_sanitize_css_size($section['content_width'] ?? '650px', '650px'))
);
?>
<section <?php echo cb_theme_section_attrs($section, 'hero_slider', 'cb-hero-slider'); ?>
    data-cb-hero
    data-autoplay="<?php echo esc_attr($section['autoplay'] ?? '1'); ?>"
    data-delay="<?php echo esc_attr((string) absint($section['autoplay_delay'] ?? 6000)); ?>"
    data-speed="<?php echo esc_attr((string) absint($section['transition_speed'] ?? 500)); ?>"
    data-pause-hover="<?php echo esc_attr($section['pause_on_hover'] ?? '1'); ?>"
    aria-roledescription="<?php echo esc_attr(cb_theme_t('carousel')); ?>">
    <div class="cb-hero-track" style="<?php echo esc_attr($hero_style); ?>">
        <?php foreach ($slides as $index => $slide) :
            $slide = wp_parse_args((array) $slide, cb_hero_slide_defaults());
            $loading = $index === 0 ? 'eager' : 'lazy';
            $position = in_array($slide['text_position'], ['left', 'center', 'right'], true) ? $slide['text_position'] : 'left';
            $alignment = in_array($slide['text_alignment'], ['left', 'center', 'right'], true) ? $slide['text_alignment'] : 'left';
            $overlay = $slide['overlay_enable'] === '1' ? sprintf('background:%s;opacity:%s', sanitize_hex_color($slide['overlay_color']) ?: '#ffffff', max(0, min(100, absint($slide['overlay_opacity']))) / 100) : '';
        ?>
            <article class="cb-hero-slide<?php echo $index === 0 ? ' is-active' : ''; ?> cb-hero-content-<?php echo esc_attr($position); ?> cb-hero-text-<?php echo esc_attr($alignment); ?>"
                data-cb-hero-slide
                aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>"
                aria-label="<?php echo esc_attr(($index + 1) . ' / ' . count($slides)); ?>">
                <?php if ($slide['image_id'] || $slide['image_url']) : ?>
                    <picture class="cb-hero-media">
                        <?php if ($slide['mobile_image_id']) :
                            $mobile_src = wp_get_attachment_image_url($slide['mobile_image_id'], 'large');
                            $mobile_srcset = wp_get_attachment_image_srcset($slide['mobile_image_id'], 'large');
                        ?>
                            <source media="(max-width: 760px)" srcset="<?php echo esc_attr($mobile_srcset ?: $mobile_src); ?>">
                        <?php elseif ($slide['mobile_image_url']) : ?>
                            <source media="(max-width: 760px)" srcset="<?php echo esc_url($slide['mobile_image_url']); ?>">
                        <?php endif; ?>
                        <?php
                        if ($slide['image_id']) {
                            echo wp_get_attachment_image($slide['image_id'], 'full', false, [
                                'alt' => $slide['image_alt'],
                                'loading' => $loading,
                                'fetchpriority' => $index === 0 ? 'high' : 'auto',
                                'sizes' => '100vw',
                            ]);
                        } elseif ($slide['image_url']) {
                            echo '<img src="' . esc_url($slide['image_url']) . '" alt="' . esc_attr($slide['image_alt']) . '" loading="' . esc_attr($loading) . '" fetchpriority="' . ($index === 0 ? 'high' : 'auto') . '">';
                        }
                        ?>
                    </picture>
                <?php endif; ?>
                <?php if ($overlay) : ?><span class="cb-hero-overlay" style="<?php echo esc_attr($overlay); ?>" aria-hidden="true"></span><?php endif; ?>
                <div class="cb-container cb-hero-inner">
                    <div class="cb-hero-copy">
                        <?php if ($slide['eyebrow']) : ?><p class="cb-eyebrow"><?php echo esc_html($slide['eyebrow']); ?></p><?php endif; ?>
                        <?php if ($slide['title']) : ?><h1><?php echo esc_html($slide['title']); ?><?php if ($slide['highlight_text']) : ?><span><?php echo esc_html($slide['highlight_text']); ?></span><?php endif; ?></h1><?php endif; ?>
                        <?php if ($slide['description']) : ?><p><?php echo esc_html($slide['description']); ?></p><?php endif; ?>
                        <?php if (($slide['primary_button_text'] && $slide['primary_button_url']) || ($slide['secondary_button_text'] && $slide['secondary_button_url'])) : ?>
                            <div class="cb-hero-actions">
                                <?php if ($slide['primary_button_text'] && $slide['primary_button_url']) : ?><a class="<?php echo esc_attr(cb_theme_button_classes($slide['primary_button_style'])); ?>" href="<?php echo esc_url($slide['primary_button_url']); ?>"><?php echo esc_html($slide['primary_button_text']); ?></a><?php endif; ?>
                                <?php if ($slide['secondary_button_text'] && $slide['secondary_button_url']) : ?><a class="<?php echo esc_attr(cb_theme_button_classes($slide['secondary_button_style'])); ?>" href="<?php echo esc_url($slide['secondary_button_url']); ?>"><?php echo esc_html($slide['secondary_button_text']); ?></a><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($slide['trust_badges']) : ?><div class="cb-trust-row">
                            <?php foreach ($slide['trust_badges'] as $badge) : ?><span><?php if (!empty($badge['icon'])) : ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php endif; ?><?php echo esc_html($badge['text'] ?? ''); ?></span><?php endforeach; ?>
                        </div><?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($has_slider && ($section['show_arrows'] ?? '1') === '1') : ?>
        <button class="cb-hero-arrow cb-hero-prev" type="button" data-cb-hero-prev aria-label="<?php echo esc_attr(cb_theme_t('previous_slide')); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <button class="cb-hero-arrow cb-hero-next" type="button" data-cb-hero-next aria-label="<?php echo esc_attr(cb_theme_t('next_slide')); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    <?php endif; ?>
    <?php if ($has_slider && ($section['show_dots'] ?? '1') === '1') : ?><div class="cb-hero-dots">
        <?php foreach ($slides as $index => $slide) : ?><button type="button" class="<?php echo $index === 0 ? 'is-active' : ''; ?>" data-cb-hero-dot="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr(cb_theme_t('go_to_slide') . ' ' . ($index + 1)); ?>"></button><?php endforeach; ?>
    </div><?php endif; ?>
</section>
