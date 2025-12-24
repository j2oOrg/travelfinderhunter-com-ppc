<?php
/**
 * Default page template.
 */
get_header();
?>

<main class="page shell basic-page">
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article <?php post_class(); ?>>
        <h1 class="page-title"><?php the_title(); ?></h1>
        <div class="page-content">
          <?php the_content(); ?>
        </div>
      </article>
    <?php endwhile; ?>
  <?php else : ?>
    <p><?php esc_html_e('Content coming soon.', 'hotel'); ?></p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
