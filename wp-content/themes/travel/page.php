<?php
get_header();
?>

<section class="section content">
    <div class="wrap">
        <?php while (have_posts()) : the_post(); ?>
            <article class="content-card reveal" style="--delay: 0.05s;">
                <h1 class="page-title"><?php the_title(); ?></h1>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</section>

<?php
get_footer();
