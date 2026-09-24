<?php

use HelperCenter\View;

if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html(__FILE__) . '</div>';
  return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();
$domain_tag_image = isset($settings['domain_tag_image']) && $settings['domain_tag_image']['url'] ? $settings['domain_tag_image'] : '';
$type_table=  isset($settings['domain_type']) ? $settings['domain_type'] : '';
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
define_if_not_defined_Center('VAT_DEFAULT', $settings['domain_text_vat']);

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
<div class="w-full vnx_bg_table h-auto ">
  <div class="domain_price_warp relative">
    <table class="header_table">
      <thead>
        <tr>
          <th class="brxe-vnx-table min-w-[418px]">
            <div class="domain_price_name_table">
              <?php
              if ($settings['domain_type'] == "vn") {
                ?>
                <img src="<?php echo $domain_tag_image['url']; ?>" alt="">
                <p>BẢNG GIÁ TÊN MIỀN VIỆT NAM</p>
                <?php
              } ?>

              <?php
              if ($settings['domain_type'] == "qt") {
                ?>
                <img src="<?php echo $domain_tag_image['url']; ?>" alt="">
                <p>BẢNG GIÁ TÊN MIỀN QUỐC TẾ</p>
                <?php
              } ?>
            </div>
          </th>
          <th class="min-w-[240px] max-w-[240px] px-2">
            <a <?php echo $data->render_attributes('domain_register_link') ?? ' href="#" rel="nofollow"'; ?> class="vnx_bg_button"> ĐĂNG KÝ MỚI</a>
          </th>
          <th class="min-w-[240px] max-w-[240px] px-2">
            <a <?php echo $data->render_attributes('domain_extend_link') ?? ' href="#" rel="nofollow"'; ?> class="vnx_bg_button">GIA HẠN</a>
          </th>
          <th class="min-w-[240px] max-w-[240px] px-2">
            <a <?php echo $data->render_attributes('domain_transfer_link') ?? ' href="#" rel="nofollow"'; ?> class="vnx_bg_button">CHUYỂN VỀ VIETNIX</a>
          </th>
        </tr>
      </thead>
    </table>
    <div class="body_wrap <?php echo $type_table == "vn" ? "vnx_bg_table_vn" : "vnx_bg_table_qt"; ?>">
      <table class="body_table">
        <tbody>
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
                <td class="px-2 row_name">
                  <div class="items-center flex justify-between w-full gap-1">
                    <div class="flex gap-1 items-center">
                      <span class="brxe-vnx-table domain_price_name">
                        <?php echo isset($value[CSV_TEN_MIEN]) ? $value[CSV_TEN_MIEN] : ''; ?>
                      </span>
                      <?php if (isset($value[CSV_TOOLTIP]) && $value[CSV_TOOLTIP]): ?>
                        <span class="tooltip_tb2 relative cursor-pointer ">
                          <?php
                          echo $tooltip_icon ? Bricks\Element::render_icon($tooltip_icon, ['vnx_tooltip_icon']) : '<i class="fa-thin fa-thin fa-circle-question"></i>';
                          ?>
                          <div class="tooltiptext tooltiptext_price_table2 custom-width-mobile-tooltiptext">
                            <div class="text">
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
                          echo "<span class='text_name' style=' color: " . $status_2 . ";  background-color: " . $status_1 . ";border: 1px solid " . $status_3 . "; padding: 0px 8px '>" . $status_0 . "</span>";
                        }
                      }

                      if (isset($value[CSV_QUA_TANG]) && $value[CSV_QUA_TANG]) {
                        $list_qua_tang = explode("|", $value[CSV_QUA_TANG]);
                        foreach ($list_qua_tang as $qua_tang) {
                          $qua_tang = trim($qua_tang);
                          if ($qua_tang) {
                            echo "<img src='" . esc_url($qua_tang) . "' alt='Quà tặng' class='qua-tang-icon' style='margin-left: 10px;' />";
                          }
                        }
                      }
                      ?>
                    </div>
                    <?php
                    if (strtolower($status_0) == "miễn phí") {
                      ?>
                      <div class="free_tooltip ml-auto relative float-right">
                        <img class="free_tooltip_hover" src='https://vietnix.vn/wp-content/uploads/2023/11/free_golobal_domain.svg'>
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
                <td class="px-2 row row_dk_moi">
                  <div class="row_price">
                    <div class="flex gap-2">
                      <?php
                      $CSV_GIA_DANG_KI_KHUYEN_MAI = isset($value[CSV_GIA_DANG_KI_KHUYEN_MAI]) ? $value[CSV_GIA_DANG_KI_KHUYEN_MAI] : '';
                      $CSV_GIA_DANG_KI_GOC = isset($value[CSV_GIA_DANG_KI_GOC]) ? $value[CSV_GIA_DANG_KI_GOC] : '';
                      if ($CSV_GIA_DANG_KI_KHUYEN_MAI) {
                        echo '<div class="text_price_old">' . $CSV_GIA_DANG_KI_GOC . 'đ</div>';
                        echo "<span class='text_price_new'>" . $CSV_GIA_DANG_KI_KHUYEN_MAI . "đ</span>";
                      } else {
                        echo '<div class="text_price_original">' . $CSV_GIA_DANG_KI_GOC . 'đ</div>';
                      }

                      $CSV_LE_PHI_DAWNG_KY = isset($value[CSV_LE_PHI_DAWNG_KY]) ? $value[CSV_LE_PHI_DAWNG_KY] : '';
                      $CSV_PHI_DUY_TRI = isset($value[CSV_PHI_DUY_TRI]) ? $value[CSV_PHI_DUY_TRI] : '';
                      $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU = isset($value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU]) ? $value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU] : '';
                      if ($CSV_LE_PHI_DAWNG_KY || $CSV_PHI_DUY_TRI || $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU):
                        ?>

                      </div>
                      <div class="float-right tooltip_tb2 relative flex justify-center">
                        <?php
                        echo $tooltip_icon ? Bricks\Element::render_icon($tooltip_icon, ['vnx_tooltip_icon']) : '<i class="fas fa-thin fa-circle-question"></i>';
                        ?>
                        <div class="tooltiptext justify-center <?php if ($key > ($count_row - 5))
                          echo 'tooltiptext_top '; ?>custom-width-mobile">
                          <ul class="vnx_cs_ul_table flex gap-3 flex-col list-none text-left">
                            <li>
                              <span class="text_infor">Lệ phí đăng ký</span>
                              <span class="float-right text_infor">
                                <?php echo $CSV_LE_PHI_DAWNG_KY ?? 0; ?>đ
                              </span>
                            </li>
                            <li>
                              <span class="text_infor">Phí duy trì/năm</span>
                              <span class="float-right text_infor">
                                <?php echo $CSV_PHI_DUY_TRI ?? 0; ?>đ
                              </span>
                            </li>
                            <li>
                              <span class="text_infor">Dịch vụ tài khoản quản trị<br> tên miền năm đầu</span>
                              <span class="float-right text_infor">
                                <?php echo $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU ?? 0; ?>đ
                              </span>
                            </li>
                            <li>
                              <span class="text_infor">VAT (
                                <?php echo VAT_DEFAULT; ?>)<br>
                                <em class="text_infor">(Thuế dịch
                                  vụ)</em>
                              </span>
                              <span class="float-right text_infor">
                                <?php echo (isset($value[CSV_VAT]) && $value[CSV_VAT]) ? $value[CSV_VAT] : 0; ?>đ
                              </span>
                            </li>
                          </ul>
                          <ul class="vnx_cs_text_total_2">
                            <li>
                              <div class="w-full justify-between items-center flex">
                                <div class="justify-center items-center flex">
                                  <div class="total_text">
                                    Tổng</div>
                                </div>
                                <div class="justify-center items-center flex">
                                  <div class="total_price">
                                    <?php echo $CSV_GIA_DANG_KI_GOC ?? 0; ?>đ
                                  </div>
                                </div>
                              </div>
                            </li>
                          </ul>
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
                  <td class="px-2 row row_gia_han">
                    <div class="row_price">
                      <?php
                      if ($CSV_GIA_HAN) {
                        echo '<div class="text_price_original">' . $CSV_GIA_HAN . 'đ</div>';
                      }
                      $CSV_PHI_DUY_TRI_GIA_HAN = isset($value[CSV_PHI_DUY_TRI_GIA_HAN]) ? $value[CSV_PHI_DUY_TRI_GIA_HAN] : '';
                      $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO = isset($value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO]) ? $value[CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO] : '';
                      $CSV_VAT_GIA_HAN = isset($value[CSV_VAT_GIA_HAN]) ? $value[CSV_VAT_GIA_HAN] : '';
                      if ($CSV_PHI_DUY_TRI_GIA_HAN || $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO || $CSV_VAT_GIA_HAN):
                        ?>
                        <div class="float-right tooltip_tb2 relative flex justify-center">
                          <?php
                          echo $tooltip_icon ? Bricks\Element::render_icon($tooltip_icon, ['vnx_tooltip_icon']) : '<i class="fa-thin fa-thin fa-circle-question"></i>';
                          ?>
                          <div class="tooltiptext <?php if ($key > ($count_row - 5))
                            echo 'tooltiptext_top '; ?> custom-width-mobile">
                            <ul class="vnx_cs_ul_table flex gap-3 flex-col list-none text-left">
                              <li>
                                <span class="text_infor">Phí duy trì/năm</span>
                                <span class="float-right text_infor">
                                  <?php echo $CSV_PHI_DUY_TRI_GIA_HAN ?? 0; ?>đ
                                </span>
                              </li>
                              <li>
                                <span class="text_infor">Dịch vụ tài khoản quản trị
                                  </br>
                                  <span class=" text_infor">tên miền năm tiếp
                                    theo</span>
                                </span> <span class="float-right text_infor">
                                  <?php echo $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO ?? 0; ?>đ
                                </span>
                              </li>
                              <li>
                                <span class="text_infor">VAT (
                                  <?php echo VAT_DEFAULT; ?>) <br>
                                  <em class=" text_infor">(Thuế dịch
                                    vụ)</em>
                                </span>
                                <span class="float-right text_infor">
                                  <?php echo $CSV_VAT_GIA_HAN ?? 0; ?>đ
                                </span>
                              </li>
                            </ul>
                            <ul class="vnx_cs_text_total_2">
                              <li>
                                <div class="w-full justify-between items-center flex">
                                  <div class="justify-center items-center flex">
                                    <div class="total_text">
                                      Tổng</div>
                                  </div>
                                  <div class="justify-center items-center flex">
                                    <div class="total_price">
                                      <?php echo $CSV_GIA_HAN ?? 0; ?>đ
                                    </div>
                                  </div>
                                </div>
                              </li>
                            </ul>
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
                if ($settings['domain_type'] == "vn") {
                  ?>
                  <td class="row row_chuyen_ten_mien">
                    <?php
                    // Kiểm tra giá trị của $settings['domain_type']
                    if ($settings['domain_type'] == "vn") {
                      ?>
                      <div class="text-center row_price">
                        <div class="text_price_original">
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
                  <td class="px-2 row row_chuyen_ten_mien">
                    <div class="row_price">
                      <?php
                      $CSV_PHI_CHUYEN_TEN_MIEN = isset($value[CSV_PHI_CHUYEN_TEN_MIEN]) ? $value[CSV_PHI_CHUYEN_TEN_MIEN] : '';
                      if ($CSV_PHI_CHUYEN_TEN_MIEN) {
                        echo '<div class="text_price_original">' . $CSV_PHI_CHUYEN_TEN_MIEN . 'đ</div>';
                      }
                      ?>
                      </li>
                  </td>
                <?php } ?>
                <!-- --------------------------------------end miến phí ----------------------------  -->
              </tr>
            <?php endif; endforeach; ?>
          <tr class="last_row">
            <td class="w-full" colspan="1" style="
    border-top: 1px solid #C0C0C2;">
              <div class="text_infor justify-start">
                <button class="vnx_more_button"> <span> Xem thêm </span> <i class="fas fa-angle-down"></i></button> 
                <button class="vnx_more_button"> <span> Thu gọn </span> <i class="fas fa-angle-up"></i></button> 
              </div>
            </td>
             <td class="w-full" colspan="3" style="
    border-top: 1px solid #C0C0C2;">
              <div class="text_infor justify-end">
                <p class="float-right"> Bảng giá đã bao gồm VAT</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>