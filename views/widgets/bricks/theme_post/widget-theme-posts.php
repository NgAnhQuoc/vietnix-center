<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
$data_row = isset($data->settings) ? $data->settings : "";

$card = (isset($data->card)) ? $data->card : 'post-theme-wp';
$class = isset($data->class) ? $data->class : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8';
$post_status = isset($data->post_status) ? $data->post_status : 'publish';
$settings = isset($data->settings) ? $data->settings->settings : "";
$num_post = isset($settings['num_post']) ? $settings['num_post'] : 6;
$show_pagination = (isset( $settings[ "pagination_show" ] )) ? 'yes' : 'no';
$show_filter = isset($settings["show_filter"]) ? 'yes' : 'no';
$post_type_slug = isset($settings["post_type_slug"]) ? $settings["post_type_slug"] : 'post';
$filter_setting = isset($settings['filter_setting']) ? $settings['filter_setting'] : '';
$current_pages = get_queried_object();
$show_all_tab = isset($settings['show_all_tab']) ? 'yes' : 'no';
echo "<script>let root_div = `{$data_row->render_attributes( '_root' )}`</script>";

$args = array(
  'post_type'      => $post_type_slug,
  'posts_per_page' => $num_post,
  'post_status'    => $post_status,
);

if (get_query_var('paged')) {
  $paged = get_query_var('paged');
} elseif (get_query_var('page')) {
  $paged = get_query_var('page');
} else {
  $paged = 1;
}

$wp_query = new WP_Query($args);
?>

<div id="vnx_themes_posts_list_data">
  <input type="hidden" name="posts_type" value="<?= $post_type_slug ?>">
  <input type="hidden" name="posts_per_page" value="<?= $num_post ?>">
</div>

<?

if ($show_filter == "yes" && $filter_setting) {

  echo '<div class="w-full m-auto mb-5"><div class="selector-category my-3 text-center pb-5">';
  if ($show_all_tab == 'yes') {
    ?>
    <button
      class="themes-get-id all-theme-post active_category border rounded-full py-2 px-3 mr-2 sm:mr-4 mb-2 hover:bg-[#38A7FF] hover:text-white"
      data-id="all" id="cat-theme-id-all">Tất cả</button>
    <?php
  }
  foreach ($filter_setting as $key => $value) {
    $value_term = $value ? $value['term'] : '';
    if ($value_term == '')
      continue;
    $term_data = explode('|', $value_term);
    if (empty($term_data) || $term_data[0] == '-1')
      continue;
    $id = $term_data[0];
    $name = $term_data[1];
?>
    <button class="themes-get-id all-theme-post border rounded-full py-2 px-3 mr-2 sm:mr-4 mb-2 hover:bg-[#38A7FF] hover:text-white" data-id="<?php echo esc_attr($id); ?>" id="cat-theme-id-<?php echo esc_attr($id); ?>"><?php echo $name ?></button>
<?
  }
  echo '</div></div>';
}
?>

<!-- View post  -->
<section class="cover-post mt-5 ">
  <div class="widget-posts <?= $class ?>">
    <?php
    $response = '';
    if ($wp_query->have_posts()) {
      while ($wp_query->have_posts()) :
        $wp_query->the_post();
        $response .= View::render('widgets/bricks/theme_post/' . $card);
      endwhile;
    } else {
      $response .= View::render('widgets/bricks/theme_post/');
    };
    ?>
  </div>
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
?>

<?php
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
</style>