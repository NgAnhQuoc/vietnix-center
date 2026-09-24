<?php
$exclude_cat_slug = ['khuyen-mai', 'thong-bao', 'su-kien', 'cap-nhat-san-pham'];
$exclude_cat = [];
foreach ($exclude_cat_slug as $e_cat) {
  if (get_category_by_slug($e_cat)) {
    array_push($exclude_cat, get_category_by_slug($e_cat)->term_id);
  }
}


$args = array(
  'post_type'      => 'post',
  'posts_per_page' => 6,
  'category'       => 0,
  'orderby'          => 'date',
  'order'            => 'DESC',
  'exclude'          => array(),
  // 'category_name' => 'tai-lieu-ky-thuat'
  'category__not_in' => array_values($exclude_cat) ? array_values($exclude_cat) : []
);

$posts = get_posts($args);

?>

<section>
  <div class="container py-8 lg:pb-16 flex flex-col">
    <div class="flex justify-between items-center">
      <h2 class="vnx-block-title f-22 lg:f-32">
        Bài viết mới nhất
      </h2>
    </div>

    <?php
    if ($posts) : ?>
      <div class="mt-8 grid grid-co'exclude'          => array(),ls-1 lg:grid-cols-2 gap-6">
        <?php foreach ($posts as $item) : ?>
          <div class="flex flex-row rounded-md bg-white border border-gray-200 p-4">
            <div class="flex flex-1 flex-col pr-5 overflow-hidden">
              <div class="f-14 flex">
                <?php
                $cat = get_the_category($item->ID)[0];
                echo '<a class="label-category-item relative font-medium mr-4" href="' . get_category_link($cat) . '">' . $cat->name . '</a>';
                ?>
              </div>

              <div class="flex-1 mt-2">
                <a href="<?php echo get_permalink($item->ID); ?>">
                  <h3><?php echo esc_html($item->post_title); ?></h3>
                </a>
              </div>

              <div class="flex items-center mt-5 f-14 text-secondary leading-none">
                <div class="pr-4 border-r">
                  <i class="far fa-calendar mr-1"></i>
                  <?php echo esc_html(get_the_date(get_option('date_format'), $item->ID)); ?>
                </div>

                <?php if (function_exists('pvc_get_post_views')) : ?>
                  <div class="px-4 hidden lg:flex">
                    <i class="fas fa-eye text-tertiary mr-1"></i>
                    <?php echo pvc_get_post_views($item->ID); ?>
                    <?php echo __('Lượt xem', 'vietnix'); ?>
                  </div>
                <?php endif; ?>

              </div>
            </div>

            <?php
            $thumbnail = get_the_post_thumbnail_url($item->ID, 'blog_thumbnail') ? get_the_post_thumbnail_url($item->ID, 'blog_thumbnail') : "https://vietnix.vn/wp-content/uploads/2022/10/thumb-mac-dinh.png";
            ?>
            <div class="flex items-center w-28 lg:w-36">
              <a href="<?= get_permalink($item->ID) ?>" class="w-full">
                <div class="relative">
                  <div class="aspect-ratio-1200/630"></div>
                  <img src="<?= $thumbnail ?>" class="absolute left-0 top-0 w-full h-full object-cover rounded-md border border-gray-200">
                </div>
              </a>
            </div>
          </div>


        <?php endforeach; ?>
      </div>
    <?php
    else :
    ?>
      <div class="center mt-8 p-10 bg-white rounded">
        Chuyên mục chưa có bài viết nào
      </div>
    <?php
    endif;
    ?>
  </div>
</section>

<style>
  .label-category-item {
    color: #F2994A;
  }

  .label-category-item:hover {
    text-decoration: underline;
  }
</style>