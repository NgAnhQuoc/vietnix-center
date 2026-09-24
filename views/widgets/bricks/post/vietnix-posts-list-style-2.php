<?php
try {
  $data = isset($data) ? $data : new stdClass();
  $settings = $data->settings;
  $order = isset($settings['order']) ? $settings['order'] : 'DESC';
  $args = array(
    'post_status' => 'publish',
    'post_type' => $settings['post_type'],
    'posts_per_page' => $settings['num_posts'],
    'order' => $order,
    's' => '',
  );
  $template_id = !empty($settings['loop_item_template']) ? intval($settings['loop_item_template']) : false;
  $list_query = new WP_Query($args);
?>
  <div id="vnx_posts_list_data_<?= $settings['widget_id'] ?>" class="lg:grid-cols-1 lg:grid-cols-2 lg:grid-cols-3">
    <input type="hidden" name="posts_type" value="<?= $settings['post_type'] ?>">
    <input type="hidden" name="posts_per_page" value="<?= $settings['num_posts'] ?>">
    <input type="hidden" name="loop_item_template" value="<?= $settings['loop_item_template'] ?>">
    <input type="hidden" name="template_nodata_id" value="<?= $settings['template_nodata_id'] ?>">
    <input type="hidden" name="string_search_query" value="">
  </div>
  <div id="vnx_posts_list_<?= $settings['widget_id'] ?>" class="grid lg:grid-cols-<?= $settings['num_row'] ?? 1 ?> md:grid-cols-1 vnx_posts_list relative" data-widget_id="<?= $settings['widget_id'] ?>">
    <?php
    if ($list_query->have_posts()) {
      while ($list_query->have_posts()) {
        $list_query->the_post();
        echo "<div class='vnx_posts_list_conetnt'>";
        echo "</div>";
        wp_reset_postdata();
      }
    }
    $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
    $next_page = intval($current_page) + 1;
    ?>
  </div>
  <?php
  if ($list_query->have_posts()) {
    $hidden_class= $settings['use_paginte']== '0' ?'hidden':'';
  ?>
    <div class="flex flex-col items-center mt-5 vnx_pages_pagination <?= $hidden_class ?>">
      <!-- Help text -->
      <span class="text-sm text-gray-700 dark:text-gray-400">
        <?= $settings['page_text'] ?> <span class="font-semibold text-[#333333] current_page_<?= $settings['widget_id'] ?>">
          <?= intval($current_page) ?>
        </span>
        <?= $settings['of_text'] ?> <span class="font-semibold text-[#333333] max_num_pages_<?= $settings['widget_id'] ?>"><?= $list_query->max_num_pages ?></span>
      </span>
      <div class="inline-flex mt-2 xs:mt-0">
        <!-- Previous Button -->
        <button id="vnx_btn_prv_<?= $settings['widget_id'] ?>" data-prv-page="1" data-widget_id="<?= $settings['widget_id'] ?>" style="display: none!important;" class="vnx_btn_prv inline-flex items-center px-4 py-2 mr-3 text-sm font-medium text-[#333333] bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-[#38A7FF]">
          <?= $settings['prv_btn_text'] ?>
        </button>
        <?php if ($list_query->max_num_pages > 1) { ?>
          <button id="vnx_btn_loadmore_<?= $settings['widget_id'] ?>" data-next-page="<?= $next_page ?>" data-widget_id="<?= $settings['widget_id'] ?>" class="vnx_btn_loadmore inline-flex items-center px-4 py-2 text-sm font-medium text-[#333333] bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-[#38A7FF]">
            <?= $settings['next_btn_text'] ?>
          </button>
        <?php } ?>
      </div>
    </div>
  <?php } ?>
<?php
} catch (Exception $e) {
  error_log($e->getMessage());
}
?>
