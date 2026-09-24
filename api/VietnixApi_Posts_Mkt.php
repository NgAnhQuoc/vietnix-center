<?php

namespace VNX_API_Center;

class VietnixAPI_Posts_Mkt
{
  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_route'));
  }

  public function register_route()
  {
    register_rest_route(VNX_Api_Prefix_Mkt, '/posts', array(
      'methods'             => 'GET',
      'permission_callback' => array('\VNX_API_Center\VietnixPluginAPI', 'authenticate'),
      'callback'            => array($this, 'get_posts'),
      'args'                => array(

        'page' => array(
          'default'           => 1,
          'required'          => false,
          'validate_callback' => fn($p) => is_numeric($p) && $p > 0,
        ),

        'itemsPerPage' => array(
          'default'           => 12,
          'required'          => false,
          'validate_callback' => function ($param) {
            if (!is_numeric($param) || $param <= 0 || $param > 100) {
              return new \WP_Error('invalid_items_per_page', 'itemsPerPage phải là số từ 1 đến 100.');
            }
            return true;
          },
        ),

        'namekey' => array(
          'default'           => '',
          'required'          => false,
          'sanitize_callback' => 'sanitize_text_field',
        ),

        // Lọc theo ngày tạo (YYYY-MM-DD)
        'createdFrom' => array(
          'default'           => '',
          'required'          => false,
          'validate_callback' => fn($p) => empty($p) || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $p),
        ),
        'createdTo' => array(
          'default'           => '',
          'required'          => false,
          'validate_callback' => fn($p) => empty($p) || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $p),
        ),

        // Lọc theo ngày cập nhật (YYYY-MM-DD)
        'updatedFrom' => array(
          'default'           => '',
          'required'          => false,
          'validate_callback' => fn($p) => empty($p) || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $p),
        ),
        'updatedTo' => array(
          'default'           => '',
          'required'          => false,
          'validate_callback' => fn($p) => empty($p) || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $p),
        ),

        // Lọc theo category ID (dấu phẩy: 1,2,3)
        'categoryId' => array(
          'default'           => '',
          'required'          => false,
          'validate_callback' => fn($p) => $p === '' || (bool) preg_match('/^[\d,]+$/', $p),
        ),
        
        // Sort theo date
        'createdAt' => array(
          'default'           => 'desc',
          'required'          => false,
          'validate_callback' => fn($p) => in_array(strtolower($p), ['asc', 'desc'], true),
          'sanitize_callback' => 'sanitize_text_field',
        ),

      ),
    ));
  }

  public function get_posts(\WP_REST_Request $request)
  {
    try {
      $paged          = max(1, (int) $request->get_param('page'));
      $items_per_page = max(1, (int) $request->get_param('itemsPerPage'));
      $name_key       = sanitize_text_field($request->get_param('search'));
      $created_from   = sanitize_text_field($request->get_param('createdFrom'));
      $created_to     = sanitize_text_field($request->get_param('createdTo'));
      $updated_from   = sanitize_text_field($request->get_param('updatedFrom'));
      $updated_to     = sanitize_text_field($request->get_param('updatedTo'));
      $category_id     = sanitize_text_field($request->get_param('categoryId'));
      $created_at_sort = strtoupper(sanitize_text_field($request->get_param('createdAt')));

      $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $items_per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => in_array($created_at_sort, ['ASC', 'DESC']) ? $created_at_sort : 'DESC',
      );

      if (!empty($name_key)) {
        $args['s'] = esc_html($name_key);
      }

      // Lọc theo category
      if (!empty($category_id)) {
        $cat_ids = array_filter(array_map('intval', explode(',', $category_id)), fn($id) => $id > 0);
        if (!empty($cat_ids)) {
          $args['category__in'] = array_values($cat_ids);
        }
      }

      // Lọc theo ngày tạo (post_date)
      if ($range = $this->build_date_range($created_from, $created_to, 'post_date')) {
        $args['date_query'][] = $range;
      }
      // Lọc theo ngày cập nhật (post_modified)
      if ($range = $this->build_date_range($updated_from, $updated_to, 'post_modified')) {
        $args['date_query'][] = $range;
      }
      if (isset($args['date_query']) && count($args['date_query']) > 1) {
        $args['date_query']['relation'] = 'AND';
      }

      $query = new \WP_Query($args);

      if (!$query->have_posts()) {
        return $this->empty_response($paged, $items_per_page);
      }

      // Batch pre-cache authors & parent categories
      cache_users(array_unique(array_map(fn($p) => (int) $p->post_author, $query->posts)));
      $this->prime_parent_category_cache($query->posts);

      $items = array_map(fn($post) => $this->format_post($post), $query->posts);

      return new \WP_REST_Response(array(
        'success' => true,
        'message' => 'Search results fetched successfully',
        'data'    => array(
          'items' => $items,
          'meta'  => array(
            'currentPage'  => $paged,
            'totalPages'   => (int) $query->max_num_pages,
            'totalItems'   => (int) $query->found_posts,
            'itemsPerPage' => $items_per_page,
          ),
        ),
      ), 200);
    } catch (\Exception $e) {
      error_log('VietnixAPI_Search_Mkt::search_posts – ' . $e->getMessage());
      return new \WP_REST_Response(array(
        'success' => false,
        'message' => 'An error occurred while searching posts.',
        'data'    => array('error' => $e->getMessage()),
      ), 500);
    }
  }

  // -------------------------------------------------------------------------
  // Helpers
  // -------------------------------------------------------------------------

  /** Build date_query entry từ from/to (YYYY-MM-DD). Trả về null nếu cả hai rỗng. */
  private function build_date_range(string $from, string $to, string $column = 'post_date'): ?array
  {
    if (empty($from) && empty($to)) {
      return null;
    }

    $range = array('column' => $column, 'inclusive' => true);

    if (!empty($from)) {
      [$y, $m, $d]    = explode('-', $from);
      $range['after'] = array('year' => (int) $y, 'month' => (int) $m, 'day' => (int) $d);
    }
    if (!empty($to)) {
      [$y, $m, $d]     = explode('-', $to);
      $range['before'] = array('year' => (int) $y, 'month' => (int) $m, 'day' => (int) $d);
    }

    return $range;
  }

  /** Format một WP_Post thành array output. */
  private function format_post(\WP_Post $post): array
  {
    $author_id = (int) $post->post_author;

    // Categories
    $categories = array();
    if ($post->post_type === 'post') {
      $cats = get_the_category($post->ID);
      if (!empty($cats) && !is_wp_error($cats)) {
        $categories = $this->build_category_flat($cats);
      }
    }

    // Tags
    $tags     = array();
    $raw_tags = get_the_tags($post->ID);
    if (!empty($raw_tags) && !is_wp_error($raw_tags)) {
      $tags = array_map(fn($t) => ['id' => (int) $t->term_id, 'name' => $t->name, 'slug' => $t->slug], $raw_tags);
    }

    // SEO meta: Rank Math → Yoast fallback
    $seo_title = get_post_meta($post->ID, 'rank_math_title', true)
               ?: get_post_meta($post->ID, '_yoast_wpseo_title', true);
    $seo_desc  = get_post_meta($post->ID, 'rank_math_description', true)
               ?: get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);

    // Excerpt
    $excerpt = trim($post->post_excerpt) ?: wp_trim_words(
      strip_shortcodes(strip_tags($post->post_content)), 55, '...'
    );

    return array(
      'id'         => (int) $post->ID,
      'title'      => $post->post_title,
      'slug'       => $post->post_name,
      'url'        => get_permalink($post->ID),
      'excerpt'    => $excerpt,
      'author'     => array('id' => $author_id, 'name' => get_the_author_meta('display_name', $author_id)),
      'post_type'  => $post->post_type,
      'categories' => $categories,
      'tags'       => $tags,
      'meta'       => array(
        'seo_title'       => $seo_title ?: null,
        'seo_description' => $seo_desc ?: null,
      ),
      'created_at' => mysql2date('c', $post->post_date_gmt . ' GMT', false),
      'updated_at' => mysql2date('c', $post->post_modified_gmt . ' GMT', false),
    );
  }

  /** Response rỗng theo đúng cấu trúc. */
  private function empty_response(int $page, int $items_per_page): \WP_REST_Response
  {
    return new \WP_REST_Response(array(
      'success' => true,
      'message' => 'No results found',
      'data'    => array(
        'items' => array(),
        'meta'  => array(
          'currentPage'  => $page,
          'totalPages'   => 0,
          'totalItems'   => 0,
          'itemsPerPage' => $items_per_page,
        ),
      ),
    ), 200);
  }

  /**
   * Batch pre-load parent categories chưa có trong object cache.
   * Tránh N×get_category() query trong build_category_flat().
   */
  private function prime_parent_category_cache(array $posts): void
  {
    $missing = array();

    foreach ($posts as $post) {
      if ($post->post_type !== 'post') continue;
      $cats = get_the_category($post->ID);
      if (empty($cats) || is_wp_error($cats)) continue;
      foreach ($cats as $cat) {
        if ($cat->parent && !wp_cache_get((int) $cat->parent, 'terms')) {
          $missing[] = (int) $cat->parent;
        }
      }
    }

    if (!empty($missing)) {
      get_terms(array(
        'taxonomy'               => 'category',
        'include'                => array_unique($missing),
        'hide_empty'             => false,
        'update_term_meta_cache' => false,
      ));
    }
  }

  /**
   * Build flat category array: children trước, parents sau.
   * Tự fetch parent nếu chưa được gán vào bài.
   */
  private function build_category_flat(array $cats): array
  {
    $ids_in_post   = array_flip(array_map(fn($c) => $c->term_id, $cats));
    $children      = array();
    $parents       = array();
    $added_parents = array();

    foreach ($cats as $cat) {
      $node = array(
        'id'        => (int) $cat->term_id,
        'name'      => $cat->name,
        'slug'      => $cat->slug,
        'parent_id' => $cat->parent ? (int) $cat->parent : null,
      );

      if ($cat->parent) {
        $children[] = $node;

        if (!isset($ids_in_post[$cat->parent]) && !isset($added_parents[$cat->parent])) {
          $parent_term = get_category($cat->parent);
          if ($parent_term && !is_wp_error($parent_term)) {
            $parents[] = array(
              'id'        => (int) $parent_term->term_id,
              'name'      => $parent_term->name,
              'slug'      => $parent_term->slug,
              'parent_id' => $parent_term->parent ? (int) $parent_term->parent : null,
            );
            $added_parents[$cat->parent] = true;
          }
        }
      } else {
        if (!isset($added_parents[$cat->term_id])) {
          $parents[]                       = $node;
          $added_parents[$cat->term_id] = true;
        }
      }
    }

    return empty($children) ? $parents : array_merge($children, $parents);
  }
}
