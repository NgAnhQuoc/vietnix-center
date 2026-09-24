<?php
include_once VNX_PLUGIN_PATH_CENTER . '/functions/requires/push_logs_error.php';
$push_log = new Vnx_Push_Logger_Center();
$data = isset($data) ? $data : new stdClass();
include_once VNX_PLUGIN_PATH_CENTER . '/widgets/bricks/vnx-service-price.php';
try {
  $gift_tittle = $data->info['list_gift_title'];
  $list_gift_description = $data->info['list_gift_description'];
  $button_icon['icon'] = !empty($data->button_icon) ? \Bricks\Element::render_icon($data->button_icon) : false;
  $button_icon['button_text'] = $data->button_text;
  $order_by = $data->order_by;
  $csvdata = [];
  $custom_url = ($data->info['custom_button_url']['url']) ?? '';
  $button_attributes = '';
  $custom_button_switch = ($data->info['custom_button_url_switch']) ?? false;
  $gift_tool_box = $data->info['list_gift_title_show'];
  if ($custom_button_switch == 1) {
    if (isset($data->info['custom_button_url']['newTab']) && $data->info['custom_button_url']['newTab'] !== '') {
      $button_attributes .= 'target="_blank" ';
    }
    if (isset($data->info['custom_button_url']['rel']) && $data->info['custom_button_url']['rel'] !== '') {
      $button_attributes .= 'rel="' . $data->info['custom_button_url']['rel'] . '" ';
    }
  }

  $random_string = $data->random_string;
  $slug_tab = $data->slug;
  $link = $data->info['list_upload']['url'];
  $regex = '/wp-content\/(.*)/';
  $file = str_replace(get_site_url(), ABSPATH, $data->info['list_upload']['url']);
  $test = new VNX_Service_Price_Center();
  // Sử dụng preg_match để tìm kiếm
  if (preg_match($regex, $link, $matches)) {
    $file = ABSPATH . $matches[0];
  }
  if (file_exists($file)) {

    if (($handle = fopen($file, "r")) !== FALSE) {
      while (($data = fgetcsv($handle)) !== FALSE) {
        $csv_data[] = $data;
      }
    }

    $result = array();
    for ($i = 0; $i < count($csv_data[0]); $i++) {
      $row_list = array();
      for ($j = 0; $j < count($csv_data); $j++) {
        $row_list[] = $csv_data[$j][$i];
      }
      $result[] = $row_list;
    }
    $boundaries = $test->findCycleBoundaries($result[0]);
    $special = $test->getToSearchExcelCompare($boundaries[0][0], $boundaries[0][1], $result[0]);
    $the_last_row = count($boundaries) - 1;
    if (is_array($boundaries) && !empty($boundaries)) {
      $gt_row = $test->getToSearchExcelCompare($boundaries[$the_last_row][0], $boundaries[$the_last_row][1], $result[0]);
      $list_tooltip = $result;
    }
?>
    <div class="owl-wrapper">
      <div class="loop<?= $random_string ?> owl-carousel owl-theme ">
        <?php
        if ($order_by == "yes") {
          $new_array = [];
          $label = array_shift($result);
          foreach ($result as $key => $value) {
            if ($value[2] != null) {
              $newArray = move_variable_and_following_elements_to_first_index_Center($result, $key);
              break;
            }
          }
          $result_new = isset($newArray) ? $newArray : $result;
        }
        if(is_array($result_new)){
        foreach ($result_new as $key => $item) {
          $spi = $item[2] != null ? 'vnx-price-special' : '';
        ?>
          <article class="card <?php echo ($item[2] != null) ? "has_header_bandage" : " mt-[34px]"; ?>">
            <?php
            if ($item[2] != null) {
              $bandage_data = explode(',', $item[2]);
            ?>
              <div class="vnx-tab-discount absolute w-full top-0 left-0 flex justify-center">
                <img class="vnx-tab-discount-label object-scale-down w-fit" src="https://vietnix.vn/wp-content/uploads/2024/03/Lable.svg" alt="icon bán chạy" />
              </div>
            <?php
            }
            ?>
            <div class="card__header">
              <div class="card__header_title">
                <?php echo  $item[0]; ?>
              </div>
            </div>
            <div class="card__content">
              <div class="card__price">
                <?php if ($item[5] != null) { ?>
                  <div class="card__price__period">
                    <span class="card__line-through text-[#757885]">
                      <?= $item[4] ?>
                    </span>
                    <?php if ($item[6] != "") { ?>
                      <span class="card__price_bandage">
                        <?= $item[6] ?>
                      </span>
                    <?php } ?>
                  </div>
                  <div class="card__price__value">
                    <span>
                      <?= $item[5] ?>
                    </span>
                    <div class="card__price__period">
                      /Tháng
                    </div>
                  </div>
                <?php } else { ?>
                  <div class="card__price__value">
                    <span>
                      <?= $item[4] ?>
                    </span>
                  </div>
                  <div class="card__price__period">
                    /Tháng
                  </div>
                <?php } ?>
                <?php if ($custom_button_switch == true) { ?>
                  <a href="<?= $custom_url ?>" <?= $button_attributes ?> class="btn_coversion_post">
                    <button class="card__button_btn">
                      <?= $button_icon['icon'] ?>
                      <?= $button_icon['button_text'] ?>
                    </button>
                  </a>
                <?php } ?>
              </div>
              <div class="card__title">
                <?php
                if (!empty($boundaries) && is_array($boundaries)) {
                  foreach ($boundaries as $index => $row) {
                    if ($index > 1 && $index < $the_last_row) {
                      $kt_row = $test->getToSearchExcelCompare($boundaries[$index][0], $boundaries[$index][1], $label);
                      $kt_card = $test->getToSearchExcelCompare($boundaries[$index][0], $boundaries[$index][1], $item);
                      $kt_infor_title = explode(" | ", $label[$boundaries[$index][0]]);
                ?>
                      <div class="box-content-infor text-[#525666] gap-3 flex flex-col">
                        <div class="infor-title flex text-[#525666]">
                          <?php echo $kt_infor_title[0]; ?>
                        </div>
                        <?php
                        if (is_array($kt_row)) {
                          foreach ($kt_row as $number => $kt_rows) {
                            $ktStart = array_search($kt_rows, $result[0]);
                            $kt_infor = explode(" | ", $kt_rows);
                        ?>
                            <div class="card__system_info">
                              <div class="image_box gap-2 flex flex-row w-full">
                                <img decoding="async" class="w-100" alt="<?= $kt_infor[2]; ?>" src="<?= $kt_infor[1]; ?>">
                                <div class="system_info_title">
                                  <?= $kt_infor[0]; ?>
                                  <strong>
                                    <?php
                                    if (isset($kt_card[$number])) {
                                      echo ': ' . $kt_card[$number];
                                    }
                                    ?>
                                  </strong>
                                </div>
                              </div>
                              <div class="infor-tooltip">
                                <div class="infor-icon">
                                  <i class="vnx_tooltip_icon fa-regular fa-circle-question"></i>
                                </div>
                                <?php
                                $keys = array_keys($label, $kt_infor[0]);
                                if (!empty($keys[0])) {
                                  $tooltip = isset($keys[0]) ? $keys[0] : null;
                                  if (!empty($list_tooltip[1][$tooltip])) {
                                    echo '<p id="text-tooltip" class="text-tooltip hidden">';
                                    echo $list_tooltip[1][$tooltip];
                                    echo '</p>';
                                  }
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
              <?php if ($gift_tool_box != '') { ?>
                <div class="card__gift">
                  <div class="card__gift_title">
                    <img decoding="async" class="w-100" alt="icon Gift bảng giá" src="https://vietnix.vn/wp-content/uploads/2024/06/Icon-gift.svg">
                    <span class="card__gift_title_info" data-gift-id="vnx-tab-gift-<?= $random_string . $slug_tab ?>">
                      <?= $gift_tittle ?>
                    </span>
                  </div>
                </div>
              <?php } ?>
            </div>
          </article>
        <?php
        }
      }
        ?>
      </div>
    </div>
<?php
  } else {
    echo 'CSV File not found.';
    return;
  }
} catch (\Throwable $e) {
  $push_log->vnxPushLogger($e->getMessage(), ['File' => __FILE__, 'Line' => $e->getLine()]);
}
?>