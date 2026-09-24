<?php

$data = isset($data) ? $data : new stdClass();
$settings = isset($data->settings) ? $data->settings->settings : "";
$data_row = isset($data->settings) ? $data->settings : "";
$post_status = 'publish';
$loop_card = isset($settings['loop_card']) ? $settings['loop_card'] : '';
$num_post = isset($settings['num_post']) ? $settings['num_post'] : 6;
$show_pagination = (isset($settings["pagination_show"])) ? 'yes' : 'no';

$show_filter = isset($settings["show_filter"]) ? true : false;
$post_type_slug = isset($settings["post_type_slug"]) ? $settings["post_type_slug"] : 'post';
$tax_id = isset($settings['tax_id']) ? $settings['tax_id'] : '';
$filter_setting = isset($settings['tax_filter']) ? $settings['tax_filter'] : '';
$show_all_tab = isset($settings['show_all_tab']) ? 'yes' : 'no';
$template_id = !empty($settings['template']) ? intval($settings['template']) : false;
$data_row->set_attribute('_root', 'class', 'vnx_loop_section gap-[22px] vnx_loop_' . $post_type_slug);

echo "<script>let root_div = `{$data_row->render_attributes('_root')}`</script>";
$args = array(
  'post_type' => $post_type_slug,
  'posts_per_page' => $num_post,
  'post_status' => $post_status,
);
$wp_query = new WP_Query($args);

?>
<div id="vnx_themes_posts_list_data">
  <input type="hidden" name="posts_type" value="<?= $post_type_slug ?>">
  <input type="hidden" name="posts_per_page" value="<?= $num_post ?>">
  <input type="hidden" name="taxonomy" value="<?= $tax_id ?>">
  <input type="hidden" name="loop_card" value="<?= $template_id ?>">
</div>
<?
if ($show_filter == true && $filter_setting && $tax_id) {
  echo '<div class="w-full m-auto mb-5"><div class="selector-category my-3 text-center pb-5">';
  if ($show_all_tab == 'yes') {
?>
    <button class="themes-get-id all-theme-post active_category border rounded-full py-2 px-3 mr-2 sm:mr-4 mb-2 hover:bg-[#38A7FF] hover:text-white" data-id="all" id="cat-theme-id-all">Tất cả</button>
  <?php
  }
  foreach ($filter_setting as $key => $value) {
    if ($value['term_id'] == 0 || !$value['term_id'])
      continue;
    $id = $value['term_id'];
    $name = $value['term_name'] ?? '';
  ?>
    <button class="themes-get-id all-theme-post border rounded-full py-2 px-3 mr-2 sm:mr-4 mb-2 hover:bg-[#38A7FF] hover:text-white" data-id="<?php echo esc_attr($id); ?>" id="cat-theme-id-<?php echo esc_attr($id); ?>">
      <?php echo esc_html($name); ?>
    </button>
<?
  }
  echo '</div></div>';
}
?>
<!-- View post  -->
<section class="cover-post mt-5">
  <!-- content  -->
</section>
<!-- End view post -->

<?php
if ($show_pagination == 'yes') {
?>
  <div class="theme-post-vnx-pagination w-full flex items-center justify-center mt-8 ">
    <? vnx_pagi_ajax_Center($wp_query); ?>
  </div>
<?
}
wp_reset_postdata();
?>

<style>
  .selector-category {
    padding: 0 10%;
  }

  .active_category {
    background-color: #38A7FF;
    color: #FFFFFF;
  }

  .page-numbers {
    font-size: 16px;
    font-weight: 400px;
    padding: 4px;
    margin: 0px 4px;
  }

  .page-numbers.current {
    color: #38A7FF;
    font-weight: 600px !important;
  }

  @media (max-width: 992px) {
    .selector-category {
      padding: 0 30px;
    }
  }

  @media (max-width:640px) {
    .selector-category {
      padding: 0;
    }
  }

  .loading_posts {
    position: unset;
    background: none;
  }
</style>