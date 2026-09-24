<?php
/**
 * VNX Validate Domain Handler
 * Manages domain validation settings for blocking specific domain patterns
 */

if (!defined('ABSPATH')) {
  exit;
}

class VNX_Validate_Domain_Center
{
  private static $instance = null;

  /**
   * Option names in database
   */
  const OPTION_BLACK_LIST = 'vnx_black_list';
  const OPTION_BLOCKED_PREFIXES = 'vnx_blocked_prefixes';

  /**
   * Default values for black list keywords
   */
  private $default_black_list = [
    'jx',
    'clmm',
    'cltx',
    'kubet',
    'kiemthe',
    'blox',
    'gunny',
    'volam',
    'lake',
    'clone',
    'ss2',
    'ss6',
    'ngocrong',
    'tft',
    'shopacc',
    'game',
    'ninja',
    'shaiya',
    'mine',
    'tibb',
    'hack',
    'lienquan',
    'dotkich',
    'silkroad',
    '88',
    'random'
  ];

  /**
   * Default values for blocked prefixes
   */
  private $default_blocked_prefixes = [
    'nso',
    'nro',
    'bet',
    'gun',
    'mu-',
    'sro'
  ];

  public static function instance()
  {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function __construct()
  {
    $this->activate();
  }

  /**
   * Initialize options when activating the tool
   * Creates the two option_name entries if they don't exist
   * 
   * @return bool
   */
  public function activate()
  {
    $black_list_exists = get_option(self::OPTION_BLACK_LIST);
    $blocked_prefixes_exists = get_option(self::OPTION_BLOCKED_PREFIXES);

    if (false === $black_list_exists) {
      add_option(self::OPTION_BLACK_LIST, $this->default_black_list);
    }

    if (false === $blocked_prefixes_exists) {
      add_option(self::OPTION_BLOCKED_PREFIXES, $this->default_blocked_prefixes);
    }

    return true;
  }

  /**
   * Get black list keywords
   * 
   * @return array
   */
  public function get_black_list()
  {
    $list = get_option(self::OPTION_BLACK_LIST, $this->default_black_list);
    return is_array($list) ? $list : $this->default_black_list;
  }

  /**
   * Get blocked prefixes
   * 
   * @return array
   */
  public function get_blocked_prefixes()
  {
    $prefixes = get_option(self::OPTION_BLOCKED_PREFIXES, $this->default_blocked_prefixes);
    return is_array($prefixes) ? $prefixes : $this->default_blocked_prefixes;
  }

  /**
   * Get black list as string (comma-separated)
   * 
   * @return string
   */
  public function get_black_list_string()
  {
    $list = $this->get_black_list();
    return empty($list) ? '' : implode(", ", $list);
  }

  /**
   * Get blocked prefixes as string (comma-separated)
   * 
   * @return string
   */
  public function get_blocked_prefixes_string()
  {
    $prefixes = $this->get_blocked_prefixes();
    return empty($prefixes) ? '' : implode(", ", $prefixes);
  }

  /**
   * Save settings from POST data
   * 
   * @param array $post_data $_POST data
   * @return array ['success' => bool, 'message' => string]
   */
  public function save_from_post($post_data)
  {
    $black_list = $this->parse_textarea_to_array($post_data['domain_list'] ?? '');
    $blocked_prefixes = $this->parse_textarea_to_array($post_data['blocked_prefixes'] ?? '');

    update_option(self::OPTION_BLACK_LIST, $black_list);
    update_option(self::OPTION_BLOCKED_PREFIXES, $blocked_prefixes);

    return [
      'success' => true,
      'message' => 'Cài đặt đã được lưu thành công!',
      'black_list' => $black_list,
      'blocked_prefixes' => $blocked_prefixes
    ];
  }

  /**
   * Parse textarea input to array (comma-separated values)
   * 
   * @param string $textarea_value
   * @return array
   */
  private function parse_textarea_to_array($textarea_value)
  {
    if (empty($textarea_value)) {
      return [];
    }

    $items_raw = explode(",", $textarea_value);
    $items = [];

    foreach ($items_raw as $item) {
      $item = trim($item);

      if (empty($item)) {
        continue;
      }

      $item = sanitize_text_field($item);
      $item = strtolower($item);

      if (!empty($item)) {
        $items[] = $item;
      }
    }

    return array_unique($items);
  }

  /**
   * Reset settings to defaults
   * 
   * @return array
   */
  public function reset_to_defaults()
  {
    update_option(self::OPTION_BLACK_LIST, $this->default_black_list);
    update_option(self::OPTION_BLOCKED_PREFIXES, $this->default_blocked_prefixes);

    return [
      'success' => true,
      'message' => 'Đã reset settings về mặc định!',
      'black_list' => $this->default_black_list,
      'blocked_prefixes' => $this->default_blocked_prefixes
    ];
  }

  /**
   * Get default values
   * 
   * @return array
   */
  public function get_defaults()
  {
    return [
      'black_list' => $this->default_black_list,
      'blocked_prefixes' => $this->default_blocked_prefixes
    ];
  }

  /**
   * Delete options when deactivating the tool
   * 
   * @return bool
   */
  public function deactivate()
  {
    delete_option(self::OPTION_BLACK_LIST);
    delete_option(self::OPTION_BLOCKED_PREFIXES);

    return true;
  }
}

new VNX_Validate_Domain_Center();