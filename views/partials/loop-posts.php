<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();

$card = $data->card ? $data->card : 'card-post-1';

$class = $data->class ?: '';
$pagination_class = isset($data->pagination_class) ? $data->pagination_class : '';

$show_pagination = isset($data->show_pagination) ? (bool) $data->show_pagination : false;
$posts_per_page = isset($data->posts_per_page) ? (int) $data->posts_per_page : (int) get_option('posts_per_page');

$args = array(
  'post_type' => 'post',
  'posts_per_page' => $posts_per_page
);

if (isset($data->categories)) {
  $args['category_name'] = $data->categories;
}

if (isset($data->tags)) {
  $args['tag_slug__in'] = $data->tags;
}

if (isset($data->author)) {
  $args['author'] = $data->author;
}

if (isset($_GET['s'])) {
  $args['s'] = esc_sql($_GET['s']);
}

if (get_query_var('paged')) {
  $paged = get_query_var('paged');
} elseif (get_query_var('page')) {
  $paged = get_query_var('page');
} else {
  $paged = 1;
}

$args['paged'] = (int) $paged;

$wp_query = new WP_Query($args);
?>

<section class="<?= $class ?>">
  <?php
  if ($wp_query->have_posts()) :
    while ($wp_query->have_posts()) : $wp_query->the_post();
      View::render('components/cards/' . $card);
    endwhile;
  else :
    View::render('components/post/not-found');
  endif;
  ?>
</section>


<?php
if ($show_pagination == true) {
  echo '<div class="vnx-pagination-2 w-full center my-6 ' . $pagination_class . '">';
  vnx_paging_nav_Center($wp_query);
  echo '</div>';
}
wp_reset_postdata();
?>