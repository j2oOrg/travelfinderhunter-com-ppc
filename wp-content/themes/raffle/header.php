<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="travelfinderhunter — Licensed raffle with cash prizes, responsible play, and clear rules.">
  <link rel="icon" href="<?php echo esc_url( home_url('/favicon.ico') ); ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( home_url('/apple-touch-icon.png') ); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
  $current = '';
  if (is_front_page()) {
      $current = 'home';
  } elseif (is_page('prizes')) {
      $current = 'prizes';
  } elseif (is_page('rules')) {
      $current = 'rules';
  } elseif (is_page('faq')) {
      $current = 'faq';
  }
  $brand = get_theme_file_uri('/assets/images/img.png');
  $faq_page = get_page_by_path('faq');
  $rules_page = get_page_by_path('rules');
  $prizes_page = get_page_by_path('prizes');
  $faq_link = $faq_page ? get_permalink($faq_page) : home_url('/faq');
  $rules_link = $rules_page ? get_permalink($rules_page) : home_url('/rules');
  $prizes_link = $prizes_page ? get_permalink($prizes_page) : home_url('/prizes');
?>

<nav class="navbar navbar-expand-lg nav-rail-q5h8">
  <div class="shell-wrap-z4m2 d-flex align-items-center justify-content-between">
    <a class="navbar-brand" href="<?php echo esc_url( home_url('/') ); ?>">
      <img class="brand-logo-u8b4" src="<?php echo esc_url( $brand ); ?>" alt="travelfinderhunter logo">
      <span class="sr-only">travelfinderhunter</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'travelfinderhunter'); ?>">
      <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navMenu">
      <ul class="navbar-nav mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item">
          <a class="nav-link-u4e7<?php echo $current === 'home' ? ' active' : ''; ?>" href="<?php echo esc_url( home_url('/#home') ); ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link-u4e7" href="<?php echo esc_url( home_url('/#impact') ); ?>">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link-u4e7" href="<?php echo esc_url( home_url('/#how') ); ?>">How It Works</a>
        </li>
        <li class="nav-item">
          <a class="nav-link-u4e7<?php echo $current === 'prizes' ? ' active' : ''; ?>" href="<?php echo esc_url( $prizes_link ); ?>">Prizes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link-u4e7<?php echo $current === 'rules' ? ' active' : ''; ?>" href="<?php echo esc_url( $rules_link ); ?>">Rules</a>
        </li>
        <li class="nav-item">
          <a class="nav-link-u4e7" href="<?php echo esc_url( home_url('/#winners') ); ?>">Winners</a>
        </li>
        <li class="nav-item">
          <a class="nav-link-u4e7" href="<?php echo esc_url( home_url('/#contact') ); ?>">Contact</a>
        </li>
        <li class="nav-item">
          <a class="nav-link-u4e7<?php echo $current === 'faq' ? ' active' : ''; ?>" href="<?php echo esc_url( $faq_link ); ?>">FAQ</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
