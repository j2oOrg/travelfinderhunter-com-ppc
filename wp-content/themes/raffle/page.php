<?php
/**
 * Default page fallback.
 */
get_header();
?>

<section class="notice-slab-n7b6">
  <div class="shell-wrap-z4m2">
    <div class="notice-box-a9r0">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h1 class="section-title-m3y2"><?php the_title(); ?></h1>
        <div class="section-sub-p0c6">
          <?php the_content(); ?>
        </div>
      <?php endwhile; endif; ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>
