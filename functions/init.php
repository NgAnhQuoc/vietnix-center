<?php
if ( !defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

// require_once 'pagination.php';
// require_once 'search_domain.php';
// require_once 'ajax_themes_filter.php';
// require_once 'custom_posts_list.php';
// require_once 'whois_check.php';
// require_once 'get_custom_post_type.php';
// require_once 'media_tool.php';
// require_once 'breadcrumbs.php';

foreach (scandir(dirname(__FILE__).'/requires/') as $filename) {
  $path = dirname(__FILE__) . '/requires/' . $filename;
  if (is_file($path)) {
    require_once $path;
  }
}