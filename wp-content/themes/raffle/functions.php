<?php
/**
 * travelfinderhunter theme setup and assets.
 */

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus([
        'primary' => __('Primary Menu', 'travelfinderhunter'),
    ]);
});

add_action('wp_enqueue_scripts', function () {
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'travelfinderhunter-google-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap',
        [],
        null
    );

    if (is_front_page()) {
        wp_enqueue_style(
            'travelfinderhunter-home-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Bebas+Neue&display=swap',
            [],
            null
        );
    }

    wp_enqueue_style(
        'travelfinderhunter-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
        [],
        '6.5.2'
    );

    wp_enqueue_style(
        'travelfinderhunter-bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3'
    );

    wp_enqueue_style(
        'travelfinderhunter-site',
        get_theme_file_uri('/assets/css/site.css'),
        ['travelfinderhunter-bootstrap'],
        $theme_version
    );

    if (is_front_page()) {
        wp_enqueue_style(
            'travelfinderhunter-home',
            get_theme_file_uri('/assets/css/front-page.css'),
            ['travelfinderhunter-site'],
            $theme_version
        );
    }

    wp_enqueue_script(
        'travelfinderhunter-bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        [],
        '5.3.3',
        true
    );

    wp_enqueue_script(
        'travelfinderhunter-main',
        get_theme_file_uri('/assets/js/main.js'),
        [],
        $theme_version,
        true
    );
});

add_filter('body_class', function ($classes) {
    $classes[] = 'nova-shell-p7q4';
    return $classes;
});
