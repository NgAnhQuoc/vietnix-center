<?php
require_once VNX_PLUGIN_PATH_CENTER . '/functions/requires/push_logs_error.php';

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}


if (!function_exists('vnx_sync_discord_sheet_submit_Center')) {
  add_action('admin_post_vnx_sync_discord_sheet_submit_Center', 'vnx_sync_discord_sheet_submit_Center');


  function vnx_sync_discord_sheet_submit_Center()
  {

    try {
      $referrer = ($_POST['_wp_http_referer']) ? site_url($_POST['_wp_http_referer']) : wp_get_referer();
      $security = $_POST['vnx-sync-discord-sheet-nonce'] ?? '';

      $sync_action = isset($_POST['submit_vnx_sync']) ? sanitize_key(wp_unslash($_POST['submit_vnx_sync'])) : '';

      if (
        wp_verify_nonce($security, 'vnx_sync_discord_sheet_security')
        && in_array($sync_action, array('save', 'delete'), true)
        && !empty($_POST['formName'])
      ) {
        $formName = sanitize_text_field(wp_unslash($_POST['formName']));

        $current_json = get_option('vnx_sync_telegram_sheet_setting');
        $current = $current_json ? (array) json_decode($current_json, true) : array();

        if ($sync_action === 'delete') {
          // Form duoc ghi nhan tu dong khi co nguoi gui (DataSyncTelegramSheet), xoa chi don
          // cau hinh cu; form con dung thi lan gui sau se xuat hien lai voi cau hinh trong.
          unset($current[$formName]);
          vnx_tool_notice_set_Center('Đã xoá form "' . $formName . '".', 'success', 'vietnix-sync-discord-sheet');
        } else {
          $current[$formName] = array(
            "referrer" => sanitize_text_field(wp_unslash($_POST['referrer'] ?? '')),
            // Khong con gui Telegram nhung giu nguyen roomID cu: option dung chung voi vietnix-plugin.
            "roomID" => $current[$formName]['roomID'] ?? '',
            "discordWebhook" => implode(',', array_filter(array_map(function ($url) {
              return esc_url_raw(trim($url));
            }, explode(',', convertDataInputTagify_Center(wp_unslash($_POST['discordWebhook'] ?? '')))))),
            "sheetID" => convertDataInputTagify_Center(wp_unslash($_POST['sheetID'] ?? '')),
            "pageName" => convertDataInputTagify_Center(wp_unslash($_POST['pageName'] ?? '')),
            "title" => convertDataInputTagify_Center(wp_unslash($_POST['title'] ?? '')),
            "content" => convertDataInputTagify_Center(wp_unslash($_POST['content'] ?? '')),
            "dataSheet" => convertDataInputTagify_Center(wp_unslash($_POST['dataSheet'] ?? '')),
          );
          vnx_tool_notice_set_Center('Đã lưu cài đặt cho form "' . $formName . '".', 'success', 'vietnix-sync-discord-sheet');
        }

        // Xoa het thi luu object rong ({}), khong phai mang ([]), giu dung dinh dang JSON cu.
        $convertedJson = $current ? wp_json_encode($current) : '{}';
        update_option('vnx_sync_telegram_sheet_setting', $convertedJson, "");

        // Luu xong mo lai dung form vua sua; xoa thi form khong con, ve form dau tien.
        $referrer = $sync_action === 'save'
          ? add_query_arg('vnx_sync_form', rawurlencode($formName), $referrer)
          : remove_query_arg('vnx_sync_form', $referrer);
      }

      wp_safe_redirect(
        esc_url_raw(
          $referrer
        )
      );
      exit;
    } catch (\Throwable $e) {
      $push_log = new Vnx_Push_Logger_Center();
      $push_log->vnxPushLogger($e->getMessage(), ['File' => __FILE__, 'Line' => $e->getLine()]);
      throw $e;
    }
  }
}

// convert data form 
//ex: [{"value":"css"},{"value":"html"},{"value":"Alice"}] -> css,html,Alice
function convertDataInputTagify_Center($value)
{

  if (empty($value)) {
    return $output = "";
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
    $output = implode(",", $output);
    return $output;
  }
}
