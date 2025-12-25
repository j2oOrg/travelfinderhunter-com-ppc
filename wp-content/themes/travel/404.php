<?php
get_header();
?>

<section class="section content">
    <div class="wrap">
        <div class="empty-state reveal" style="--delay: 0.05s;">
            <h1><?php esc_html_e('Page not found', 'travel'); ?></h1>
            <p><?php esc_html_e('The page you are looking for does not exist. Try a new search or head back to the homepage.', 'travel'); ?></p>
            <?php get_search_form(); ?>
            <a class="button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to home', 'travel'); ?></a>
        </div>
    </div>
</section>

<?php
get_footer();
