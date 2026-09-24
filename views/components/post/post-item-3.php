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

<div id="post-<?= $id ?>" class="relative flex-1 flex flex-col-reverse lg:flex-row border-b p-4 lg:p-7">
  <div class="flex-1 flex flex-col lg:pr-7 mt-4 lg:mt-0">
    <div class="flex text-[10px] lg:text-sm text-[#F2994A] font-bold">
      <?php foreach ($categories as $cat) {
        echo '<a class="mr-2 hover:underline" href="' . get_category_link($cat) . '">' . $cat->name . '</a>';
      } ?>
    </div>

    <div>
      <a href="<?= $link ?>" title="<?= $title ?>" class="text-gray-1 font-black text-base lg:text-[22px] leading-normal">
        <?= $title ?>
      </a>
    </div>

    <div>
      <div class="line-clamp-2 text-[11px] lg:text-sm text-gray-2 mt-2">
        <?= esc_html($excerpt); ?>
      </div>
    </div>

    <div class="flex flex-row mt-4 text-sm text-[#757575]">
      <div class="pr-4">
        <i class="fas fa-calendar text-tertiary mr-1"></i>
        <?= $post_date ?>
      </div>

      <?php if (function_exists('pvc_get_post_views')) : ?>
        <div class="border-l pl-4">
          <i class="fas fa-eye text-tertiary mr-1"></i>
          <?php echo pvc_get_post_views($id); ?>
          <?php echo __('Lượt xem', 'vietnix'); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="w-full lg:w-80">
    <a href="<?= $link ?>" class="relative">
      <div class="aspect-ratio-1200/630">
        <img src="<?= $thumbnail ?>" class="absolute left-0 top-0 w-full h-full object-cover">
      </div>
    </a>
  </div>
</div>