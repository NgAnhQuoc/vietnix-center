<?php

use HelperCenter\View;

if ( !isset( $data->settings ) ) {
  if ( current_user_can( 'update_core' ) )
    echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
  return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();

if ( $get_csv[ 'status' ] == 'error' ) {
  if ( current_user_can( 'update_core' ) )
    echo '<div class="vnx_error no_data"><b>' . $get_csv[ 'message' ] . '</div>';
  return;
}

if ( $get_csv[ 'status' ] == 'success' )
  $csv_data = isset( $get_csv[ 'data' ] ) ? $get_csv[ 'data' ] : array();
if ( empty( $csv_data ) ) {
  if ( current_user_can( 'update_core' ) )
    echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html( __FILE__ ) . '</b></div>';
  return;
}

/** ĐĂNG KÝ MỚI*/
define_if_not_defined_Center( 'CSV_TEN_MIEN', 0 );
define_if_not_defined_Center( 'CSV_TOOLTIP', 1 );
define_if_not_defined_Center( 'CSV_TRANG_THAI', 2 );
define_if_not_defined_Center( 'CSV_QUA_TANG', 3 );
define_if_not_defined_Center( 'CSV_GIA_DANG_KI_GOC', 4 );
define_if_not_defined_Center( 'CSV_GIA_DANG_KI_KHUYEN_MAI', 5 );
define_if_not_defined_Center( 'CSV_LE_PHI_DAWNG_KY', 6 );
define_if_not_defined_Center( 'CSV_PHI_DUY_TRI', 7 );
define_if_not_defined_Center( 'CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU', 8 );
define_if_not_defined_Center( 'CSV_VAT', 9 );
/** GIA HẠN */
define_if_not_defined_Center( 'CSV_GIA_HAN', 10 );
define_if_not_defined_Center( 'CSV_PHI_DUY_TRI_GIA_HAN', 11 );
define_if_not_defined_Center( 'CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO', 12 );
define_if_not_defined_Center( 'CSV_VAT_GIA_HAN', 13 );
define_if_not_defined_Center( 'CSV_PHI_CHUYEN_TEN_MIEN', 14 );
define_if_not_defined_Center( 'CSV_LOAI', 15 );
define_if_not_defined_Center( 'VAT_DEFAULT', '10%' );

$tooltip_icon = isset( $settings[ 'tooltip_icon' ] ) && $settings[ 'tooltip_icon' ][ 'library' ] ? $settings[ 'tooltip_icon' ] : '';

$name_table = '';
if ( !isset( $settings[ 'domain_type' ] ) ) {
  if ( current_user_can( 'update_core' ) )
    echo '<div class="vnx_error no_data"><b>Không có domain_type<b/></div>';
  return;
}
$name_table = $settings[ 'domain_type' ] == "vn" ? "Việt Nam" : "Quốc tế";

if ( isset( $settings[ 'domain_register_link' ] ) )
  $data->set_link_attributes( 'domain_register_link', $data->settings[ 'domain_register_link' ] );

if ( isset( $settings[ 'domain_extend_link' ] ) )
  $data->set_link_attributes( 'domain_extend_link', $data->settings[ 'domain_extend_link' ] );

if ( isset( $settings[ 'domain_transfer_link' ] ) )
  $data->set_link_attributes( 'domain_transfer_link', $data->settings[ 'domain_transfer_link' ] );

?>
<div class="w-full md:w-auto  vnx_bg_table h-auto vnx-custom-over-scroll">
  <div class="vnx-custom-w-full-mobile">
    <div class="flex padding_30">
      <div class="flex-grow mr-4 cs-width-column-mobile" style="z-index:5;">
        <div class="whitespace-nowrap flex flex-nowrap  vnx_custom_text_title"><img class="float-left"
            src="https://vietnix.vn/wp-content/uploads/2023/07/domain.png"> Tên miền
          <?php echo $name_table; ?>
        </div>
        <ul class="vnx-bg-f9fbfe">
          <?php foreach ( $csv_data as $key => $value ) :
            $CSV_LOAI = isset( $value[ CSV_LOAI ] ) ? $value[ CSV_LOAI ] : '';
            if ( $key != 0 && ( strtolower( $CSV_LOAI ) == $settings[ 'domain_type' ] ) ) :
              ?>
              <li class="vnx_cus_border_li items-center">
                <span class="nameTLD">
                  <?php echo isset( $value[ CSV_TEN_MIEN ] ) ? $value[ CSV_TEN_MIEN ] : ''; ?>
                </span>
                <?php if ( isset( $value[ CSV_TOOLTIP ] ) && $value[ CSV_TOOLTIP ] ) : ?>
                  <span class="tooltip relative cursor-pointer ml-1">
                    <?php
                    echo $tooltip_icon ? Bricks\Element::render_icon( $tooltip_icon, [ 'vnx_tooltip_icon' ] ) : '<i class="fas fa-circle-question"></i>';
                    ?>
                    <div
                      class="tooltiptext bg-white text-black text-center  px-3 rounded-md absolute w-full custom-width-mobile-tooltiptext">
                      <div class="text-xs text-left font-normal mt-1 w-full px-2.5 py-2.5">
                        <?php echo isset( $value[ CSV_TOOLTIP ] ) ? $value[ CSV_TOOLTIP ] : ''; ?>
                      </div>
                    </div>
                  </span>
                <?php endif; ?>
                <?php if ( isset( $value[ CSV_TRANG_THAI ] ) && $value[ CSV_TRANG_THAI ] ) {
                  $array_status = explode( ",", $value[ CSV_TRANG_THAI ] );
                  $status_0 = isset( $array_status[ 0 ] ) ? $array_status[ 0 ] : '';
                  $status_1 = isset( $array_status[ 1 ] ) ? $array_status[ 1 ] : '';
                  $status_2 = isset( $array_status[ 2 ] ) ? $array_status[ 2 ] : '';
                  if ( strtolower( $status_0 ) == "miễn phí" ) {
                    ?>
                    <div class="free_tooltip ml-auto relative float-right">
                      <img class="free_tooltip_hover"
                        src='https://vietnix.vn/wp-content/uploads/2023/11/free_golobal_domain.svg'>
                      <span class="free_tooltip_data absolute w-60 p-2 bg-white rounded-md text-sm">
                        <?php echo $status_1; ?>
                      </span>
                    </div>
                    <?php
                  } else {
                    echo "<span class='ml-2' style='padding: 5px; color: " . $status_2 . ";  background-color: " . $status_1 . "; border-radius: 3px; '>" . $status_0 . "</span>";
                  }

                }
                if ( isset( $value[ CSV_QUA_TANG ] ) && $value[ CSV_QUA_TANG ] == "TRUE" ) {
                  echo "<img src='https://vietnix.vn/wp-content/uploads/2023/03/Flat.png' style='width: 14px; height: 14px; float: right;'>";
                }
                ?>
              </li>
            <?php endif; endforeach; ?>
        </ul>
      </div>

      <div class="flex-shrink  mr-4" style="width: 20%; z-index: 4;">
        <div class="whitespace-nowrap flex flex-nowrap items-center vnx_custom_text_title">Đăng ký mới</div>
        <ul class="vnx-bg-white">
          <?php
          foreach ( $csv_data as $key => $value ) :
            $CSV_LOAI = isset( $value[ CSV_LOAI ] ) ? $value[ CSV_LOAI ] : '';
            if ( $key != 0 && ( strtolower( $CSV_LOAI ) == $settings[ 'domain_type' ] ) ) :
              ?>
              <li class="vnx_cus_border_li">
                <?php
                $CSV_GIA_DANG_KI_KHUYEN_MAI = isset( $value[ CSV_GIA_DANG_KI_KHUYEN_MAI ] ) ? $value[ CSV_GIA_DANG_KI_KHUYEN_MAI ] : '';
                $CSV_GIA_DANG_KI_GOC = isset( $value[ CSV_GIA_DANG_KI_GOC ] ) ? $value[ CSV_GIA_DANG_KI_GOC ] : '';
                if ( $CSV_GIA_DANG_KI_KHUYEN_MAI ) {
                  echo "<span class='vnx_price_discount mr-2'>" . $CSV_GIA_DANG_KI_KHUYEN_MAI . " đ</span>";
                  echo "<span class='vnx_price_cost'>" . $CSV_GIA_DANG_KI_GOC . "đ</span>";
                } else {
                  echo "<span class='price_cost'>" . $CSV_GIA_DANG_KI_GOC . " đ</span>";
                }

                $CSV_LE_PHI_DAWNG_KY = isset( $value[ CSV_LE_PHI_DAWNG_KY ] ) ? $value[ CSV_LE_PHI_DAWNG_KY ] : '';
                $CSV_PHI_DUY_TRI = isset( $value[ CSV_PHI_DUY_TRI ] ) ? $value[ CSV_PHI_DUY_TRI ] : '';
                $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU = isset( $value[ CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU ] ) ? $value[ CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU ] : '';
                if ( $CSV_LE_PHI_DAWNG_KY || $CSV_PHI_DUY_TRI || $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU ) :
                  ?>
                  <div class="float-right tooltip relative">
                    <?php
                    echo $tooltip_icon ? Bricks\Element::render_icon( $tooltip_icon, [ 'vnx_tooltip_icon' ] ) : '<i class="fas fa-circle-question"></i>';
                    ?>
                    <div
                      class="tooltiptext bg-white text-black text-center  px-3 rounded-md absolute w-full custom-width-mobile">
                      <div class="text-xs text-left font-normal mt-1 w-full">
                        <ul class="vnx_cs_ul_table inside">
                          <li><span>Lệ phí đăng ký</span> <span class="float-right">
                              <?php echo $CSV_LE_PHI_DAWNG_KY ?? 0; ?> đ
                            </span></li>
                          <li><span>Phí duy trì/năm</span> <span class="float-right">
                              <?php echo $CSV_PHI_DUY_TRI ?? 0; ?> đ
                            </span></li>
                          <li><span>Dịch vụ tài khoản quản trị
                              </br>
                              <span class="ml-2">tên miền năm đầu</span>
                            </span> <span class="float-right" style="margin-top: -5px;">
                              <?php echo $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_DAU ?? 0; ?> đ
                            </span></li>
                          <li><span>VAT (
                              <?php echo VAT_DEFAULT; ?>)<br>
                              <small style="font-size: 10px;" class="ml-2">(Thuế dịch vụ)</small>
                            </span> <span class="float-right" style="margin-top: -5px;">
                              <?php echo ( isset( $value[ CSV_VAT ] ) && $value[ CSV_VAT ] ) ? $value[ CSV_VAT ] : 0; ?> đ
                            </span></li>
                        </ul>
                        <ul class="vnx_cs_text_total mb-4">
                          <li>
                            <span class="vnx_cus_text_total">TỔNG</span> <span class="float-right vnx_number_total">
                              <?php echo $CSV_GIA_DANG_KI_GOC ?? 0; ?> đ
                            </span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </li>
              <?php
            endif;
          endforeach;
          ?>
        </ul>
      </div>

      <div class="flex-shrink  mr-4" style="width: 20%; z-index: 2;">
        <div class="whitespace-nowrap flex flex-nowrap items-center vnx_custom_text_title">Gia hạn</div>
        <ul class="vnx-bg-white">
          <?php foreach ( $csv_data as $key => $value ) :
            $CSV_LOAI = isset( $value[ CSV_LOAI ] ) ? $value[ CSV_LOAI ] : '';
            if ( $key != 0 && ( strtolower( $CSV_LOAI ) == $settings[ 'domain_type' ] ) ) :
              $CSV_GIA_HAN = isset( $value[ CSV_GIA_HAN ] ) ? $value[ CSV_GIA_HAN ] : '';
              ?>
              <li class="vnx_cus_border_li">
                <?php
                if ( $CSV_GIA_HAN ) {
                  echo "<span class='price_cost'>" . $CSV_GIA_HAN . " đ</span>";
                }
                $CSV_PHI_DUY_TRI_GIA_HAN = isset( $value[ CSV_PHI_DUY_TRI_GIA_HAN ] ) ? $value[ CSV_PHI_DUY_TRI_GIA_HAN ] : '';
                $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO = isset( $value[ CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO ] ) ? $value[ CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO ] : '';
                $CSV_VAT_GIA_HAN = isset( $value[ CSV_VAT_GIA_HAN ] ) ? $value[ CSV_VAT_GIA_HAN ] : '';

                if ( $CSV_PHI_DUY_TRI_GIA_HAN || $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO || $CSV_VAT_GIA_HAN ) :
                  ?>
                  <div class="float-right tooltip relative">
                    <?php
                    echo $tooltip_icon ? Bricks\Element::render_icon( $tooltip_icon, [ 'vnx_tooltip_icon' ] ) : '<i class="fas fa-circle-question"></i>';
                    ?>
                    <div
                      class="tooltiptext bg-white text-black text-center  px-3 rounded-md absolute w-full custom-width-mobile">
                      <div class="text-xs text-left font-normal mt-1 w-full">
                        <ul class="vnx_cs_ul_table inside">
                          <li>
                            <span>Phí duy trì/năm</span> <span class="float-right">
                              <?php echo $CSV_PHI_DUY_TRI_GIA_HAN ?? 0; ?> đ
                            </span>
                          </li>
                          <li>
                            <span>Dịch vụ tài khoản quản trị
                              </br>
                              <span class="ml-2">tên miền năm tiếp theo</span>
                            </span> <span class="float-right" style="margin-top: -5px;">
                              <?php echo $CSV_DICH_VU_TAI_KHOAN_QUAN_TRI_MIEN_NAM_TIEP_THEO ?? 0; ?> đ
                            </span>
                          </li>
                          <li>
                            <span>VAT (
                              <?php echo VAT_DEFAULT; ?>) <br>
                              <small style="font-size: 10px;" class="ml-2">(Thuế dịch vụ)</small>
                            </span> <span class="float-right" style="margin-top: -5px;">
                              <?php echo $CSV_VAT_GIA_HAN ?? 0; ?> đ
                            </span>
                          </li>
                        </ul>
                        <ul class="vnx_cs_text_total mb-4">
                          <li>
                            <span class="vnx_cus_text_total">TỔNG</span> <span class="float-right vnx_number_total">
                              <?php echo $CSV_GIA_HAN ?? 0; ?> đ
                            </span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </li>
              <?php
            endif;
          endforeach;
          ?>
        </ul>
      </div>
      <div class="flex-shrink" style="width: 20%; z-index: 1; overflow: hidden; border-radius: 0px 0px 12px 12px;">
        <div class="whitespace-nowrap flex flex-nowrap items-center vnx_custom_text_title text-center"><span
            class="float-left">Chuyển về </span>
          <img class="ml-1" src="https://vietnix.vn/wp-content/uploads/2023/07/vietnix_text.png">
        </div>
        <?php
        if ( $settings[ 'domain_type' ] == "vn" ) {
          ?>
          <div class="vnx-bg-white text-center"
            style="align-items: center; display: flex; flex-wrap: nowrap;justify-content: space-evenly; height: 100%;">
            <span><img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/07/Capa_1-1.png">Miễn phí</span>
          </div>
          <?php
        }
        if ( $settings[ 'domain_type' ] == "qt" ) {
          ?>
          <ul class="vnx-bg-white">
            <?php
            foreach ( $csv_data as $key => $value ) :
              $CSV_LOAI = isset( $value[ CSV_LOAI ] ) ? $value[ CSV_LOAI ] : '';
              if ( $key != 0 && ( strtolower( $CSV_LOAI ) == $settings[ 'domain_type' ] ) ) :
                ?>
                <li class="vnx_cus_border_li">
                  <?php
                  $CSV_PHI_CHUYEN_TEN_MIEN = isset( $value[ CSV_PHI_CHUYEN_TEN_MIEN ] ) ? $value[ CSV_PHI_CHUYEN_TEN_MIEN ] : '';
                  if ( $CSV_PHI_CHUYEN_TEN_MIEN ) {
                    echo "<span class='price_cost'>" . $CSV_PHI_CHUYEN_TEN_MIEN . " đ</span>";
                  }
                  ?>
                </li>
                <?php
              endif;
            endforeach;
            ?>
          </ul>
        <?php } ?>
      </div>
    </div>
    <div class="flex padding_30_l_r ">
      <div class="flex-grow " style="width: 40%; "></div>
      <div class="flex-shrink text-center mr-8" style="width: 20%;">
        <a <?php echo $data->render_attributes( 'domain_register_link' ) ?? ' href="#" rel="nofollow"'; ?>
          class="vn-bg-button-subscribe">Đăng ký</a>
      </div>
      <div class="flex-shrink text-center mr-8" style="width: 20%;">
        <a <?php echo $data->render_attributes( 'domain_extend_link' ) ?? ' href="#" rel="nofollow"'; ?>
          class="vn-bg-button-subscribe">Gia hạn</a>
      </div>
      <div class="flex-shrink text-center" style="width: 20%;">
        <a <?php echo $data->render_attributes( 'domain_transfer_link' ) ?? ' href="#" rel="nofollow"'; ?>
          class="vn-bg-button-subscribe">Chuyển tên miền</a>
      </div>
    </div>
  </div>
</div>
<?php
// echo '<pre>';
// print_r( $settings );
// echo '</pre>';
?>