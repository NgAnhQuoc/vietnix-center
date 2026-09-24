<?php
$data = isset($data) ? $data : new stdClass();
if (!isset($data->settings)) {
  if (current_user_can('update_core')){
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
  }
  return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();

if ($get_csv['status'] == 'error') {
  if (current_user_can('update_core'))
  {
    echo '<div class="vnx_error no_data"><b>' . $get_csv['message'] . '</div>';
  }
  return;
}
if ($get_csv['status'] == 'success' && !empty($get_csv['data'])){
  $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
}

if (empty($csv_data)) {
  if (current_user_can('update_core'))
  {
    echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html(__FILE__) . '</div>';
  }
  return;
}
$result = array();
for ($i = 0; $i < count($csv_data[0]); $i++) {
  $row = array();
  for ($j = 0; $j < count($csv_data); $j++) {
    $row[] = $csv_data[$j][$i];
  }
  $result[] = $row;
}
// Sử dụng hàm để tìm các cặp chuỗi bắt đầu và kết thúc
if (is_array($result[0])) {
  $boundaries = $data->findCycleBoundaries($result[0]);
}
?>
<div class="vnx_service_header w-full swiper_service_multiple">
  <div class="swiper-wrapper vnx_tab_list">
    <?php
    if (!empty($boundaries)) {
      $i = 1;
      foreach ($boundaries as $index => $tab_title) {
        $arr_title = explode(" | ", $result[0][$tab_title[0]]);
        if (is_array($arr_title)) {
          $target = 'vnx_tab_' . $i;
          $active = $i == '1' ? 'tab_active' : '';
    ?>
          <div class="swiper-slide flex justify-center vnx_tab_title cursor-pointer"><?php echo $arr_title[0]; ?></div>
    <?php
        }
        $i++;
      }
    }
    ?>
  </div>
  <div class="vnx-swiper-button">
    <button id="swiper-button-prev" class="swiper_button swiper-button-prev"><i class="fa-solid fa-angle-left"></i></button>
    <button id="swiper-button-next" class="swiper_button swiper-button-next"><i class="fa-solid fa-angle-right"></i></button>
  </div>
</div>
<div class="vnx_service_body w-full swiper_service_multiple">
  <div class="swiper-wrapper">
    <?php
    if (!empty($boundaries)) {
      $i = 1;
      foreach ($boundaries as $index => $tab_content) {
        $arr_body = explode(" | ", $result[0][$tab_content[0]]);
        $target = 'vnx_tab_' . $i;
        $active = $i == '1' ? 'tab_active' : '';
    ?>
        <div class="swiper-slide vnx_tab_body py-6 px-4 <?php echo $target; ?> " >
          <div class="w-full grid grid-cols-1 gap-y-3">
            <?php
            $item = $tab_content[0] + 1;
            for ($item; $item < $tab_content[1]; $item++) {
              $package = $csv_data[$item];
              $the_last_row = count($package) - 1;
            ?>
              <div class="vnx_package flex flex-col items-start border border-[#38A7FF] rounded-lg p-3 gap-y-1">
                <p class="text-[#164366] text-base font-bold"><?php echo $package[0]; ?></p>
                <div class="vnx_package_list gap-y-1 flex flex-col items-start border-b border-dashed pb-2 mb-2 w-full">
                  <?php
                  foreach ($package as $index => $packages) {
                    if ($index != 0  && $index < $the_last_row) {
                      $titles = explode(" | ", $csv_data[0][$index]);
                      $data_infor = explode(" + ", $packages);
                      if (is_array($titles)) {
                  ?>
                        <div class="vnx_package_infor flex gap-[8px] items-center">
                          <img src="<?php echo $titles[1]; ?>" alt="CPU" class="img">
                          <span class="title text-sm"><?php echo $titles[0]; ?>:</span>
                          <div class="flex gap-1">
                            <?php if (count($data_infor) == 2) { ?>
                              <p class="title text-sm"><?php echo $data_infor[0]; ?><span class="title text-sm text-[#FF9038]"> + <?php echo $data_infor[1]; ?></span></p>
                              
                            <?php } else { ?>
                              <p class="title text-sm"><?php echo $packages; ?></p>
                            <?php } ?>
                          </div>
                        </div>
                  <?php
                      }
                    }
                  }
                  ?>
                </div>
                <div class="vnx_package_price flex justify-between w-full">
                  <div class="vnx_price_infor flex flex-row gap-[4px] items-center">
                    <p class="vnx_price_number text-xl"><?php echo $package[5]; ?></p>
                    <p class="text-sm text-[#B3B3B3]">/ Tháng</p>
                  </div>
                  <div class="vnx_button_price">
                    <a id="vnx_button_price" class="brxe-button bricks-button bricks-background-primary gap-[4px] text-sm" data-service="<?php echo $arr_body[0]; ?>" data-package="<?php echo $package[0]; ?>" href="<?php echo $settings['list_button_url']; ?>"><?php echo $settings['list_button_text']; ?><i class="fa-light fa-arrow-right"></i></a>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
          <div class="vnx_button w-full flex justify-center items-center mt-6">
            <a id="vnx_button_loadmore" class="flex items-center gap-[4px] text-lg text-[#38A7FF]">Xem thêm <i class="fa-light fa-angle-down"></i></a>
            <a id="vnx_button_closemore" class="flex items-center gap-[4px] text-lg text-[#38A7FF] hidden">Thu gọn <i class="fa-light fa-angle-up"></i></a>
          </div>
        </div>
    <?php
        $i++;
      }
    }
    ?>
  </div>
</div>