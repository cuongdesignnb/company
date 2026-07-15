<article class="cb-news-card">
    <a class="cb-news-image" href="<?php the_permalink(); ?>"><?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'large'), get_the_title(), '', 900, 600); ?></a>
    <div>
        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo esc_html(get_the_excerpt()); ?></p>
        <a class="cb-text-link" href="<?php the_permalink(); ?>"><?php echo esc_html(cb_theme_t('read_more')); ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2"/></svg></a>
    </div>
</article>
