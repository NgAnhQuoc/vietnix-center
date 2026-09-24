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
  $current_page = isset($_POST['data']['current_page']) ? intval($_POST['data']['current_page']) + 1 : 1;
  $template_id = !empty($settings['loop_item_template']) ? intval($settings['loop_item_template']) : false;
  $list_query = new WP_Query($args);
?>
  <div id="vnx_posts_list_data_<?= $settings['widget_id'] ?>" class="lg:grid-cols-1 lg:grid-cols-2 lg:grid-cols-3">
    <input type="hidden" name="id_widget" value="<?= $settings['widget_id'] ?>">
    <input type="hidden" name="posts_type" value="<?= $settings['post_type'] ?>">
    <input type="hidden" name="posts_per_page" value="<?= $settings['num_posts'] ?>">
    <input type="hidden" name="current_page" value="<?= $current_page ?>">
    <input type="hidden" name="loop_item_template" value="<?= $settings['loop_item_template'] ?>">
    <input type="hidden" name="template_nodata_id" value="<?= $settings['template_nodata_id'] ?>">
    <input type="hidden" name="meta_query" value="">
    <input type="hidden" name="string_search_query" value="">
  </div>
  <div id="vnx_posts_list_<?= $settings['widget_id'] ?>" class="grid lg:grid-cols-<?= $settings['num_row'] ?? 1 ?> md:grid-cols-1 vnx_posts_list relative" data-widget_id="<?= $settings['widget_id'] ?>" style="gap: <?= $settings['gap_rows'] ?>px;">
    <?php
    if ($list_query->have_posts()) {
      while ($list_query->have_posts()) {
        $list_query->the_post();
        wp_reset_postdata();
      }
    }
    ?>
    <div class="loading_posts" v-show="LoadingResult == true">
      <div role="status"><img src="https://vietnix.vn/wp-content/uploads/2023/06/Rolling-1.2s-50px.svg" alt=""></div>
    </div>
  </div>
  <?php
  if ($list_query->have_posts()) {
    $hidden_class = $settings['show_pagination'] == '0' ? 'hidden' : '';
    $type_pagi = isset($settings['use_paginte']) ? $settings['use_paginte'] : 'loadmore';
    $next_page = intval($current_page) + 1;
  ?>
    <div class="flex flex-col items-center mt-5 vnx_pages_pagination <?= $hidden_class ?>" v-if="ShowButton == true">
      <?php if ($type_pagi == 'loadmore' && $hidden_class != 'hidden') {
      ?>
        <button id="vnx_btn_loadmore_<?= $settings['widget_id'] ?>" data-next-page="<?= $next_page ?>" data-widget_id="<?= $settings['widget_id'] ?>"
          class="vnx_btn_loadmore inline-flex items-center px-4 py-2  bg-white  border-gray-300  text-[#007CFC] gap-2" @click="clickLoadMore()" >
          <?= $settings['loadmore_text'] ?><?php if(!empty($settings['loadmore_icon'])){ echo ' <i class="'. $settings['loadmore_icon']['icon'].' "></i>'; } ?>
        </button>
      <?php } ?>
    </div>
  <?php } ?>

<?php
} catch (Exception $e) {
  error_log($e->getMessage());
}
?>