<?php
get_header();
$search_query = get_search_query();
$hotel_results = travel_get_hotel_results($search_query);
$has_hotel_results = !is_wp_error($hotel_results) && !empty($hotel_results['items']);
$debug_enabled = current_user_can('manage_options') && isset($_GET['debug']) && $_GET['debug'];
$debug_data = $debug_enabled ? travel_get_hotel_debug() : [];
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

        <?php if ($debug_enabled) : ?>
            <div class="debug-panel">
                <h2><?php esc_html_e('Debug query params', 'travel'); ?></h2>
                <pre class="debug-code"><?php echo esc_html(wp_json_encode($debug_data, JSON_PRETTY_PRINT)); ?></pre>
            </div>
        <?php endif; ?>

        <?php if ($search_query !== '') : ?>
            <?php if ($has_hotel_results) : ?>
                <div class="hotel-results">
                    <div class="results-intro reveal" style="--delay: 0.1s;">
                        <p class="eyebrow"><?php esc_html_e('Hotel results', 'travel'); ?></p>
                        <h2>
                            <?php
                            if (!empty($hotel_results['region']['name'])) {
                                printf(
                                    esc_html__('Stays in %s', 'travel'),
                                    esc_html($hotel_results['region']['name'])
                                );
                            } else {
                                esc_html_e('Top stays for your search', 'travel');
                            }
                            ?>
                        </h2>
                        <p>
                            <?php
                            printf(
                                esc_html__('Check-in %s - Check-out %s', 'travel'),
                                esc_html($hotel_results['checkin']),
                                esc_html($hotel_results['checkout'])
                            );
                            ?>
                        </p>
                    </div>
                    <div class="hotel-grid">
                        <?php
                        $hotel_index = 0;
                        foreach ($hotel_results['items'] as $item) :
                            $hotel_index++;
                            $delay = sprintf('%.2fs', 0.05 * $hotel_index);
                            $link = $item['link'];
                            $link_attrs = $item['external'] ? ' target="_blank" rel="noopener noreferrer"' : '';
                            $review_count = $item['reviews'];
                            if ($review_count !== '' && is_numeric($review_count)) {
                                $review_count = number_format_i18n((int) $review_count);
                            }
                            ?>
                            <article class="hotel-card reveal" style="--delay: <?php echo esc_attr($delay); ?>;">
                                <div class="hotel-media">
                                    <?php if (!empty($item['image'])) : ?>
                                        <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['name']); ?>" loading="lazy">
                                    <?php else : ?>
                                        <div class="hotel-media-fallback" aria-hidden="true"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="hotel-body">
                                    <span class="tag"><?php esc_html_e('Hotel deal', 'travel'); ?></span>
                                    <h2>
                                        <?php if ($link) : ?>
                                            <a href="<?php echo esc_url($link); ?>"<?php echo $link_attrs; ?>><?php echo esc_html($item['name']); ?></a>
                                        <?php else : ?>
                                            <?php echo esc_html($item['name']); ?>
                                        <?php endif; ?>
                                    </h2>
                                    <?php if (!empty($item['location'])) : ?>
                                        <p class="hotel-location"><?php echo esc_html($item['location']); ?></p>
                                    <?php endif; ?>
                                    <div class="hotel-meta">
                                        <?php if (!empty($item['price'])) : ?>
                                            <span class="price"><?php echo esc_html($item['price']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['rating'])) : ?>
                                            <span class="hotel-rating">
                                                <?php
                                                echo esc_html($item['rating']);
                                                if ($review_count !== '') {
                                                    printf(
                                                        ' (%s)',
                                                        esc_html($review_count)
                                                    );
                                                }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($link) : ?>
                                        <a class="text-link" href="<?php echo esc_url($link); ?>"<?php echo $link_attrs; ?>>
                                            <?php esc_html_e('View deal', 'travel'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif (is_wp_error($hotel_results)) : ?>
                <?php if (current_user_can('manage_options')) : ?>
                    <div class="empty-state">
                        <h2><?php esc_html_e('Hotel search is offline', 'travel'); ?></h2>
                        <p><?php esc_html_e('Add your RapidAPI key to enable live hotel results.', 'travel'); ?></p>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="empty-state">
                    <h2><?php esc_html_e('No hotel deals found', 'travel'); ?></h2>
                    <p><?php esc_html_e('Try a broader city search or adjust the dates.', 'travel'); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

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
                <?php if ($has_hotel_results) : ?>
                    <h2><?php esc_html_e('No guides matched this search', 'travel'); ?></h2>
                    <p><?php esc_html_e('Try another destination or browse the latest travel journal posts.', 'travel'); ?></p>
                <?php else : ?>
                    <h2><?php esc_html_e('No matches found', 'travel'); ?></h2>
                    <p><?php esc_html_e('Try a different destination, hotel name, or travel style.', 'travel'); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
