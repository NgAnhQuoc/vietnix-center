<?php

use HelperCenter\View;

function webp_upload_mimes_Center($existing_mimes)
{
  $existing_mimes['webp'] = 'image/webp';
  $existing_mimes['svg'] = 'image/svg+xml';
  return $existing_mimes;
}

function webp_is_displayable_Center($result, $path)
{
  if ($result === false) {
    $displayable_image_types = array(IMAGETYPE_WEBP);
    $info = @getimagesize($path);

    if (empty($info)) {
      $result = false;
    } elseif (!in_array($info[2], $displayable_image_types)) {
      $result = false;
    } else {
      $result = true;
    }
  }

  return $result;
}

function svg_is_displayable_image_Center($result, $path)
{
  return pathinfo($path, PATHINFO_EXTENSION) == 'svg' || $result;
}


function add_title_faqs_in_post_Center($content)
{
  $replace = "class=\"rank-math-block\"><div class='vnx-rank-math-title'><h2><span class='vnx-rank-math-title-icon'></span>Câu hỏi thường gặp</h2></div>";
  $new_content = str_replace('class="rank-math-block">', $replace, $content);

  return $new_content;
}

function disable_admin_caching_Center()
{
  if (is_user_logged_in()) {
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
  }
}


function litespeed_cusstom_purge_cache_Center()
{
  try {
    if (is_plugin_active('litespeed-cache/litespeed-cache.php')) {
      $url = 'http://14.225.204.41:8081/v1/api/cache/purgeall';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_exec($ch);
      curl_close($ch);
      do_action('litespeed_purged_all_cssjs');
    }
  } catch (Exception $e) {
  }
}


function prefix_debug_redirection_Center($redirect_url = '', $requested_url = '')
{
  $redirect_post_id = url_to_postid($redirect_url);
  if (!$redirect_post_id > 0) {
    return '';
  }
  return $redirect_url;
}
// add_action('template_redirect', 'prevent_parent_post_access_Center');

function prevent_parent_post_access_Center()
{
  global $pagenow;
  if ($pagenow !== 'wp-login.php' && !is_user_logged_in()) {
    if (is_single()) {
      $post_id = get_queried_object_id();
      $post_url = get_permalink($post_id);
      $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      if ($current_url !== $post_url) {
        wp_redirect($post_url, 301);
      }
    }
  }
}
function wpse_force_template_Center($template)
{
  if (is_archive() && !is_author() && !is_category()) {
    vnx_style_Center();
    include_once('vnx-theme-page/archive-vnx.php');
    exit();
  } else if (is_search() && !is_page_template('elementor_template_search')) {
    vnx_style_Center();
    include_once('vnx-theme-page/search-vnx.php');
    exit();
  }
  return $template;
}

function vnx_style_Center()
{
  add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('vnx-general-style-center', VNX_PLUGIN_URL_CENTER . 'build/css/vnx-theme.css');
  });
}

function vnx_add_page_template_to_dropdown_Center($templates)
{
  $templates['templates/tin-tuc.php'] = __('Tin tức', 'vnx');
  $templates['templates/tai-lieu.php'] = __('Tài liệu', 'vnx');
  $templates['templates/policy.php'] = __('Policy', 'vnx');
  $templates['templates/fullwidth.php'] = __('Full Width', 'vnx');
  return $templates;
}

function vnx_change_page_template_Center($template)
{
  $list_templates = array(
    'templates/tin-tuc.php',
    'templates/tai-lieu.php',
    'templates/policy.php',
    'templates/fullwidth.php',
  );
  if (is_page_template($list_templates)) {
    $meta = get_post_meta(get_the_ID());
    vnx_style_Center();
    if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template) {
      if ($meta['_wp_page_template'][0] == 'templates/tin-tuc.php') {
        $template = plugin_dir_path(__FILE__) . 'vnx-theme-page/tin-tuc.php';
      } else if ($meta['_wp_page_template'][0] == 'templates/tai-lieu.php') {
        $template = plugin_dir_path(__FILE__) . 'vnx-theme-page/tai-lieu.php';
      } else if ($meta['_wp_page_template'][0] == 'templates/policy.php') {
        $template = plugin_dir_path(__FILE__) . 'vnx-theme-page/policy.php';
      } else if ($meta['_wp_page_template'][0] == 'templates/fullwidth.php') {
        $template = plugin_dir_path(__FILE__) . 'vnx-theme-page/fullwidth.php';
      } else {
        $template = $meta['_wp_page_template'][0];
      }
    }
  }

  return $template;
}

// vietnix-plugin co dung bo hook nay (cung ten, cung logic). Chay song song ma dang ky
// lai thi: tieu de FAQ bi chen 2 lan (str_replace van khop sau lan dau), goi API purge
// cache 2 lan, template archive/search/page cua center chiem truoc ban cua vietnix-plugin.
if (!vnx_center_companion_mode()) {
  add_filter('mime_types', 'webp_upload_mimes_Center');
  add_filter('file_is_displayable_image', 'webp_is_displayable_Center', 10, 2);
  add_filter('file_is_displayable_image', 'svg_is_displayable_image_Center', 10, 2);
  add_filter('the_content', 'add_title_faqs_in_post_Center');
  add_action('admin_init', 'disable_admin_caching_Center');
  add_action('litespeed_purged_all', 'litespeed_cusstom_purge_cache_Center');

  //remove redirect
  add_filter('do_redirect_guess_404_permalink', '__return_false');
  add_filter('redirect_canonical', 'prefix_debug_redirection_Center', 10, 2);

  add_filter('template_include', 'wpse_force_template_Center');
  add_filter('theme_page_templates', 'vnx_add_page_template_to_dropdown_Center');
  add_filter('template_include', 'vnx_change_page_template_Center', 99);
}
