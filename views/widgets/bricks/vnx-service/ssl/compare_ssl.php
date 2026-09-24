<?php
try {
  $data = isset($data) ? $data : new stdClass();
  $settings = $data->settings;
  $get_csv = $data->get_upload_file_data();

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
  //phần xử lý gói dịch vụ, cột dọc
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
  $service_popular = isset($settings['service_price']) ? $settings['service_price'] : '';
  $table_key = isset($settings['table_key']) ? $settings['table_key'] : '';
  $class_chatbutton = isset($settings['class_chatbutton']) ? $settings['class_chatbutton'] : '';
  $register_class_prefix = isset($settings['register_class_prefix']) ? $settings['register_class_prefix'] : '';
  $register_class_suffix = isset($settings['register_class_suffix']) ? $settings['register_class_suffix'] : '';
  $class_chatbutton = isset($settings['class_chatbutton']) ? ' ' . $settings['class_chatbutton'] : '';
  $next_arrow_class = isset($settings['next_arrow_class']) ? ' ' . $settings['next_arrow_class'] : '';
  $prev_arrow_class = isset($settings['prev_arrow_class']) ? ' ' . $settings['prev_arrow_class'] : '';
  $chat_button = isset($settings['text_chatbutton']) ? $settings['text_chatbutton'] : 'Chat với hỗ trợ';
  $subtable_keys = isset($settings['subtable_keys']) ? $settings['subtable_keys'] : [];
  $subtable_viewmore_class = isset($settings['subtable_viewmore_class']) ? $settings['subtable_viewmore_class'] : '';
  $highlight_lable = isset($settings['highlight_lable']['url']) ? $settings['highlight_lable']['url'] : '';
  $text_button = isset($settings['button_register']) ? $settings['button_register'] : 'Đăng ký ngay';
  ?>
  <div class="vnx_header_price">
    <div class="vnx_name_price w-full flex flex-row">
      <div class="button_box_support w-1/5">
        <span class=" text-lg font-bold text-center flex justify-center items-center rounded-full bg-[#F49846] text-[#525666] h-[52px] w-[52px] mb-2"><i class="fa-solid fa-comments" style="color: #FFF;"></i></span>
        <a class="button_support btn_tawk<?php echo esc_attr($class_chatbutton); ?>"><?php echo $chat_button; ?></a>
      </div>
      <div class="button_box_prices w-4/5 swiper-container">
        <div class="swiper-wrapper vnx_slider_bar_price w-full flex flex-row items-center">
          <?php
          if (!empty($boundaries)) {
            foreach ($boundaries as $key => $header_item) {
              if (is_array($header_item)) {
                if ($key == 0) {
                  $name_header = $result[0][$header_item[0]];
                  $name_box = explode(" | ", $result[0][$header_item[0]]);
                  $cycle_row_header = $data->getToSearchExcelCompare($header_item[0], $header_item[1], $result[0]);
                  if (!empty($result)) {
                    $column_order = 0;
                    foreach ($result as $key => $slides_header) {
                      if ($key !== 0) {
                        $column_order++;
                        $register_btn_class = '';
                        if ($register_class_prefix || $register_class_suffix || $table_key)
                          $register_btn_class = $register_class_prefix . $table_key . $column_order . $register_class_suffix . ' ';
                        $cycle_item_header = $data->getToSearchExcelCompare($header_item[0], $header_item[1], $slides_header);
                        $popular = $slides_header[0] === $service_popular ? 'vnx_price_pack_popular' : '';
                        ?>
                        <div class="w-1/4 swiper-slide vnx_price_pack flex flex-col justify-end items-center relative rounded <?php echo $popular; ?>">
                          <?php if ($slides_header[0] === $service_popular) {
                            if (!empty($highlight_lable)) { ?>
                              <div class="vnx_box_tag w-full">
                                <img class="vnx-common-label min-h-full" src="<?php esc_html_e($highlight_lable); ?>" alt="icon phổ biến" />
                              </div>
                            <?php }
                          } ?>
                          <div class="vnx_box_content h-full w-full flex flex-col justify-between items-center">
                            <span class="vnx_price_pack_title text-lg font-medium text-center"><?php echo $slides_header[0]; ?></span>
                            <div class="flex flex-col justify-between w-full gap-2">
                              <div class="vnx_price_pack_price w-full flex flex-row flex-nowrap justify-center items-center gap-2 ">
                                <span class="vnx_price text-xl font-bold"><?php echo $cycle_item_header[0]; ?></span><span class="text-sm font-medium">/Năm</span>
                              </div>
                              <div class="vnx_button_register w-full">
                                <a rel="nofollow" data-price="<?php echo $cycle_item_header[0]; ?>" data-period="1 năm" data-product-name="<?php echo $slides_header[0]; ?>"
                                  data-product-category="<?php echo trim(preg_replace('/[^a-zA-Z\s.-]/', '', $slides_header[0])); ?>" href="<?php echo esc_html($cycle_item_header[1]); ?>"
                                  class="vnx-btn-conversion <?php echo esc_attr($register_btn_class); ?>vnx-button-register url-register text-base font-medium text-[#FFF]"><?php echo $text_button; ?></a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <?php
                      }
                    }
                  }
                }
              }
            }
          }
          ?>
        </div>
        <?php
        if (count($result) > 4) { ?>
          <div class="vnx-swiper-button">
            <button id="swiper-button-prev" class="swiper_button swiper-button-prev<?php echo esc_attr($prev_arrow_class); ?>"><i class="fa-solid fa-angle-left"></i></button>
            <button id="swiper-button-next" class="swiper_button swiper-button-next<?php echo esc_attr($next_arrow_class); ?>"><i class="fa-solid fa-angle-right"></i></button>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="vnx_body_prices">
    <!-- loop  -->
    <?php
    if (!empty($boundaries)) {
      $stt = 0;
      foreach ($boundaries as $key => $item) {
        if (is_array($item)) {
          $lastIndex = count($boundaries) - 1;
          if ($key !== 0 && $key !== $lastIndex) {
            $name = $result[0][$item[0]];
            $icon = $result[1][$item[0]];
            $name_box = explode(" | ", $result[0][$item[0]]);
            $cycle_row = $data->getToSearchExcelCompare($item[0], $item[1], $result[0]);
            $box_first = $stt == 1 ? 'vnx_box_infor_hosting_first' : 'vnx_box_infor_hosting_second';
            $over_hidden = count($cycle_row) > 4 && $stt != 1 ? 'overflow-hidden' : '';
            ?>
            <div class="vnx_box_infor_hosting <?php echo $box_first; ?>">
              <div class="vnx_title_box_hosting">
                <div class="title">
                  <span class=" text-lg font-bold text-center text-[#FFF]"><?php echo $name_box[0]; ?></span>
                </div>
              </div>
              <div class="vnx_content_box_hosting flex flex-row <?php echo $over_hidden; ?>">
                <!-- title slider  -->
                <div class="w-1/5 box-list-row-infor">
                  <?php
                  if (!empty($cycle_row)) {
                    foreach ($cycle_row as $title) {
                      $pos_tooltip = $data->find_second_occurrence($result[0], $title);
                      $text_tooltip = $result[1][$pos_tooltip];
                      ?>
                      <div class="box-row-infor">
                        <span class="text-lg font-normal "><?php echo $title; ?></span>
                        <?php if (!empty($text_tooltip)) { ?>
                          <div class="infor-tooltip">
                            <span class="infor-icon"><i class="vnx_tooltip_icon <?php echo $settings['tooltip_icon']['icon']; ?>"></i></span>
                            <p class="text-tooltip text-xs">
                              <?php echo $text_tooltip; ?>
                            </p>
                          </div>
                        <?php } ?>
                      </div>
                      <?php
                    }
                  }
                  ?>
                </div>
                <!-- title slider  -->
                <!-- slider  -->
                <div class="w-4/5 flex flex-row box-row-infor-all-price swiper-container" id="compare_ssl">
                  <div class="heath w-full flex flex-row swiper-wrapper">
                    <?php
                    if (!empty($result)) {
                      foreach ($result as $key => $slides) {
                        if ($key !== 0) {
                          $cycle_item = $data->getToSearchExcelCompare($item[0], $item[1], $slides);
                          $popular_row = $slides[0] === $service_popular ? 'vnx_price_pack_popular_row' : '';
                          ?>
                          <div class=" box-row-infor-price swiper-slide <?php echo $popular_row; ?>">
                            <?php
                            if (!empty($cycle_item)) {
                              foreach ($cycle_item as $slide) {
                                $slide = explode(" | ", $slide);
                                $icon_yes = isset($settings['yes_icon']['icon']) ? $settings['yes_icon']['icon'] : '';
                                $icon_no = isset($settings['no_icon']['icon']) ? $settings['no_icon']['icon'] : '';
                                ?>
                                <div class="row-price">
                                  <?php
                                  if ($slide[0] == 'yes' && empty($slide[1])) {
                                    echo ' <i aria-hidden="true" class="vnx_icon_yes ' . $icon_yes . '"></i>';
                                  } else if ($slide[0] == 'no') {
                                    echo ' <i aria-hidden="true" class="vnx_icon_no ' . $icon_no . '"></i>';
                                  } else if ($slide[0] == 'image' && !empty($slide[1])) {
                                    echo '<img src="' . $slide[1] . '" alt="icon image" />';
                                  } else {
                                    $modified_content = preg_replace('/\+(.+)/', '<span style="color: #FF9038; font-weight: 700;">+$1</span>', $slide[1]);
                                    echo '<span class="text-lg text-center">' . $modified_content . '</span>';
                                  }
                                  ?>
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
                </div>
                <!-- slider  -->
              </div>
              <!-- load more  -->
              <?php
              if ($stt != 1 && count($cycle_row) > 4) {
                $viewmore_class = isset($subtable_keys[$stt - 1]['key']) ? ' ' . $subtable_viewmore_class . $subtable_keys[$stt - 1]['key'] : ' ' . $subtable_viewmore_class;
                ?>
                <div class="vnx_load_more_Center flex flex-row justify-center collapsed">
                  <button type="button" class="vnx-button-more toggle-btn<?php echo esc_html($viewmore_class); ?>">Xem thêm <i class="fa-solid fa-angles-down"></i></button>
                  <button type="button" class="vnx-button-close hidden ">Thu gọn <i class="fa-solid fa-angles-up"></i></button>
                </div>
              <?php } ?>
              <!-- load more  -->
            </div>
            <?php
          }
        }
        $stt++;
      }
    }
    ?>
    <!-- loop  -->
  </div>
  <?php
} catch (Exception $e) {
  error_log($e->getMessage());
}