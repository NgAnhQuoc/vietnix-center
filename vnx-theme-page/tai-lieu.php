<?php
/*
 * Template Name: Tài Liệu
 * Template Post Type: page
 */

use HelperCenter\View;

get_header();
?>

<div class="flex flex-col">
  <main class="flex-1">
    <?php View::render('widgets/vnx-theme/tai-lieu/block-1'); ?>

    <?php View::render('/widgets/vnx-theme/tai-lieu/block-2'); ?>

    <?php View::render('/widgets/vnx-theme/tai-lieu/block-3'); ?>
  </main>
</div>

<?php get_footer(); ?>