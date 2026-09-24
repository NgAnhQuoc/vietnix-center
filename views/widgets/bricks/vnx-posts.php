<?php

use HelperCenter\View;

if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html(__FILE__) . '</b></div>';
  return;
}
$settings = $data->settings;
$categories = isset($settings['categories']) ? $settings['categories'] : [];
$posts_per_page = isset($settings['posts_per_page']) ? $settings['posts_per_page'] : 12;
$paginate = isset($settings['paginate']) ? $settings['paginate'] : '';
$prev_icon = isset($settings['prev_icon']) && $settings['prev_icon']['library'] ? $settings['prev_icon'] : '';
$prev = $prev_icon ? Bricks\Element::render_icon($prev_icon, ['vnx_prev_icon']) : '';
$next_icon = isset($settings['next_icon']) && $settings['next_icon']['library'] ? $settings['next_icon'] : '';
$next = $next_icon ? Bricks\Element::render_icon($next_icon, ['vnx_next_icon']) : '';

$card = isset($settings->card) ? $settings->card : 'post-item-1';
$post_status = 'publish';

$args = array(
  'post_type'      => 'post',
  'posts_per_page' => $posts_per_page,
  'post_status'    => $post_status
);
if (!empty($categories)) {
  $args['category__in'] = $categories;
} else {
  if (is_category()) {
    $current_cat = get_queried_object()->term_taxonomy_id;
    $args['category__in'] = $current_cat;
  }
}
if (get_query_var('paged')) {
  $paged = get_query_var('paged');
} elseif (get_query_var('page')) {
  $paged = get_query_var('page');
} else {
  $paged = 1;
}
$args['paged'] = (int) $paged;

$loop = new WP_Query($args);

$my_class = [
  'vnx_element',
];
$data->set_attribute('_root', 'class', $my_class);

echo "<div {$data->render_attributes('_root')}>";
if ($loop->have_posts()) {
  echo '<section class="widget-posts grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">';
  while ($loop->have_posts()) {
    $loop->the_post();
    View::render('components/post/' . $card);
  }
  echo '</section>';
} else {
  View::render('components/post/not-found');
}

if ($paginate) {
  echo '<div class="vnx-pagination-2 w-full flex items-center justify-center mt-8 center">';
  vnx_paging_nav_custom_icon_Center($loop, $prev, $next);
  echo '</div>';
}
wp_reset_postdata();
echo '</div>';
