<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
$settings = $data->settings;
// check file csv upload thành công hay không
$get_csv = $data->get_upload_file_data();
if (!empty($data->settings["popular"])) {
  $popular = $data->settings["popular"];
}
if ($get_csv['status'] == 'error') {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>' . $get_csv['message'] . '</div>';
  return;
}
if ($get_csv['status'] == 'success' && !empty($get_csv['data']))
  $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
if (empty($csv_data)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html(__FILE__) . '</div>';
  return;
}

if (!function_exists('findIndexInObject_Center')) {
  function findIndexInObject_Center($obj, $searchValue)
  {
    foreach ($obj as $key => $value) {
      if (strtolower($value) === strtolower($searchValue)) {
        return $key;
      }
    }
    return null;
  }
}

//phần xử lý array
$result = array();
for ($i = 0; $i < count($csv_data[0]); $i++) {
  $row = array();
  for ($j = 0; $j < count($csv_data); $j++) {
    $row[] = $csv_data[$j][$i];
  }
  $result[] = $row;
}
if (!function_exists('vnxCutString_Center')) {
  function vnxCutString_Center($string)
  {
    $string_af = str_replace(['[', ']'], ['', ''], explode(" - ", $string));
    return $string_af;
  }
}
?>
<!-- Desktop -->
<div class="w-full hidden lg:flex gap-x-7">
  <?php
  View::render('widgets/bricks/vnx-table/hosting/' . $settings['table_style'] . '_desktop', ['info' => $csv_data, 'array' => $result]);
  ?>
</div>
<!-- /Desktop -->

<!-- Mobile -->
<div class="vnx_table_mobile w-full lg:hidden flex flex-col items-center gap-[68px] px-4">
  <?php
  View::render('widgets/bricks/vnx-table/hosting/' . $settings['table_style'] . '_mobile', ['info' => $csv_data, 'array' => $result]);
  ?>
</div>
<!-- /Mobile -->