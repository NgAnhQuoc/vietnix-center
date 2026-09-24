<?php

namespace VNX_API_Center;

use HelperCenter\VnxApiKeyCrypt;

class VietnixPluginAPI
{
  public function __construct()
  {
    // Assuming your API files are in the 'api' directory within your plugin
    $api_dir = plugin_dir_path(__FILE__);
    $this->load_api_files($api_dir);
    $this->register_routes();
  }

  private function load_api_files($dir)
  {
    // Resolve real path to prevent path traversal
    $real_base = realpath($dir);
    if ($real_base === false) {
      return;
    }

    $files = scandir($real_base);
    foreach ($files as $file) {
      if ($file === '.' || $file === '..') {
        continue;
      }
      $path = $real_base . DIRECTORY_SEPARATOR . $file;
      $real_path = realpath($path);

      // Ensure the resolved path is still within the base directory (no symlink escape)
      if (
        $real_path !== false &&
        strpos($real_path, $real_base) === 0 &&
        is_file($real_path) &&
        pathinfo($real_path, PATHINFO_EXTENSION) === 'php'
      ) {
        require_once $real_path;
      }
    }
  }

  public function register_routes()
  {
    // Get all declared classes
    $declared_classes = get_declared_classes();

    // Filter classes that belong to the VNX_API_Center namespace and start with 'VietnixAPI'
    // (ten class van giu nguyen nhu ban vietnix-plugin: VietnixAPI_Posts_Mkt, VietnixAPI_Category...)
    $api_classes = array_filter($declared_classes, function ($class) {
      return strpos($class, 'VNX_API_Center\\VietnixAPI') === 0;
    });

    // Instantiate and register routes for each API class
    foreach ($api_classes as $class) {
      $full_class_name = $class; // The class name already includes the namespace
      new $full_class_name();
    }
  }

  // Lấy API key đã giải mã từ wp_options (mã hóa bởi HelperCenter\VnxApiKeyCrypt)
  private static function get_db_api_key(): ?string
  {
    return VnxApiKeyCrypt::decrypt();
  }

  // Authentication method
  public static function authenticate($request)
  {
    // Step 1: Check IP whitelist first
    $allowed_ips = get_option('vnx_api_allow_ip', '');

    // --- DEBUG LOG ---
    $client_ip_log = self::get_client_ip();
     //error_log('[VNX_API] authenticate() called');
     //error_log('[VNX_API] vnx_api_allow_ip = ' . var_export($allowed_ips, true));
     error_log('[VNX_API] client IP         = ' . var_export($client_ip_log, true));
    // -----------------
    $allowed_ips_array = array_map('trim', explode(',', $allowed_ips));
    $client_ip = self::get_client_ip();

    $ip_allowed = !empty($allowed_ips) && in_array($client_ip, $allowed_ips_array);

    if (!$ip_allowed) {
      return new \WP_Error('rest_forbidden', 'Access denied: IP not allowed.', array('status' => 403));
    }

    // Step 2: IP is allowed — now check API key
    $api_key_header = $request->get_header('api-key');
    if (empty($api_key_header)) {
      return new \WP_Error('rest_forbidden', 'Access denied: Missing API key.', array('status' => 401));
    }

    $db_api_key = self::get_db_api_key();
    if ($db_api_key !== null && hash_equals($db_api_key, $api_key_header)) {
      return true;
    }

    return new \WP_Error('rest_forbidden', 'Access denied: Invalid API key.', array('status' => 401));
  }

  // Helper function to get client IP address
  // NOTE: Only use X-Forwarded-For if your server sits behind a TRUSTED reverse proxy.
  // Accepting this header blindly allows IP spoofing.
  private static function get_client_ip()
  {
    // Use REMOTE_ADDR as the primary source — this cannot be spoofed by the client
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // Only trust X-Forwarded-For when behind a known trusted proxy
    $trusted_proxies = defined('VNX_TRUSTED_PROXIES') ? VNX_TRUSTED_PROXIES : [];
    if (!empty($trusted_proxies) && in_array($ip, (array) $trusted_proxies)) {
      if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // X-Forwarded-For can contain a comma-separated chain; take the first (original client)
        $forwarded_ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        $ip = $forwarded_ips[0];
      }
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
  }
}