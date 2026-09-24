<?php
$data = isset($data) ? $data : new stdClass();
$settings = $data->settings;
try {
  $get_csv = $data->get_upload_file_data();
  if ($get_csv['status'] == 'success' && !empty($get_csv['data']))
    $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
  if (empty($csv_data)) {
    if (current_user_can('update_core'))
      echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html(__FILE__) . '</div>';
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

  $boundaries = $data->findCycleBoundaries($result[0]);
  $the_last_row = count($boundaries) - 1;
  if (is_array($boundaries) && !empty($boundaries)) {
    $cycle_row = $data->getToSearchExcelCompare($boundaries[0][0], $boundaries[0][1], $result[0]);
    $data_row = $data->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[0]);
    $url_row = $data->getToSearchExcelCompare($boundaries[$the_last_row][0], $boundaries[$the_last_row][1], $result[0]);
  }
  $price_name = $csv_data[0][0];
  $row_title_fire = array_slice($csv_data[0], 2);
  $img_highlight = !empty($settings['highlight_lable']['url']) ? $settings['highlight_lable']['url'] : '';
  $active_label = !empty($settings['service_label_active']) ? $settings['service_label_active'] : 'false';
  ?>
  <div class="w-full flex justify-center lg:mb-1 mb-5 vnx-fillter-service-price py-8">
    <div class="grid-cols-2 gap-3 lg:gap-4 w-full lg:w-auto grid lg:flex lg:flex-row bg-white py-2.5 px-2 lg:px-2 rounded-2xl lg:rounded-full text-gray-600 font-bold border">
      <?php
      if (is_array($cycle_row) && !empty($cycle_row)) {
        foreach ($cycle_row as $key => $value) {
          $cycle_data = $data->getParaminRow($value);
          $tab_active = !empty($settings['cycle_price']) && $settings['cycle_price'] == $cycle_data[0] ? 'tab-active' : '';
          ?>
          <div class="vnx-cyc-price border lg:border-0 lg:w-[8em] text-center cursor-pointer lg:bg-white bg-[#38A7FF1A] hover:bg-[#38A7FF1A] py-1.5 lg:py-1 lg:rounded-full rounded-md relative <?php echo $tab_active; ?>"
            data-target="vnx-tab-<?php echo $key; ?>" data-discount="<?php echo $cycle_data[1]; ?>" data-period="<?php echo $cycle_data[0]; ?>">
            <?php if (!empty($settings['cycle_price']) && $settings['cycle_price'] == $cycle_data[0] && $active_label == 'true' && !empty($settings['service_label_desktop']['url'])) { ?>
              <img class="vnx-common-label w-full object-scale-down object-bottom bottom-full absolute hidden lg:block" src="<?php echo $settings['service_label_desktop']['url']; ?>" alt="icon phổ biến">
            <?php } ?>
            <span class="el-custom-tab-title text-[#2D86CC] leading-8"><?php echo $cycle_data[0]; ?></span>
            <?php if(!empty($cycle_data[1])){?>
            <span class="el-custom-tab-discount text-xs py-1 px-1 rounded-2xl"><?php echo $cycle_data[1]; ?></span>
            <?php } ?>
          </div>
          <?php
        }
      } ?>
    </div>
  </div>
  <div class=" text-sm  lg:table ">
    <!-- Danh sách tên gói  -->
    <div class="vnx_firewall_name flex flex-nowrap">
      <div class="box-title"></div>
      <?php if (is_array($row_title_fire)) {
        foreach ($row_title_fire as $key => $title) {
          $active = !empty($settings['service_price']) && $settings['service_price'] == $title ? 'special' : '';
          ?>
          <div class="text-center leading-6 bg-[#FFFFFF1A] flex flex-col items-center justify-center relative cursor-pointer box-title <?php echo $active; ?>" data-tab-active="<?php echo $key+2; ?>">
            <?php if (!empty($settings['service_price']) && $settings['service_price'] == $title && !empty($img_highlight)) { ?>
              <img class="absolute -right-[1px] -top-[1px]" src="<?php esc_html_e($img_highlight); ?>" alt="icon bán chạy" />
            <?php } ?>
            <span class="title"> <?php echo $title; ?></span>
          </div>
        <?php }
      } ?>
    </div>
    <!-- /Danh sách tên gói  -->
    <!-- Danh sách thông tin gói  -->
    <div class="vnx_firewall_package flex flex-row flex-nowrap">
      <div class="box-infor">
        <div class="box-infor-col">
          <div class="box-infor-col-title">
            <?php echo $price_name; ?>
          </div>
        </div>
        <?php
        if ($key != 0) {
          if (is_array($data_row)) {
            foreach ($data_row as $key => $value) {
              ?>
              <div class="box-infor-col">
                <div class="box-infor-col-title">
                  <?php
                  echo $value;
                  ?>
                </div>
              </div>
            <?php }
          }
        } ?>
      </div>
      <?php
      if (is_array($result)) {
        foreach ($result as $key => $value) {
          if ($key > 1) {
            $row_data = $data->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[$key]);
            $row_price = $data->getToSearchExcelCompare($boundaries[0][0], $boundaries[0][1], $result[$key]);
            $price_title = $csv_data[0][$key];
            ?>
            <div class="box-infor" data-box="<?php echo $key;?>">
              <div class="box-infor-col price">
                <div class="box-infor-col-data vnx-price-reduced">
                  <span class="vnx-price-reduced-text">0đ</span><span class="vnx-price-reduced-pr">/tháng</span>
                </div>
                <div class="hidden vnx-price-reduced-name">
                  <p><?php echo $price_title; ?></p>
                </div>
                <div class="hidden vnx-price-reduced-name-category">
                  <p><?php echo $price_title; ?></p>
                </div>
                <div class="flex flex-row justify-center text-sm box-vnx-price-reduced-year">
                  <p class="box-infor-text-price-year text-[#FFF]">Tổng&nbsp;<span>1 năm</span>&nbsp;
                    <?php if(is_array($settings['tooltip_icon']) && $settings['tooltip_icon']['library'] == 'svg'){ ?>
                      <img src="<?php echo $settings['tooltip_icon']['svg']['url']; ?>" class="w-5 h-5" alt="icon tooltip">
                      <?php }else{ ?>
                        <i aria-hidden="true" class="<?php echo $settings['tooltip_icon']['icon']; ?>"></i>
                      <?php }?>
                  </p>
                  <div class="box-infor-tooltip-price-year flex flex-col gap-1 hidden">
                    <span class="text-base font-bold">Tạm tính</span>
                    <div class="flex flex-nowrap flex-row gap-3">
                      <span class="min-w-max w-1/3">Chu kỳ:</span><span class="vnx-price-cyc-year font-semibold min-w-max w-2/3"></span>
                    </div>
                    <div class="flex justify-between flex-nowrap flex-row gap-3">
                      <span class="min-w-max w-1/3">Tổng:</span><span class="vnx-price-reduced-year font-semibold bg-text min-w-max w-2/3"></span>
                    </div>
                  </div>
                </div>
                <div class="hidden">
                  <?php
                  foreach ($row_price as $key_price => $price) {
                    $price_info = explode(" | ", $price);
                    echo ' <span class="vnx-price-cyc" data-tab="vnx-tab-' . $key_price . '" data-cost="' . $price_info[0] . '" data-price-year="' . $price_info[1] . '" data-product-name="' . $price_title . '" data-product-category="'.trim(preg_replace('/[^a-zA-Z\s.-]/', '', $price_title)).'"></span>';
                  } ?>
                </div>
              </div>
              <?php if (is_array($row_data)) {
                foreach ($row_data as $key => $value) {
                  $kt_infor = explode(" | ", $value);
                  ?>
                  <div class="box-infor-col">
                    <div class="box-infor-col-data">
                      <?php
                      if ($kt_infor[1] == '*') {
                        echo $kt_infor[0];
                      } else {
                        $icon = $kt_infor[0] == 'yes' ? $settings['yes_icon']['icon'] : $settings['no_icon']['icon'];
                        $status_icon = $kt_infor[0] == 'yes' ? 'yes' : 'no';
                        print ('<i aria-hidden="true" class="vnx_icon_' . $status_icon . ' ' . $icon . '"></i>');
                      }
                      ?>
                    </div>
                  </div>
                <?php }
              } ?>
            </div>
            <?php
          }
        }
      }
      ?>
    </div>
    <!-- /Danh sách thông tin gói  -->
    <!-- Nút Đăng ký  -->
    <div class="vnx_firewall_url flex flex-nowrap">
      <div class="box-title"></div>
      <?php if (is_array($result)) {
        foreach ($result as $key => $url) {
          if ($key > 1) {
            ?>
            <div class="text-center leading-6 bg-[#FFFFFF1A] flex flex-col items-center justify-center relative cursor-pointer box-title url-register" data-box="<?php echo $key; ?>">
              <a class=" vnx-btn-conversion vnx-button-register" rel="nofollow" data-price="0đ" data-period="1 Tháng" data-product-name="" data-product-category="" href="#">
                <?php echo $settings['button_register']; ?>
              </a>
              <div class="hidden">
                <?php
                $row_url= $data->getToSearchExcelCompare($boundaries[$the_last_row][0], $boundaries[$the_last_row][1], $result[$key]);
                foreach ($row_url as $key_url => $url) {
                  echo ' <span class="vnx-price-cyc-url" data-tab="vnx-tab-' . $key_url . '" data-url="' . $url . '" ></span>';
                } ?>
              </div>
            </div>
          <?php }
        }
      } ?>
    </div>
    <!-- /Nút Đăng ký  -->
  </div>
  <?php
} catch (Exception $e) {
  error_log($e->getMessage());
}