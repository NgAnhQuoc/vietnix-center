<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

if (!function_exists('vnx_search_ai_dir_Center')) {
  /**
   * Thu muc goc chua file embeddings cua AI Search (storage/*.json, huongdan_embeddings_ai.json).
   *
   * Cac file nay nam TRONG thu muc plugin chu khong o DB, nen moi plugin mot ban rieng.
   * De center dung chung dung bo embeddings voi vietnix-plugin (ban chuan) - chuyen qua
   * lai khong phai tao lai 40-50 phut + ton phi OpenAI - thi uu tien thu muc cua
   * vietnix-plugin neu no con tren site (ke ca khi dang tat), khong thi dung cua center.
   */
  function vnx_search_ai_dir_Center()
  {
    static $dir = null;
    if ($dir !== null) {
      return $dir;
    }

    // __DIR__ thay vi VNX_PLUGIN_PATH_CENTER: crontab/updateSearchAI.php co the chay khi
    // chi vietnix-plugin dang active (hang so cua center chua duoc define).
    $own = __DIR__;
    $dir = $own;

    $plugin_dirs = array();
    // Ten thu muc cua vietnix-plugin khac nhau giua cac moi truong (vietnix-plugin,
    // vietnix-plugin-master...): lay tu active_plugins truoc, sau do moi doan theo ten.
    foreach ((array) get_option('active_plugins', array()) as $plugin) {
      if (basename($plugin) === 'vietnix-plugin.php') {
        $plugin_dirs[] = WP_PLUGIN_DIR . '/' . dirname($plugin);
      }
    }
    $plugin_dirs[] = WP_PLUGIN_DIR . '/vietnix-plugin';
    $plugin_dirs[] = WP_PLUGIN_DIR . '/vietnix-plugin-master';

    foreach ($plugin_dirs as $plugin_dir) {
      $shared = $plugin_dir . '/tools/inc/vietnix-search-ai';
      if (!is_file($plugin_dir . '/vietnix-plugin.php') || !is_dir($shared)) {
        continue;
      }
      // Center da co embeddings rieng ma ben chuan chua tung tao storage: giu ban dang co,
      // tranh AI Search cua center tu nhien trong rong.
      if (!is_dir($shared . '/storage') && is_dir($own . '/storage')) {
        break;
      }
      $dir = $shared;
      break;
    }

    return $dir;
  }
}
