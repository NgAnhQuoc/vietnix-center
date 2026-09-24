<?php
$data = isset($data) ? $data : new stdClass();

define_if_not_defined_Center('CSV_FILE_ROW_GIA_DICH_VU', 3);
define_if_not_defined_Center('CSV_FILE_ROW_DON_VI', 4);
define_if_not_defined_Center('CSV_FILE_ROW_BANG_THONG', 5);
define_if_not_defined_Center('CSV_FILE_ROW_TAN_SO_GOI_TIN', 6);

define_if_not_defined_Center('COLUMN_TITLE', 0);
define_if_not_defined_Center('COLUMN_EXPLAIN', 1);
$settings = isset($data->settings) ? $data->settings : [];
$yes_icon = isset($settings['yes_icon']) ? $settings['yes_icon'] : '';
$no_icon = isset($settings['no_icon']) ? $settings['no_icon'] : '';
$expand_icon = isset($settings['expand_icon']) ? $settings['expand_icon'] : '';
$collapse_icon = isset($settings['collapse_icon']) ? $settings['collapse_icon'] : '';
$firewall_popular = isset($settings['firewall_popular']) ? $settings['firewall_popular'] : '';
$firewall_row_mobile = isset($settings['firewall_row_mobile']) ? $settings['firewall_row_mobile'] : '4';
define_if_not_defined_Center('VALUE_EXPAND_COLLAPSE', $firewall_row_mobile);
$DATA_INFO_0 = isset($data->info[0]) ? $data->info[0] : '';
$COLUMN_TITLE = isset($data->info[COLUMN_TITLE]) ? $data->info[COLUMN_TITLE] : [];

$CSV_FILE_ROW_GIA_DICH_VU = isset($data->info[CSV_FILE_ROW_GIA_DICH_VU]) ? $data->info[CSV_FILE_ROW_GIA_DICH_VU] : [];
$CSV_FILE_ROW_GIA_DICH_VU_TITLE = isset($CSV_FILE_ROW_GIA_DICH_VU[COLUMN_TITLE]) ? $CSV_FILE_ROW_GIA_DICH_VU[COLUMN_TITLE] : '';
$CSV_FILE_ROW_GIA_DICH_VU_EXPLAIN = isset($CSV_FILE_ROW_GIA_DICH_VU[COLUMN_EXPLAIN]) ? $CSV_FILE_ROW_GIA_DICH_VU[COLUMN_EXPLAIN] : '';

$CSV_FILE_ROW_DON_VI = isset($data->info[CSV_FILE_ROW_DON_VI]) ? $data->info[CSV_FILE_ROW_DON_VI] : [];

$CSV_FILE_ROW_BANG_THONG = isset($data->info[CSV_FILE_ROW_BANG_THONG]) ? $data->info[CSV_FILE_ROW_BANG_THONG] : [];
$CSV_FILE_ROW_BANG_THONG_TITLE = isset($CSV_FILE_ROW_BANG_THONG[COLUMN_TITLE]) ? $CSV_FILE_ROW_BANG_THONG[COLUMN_TITLE] : '';
$CSV_FILE_ROW_BANG_THONG_EXPLAIN = isset($CSV_FILE_ROW_BANG_THONG[COLUMN_EXPLAIN]) ? $CSV_FILE_ROW_BANG_THONG[COLUMN_EXPLAIN] : '';

$CSV_FILE_ROW_TAN_SO_GOI_TIN = isset($data->info[CSV_FILE_ROW_TAN_SO_GOI_TIN]) ? $data->info[CSV_FILE_ROW_TAN_SO_GOI_TIN] : [];
$CSV_FILE_ROW_TAN_SO_GOI_TIN_TITLE = isset($CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_TITLE]) ? $CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_TITLE] : '';
$CSV_FILE_ROW_TAN_SO_GOI_TIN_EXPLAIN = isset($CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_EXPLAIN]) ? $CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_EXPLAIN] : '';

$priceService = new VNX_Service_Price_Center();
$csv_data = $data->info;
$result = array();
for ($i = 0; $i < count($csv_data[0]); $i++) {
  $row = array();
  for ($j = 0; $j < count($csv_data); $j++) {
    $row[] = $csv_data[$j][$i];
  }
  $result[] = $row;
}
$boundaries = $priceService->findCycleBoundaries($result[0]);
$key_limit = $boundaries[1][0];
$the_last_row = count($boundaries) - 1;
?>
<div class="text-sm lg:hidden">
  <?php
  for ($index = 2; $index < count($DATA_INFO_0); $index++):
    $relative = isset($COLUMN_TITLE[$index]) && $COLUMN_TITLE[$index] == $firewall_popular ? ' relative' : '';
    ?>
    <div class="rounded-md bg-white">
      <table class="w-full mt-5 rounded-t-md bg-white">
        <tbody>
          <!-- Gói dịch vụ - Giá - URL -->
          <tr>
            <td class="p-3 border-[#F2F2F2] text-lg font-medium text-gray-1">
              <?php
              $array = explode(" | ", $result[0][1]);
              printf($array[0]);
              ?>
            </td>
            <td class="flex center px-5<?php echo $relative; ?>">
              <?php
              if (isset($COLUMN_TITLE[$index]) && $COLUMN_TITLE[$index] == $firewall_popular) {
                echo '<img src="https://vietnix.vn/wp-content/uploads/2023/01/recom.svg" class="absolute right-0 top-0 " />';
              }
              ?>
              <img class="mb-5 mt-5" src="<?php echo isset($data->info[2][$index]) ? $data->info[2][$index] : ''; ?>" />
            </td>
          </tr>
          <!-- /Gói dịch vụ - Giá - URL -->

          <!-- Giá dịch vụ -->
          <tr>
            <td class="px-3 py-5 border-y border-[#F2F2F2] bg-[#F3F6F9]">
              <div class="flex flex-row">
                <div>Giá dịch vụ</div>
                <?php if ($CSV_FILE_ROW_GIA_DICH_VU_EXPLAIN): ?>
                  <!-- Tooltip Start -->
                  <span class="relative flex flex-col items-center group cursor-pointer">
                    <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
                    <span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
                      <span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
                        <?php esc_html_e($CSV_FILE_ROW_GIA_DICH_VU_EXPLAIN); ?>
                      </span>
                      <span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
                    </span>
                  </span>
                  <!-- Tooltip End  -->
                <?php endif; ?>
              </div>
            </td>
            <td class="px-3 py-5 border-y border-[#F2F2F2] text-right">
              <span class="el-custom-text-price">
                <?php echo isset($CSV_FILE_ROW_GIA_DICH_VU[$index]) ? $CSV_FILE_ROW_GIA_DICH_VU[$index] : ''; ?>
              </span>
              </br>
              <span class="text-[#828282]">/
                <?php echo isset($CSV_FILE_ROW_DON_VI[$index]) ? $CSV_FILE_ROW_DON_VI[$index] : ''; ?>
              </span>
            </td>
          </tr>
          <!-- /Giá dịch vụ -->

          <!-- Băng thông -->
          <tr>
            <td class="px-3 py-5 border-y border-[#F2F2F2] bg-[#F3F6F9]">
              <div class="flex flex-row">
                <?php echo $CSV_FILE_ROW_BANG_THONG_TITLE; ?>
                <?php if ($CSV_FILE_ROW_BANG_THONG_EXPLAIN): ?>
                  <!-- Tooltip Start -->
                  <span class="relative flex flex-col items-center group cursor-pointer">
                    <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
                    <span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
                      <span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
                        <?php esc_html_e($CSV_FILE_ROW_BANG_THONG_EXPLAIN); ?>
                      </span>
                      <span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
                    </span>
                  </span>
                  <!-- Tooltip End  -->
                <?php endif; ?>
              </div>
            </td>
            <td class="px-3 py-5 border-y border-[#F2F2F2] font-bold text-right">
              <?php echo isset($CSV_FILE_ROW_BANG_THONG[$index]) ? $CSV_FILE_ROW_BANG_THONG[$index] : ''; ?>
            </td>
          </tr>
          <!-- /Băng thông -->
          <!-- tần số gói tin -->
          <tr>
            <td class="px-3 py-5 border-b border-[#F2F2F2] bg-[#F3F6F9]">
              <div class="flex flex-row break-words">
                <?php echo $CSV_FILE_ROW_TAN_SO_GOI_TIN_TITLE; ?>
                <?php if ($CSV_FILE_ROW_TAN_SO_GOI_TIN_EXPLAIN): ?>
                  <!-- Tooltip Start -->
                  <span class="relative flex flex-col items-center group cursor-pointer">
                    <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
                    <span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
                      <span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
                        <?php esc_html_e($CSV_FILE_ROW_TAN_SO_GOI_TIN_EXPLAIN); ?>
                      </span>
                      <span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
                    </span>
                  </span>
                  <!-- Tooltip End  -->
                <?php endif; ?>
              </div>
            </td>
            <td class="px-3 py-5 border-b border-[#F2F2F2] text-right">
              <?php echo isset($CSV_FILE_ROW_TAN_SO_GOI_TIN[$index]) ? $CSV_FILE_ROW_TAN_SO_GOI_TIN[$index] : ''; ?>
            </td>
          </tr>
          <!-- /Tần số gói tin -->
          <!-- Start - Chống Botnet -->
          <?php
          $data_info = $priceService->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[$index]);
          $data_info_title = $priceService->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[0]);
          $data_info_tool = $priceService->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[1]);
          ?>
          <?php
          foreach ($data_info as $key => $value) {
            $hidden = $key >= VALUE_EXPAND_COLLAPSE ? 'class="default-hidden hidden"' : '';
            ?>
            <tr <?php echo $hidden; ?>>
              <td class="px-3 py-5 border-b border-[#F2F2F2] bg-[#F3F6F9]">
                <div class="flex flex-row break-words">
                  <?php
                  echo $data_info_title[$key];
                  if (!empty($data_info_tool[$key])):
                    ?>
                    <!-- Tooltip Start -->
                    <span class="relative flex flex-col items-center group cursor-pointer">
                      <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
                      <span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
                        <span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
                          <?php esc_html_e($data_info_tool[$key]); ?>
                        </span>
                        <span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
                      </span>
                    </span>
                    <!-- Tooltip End  -->
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-3 py-5 border-b border-[#F2F2F2] text-right font-bold">
                <?php
                $text_value = explode(" | ", $value);
                if (is_array($text_value) && $text_value[1] === "*") {
                  echo $text_value[0];
                } else {
                  if (isset($text_value[0]) && $text_value[0] === '1') {
                    echo $yes_icon ? Bricks\Element::render_icon($yes_icon, ['vnx_icon vnx_icon_yes', 'text-sm', 'inline']) : '<i class="fas fa-check-circle text-[#219653] text-sm"/>';
                  } else {
                    echo $no_icon ? Bricks\Element::render_icon($no_icon, ['vnx_icon vnx_icon_no', 'text-sm', 'inline']) : '<i class="far fa-times-circle text-red-600 text-sm"/>';
                  }
                }
                ?>
              </td>
            </tr>
            <?php
          }
          ?>
          <!-- End - Tùy biến rule theo yêu cầu -->
        </tbody>
      </table>
      <div class="vnx_expand_btn text-center pt-6 pb-3 cursor-pointer rounded-b-md" style="background: #FFFFFF">
        <div class="expand_text">
          <?php
          echo $expand_icon ? Bricks\Element::render_icon($expand_icon, ['vnx_icon vnx_icon_expand', 'inline']) : '<i class="fas fa-angle-double-down"></i>';
          ?>
          Chi tiết
        </div>
        <div class="collapse_text">
          <?php
          echo $collapse_icon ? Bricks\Element::render_icon($collapse_icon, ['vnx_icon vnx_icon_collapse', 'inline']) : '<i class="fas fa-angle-double-up"></i>';
          ?>
          Thu gọn
        </div>
      </div>
      <?php
      $data_price = isset($CSV_FILE_ROW_GIA_DICH_VU[$index]) ? $CSV_FILE_ROW_GIA_DICH_VU[$index] : '';
      $data_period = isset($CSV_FILE_ROW_DON_VI[$index]) ? $CSV_FILE_ROW_DON_VI[$index] : '';
      $data_product_name = 'FIREWALL ' . ($index - 1);
      $data_register = $priceService->getToSearchExcelCompare($boundaries[2][0], $boundaries[2][1], $result[$index]);
      $href = is_array($data_register) ? $data_register[0] : '';
      ?>
      <div class="flex text-center py-3 cursor-pointer bg-[#FFFFFF] rounded-b-md">
        <a class="flex justify-center items-center mb-4 mx-6 w-full h-10 text-white rounded-[4px] f-15 bg-[#38A7FF] hover:underline" rel="nofollow" data-price="<?php echo $data_price; ?>" data-period="<?php echo '1 ' . $data_period; ?>"
          data-product-name="<?php echo $data_product_name; ?>" data-product-category="FIREWALL" href="<?php echo $href; ?>">
          ĐĂNG KÝ
        </a>
      </div>
    </div>
  <?php endfor; ?>
</div>