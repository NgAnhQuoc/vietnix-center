<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
if (!function_exists('regenerate_attachment_slugs_Center')) {
  function regenerate_attachment_slugs_Center()
  {
    try {
      check_ajax_referer('media-tool-ajax-nonce', 'security');
      if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'), 403);
      }
      $args = array(
        'post_type' => 'attachment',
        'post_status' => 'any',
        'posts_per_page' => 500,
        'paged' => max(1, absint($_GET['paged'] ?? 1)),
      );

      $attachments = new WP_Query($args);

      if ($attachments->have_posts()) {
        while ($attachments->have_posts()) {
          $attachments->the_post();
          $attachment_id = get_the_ID();
          $random_slug = 'vnx-' . wp_generate_password(32, false);
          wp_update_post(
            array(
              'ID' => $attachment_id,
              'post_name' => $random_slug,
            )
          );
        }
      }
      $posts_on_current_page = $attachments->post_count;
      wp_reset_postdata();
      wp_send_json_success($posts_on_current_page);
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }

  add_action('wp_ajax_regenerate_attachment_slugs_center', 'regenerate_attachment_slugs_Center');
}

if (!function_exists('reset_attachment_slugs_to_default_Center')) {
  function reset_attachment_slugs_to_default_Center()
  {
    try {
      check_ajax_referer('media-tool-ajax-nonce', 'security');
      if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'), 403);
      }
      $args = array(
        'post_type' => 'attachment',
        'post_status' => 'any',
        'posts_per_page' => 500,
        'orderby' => 'post_date',
        'order' => 'ASC',
        'paged' => max(1, absint($_GET['paged'] ?? 1)),
      );

      $attachments = new WP_Query($args);

      if ($attachments->have_posts()) {
        while ($attachments->have_posts()) {
          $attachments->the_post();

          $attachment_id = get_the_ID();
          $attachment_title = get_the_title();
          $new_slug = sanitize_title($attachment_title); // Generate a new slug from the attachment title

          // Update the attachment's slug
          wp_update_post(
            array(
              'ID' => $attachment_id,
              'post_name' => $new_slug,
            )
          );
        }
      }
      $posts_on_current_page = $attachments->post_count;
      wp_reset_postdata();
      wp_send_json_success($posts_on_current_page);
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }

  add_action('wp_ajax_reset_attachment_slugs_to_default_center', 'reset_attachment_slugs_to_default_Center');
}

if (!function_exists('count_attachment_paged_Center')) {
  function count_attachment_paged_Center()
  {
    try {
      // Verify nonce and check user capabilities
      check_ajax_referer('media-tool-ajax-nonce', 'security');

      // Ensure only administrators can run this
      if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        exit;
      }

      // Use a direct database query for better performance
      global $wpdb;
      $query = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash'";
      $total_attachments = (int)$wpdb->get_var($query);

      // Calculate total pages
      $posts_per_page = 500; // Number of attachments per page
      $total_pages = ceil($total_attachments / $posts_per_page);

      // Send the response
      wp_send_json_success($total_pages);
      exit;
    } catch (Exception $e) {
      // Log the error for debugging
      error_log('Error in count_attachment_paged_Center: ' . $e->getMessage());

      // Send a detailed error response
      wp_send_json_error(array(
        'message' => 'Something went wrong!',
        'error' => $e->getMessage(),
      ));
    }
  }

  add_action('wp_ajax_count_attachment_paged_center', 'count_attachment_paged_Center');
}