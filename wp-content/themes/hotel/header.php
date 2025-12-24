<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Casino hotel entertainment in the heart of Helsinki, presented in English.">
  <link rel="icon" href="<?php echo esc_url( get_theme_file_uri('/assets/images/brand.svg') ); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
  $brand = get_theme_file_uri('/assets/images/img.png');
  $dining_page = get_page_by_path('dining');
  $dining_link = $dining_page ? get_permalink($dining_page) : home_url('/dining');
?>

<header class="site-header">
  <div class="shell header-bar">
    <a class="brand" href="<?php echo esc_url( home_url('/') ); ?>">
      <img src="<?php echo esc_url( $brand ); ?>" alt="travelfinderhunter logo">
      <span class="sr-only">travelfinderhunter</span>
    </a>

    <button class="nav-toggle" type="button" aria-controls="primary-nav" aria-expanded="false">
      <span></span><span></span><span></span>
      <span class="sr-only"><?php esc_html_e('Toggle navigation', 'hotel'); ?></span>
    </button>

    <nav id="primary-nav" class="primary-nav" aria-label="<?php esc_attr_e('Primary', 'hotel'); ?>">
      <ul>
        <li><a href="<?php echo esc_url( home_url('/#home') ); ?>"><?php esc_html_e('Home', 'hotel'); ?></a></li>
        <li><a href="<?php echo esc_url( home_url('/#casino') ); ?>"><?php esc_html_e('Casino', 'hotel'); ?></a></li>
        <li><a href="<?php echo esc_url( home_url('/#games') ); ?>"><?php esc_html_e('Games', 'hotel'); ?></a></li>
        <li><a href="<?php echo esc_url( $dining_link ); ?>"><?php esc_html_e('Dining', 'hotel'); ?></a></li>
        <li><a href="<?php echo esc_url( home_url('/#events') ); ?>"><?php esc_html_e('Events', 'hotel'); ?></a></li>
        <li><a href="<?php echo esc_url( home_url('/#poker') ); ?>"><?php esc_html_e('Poker', 'hotel'); ?></a></li>
        <li><a href="<?php echo esc_url( home_url('/#visit') ); ?>"><?php esc_html_e('Visit', 'hotel'); ?></a></li>
        <li><a href="<?php echo esc_url( home_url('/#contact') ); ?>"><?php esc_html_e('Contact', 'hotel'); ?></a></li>
      </ul>
      <a class="btn small" href="<?php echo esc_url( home_url('/#visit') ); ?>"><?php esc_html_e('Plan your visit', 'hotel'); ?></a>
    </nav>
  </div>
</header>
