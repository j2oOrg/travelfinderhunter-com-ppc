<?php
get_header();
?>

<section class="section search-results">
    <div class="wrap">
        <div class="section-head reveal" style="--delay: 0.05s;">
            <p class="eyebrow"><?php esc_html_e('Search results', 'travel'); ?></p>
            <h1>
                <?php
                printf(
                    esc_html__('Results for "%s"', 'travel'),
                    esc_html(get_search_query())
                );
                ?>
            </h1>
            <?php get_search_form(); ?>
        </div>

        <?php if (have_posts()) : ?>
            <div class="journal-grid">
                <?php
                $index = 0;
                while (have_posts()) :
                    the_post();
                    $index++;
                    ?>
                    <article class="journal-card reveal" style="--delay: <?php echo esc_attr(sprintf('%.2fs', 0.05 * $index)); ?>;">
                        <div class="journal-media">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
                            <?php else : ?>
                                <div class="journal-media-fallback" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                        <div class="journal-body">
                            <span class="tag"><?php esc_html_e('Result', 'travel'); ?></span>
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
                            <a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e('Read more', 'travel'); ?></a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <div class="empty-state">
                <h2><?php esc_html_e('No matches found', 'travel'); ?></h2>
                <p><?php esc_html_e('Try a different destination, hotel name, or travel style.', 'travel'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
