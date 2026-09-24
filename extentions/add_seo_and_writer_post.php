<?php
add_filter( 'manage_posts_columns' , 'by_manage_posts_columns_Center' );
add_filter( 'manage_post_posts_custom_column', 'by_manage_post_posts_custom_column_Center', 10, 2 );
add_filter( 'manage_directory_posts_custom_column', 'by_manage_post_posts_custom_column_Center', 10, 2 );

function by_manage_posts_columns_Center( $post_columns) {
	$post_type = get_post_type();
	if (  $post_type == 'post' ) {
    $post_columns = array_slice( $post_columns, 0, 3 , true ) + array ( 'seo_author' => 'SEO', 'writer' => 'Writer' ) + array_slice( $post_columns, 1, count( $post_columns ), true);
    return $post_columns;
	}
	return $post_columns;
}

function by_manage_post_posts_custom_column_Center( $column_name, $post_id ) {
  if( $column_name == 'seo_author' ) {
    $user_id = (get_field('seo_author', $post_id, false, false)) ?? '';
    if($user_id != ''){
      echo get_userdata( $user_id )->display_name;
    }
	}
  if( $column_name == 'writer' ) {
		$user_id = (get_field('writer', $post_id, false, false)) ?? '';
    if($user_id != ''){
      echo get_userdata( $user_id )->display_name;
    } 
	}
	
	return $column_name;
}

add_action( 'pre_get_posts', 'smashing_posts_orderby_Center' );
function smashing_posts_orderby_Center( $query ) {
  if( ! is_admin() || ! $query->is_main_query() ) {
    return;
  }

  if ( 'seo_author' === $query->get( 'orderby') ) {
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'meta_key', 'seo_author' );
  }
	
	if ( 'writer' === $query->get( 'orderby') ) {
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'meta_key', 'writer' );
  }
	
}


add_filter( 'manage_edit-post_sortable_columns', 'my_sortable_cake_column_Center' );
function my_sortable_cake_column_Center( $sortable_columns ) {
    $sortable_columns['seo_author'] = 'seo_author';
    $sortable_columns['writer'] = 'writer';
    return $sortable_columns;
}


// Add Custom Filter Dropdown for SEO Author
function custom_posts_filter_Center() {
  global $typenow;
  $post_types = array('post'); // Add other post types if needed

  // Display filter only on selected post types
  if (in_array($typenow, $post_types)) {
      $users = get_users(); // Retrieve all users
      if ($users) {
          echo '<select name="seo_author_filter">';
          echo '<option value="">Select SEO</option>';
          foreach ($users as $user) {
              $selected = isset($_GET['seo_author_filter']) && $_GET['seo_author_filter'] == $user->ID ? 'selected' : '';
              echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . get_the_author_meta('display_name', $user->ID) . '</option>';
          }
          echo '</select>';

          echo '<select name="writer_filter">';
            echo '<option value="">Select Writer</option>';
            foreach ($users as $user) {
                $selected = isset($_GET['writer_filter']) && $_GET['writer_filter'] == $user->ID ? 'selected' : '';
                echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . get_the_author_meta('display_name', $user->ID) . '</option>';
            }
            echo '</select>';
      }
  }
}
add_action('restrict_manage_posts', 'custom_posts_filter_Center');

// Apply the filter to the query
function apply_custom_posts_filter_Center($query) {
  global $pagenow;
  if ($pagenow == 'edit.php' ) {
    $meta_query = array('relation' => 'AND');
    if (isset($_GET['seo_author_filter']) && $_GET['seo_author_filter'] != '') {
      $meta_query[] = array(
          'key' => 'seo_author',
          'value' => $_GET['seo_author_filter'],
          'compare' => '=',
      );
    }

    if (isset($_GET['writer_filter']) && $_GET['writer_filter'] != '') {
        $meta_query[] = array(
            'key' => 'writer',
            'value' => $_GET['writer_filter'],
            'compare' => '=',
        );
    }
    if (!empty($meta_query)) {
      $query->set('meta_query', $meta_query);
    }
  }
}
add_action('pre_get_posts', 'apply_custom_posts_filter_Center');