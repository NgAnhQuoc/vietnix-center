<?php

namespace VNX_API_Center;

class VietnixAPI_Categories_Mkt
{
  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_route'));
  }

  public function register_route()
  {
    register_rest_route(VNX_Api_Prefix_Mkt, '/categories', array(
      'methods'             => 'GET',
      'permission_callback' => array('\VNX_API_Center\VietnixPluginAPI', 'authenticate'),
      'callback'            => array($this, 'get_categories'),
    ));
  }

  public function get_categories(\WP_REST_Request $request)
  {
    try {
      $terms = get_terms(array(
        'taxonomy'   => 'category',
        'hide_empty' => true,   // chỉ lấy category có bài viết public
        'number'     => 0,      // lấy tất cả, không giới hạn
        'orderby'    => 'name',
        'order'      => 'ASC',
      ));

      if (is_wp_error($terms)) {
        throw new \Exception($terms->get_error_message());
      }

      $items = array();
      foreach ($terms as $term) {
        $items[] = $this->format_term($term);
      }

      return new \WP_REST_Response(array(
        'success' => true,
        'message' => 'Categories fetched successfully',
        'data'    => array(
          'items'      => $items,
          'totalItems' => count($items),
        ),
      ), 200);

    } catch (\Exception $e) {
      error_log('Error in VietnixApi_Categories_Mkt::get_categories – ' . $e->getMessage());
      return new \WP_REST_Response(array(
        'success' => false,
        'message' => 'An error occurred while fetching categories.',
        'data'    => array('error' => $e->getMessage()),
      ), 500);
    }
  }

  // -----------------------------------------------------------------------
  // Helpers
  // -----------------------------------------------------------------------

  /**
   * Định dạng một term category thành cấu trúc output.
   */
  private function format_term(\WP_Term $term): array
  {
    return array(
      'id'          => (int) $term->term_id,
      'name'        => $term->name,
      'slug'        => $term->slug,
      'description' => $term->description,
      'parentId'    => (int) $term->parent,  // 0 = cate gốc
    );
  }
}

new VietnixAPI_Categories_Mkt();
