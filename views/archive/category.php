<?php

use HelperCenter\View;

$term = get_queried_object();
$title = single_cat_title('', false);
$desc = category_description($term->term_id);
?>

<div class="flex flex-col">
  <?php View::render('components/page.header-3', [
    'title' => $title,
    'desc' => $desc,
    'image' => 'assets/images/banner/tin-tuc.webp'
  ]); ?>

  <div class="pt-5">
    <div class="container blog main-post py-12 flex flex-col">
      <?php View::render('widgets/vnx-theme/widget-posts', [
        'pagination_class' => 'center',
        'categories' => $term->slug,
        'show_pagination' => true
      ]); ?>
    </div>
  </div>
</div>