<?php
$tagname = 'h4';

$id = get_the_ID();
$title = get_the_title();
$categories = get_the_category($id);
$thumbnail = get_the_post_thumbnail_url($id, 'blog_thumbnail') ?: get_template_directory_uri() . "/assets/images/common/placeholder.jpg";

$post_date = get_the_date(get_option('date_format'));

$link = get_the_permalink();
?>

<<?= $tagname ?> id="post-<?= $id ?>" class="relative p-5 lg:p-8 flex-1 flex overflow-hidden border-b border-gray-200">
  <div class="flex flex-col pr-5 lg:pr-8 flex-1">
    <a href="<?php echo esc_url($link); ?>" class="font-medium lg:font-black f-18 lg:f-22">
      <?php echo esc_html(get_the_title()); ?>
    </a>

    <div class="flex-1 pt-4 hidden lg:block">
      <div class="line-clamp-2 text-secondary">
        <?php echo esc_html(get_the_excerpt()); ?>
      </div>
    </div>

    <div class="f-14 text-secondary flex pt-3 leading-none items-center">
      <div class="pr-4 flex flex-no-wrap">
        <i class="fas fa-calendar-alt text-tertiary mr-1"></i>
        <?php echo $post_date; ?>
      </div>

      <?php if (function_exists('pvc_get_post_views')) : ?>
        <div class="px-4 flex flex-no-wrap">
          <i class="fas fa-eye text-tertiary mr-1"></i>
          <?php echo pvc_get_post_views(); ?>
          <div class="hidden md:flex ml-1">
            <?php echo __('Lượt xem', 'vietnix'); ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="flex w-32 lg:w-84">
    <a href="<?php echo esc_url($link); ?>" class="w-full">
      <div class="relative w-full">
        <div class="aspect-ratio-1200/630"></div>
        <img src="<?= $thumbnail ?>" class="absolute left-0 top-0 w-full h-full object-cover rounded-md border border-gray-200">
      </div>
    </a>
  </div>
</<?= $tagname ?>>