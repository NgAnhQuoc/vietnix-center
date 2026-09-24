<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
$settings = $data->settings;
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
// var_dump($csv_data);
?>
<!-- Desktop -->
<div class="w-full hidden lg:block el-custom-table-price-v2">
  <?php
  View::render('widgets/bricks/vnx-table/' . $settings['table_style'] . '_desktop', ['info' => $csv_data]);
  ?>
</div>
<!-- /Desktop -->

<!-- Mobile -->
<div class="w-full lg:hidden">
  <?php
  View::render('widgets/bricks/vnx-table/' . $settings['table_style'] . '_mobile', ['info' => $csv_data]);
  ?>
</div>
<!-- /Mobile -->