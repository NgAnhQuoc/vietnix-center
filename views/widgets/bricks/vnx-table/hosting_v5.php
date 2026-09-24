<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
  return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();

if (!isset($settings["cycle"]) || empty($settings["cycle"])) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Chưa nhập chu kỳ Cycle: ' . esc_html(__FILE__) . '</div>';
  return;
}

$cycle = array();
foreach ($settings["cycle"] as $key => $value) {
  $tab_data = array();
  if (isset($value['title']))
    array_push($tab_data, $value['title']);
  if (isset($value['sale']))
    array_push($tab_data, $value['sale']);
  if (isset($value['featured']))
    array_push($tab_data, $value['featured']);
  array_push($cycle, $tab_data);
}

$featured_tab = -1;

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
$header = $csv_data[0];
$cycle_data = array();

for ($i = 1; $i < count($csv_data); $i++) {
  foreach ($cycle as $index => $value) {
    if ($csv_data[$i][0] == $value[0]) {
      $cycle_data[$value[0]][] = $csv_data[$i];
    }
  }
}
$hide_col_list = isset($settings['hide_col_list']) ? explode(",", $settings['hide_col_list']) : array();
?>
<div class="w-full flex justify-center mb-10">
  <div class="grid-cols-2 gap-3 lg:gap-4 w-full lg:w-auto grid lg:flex lg:flex-row bg-white lg:py-2 px-2.5 lg:px-2 lg:rounded-md text-gray-600 font-bold lg:border">
    <?php
    foreach ($cycle as $index => $value) {
      $name = $value[0];
      $sale = $value[1];
      $featured = isset($value[2]) ? $value[2] : '';
      if ($featured_tab == -1 && $featured) {
        $featured_tab = $index;
      }
      if (!isset($cycle_data[$name])) {
        continue;
      }


    ?>
      <div class="border lg:border-0 lg:w-[8em] text-center cursor-pointer bg-white hover:bg-gray-200 py-1.5 lg:py-1 rounded-md leading-8 vnx-tab <?php echo ($index == $featured_tab) ? 'relative tab-active' : '' ?>" data-target="vnx-tab-<?php echo esc_attr($index); ?>">
        <?php
        echo esc_html($name);
        if ($sale) {
        ?>
          <span class="el-custom-tab-discount text-xs py-1 px-1 rounded-md">-
            <?php echo esc_html($sale) ?>
          </span>
        <?php
        }
        if ($index == $featured_tab) {
        ?>
          <img class="vnx-common-label w-full object-scale-down object-bottom bottom-full absolute hidden lg:block" src="https://vietnix.vn/wp-content/uploads/2021/06/recom.svg" alt="icon phổ biến" />
          <img class="vnx-common-label left-0 bottom-0 absolute lg:hidden" src="https://vietnix.vn/wp-content/uploads/2021/06/recoms.svg" alt="icon phổ biến" />
        <?php
        }
        ?>
      </div>
    <?php
    }
    ?>
  </div>
</div>

<div class="vnx-tabs-content bg-white">
  <?php
  foreach ($cycle as $index => $value) {
    $name = $value[0];
    $sale = $value[1];
    $featured = isset($value[2]) ? $value[2] : '';
    if ($featured_tab == -1 && $featured)
      $featured_tab = $index;
    $active = $index == $featured_tab ? 'is_active ' : '';
  ?>
    <div class="<?php echo $active; ?>vnx-tab-content px-2.5 lg:px-0 vnx-tab-<?php echo esc_attr($index); ?>">
      <?php
      // var_dump($cycle_data[$name]);
      if (isset($cycle_data[$name])) {
        // continue;
        View::render('widgets/bricks/vnx-table/hosting/' . $settings['table_style'] . '_desktop', ['header' => $header, 'row' => $cycle_data[$name]]);
        if(isset($settings['mobile_layout_for_shared_hosting'])){
          View::render('widgets/bricks/vnx-table/hosting/' . $settings['table_style'] . '_mobile_shared_hosting', ['header' => $header, 'row' => $cycle_data[$name]]);
        }
        else{
          View::render('widgets/bricks/vnx-table/hosting/' . $settings['table_style'] . '_mobile', ['header' => $header, 'row' => $cycle_data[$name]]);
        }
      }
      if ($settings['show_load_more'] == true) { ?>
        <div class="vnx_div_loadmore vnx_table_mobile w-full lg:hidden bg-white rounded-md py-6 gap-y-6  flex flex-col">
          <span class="vnx_title_button text-center font-bold">
            Nếu bạn chưa tìm thấy gói phù hợp
          </span>
          <button class="vnx_button_loadmore">
            <span>Xem thêm các gói khác</span>
          </button>
        </div>
      <?php } ?>
    </div>
  <?php
  }
  ?>
</div>