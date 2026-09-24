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
  $text_cycle_price = isset($settings['text_cycle_price']) ? $settings['text_cycle_price'] : '/Năm';
?>

  <div>
    <div v-cloak id="top-compare-table" class="flex z-20 flex-row gap-4 mb-6 items-end py-8 px-5 rounded-[4px]" style="background: linear-gradient(90deg, rgba(0, 124, 252, 0.04) 0%, rgba(0, 100, 204, 0.04) 100%);">
      <template v-for="i in 4">
        <div class="flex flex-col items-start w-1/4 relative">
          <label class="mb-2 font-medium text-base text-[#282829]">Server {{ i }}</label>
          <div class="relative w-full">
            <button
              class="w-full h-10 px-3 border border-[#0F0F0F] rounded focus:outline-none focus:ring-2 focus:ring-blue-400 flex items-center justify-between bg-white"
              @click="handleDropdown(i-1)"
              type="button"
            >
              <span>
                {{ selectedServers[i-1] !== '' && selectedServers[i-1] !== null && selectedServers[i-1] !== undefined ? titles[selectedServers[i-1]] : 'Chọn máy chủ' }}
              </span>
              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div
              :class="[
                'w-[248px] p-2 left-0 top-[50px] absolute bg-white rounded shadow-[0px_1px_8px_0px_rgba(0,0,0,0.12)] inline-flex flex-col justify-start items-start z-20 max-h-[216px] overflow-auto border border-gray-200',
                dropdownOpen[i-1] ? '' : 'hidden'
              ]">
              <template v-for="(title, idx) in titles">
                <div
                  v-if="!selectedServers.includes(idx) || selectedServers[i-1] === idx"
                  :key="'server' + i + '-' + idx"
                  class="self-stretch p-2 rounded inline-flex justify-start items-center gap-2 cursor-pointer transition-colors"
                  :class="[
                    selectedServers[i-1] === idx ? 'bg-[#f2f2f4]' : '',
                    'hover:bg-[#f2f2f4]'
                  ]"
                  @click="selectDropdown(idx, i-1)"
                >
                  <div class="flex-1 justify-start text-[#282828] text-base font-normal leading-normal">
                    {{ title }}
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </template>


      <div class="flex gap-4">
        <button
          :disabled="!isTwoSelected"
          v-on:click="handleCompare"
          class="w-[111px] px-6 py-2 bg-[#007CFC] hover:bg-[#085FC5] rounded-lg gap-2 text-base font-medium leading-normal text-white whitespace-nowrap disabled:opacity-20">
          So Sánh
        </button>
      <div  class="w-[111px]">
          <button
          v-show="isShowCompare"
          v-on:click="removeFilter()"
          class="w-[111px] bg-[#EDFAFF] hover:bg-[#D6F2FF] rounded-lg px-6 py-2 text-[#007CFC]"
          aria-label="Xoá bộ lọc">
          <span class="text-base font-bold">Xoá</span>
        </button>
      </div>
      </div>
    </div>
  </div>

  <div class="vnx_header_price_duplicate relative" v-show="isShowCompare"></div>
  <div class="vnx_header_price" v-show="!isShowCompare">
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
                          <div class="vnx_box_content h-full w-full flex flex-col justify-end items-center">
                            <span class="vnx_price_pack_title text-lg font-medium text-center"><?php echo $slides_header[0]; ?></span>
                            <div class="flex flex-col justify-between w-full gap-2">
                              <div class="vnx_price_pack_price w-full flex flex-row flex-nowrap justify-center items-center gap-2 ">
                                <span class="vnx_price text-xl font-bold"><?php echo $cycle_item_header[0]; ?></span><span class="text-sm font-medium"><?php echo $text_cycle_price; ?></span>
                              </div>
                              <div class="vnx_button_register w-full">
                                <?php
                                $year_ru = !empty($cycle_item_header[1]) ? explode(' | ', $cycle_item_header[1]) : ['1 năm', $cycle_item_header[0]];
                                $register_url = !empty($cycle_item_header[2]) ? $cycle_item_header[2] : $cycle_item_header[1];
                                ?>
                                <a rel="nofollow" data-price="<?php echo esc_html($year_ru[1] ?? $cycle_item_header[0]); ?>" data-period="<?php echo esc_html($year_ru[0] ?? '1 năm'); ?>" data-product-name="<?php echo $slides_header[0]; ?>"
                                  data-product-category="<?php echo trim(preg_replace('/[^a-zA-Z\s.-]/', '', $slides_header[0])); ?>" href="<?php echo esc_url($register_url); ?>"
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
      </div>
      <?php
        if (count($result) > 4) { ?>
        <div class="vnx-swiper-button w-4/5">
          <button id="swiper-button-prev" class="swiper_button swiper-button-prev<?php echo esc_attr($prev_arrow_class); ?>"><i class="fa-solid fa-angle-left"></i></button>
          <button id="swiper-button-next" class="swiper_button swiper-button-next<?php echo esc_attr($next_arrow_class); ?>"><i class="fa-solid fa-angle-right"></i></button>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="vnx_body_prices_duplicate" v-show="isShowCompare"></div>
  <div class="vnx_body_prices" v-show="!isShowCompare">
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
