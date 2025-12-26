<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $site_description = get_bloginfo('description');
    if (!$site_description) {
        $site_description = __('Find great travel destinations to book with curated hotel deals and smart search tools.', 'travel');
    }
    ?>
    <meta name="description" content="<?php echo esc_attr($site_description); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'travel'); ?></a>
<div class="site">
    <header class="site-header">
        <div class="wrap header-inner">
            <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
                <span class="brand-mark" aria-hidden="true"></span>
                <?php bloginfo('name'); ?>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
                <?php esc_html_e('Menu', 'travel'); ?>
            </button>
            <div class="header-links">
                <nav class="site-nav" aria-label="<?php esc_attr_e('Primary', 'travel'); ?>">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_id' => 'primary-menu',
                        'menu_class' => 'menu',
                        'container' => false,
                        'fallback_cb' => 'travel_fallback_menu',
                    ]);
                    ?>
                </nav>
                <a class="header-cta" href="<?php echo esc_url(travel_get_deals_link()); ?>">
                    <?php esc_html_e('View deals', 'travel'); ?>
                </a>
            </div>
        </div>
    </header>
    <main id="content" class="site-main">
