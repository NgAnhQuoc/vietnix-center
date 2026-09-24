<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
// Du phong: asset chinh da duoc nap tu admin_enqueue_scripts (Vietnix_plugin_Center::vnx_admin_style),
// goi lai o day chi la no-op neu handle da nam trong queue.
vietnix_plugin_enqueue_admin_style_Center();
include_once(VNX_PLUGIN_PATH_CENTER . 'register.php');

$RegisterVariables_Center = new RegisterVariables_Center();
$list_tools = $RegisterVariables_Center->ToolsRegister();
$list_extentions = $RegisterVariables_Center->ExtentionsRegister();

$option_name = 'vnx_plugin_setting';
$tools_enabled = RegisterVariables_Center::get_enabled($option_name, '_tools');
$extentions_enabled = RegisterVariables_Center::get_enabled($option_name, '_extentions');

/**
 * Chỉ giữ addon vừa đang bật vừa có trang thật để mở ra - hoặc file layout
 * trong views/tools (render panel tại chỗ), hoặc 'link' trỏ sang trang admin
 * khác (vd CPT riêng) - rồi gom theo group để dựng sidebar.
 */
$vnx_collect_addons = function (array $registry, array $enabled) {
  $collected = array();
  foreach ($registry as $key => $addon) {
    if (!isset($enabled[$key]) || !RegisterVariables_Center::has_tool_page($addon)) {
      continue;
    }
    // slug dùng cho id panel và deep link (#tool=...)
    $addon['slug'] = str_replace('_', '-', preg_replace('/^vietnix-/', '', $key));
    $collected[$key] = $addon;
  }

  return $collected;
};

$active_addons = $vnx_collect_addons($list_tools, $tools_enabled)
  + $vnx_collect_addons($list_extentions, $extentions_enabled);

$tool_groups = array_values(RegisterVariables_Center::group_by_key($active_addons));

$tools_total = count($active_addons);

if ($tools_total > 0) {
  include_once(VNX_PLUGIN_PATH_CENTER . 'tools_layout.php');
} else {
  include_once(VNX_PLUGIN_PATH_CENTER . 'tools_empty.php');
}
