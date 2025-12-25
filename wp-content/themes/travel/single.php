<?php
get_header();
?>

<section class="section content">
    <div class="wrap">
        <?php while (have_posts()) : the_post(); ?>
            <article class="content-card reveal" style="--delay: 0.05s;">
                <div class="post-meta">
                    <span><?php echo esc_html(get_the_date()); ?></span>
                    <span><?php echo wp_kses_post(get_the_category_list(', ')); ?></span>
                </div>
                <h1 class="page-title"><?php the_title(); ?></h1>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-hero">
                        <?php the_post_thumbnail('large', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
                    </div>
                <?php endif; ?>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
                <div class="post-footer">
                    <?php the_tags('<span class="tag-list">', ', ', '</span>'); ?>
                </div>
            </article>
            <div class="post-nav">
                <?php the_post_navigation(); ?>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php
get_footer();
