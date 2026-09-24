<?php

namespace VNX_API_Center;

class VietnixAPI_Urls
{
  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_route'));
  }

  public function register_route()
  {
    register_rest_route(VNX_Api_Prefix_V1, '/urls', array(
      'methods' => 'GET',
      'permission_callback' => array('\VNX_API_Center\VietnixPluginAPI', 'authenticate'),
      'callback' => array($this, 'get_urls'),
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
            if (!is_numeric($param) || $param <= 0 || $param > 100) {
              return new \WP_Error('invalid_items_per_page', 'The itemsPerPage parameter must be a number between 1 and 100.');
            }
            return true;
          }
        ),
        'search' => array(
          'default' => '',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_string($param);
          }
        ),
        'searchUrl' => array(
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
            return in_array(strtolower($param), array('', 'id', 'name'));
          }
        ),
        'sortDir' => array(
          'default' => 'asc',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return in_array(strtolower($param), array('', 'asc', 'desc'));
          }
        ),
        'sortByCategory' => array(
          'default' => false,
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return in_array(strtolower($param), array('', 'asc', 'desc'));
          }
        ),
        'postType' => array(
          'default' => '',
          'required' => false,
          'type' => 'string',
        ),
      ),
    ));
  }

  static function get_urls(\WP_REST_Request $request)
  {
    try {
      $params = [
        'page' => max(1, intval($request->get_param('page'))),
        'items_per_page' => max(1, intval($request->get_param('itemsPerPage'))),
        'search_url' => $request->get_param('searchUrl'),
        'sort_by_category' => strtolower($request->get_param('sortByCategory')) ?: 'asc',
        'post_type' => strtolower($request->get_param('postType')),
        'search_title' => $request->get_param('search'),
        'order_by' => strtolower($request->get_param('orderBy')) ?: 'id',
        'order' => strtolower($request->get_param('sortDir')) ?: 'asc',
      ];

      $args = [
        'post_status' => 'publish',
        'posts_per_page' => $params['items_per_page'],
        'paged' => $params['page'],
        'orderby' => $params['order_by'],
        'order' => $params['order'],
        'post_type' => $params['post_type'] ?: ['post', 'page', 'lap-trinh'],
        'meta_query' => [
          'relation' => 'OR',
          [
            'key' => 'rank_math_robots',
            'compare' => 'NOT EXISTS'
          ],
          [
            'key' => 'rank_math_robots',
            'value' => 'noindex',
            'compare' => 'NOT LIKE'
          ]
        ]
      ];

      if ($params['search_url']) {
        $post_id = url_to_postid($params['search_url']);
        if (!$post_id) {
          return self::empty_response($params['page'], $params['items_per_page']);
        }
        $args['p'] = $post_id;
      }

      if ($params['search_title']) {
        $args['s'] = $params['search_title'];
      }

      $query = new \WP_Query($args);
      if ($query->have_posts()) {
        $data = self::process_posts($query->posts, $params['sort_by_category'], $params['post_type']);
      } else {
        return self::empty_response($params['page'], $params['items_per_page']);
      }

      return new \WP_REST_Response([
        'success' => true,
        'message' => 'URLs fetched successfully',
        'data' => [
          'items' => $data,
          'meta' => [
            'totalItems' => $query->found_posts,
            'totalPages' => $args['p'] ? 1 : $query->max_num_pages,
            'page' => $params['page'],
            'itemsPerPage' => $params['items_per_page']
          ]
        ]
      ], 200);
    } catch (\Exception $e) {
      error_log('Error in get_urls: ' . $e->getMessage());
      return new \WP_REST_Response([
        'success' => false,
        'message' => 'An error occurred while fetching URLs',
        'data' => ['error' => $e->getMessage()]
      ], 500);
    }
  }

  private static function process_posts($posts, $sort_by_category, $post_type)
  {
    $data_by_category = [];
    foreach ($posts as $post) {
      $post_data = [
        'id' => $post->ID,
        'post_type' => $post->post_type,
        'post_title' => $post->post_title,
        'permalink' => get_permalink($post->ID),
        'categories' => []
      ];

      $category = '';
      if ($post->post_type === 'post') {
        $categories = get_the_category($post->ID);
        if (!empty($categories) && !is_wp_error($categories)) {
          $category = $categories[0]->name;
          $post_data['categories'] = array_map(function ($cat) {
            $parent = $cat->parent ? get_category($cat->parent) : null;
            return [
              'term_id' => $cat->term_id,
              'name' => $cat->name,
              'slug' => $cat->slug,
              'parent' => $parent ? $parent->name : null,
            ];
          }, $categories);
        }
      }

      if ($post->post_type === 'lap-trinh') {
        $terms = get_the_terms($post->ID, 'tax_lap-trinh');
        if (!empty($terms) && !is_wp_error($terms)) {
          $category = $terms[0]->name;
          $post_data['categories'] = array_map(function ($term) {
            $parent = $term->parent ? get_term($term->parent) : null;
            return [
              'term_id' => $term->term_id,
              'name' => $term->name,
              'slug' => $term->slug,
              'parent' => $parent ? $parent->name : null,
            ];
          }, $terms);
        }
      }

      $data_by_category[$category][] = $post_data;
    }

    if ($sort_by_category === 'asc') {
      ksort($data_by_category);
    } else {
      krsort($data_by_category);
    }

    $data = [];
    if ($post_type === 'post' || $post_type === 'page') {
      $data = $data_by_category[$post_type] ?? [];
      unset($data_by_category[$post_type]);
    }
    foreach ($data_by_category as $posts) {
      $data = array_merge($data, $posts);
    }

    return $data;
  }

  private static function empty_response($page, $items_per_page)
  {
    return new \WP_REST_Response([
      'success' => true,
      'message' => 'No URLs found',
      'data' => [
        'items' => [],
        'meta' => [
          'totalItems' => 0,
          'totalPages' => 0,
          'page' => $page,
          'itemsPerPage' => $items_per_page
        ]
      ]
    ], 200);
  }
}
