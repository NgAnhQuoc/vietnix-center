<?php
$tagname = 'h3';

$id = get_the_ID();
$link = esc_url(get_the_permalink());
$title = get_the_title();
$excerpt = get_the_excerpt($id);

$categories = get_the_category($id);
$thumbnail = get_the_post_thumbnail_url($id, 'blog_thumbnail') ?: get_template_directory_uri() . "/assets/images/common/default.jpg";
$post_date = get_the_date('d/m/Y');

?>

<div id="post-<?= $id ?>" class="relative flex-1 flex flex-col rounded-md overflow-hidden" style="box-shadow: 0px 1px 8px 2px rgba(0, 0, 0, 0.05);">
  <a href="<?= $link ?>" class="relative">
    <div class="aspect-ratio-1200/630"></div>
    <img src="<?= $thumbnail ?>" class="absolute left-0 top-0 w-full h-full object-cover">
  </a>

  <div class="flex flex-col px-6 py-5 h-full relative">
    <div class="flex f-14 text-orange-500">
      <?php foreach ($categories as $cat) {
        echo '<a class="mr-2 hover:underline" href="' . get_category_link($cat) . '">' . $cat->name . '</a>';
      } ?>
    </div>
    <<?= $tagname ?> class="line-clamp-2">
      <a href="<?= $link ?>" title="<?= $title ?>" class="f-roboto f-18 text-gray-800 font-bold leading-normal">
        <?= $title ?>
      </a>
    </<?= $tagname ?>>

    <div class="flex-1">
      <div class="line-clamp-3 mt-3 f-15 text-secondary">
        <?= esc_html($excerpt); ?>
      </div>
    </div>
  </div>

  <div class="flex py-4 px-6">
    <div class="f-14 font-tertiary">
      <?= $post_date ?>
    </div>
  </div>
</div>