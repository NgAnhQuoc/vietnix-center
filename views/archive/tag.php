<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
$tag = get_queried_object();
$title = sprintf(esc_html__('Tag: %s', 'vietnix'), single_tag_title('', false));
?>

<div class="flex flex-col">
  <?php View::render('components/page.header-3', [
    'title' => $title,
    'desc' => 'Có <b>' . $tag->count . '</b> bài viết.',
    'hideCta' => true,
    'image' => 'assets/images/banner/tin-tuc.webp'
  ]); ?>

  <div class="pt-5">
    <div class="container blog main-post py-12 flex flex-col">
      <?php View::render('widgets/vnx-theme/widget-posts', [
        'pagination_class' => 'center',
        'tag' => $tag->slug,
        'show_pagination' => true
      ]); ?>
    </div>
  </div>
</div>