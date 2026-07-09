<article class="cb-news-card">
    <a href="<?php the_permalink(); ?>"><?php echo cb_theme_image(get_the_post_thumbnail_url(get_the_ID(), 'medium'), get_the_title()); ?></a>
    <div>
        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo esc_html(get_the_excerpt()); ?></p>
        <a href="<?php the_permalink(); ?>"><?php echo esc_html(cb_theme_t('read_more')); ?> →</a>
    </div>
</article>
