<?php
$tagname = 'h4';

$id = get_the_ID();
$link = esc_url(get_the_permalink());
$title = get_the_title();
$excerpt = get_the_excerpt($id);

$categories = get_the_category($post_id);
$thumbnail = get_the_post_thumbnail_url($post_id, 'blog_thumbnail') ?: get_template_directory_uri() . "/assets/images/common/placeholder.jpg";
$post_date = get_the_date(get_option('date_format'));

?>

<div id="post-<?= $id ?>" class="relative flex-1 flex flex-col rounded-md overflow-hidden" style="box-shadow: 0px 1px 8px 2px rgba(0, 0, 0, 0.05);">
  <a href="<?= $link ?>" class="relative">
    <div class="aspect-ratio-1200/630"></div>
    <img src="<?= $thumbnail ?>" class="absolute left-0 top-0 w-full h-full object-cover">
  </a>

  <div class="flex flex-col px-6 py-4 h-full relative">
    <<?= $tagname ?> class="f-18 font-semibold mt-2 line-clamp-2 text-heading" style="max-height: 56px;">
      <a href="<?= $link ?>" title="<?= $title ?>">
        <?= $title ?>
      </a>
    </<?= $tagname ?>>

    <div class="line-clamp-3 mt-3 f-15 flex-1 overflow-hidden text-secondary" style="max-height: 68px;">
      <?= $excerpt ?>
    </div>
  </div>

  <div class="mt-4 flex  border-t border-gray-200 py-4 px-6">
    <div class="flex-1 flex items-center">
      <i class="fas fa-folder mr-2 text-tertiary"></i>

      <div class="flex f-14 text-secondary hover:underline">
        <?php foreach ($categories as $cat) {
          echo '<a class="font-semibold mr-2" href="/category/' . $cat->slug . '">' . $cat->name . '</a>';
        } ?>
      </div>
    </div>

    <div>
      <div class="f-14 text-secondary font-tertiary font-semibold">
        <i class="fas fa-calendar-alt mr-1 text-tertiary"></i>
        <?= $post_date ?>
      </div>
    </div>
  </div>
</div>