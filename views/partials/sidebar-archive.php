<?php
$data = isset($data) ? $data : new stdClass();
$category_name = $data->category_name ? $data->category_name : 'tai-lieu-ky-thuat';

$tai_lieu_pho_bien = get_posts(
  array(
    'posts_per_page'   => 8,
    'post_type' => 'post',
    'category_name' => $category_name
  )
);
?>

<div class="h-full w-84 pl-6 hidden lg:block relative" data-sticky-container>
  <!--  -->
  <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="rounded-md border h-10 relative overflow-hidden border-gray-200">
      <i class="fas fa-search absolute text-gray-400 f-14" style="top: 13px; left: 10px;"></i>
      <input name="s" type="text" class="round w-full h-full outline-none pl-8 pr-4 placeholder-gray-400" placeholder="Tìm kiếm">
    </div>
  </form>

  <!--  -->
  <div class="mt-6 border rounded-md overflow-hidden bg-white border-gray-200">
    <div class="p-4">
      <h3 class="f-20 font-bold">
        Tài liệu phổ biến
      </h3>
    </div>

    <div class="flex flex-col">
      <?php foreach ($tai_lieu_pho_bien as $i => $item) : ?>
        <div class="flex border-t border-dashed p-4 items-start">
          <i class="fas fa-bookmark f-12 text-tertiary mt-2 mr-3"></i>

          <a href="<?php echo get_the_permalink($item->ID); ?>" class="hover:underline">
            <div class="line-clamp-2">
              <?php echo $item->post_title ?>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <!--  -->

  <?php if (vnx_get_option('post_sidebar_banner_image')) : ?>
    <div class="vnx-sticky pt-5" data-margin-top="130">
      <a href="<?php echo vnx_get_option('post_sidebar_banner_url'); ?>" class="mt-6" rel="nofollow">
        <img id="banner-sidebar-posts" src="<?php echo vnx_get_option('post_sidebar_banner_image')['url']; ?>" alt="" width="auto" height="auto">
      </a>
    </div>
  <?php endif; ?>

</div>