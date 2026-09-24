<?php

use HelperCenter\View;

$term = get_queried_object();
$title = single_cat_title('', false);
?>

<div class="flex-1 flex flex-col" style="background-color: #F5F7FA;">
  <!-- Start: Breadcrumbs -->
  <div class="h-10 bg-white flex items-center border-b border-gray-200">
    <div class="container text-secondary flex items-center f-13">
      <i class="fas fa-home mr-2"></i>
      <?php echo esc_html(iwp_breadcrumbs_Center()); ?>
    </div>
  </div>
  <!-- End: Breadcrumbs -->


  <div class="container py-10">
    <h1 class="vnx-page-title f-32" style="color: #333333;">
      <?php echo esc_html($title); ?>
    </h1>
  </div>

  <div class="container flex">
    <div class="flex-1 flex flex-col rounded border border-gray-200 bg-white">
      <?php View::render('partials/loop-posts', [
        'class' => 'flex flex-col',
        'card' => 'card-post-2',
        'pagination_class' => 'center',
        'categories' => $term->slug,
        'show_pagination' => true
      ]); ?>
    </div>

    <?php View::render('partials/sidebar-archive') ?>
  </div>

  <div class="mt-16 pt-12 bg-white">
    <?php View::render('page.support'); ?>
  </div>
</div>