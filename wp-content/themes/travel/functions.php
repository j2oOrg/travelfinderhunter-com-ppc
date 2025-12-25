<?php
/**
 * Travel theme setup and assets.
 */

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary Menu', 'travel'),
        'footer' => __('Footer Menu', 'travel'),
    ]);
});

add_action('wp_enqueue_scripts', function () {
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'travel-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'travel-site',
        get_theme_file_uri('/assets/css/site.css'),
        ['travel-fonts'],
        $theme_version
    );

    wp_enqueue_script(
        'travel-main',
        get_theme_file_uri('/assets/js/main.js'),
        [],
        $theme_version,
        true
    );
});

add_filter('body_class', function ($classes) {
    $classes[] = 'travel-shell';
    return $classes;
});

function travel_fallback_menu() {
    echo '<ul class="menu">';
    wp_list_pages(['title_li' => '']);
    echo '</ul>';
}

function travel_get_deals_link() {
    $category = get_category_by_slug('hotel-deals');
    if ($category) {
        return get_category_link($category->term_id);
    }

    $posts_page = (int) get_option('page_for_posts');
    if ($posts_page) {
        return get_permalink($posts_page);
    }

    return home_url('/');
}
