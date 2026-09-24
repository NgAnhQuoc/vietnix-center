<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

if (!function_exists('vnx_get_custom_posts_list_Center')) {
  function vnx_get_custom_posts_list_Center()
  {
    $meta_query_relation = $_POST['data']['data']['meta_query_relation'] ? $_POST['data']['data']['meta_query_relation'] : 'OR';
    $meta_query = array(
      'relation' => $meta_query_relation,
    );
    if ($_POST['data']['data']['post_type'] == 'jobs') {
      $meta_query['relation'] = 'AND';
      $meta_query_jobs = array(
        'relation' => $meta_query_relation,
      );
      if ($_POST['data']['data']['meta_query']) {
        $meta_query_jobs = array(
          'relation' => $meta_query_relation,
        );
        $ram_key = $_POST['data']['data']['meta_query'][0]['key'];
        foreach ($_POST['data']['data']['meta_query'] as $key => $param) {
          if ($ram_key != $param['key']) {
            $ram_key = $param['key'];
            $meta_query[] = $meta_query_jobs;
            $meta_query_jobs = array(
              'relation' => 'OR',
            );
            $meta_query_jobs[] = array(
              'key' => $param['key'],
              'value' => $param['value'],
              'compare' => $param['compare'],
            );
          } else {
            $meta_query_jobs[] = array(
              'key' => $param['key'],
              'value' => $param['value'],
              'compare' => $param['compare'],
            );
          }
        }
        $meta_query[] = $meta_query_jobs;
      }
      $meta_query[] = array(
        'key' => 'deadline',
        'value' => date('Y-m-d'),
        'compare' => '>=',
        'type' => 'DATE',
      );
    } else {
      if ($_POST['data']['data']['meta_query']) {
        foreach ($_POST['data']['data']['meta_query'] as $param) {
          $meta_query[] = array(
            'key' => $param['key'],
            'value' => $param['value'],
            'compare' => $param['compare'],
          );
        }
      }
    }
    $args = array();
    foreach ($_POST['data']['data'] as $key => $param) {
      $args[$key] = $param;
    }
    $args['meta_query'] = $meta_query;
    if ($_POST['data']['data']['s']) {
      $args['s'] = $_POST['data']['data']['s'];
    }
    $query = new WP_Query($args);
    $response['data'] = [];
    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $response['data'][] = $post_id;
        wp_reset_postdata();
      }
    }
    $response['max_numpage'] = $query->max_num_pages;
    echo json_encode($response);
    exit();
  }
  add_action('wp_ajax_vnx_get_custom_posts_list_center', 'vnx_get_custom_posts_list_Center');
  add_action('wp_ajax_nopriv_vnx_get_custom_posts_list_center', 'vnx_get_custom_posts_list_Center');
}

if (!function_exists('vnx_get_custom_posts_template_Center')) {
  function vnx_get_custom_posts_template_Center()
  {
    if (isset($_POST['post_data']['data'])) {
      $data = $_POST['post_data']['data'];
      $args = $data;
      $query = new WP_Query($args);
      if ($query->have_posts()) {
        // Start the loop
        while ($query->have_posts()) {
          $query->the_post();
          $template_id = $_POST['loop_item_template'];
          echo do_shortcode("[bricks_template id=\"$template_id\"]");
        }
        wp_reset_postdata();
      } else {
        $template_id = $data['no_data_template'];
        echo do_shortcode("[bricks_template id=\"$template_id\"]");
      }
    }
    exit();
  }

  add_action('wp_ajax_vnx_get_custom_posts_template_center', 'vnx_get_custom_posts_template_Center');
  add_action('wp_ajax_nopriv_vnx_get_custom_posts_template_center', 'vnx_get_custom_posts_template_Center');
}

// đầu tiên sẽ load hàm này 
if (!function_exists('vnx_search_custom_posts_list_Center')) {
  function vnx_search_custom_posts_list_Center()
  {
    global $wpdb;
    if (isset($_POST['data']) && is_array($_POST['data']) && isset($_POST['data']['data'])) {
      $data = $_POST['data']['data'];
      $max_num_pages = null;
      $today_date = date('Y-m-d');
      $query = $wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
        WHERE p.post_type = %s AND p.post_status = 'publish' AND p.post_title LIKE %s",
        $data['post_type'],
        '%' . $wpdb->esc_like($data['s']) . '%'
      );

      if ($data['post_type'] == 'jobs') {
        $query .= $wpdb->prepare(
          " AND EXISTS (
            SELECT 1 FROM {$wpdb->postmeta} pm 
            WHERE pm.post_id = p.ID AND pm.meta_key = %s AND CAST(pm.meta_value AS DATE) > %s
          )",
          'deadline',
          $today_date
        );
      }

      $query .= " ORDER BY p.post_date DESC";
      $post_ids = $wpdb->get_col($query);

      $posts_per_page = isset($data['posts_per_page']) ? intval($data['posts_per_page']) : 10;
      $paged = isset($data['paged']) ? intval($data['paged']) : 1;
      $total_posts = count($post_ids);
      $max_num_pages = ceil($total_posts / $posts_per_page);

      $start = ($paged - 1) * $posts_per_page;
      $response['data'] = array_slice($post_ids, $start, $posts_per_page);
      $response['max_numpage'] = $max_num_pages;

      wp_send_json($response);
    } else {
      wp_send_json_error(['message' => 'Invalid input data']);
    }
  }

  add_action('wp_ajax_vnx_search_custom_posts_list_center', 'vnx_search_custom_posts_list_Center');
  add_action('wp_ajax_nopriv_vnx_search_custom_posts_list_center', 'vnx_search_custom_posts_list_Center');
}


if (!function_exists('vnx_get_template_list_Center')) {
  function vnx_get_template_list_Center()
  {
    global $wpdb;

    $template_type = $_POST['template_type'];
    $result = array();
    if ($template_type == 'bricks') {
      $query = $wpdb->prepare(
        "SELECT ID, post_title
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE post_type = 'bricks_template'
        ",
      );
      if (isset($_POST['category'])) {
        $category_slug = $_POST['category'];
        $query .= $wpdb->prepare(" AND post_status = 'publish'AND t.slug = %s", $category_slug);
      }
      $bricks_templates = $wpdb->get_results($query);
      $response = array(
        'id' => array(-1),
        'name' => array(-1)
      );
      foreach ($bricks_templates as $template) {
        $response['id'][] = $template->ID;
        $response['name'][] = $template->post_title;
      }
      $result = array('data' => $response);
    }

    wp_send_json_success($result);
    exit();
  }
  add_action('wp_ajax_vnx_get_template_list_center', 'vnx_get_template_list_Center');
  add_action('wp_ajax_nopriv_vnx_get_template_list_center', 'vnx_get_template_list_Center');
}


//ajax get post for custom post list v2

if (!function_exists('vnx_get_listpost_Center')) {
  function vnx_get_listpost_Center()
  {
    //tham số đầu vào của dahnh sách bài viết
    $post_type = isset($_POST['data']['post_type']) ? sanitize_text_field($_POST['data']['post_type']) : '';
    $meta_query = array('relation' => 'OR');
    $per_page = isset($_POST['data']['perpage']) ? intval($_POST['data']['perpage']) : 9;
    $current_page = isset($_POST['data']['current_page']) ? intval($_POST['data']['current_page']) : 1;
    if (isset($_POST['data']['meta_keys']) && is_array($_POST['data']['meta_keys'])) {
      foreach ($_POST['data']['meta_keys'] as $key => $meta_value) {
        $meta_query[] = array(
          'key' => sanitize_text_field($meta_value['key']),
          'value' => sanitize_text_field($meta_value['value']),
          'compare' => '='
        );
      }
    }
    $args = array(
      'post_type' => $post_type,
      'meta_query' => $meta_query,
      'posts_per_page' => $per_page,
      'paged' => $current_page,
    );

    $query = new WP_Query($args);
    $total_pages = $query->max_num_pages;
    $response = [];

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        $response[] = get_the_ID();
      }
      wp_reset_postdata();
    }

    wp_send_json_success(array(
      'data' => $response,
      'total_pages' => $total_pages,
      'current_page' => $current_page,
    ));
    exit();
  }
  add_action('wp_ajax_vnx_get_listpost_center', 'vnx_get_listpost_Center');
  add_action('wp_ajax_nopriv_vnx_get_listpost_center', 'vnx_get_listpost_Center');
}

if (!function_exists('vnx_get_posts_template_Center')) {
  function vnx_get_posts_template_Center()
  {
    if (isset($_POST['post_data']['data'])) {
      $data = $_POST['post_data']['data'];
      $args = $data;
      if (empty($args['no_data_template'])) {
        $query = new WP_Query($args);
        if ($query->have_posts()) {
          // Start the loop
          while ($query->have_posts()) {
            $query->the_post();
            $template_id = $_POST['loop_item_template'];
          echo do_shortcode("[bricks_template id=\"$template_id\"]");

          }
          wp_reset_postdata();
        }
      } else {
        $template_id = $data['no_data_template'];
        echo do_shortcode("[bricks_template id=\"$template_id\"]");
      }

    }
    exit();
  }

  add_action('wp_ajax_vnx_get_posts_template_center', 'vnx_get_posts_template_Center');
  add_action('wp_ajax_nopriv_vnx_get_posts_template_center', 'vnx_get_posts_template_Center');
}

if (!function_exists('vnx_onload_list_post_Center')) {
  function vnx_onload_list_post_Center()
  {
    if (isset($_POST['data']) && is_array($_POST['data']) && isset($_POST['data']['data'])) {
      $data = $_POST['data']['data'];
      $post_type = isset($data['post_type']) ? sanitize_text_field($data['post_type']) : 'post';
      $search = isset($data['s']) ? sanitize_text_field($data['s']) : '';
      $posts_per_page = isset($data['posts_per_page']) ? intval($data['posts_per_page']) : 10;
      $paged = isset($data['paged']) ? intval($data['paged']) : 1;

      // Tạo điều kiện meta_query
      $meta_query = [];

      if ($post_type === 'jobs') {
        $meta_query[] =
          [
          'key' => 'deadline',
          'value' => date('Y-m-d'),
          'compare' => '>',
          'type' => 'DATE'
          ];
      }
      if (!empty($data['meta_query']) && is_array($data['meta_query'])) {
        if ($post_type === 'jobs') {
          $meta_query = ['relation' => 'AND'];
          $meta_query_groups = [];

          // Duyệt qua từng meta_query và tự động nhóm theo key
          foreach ($data['meta_query'] as $meta) {
            $key = sanitize_text_field($meta['key']);
            $value = sanitize_text_field($meta['value']);
            // Tạo nhóm nếu chưa có
            if (!isset($meta_query_groups[$key])) {
              $meta_query_groups[$key] = ['relation' => 'OR'];
            }
            // Thêm điều kiện vào nhóm tương ứng
            $meta_query_groups[$key][] = [
              'key' => $key,
              'value' => $value,
              'compare' => '='
            ];
          }
          // Thêm từng nhóm vào `meta_query` nếu có dữ liệu hợp lệ
          foreach ($meta_query_groups as $group) {
            if (count($group) > 1) {
              $meta_query[] = $group;
            }
          }

        }else{
          foreach ($data['meta_query'] as $meta) {
            $meta_query[] = [
              'key' => sanitize_text_field($meta['key']),
              'value' => sanitize_text_field($meta['value']),
              'compare' => '='
            ];
          }
        }
      }
      // Thiết lập query
      $args = [
        'post_type' => $post_type,
        'post_status' => 'publish',
        's' => $search,
        'meta_query' => ['relation' => 'AND', $meta_query],
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'orderby' => 'date',
        'order' => 'DESC'
      ];
      $query = new WP_Query($args);

      $response = [
        'data' => wp_list_pluck($query->posts, 'ID'), // Lấy danh sách ID bài viết
        'max_numpage' => $query->max_num_pages
      ];

      wp_send_json($response);
    } else {
      wp_send_json_error(['message' => 'Invalid input data']);
    }
  }


  add_action('wp_ajax_vnx_onload_list_post_center', 'vnx_onload_list_post_Center');
  add_action('wp_ajax_nopriv_vnx_onload_list_post_center', 'vnx_onload_list_post_Center');
}
