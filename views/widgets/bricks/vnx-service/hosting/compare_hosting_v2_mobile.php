<?php
$data = isset($data) ? $data : new stdClass();
try{
if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
  return;
}
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
$popular_active = 0;
$table_key = isset( $settings[ 'table_key' ] ) ? $settings[ 'table_key' ] : '';
$class_chatbutton = isset( $settings[ 'class_chatbutton' ] ) ? $settings[ 'class_chatbutton' ] : '';
$register_class_prefix = isset( $settings[ 'register_class_prefix' ] ) ? $settings[ 'register_class_prefix' ] : '';
$register_class_suffix = isset( $settings[ 'register_class_suffix' ] ) ? $settings[ 'register_class_suffix' ] : '';
$class_chatbutton = isset( $settings[ 'class_chatbutton' ] ) ? ' ' . $settings[ 'class_chatbutton' ] : '';
$next_arrow_class = isset( $settings[ 'next_arrow_class' ] ) ? ' ' . $settings[ 'next_arrow_class' ] : '';
$prev_arrow_class = isset( $settings[ 'prev_arrow_class' ] ) ? ' ' . $settings[ 'prev_arrow_class' ] : '';
$subtable_keys = isset( $settings[ 'subtable_keys' ] ) ? $settings[ 'subtable_keys' ] : [];
$subtable_viewmore_class = isset( $settings[ 'subtable_viewmore_class' ] ) ? $settings[ 'subtable_viewmore_class' ] : '';
?>
<div class="vnx_header_price">
  <div class="vnx_name_price w-full flex flex-row">
    <div class="button_box_prices w-full swiper-container-mobile" id="compare_hosting_2">
      <div class="swiper-wrapper vnx_slider_bar_price w-full flex flex-row items-stretch">
        <?php
        if (!empty($boundaries)) {
          $stt = 0;
          foreach ($boundaries as $key => $header_item) {
            if (is_array($header_item)) {
              if ($key == 0) {
                $name_header = $result[0][$header_item[0]];
                $name_box = explode(" | ", $result[0][$header_item[0]]);
                $cycle_row_header = $data->getToSearchExcelCompare($header_item[0], $header_item[1], $result[0]);
                if (!empty($result)) {
                  foreach ($result as $key => $slides_header) {
                    if ($key !== 0) {
                      $cycle_item_header = $data->getToSearchExcelCompare($header_item[0], $header_item[1], $slides_header);
                      $popular = $slides_header[0] === $service_popular ? 'vnx_price_pack_popular' : '';
                      if ($slides_header[0] === $service_popular) {
                        $popular_active = $stt;
                      }
                      ?>
                      <div class="w-4/12 swiper-slide vnx_price_pack flex flex-col justify-center items-center relative <?php echo $popular; ?>">
                        <span class="vnx_price_pack_title text-sm font-semibold text-center pb-3"><?php echo $slides_header[0]; ?></span>
                        <div class="vnx_price_pack_price w-full">
                          <?php
                            $cycle_item_price = explode(' | ', $cycle_item_header[1]);
                            ?>
                          <a rel="nofollow" data-price="<?php echo esc_html($cycle_item_price[1]); ?>" data-period="<?php echo $cycle_item_price[0]; ?>" data-product-name="<?php echo $slides_header[0]; ?>" data-product-category="<?php echo trim(preg_replace('/[^a-zA-Z\s.-]/', '', $slides_header[0])); ?>"
                            href="<?php echo esc_html($cycle_item_header[2]); ?>" class="vnx-btn-conversion vnx_price w-full flex"><?php echo $cycle_item_header[0]; ?><span class="text-[10px]  font-medium">/tháng</span></a>
                        </div>
                      </div>
                      <?php
                    }
                    $stt++;
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
    if (count($result) > 4) {
      ?>
      <div class="vnx-swiper-button">
        <button id="swiper-button-prev" class="swiper_button swiper-button-prev<?php echo esc_attr( $prev_arrow_class ); ?>"><i class="fa-solid fa-angle-left"></i></button>
        <button id="swiper-button-next" class="swiper_button swiper-button-next<?php echo esc_attr( $next_arrow_class ); ?>"><i class="fa-solid fa-angle-right"></i></button>
      </div>
    <?php } ?>
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
          $name_box = explode(" | ", $result[0][$item[0]]);
          $cycle_row = $data->getToSearchExcelCompare($item[0], $item[1], $result[0]);
          $box_first = $stt == 1 ? 'vnx_box_infor_hosting_first' : 'vnx_box_infor_hosting_second';
          ?>
          <div class="vnx_box_infor_hosting <?php echo $box_first; ?>">
            <div class="vnx_title_box_hosting">
              <div class="title">
                <span class=" text-sm font-bold text-center text-[#FFF]"><?php echo $name_box[0]; ?></span>
              </div>
            </div>
            <div class="vnx_content_box_hosting flex flex-col">
              <!-- title slider  -->
              <div class="w-full">
                <?php
                if (!empty($cycle_row)) {
                  foreach ($cycle_row as $title) {
                    $pos_tooltip = $data->find_second_occurrence($result[0], $title);
                    $text_tooltip = $result[1][$pos_tooltip];
                    $index = array_search($title, $result[0]);
                    $column_slides = $csv_data[$index];
                    ?>
                    <div class="box-row-infor">
                      <span class="text-sm font-normal"><?php echo $title; ?></span>
                      <?php if (!empty($text_tooltip)) { ?>
                        <div class="infor-tooltip">
                          <span class="infor-icon"><i class="vnx_tooltip_icon <?php echo $settings['tooltip_icon']['icon']; ?>"></i></span>
                          <p class="text-tooltip text-xs">
                            <?php echo $text_tooltip; ?>
                          </p>
                        </div>
                      <?php } ?>
                    </div>
                    <!-- slider  -->
                    <div class="w-full flex flex-row box-row-infor-all-price swiper-container-mobile" id="compare_hosting">
                      <div class="heath w-full flex flex-row swiper-wrapper">
                        <?php
                        if (!empty($column_slides)) {
                          $stt_cloumn = 1;
                          foreach ($column_slides as $key => $slides) {
                            $popular_row = $popular_active === $key ? 'vnx_price_pack_popular_row' : '';
                            if ($key !== 0) {
                              ?>
                              <div class="w-4/12 box-row-infor-price swiper-slide <?php echo $popular_row; ?>">
                                <div class="row-price">
                                  <?php if ($slides == 'yes') { ?>
                                    <i aria-hidden="true" class="vnx_icon_yes <?php echo $settings['yes_icon']['icon']; ?>"></i>
                                  <?php } else if ($slides == 'no') { ?>
                                      <i aria-hidden="true" class="vnx_icon_no <?php echo $settings['no_icon']['icon']; ?>"></i>
                                  <?php } else {
                                    $modified_content = preg_replace('/\+(.+)/', '<span style="color: #FF9038; font-weight: 700;">+$1</span>', $slides);
                                    ?>
                                      <span class="text-sm text-center flex flex-col"><?php echo $modified_content; ?></span>
                                  <?php } ?>
                                </div>
                              </div>
                              <?php
                            }
                            $stt_cloumn++;
                          }
                        }
                        ?>
                      </div>
                    </div>
                    <!-- slider  -->
                    <?php
                  }
                }
                ?>
              </div>
              <!-- title slider  -->
            </div>
            <!-- load more  -->
            <?php
            if ($stt != 1 && count($cycle_row) > 4) {
              $viewmore_class = isset( $subtable_keys[ $stt - 1 ][ 'key' ] ) ? ' ' . $subtable_viewmore_class . $subtable_keys[ $stt - 1 ][ 'key' ] : ' ' . $subtable_viewmore_class;
              ?>
              <div class="vnx_load_more_Center flex flex-row justify-center collapsed">
                <button type="button" class="vnx-button-more toggle-btn<?php echo esc_html( $viewmore_class ); ?>">Xem thêm <i class="fa-solid fa-angles-down"></i></button>
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