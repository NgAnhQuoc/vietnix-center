<?php

use HelperCenter\DiscordBot;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

if (!function_exists('vnx_discord_get_webhook_Center')) {
  /**
   * Doc webhook theo thu tu: option vnx_plugin_setting_options[$key] -> constant
   * VNX_DISCORD_WEBHOOK_* trong wp-config.php (vd. discord_webhook_trial -> VNX_DISCORD_WEBHOOK_TRIAL) -> rong.
   */
  function vnx_discord_get_webhook_Center($key)
  {
    $options = (array) get_option('vnx_plugin_setting_options', []);
    if (!empty($options[$key])) {
      return trim((string) $options[$key]);
    }

    $constant = 'VNX_' . strtoupper($key);
    if (defined($constant) && constant($constant)) {
      return trim((string) constant($constant));
    }

    return '';
  }
}

if (!function_exists('vnx_discord_parse_webhooks_Center')) {
  /**
   * Nhan chuoi (cach dau phay) hoac mang, tra ve danh sach URL webhook Discord hop le.
   */
  function vnx_discord_parse_webhooks_Center($webhooks)
  {
    if (!is_array($webhooks)) {
      $webhooks = explode(',', (string) $webhooks);
    }

    $valid = array();
    foreach ($webhooks as $url) {
      $url = trim((string) $url);
      if (preg_match('#^https://(?:(?:ptb|canary)\.)?discord(?:app)?\.com/api/webhooks/\d+/[\w-]+$#', $url)) {
        $valid[] = $url;
      }
    }

    return array_values(array_unique($valid));
  }
}

if (!function_exists('vnx_discord_escape_Center')) {
  /**
   * Escape markdown Discord cho du lieu khach nhap, tranh "**x**", "`", "> " lam vo format.
   * Mention da bi chan boi allowed_mentions trong DiscordBot, o day chen them zero-width
   * space sau '@' de "@everyone" hien thi nhu chu thuong.
   * URL de nguyen: Discord tu nhan link, chen "\" vao URL (vd. "dung\-thu") lam hong link.
   */
  function vnx_discord_escape_Center($text)
  {
    $parts = preg_split('#(https?://[^\s<>]+)#u', (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $i => $part) {
      // Phan tu le la URL (nhom bat duoc cua preg_split).
      if ($i % 2 === 0) {
        $parts[$i] = str_replace('@', "@\u{200B}", preg_replace('/([\\\\*_~`|>#\[\]()-])/u', '\\\\$1', $part));
      }
    }
    return implode('', $parts);
  }
}

if (!function_exists('vnx_discord_format_fields_Center')) {
  /**
   * array('Tên' => 'A', 'SĐT' => '09..') -> "**Tên:** A\n**SĐT:** 09.."; bo qua gia tri rong.
   */
  function vnx_discord_format_fields_Center(array $fields)
  {
    $lines = array();
    foreach ($fields as $label => $value) {
      $value = trim((string) $value);
      if ($value === '') {
        continue;
      }
      $lines[] = '**' . vnx_discord_escape_Center($label) . ':** ' . vnx_discord_escape_Center($value);
    }

    return implode("\n", $lines);
  }
}

if (!function_exists('vnx_discord_notify_Center')) {
  /**
   * Gui 1 embed toi 1 hoac nhieu webhook.
   *
   * @param string|array $webhooks  Chuoi cach dau phay hoac mang URL.
   * @param string       $title
   * @param string       $content   Da escape (dung vnx_discord_format_fields_Center cho du lieu khach nhap).
   * @param string       $color     Hex.
   * @return bool true neu gui thanh cong toi it nhat 1 webhook.
   */
  function vnx_discord_notify_Center($webhooks, $title, $content, $color = '00b0f4')
  {
    $webhooks = vnx_discord_parse_webhooks_Center($webhooks);
    if (!$webhooks) {
      error_log('[vietnix-center] Discord notify skipped: chua cau hinh webhook (' . $title . ')');
      return false;
    }

    $sent = false;
    foreach ($webhooks as $url) {
      // Timeout 5s: gui tuan tu trong request submit form, khong de Discord cham lam treo form.
      if (DiscordBot::sendMessageByWebhook($url, $title, $content, $color, 5)) {
        $sent = true;
      }
    }

    return $sent;
  }
}
