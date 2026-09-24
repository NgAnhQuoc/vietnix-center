<?php
$tagname = 'h2';

$id = get_the_ID();
$link = esc_url(get_the_permalink());
$title = get_the_title();
$excerpt = get_the_excerpt($id);

$categories = get_the_category($id);
$thumbnail = get_the_post_thumbnail_url($id, 'blog_thumbnail') ?: "https://vietnix.vn/wp-content/uploads/2022/10/thumb-mac-dinh.png";
$post_date = get_the_date(get_option('date_format'));

?>

<div id="post-<?= $id ?>" class="relative flex-1 flex flex-col rounded-md overflow-hidden" style="box-shadow: 0px 1px 8px 2px rgba(0, 0, 0, 0.05);">
  <a href="<?= $link ?>" class="relative">
    <div class="aspect-ratio-1200/630"></div>
    <img src="<?= $thumbnail ?>" class="absolute left-0 top-0 w-full h-full object-cover">
    <?php if( get_post_meta($id , "expiration_date", true) != null){ ?>
    <div class="vnx_custom_sale" data-time="<?=get_post_meta($id , "expiration_date", true)?>"><span class="has_expiration_date"><?=date_format(date_create(get_post_meta($id , "expiration_date", true)), "d/m/Y")?></span></div> 
    <?php }?>
  </a>
  <?php if( get_post_meta($id , "expiration_date", true) != null){ ?>
  <div class="w-full z-10 vnx_custom_mr_top">
    <div class="vnx_custom_width_90 m-auto  py-2  text-center vnx_custom_count_down">
      </div>
  </div>
  <?php }?>
  <div class="flex flex-col px-6 py-4 h-full relative">
    <<?= $tagname ?> class="mt-2 line-clamp-2">
      <a href="<?= $link ?>" title="<?= $title ?>" class="f-roboto text-md md:text-lg lg:text-xl xl:text-xl text-gray-800 font-medium leading-normal">
        <?= $title ?>
      </a>
    </<?= $tagname ?>>

    <div class="flex-1">
      <div class="line-clamp-3 mt-3 f-15 text-secondary">
        <?= esc_html($excerpt); ?>
      </div>
    </div>
  </div>

  <?php    if( get_post_meta($id , "link_register", true) != null){ ?>
    <div class="mt-4 flex  py-4 px-6 items-center flex-col">
      <div class="w-full text-center mb-2"><a href="<?=esc_url(get_post_meta($id , "link_register", true))?>"><div class="vnx_button_register_post vnx_button_register_post_bg text-white">Đăng ký ngay</div></a></div>
      <div class="w-full text-center"><a href="<?= $link ?>"><div class="vnx_button_register_post vnx_button_register_post_bg_white">Xem chi tiết</div></a></div>
    </div>

<?php }else{?>

  <div class="mt-4 flex  border-t border-gray-200 py-4 px-6">
    <div class="flex-1 flex items-center">
      <i class="fas fa-folder mr-2 text-tertiary"></i>

      <div class="flex f-14">
        <?php foreach ($categories as $cat) {
          echo '<a class="mr-2 hover:underline" href="' . get_category_link($cat) . '">' . $cat->name . '</a>';
        } ?>
      </div>
    </div>

    <div>
      <div class="f-14 font-tertiary">
        <i class="fas fa-calendar-alt mr-1 text-tertiary"></i>
        <?= $post_date ?>
      </div>
    </div>
  </div>
  <?php }?>
</div>