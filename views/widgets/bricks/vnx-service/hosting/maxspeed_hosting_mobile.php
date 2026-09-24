<?php
$data = isset($data) ? $data : new stdClass();
$settings = $data->settings;
try {
  $get_csv = $data->get_upload_file_data();
  if ($get_csv['status'] == 'success' && !empty($get_csv['data']))
    $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
  if (empty($csv_data)) {
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
  }
  $row_title_fire = array_slice($result, 2);
  $img_highlight = !empty($settings['highlight_lable']['url']) ? $settings['highlight_lable']['url'] : '';
  $item_focus = !empty($settings['item_start']) ? $settings['item_start'] : '1';
  $active_label = !empty($settings['service_label_active']) ? $settings['service_label_active'] : 'false';
  ?>
  <div class="w-full flex flex-col justify-center items-center vnx-fillter-service-price">
    <div class="grid-cols-2 gap-2 w-fit grid p-2 mb-8 font-bold rounded vnx-cyc-price-list">
      <?php
      if (is_array($cycle_row) && !empty($cycle_row)) {
        foreach ($cycle_row as $key => $value) {
          $cycle_data = $data->getParaminRow($value);
          $tab_active = !empty($settings['cycle_price']) && $settings['cycle_price'] == $cycle_data[0] ? 'tab-active' : '';
          ?>
          <div class="vnx-cyc-price w-[157px] text-center cursor-pointer hover:bg-[#ffffff1f] bg-[#ffffff1f] py-1.5  rounded-md relative <?php echo $tab_active; ?>" data-target="vnx-tab-<?php echo $key; ?>" data-discount="<?php echo $cycle_data[1]; ?>"
            data-period="<?php echo $cycle_data[0]; ?>">
            <?php if (!empty($settings['cycle_price']) && $settings['cycle_price'] == $cycle_data[0] && $active_label == 'true' && !empty($settings['service_label_mobile']['url'])) { ?>
              <img class="vnx-common-label w-full object-scale-down object-top top-[-8px] absolute lg:hidden" src="<?php echo $settings['service_label_mobile']['url']; ?>" alt="icon phổ biến">
            <?php } ?>
            <span class="el-custom-tab-title text-[#FFF] leading-8"><?php echo $cycle_data[0]; ?></span>
            <?php if (!empty($cycle_data[1])) { ?>
              <span class="el-custom-tab-discount ml-2.5 text-xs py-0.5 px-2 rounded-2xl"><?php echo $cycle_data[1]; ?></span>
            <?php } ?>
          </div>
          <?php
        }
      } ?>
    </div>
    <div class="vnx-data-service-price w-full px-2 flex justify-center">
      <div class="vnx_div_data_maxspeed hidden" data-splide='{"speed":200,"pagination":true,"updateOnMove":true,"gap":"10px","start":<?php echo $item_focus; ?>,"perMove":1,"perPage":1,"type":"loop"}'></div>
      <div id="vnx_body_slider_maxspeed" class="splide vnx_body_slider_maxspeed w-[343px]">
        <div class="splide__arrows splide__arrows--ltr service-price-arrow">
          <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Previous slide">
            <i class="fas fa-angle-left"></i>
          </button>
          <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide">
            <i class="fas fa-angle-right"></i>
          </button>
        </div>
        <div class="splide__track">
          <ul class="splide__list items-end">
            <!-- slider -->
            <?php
            if (is_array($row_title_fire)) {
              foreach ($row_title_fire as $key => $title) {
                $kt_row = $data->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[$key + 2]);
                $kt_icon = $data->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[0]);
                $kt_tooltip = $data->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[1]);
                $row_price = $data->getToSearchExcelCompare($boundaries[0][0], $boundaries[0][1], $result[$key + 2]);
                $price_title = $csv_data[0][$key + 2];
                $price_special = $csv_data[1][$key + 2];
                $special = !empty($settings['service_price']) && $settings['service_price'] == $price_title ? 'special' : '';
                ?>
                <li class="splide__slide p-2">
                  <div class="box-card-hosting border border-[#424242] rounded-lg flex flex-col relative items-center <?php echo $special; ?>" data-box="<?php echo $key; ?>">
                    <img src="" alt="icon tag discount ưu đãi" class="discount-label absolute top-[-1px] right-[-1px] hidden">
                    <div class=" absolute w-fit top-[-1px] ">
                      <?php if (!empty($settings['service_price']) && $settings['service_price'] == $price_title && !empty($img_highlight)) { ?>
                        <img class="vnx-tab-discount" src="<?php esc_html_e($img_highlight); ?>" alt="icon bán chạy" />
                      <?php } ?>
                    </div>
                    <div class="card-content-price flex flex-col pb-5">
                      <p class="title-card text-[#FFF] text-base font-bold mb-5"><?php echo $price_title; ?></p>
                      <div class="card-price">
                        <div class="card-price-reduced flex flex-row gap-2 pb-1 mb-2">
                          <span class="price-reduced-text text-[#C0C0C2] text-sm">222,300</span>
                          <div class="price-reduced-discount">
                            <img src="" alt="icon tag discount">
                          </div>
                        </div>
                        <div class="card-price-popular flex flex-row gap-1 items-center">
                          <p class="price vnx-price-popular">200,070</p>
                          <span class="text-[#C0C0C2] mr-3 text-sm">/tháng</span>
                          <div class="flex flex-row justify-center text-sm box-vnx-price-reduced-year ">
                            <div class="el-custom-text-price-year">
                              <?php
                              if (!empty($settings['tooltip_icon_price'])) {
                                if ($settings['tooltip_icon_price']['library'] == 'svg') {
                                  echo '<img src="' . $settings['tooltip_icon_price']['svg']['url'] . '" alt="icon tooltip">';
                                } else {
                                  echo '<i aria-hidden="true" class="' . $settings['tooltip_icon_price']['icon'] . '"></i>';
                                }
                              }
                              ?>
                            </div>
                            <div class="el-custom-tooltip-price-year flex flex-col gap-1 w-max hidden">
                              <span class="text-base font-bold text-[#00070E]">Tạm tính</span>
                              <div class="text-[#00070E]">
                                Chu kỳ:&nbsp;<span class="vnx-price-cyc-year font-semibold"></span>
                              </div>
                              <div class="text-[#00070E]">
                                Tổng:&nbsp;<span class="vnx-price-reduced-year font-semibold bg-text"></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="hidden">
                        <?php
                        foreach ($row_price as $key_price => $price) {
                          $price_info = explode(" | ", $price);
                          $price_cat = trim(preg_replace('/[^a-zA-Z\s.-]/', '', $price_title));
                          echo ' <span class="vnx-price-cyc" data-tab="vnx-tab-' . $key_price . '" data-cost="' . $price_info[0] . '" data-price-year="' . $price_info[1] . '" data-product-name="' . $price_title . '" data-total="' . $price_info[2] . '" data-discount="' . $price_info[3] . '" data-discount-label="' . $price_info[4] . '" data-product-category="' . $price_cat . '"></span>';
                        } ?>
                      </div>
                    </div>
                    <div class="card-content-infor w-full">
                      <?php
                      if (is_array($kt_row)) {
                        foreach ($kt_row as $number => $kt_rows) {
                          $ktStart = $kt_icon[$number];
                          $kt_infor = explode("|", $kt_rows);
                          $kt_icon_hl = explode("|", $ktStart);
                          if (!empty($kt_infor[0])) {
                            ?>
                            <div class="box-row-infor">
                              <div class="infor-text">
                                <span class="infor-icon">
                                  <?php if (trim($kt_infor[2]) == 'yes' || trim($kt_infor[2]) == 'true' || trim($kt_infor[2]) != 'no') {
                                    if (is_array($kt_icon_hl) && !empty($kt_icon_hl[1]) && trim($kt_icon_hl[1]) != "no") {
                                      print ('<i aria-hidden="true" class="vnx_highlight_icon ' . $settings['highlight_icon']['icon'] . '"></i>');
                                    } else {
                                      print ('<i aria-hidden="true" class="vnx_icon_yes ' . $settings['yes_icon']['icon'] . '"></i>');
                                    }
                                    ?>
                                  <?php } else if (trim($kt_infor[2]) == 'no') { ?>
                                      <i aria-hidden="true" class="vnx_icon_no <?php echo $settings['no_icon']['icon']; ?>"></i>
                                  <?php } ?>
                                </span>
                                <span class="text-sm <?php echo $opacity = trim($kt_infor[2]) == 'no' ? "opacity-60" : ""; ?>">
                                  <?php
                                  echo $kt_infor[0];
                                  if (!empty($kt_infor[1]) && trim($kt_infor[1]) != "") {
                                    echo '<strong>' . $kt_infor[1] . '</strong>';
                                  }
                                  ?>
                                </span>
                              </div>
                              <?php
                              if (!empty($kt_tooltip[$number])) {
                                ?>
                                <div class="infor-tooltip">
                                  <span class="infor-icon"><i aria-hidden="true" class="vnx_tooltip_icon <?php echo $settings['tooltip_icon']['icon']; ?>"></i></span>
                                  <?php
                                  echo '<p class="text-tooltip text-xs">' . $kt_tooltip[$number] . '</p>';
                                  ?>
                                </div>
                                <?php
                              }
                              ?>
                            </div>
                            <?php
                          }
                        }
                      } ?>
                    </div>
                    <div class="card-content-register w-full">
                      <div class="text-center leading-6  flex flex-col items-center justify-center cursor-pointer box-title url-register" data-box="<?php echo $key; ?>">
                        <a class=" vnx-btn-conversion vnx-button-register" rel="nofollow" data-price="0đ" data-period="1 Tháng" data-product-name="MAXSPEED_HOSTING" data-product-category="MAXSPEED_HOSTING" href="#">
                          <?php echo $settings['button_register']; ?>
                        </a>
                        <div class="hidden">
                          <?php
                          $row_url = $data->getToSearchExcelCompare($boundaries[$the_last_row][0], $boundaries[$the_last_row][1], $result[$key + 2]);
                          foreach ($row_url as $key_url => $url) {
                            echo ' <span class="vnx-price-cyc-url" data-tab="vnx-tab-' . $key_url . '" data-url="' . $url . '" ></span>';
                          } ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <?php
              }
            }
            ?>
            <!-- slider -->
          </ul>
        </div>
        <ul class="splide__pagination splide__pagination--ltr" role="tablist" aria-label="Select a slide to show">

        </ul>
      </div>
    </div>
  </div>
  <?php
} catch (Exception $e) {
  error_log($e->getMessage());
}