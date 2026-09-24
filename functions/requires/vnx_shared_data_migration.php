<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

if (!function_exists('vnx_migrate_shared_data_Center')) {
  /**
   * Truoc day center luu cau hinh vao key rieng (vnxc_plugin_setting_*, *_center...), nay
   * dung chung key voi vietnix-plugin (ban chuan). Chay 1 lan luc nap plugin - truoc khi
   * vnx_active.php nap tool - va chi lap cho trong: key chuan CHUA co thi mang du lieu cu
   * cua center sang, da co thi giu nguyen (ban chuan thang). Khong xoa key cu.
   */
  function vnx_migrate_shared_data_Center()
  {
    $version = 1;
    if ((int) get_option('vnx_center_shared_data_version', 0) >= $version) {
      return;
    }

    $pairs = array(
      'vnxc_plugin_setting_widgets' => 'vnx_plugin_setting_widgets',
      'vnxc_plugin_setting_tools' => 'vnx_plugin_setting_tools',
      'vnxc_plugin_setting_extentions' => 'vnx_plugin_setting_extentions',
      'vnxc_plugin_setting_options' => 'vnx_plugin_setting_options',
      'vnx_search_ai_center' => 'vnx_search_ai',
      'vnx_internal_link_ldp_setting_center' => 'vnx_internal_link_ldp_setting',
      'vnx_export_sitemap_setting_center' => 'vnx_export_sitemap_setting',
      'vnx_sync_telegram_sheet_setting_center' => 'vnx_sync_telegram_sheet_setting',
      'vnx_sitemap_settings_center' => 'vnx_sitemap_settings',
      'vnx_utm_tracker_center_cookies_age' => 'vnx_utm_tracker_cookies_age',
      'vnx_utm_tracker_center_seo_channel' => 'vnx_utm_tracker_seo_channel',
      'vnx_utm_tracker_center_social_channel' => 'vnx_utm_tracker_social_channel',
    );

    foreach ($pairs as $legacy => $shared) {
      $legacy_value = get_option($legacy, null);
      if ($legacy_value !== null && get_option($shared, null) === null) {
        add_option($shared, $legacy_value);
      }
    }

    // Tool chi co o center (vietnix-plugin khong co checkbox) thi key chuan khong the co san,
    // nen mang rieng trang thai bat tu ban cu sang du key chuan da ton tai.
    require_once VNX_PLUGIN_PATH_CENTER . 'register.php';
    $legacy_tools = (array) get_option('vnxc_plugin_setting_tools', array());
    $shared_tools = get_option('vnx_plugin_setting_tools', null);
    if (is_array($shared_tools)) {
      $changed = false;
      foreach (RegisterVariables_Center::CENTER_ONLY_TOOLS as $key) {
        if (isset($legacy_tools[$key]) && !isset($shared_tools[$key])) {
          $shared_tools[$key] = $legacy_tools[$key];
          $changed = true;
        }
      }
      if ($changed) {
        update_option('vnx_plugin_setting_tools', $shared_tools);
      }
    }

    // Lich rebuild sitemap rieng cua center: nay dung chung hook vnx_sitemap_rebuild_cache.
    wp_clear_scheduled_hook('vnx_sitemap_rebuild_cache_center');

    update_option('vnx_center_shared_data_version', $version);
  }

  vnx_migrate_shared_data_Center();
}
