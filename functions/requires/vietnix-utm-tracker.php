<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

if ( !function_exists( 'vnx_utm_tracker_submit_Center' ) ) {
  add_action( 'admin_post_vnx_utm_tracker_submit_Center', 'vnx_utm_tracker_submit_Center' );
  add_action( 'admin_post_nopriv_vnx_utm_tracker_submit_Center', 'vnx_utm_tracker_submit_Center' );
  function vnx_utm_tracker_submit_Center()
  {
    $security = ($_POST['vnx-utm_tracker-nonce']) ?? '';
    $referrer = ($_POST['_wp_http_referer']) ? site_url($_POST['_wp_http_referer']) : wp_get_referer();
    if( wp_verify_nonce( $security, 'vnx_utm_tracker_security' ) ) {
      $prefix = Vietnix_Utm_Tracker_Center__PREFIX;

      if (isset($_POST['form_action']) && $_POST['form_action'] ==	'update_cookies_age' && isset($_POST['cookies_age']) && $_POST['cookies_age'] != '') {
        update_option($prefix . 'cookies_age', $_POST['cookies_age']);
      }
  
      if (isset($_POST['form_action']) && $_POST['form_action'] ==	'update_channel' && isset($_POST['seo_channel'])) {
        $seo_channel = $_POST['seo_channel'];
        $seo_channel = br_bookmarks_tagify_json_to_array_Center($seo_channel);
        update_option($prefix . 'seo_channel', implode(',', $seo_channel));
      }
  
      if (isset($_POST['form_action']) && $_POST['form_action'] ==	'update_channel' && isset($_POST['social_channel'])) {
        $social_channel = $_POST['social_channel'];
        $social_channel = br_bookmarks_tagify_json_to_array_Center($social_channel);
        update_option($prefix . 'social_channel', implode(',', $social_channel));
      }

      vnx_tool_notice_set_Center('Đã lưu cài đặt.', 'success', 'vietnix-utm-tracker');
      wp_safe_redirect(
        esc_url_raw(
          $referrer
        )
      );
    }else{
      vnx_tool_notice_set_Center('Lỗi bảo mật! Vui lòng thử lại.', 'error', 'vietnix-utm-tracker');
      wp_safe_redirect(
        esc_url_raw(
          $referrer
        )
      );
    }
  }
}

function br_bookmarks_tagify_json_to_array_Center($value)
{
  if (empty($value)) {
    return $output = array();
  } else {
    $value = str_replace(array('[', ']'), '', $value);

    $value = str_replace('\"', "\"", $value);

    $value = explode(',', $value);

    $value_array = array();

    if (is_array($value) && 0 !== count($value)) {
      foreach ($value as $value_inner) {
        $value_array[] = json_decode($value_inner);
      }

      $value_array = json_decode(json_encode($value_array), true);

      $output = array();

      foreach ($value_array as $value_array_inner) {
        foreach ($value_array_inner as $key => $val) {
          $output[] = $val;
        }
      }
    }

    return $output;
  }
}