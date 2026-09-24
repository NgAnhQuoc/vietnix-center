<?php
/*
 * Template Name: Full Width
 * Template Post Type: page
 */

use HelperCenter\View;

get_header();
?>

<div class="flex flex-col">

  <main>
    <?php while (have_posts()) : the_post(); ?>
      <?php the_content(); ?>
    <?php endwhile; ?>
  </main>

</div>

<?php get_footer(); ?>