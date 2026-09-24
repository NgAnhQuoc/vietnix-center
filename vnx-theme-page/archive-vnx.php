<?php

use HelperCenter\View;



get_header();

$template = 'category';
if (is_category()) {
  $category = get_queried_object();

  $template = 'category';

  if ($category && $category->slug == 'tai-lieu-ky-thuat') {
    // $template = 'category-tai-lieu';
    $template = 'category';
  } else if ($category && $category->slug == 'digital-marketing') {
    $template = 'category-digital-marketing';
  } else if ($category && $category->slug == 'huong-dan') {
    $template = 'category-huong-dan';
  }
} else if (is_tag()) {
  $template = 'tag';
}
?>

<div class="flex flex-col">
  <main class="flex-1 flex flex-col">
    <?php View::render('archive/' . $template); ?>
  </main>
</div>

<?php get_footer(); ?>