<?php

namespace RankMath\Sitemap\Providers;
use RankMath\Sitemap\Image_Parser;

if (!class_exists('RankMath')) {
  return;
}
class VnxCustomPageSitemap_Center implements Provider
{
  public function handles_type($type)
  {
    return 'san-pham' === $type;
  }

  public function get_index_links($max_entries)
  {
    return [
      [
        'loc' => \RankMath\Sitemap\Router::get_base_url('san-pham-sitemap.xml'),
        'lastmod' => date('c'),
      ]
    ];
  }

  public function get_sitemap_links($type, $max_entries, $current_page)
  {
    try {
      $links = [];
      $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_key' => 'vnx_check_product_page',
        'meta_value' => 'san-pham',
        'meta_compare' => 'LIKE',
        'posts_per_page' => -1,
      ]);

      $image_parser = new Image_Parser();
      foreach ($pages as $page) {
        $images = [];
        $post_images = $image_parser->get_images($page);
        if (!empty($post_images)) {
          foreach ($post_images as $img) {
            if (!empty($img['src'])) {
              $images[] = [
                'src' => $img['src'],
                'title' => $img['title'] ?? get_the_title($page->ID),
                'alt' => $img['alt'] ?? ''
              ];
            }
          }
        }
        // Thêm Featured Image
        if (has_post_thumbnail($page->ID)) {
          $images[] = [
            'src' => get_the_post_thumbnail_url($page->ID, 'full'),
            'title' => get_the_title($page->ID),
            'alt' => self::get_alt_tag(get_post_thumbnail_id($page->ID))
          ];
        }

        $links[] = [
          'loc' => get_permalink($page),
          'mod' => get_post_modified_time('c', true, $page),
          'images' => $images
        ];
      }

      return $links;
    } catch (Exception $e) {
      error_log('VNX Sitemap Error: ' . $e->getMessage());
    }
  }
  public static function get_alt_tag($attachment_id)
  {
    return (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
  }
}

// Filter URL trong sitemap
add_filter('rank_math/sitemap/xml_post_url', function ($url, $post) {
  if ($post->post_type === 'page' && get_post_meta($post->ID, 'vnx_check_product_page', true) === 'san-pham') {
    return '';
  }
  return $url;
}, 10, 2);

// Lọc sitemap trước khi thêm mới sitemap
add_filter('rank_math/sitemap/entry', function ($url, $type, $object) {
  if ($object->post_type === 'page' && get_post_meta($object->ID, 'vnx_check_product_page', true) === 'san-pham') {
    return '';
  }
  return $url;
}, 10, 3);

// Kích hoạt khi publish page
add_action('transition_post_status', function ($new_status, $old_status, $post) {
  if ($post->post_type === 'page' && $new_status === 'publish') {
    // Xóa cache sitemap
    do_action('rank_math/sitemap/clear_cache');
  }
}, 10, 3);

// Đăng ký provider sitemap mới với Rank Math
add_filter('rank_math/sitemap/providers', function ($providers) {
  // Thêm provider mới vào mảng providers hiện có
  $providers['san_pham'] = new \RankMath\Sitemap\Providers\VnxCustomPageSitemap_Center();
  return $providers;
});

// tắt bộ nhớ đệm sitemap
add_filter('rank_math/sitemap/enable_caching', '__return_false');



