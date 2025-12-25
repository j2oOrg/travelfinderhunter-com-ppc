<?php
get_header();

$destinations = [
    [
        'name' => 'Lisbon',
        'tag' => 'City and coast',
        'desc' => 'Pastel streets, atlantic breezes, and late dinners by the river.',
        'price' => 'From $130/night',
        'class' => 'sunset',
    ],
    [
        'name' => 'Kyoto',
        'tag' => 'Temples and tea',
        'desc' => 'Quiet alleys, lantern light, and garden views.',
        'price' => 'From $155/night',
        'class' => 'moss',
    ],
    [
        'name' => 'Cape Town',
        'tag' => 'Ocean meets mountain',
        'desc' => 'Cape views, craft markets, and sunrise hikes.',
        'price' => 'From $120/night',
        'class' => 'coast',
    ],
    [
        'name' => 'Reykjavik',
        'tag' => 'Northern lights',
        'desc' => 'Geothermal pools and winter adventures.',
        'price' => 'From $180/night',
        'class' => 'glacier',
    ],
    [
        'name' => 'Buenos Aires',
        'tag' => 'Food and tango',
        'desc' => 'Bold flavors, live music, and wide boulevards.',
        'price' => 'From $110/night',
        'class' => 'terra',
    ],
    [
        'name' => 'Vancouver',
        'tag' => 'City and nature',
        'desc' => 'Rainforest walks and waterfront cafes.',
        'price' => 'From $165/night',
        'class' => 'pine',
    ],
];

$deal_placeholders = [
    [
        'name' => 'Santorini Cliffside Suites',
        'location' => 'Oia, Greece',
        'price' => 'From $210/night',
        'perk' => 'Sunset terrace and breakfast included',
        'class' => 'cliff',
    ],
    [
        'name' => 'Marrakesh Riad Retreat',
        'location' => 'Medina, Morocco',
        'price' => 'From $145/night',
        'perk' => 'Courtyard pool and rooftop lounge',
        'class' => 'spice',
    ],
    [
        'name' => 'Banff Lakeside Lodge',
        'location' => 'Alberta, Canada',
        'price' => 'From $190/night',
        'perk' => 'Lakeside rooms with fireplace',
        'class' => 'alpine',
    ],
];

$steps = [
    [
        'title' => 'Search with intent',
        'desc' => 'Type a city, a vibe, or a landmark and get fast matches.',
    ],
    [
        'title' => 'Compare stays',
        'desc' => 'Filter by neighborhood, amenities, and flexible price ranges.',
    ],
    [
        'title' => 'Book with clarity',
        'desc' => 'See what is included, when to go, and the best deal to grab.',
    ],
];

$deals_category = get_category_by_slug('hotel-deals');
$deals_category_id = $deals_category ? $deals_category->term_id : 0;
$cached_hotels = function_exists('travel_get_cached_hotel_results') ? travel_get_cached_hotel_results(20) : [];
?>

<section class="hero">
    <div class="wrap hero-grid">
        <div class="hero-copy reveal" style="--delay: 0.05s;">
            <p class="eyebrow"><?php esc_html_e('Plan with confidence', 'travel'); ?></p>
            <h1><?php esc_html_e('Find destinations and hotel deals that match your travel style.', 'travel'); ?></h1>
            <p class="hero-lede"><?php esc_html_e('Search by city, mood, or landmark. We surface the best stays, smart filters, and deals worth booking.', 'travel'); ?></p>
            <div class="hero-actions">
                <a class="button" href="#destinations"><?php esc_html_e('Explore destinations', 'travel'); ?></a>
                <a class="button button--ghost" href="#deals"><?php esc_html_e('See hotel deals', 'travel'); ?></a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-value">1,200+</span>
                    <span class="stat-label"><?php esc_html_e('stays reviewed weekly', 'travel'); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-value">86%</span>
                    <span class="stat-label"><?php esc_html_e('travelers find a deal in 10 minutes', 'travel'); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-value">42</span>
                    <span class="stat-label"><?php esc_html_e('destination guides live now', 'travel'); ?></span>
                </div>
            </div>
        </div>
        <div class="hero-panel reveal" style="--delay: 0.15s;">
            <div class="search-card">
                <div class="search-card-head">
                    <h2><?php esc_html_e('Start your search', 'travel'); ?></h2>
                    <p><?php esc_html_e('Filter by dates, ratings, and amenities to find the right stay.', 'travel'); ?></p>
                </div>
                <?php get_search_form(); ?>
            </div>
            <div class="mini-grid">
                <div class="mini-card">
                    <span class="mini-title"><?php esc_html_e('Deal alert', 'travel'); ?></span>
                    <strong><?php esc_html_e('Save 22% in Bali', 'travel'); ?></strong>
                    <span class="mini-caption"><?php esc_html_e('Ends in 36 hours', 'travel'); ?></span>
                </div>
                <div class="mini-card">
                    <span class="mini-title"><?php esc_html_e('Traveler score', 'travel'); ?></span>
                    <strong>4.8/5</strong>
                    <span class="mini-caption"><?php esc_html_e('From 12k reviews', 'travel'); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($cached_hotels)) : ?>
    <section class="section stays">
        <div class="wrap">
            <div class="section-head reveal" style="--delay: 0.05s;">
                <p class="eyebrow"><?php esc_html_e('Live stays', 'travel'); ?></p>
                <h2><?php esc_html_e('Random picks from recent searches', 'travel'); ?></h2>
                <p><?php esc_html_e('These hotel snapshots refresh as travelers search the site.', 'travel'); ?></p>
            </div>
            <div class="hotel-grid">
                <?php foreach ($cached_hotels as $index => $item) : ?>
                    <?php
                    $delay = sprintf('%.2fs', 0.05 * ($index + 1));
                    $link = isset($item['link']) ? $item['link'] : '';
                    $external = !empty($item['external']);
                    $link_attrs = $external ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $review_count = isset($item['reviews']) ? $item['reviews'] : '';
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
                            <h3>
                                <?php if ($link) : ?>
                                    <a href="<?php echo esc_url($link); ?>"<?php echo $link_attrs; ?>><?php echo esc_html($item['name']); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html($item['name']); ?>
                                <?php endif; ?>
                            </h3>
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
    </section>
<?php endif; ?>

<section id="destinations" class="section destinations">
    <div class="wrap">
        <div class="section-head reveal" style="--delay: 0.05s;">
            <p class="eyebrow"><?php esc_html_e('Popular right now', 'travel'); ?></p>
            <h2><?php esc_html_e('Destinations built for the way you travel', 'travel'); ?></h2>
            <p><?php esc_html_e('Mix iconic sights with local stays. These picks balance value, vibe, and access to the best neighborhoods.', 'travel'); ?></p>
        </div>
        <div class="destination-grid">
            <?php foreach ($destinations as $index => $destination) : ?>
                <article class="destination-card destination-card--<?php echo esc_attr($destination['class']); ?> reveal" style="--delay: <?php echo esc_attr(sprintf('%.2fs', 0.05 * ($index + 1))); ?>;">
                    <div class="card-media" aria-hidden="true"></div>
                    <div class="card-body">
                        <div class="card-top">
                            <span class="tag"><?php echo esc_html($destination['tag']); ?></span>
                            <span class="price"><?php echo esc_html($destination['price']); ?></span>
                        </div>
                        <h3><?php echo esc_html($destination['name']); ?></h3>
                        <p><?php echo esc_html($destination['desc']); ?></p>
                        <a class="text-link" href="<?php echo esc_url(add_query_arg('s', $destination['name'], home_url('/'))); ?>">
                            <?php esc_html_e('Search stays', 'travel'); ?>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="deals" class="section deals">
    <div class="wrap">
        <div class="section-head reveal" style="--delay: 0.05s;">
            <p class="eyebrow"><?php esc_html_e('Hotel deals', 'travel'); ?></p>
            <h2><?php esc_html_e('Grab these limited-time offers', 'travel'); ?></h2>
            <p><?php esc_html_e('Handpicked stays with flexible cancellation and strong neighborhood access.', 'travel'); ?></p>
        </div>
        <div class="deal-grid">
            <?php
            $deal_query = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => 3,
                'category_name' => 'hotel-deals',
            ]);

            if ($deal_query->have_posts()) :
                $deal_index = 0;
                while ($deal_query->have_posts()) :
                    $deal_query->the_post();
                    $deal_index++;
                    $deal_price = get_post_meta(get_the_ID(), 'deal_price', true);
                    $deal_price = $deal_price ? $deal_price : __('Limited-time offer', 'travel');
                    ?>
                    <article class="deal-card reveal" style="--delay: <?php echo esc_attr(sprintf('%.2fs', 0.05 * $deal_index)); ?>;">
                        <div class="deal-media">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
                            <?php else : ?>
                                <div class="deal-media-fallback" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                        <div class="deal-body">
                            <span class="tag"><?php esc_html_e('Hotel deal', 'travel'); ?></span>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?></p>
                            <div class="deal-meta">
                                <span class="price"><?php echo esc_html($deal_price); ?></span>
                                <a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e('View details', 'travel'); ?></a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <?php foreach ($deal_placeholders as $index => $deal) : ?>
                    <article class="deal-card deal-card--<?php echo esc_attr($deal['class']); ?> reveal" style="--delay: <?php echo esc_attr(sprintf('%.2fs', 0.05 * ($index + 1))); ?>;">
                        <div class="deal-media">
                            <div class="deal-media-fallback" aria-hidden="true"></div>
                        </div>
                        <div class="deal-body">
                            <span class="tag"><?php esc_html_e('Hotel deal', 'travel'); ?></span>
                            <h3><?php echo esc_html($deal['name']); ?></h3>
                            <p class="deal-location"><?php echo esc_html($deal['location']); ?></p>
                            <p><?php echo esc_html($deal['perk']); ?></p>
                            <div class="deal-meta">
                                <span class="price"><?php echo esc_html($deal['price']); ?></span>
                                <a class="text-link" href="<?php echo esc_url(add_query_arg('s', $deal['name'], home_url('/'))); ?>">
                                    <?php esc_html_e('Search stays', 'travel'); ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="section-footer">
            <a class="button button--ghost" href="<?php echo esc_url(travel_get_deals_link()); ?>"><?php esc_html_e('Browse all deals', 'travel'); ?></a>
        </div>
    </div>
</section>

<section class="section steps">
    <div class="wrap">
        <div class="section-head reveal" style="--delay: 0.05s;">
            <p class="eyebrow"><?php esc_html_e('How it works', 'travel'); ?></p>
            <h2><?php esc_html_e('Find your next trip in three easy steps', 'travel'); ?></h2>
        </div>
        <div class="step-grid">
            <?php foreach ($steps as $index => $step) : ?>
                <article class="step-card reveal" style="--delay: <?php echo esc_attr(sprintf('%.2fs', 0.05 * ($index + 1))); ?>;">
                    <span class="step-number"><?php echo esc_html($index + 1); ?></span>
                    <h3><?php echo esc_html($step['title']); ?></h3>
                    <p><?php echo esc_html($step['desc']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section journal" id="journal">
    <div class="wrap">
        <div class="section-head reveal" style="--delay: 0.05s;">
            <p class="eyebrow"><?php esc_html_e('Travel journal', 'travel'); ?></p>
            <h2><?php esc_html_e('Fresh guides and city insights', 'travel'); ?></h2>
            <p><?php esc_html_e('Short reads to help you pick the right neighborhood and timing.', 'travel'); ?></p>
        </div>
        <div class="journal-grid">
            <?php
            $journal_args = [
                'post_type' => 'post',
                'posts_per_page' => 3,
            ];

            if ($deals_category_id) {
                $journal_args['category__not_in'] = [$deals_category_id];
            }

            $journal_query = new WP_Query($journal_args);

            if ($journal_query->have_posts()) :
                $journal_index = 0;
                while ($journal_query->have_posts()) :
                    $journal_query->the_post();
                    $journal_index++;
                    ?>
                    <article class="journal-card reveal" style="--delay: <?php echo esc_attr(sprintf('%.2fs', 0.05 * $journal_index)); ?>;">
                        <div class="journal-media">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
                            <?php else : ?>
                                <div class="journal-media-fallback" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                        <div class="journal-body">
                            <span class="tag"><?php esc_html_e('Guide', 'travel'); ?></span>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?></p>
                            <a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e('Read guide', 'travel'); ?></a>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-state">
                    <h3><?php esc_html_e('No journal posts yet', 'travel'); ?></h3>
                    <p><?php esc_html_e('Publish travel guides or blog posts to populate this section.', 'travel'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section cta">
    <div class="wrap cta-grid">
        <div class="cta-copy reveal" style="--delay: 0.05s;">
            <p class="eyebrow"><?php esc_html_e('Ready to go', 'travel'); ?></p>
            <h2><?php esc_html_e('Build a smarter itinerary in minutes', 'travel'); ?></h2>
            <p><?php esc_html_e('Save favorites, compare options, and track prices before you book.', 'travel'); ?></p>
        </div>
        <div class="cta-actions reveal" style="--delay: 0.1s;">
            <a class="button" href="<?php echo esc_url(add_query_arg('s', 'hotel', home_url('/'))); ?>"><?php esc_html_e('Search hotels', 'travel'); ?></a>
            <a class="button button--ghost" href="#destinations"><?php esc_html_e('Browse destinations', 'travel'); ?></a>
        </div>
    </div>
</section>

<?php
get_footer();
