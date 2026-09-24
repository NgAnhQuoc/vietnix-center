<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

/**
 * Thong bao sau khi submit form cua tool (admin-post -> redirect ve trang Tools).
 * Trang Tools render cung luc nhieu tool co thong bao (Portal API, UTM, Discord & Sheets):
 * dung chung mot transient thi view nao render truoc se lay mat thong bao cua tool khac,
 * nen moi tool co khoa rieng ($tool = key cua tool trong register.php).
 */
function vnx_tool_notice_key_Center($tool)
{
  return 'vnx_tool_notice_' . get_current_user_id() . ($tool !== '' ? '_' . md5($tool) : '');
}

function vnx_tool_notice_set_Center($message, $type = 'success', $tool = '')
{
  $key = vnx_tool_notice_key_Center($tool);
  set_transient($key, array('message' => $message, 'type' => $type), 30);
}

function vnx_tool_notice_consume_Center($tool = '')
{
  $key = vnx_tool_notice_key_Center($tool);
  $notice = get_transient($key);
  if ($notice === false) {
    return array('message' => '', 'type' => '');
  }

  delete_transient($key);

  return array(
    'message' => isset($notice['message']) ? (string) $notice['message'] : '',
    'type' => isset($notice['type']) ? (string) $notice['type'] : 'info',
  );
}
