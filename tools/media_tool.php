<?php
namespace VNXToolCenter;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

use HelperCenter\View;
use WP_Query;

try {
  if (!class_exists('VNX_MediaTool')) {
    class VNX_MediaTool
    {
      public static function Instance()
      {
        static $instance = null;
        if ($instance === null) {
          $instance = new self();
        }
        return $instance;
      }

      public function __construct()
      {
        $this->init();
      }

      public function init()
      {
        add_action('add_attachment', array($this, 'generate_random_slug_on_upload'));
        add_filter( 'request', array( $this, 'remove_attachment_query_var' ) );
        add_filter('attachment_link', array($this, 'change_attachment_link_to_file'), 10, 2);
        add_action('template_redirect', array($this, 'redirect_attachment_pages_to_file'));
        add_filter('register_post_type_args', array($this, 'make_attachments_private'), 10, 2);
        add_filter('redirect_canonical', array($this, 'remove_redirect_canonical_if_not_admin_page'), 10, 2);
        // remove_action('template_redirect', 'redirect_canonical');
      }
      public function remove_redirect_canonical_if_not_admin_page($redirect_url) {
        global $pagenow;
        global $wp;
        if ($redirect_url && $pagenow !== 'wp-login.php' && !is_user_logged_in()) {
          if($redirect_url != home_url( $wp->request ).'/'){
            $redirect_url = false;
          }
        }
        return $redirect_url;
      }

      public function remove_attachment_query_var( $vars ) {
        if ( ! empty( $vars['attachment'] ) ) {
          $vars['page'] = '';
          $vars['name'] = $vars['attachment'];
          unset( $vars['attachment'] );
        }
  
        return $vars;
      }

      public function change_attachment_link_to_file($url, $id)
      {
        $attachment_url = wp_get_attachment_url($id);
        if ($attachment_url) {
          return $attachment_url;
        }
        return $url;
      }

      public function redirect_attachment_pages_to_file()
      {
        if (is_attachment()) {
          $id = get_the_ID();
          $url = wp_get_attachment_url($id);
          if ($url) {
            wp_redirect($url, 301);
            die;
          }
        }
      }

      public function make_attachments_private($args, $slug)
      {
        if ($slug == 'attachment') {
          $args['public'] = false;
          $args['publicly_queryable'] = false;
        }
        return $args;
      }

      function generate_random_slug_on_upload($attachment_id)
      {
        $attachment = get_post($attachment_id);
        if ('attachment' === $attachment->post_type) {
          $random_slug = 'vnx-' . wp_generate_password(32, false); // Generate an 32-character random slug

          // Update the attachment's slug
          wp_update_post(
            array(
              'ID' => $attachment_id,
              'post_name' => $random_slug,
            )
          );
        }
      }

    }
    VNX_MediaTool::Instance();
  }
} catch (\Throwable $th) {
  echo "VNX media tool error";
}
?>