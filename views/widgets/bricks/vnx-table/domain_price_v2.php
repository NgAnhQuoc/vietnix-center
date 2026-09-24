<?php

use HelperCenter\View;

if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html(__FILE__) . '</div>';
  return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();

if ($get_csv['status'] == 'error') {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>' . $get_csv['message'] . '</div>';
  return;
}

if ($get_csv['status'] == 'success')
  $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
if (empty($csv_data)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html(__FILE__) . '</b></div>';
  return;
}

/** ĐĂNG KÝ MỚI*/
define_if_not_defined_Center('CSV_TEN_MIEN', 0);
define_if_not_defined_Center('CSV_TOOLTIP', 1);
define_if_not_defined_Center('CSV_TRANG_THAI', 2);
define_if_not_defined_Center('CSV_QUA_TANG', 3);
define_if_not_defined_Center('CSV_GIA_DANG_KI_GOC', 4);
define_if_not_defined_Center('CSV_GIA_DANG_KI_KHUYEN_MAI', 5);
define_if_not_defined_Center('CSV_LE_PHI_DAWNG_KY', 6);
define_if_not_defined_Center('CSV_PHI_DUY_TRI', 7);
define_if_not_defined_Center('CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU', 8);
define_if_not_defined_Center('CSV_VAT', 9);
/** GIA HẠN */
define_if_not_defined_Center('CSV_GIA_HAN', 10);
define_if_not_defined_Center('CSV_PHI_DUY_TRI_GIA_HAN', 11);
define_if_not_defined_Center('CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO', 12);
define_if_not_defined_Center('CSV_VAT_GIA_HAN', 13);
define_if_not_defined_Center('CSV_PHI_CHUYEN_TEN_MIEN', 14);
define_if_not_defined_Center('CSV_LOAI', 15);
define_if_not_defined_Center('VAT_DEFAULT_TEXT', $settings['domain_text_vat']);


$tooltip_icon = isset($settings['tooltip_icon']) && $settings['tooltip_icon']['library'] ? $settings['tooltip_icon'] : '';

$name_table = '';
if (!isset($settings['domain_type'])) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có domain_type<b/></div>';
  return;
}
$name_table = $settings['domain_type'] == "vn" ? "Việt Nam" : "Quốc tế";

if (isset($settings['domain_register_link']))
  $data->set_link_attributes('domain_register_link', $data->settings['domain_register_link']);

if (isset($settings['domain_extend_link']))
  $data->set_link_attributes('domain_extend_link', $data->settings['domain_extend_link']);

if (isset($settings['domain_transfer_link']))
  $data->set_link_attributes('domain_transfer_link', $data->settings['domain_transfer_link']);

?>
<div class="w-full  vnx_bg_table h-auto ">
  <div class="brxe-vnx-table domain_price_warp overflow-x-auto relative">

    <table class="brxe-vnx-table domain_price_v2">
      <tr>
        <th class="brxe-vnx-table domain_price_name_table flex gap-3 items-center pb-4 min-w-[480px]">
          <?php
          if ($settings['domain_type'] == "vn") {
            ?>
            <img src="https://vietnix.vn/wp-content/uploads/2024/06/icon.png" height="43" alt="">
            <p class="text-cyan-900 text-2xl font-bold leading-loose">BẢNG GIÁ TÊN MIỀN VIỆT NAM</p>
            <?php
          } ?>

          <?php
          if ($settings['domain_type'] == "qt") {
            ?>
            <img src="https://vietnix.vn/wp-content/uploads/2024/06/ICANN-1.png" height="43" alt="">
            <p class="text-cyan-900 text-2xl font-bold leading-loose">BẢNG GIÁ TÊN MIỀN QUỐC TẾ</p>
            <?php
          } ?>

        </th>
        <th class="min-w-[230px]">
          <div class="text-white text-base font-bold leading-normal py-5   mr-3">ĐĂNG KÝ MỚI</div>
        </th>
        <th class="min-w-[160px]">
          <div class="text-white text-base font-bold leading-normal py-5  mr-3">GIA HẠN</div>
        </th>
        <th class="min-w-[190px]">
          <div class="text-white text-base font-bold leading-normal py-5  ">CHUYỂN VỀ VIETNIX
          </div>
        </th>
      </tr>


      <?php
      $count_row = 0;
      foreach ($csv_data as $key => $value) {
        $CSV_LOAI = isset($value[CSV_LOAI]) ? $value[CSV_LOAI] : '';
        if ($key != 0 && (strtolower($CSV_LOAI) == $settings['domain_type'])) {
          $count_row++;
        }
      }

      ?>

      <?php foreach ($csv_data as $key => $value):

        $CSV_LOAI = isset($value[CSV_LOAI]) ? $value[CSV_LOAI] : '';
        if ($key != 0 && (strtolower($CSV_LOAI) == $settings['domain_type'])):

          ?>
          <tr>
            <!-- -------------------- Tên miền Việt Nam ------------------------ -->

            <td class="pr-3">
              <div class="items-center flex justify-between p-4 h-14"
                style="background:<?php echo ($key % 2) ? 'white' : '#F9F9F9'; ?>">

                <div class="flex gap-2 items-center">
                  <span class="brxe-vnx-table domain_price_name text-gray-600 text-sm font-bold leading-tight">
                    <?php echo isset($value[CSV_TEN_MIEN]) ? $value[CSV_TEN_MIEN] : ''; ?>
                  </span>
                  <?php if (isset($value[CSV_TOOLTIP]) && $value[CSV_TOOLTIP]): ?>
                    <span class="tooltip_tb2 relative cursor-pointer ">
                      <?php
                      echo $tooltip_icon ? Bricks\Element::render_icon($tooltip_icon, ['vnx_tooltip_icon']) : '<i class="fa-thin fa-thin fa-circle-question"></i>';
                      ?>
                      <div
                        class="tooltiptext tooltiptext_price_table2 text-white text-center  p-2 rounded-md absolute w-full shadow-none custom-width-mobile-tooltiptext">
                        <div class="text-xs text-left font-normal w-full">
                          <?php echo isset($value[CSV_TOOLTIP]) ? $value[CSV_TOOLTIP] : ''; ?>
                        </div>
                      </div>
                    </span>
                  <?php endif; ?>


                  <?php if (isset($value[CSV_TRANG_THAI]) && $value[CSV_TRANG_THAI]) {
                    $array_status = explode(",", $value[CSV_TRANG_THAI]);
                    $status_0 = isset($array_status[0]) ? $array_status[0] : '';
                    $status_1 = isset($array_status[1]) ? $array_status[1] : '';
                    $status_2 = isset($array_status[2]) ? $array_status[2] : '';
                    $status_3 = isset($array_status[3]) ? $array_status[3] : '';

                    if (strtolower($status_0) != "miễn phí") {

                      echo "<span class='ml-2 rounded-2xl text-xs' style=' color: " . $status_2 . ";  background-color: " . $status_1 . ";border: 1px solid " . $status_3 . "; padding: 0px 8px '>" . $status_0 . "</span>";
                    }

                  } ?>
                </div>
                <?php


                if (strtolower($status_0) == "miễn phí") {
                  ?>
                  <div class="free_tooltip ml-auto relative float-right">
                    <img class="free_tooltip_hover"
                      src='https://vietnix.vn/wp-content/uploads/2023/11/free_golobal_domain.svg'>
                    <span class="free_tooltip_data absolute w-60 p-2 bg-white rounded-md text-sm">
                      <?php echo $status_1; ?>
                    </span>
                  </div>
                  <?php
                }
                ?>
              </div>
            </td>
            <!-- -------------------- end Tên miền Việt Nam ------------------------ -->

            <!-- -------------------- dang ki moi ------------------------ -->
            <td class="pr-3">
              <div class="items-center flex justify-between p-4 h-14"
                style="background:<?php echo ($key % 2) ? 'white' : '#F9F9F9'; ?>">
                <div class="flex gap-2">
                  <?php
                  $CSV_GIA_DANG_KI_KHUYEN_MAI = isset($value[CSV_GIA_DANG_KI_KHUYEN_MAI]) ? $value[CSV_GIA_DANG_KI_KHUYEN_MAI] : '';
                  $CSV_GIA_DANG_KI_GOC = isset($value[CSV_GIA_DANG_KI_GOC]) ? $value[CSV_GIA_DANG_KI_GOC] : '';

                  if ($CSV_GIA_DANG_KI_KHUYEN_MAI) {
                    echo "<span class='text-orange-400 text-sm font-bold leading-tight'>" . $CSV_GIA_DANG_KI_KHUYEN_MAI . " đ</span>";
                    echo '<div class="text-zinc-400 text-xs font-normal line-through leading-[18px]">' . $CSV_GIA_DANG_KI_GOC . ' đ</div>';
                  } else {
                    echo '<div class="text-gray-600 font-bold  text-sm leading-tight">' . $CSV_GIA_DANG_KI_GOC . ' đ</div>';
                  }

                  $CSV_LE_PHI_DAWNG_KY = isset($value[CSV_LE_PHI_DAWNG_KY]) ? $value[CSV_LE_PHI_DAWNG_KY] : '';
                  $CSV_PHI_DUY_TRI = isset($value[CSV_PHI_DUY_TRI]) ? $value[CSV_PHI_DUY_TRI] : '';
                  $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU = isset($value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU]) ? $value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU] : '';
                  if ($CSV_LE_PHI_DAWNG_KY || $CSV_PHI_DUY_TRI || $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU):
                    ?>

                  </div>
                  <div class="float-right tooltip_tb2 relative">
                    <?php
                    echo $tooltip_icon ? Bricks\Element::render_icon($tooltip_icon, ['vnx_tooltip_icon']) : '<i class="fas fa-thin fa-circle-question"></i>';
                    ?>
                    <div
                      class="tooltiptext <?php if ($key > ($count_row - 5))
                        echo 'tooltiptext_top '; ?> bg-white text-black text-center  px-3 rounded-md absolute w-full custom-width-mobile">
                      <div class="text-xs text-left font-normal mt-1 w-full">
                        <ul class="vnx_cs_ul_table px-3 py-6 flex gap-4 flex-col list-none text-left">
                          <li>
                            <span class="text-gray-600 text-base font-normal m-0 ">Lệ phí đăng ký</span>
                            <span class="float-right text-gray-600 text-base font-normal m-0">
                              <?php echo $CSV_LE_PHI_DAWNG_KY ?? 0; ?> đ
                            </span>
                          </li>
                          <li>
                            <span class="text-gray-600 text-base font-normal m-0">Phí duy trì/năm</span>
                            <span class="float-right text-gray-600 text-base font-normal m-0">
                              <?php echo $CSV_PHI_DUY_TRI ?? 0; ?> đ
                            </span>
                          </li>
                          <li>
                            <span class="text-gray-600 text-base font-normal m-0">Dịch vụ tài khoản quản trị<br> tên miền năm đầu</span>

                            <span
                              class="float-right text-gray-600 text-base font-normal m-0" style="margin-top: -5px;">
                              <?php echo $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU ?? 0; ?> đ
                            </span>

                          <li>
                            <span class="text-gray-600 text-base font-normal m-0">VAT (
                              <?php echo VAT_DEFAULT_TEXT; ?>)<br>
                              <small style="font-size: 10px;" class="text-gray-600 text-base font-normal m-0">(Thuế dịch
                                vụ)</small>
                            </span>
                            <span class="float-right text-gray-600 text-base font-normal m-0" style="margin-top: -5px;">
                              <?php echo (isset($value[CSV_VAT]) && $value[CSV_VAT]) ? $value[CSV_VAT] : 0; ?> đ
                            </span>
                          </li>
                        </ul>
                        <ul class="vnx_cs_text_total_2 mb-4">
                          <li>
                            <div
                              class="h-11 w-full px-2 bg-sky-400/opacity-10 rounded-lg justify-between items-center gap-6 inline-flex">
                              <div class="h-7 justify-center items-center gap-2.5 flex">
                                <div class="grow shrink basis-0 text-gray-600 text-lg font-bold  leading-7">
                                  Tổng</div>
                              </div>
                              <div class="h-7 justify-center items-center gap-2.5 flex">
                                <div class="text-sky-400 text-lg font-bold leading-7">
                                  <?php echo $CSV_GIA_DANG_KI_GOC ?? 0; ?> đ
                                </div>
                              </div>
                            </div>

                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
            </td>
            <!-- --------------------end đăng kí mới ------------------------ -->

            <!-- ------------------------------ gian hạn ----------------------------------  -->
            <?php
            $CSV_LOAI = isset($value[CSV_LOAI]) ? $value[CSV_LOAI] : '';
            if ($key != 0 && (strtolower($CSV_LOAI) == $settings['domain_type'])):
              $CSV_GIA_HAN = isset($value[CSV_GIA_HAN]) ? $value[CSV_GIA_HAN] : '';
              ?>

              <td class="pr-3">
                <div class="items-center flex justify-between p-4 h-14"
                  style="background:<?php echo ($key % 2) ? 'white' : '#F9F9F9'; ?>">
                  <?php
                  if ($CSV_GIA_HAN) {
                    echo '<div class="text-gray-600 font-bold  text-sm leading-tight">' . $CSV_GIA_HAN . ' đ</div>';
                  }
                  $CSV_PHI_DUY_TRI_GIA_HAN = isset($value[CSV_PHI_DUY_TRI_GIA_HAN]) ? $value[CSV_PHI_DUY_TRI_GIA_HAN] : '';
                  $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO = isset($value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO]) ? $value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO] : '';
                  $CSV_VAT_GIA_HAN = isset($value[CSV_VAT_GIA_HAN]) ? $value[CSV_VAT_GIA_HAN] : '';

                  if ($CSV_PHI_DUY_TRI_GIA_HAN || $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO || $CSV_VAT_GIA_HAN):
                    ?>
                    <div class="float-right tooltip_tb2 relative">
                      <?php
                      echo $tooltip_icon ? Bricks\Element::render_icon($tooltip_icon, ['vnx_tooltip_icon']) : '<i class="fa-thin fa-thin fa-circle-question"></i>';
                      ?>
                      <div
                        class="tooltiptext   <?php if ($key > ($count_row - 5))
                          echo 'tooltiptext_top '; ?> bg-white text-black text-center  px-3 rounded-md absolute w-full custom-width-mobile">
                        <div class="text-xs text-left font-normal mt-1 w-full">
                          <ul class="vnx_cs_ul_table  list-none text-left px-3 py-6 flex gap-4 flex-col">
                            <li>
                              <span class="text-gray-600 text-base font-normal m-0">Phí duy trì/năm</span>
                              <span class="float-right text-gray-600 text-base font-normal m-0">
                                <?php echo $CSV_PHI_DUY_TRI_GIA_HAN ?? 0; ?> đ

                              </span>
                            </li>
                            <li>
                              <span class="text-gray-600 text-base font-normal m-0">Dịch vụ tài khoản quản trị
                                </br>
                                <span class=" text-gray-600 text-base font-normal m-0">tên miền năm tiếp
                                  theo</span>
                              </span> <span class="float-right text-gray-600 text-base font-normal m-0"
                                style="margin-top: -5px;">
                                <?php echo $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO ?? 0; ?> đ
                              </span>
                            </li>
                            <li>
                              <span class="text-gray-600 text-base font-normal m-0">VAT (
                                <?php echo VAT_DEFAULT_TEXT; ?>) <br>
                                <small style="font-size: 10px;" class=" text-gray-600 text-base font-normal m-0">(Thuế dịch
                                  vụ)</small>
                              </span> <span class="float-right text-gray-600 text-base font-normal m-0"
                                style="margin-top: -5px;">
                                <?php echo $CSV_VAT_GIA_HAN ?? 0; ?> đ
                              </span>
                            </li>
                          </ul>

                          <ul class="vnx_cs_text_total_2 mb-4">
                            <li>
                              <div
                                class="h-11 w-full px-2 bg-sky-400/opacity-10 rounded-lg justify-between items-center gap-6 inline-flex">
                                <div class="h-7 justify-center items-center gap-2.5 flex">
                                  <div class="grow shrink basis-0 text-gray-600 text-lg leading-7 font-bold">
                                    Tổng</div>
                                </div>
                                <div class="h-7 justify-center items-center gap-2.5 flex">
                                  <div class="text-sky-400 text-lg font-bold leading-7">
                                    <?php echo $CSV_GIA_HAN ?? 0; ?> đ
                                  </div>
                                </div>
                              </div>

                            </li>
                          </ul>

                        </div>
                      </div>
                    </div>
                  <?php endif; ?>
                  </li>
              </td>
              <?php
            endif;
            ?>

            <!-- ------------------------------end gia hạn ----------------------------------  -->

            <!-- -------------------------------------- miễn phí ----------------------------  -->
            <?php
            if ($key == 1 && $settings['domain_type'] == "vn") {
              ?>
              <td class="bg-white" rowspan="<?php echo $count_row ?>">
                <?php
                // Kiểm tra giá trị của $settings['domain_type']
                if ($settings['domain_type'] == "vn") {
                  ?>
                  <div class="text-center" style="">
                    <div>
                      <img alt="" class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/07/Capa_1-1.png">
                      Miễn phí
                    </div>
                    <?php
                }
                ?>
              </td>
              <?php
            }
            ?>


            <?php
            if ($settings['domain_type'] == "qt" && $value[CSV_LOAI] == 'QT') {
              ?>

              <td class="">
                <div class="items-center flex justify-between p-4 h-14"
                  style="background:<?php echo ($key % 2) ? 'white' : '#F9F9F9'; ?>">

                  <?php
                  $CSV_PHI_CHUYEN_TEN_MIEN = isset($value[CSV_PHI_CHUYEN_TEN_MIEN]) ? $value[CSV_PHI_CHUYEN_TEN_MIEN] : '';
                  if ($CSV_PHI_CHUYEN_TEN_MIEN) {
                    echo '<div class="text-gray-600 font-bold  text-sm leading-tight">' . $CSV_PHI_CHUYEN_TEN_MIEN . ' đ</div>';
                  }
                  ?>
                  </li>
              </td>

            <?php } ?>

            <!-- --------------------------------------end miến phí ----------------------------  -->


          </tr>
        <?php endif; endforeach; ?>

      <tr>
        <td>
          <div class="mr-3 p-4 " style="background:<?php echo ($count_row  % 2) ? '#F9F9F9' : 'white'; ?>">
            <div class="h-10">

            </div>
          </div>
        </td>
        <td>
          <div class="mr-3 p-4 " style="background:<?php echo ($count_row % 2) ? '#F9F9F9' : 'white'; ?>">
            <a <?php echo $data->render_attributes('domain_register_link') ?? ' href="#" rel="nofollow"'; ?>
              class="vn-bg-button-subscribe text-center hover:bg-[#2D86CC]">Đăng ký</a>
          </div>
        </td>
        <td>
          <div class="mr-3 p-4" style="background:<?php echo ($count_row % 2) ? '#F9F9F9' : 'white'; ?>">
            <a <?php echo $data->render_attributes('domain_extend_link') ?? ' href="#" rel="nofollow"'; ?>
              class="vn-bg-button-subscribe text-center hover:bg-[#2D86CC]">Gia hạn</a>
          </div>
        </td>
        <td>
          <div class="p-4" style="background:<?php echo ($count_row % 2) ? '#F9F9F9' : 'white'; ?>">
            <a <?php echo $data->render_attributes('domain_transfer_link') ?? ' href="#" rel="nofollow"'; ?>
              class="vn-bg-button-subscribe text-center hover:bg-[#2D86CC]">Chuyển tên miền</a>
          </div>
        </td>
      </tr>
    </table>

  </div>
</div>
