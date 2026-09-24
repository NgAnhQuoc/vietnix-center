<?php
add_filter('manage_lap-trinh_posts_columns', 'by_manage_posts_columns_dev_Center');
add_filter('manage_lap-trinh_posts_custom_column', 'by_manage_dev_posts_custom_column_dev_Center', 10, 2);
add_filter('manage_directory_posts_custom_column', 'by_manage_dev_posts_custom_column_dev_Center', 10, 2);

// show columns in dashboard Lập Trình
function by_manage_posts_columns_dev_Center($post_columns)
{
  $post_type = get_post_type();
  if ($post_type == 'lap-trinh') {
    $post_columns = array_slice($post_columns, 0, 3, true) + array('seo_author_dev' => 'SEO', 'writer_dev' => 'Writer') + array_slice($post_columns, 1, count($post_columns), true);
    return $post_columns;
  }
  return $post_columns;
}
//show data columns SEO and Writer
function by_manage_dev_posts_custom_column_dev_Center($column_name, $post_id)
{
  if ($column_name == 'seo_author_dev') {
    $user_id = (get_field('seo_author_dev', $post_id, false, false)) ?? '';
    if ($user_id != '') {
      echo get_userdata($user_id)->display_name;
    }
  }
  if ($column_name == 'writer_dev') {
    $user_id = (get_field('writer_dev', $post_id, false, false)) ?? '';
    if ($user_id != '') {
      echo get_userdata($user_id)->display_name;
    }
  }
  return $column_name;
}

add_action('pre_get_posts', 'filter_lap_trinh_posts_Center');
function filter_lap_trinh_posts_Center($query)
{
  if (!is_admin() || !$query->is_main_query()) {
    return;
  }

  if ('seo_author_dev' === $query->get('orderby')) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'seo_author_dev');
  }

  if ('writer_dev' === $query->get('orderby')) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'writer_dev');
  }
}


add_filter('manage_edit-lap-trinh_sortable_columns', 'my_sortable_cake_column_dev_Center');
function my_sortable_cake_column_dev_Center($sortable_columns)
{
  $sortable_columns['seo_author_dev'] = 'seo_author_dev';
  $sortable_columns['writer_dev'] = 'writer_dev';
  return $sortable_columns;
}


// Add Custom Filter Dropdown for SEO Author
function custom_posts_filter_dev_Center()
{
  global $typenow;
  $post_types = array('lap-trinh'); // Add other post types if needed

  // Display filter only on selected post types
  if (in_array($typenow, $post_types)) {
    $users = get_users(); // Retrieve all users
    if ($users) {
      echo '<select name="seo_author_dev_filter">';
      echo '<option value="">Select SEO</option>';
      foreach ($users as $user) {
        $selected = isset($_GET['seo_author_dev_filter']) && $_GET['seo_author_dev_filter'] == $user->ID ? 'selected' : '';
        echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . get_the_author_meta('display_name', $user->ID) . '</option>';
      }
      echo '</select>';
      echo '<select name="writer_dev_filter">';
      echo '<option value="">Select Writer</option>';
      foreach ($users as $user) {
        $selected = isset($_GET['writer_dev_filter']) && $_GET['writer_dev_filter'] == $user->ID ? 'selected' : '';
        echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . get_the_author_meta('display_name', $user->ID) . '</option>';
      }
      echo '</select>';
    }
  }
}
add_action('restrict_manage_posts', 'custom_posts_filter_dev_Center');

// Apply the filter to the query
function apply_custom_posts_filter_dev_Center($query)
{
  global $pagenow;
  if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'lap-trinh' && $query->is_main_query()) {
    $meta_query = array('relation' => 'AND');
    if (isset($_GET['seo_author_dev_filter']) && $_GET['seo_author_dev_filter'] != '') {
      $meta_query[] = array(
        'key' => 'seo_author_dev',
        'value' => $_GET['seo_author_dev_filter'],
        'compare' => '=',
      );
    }

    if (isset($_GET['writer_dev_filter']) && $_GET['writer_dev_filter'] != '') {
      $meta_query[] = array(
        'key' => 'writer_dev',
        'value' => $_GET['writer_dev_filter'],
        'compare' => '=',
      );
    }

    if (!empty($meta_query)) {
      $query->set('meta_query', $meta_query);
    }
  }
}
add_action('pre_get_posts', 'apply_custom_posts_filter_dev_Center');
