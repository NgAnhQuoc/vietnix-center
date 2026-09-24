<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
use HelperCenter\View;

/**
 * Display navigation to next/previous set of posts when applicable.
 */
if (!function_exists('vnx_paging_nav_Center')) {

  function vnx_load_more_Center()
  {
    $ajaxposts = new WP_Query([
      'post_type' => 'publications',
      'posts_per_page' => 6,
      'orderby' => 'date',
      'order' => 'DESC',
      'paged' => $_POST['paged'],
    ]);

    $response = '';

    if ($ajaxposts->have_posts()) {
      while ($ajaxposts->have_posts()) : $ajaxposts->the_post();
        $response .= get_template_part('parts/card', 'publication');
      endwhile;
    } else {
      $response = '';
    }

    echo $response;
    exit;
  }

  add_action('wp_ajax_vnx_load_more_center', 'vnx_load_more_Center');
  add_action('wp_ajax_nopriv_vnx_load_more_center', 'vnx_load_more_Center');
}
function vnx_load_more_posts_Center() {

  if(isset($_POST['author']))
    $author = $_POST['author'];
  else $author = '';
  $wp_query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => $_POST['posts_per_page'],
    'paged' => $_POST['page'],
    'post_status' => $_POST['post_status'],
    'author' => $author,
  ]);
  if($wp_query->have_posts()) {
    while($wp_query->have_posts()) : $wp_query->the_post();
      View::render('components/post/' . $_POST['card']);
    endwhile;
  }
  wp_reset_postdata();
  exit;
}
add_action('wp_ajax_vnx_load_more_posts_center', 'vnx_load_more_posts_Center');
add_action('wp_ajax_nopriv_vnx_load_more_posts_center', 'vnx_load_more_posts_Center');