<?php

namespace VNX_API_Center;

class VietnixAPI_Category
{
  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_route'));
  }

  public function register_route()
  {
    register_rest_route(VNX_Api_Prefix_V1, '/category', array(
      'methods' => 'GET',
      'permission_callback' => array('VNX_API_Center\VietnixPluginAPI', 'authenticate'),
      'callback' => array($this, 'get_category'),
      'args' => array(
        'page' => array(
          'default' => 1,
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_numeric($param) && $param > 0;
          }
        ),
        'itemsPerPage' => array(
          'default' => 10,
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_numeric($param) && $param > 0;
          }
        ),
        'search' => array(
          'default' => '',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_string($param);
          }
        ),
        'orderBy' => array(
          'default' => 'id',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return in_array($param, array('id', 'name', 'slug'));
          }
        ),
        'sortDir' => array(
          'default' => 'asc',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return in_array($param, array('asc', 'desc'));
          }
        ),
        'parent' => array(
          'default' => true,
          'required' => false,
          'type' => 'boolean',
        ),
      ),
    ));

    register_rest_route(VNX_Api_Prefix_V1, '/category/posts', array(
      'methods' => 'POST',
      'permission_callback' => array('VNX_API_Center\VietnixPluginAPI', 'authenticate'),
      'callback' => array($this, 'get_posts_by_category'),
    ));
  }

  public function get_category(\WP_REST_Request $request)
  {
    try {
      // Get and validate parameters
      $page = max(1, intval($request->get_param('page')));
      $items_per_page = max(1, intval($request->get_param('itemsPerPage')));

      $args = array(
        'orderby' => $request->get_param('orderBy'),
        'order' => $request->get_param('sortDir'),
        'number' => $items_per_page,
        'offset' => ($page - 1) * $items_per_page,
        'search' => $request->get_param('search'),
        'parent' => $request->get_param('parent') ? 0 : '',
        'hide_empty' => false,
      );
      // Validate and set defaults for parameters
      $args['orderby'] = in_array($args['orderby'], ['id', 'name', 'slug']) ? $args['orderby'] : 'id';
      $args['order'] = in_array(strtolower($args['order']), ['asc', 'desc']) ? strtoupper($args['order']) : 'ASC';
      $args['search'] = is_string($args['search']) ? $args['search'] : '';
      $args['parent'] = is_bool($args['parent']) ? ($args['parent'] ? 0 : '') : '';

      // Get categories
      $categories = get_categories($args);

      // Get total count for pagination
      $total_args = $args;
      unset($total_args['number']);
      unset($total_args['offset']);
      $total_categories = wp_count_terms('category', $total_args);
      $total_categories = intval($total_categories);

      // Prepare categories data
      $categories_data = array();
      foreach ($categories as $category) {
        $category_data = array(
          'id' => $category->term_id,
          'name' => $category->name,
          'slug' => $category->slug,
          'description' => $category->description,
          'count' => $category->count,
        );

        // Add parent name if the category has a parent
        if ($category->parent !== 0) {
          $category_data['parent'] = $category->parent;
          $parent_category = get_term($category->parent, 'category');
          if (!is_wp_error($parent_category) && $parent_category) {
            $category_data['parent_name'] = $parent_category->name;
          }
        }

        $categories_data[] = $category_data;
      }

      // Prepare pagination info
      $total_pages = ceil($total_categories / $items_per_page);

      $response_data = array(
        'success' => true,
        'message' => 'Categories fetched successfully',
        'data' => array(
          'items' => $categories_data,
          'meta' => array(
            'totalItems' => $total_categories,
            'totalPages' => $total_pages,
            'page' => $page,
            'itemsPerPage' => $items_per_page
          )
        )
      );

      return new \WP_REST_Response($response_data, 200);

    } catch (\Exception $e) {
      // Log the error
      error_log('Error in get_category: ' . $e->getMessage());

      // Prepare error response
      $error_response = array(
        'success' => false,
        'message' => 'An error occurred while fetching categories',
        'data' => null
      );

      // You might want to include more details in debug mode
//      if (WP_DEBUG) {
//        $error_response['debug'] = $e->getMessage();
//      }

      return new \WP_REST_Response($error_response, 500);
    }
  }

  public function get_posts_by_category(\WP_REST_Request $request)
  {
    try {
      $params = $request->get_json_params();

      // Validate and set default values
      $category_ids = isset($params['category_id']) && is_array($params['category_id']) ? $params['category_id'] : array();
      $page = isset($params['page']) && is_numeric($params['page']) ? max(1, intval($params['page'])) : 1;
      $items_per_page = isset($params['itemsPerPage']) && is_numeric($params['itemsPerPage']) ? max(1, min(100, intval($params['itemsPerPage']))) : 10;
      $order_by = isset($params['orderBy']) && in_array($params['orderBy'], array('date', 'title', 'id')) ? $params['orderBy'] : 'date';
      $order = isset($params['sortDir']) && in_array(strtolower($params['sortDir']), array('asc', 'desc')) ? strtoupper($params['sortDir']) : 'DESC';

      if (empty($category_ids)) {
        return new \WP_REST_Response(array(
          'success' => false,
          'message' => 'No category IDs provided',
          'data' => null,
        ), 400);
      }

      // Get categories information
      $categories_info = array();
      foreach ($category_ids as $category_id) {
        $category = get_category($category_id);
        if ($category) {
          $category_info = array(
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug,
            'count' => $category->count,
            'parent' => $category->parent,
          );

          // Add parent category info if exists
          if ($category->parent !== 0) {
            $parent_category = get_category($category->parent);
            if ($parent_category) {
              $category_info['parent_name'] = $parent_category->name;
              $category_info['parent_slug'] = $parent_category->slug;
            }
          }

          $categories_info[] = $category_info;
        }
      }

      if (empty($categories_info)) {
        return new \WP_REST_Response(array(
          'success' => false,
          'message' => 'No valid categories found',
          'data' => null,
        ), 404);
      }

      $args = array(
        'category__in' => $category_ids,
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $items_per_page,
        'paged' => $page,
        'orderby' => $order_by,
        'order' => $order,
      );

      $query = new \WP_Query($args);

      if ($query->have_posts()) {
        $posts_data = array_map(function ($post) use ($category_ids) {
          $post_categories = get_the_category($post->ID);
          $matching_category = null;

          // Find the first category that matches the requested category IDs
          foreach ($post_categories as $category) {
            if (in_array($category->term_id, $category_ids)) {
              $matching_category = $category;
              break;
            }
          }

          return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'url' => get_permalink($post->ID),
            'date' => get_the_date('c', $post->ID),
            'category' => $matching_category ? $matching_category->name : '',
          );
        }, $query->posts);

        $response_data = array(
          'success' => true,
          'message' => 'Categories and posts fetched successfully',
          'data' => array(
            'categories' => $categories_info,
            'items' => $posts_data,
            'meta' => array(
              'totalItems' => $query->found_posts,
              'totalPages' => $query->max_num_pages,
              'page' => $page,
              'itemsPerPage' => $items_per_page,
            ),
          ),
        );

        return new \WP_REST_Response($response_data, 200);
      } else {
        return new \WP_REST_Response(array(
          'success' => true,
          'message' => 'Categories found, but no posts in these categories',
          'data' => array(
            'categories' => $categories_info,
            'items' => array(),
            'meta' => array(
              'totalItems' => 0,
              'totalPages' => 0,
              'page' => $page,
              'itemsPerPage' => $items_per_page,
            ),
          ),
        ), 200);
      }
    } catch (\Exception $e) {
      error_log('Error in get_posts_by_category: ' . $e->getMessage());
      return new \WP_REST_Response(array(
        'success' => false,
        'message' => 'An error occurred while fetching categories and posts',
        'data' => array('error' => $e->getMessage()),
      ), 500);
    }
  }
}