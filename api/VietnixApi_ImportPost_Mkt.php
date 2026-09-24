<?php

namespace VNX_API_Center;

class VietnixAPI_ImportPost_Mkt
{
  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_route'));
  }

  public function register_route()
  {
    register_rest_route(VNX_Api_Prefix_Mkt, '/import-post', array(
      'methods'             => 'POST',
      'permission_callback' => array('\VNX_API_Center\VietnixPluginAPI', 'authenticate'),
      'callback'            => array($this, 'import_post'),
      'args'                => array(

        'title' => array(
          'required'          => true,
          'type'              => 'string',
          'sanitize_callback' => 'sanitize_text_field',
          'validate_callback' => function ($param) {
            if (empty(trim($param))) {
              return new \WP_Error('invalid_title', 'title không được để trống.');
            }
            return true;
          },
        ),

        'content' => array(
          'required'          => true,
          'type'              => 'string',
          'validate_callback' => function ($param) {
            if (empty(trim($param))) {
              return new \WP_Error('invalid_content', 'content không được để trống.');
            }
            return true;
          },
        ),

        'slug' => array(
          'required'          => false,
          'type'              => 'string',
          'sanitize_callback' => 'sanitize_title',
        ),

        'excerpt' => array(
          'required'          => false,
          'type'              => 'string',
          'sanitize_callback' => 'sanitize_textarea_field',
        ),

        'status' => array(
          'required'          => false,
          'type'              => 'string',
          'default'           => 'publish',
          'validate_callback' => function ($param) {
            $allowed = array('publish', 'draft', 'pending', 'private', 'scheduled');
            if (!in_array(strtolower($param), $allowed, true)) {
              return new \WP_Error(
                'invalid_status',
                'status phải là một trong: ' . implode(', ', $allowed) . '.'
              );
            }
            return true;
          },
          'sanitize_callback' => 'sanitize_text_field',
        ),

        'author_id' => array(
          'required'          => false,
          'type'              => 'integer',
          'default'           => 0,
          'validate_callback' => fn($p) => is_numeric($p) && (int) $p >= 0,
        ),

        'category_ids' => array(
          'required'          => false,
          'type'              => 'string',
          'default'           => '',
          'validate_callback' => fn($p) => $p === '' || (bool) preg_match('/^[\d,]+$/', $p),
        ),

        'tag_ids' => array(
          'required'          => false,
          'type'              => 'string',
          'default'           => '',
          'validate_callback' => fn($p) => $p === '' || (bool) preg_match('/^[\d,]+$/', $p),
        ),

        'featured_image_url' => array(
          'required'          => false,
          'type'              => 'string',
          'default'           => '',
          'validate_callback' => fn($p) => empty($p) || filter_var($p, FILTER_VALIDATE_URL) !== false,
          'sanitize_callback' => 'esc_url_raw',
        ),

        'rankmath' => array(
          'required'          => false,
          'type'              => 'object',
          'default'           => array(),
          'description'       => 'Object chứa các trường SEO RankMath: title, description, focus_keyword, permalink.',
          'sanitize_callback' => array($this, 'sanitize_rankmath'),
        ),

        'meta' => array(
          'required' => false,
          'type'     => 'object',
          'default'  => array(),
        ),

        'date' => array(
          'required'          => false,
          'type'              => 'string',
          'default'           => '',
          'validate_callback' => fn($p) => empty($p) || (bool) preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}:\d{2})?$/', $p),
        ),

        'scheduled_date' => array(
          'required'          => false,
          'type'              => 'string',
          'default'           => '',
          'validate_callback' => fn($p) => empty($p) || (bool) preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}:\d{2})?$/', $p),
        ),

      ),
    ));
  }

  // -----------------------------------------------------------------------

  public function import_post(\WP_REST_Request $request)
  {
    try {
      $title          = $request->get_param('title');
      $content        = $request->get_param('content');   // raw HTML — giữ nguyên
      $slug           = $request->get_param('slug');
      $excerpt        = $request->get_param('excerpt');
      $status         = $request->get_param('status') ?: 'publish';
      $author_id      = (int) $request->get_param('author_id');
      $cat_raw        = $request->get_param('category_ids');
      $tag_raw        = $request->get_param('tag_ids');
      $image_url      = $request->get_param('featured_image_url');
      $rankmath       = $request->get_param('rankmath');
      $meta           = $request->get_param('meta');
      $date_input     = $request->get_param('date');
      $scheduled_date = $request->get_param('scheduled_date');

      // Resolve author
      $author_id = $this->resolve_author($author_id);

      $status = strtolower($status);
      if ($status === 'scheduled') {
        $status = 'future';
      }

      $publish_date = !empty($scheduled_date) ? $scheduled_date : $date_input;

      if ($status === 'future') {
        if (empty($publish_date)) {
          throw new \Exception('Để lên lịch bài viết (status = scheduled), bạn phải cung cấp ngày giờ (scheduled_date hoặc date).');
        }
      }

      // Build post data
      $post_data = array(
        'post_title'   => $title,
        'post_content' => $content,   // lưu raw HTML, hiện ra y như nhập vào
        'post_status'  => $status,
        'post_author'  => $author_id,
        'post_type'    => 'post',
      );

      if (!empty($slug)) {
        $post_data['post_name'] = $slug;
      }
      if (!empty($excerpt)) {
        $post_data['post_excerpt'] = $excerpt;
      }
      if (!empty($publish_date)) {
        $post_data['post_date']     = $this->parse_date($publish_date);
        $post_data['post_date_gmt'] = get_gmt_from_date($post_data['post_date']);
        if ($status === 'future' && $post_data['post_date_gmt'] <= gmdate('Y-m-d H:i:s')) {
          throw new \Exception('Thời gian lên lịch phải là một thời điểm trong tương lai.');
        }
      }

      $post_id = wp_insert_post($post_data, true);

      if (is_wp_error($post_id)) {
        throw new \Exception('wp_insert_post thất bại: ' . $post_id->get_error_message());
      }

      // Categories
      if (!empty($cat_raw)) {
        $cat_ids = $this->parse_ids($cat_raw);
        if (!empty($cat_ids)) {
          wp_set_post_categories($post_id, $cat_ids);
        }
      }

      // Tags
      if (!empty($tag_raw)) {
        $tag_ids = $this->parse_ids($tag_raw);
        if (!empty($tag_ids)) {
          wp_set_post_tags($post_id, $tag_ids);
        }
      }

      // Featured image (sideload từ URL)
      $warnings = array();
      if (!empty($image_url)) {
        $thumbnail_id = $this->sideload_image($image_url, $post_id, $title);
        if (!is_wp_error($thumbnail_id)) {
          set_post_thumbnail($post_id, $thumbnail_id);
        } else {
          $warning_msg = 'Không thể set featured image: ' . $thumbnail_id->get_error_message();
          $warnings[] = $warning_msg;
          error_log('VietnixAPI_ImportPost_Mkt::import_post – sideload_image: ' . $thumbnail_id->get_error_message());
        }
      }

      // RankMath SEO fields
      if (!empty($rankmath) && is_array($rankmath)) {
        $this->save_rankmath_meta($post_id, $rankmath);
      }

      // Custom meta
      if (!empty($meta) && is_array($meta)) {
        foreach ($meta as $key => $value) {
          $safe_key = sanitize_key($key);
          if (!empty($safe_key)) {
            update_post_meta($post_id, $safe_key, $value);
          }
        }
      }

      $post = get_post($post_id);

      $response_data = array(
        'success' => true,
        'message' => 'Import post thành công.',
        'data'    => $this->format_post($post),
      );

      if (!empty($warnings)) {
        $response_data['warnings'] = $warnings;
      }

      return new \WP_REST_Response($response_data, 201);
    } catch (\Exception $e) {
      error_log('VietnixAPI_ImportPost_Mkt::import_post – ' . $e->getMessage());
      return new \WP_REST_Response(array(
        'success' => false,
        'message' => 'Đã xảy ra lỗi khi import post.',
        'data'    => array('error' => $e->getMessage()),
      ), 500);
    }
  }

  // -----------------------------------------------------------------------
  // Helpers
  // -----------------------------------------------------------------------

  /**
   * Resolve author: dùng ID được truyền nếu hợp lệ, không thì dùng current user,
   * cuối cùng fallback admin đầu tiên.
   */
  private function resolve_author(int $author_id): int
  {
    if ($author_id > 0 && get_user_by('id', $author_id)) {
      return $author_id;
    }

    $current = get_current_user_id();
    if ($current > 0) {
      return $current;
    }

    $admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    return !empty($admins) ? (int) $admins[0] : 1;
  }

  /** Parse chuỗi "1,2,3" → [1, 2, 3] (chỉ giữ giá trị > 0). */
  private function parse_ids(string $raw): array
  {
    return array_values(
      array_filter(
        array_map('intval', explode(',', $raw)),
        fn($id) => $id > 0
      )
    );
  }

  /** Parse date string thành định dạng MySQL datetime. */
  private function parse_date(string $date): string
  {
    $ts = strtotime($date);
    if ($ts === false) {
      throw new \Exception('Định dạng date không hợp lệ: ' . $date);
    }
    return date('Y-m-d H:i:s', $ts);
  }

  /**
   * Sideload ảnh từ URL vào Media Library.
   * Trả về attachment ID hoặc WP_Error.
   */
  private function sideload_image(string $url, int $post_id, string $title)
  {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url($url);
    if (is_wp_error($tmp)) {
      return $tmp;
    }

    $file_arr = array(
      'name'     => sanitize_file_name(basename(wp_parse_url($url, PHP_URL_PATH)) ?: 'image.jpg'),
      'tmp_name' => $tmp,
    );

    $attachment_id = media_handle_sideload($file_arr, $post_id, $title);

    if (file_exists($tmp)) {
      @unlink($tmp);
    }

    return $attachment_id;
  }

  /**
   * Sanitize object rankmath từ request.
   * Chỉ giữ các key hợp lệ, sanitize từng field.
   */
  public function sanitize_rankmath($value): array
  {
    if (!is_array($value)) {
      return array();
    }

    $sanitized = array();

    if (isset($value['title']) && !empty($value['title'])) {
      $sanitized['title'] = sanitize_text_field($value['title']);
    }

    if (isset($value['description']) && !empty($value['description'])) {
      $sanitized['description'] = sanitize_textarea_field($value['description']);
    }

    if (isset($value['focus_keyword']) && !empty($value['focus_keyword'])) {
      $sanitized['focus_keyword'] = sanitize_text_field($value['focus_keyword']);
    }

    if (isset($value['permalink']) && !empty($value['permalink'])) {
      $sanitized['permalink'] = sanitize_title($value['permalink']);
    }

    if (isset($value['thumbnail_url']) && !empty($value['thumbnail_url'])) {
      $url = esc_url_raw($value['thumbnail_url']);
      if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
        $sanitized['thumbnail_url'] = $url;
      }
    }

    return $sanitized;
  }

  /**
   * Lưu các meta field RankMath từ object rankmath.
   */
  private function save_rankmath_meta(int $post_id, array $rankmath): void
  {
    if (!empty($rankmath['title'])) {
      update_post_meta($post_id, 'rank_math_title', $rankmath['title']);
    }

    if (!empty($rankmath['description'])) {
      update_post_meta($post_id, 'rank_math_description', $rankmath['description']);
    }

    if (!empty($rankmath['focus_keyword'])) {
      update_post_meta($post_id, 'rank_math_focus_keyword', $rankmath['focus_keyword']);
    }

    if (!empty($rankmath['permalink'])) {
      $update_slug = wp_update_post(array(
        'ID'        => $post_id,
        'post_name' => $rankmath['permalink'],
      ), true);

      if (is_wp_error($update_slug)) {
        error_log('VietnixAPI_ImportPost_Mkt::save_rankmath_meta – update permalink: ' . $update_slug->get_error_message());
      }
    }

    if (!empty($rankmath['thumbnail_url'])) {
      update_post_meta($post_id, 'rank_math_facebook_image', $rankmath['thumbnail_url']);
      update_post_meta($post_id, 'rank_math_twitter_image', $rankmath['thumbnail_url']);
    }
  }

  /** Format WP_Post thành array output chuẩn. */
  private function format_post(\WP_Post $post): array
  {
    $cats     = get_the_category($post->ID);
    $raw_tags = get_the_tags($post->ID);

    $categories = array();
    if (!empty($cats) && !is_wp_error($cats)) {
      foreach ($cats as $cat) {
        $categories[] = array(
          'id'   => (int) $cat->term_id,
          'name' => $cat->name,
          'slug' => $cat->slug,
        );
      }
    }

    $tags = array();
    if (!empty($raw_tags) && !is_wp_error($raw_tags)) {
      foreach ($raw_tags as $tag) {
        $tags[] = array(
          'id'   => (int) $tag->term_id,
          'name' => $tag->name,
          'slug' => $tag->slug,
        );
      }
    }

    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full') ?: null;

    return array(
      'id'            => (int) $post->ID,
      'title'         => $post->post_title,
      'slug'          => $post->post_name,
      'url'           => get_permalink($post->ID),
      'status'        => $post->post_status === 'future' ? 'scheduled' : $post->post_status,
      'author_id'     => (int) $post->post_author,
      'categories'    => $categories,
      'tags'          => $tags,
      'thumbnail_url' => $thumbnail_url,
      'rankmath'      => array(
        'title'         => get_post_meta($post->ID, 'rank_math_title', true) ?: null,
        'description'   => get_post_meta($post->ID, 'rank_math_description', true) ?: null,
        'focus_keyword' => get_post_meta($post->ID, 'rank_math_focus_keyword', true) ?: null,
        'permalink'     => $post->post_name,
        'thumbnail_url' => get_post_meta($post->ID, 'rank_math_facebook_image', true) ?: null,
      ),
      'created_at'    => mysql2date('c', $post->post_date_gmt . ' GMT', false),
      'updated_at'    => mysql2date('c', $post->post_modified_gmt . ' GMT', false),
    );
  }
}

new VietnixAPI_ImportPost_Mkt();