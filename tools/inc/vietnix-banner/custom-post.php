<?php

/**
 * post_content_filter_by_paragraph_Center function
 *
 * @param [type] $content
 * @return void
 */
function post_content_filter_by_paragraph_Center($content)
{
  $post_categories = array();
  $post_banners = array();

  // Loop to get ID catgory of post
  foreach (get_the_category(get_the_ID()) as $cat) {
    $post_categories[] = $cat->term_id;
  }

  // Query list Vietnix banner
  $banners_query = array(
    'posts_per_page'   => -1,
    'post_status'      => array('publish'),
    'post_type'        => 'vietnix_banner',
    'order'            => 'DESC',
    'orderby'          => 'date',
    'suppress_filters' => false,
  );
  $banners = get_posts($banners_query);

  // Filter Vietnix banner allow post category
  foreach ($banners as $banner) {
    $settings = get_post_meta($banner->ID, '_vietnix_banner_single_settings', true);

    if (isset($settings['category'])) {
      $banner_categories = $settings['category'] ? $settings['category'] : array();

      foreach ($banner_categories as $cat) {
        if (in_array($cat, $post_categories)) {
          array_push($post_banners, $banner->ID);
        }
      }
    }
  }

  // Random banner when banner in muitiple category
  if (count($post_categories) > 1 && count($post_banners) > 3) {
    $rand_banner = array_rand($post_banners, 3);
    $post_banners = array_filter(
      $post_banners,
      fn ($key) => in_array($key, $rand_banner),
      ARRAY_FILTER_USE_KEY
    );
  }

  // Have Vietnix banner show on post
  if ($post_banners) {
    if (!is_main_query() || is_admin()) {
      return $content;
    }

    foreach ($post_banners as $banner) {
      $settings = get_post_meta($banner, '_vietnix_banner_single_settings', true);
      $position = $settings['position'];
      $pos_insert = $settings['pos_insert'];
      $number_p = $settings['number_p'];
      $tag_insert = $settings['tag_insert'];

      if (!isset($position)) {
        continue;
      }

      // INSERT AFTER NUMBER PARAGRAPHS THE CONTENT POST
      if ($position == 'in_content') {
        if ($pos_insert == 'after') {
          $closing_p = '</' . $tag_insert . '>';

          $paragraphs = explode($closing_p, $content);

          foreach ($paragraphs as $index => $paragraph) {
            if (trim($paragraph)) {
              $paragraphs[$index] .= $closing_p;
            }

            $inside_content = do_shortcode('[VIETNIX_BANNER id="' . $banner . '"]');

            if ($number_p == ($index + 1)) {
              $paragraphs[$index] .=  $inside_content;
            }
          }
          $content = implode('', $paragraphs);
        } else if ($pos_insert == 'before') {
          $closing_p = '<' . $tag_insert;
          $paragraphs = explode($closing_p, $content);

          foreach ($paragraphs as $index => $paragraph) {
            if (trim($paragraph) && $index) {
              $paragraphs[$index] = $closing_p . $paragraphs[$index];
            }

            $inside_content = do_shortcode('[VIETNIX_BANNER id="' . $banner . '"]');

            if ($number_p == $index) {
              $paragraphs[$index] = $inside_content . $paragraphs[$index];
            }
          }
          $content = implode('', $paragraphs);
        }
      }

      // INSERT BEGIN THE CONTENT POST
      if ($position == 'before_content') {
        $before_content = do_shortcode('[VIETNIX_BANNER id="' . $banner . '"]');
        $content = $before_content . $content;
      }

      // INSERT END THE CONTENT POST
      if ($position == 'after_content') {
        $after_content = do_shortcode('[VIETNIX_BANNER id="' . $banner . '"]');
        $content = $content . $after_content;
      }
    }
  }
  return $content;
}

/**
 * add_filter the_content
 */
add_filter('the_content', 'post_content_filter_by_paragraph_Center');
