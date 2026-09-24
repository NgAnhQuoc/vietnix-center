<?php
$data = isset( $data ) ? $data : new stdClass();
if ( !isset( $data->row ) || empty( $data->row ) || !isset( $data->header ) || !isset( $data->class ) )
  return;
$super = $data->class;

$LABEL = $super->findIndexInObject_Center( $data->header, 'Label' );
$GIA = $super->findIndexInObject_Center( $data->header, 'Giá' );
$GIAM = $super->findIndexInObject_Center( $data->header, 'Giảm' );
$GIA_GIAM = $super->findIndexInObject_Center( $data->header, 'Giá Giảm' );
$DON_VI = $super->findIndexInObject_Center( $data->header, 'Đơn vị' );
$CPU = $super->findIndexInObject_Center( $data->header, 'CPU' );
$NVME = $super->findIndexInObject_Center( $data->header, 'Dung lượng NVMe' );
$EXTRA_CAPACITY = $super->findIndexInObject_Center( $data->header, 'Dung lượng thêm' );
$TRUY_CAP_CAO_DIEM = $super->findIndexInObject_Center( $data->header, 'Lưu lượng truy cập (Tháng)' );
$BANG_THONG = $super->findIndexInObject_Center( $data->header, 'Băng thông (Không giới hạn)' );
$SUB_DOMAIN = $super->findIndexInObject_Center( $data->header, 'Subdomain (Không giới hạn)' );
$MYSQL = $super->findIndexInObject_Center( $data->header, 'MySQL Database (Không giới hạn)' );
$EMAIL = $super->findIndexInObject_Center( $data->header, 'Email Account (Không giới hạn)' );
$FTP = $super->findIndexInObject_Center( $data->header, 'FTP Account (Không giới hạn)' );
$KHU_VUC_THU_NGHIEM = $super->findIndexInObject_Center( $data->header, 'Khu vực thử nghiệm' );
$DOMAIN_CHINH = $super->findIndexInObject_Center( $data->header, 'Domain chính' );
$BACKUP = $super->findIndexInObject_Center( $data->header, 'Backup dữ liệu (Hàng ngày)' );
$RAM = $super->findIndexInObject_Center( $data->header, 'RAM' );
$IOPS = $super->findIndexInObject_Center( $data->header, 'IOPS' );
$DISK_I_O = $super->findIndexInObject_Center( $data->header, 'DISK I/O' );
$ENTRY_PRS = $super->findIndexInObject_Center( $data->header, 'Entry Processes' );
$NUM_PRS = $super->findIndexInObject_Center( $data->header, 'Number of Process' );
$ANTI_DDOS = $super->findIndexInObject_Center( $data->header, 'Anti DDoS toàn diện' );
$LITESPEED = $super->findIndexInObject_Center( $data->header, 'LiteSpeed Webserver' );
$SHELL = $super->findIndexInObject_Center( $data->header, 'Shell Access' );
$LSCACHE = $super->findIndexInObject_Center( $data->header, 'LScache' );
$CLOUDLINUX = $super->findIndexInObject_Center( $data->header, 'CloudLinux' );
$FREESSL = $super->findIndexInObject_Center( $data->header, 'Free SSL' );
$IMUNIFY360 = $super->findIndexInObject_Center( $data->header, 'Imunify360' );
$TANG_THEME = $super->findIndexInObject_Center( $data->header, 'Tặng Theme & Plugin' );
$MEMCACHED = $super->findIndexInObject_Center( $data->header, 'Memcached Socket' );
$REDIS = $super->findIndexInObject_Center( $data->header, 'Redis Socket' );
$JETBACKUP = $super->findIndexInObject_Center( $data->header, 'Jetbackup' );
$URL = $super->findIndexInObject_Center( $data->header, 'URL' );
$TONG = $super->findIndexInObject_Center( $data->header, 'Tổng tạm tính' );
$CHUKY = $super->findIndexInObject_Center( $data->header, 'Chu kỳ' );

$expand_icon = isset( $settings[ 'expand_icon' ] ) ? $settings[ 'expand_icon' ] : '';
$collapse_icon = isset( $settings[ 'collapse_icon' ] ) ? $settings[ 'collapse_icon' ] : '';

$header_array = [];
foreach ( $data->row as $value ) {
  if ( isset( $value[ 1 ] ) && !in_array( $value[ 1 ], $header_array ) ) {
    $header_array[] = $value[ 1 ];
  }
}

$check_row_1 = [ $BANG_THONG, $SUB_DOMAIN, $MYSQL, $EMAIL, $FTP, $ANTI_DDOS, $LITESPEED, $SHELL, $LSCACHE, $CLOUDLINUX, $FREESSL, $IMUNIFY360, $TANG_THEME, $MEMCACHED, $REDIS, $JETBACKUP ];
$text_row_1 = [ $CPU, $RAM, $TRUY_CAP_CAO_DIEM, $BACKUP, $KHU_VUC_THU_NGHIEM, $DOMAIN_CHINH, $IOPS, $DISK_I_O, $ENTRY_PRS, $NUM_PRS ];
// print_r($data->header);  
foreach ( $data->row as $key => $value ) : ?>
  <div class="bg-white vnx-wph-compare px-3">
    <table class="w-full mt-5 rounded-t-md bg-white">
      <tbody class="flex flex-col w-full overflow-hidden vnx_wp_host_mobile vnx-fixed-height">
        <!-- Gói dịch vụ - Giá - URL -->
        <tr class="flex flex-row justify-between">
          <td class="py-3 border-[#F2F2F2] text-lg font-bold text-[#013A52]">Thông số kỹ thuật</td>
          <td class="py-3 border-[#F2F2F2] text-lg font-bold text-[#38A7FF] text-right vnx-price-name">
            <?= isset( $value[ 1 ] ) ? $value[ 1 ] : ''; ?>
          </td>
        </tr>
        <!-- /Gói dịch vụ - Giá - URL -->
        <!-- Giá dịch vụ -->
        <tr class="flex flex-row justify-between border-b border-[#F2F2F2]">
          <td class="py-5">
            <div class="flex flex-row">
              <div>Giá dịch vụ</div>
            </div>
          </td>
          <td class="py-5 text-right">
            <span class="el-custom-text-price text-base font-bold">
              <?php echo isset( $value[ $GIA_GIAM ] ) ? $value[ $GIA_GIAM ] : ''; ?>
            </span><span class="text-white bg-[#EB5757] rounded ml-2 px-1.5 py-0.5">-
              <?php echo isset( $value[ $GIAM ] ) ? $value[ $GIAM ] : ''; ?>
            </span>
            </br>
            <span class="line-through text-xs font-nomal text-[#828282]">
              <?php echo isset( $value[ $GIA ] ) ? $value[ $GIA ] : ''; ?>
            </span><span class="text-[#828282] text-xs font-nomal"> /
              <?php echo isset( $value[ $DON_VI ] ) ? $value[ $DON_VI ] : '' ?>
            </span>
          </td>
        </tr>
        <!-- /Giá dịch vụ -->
        <!-- Ổ cứng NVMe -->
        <tr class="flex flex-row justify-between border-b border-[#F2F2F2]">
          <td class="py-2.5">
            <div class="flex flex-row">
              <div>Ổ cứng NVMe</div>
            </div>
          </td>
          <td class="py-2.5 text-right">
            <span class="text-[#4F4F4F] text-base font-bold">
              <?php echo isset( $value[ $NVME ] ) ? $value[ $NVME ] : ''; ?> <span class="text-[#FF9038]"> <?php echo ($value[ $EXTRA_CAPACITY ]) ? ' + ' . $value[ $EXTRA_CAPACITY ] : '' ?></span>
            </span>
          </td>
        </tr>
        <!-- /Ổ cứng NVMe -->
        <!-- loop text -->
        <?php foreach ( $text_row_1 as $text ) :
          if ( isset( $data->header[ $text ] ) ) :
            ?>
            <tr class="flex flex-row justify-between border-b border-[#F2F2F2]">
              <td class="py-2.5">
                <div class="flex flex-row">
                  <div>
                    <?= $data->header[ $text ] ?>
                  </div>
                </div>
              </td>
              <td class="py-2.5 text-right">
                <span class="text-[#4F4F4F] text-base font-nomal">
                  <?php echo isset( $value[ $text ] ) ? $value[ $text ] : ''; ?>
                </span>
              </td>
            </tr>
          <?php endif;
        endforeach; ?>
        <!-- /loop text -->
        <!-- loop check -->
        <?php foreach ( $check_row_1 as $text ) :
          if ( isset( $data->header[ $text ] ) ) :
            ?>
            <tr class="flex flex-row justify-between border-b border-[#F2F2F2]">
              <td class="py-2.5">
                <div class="flex flex-row">
                  <div>
                    <?= $data->header[ $text ] ?>
                  </div>
                </div>
              </td>
              <td class="py-2.5 text-right flex items-center">
                <span class="text-[#4F4F4F] text-base font-nomal">
                  <?php echo ( isset( $value[ $text ] ) && $value[ $text ] == 'TRUE' ) ? '<img class="ml-auto" src="https://vietnix.vn/wp-content/uploads/2023/08/Check.svg" alt="icon check">' : '<i class="far fa-times-circle text-red-600 text-sm" />' ?>
                </span>
              </td>
            </tr>
          <?php endif;
        endforeach; ?>
        <!-- /loop check -->
      </tbody>
    </table>
    <div class="text-center pt-6 pb-3 vnx-custom-btn-expand cursor-pointer rounded-b-md" style="background: #FFFFFF">
      <span class="expand_text">
        <?php
        echo $expand_icon ? Bricks\Element::render_icon( $expand_icon, [ 'vnx_icon vnx_icon_expand', 'inline' ] ) : '<i class="fas fa-angle-double-down"></i>';
        ?>
        Chi tiết
      </span>
      <span class="collapse_text">
        <?php
        echo $collapse_icon ? Bricks\Element::render_icon( $collapse_icon, [ 'vnx_icon vnx_icon_collapse', 'inline' ] ) : '<i class="fas fa-angle-double-up"></i>';
        ?>
        Thu gọn
      </span>
    </div>
    <div class="flex text-center py-3 cursor-pointer bg-[#FFFFFF] rounded-b-md">
      <a class="flex justify-center items-center text-base font-medium mb-4 mx-6 w-full h-10 text-[#38A7FF] rounded-[4px] f-15 bg-[#E8F0FA]"
        rel="nofollow" data-price="<?php echo isset( $value[ $TONG ] ) ? $value[ $TONG ] : ''; ?>"
        data-period="<?php echo isset( $value[ $CHUKY ] ) ? $value[ $CHUKY ] : ''; ?>"
        data-product-name="<?php echo isset( $value[ 1 ] ) ? $value[ 1 ] : ''; ?>"
        data-product-category="<?php echo "WORDPRESS HOSTING"; ?>"
        href="<?php echo isset( $value[ $URL ] ) ? $value[ $URL ] : '#'; ?>">
        ĐĂNG KÝ
      </a>
    </div>
  </div>
<?php endforeach; ?>