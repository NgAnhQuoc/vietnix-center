<?php
$args = array(
  'post_type' => 'post',
  'posts_per_page' => 5,
  'category_name' => 'tai-lieu-huong-dan'
);

$wp_query = new WP_Query($args);

$exclude_cat_slug = ['khuyen-mai', 'thong-bao', 'su-kien', 'cap-nhat-san-pham'];
$exclude_cat = [];
foreach ($exclude_cat_slug as $e_cat) {
  if (get_category_by_slug($e_cat)) {
    array_push($exclude_cat, get_category_by_slug($e_cat)->term_id);
  }
}

$cat = get_category_by_slug('tai-lieu-huong-dan');
$child_categories = get_categories(
  array(
    // 'parent' => $cat->term_id,
    'orderby'     => 'name',
    'order'   => 'ASC',
    'hide_empty'  => false,
    'exclude' => array_values($exclude_cat) ? array_values($exclude_cat) : []
  )
);
?>

<section>
  <div class="container py-8 lg:py-16 flex flex-col">
    <div class="flex justify-between items-center">
      <h2 class="vnx-block-title f-24 lg:f-32 pr-5">
        Danh mục phổ biến
      </h2>
    </div>

    <div class="flex flex-col lg:flex-row pt-6">
      <div class="w-full mt-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <?php
          foreach ($child_categories as $item) :  ?>
            <a href="<?php echo esc_url(get_category_link($item->term_id)); ?>" class="border border-gray-200 rounded-md flex bg-white px-4 py-5 items-center hover:shadow-widget">
              <div class="w-6 h-6 center">
                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDUiIGhlaWdodD0iNDUiIHZpZXdCb3g9IjAgMCA0NSA0NSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTIuODM3MTcgNC44NDc2NkgxMy41OTkzQzE0LjMwNDEgNC44NDc2NiAxNC45NTUyIDUuMjI0MTUgMTUuMzA2OSA1LjgzNDkyTDE3LjA3ODIgOC4yNTQ2NkMxNy40Mjk5IDguODY1NDMgMTguMDgxIDkuMjQxOTIgMTguNzg1NyA5LjI0MTkySDQ0Ljk5OTlWMzYuODY4NEM0NC45OTk5IDM4LjMxOTMgNDMuODIzNiAzOS40OTU2IDQyLjM3MjcgMzkuNDk1NkgyLjYyNzJDMS4xNzYyOCAzOS40OTU2IDAgMzguMzE5MyAwIDM2Ljg2ODRWMTIuMzA2NUMwIDEwLjI3NTggMC4zMTM3NDYgOC4yNTcxNiAwLjkzMDE4NyA2LjMyMjIxQzEuMTU2MDEgNS40NTM4MiAxLjkzOTk0IDQuODQ3NjYgMi44MzcxNyA0Ljg0NzY2WiIgZmlsbD0iI0Y2QzM1OCIvPgo8cGF0aCBkPSJNMzguNjg1IDguNzg5MDZINC4wOTUwM1YyMy40MDA5SDM4LjY4NVY4Ljc4OTA2WiIgZmlsbD0iI0VCRjBGMyIvPgo8cGF0aCBkPSJNNDMuMDI5NiA2LjE2MDE2SDMyLjE4NDRDMzEuNDEzMSA2LjE2MDE2IDMwLjcxMjcgNi42MTAxNiAzMC4zOTIyIDcuMzExNjVMMjguNTM2NCAxMS4zNzMyQzI4LjIxNTggMTIuMDc0NyAyNy41MTU1IDEyLjUyNDcgMjYuNzQ0MiAxMi41MjQ3SDBWMzcuNTI0QzAgMzguOTc0OSAxLjE3NjI4IDQwLjE1MTIgMi42MjcyIDQwLjE1MTJINDIuMzcyOEM0My44MjM3IDQwLjE1MTIgNDUgMzguOTc0OSA0NSAzNy41MjRWOC4xMzA1NkM0NSA3LjA0MjM5IDQ0LjExNzggNi4xNjAxNiA0My4wMjk2IDYuMTYwMTZaIiBmaWxsPSIjRkNENDYyIi8+Cjwvc3ZnPgo=">
              </div>
              <h3 class="pl-4 font-bold">
                <?php echo esc_html($item->name); ?>
              </h3>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>