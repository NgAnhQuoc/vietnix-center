<?php

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
include_once(VNX_PLUGIN_PATH_CENTER . 'register.php');

class VNX_Plugin_Active_Center
{
  private $option_name = 'vnx_plugin_setting';

  /**
   * Tool bi bo qua khi chay song song voi vietnix-plugin (xem vnx_center_companion_mode()):
   * chi dang ky hook chay ngam/ngoai site trung voi ban cua vietnix-plugin, khong co
   * giao dien admin rieng can class cua chinh file tool.
   * - media_tool: doi slug/redirect attachment (trang Media Tool dung AJAX *_center trong functions/requires)
   * - vietnix-api, vietnix-api-ldp, vietnix-api-price-table: REST vnx_api/* (cung route)
   * - vietnix-banner: post type vietnix_banner, shortcode [VIETNIX_BANNER], chen banner vao bai
   * - vietnix-logger-search: AJAX cong khai vnx_log_search_keyword + tao bang (vietnix-plugin da lam)
   */
  private $companion_skip_tools = array(
    'media_tool',
    'vietnix-api',
    'vietnix-api-ldp',
    'vietnix-api-price-table',
    'vietnix-banner',
    'vietnix-logger-search',
  );

  public function __construct()
  {
    // Chay song song voi vietnix-plugin: widget Bricks/Gutenberg va extension (form bot,
    // Discord, sitemap, an login, cot SEO/Writer...) deu trung ten/hook voi ban cua
    // vietnix-plugin nen de no chay, center khong dang ky lai.
    if (!vnx_center_companion_mode()) {
      $this->bricks_active_widget();
      $this->gutenberg_active_widget();
      $this->extentions_active();
    }
    $this->tools_active();
  }

  private function bricks_active_widget()
  {
    $theme_name = 'Bricks';
    if (isset(get_option($this->option_name . '_widgets', false)['bricks'])) {
      $bricks_widget_active = get_option($this->option_name . '_widgets')['bricks'];
      if (count($bricks_widget_active) > 0) {
        if ($this->are_theme_active($theme_name)) {
          add_action('init', function () use ($bricks_widget_active) {
            foreach ($bricks_widget_active as $index => $value) {
              if (file_exists(VNX_PLUGIN_PATH_CENTER . 'widgets/bricks/' . $index . '.php')) {
                \Bricks\Elements::register_element(VNX_PLUGIN_PATH_CENTER . 'widgets/bricks/' . $index . '.php');
              }
            }
          }, 11);
          $this->bricks_init();
        } else {
          add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>The plugin "Vietnix Plugin" has error because the required theme "Bricks" is not active. Widget Bricks of plugin can not using!</p></div>';
          });
        }
      }
    }
  }

  private function gutenberg_active_widget()
  {
    if (isset(get_option($this->option_name . '_widgets', false)['gutenberg'])) {
      $gutenberg_widget_active = get_option($this->option_name . '_widgets')['gutenberg'];
      if ($gutenberg_widget_active != array() && count($gutenberg_widget_active) > 0) {
        foreach ($gutenberg_widget_active as $index => $value) {
          if (file_exists(VNX_PLUGIN_PATH_CENTER . 'widgets/gutenberg/' . $index . '.php')) {
            require_once(VNX_PLUGIN_PATH_CENTER . 'widgets/gutenberg/' . $index . '.php');
          }
        }
      }
    }
  }

  private function extentions_active()
  {
    // Qua get_enabled de doi key cu (vd vnx_telegram_post_message) sang key moi: option chi duoc ghi lai
    // khi bam luu Settings, doc key tho thi file extension da doi ten se khong duoc nap.
    $extentions_active = RegisterVariables_Center::get_enabled($this->option_name, '_extentions');
    if (isset($extentions_active) && $extentions_active != null) {
      if (count($extentions_active) > 0) {
        foreach ($extentions_active as $index => $value) {
          if (file_exists(VNX_PLUGIN_PATH_CENTER . 'extentions/' . $index . '.php')) {
            require_once(VNX_PLUGIN_PATH_CENTER . 'extentions/' . $index . '.php');
          }
        }
      }
    }
  }

  private function tools_active()
  {
    $tools_active = RegisterVariables_Center::get_enabled($this->option_name, '_tools');
    if (isset($tools_active) && $tools_active != null) {
      if (count($tools_active) > 0) {
        foreach ($tools_active as $index => $value) {
          if (vnx_center_companion_mode() && in_array($index, $this->companion_skip_tools, true)) {
            continue;
          }
          if (file_exists(VNX_PLUGIN_PATH_CENTER . 'tools/' . $index . '.php')) {
            require_once(VNX_PLUGIN_PATH_CENTER . 'tools/' . $index . '.php');
          }
        }
      }
    }
  }

  private function are_theme_active($theme_name)
  {
    $theme = wp_get_theme();
    if ($theme_name == $theme->name || $theme_name == $theme->parent_theme) {
      return true;
    }
    return false;
  }

  private function bricks_init()
  {
    add_filter('bricks/builder/i18n', function ($i18n) {
      $i18n['vietnix'] = esc_html__('Vietnix', 'bricks');

      return $i18n;
    });
    add_action( 'wp_enqueue_scripts', function(){
      if(function_exists( 'is_use_bricks_Center' ) && is_use_bricks_Center() ){
        wp_enqueue_style('vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css');
        wp_enqueue_style( 'vnx-bricks-style-center', plugin_dir_url(__FILE__) . 'build/css/vnx-bricks.css', [], random_int(111, 9999));
      };
    }, 19 );
  }
}

new VNX_Plugin_Active_Center();
