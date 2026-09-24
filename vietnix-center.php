<?php

/**
 * Plugin Name: 	Vietnix Center
 * Description: 	Vietnix Center (giao dien moi cua Vietnix Plugin, dung chung du lieu voi Vietnix Plugin - xem README.md).
 * Author: 			Vietnix
 * Version: 		0.0.1
 */


use HelperCenter\VNXAutoLoader;
use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

// vietnix-center co class/ham/constant/namespace rieng (hau to "_Center"/"Center"),
// khong trung voi vietnix-plugin nen co the active dong thoi ca 2 ma khong fatal
// "Cannot redeclare class/function". Con DU LIEU (option, file embeddings, cron,
// REST/shortcode/AJAX cong khai...) thi dung dung key cua vietnix-plugin - ban chuan.
if (!defined('VNX_PLUGIN_PATH_CENTER')) {
  define('VNX_PLUGIN_PATH_CENTER', plugin_dir_path(__FILE__));
}
if (!defined('VNX_PLUGIN_URL_CENTER')) {
  define('VNX_PLUGIN_URL_CENTER', plugin_dir_url(__FILE__));
}
if (!defined('VNX_PLUGIN_SLUG_CENTER')) {
  define('VNX_PLUGIN_SLUG_CENTER', basename(rtrim(VNX_PLUGIN_PATH_CENTER, '/\\')));
}

if (!function_exists('define_if_not_defined_Center')) {
  function define_if_not_defined_Center($name, $value)
  {
    if (!defined($name)) {
      define($name, $value);
    }
  }
}

// Service account Google (gbot) cho Sheets: doc tu app/gbot-credentials.json (khong commit, xem .gitignore).
if (!function_exists('vnx_center_gbot_credentials')) {
  function vnx_center_gbot_credentials()
  {
    $path = VNX_PLUGIN_PATH_CENTER . 'app/gbot-credentials.json';
    $credentials = is_readable($path) ? json_decode(file_get_contents($path), true) : null;
    return is_array($credentials) ? $credentials : array();
  }
}

if (!function_exists('vnx_asset_version_Center')) {
  function vnx_asset_version_Center($relative_path)
  {
    $file = VNX_PLUGIN_PATH_CENTER . $relative_path;

    return file_exists($file) ? (string) filemtime($file) : null;
  }
}

if (!function_exists('vnx_bricks_form_captured_errors_Center')) {
  /**
   * Doc (hoac ghi, khi truyen $errors) loi validate da chot o priority 9.
   *
   * Co callback dang ky bang add_action tren filter 'bricks/form/validate' ma khong return
   * (vd snippet WPCode "Notify To Website (All)", ban cu cua vietnix-plugin) -> tra null, xoa
   * loi cua callback chay truoc no (vd domain_transfer_form_validate_Center o -10). Chot lai
   * o priority 9 de callback gui tin o cuoi van biet form co loi.
   */
  function vnx_bricks_form_captured_errors_Center($errors = null)
  {
    static $captured = array();
    if (func_num_args() > 0) {
      $captured = $errors;
    }
    return $captured;
  }

  add_filter('bricks/form/validate', function ($validation_errors) {
    vnx_bricks_form_captured_errors_Center($validation_errors);
    return $validation_errors;
  }, 9);
}

if (!function_exists('vnx_bricks_form_passes_validation_Center')) {
  /**
   * Form Bricks co qua duoc validate khong - dung cho callback gui Discord/Sheet.
   *
   * Cac callback nay gan vao filter 'bricks/form/validate', ma Bricks chay filter nay TRUOC
   * buoc kiem tra field bat buoc (validate_required_fields). Khong kiem tra o day thi form
   * thieu field / sai ma chuyen ten mien van gui tin, khach sua roi gui lai -> tin trung.
   */
  function vnx_bricks_form_passes_validation_Center($validation_errors, $form)
  {
    if (!empty($validation_errors) || !empty(vnx_bricks_form_captured_errors_Center())) {
      return false;
    }

    if (is_object($form) && method_exists($form, 'validate_required_fields')) {
      return empty($form->validate_required_fields());
    }

    return true;
  }
}

if (!function_exists('vnx_center_companion_mode')) {
  /**
   * Che do "song song": vietnix-plugin (ban chuan) dang active cung luc voi center.
   *
   * Hai plugin dung chung du lieu va dang ky cung ten element Bricks, block Gutenberg,
   * shortcode, REST, cron, filter the_content, form bot... Chay ca 2 thi moi thu ngoai
   * site/chay ngam bi lap (gui Telegram 2 lan, chen banner 2 lan...). Nen khi co
   * vietnix-plugin, center chi giu phan giao dien admin cua minh (Tools, Settings,
   * cac trang "(Vietnix Center)", Cache Scheduler) va nhuong phan con lai cho vietnix-plugin.
   * Tat vietnix-plugin thi center tu chay day du tro lai.
   *
   * Doc thang option active_plugins (khong dung is_plugin_active) vi center load TRUOC
   * vietnix-plugin theo thu tu ten thu muc, luc do chua co constant/class nao cua no.
   * Ten thu muc co the la "vietnix-plugin" hoac "vietnix-plugin-master" nen chi so ten file.
   */
  function vnx_center_companion_mode()
  {
    return vnx_center_companion_plugin_dir() !== '';
  }
}

if (!function_exists('vnx_center_companion_plugin_dir')) {
  /**
   * Ten thu muc cua vietnix-plugin dang active (vd. "vietnix-plugin"), '' neu khong active.
   */
  function vnx_center_companion_plugin_dir()
  {
    static $dir = null;
    if ($dir !== null) {
      return $dir;
    }

    $active = (array) get_option('active_plugins', array());
    if (is_multisite()) {
      $active = array_merge($active, array_keys((array) get_site_option('active_sitewide_plugins', array())));
    }

    $dir = '';
    foreach ($active as $plugin) {
      if (is_string($plugin) && substr($plugin, -strlen('/vietnix-plugin.php')) === '/vietnix-plugin.php') {
        $dir = dirname($plugin);
        break;
      }
    }

    return $dir;
  }
}

if (!function_exists('vnx_center_is_activating_companion')) {
  /**
   * Request hien tai dang kich hoat vietnix-plugin (plugins.php: activate, error_scrape,
   * bulk activate-selected; hoac WP-CLI "wp plugin activate vietnix-plugin").
   */
  function vnx_center_is_activating_companion()
  {
    $is_vnx_plugin = function ($plugin) {
      return is_string($plugin) && substr($plugin, -strlen('/vietnix-plugin.php')) === '/vietnix-plugin.php';
    };

    if (defined('WP_CLI') && WP_CLI) {
      $argv = isset($GLOBALS['argv']) ? (array) $GLOBALS['argv'] : array();
      return in_array('activate', $argv, true) && (bool) array_filter($argv, function ($arg) {
        return is_string($arg) && strpos($arg, 'vietnix-plugin') === 0;
      });
    }

    if (!is_admin()) {
      return false;
    }

    $action = isset($_REQUEST['action']) ? wp_unslash($_REQUEST['action']) : '';
    if ($action === '-1' && isset($_REQUEST['action2'])) {
      $action = wp_unslash($_REQUEST['action2']);
    }

    if (in_array($action, array('activate', 'error_scrape'), true)) {
      return $is_vnx_plugin(isset($_REQUEST['plugin']) ? wp_unslash($_REQUEST['plugin']) : '');
    }

    if ($action === 'activate-selected') {
      $checked = isset($_REQUEST['checked']) ? (array) wp_unslash($_REQUEST['checked']) : array();
      return (bool) array_filter($checked, $is_vnx_plugin);
    }

    return false;
  }
}

if (!function_exists('vnx_center_menu_title')) {
  /**
   * Ten menu rieng cua center. Chi them hau to "(Vietnix Center)" khi chay song song voi
   * vietnix-plugin - luc do sidebar co 2 menu cung ten (vd. 2 cai "Import Docs") can phan biet.
   */
  function vnx_center_menu_title($title)
  {
    return vnx_center_companion_mode() ? $title . ' (Vietnix Center)' : $title;
  }
}

if (!function_exists('vnx_center_is_own_admin_page')) {
  /**
   * Trang admin do chinh center dang ky: menu Vietnix Center (Tools), Settings va cac
   * trang rieng co slug hau to "_center". Khong so theo chu "vnx"/"vietnix" vi trang
   * cua vietnix-plugin (vnx_import_docs, vietnix_utm_tracking...) cung chua chu do.
   */
  function vnx_center_is_own_admin_page()
  {
    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    if ($page === '') {
      return false;
    }

    return $page === plugin_basename(VNX_PLUGIN_PATH_CENTER . 'vietnix-center.php')
      || $page === 'vnx-setting'
      || substr($page, -strlen('_center')) === '_center';
  }
}

if (!function_exists('vietnix_plugin_enqueue_admin_style_Center')) {
  function vietnix_plugin_enqueue_admin_style_Center()
  {
    wp_enqueue_style('vnx-general-style-center', VNX_PLUGIN_URL_CENTER . 'build/css/vnx-general.css', array(), vnx_asset_version_Center('build/css/vnx-general.css'));
    wp_enqueue_script('vnx-general-script-center', VNX_PLUGIN_URL_CENTER . 'build/js/admin.js', array(), vnx_asset_version_Center('build/js/admin.js'));
    wp_enqueue_style('vnx-fontawewsome-center', VNX_PLUGIN_URL_CENTER . 'build/css/vnx-fontawesome.min.css', array(), vnx_asset_version_Center('build/css/vnx-fontawesome.min.css'));
  }
}

define_if_not_defined_Center('VNX_WHMCS_API_LINK', 'https://portal.vietnix.vn/includes/api.php');
define_if_not_defined_Center('VNX_DOMAIN_CHECKING', 'https://whois.vietnix.vn/available');
define_if_not_defined_Center('VNX_WHOIS_LINK', 'https://guestapi.vietnix.vn/whois');
define_if_not_defined_Center('VNX_Plugin_version_CENTER', '0.0.1');
define_if_not_defined_Center('VNX_Api_Prefix_V1', 'vnx_api/v1');
define_if_not_defined_Center('VNX_Api_Prefix_Mkt', 'vnx_api/mkt');
// Secret (token...) nam o app/secrets.php, khong commit (xem .gitignore).
if (is_readable(VNX_PLUGIN_PATH_CENTER . 'app/secrets.php')) {
  require_once VNX_PLUGIN_PATH_CENTER . 'app/secrets.php';
}
define_if_not_defined_Center('VNX_Discord_Bot_Token', '');



if (!class_exists('Vietnix_plugin_Center')) {
class Vietnix_plugin_Center
{
  // Dung chung option voi vietnix-plugin (ban chuan): bat/tat o plugin nao cung la mot du lieu.
  private $option_name = 'vnx_plugin_setting';
  function __construct()
  {
    add_action('admin_menu', array($this, 'vnx_admin_menu'));
    add_action('admin_init', array($this, 'register_vietnix_plugin_settings'));
    if (!vnx_center_companion_mode()) {
      add_action('wp_enqueue_scripts', array($this, 'vnx_app_style'), 21);
    }
    add_action('admin_enqueue_scripts', array($this, 'vnx_admin_style'));
    if (vnx_center_companion_mode()) {
      add_action('admin_enqueue_scripts', array($this, 'vnx_dequeue_companion_assets'), PHP_INT_MAX);
    }
    add_action('in_admin_header', array($this, 'vnx_hide_foreign_notices'));
    require_once(VNX_PLUGIN_PATH_CENTER . '/app/AutoLoader.php');
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    require_once(VNX_PLUGIN_PATH_CENTER . '/functions/init.php');
    require_once(VNX_PLUGIN_PATH_CENTER . '/hooks_function.php');
    $this->autoloader();
    require_once(VNX_PLUGIN_PATH_CENTER . '/vnx_active.php');
    // Thay the han vietnix-plugin: giu ten ham/class cu cho code ngoai plugin (WPCode, Bricks).
    // Bo qua ca request dang kich hoat vietnix-plugin: luc do no chua co trong active_plugins nhung WP
    // se nap file cua no ngay sau center -> fatal "Cannot redeclare define_if_not_defined()".
    if (!vnx_center_companion_mode() && !vnx_center_is_activating_companion()) {
      require_once(VNX_PLUGIN_PATH_CENTER . '/functions/compat-vietnix-plugin.php');
    }
  }

  public function autoloader()
  {
    $loader = new VNXAutoLoader();
    $loader->register();
    $loader->addNamespace('HelperCenter', VNX_PLUGIN_PATH_CENTER . 'app');
    $loader->addNamespace('VNXToolCenter', VNX_PLUGIN_PATH_CENTER . 'tools');
    $loader->addNamespace('VNX_API_Center', VNX_PLUGIN_PATH_CENTER . 'api');
    View::$view_dir = VNX_PLUGIN_PATH_CENTER . 'views';
  }
  function vnx_admin_menu()
  {
    $icon_base64 = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMyIgaGVpZ2h0PSIzNSIgdmlld0JveD0iMCAwIDMzIDM1IiBmaWxsPSJub25lIj4KPHBhdGggZD0iTTMwLjQwODYgMTQuNjkwOUMzMC40MDg2IDE0LjU5NTIgMzAuNDI1NiAxNC41MDQzIDMwLjQ0ODkgMTQuNDE2MUwxOS4zMjIgMjAuODAzNkMxOC43ODgxIDIxLjExMjYgMTguMjIzNSAyMS4zNDQzIDE3LjY0MzggMjEuNTAyOVYyMy4xMjAzQzE4LjUwNTEgMjIuOTI4OSAxOS4zMzY0IDIyLjYwOSAyMC4xMDQxIDIyLjE2NTNMMzEuMjk5MyAxNS43Mzc1QzMwLjc5NDEgMTUuNjU0OCAzMC40MDg2IDE1LjIxOTMgMzAuNDA4NiAxNC42OTA5WiIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBkPSJNMzAuNDA4NSAyMC41NTg0QzMwLjQwODUgMjAuNDQ5NyAzMC40Mjk3IDIwLjM0NjUgMzAuNDU5OCAyMC4yNDc0TDE5LjMyMiAyNi42NDE3QzE4Ljc4ODEgMjYuOTUwNyAxOC4yMjM1IDI3LjE4MjUgMTcuNjQzOCAyNy4zNDExVjI4Ljk1ODRDMTguNTA1MSAyOC43NjcgMTkuMzM2NCAyOC40NDcxIDIwLjEwNDEgMjguMDAzNUwzMS4yNTYyIDIxLjYwMDJDMzAuNzcyMiAyMS41MDA0IDMwLjQwODUgMjEuMDcxOCAzMC40MDg1IDIwLjU1ODRaIiBmaWxsPSIjZmZmZmZmIi8+CjxwYXRoIGQ9Ik0zMC40MDg1IDI2LjQyNkMzMC40MDg1IDI2LjMwMzYgMzAuNDMzMSAyNi4xODgxIDMwLjQ3MDcgMjYuMDc4N0wxOS4zMjIgMzIuNDc5OUMxOC43ODgxIDMyLjc4ODkgMTguMjIzNSAzMy4wMjA2IDE3LjY0MzggMzMuMTc5MlYzNC43OTY2QzE4LjUwNTEgMzQuNjA1MiAxOS4zMzY0IDM0LjI4NTIgMjAuMTA0MSAzMy44NDE2TDMxLjIyMjcgMjcuNDU3NUMzMC43NTU4IDI3LjM0NTQgMzAuNDA4NSAyNi45MjcgMzAuNDA4NSAyNi40MjZaIiBmaWxsPSIjZmZmZmZmIi8+CjxwYXRoIGQ9Ik0xNS43NjIzIDI2LjAyNTdDMTQuNzg5NiAyNi4wMjU3IDEzLjgyOTggMjUuNzY4NyAxMi45ODI4IDI1LjI3OTlMMCAxNy44Mzk3VjIxLjQ1OTNMMTEuNDE2NyAyOC4wMDI3QzEyLjczNiAyOC43NjM1IDE0LjIzODYgMjkuMTY2MSAxNS43NjIzIDI5LjE2NjFDMTUuODQ3OCAyOS4xNjYxIDE1LjkzMzIgMjkuMTY0MSAxNi4wMTg3IDI5LjE2MlYyNi4wMTgyQzE1LjkzMzIgMjYuMDIwOSAxNS44NDc4IDI2LjAyNTcgMTUuNzYyMyAyNi4wMjU3WiIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBkPSJNMTUuNzYyMyAyMC4xODc2QzE0Ljc4OTYgMjAuMTg3NiAxMy44Mjk4IDE5LjkzMDYgMTIuOTgyOCAxOS40NDE4TDAgMTIuMDAxNlYxNS42MjA1TDExLjQxNjcgMjIuMTYzOUMxMi43MzYgMjIuOTI1NCAxNC4yMzg2IDIzLjMyODEgMTUuNzYyMyAyMy4zMjgxQzE1Ljg0NzggMjMuMzI4MSAxNS45MzMyIDIzLjMyNiAxNi4wMTg3IDIzLjMyNFYyMC4xODAxQzE1LjkzMzIgMjAuMTgzNSAxNS44NDc4IDIwLjE4NzYgMTUuNzYyMyAyMC4xODc2WiIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBkPSJNMTkuMzIyNSAxNS4wNjcyQzE4LjI5NzggMTUuNjU4NSAxNy4xNjMgMTUuOTc1IDE2LjAyMDEgMTYuMDE2QzE0Ljg3NzEgMTUuOTc0MyAxMy43NDE3IDE1LjY1ODUgMTIuNzE3IDE1LjA2NzJMMS43ODk3MSA4Ljc5Mzg1TDEyLjcxNyAyLjUxOTgyQzEzLjc0MTcgMS45Mjc4MyAxNC44NzcxIDEuNjEyMDEgMTYuMDE5NCAxLjU3MUMxNy4xNjI0IDEuNjEyMDEgMTguMjk3OCAxLjkyODUyIDE5LjMyMjUgMi41MTk4MkwyOC42MTk0IDcuODU3MzJMMzAuMTk1OCA2Ljk1MjI1TDIwLjEwMzkgMS4xNTgxMUMxOC44NjI1IDAuNDQxMDE2IDE3LjQ1NDkgMC4wNDMxNjQ4IDE2LjAyMDEgOS44NDA3M2UtMDVDMTYuMDE5NCA5Ljg0MDczZS0wNSAxNi4wMTk0IDkuODQwNzNlLTA1IDE2LjAxODcgOS44NDA3M2UtMDVDMTYuMDE4IDkuODQwNzNlLTA1IDE2LjAxOCA5Ljg0MDczZS0wNSAxNi4wMTczIDkuODQwNzNlLTA1QzE0LjU4MzIgMC4wNDMxNjQ4IDEzLjE3NTYgMC40NDE2OTkgMTEuOTMzNiAxLjE1ODExTDAuMjI1NjQ3IDcuODgxMjVWNy44OTU2MVY5LjY5MTQxVjkuNzA1NzZMMTEuOTM0OSAxNi40Mjg5QzEzLjE3NjMgMTcuMTQ2IDE0LjU4MzkgMTcuNTQzOCAxNi4wMTggMTcuNTg2OUgxNi4wMTk0SDE2LjAyMDFDMTcuNDU0OSAxNy41NDM4IDE4Ljg2MjUgMTcuMTQ2IDIwLjEwMzkgMTYuNDI4OUwzMS44MTMxIDkuNzA1NzZWNy44OTU2MUwxOS4zMjI1IDE1LjA2NzJaIiBmaWxsPSIjZmZmZmZmIi8+CjxwYXRoIGQ9Ik0xNS43NjIzIDMxLjg1OTZDMTQuNzg5NiAzMS44NTk2IDEzLjgyOTggMzEuNjAyNSAxMi45ODI4IDMxLjExMzhMMCAyMy42NzM1VjI3LjI5MjVMMTEuNDE2NyAzMy44MzU4QzEyLjczNiAzNC41OTc0IDE0LjIzODYgMzUgMTUuNzYyMyAzNUMxNS44NDc4IDM1IDE1LjkzMzIgMzQuOTk3OSAxNi4wMTg3IDM0Ljk5NTlWMzEuODUyMUMxNS45MzMyIDMxLjg1NDggMTUuODQ3OCAzMS44NTk2IDE1Ljc2MjMgMzEuODU5NloiIGZpbGw9IiNmZmZmZmYiLz4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0zMC4xOTYgMjAuNTU4NEMzMC4xOTYgMTkuODUzNyAzMC43Njc1IDE5LjI4MjIgMzEuNDcyMyAxOS4yODIyQzMyLjE3NzEgMTkuMjgyMiAzMi43NDg2IDE5Ljg1MzcgMzIuNzQ4NiAyMC41NTg0QzMyLjc0OTMgMjEuMjYzOSAzMi4xNzc4IDIxLjgzNTQgMzEuNDcyMyAyMS44MzU0QzMwLjc2NjggMjEuODM1NCAzMC4xOTYgMjEuMjYzOSAzMC4xOTYgMjAuNTU4NFpNMzIuMDcxMSAyMC41NTg0QzMyLjA3MTEgMjAuMjI4MyAzMS44MDI1IDE5Ljk1OTYgMzEuNDcyMyAxOS45NTk2QzMxLjE0MjEgMTkuOTU5NiAzMC44NzM1IDIwLjIyODMgMzAuODczNSAyMC41NTg0QzMwLjg3MzUgMjAuODg5MyAzMS4xNDIxIDIxLjE1NzkgMzEuNDcyMyAyMS4xNTc5QzMxLjgwMjUgMjEuMTU3MyAzMi4wNzExIDIwLjg4ODYgMzIuMDcxMSAyMC41NTg0WiIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTMwLjE5NiAyNi40MjU4QzMwLjE5NiAyNS43MjEgMzAuNzY3NSAyNS4xNDk1IDMxLjQ3MjMgMjUuMTQ5NUMzMi4xNzcxIDI1LjE0OTUgMzIuNzQ4NiAyNS43MjEgMzIuNzQ4NiAyNi40MjU4QzMyLjc0OTMgMjcuMTMxMyAzMi4xNzc4IDI3LjcwMjggMzEuNDcyMyAyNy43MDI4QzMwLjc2NjggMjcuNzAyOCAzMC4xOTYgMjcuMTMxMyAzMC4xOTYgMjYuNDI1OFpNMzIuMDcxMSAyNi40MjU4QzMyLjA3MTEgMjYuMDk1NiAzMS44MDI1IDI1LjgyNyAzMS40NzIzIDI1LjgyN0MzMS4xNDIxIDI1LjgyNyAzMC44NzM1IDI2LjA5NTYgMzAuODczNSAyNi40MjU4QzMwLjg3MzUgMjYuNzU2NyAzMS4xNDIxIDI3LjAyNDYgMzEuNDcyMyAyNy4wMjQ2QzMxLjgwMjUgMjcuMDI0NiAzMi4wNzExIDI2Ljc1NjcgMzIuMDcxMSAyNi40MjU4WiIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTMwLjE5NiAxNC42OTExQzMwLjE5NiAxMy45ODY0IDMwLjc2NzUgMTMuNDE0MiAzMS40NzIzIDEzLjQxNDJDMzIuMTc3MSAxMy40MTQyIDMyLjc0ODYgMTMuOTg1NyAzMi43NDg2IDE0LjY5MTFDMzIuNzQ4NiAxNS4zOTY2IDMyLjE3NzggMTUuOTY3NCAzMS40NzIzIDE1Ljk2NzRDMzAuNzY2OCAxNS45Njc0IDMwLjE5NiAxNS4zOTU5IDMwLjE5NiAxNC42OTExWk0zMi4wNzExIDE0LjY5MTFDMzIuMDcxMSAxNC4zNjAzIDMxLjgwMjUgMTQuMDkxNiAzMS40NzIzIDE0LjA5MTZDMzEuMTQyMSAxNC4wOTE2IDMwLjg3MzUgMTQuMzYwMyAzMC44NzM1IDE0LjY5MTFDMzAuODczNSAxNS4wMjEzIDMxLjE0MjEgMTUuMjkgMzEuNDcyMyAxNS4yOUMzMS44MDI1IDE1LjI5IDMyLjA3MTEgMTUuMDIxMyAzMi4wNzExIDE0LjY5MTFaIiBmaWxsPSIjZmZmZmZmIi8+Cjwvc3ZnPg==';
    $icon_data_uri = 'data:image/svg+xml;base64,' . $icon_base64;
    add_menu_page(
      'Vietnix Center',
      'Vietnix Center',
      'activate_plugins',
      __FILE__,
      array($this, 'tools'),
      $icon_data_uri,
      4
    );

    // Setting đăng ký sau cùng để luôn nằm cuối menu
    add_submenu_page(
      __FILE__,
      'Vietnix Settings',
      'Settings',
      'activate_plugins',
      'vnx-setting',
      array($this, 'general')
    );

    // Submenu đầu tiên (trùng menu cha) đổi tên thành "Tools"
    global $submenu;
    $parent_slug = plugin_basename(__FILE__);
    if (isset($submenu[$parent_slug][0][0])) {
      $submenu[$parent_slug][0][0] = 'Tools';
    }
  }

  public function register_vietnix_plugin_settings()
  {
    $home_url = get_home_url();
    $parsed_home_url = wp_parse_url($home_url);
    register_setting('vietnix-plugin', $this->option_name . '_widgets');
    add_option($this->option_name . '_widgets', array());
    register_setting('vietnix-plugin', $this->option_name . '_tools');
    add_option($this->option_name . '_tools', array());
    register_setting('vietnix-plugin', $this->option_name . '_extentions');
    add_option($this->option_name . '_extentions', array());
    register_setting('vietnix-plugin', $this->option_name . '_options');
    add_option($this->option_name . '_options', array(
      'cookie_domain' => '.' . $parsed_home_url['host']
    ));
  }
  function general()
  {
    echo '<div class="wrap">';
    require(VNX_PLUGIN_PATH_CENTER . 'general.php');
    echo '</div>';
  }

  function tools()
  {
    echo '<div class="wrap">';
    require(VNX_PLUGIN_PATH_CENTER . 'tool_page.php');
    echo '</div>';
  }

  function vnx_admin_style()
  {
    if (!$this->vnx_is_own_admin_page()) {
      return;
    }

    vietnix_plugin_enqueue_admin_style_Center();
  }

  /**
   * vietnix-plugin nap JS/CSS tool cua no (Vue import-docs, filter-posts...) o MOI trang admin.
   * Tren trang cua center khong co giao dien nao cua no, nhung Vue 2 khong thay el van chay
   * mounted() -> goi AJAX cua vietnix-plugin, JQMIGRATE bao jqXHR.success... Go het asset
   * cua vietnix-plugin khoi trang cua center (khong dung trang nao khac cua no).
   */
  function vnx_dequeue_companion_assets()
  {
    if (!$this->vnx_is_own_admin_page()) {
      return;
    }

    $needle = '/plugins/' . vnx_center_companion_plugin_dir() . '/';
    foreach (array(wp_scripts(), wp_styles()) as $registry) {
      foreach ($registry->queue as $handle) {
        $src = isset($registry->registered[$handle]) ? (string) $registry->registered[$handle]->src : '';
        if ($src !== '' && strpos($src, $needle) !== false) {
          $registry->dequeue($handle);
        }
      }
    }
  }

  // Nap admin.js/CSS cua center hay go notice tren trang cua vietnix-plugin se khoi tao
  // Tagify/Preline lan 2 o do va an mat notice cua no, nen chi nhan dung trang cua center.
  private function vnx_is_own_admin_page()
  {
    return vnx_center_is_own_admin_page();
  }

  /**
   * Trang Tools/Settings dung JS chuan cua WP (xem .wp-header-end o
   * views/tools/partials/tool_page_header.php) de dat admin notice ngay duoi
   * header cua minh. Nhung selector do la GLOBAL - bat ky notice nao cua plugin
   * khac (vd. "Action Scheduler: 2 past-due actions...") cung bi keo vao trong
   * khung UI cua Vietnix, nhin nhu loi cua minh. Chi giu lai notice do chinh
   * code trong thu muc plugin nay dang ky, con lai go bo tren trang cua minh.
   */
  function vnx_hide_foreign_notices()
  {
    if (!$this->vnx_is_own_admin_page()) {
      return;
    }

    foreach (array('admin_notices', 'all_admin_notices') as $hook) {
      $this->vnx_strip_foreign_callbacks($hook);
    }
  }

  private function vnx_strip_foreign_callbacks($hook)
  {
    global $wp_filter;

    if (empty($wp_filter[$hook])) {
      return;
    }

    foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
      foreach ($callbacks as $registered) {
        if (!$this->vnx_is_own_callback($registered['function'])) {
          remove_action($hook, $registered['function'], $priority);
        }
      }
    }
  }

  private function vnx_is_own_callback($callback)
  {
    try {
      if (is_array($callback)) {
        $reflection = new ReflectionMethod($callback[0], $callback[1]);
      } elseif ($callback instanceof Closure || is_string($callback)) {
        $reflection = new ReflectionFunction($callback);
      } else {
        return false;
      }
    } catch (\ReflectionException $e) {
      // Khong soi duoc nguon goc (vd. callback noi bo cua PHP) thi coi nhu khong
      // phai cua minh, an di cho an toan thay vi lo lam mat notice quan trong.
      return false;
    }

    $file = $reflection->getFileName();
    $plugin_path = realpath(VNX_PLUGIN_PATH_CENTER);

    return $file && $plugin_path && strpos($file, $plugin_path) === 0;
  }

  function vnx_app_style()
  {
    wp_enqueue_style('vnx-app-style-center', VNX_PLUGIN_URL_CENTER . 'build/css/app.css', array(), vnx_asset_version_Center('build/css/app.css'));
    wp_enqueue_style('vnx-fontawewsome-center', VNX_PLUGIN_URL_CENTER . 'build/css/vnx-fontawesome.min.css', array(), vnx_asset_version_Center('build/css/vnx-fontawesome.min.css'));
    wp_enqueue_script('vnx-app-script-center', VNX_PLUGIN_URL_CENTER . 'build/js/vnx-app.js', ['jquery'], vnx_asset_version_Center('build/js/vnx-app.js'), true);
    if (function_exists('is_use_bricks_Center') && !is_use_bricks_Center()) {
      if (has_block('vnx/shortcode-widget-block')) {
        wp_enqueue_style('vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css', array(), vnx_asset_version_Center('build/css/brickstailwindBase.css'));
      }
    }
    if (is_singular('post')) {
      wp_enqueue_style('vnx-single-post-center', VNX_PLUGIN_URL_CENTER . 'build/css/single_post_v2.css', array(), vnx_asset_version_Center('build/css/single_post_v2.css'));
    } elseif (is_single()) {
      wp_enqueue_style('vnx-single-post-center', VNX_PLUGIN_URL_CENTER . 'build/css/single_post.css', array(), vnx_asset_version_Center('build/css/single_post.css'));
    }
    wp_localize_script(
      'vnx-app-script-center',
      'vnx_app_array',
      array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'cookie_domain' => get_option($this->option_name . '_options')['cookie_domain'],
      )
    );
  }
}
}

new Vietnix_plugin_Center();
