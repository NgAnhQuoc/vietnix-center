<?php

use HelperCenter\View;

$category = get_the_category();
$cat = empty($category) ? null : $category[0];
?>

<div class="flex flex-col" style="background-color: #F8F8F8;">
  <div class="bg-gray-100 h-64 center">
    <h1 class="vnx-page-title">
      <?= $category ? $category[0]->name : 'TÀI LIỆU' ?>
    </h1>
  </div>

  <div class="py-16 container flex flex-col lg:flex-row">
    <div class="flex-1 blog main-post flex flex-col pr-10">
      <?php View::render('widgets/widget-posts', [
        'class' => 'grid grid-cols-1',
        'pagination_class' => 'center',
        'categories' => $category ? $category[0]->slug : 'huong-dan,kien-thuc',
        'show_pagination' => true
      ]); ?>
    </div>

    <div class="page-sidebar w-84">
      <div>
        <?php \HelperCenter\View::render('widgets/widget-search-form'); ?>
      </div>

      <div class="mt-6">
        <?php \HelperCenter\View::render('widgets/widget-categories'); ?>
      </div>
    </div>
  </div>
</div>