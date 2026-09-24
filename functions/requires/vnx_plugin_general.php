<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

if (!function_exists('vnx_settings_apply_toggles_Center')) {
  /**
   * Cap nhat danh sach bat/tat nhung CHI dong vao cac key ma form nay hien thi.
   *
   * Option vnx_plugin_setting_* dung chung voi vietnix-plugin (ban chuan) - ben do co
   * the co key center khong biet (vd widget Elementor, addon moi them). Ghi de ca mang
   * tu $_POST se xoa mat cac key do, nen chi bat/tat dung $known_keys, con lai giu nguyen.
   */
  function vnx_settings_apply_toggles_Center(array $current, array $submitted, array $known_keys)
  {
    foreach ($known_keys as $key) {
      if (isset($submitted[$key])) {
        $current[$key] = $submitted[$key];
      } else {
        unset($current[$key]);
      }
    }

    return $current;
  }
}

if (!function_exists('vnx_keep_center_only_tools_Center')) {
  /**
   * Form Settings cua vietnix-plugin luu lai TOAN BO danh sach tool tu checkbox cua no,
   * ma ben do khong co tool rieng cua center -> moi lan luu o ben do se tat mat cac tool
   * nay. Khi ca 2 plugin cung active thi giu lai trang thai cu cua chung.
   */
  function vnx_keep_center_only_tools_Center($value, $old_value)
  {
    if (($_POST['action'] ?? '') !== 'vnx_plugin_general_submit' || !class_exists('RegisterVariables_Center')) {
      return $value;
    }

    $value = (array) $value;
    $old_value = (array) $old_value;
    foreach (RegisterVariables_Center::CENTER_ONLY_TOOLS as $key) {
      if (isset($old_value[$key]) && !isset($value[$key])) {
        $value[$key] = $old_value[$key];
      }
    }

    return $value;
  }
  add_filter('pre_update_option_vnx_plugin_setting_tools', 'vnx_keep_center_only_tools_Center', 10, 2);
}

if (!function_exists('vnx_plugin_general_submit_Center')) {
  add_action('admin_post_vnx_plugin_general_submit_Center', 'vnx_plugin_general_submit_Center');
  function vnx_plugin_general_submit_Center()
  {
    require_once VNX_PLUGIN_PATH_CENTER . 'register.php';

    // Check security nonce
    $security = ($_POST['vnx-general-nonce']) ?? '';
    $referrer = ($_POST['_wp_http_referer']) ? site_url($_POST['_wp_http_referer']) : wp_get_referer();
    $home_url = get_home_url();
    $parsed_home_url = wp_parse_url($home_url);

    $hash = '';

    if (current_user_can('manage_options') && wp_verify_nonce($security, 'vnx_plugin_general_security')) {
      $option_name = 'vnx_plugin_setting';
      $registry = new RegisterVariables_Center();

      $form = isset($_POST['vnx_settings_form']) ? sanitize_key($_POST['vnx_settings_form']) : '';

      switch ($form) {
        case 'widgets':
          $submitted = (array) ($_POST[$option_name . '_widgets'] ?? array());
          // Giu nguyen cac nhom khac (vd 'elementor' cua vietnix-plugin), chi cap nhat Bricks/Gutenberg.
          $widgets = (array) get_option($option_name . '_widgets', array());
          $frameworks = array(
            'bricks' => $registry->BricksRegisterWidget(),
            'gutenberg' => $registry->GutenbergRegisterWidget(),
          );
          foreach ($frameworks as $framework => $list) {
            $widgets[$framework] = vnx_settings_apply_toggles_Center(
              (array) ($widgets[$framework] ?? array()),
              (array) ($submitted[$framework] ?? array()),
              array_keys($list)
            );
          }
          update_option($option_name . '_widgets', $widgets);
          $widget_group = isset($_POST['vnx_active_widget_group']) ? sanitize_key($_POST['vnx_active_widget_group']) : 'bricks';
          $hash = '#tab=widget&subtab=vnx-widget-subtab-' . $widget_group;
          break;

        case 'extensions':
          $extentions = vnx_settings_apply_toggles_Center(
            RegisterVariables_Center::get_enabled($option_name, '_extentions'),
            (array) ($_POST[$option_name . '_extentions'] ?? array()),
            array_keys($registry->ExtentionsRegister())
          );
          update_option($option_name . '_extentions', $extentions);
          $hash = '#tab=extensions';
          break;

        case 'tools':
          $group = isset($_POST['vnx_tool_group']) ? sanitize_key($_POST['vnx_tool_group']) : '';
          $group_keys = array_keys(array_filter($registry->ToolsRegister(), function ($tool) use ($group) {
            return ($tool['group'] ?? '') === $group;
          }));
          $tools = vnx_settings_apply_toggles_Center(
            RegisterVariables_Center::get_enabled($option_name, '_tools'),
            (array) ($_POST[$option_name . '_tools'] ?? array()),
            $group_keys
          );
          update_option($option_name . '_tools', $tools);
          $hash = '#tab=tool' . ($group !== '' ? '&subtab=vnx-tool-group-subtab-' . $group : '');
          break;

        case 'options':
          $submitted = (array) ($_POST[$option_name . '_options'] ?? array('cookie_domain' => '.' . $parsed_home_url['host']));
          foreach (array('discord_webhook_trial', 'discord_webhook_callme', 'discord_webhook_post') as $webhook_key) {
            if (isset($submitted[$webhook_key])) {
              $submitted[$webhook_key] = esc_url_raw(trim(wp_unslash($submitted[$webhook_key])));
            }
          }
          $options = array_merge((array) get_option($option_name . '_options', array()), $submitted);
          update_option($option_name . '_options', $options);
          $hash = '#tab=options';
          break;
      }
    }

    $redirect_url = strtok(esc_url_raw($referrer), '#');
    wp_safe_redirect($redirect_url . $hash);
    exit;
  }
}
