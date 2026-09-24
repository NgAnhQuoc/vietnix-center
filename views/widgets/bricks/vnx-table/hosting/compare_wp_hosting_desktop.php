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

$header_array = [];
foreach ( $data->row as $value ) {
  if ( isset( $value[ 1 ] ) && !in_array( $value[ 1 ], $header_array ) ) {
    $header_array[] = $value[ 1 ];
  }
}

$check_row_1 = [ $BANG_THONG, $SUB_DOMAIN, $MYSQL, $EMAIL, $FTP ];
$text_row_2 = [ $BACKUP, $RAM, $KHU_VUC_THU_NGHIEM, $DOMAIN_CHINH, $IOPS, $DISK_I_O, $ENTRY_PRS, $NUM_PRS ];
$check_row_2 = [ $ANTI_DDOS, $LITESPEED, $SHELL, $LSCACHE, $CLOUDLINUX, $FREESSL, $IMUNIFY360, $TANG_THEME, $MEMCACHED, $REDIS, $JETBACKUP ];
?>

<table class="w-full text-sm">

  <thead class="block border-[#F1F1F1] border rounded-tl-md rounded-tr-md">
    <tr class="bg-white">
      <th class="px-5 text-left text-[#333] font-medium text-lg rounded-tl-md bg-[#F3F6F9] border-[#F1F1F1]"
        style="color: #013A52; width:25%;">
        Thông số kỹ thuật
      </th>
      <?php foreach ( $header_array as $key => $value ) : ?>
        <th class="text-center leading-6 relative border-l align-top">
          <div class="flex flex-col items-center justify-center gap-6 px-3.5 py-9 border-[#F1F1F1]">
            <?php if ( isset( $data->row[ $key ][ $LABEL ] ) && $data->row[ $key ][ $LABEL ] ) ?>
            <span class="absolute wph-label text-white">
              <?= isset( $data->row[ $key ][ $LABEL ] ) ? $data->row[ $key ][ $LABEL ] : ''; ?>
            </span>
            <p class="font-medium text-[#38A7FF] text-sm not-italic">
              <?php echo $value ?>
            </p>
            <p class="p-2.5 text-[#EB5757] bg-[#EB5757]/10 rounded-md not-italic text-xs font-bold">
              Ưu đãi
              <?php echo isset( $data->row[ $key ][ $GIAM ] ) ? $data->row[ $key ][ $GIAM ] : ''; ?>
            </p>
            <p class="text-[#F2994A] text-lg font-black not-italic">
              <?php echo isset( $data->row[ $key ][ $GIA_GIAM ] ) ? $data->row[ $key ][ $GIA_GIAM ] : ''; ?>
              <span class="font-normal text-[#828282]" style="font-size: 8px">
                /
                <?php echo isset( $data->row[ $key ][ $DON_VI ] ) ? $data->row[ $key ][ $DON_VI ] : ''; ?>
              </span>
            </p>
          </div>
        </th>
      <?php endforeach; ?>
    </tr>
  </thead>

  <tbody class="overflow-y-scroll block hide_scroll vnx-wph-height">
    <!-- Thông số kỹ thuật -->
    <!-- CPU -->
    <tr class="bg-white border-t border-[#F1F1F1] border-b flex">
      <td class="bg-[#F3F6F9] " style="width:25%">
        <div class="flex-1 h-full flex flex-row p-5 text-[#333] relative flex items-center">
          <?php echo isset( $data->header[ $CPU ] ) ? $data->header[ $CPU ] : ''; ?>
          <?php
          $CPU_string = isset( $data->row[ 0 ][ $CPU + 1 ] ) ? $super->convertStringToArray_Center( $data->row[ 0 ][ $CPU + 1 ] ) : '';
          if ( count( $CPU_string ) >= 3 ) :
            $tool_tip_arr = $CPU_string;
            ?>
            <span class=" flex flex-col group cursor-pointer center">
              <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
              <span class="absolute w-52 top-10 left-2 flex flex-col items-center mb-6 group-hover:flex hidden">
                <span
                  class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#fff] text-[#525666] shadow-lg p-2.5 w-full flex gap-4 flex-col">
                  <p class="text-[#525666] font-semibold text-xs">
                    <?= $tool_tip_arr[ 0 ] ?>
                  </p>
                  <p class="font-normal text-xs text-[#525666]">
                    <?= $tool_tip_arr[ 1 ] ?>
                  </p>
                  <a href="<?= $tool_tip_arr[ 2 ] ?>" rel="nofollow" class="font-normal text-xs text-[#38A7FF]">Xem thêm
                    <i class="fas fa-arrow-right ml-2"></i></a>
                </span>
              </span>
            </span>
            <?php
          endif;
          ?>

        </div>
      </td>
      <?php foreach ( $data->row as $key => $value ) : ?>
        <td style="width:12.5%"
          class="p-5 border-l border-[#F1F1F1] text-center font-bold <?php echo $key === count( $data->row ) - 1 ? 'border-r' : ''; ?>">
          <?php echo isset( $value[ $CPU ] ) ? $value[ $CPU ] : ''; ?>
        </td>
      <?php endforeach; ?>
    </tr>
    <!-- /CPU -->
    <!-- NVMe -->
    <tr class="bg-white border-t border-[#F1F1F1] border-b flex">
      <td class="bg-[#F3F6F9] " style="width:25%">
        <div class="flex-1 h-full flex flex-row p-5 text-[#333] relative flex items-center">
          <?php echo isset( $data->header[ $NVME ] ) ? $data->header[ $NVME ] : ''; ?>
          <?php
          $NVME_string = isset( $data->row[ 0 ][ $NVME + 1 ] ) ? $super->convertStringToArray_Center( $data->row[ 0 ][ $NVME + 1 ] ) : '';
          if ( count( $NVME_string ) >= 3 ) :
            $tool_tip_arr = $NVME_string;
            ?>
            <span class=" flex flex-col group cursor-pointer center">
              <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
              <span class="absolute w-52 top-10 left-2 flex flex-col items-center mb-6 group-hover:flex hidden">
                <span
                  class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#fff] text-[#525666] shadow-lg p-2.5 w-full flex gap-4 flex-col">
                  <p class="text-[#525666] font-semibold text-xs">
                    <?= $tool_tip_arr[ 0 ] ?>
                  </p>
                  <p class="font-normal text-xs text-[#525666]">
                    <?= $tool_tip_arr[ 1 ] ?>
                  </p>
                  <a href="<?= $tool_tip_arr[ 2 ] ?>" rel="nofollow" class="font-normal text-xs text-[#38A7FF]">Xem thêm
                    <i class="fas fa-arrow-right ml-2"></i></a>
                </span>
              </span>
            </span>
            <?php
          endif;
          ?>

        </div>
      </td>
      <?php foreach ( $data->row as $key => $value ) : ?>
        <td style="width:12.5%" class="py-5 px-2 text-nowrap border-l border-[#F1F1F1] text-center font-bold <?php if ( $key === count( $data->row ) - 1 ) {
          echo 'border-r';
        } ?>">
          <?php echo isset( $value[ $NVME ] ) ? $value[ $NVME ] : ''; ?> <span class="text-[#FF9038]"> <?php echo ($value[ $EXTRA_CAPACITY ]) ? ' + ' . $value[ $EXTRA_CAPACITY ] : '' ?></span>
        </td>
      <?php endforeach; ?>
    </tr>
    <!-- /NVMe -->
    <!-- Truy cập lúc cao điểm -->
    <tr class="bg-white border-t border-[#F1F1F1] border-b flex items-center">
      <td class="bg-[#F3F6F9] " style="width:25%">
        <div class="flex-1 h-full flex flex-row p-5 text-[#333] relative flex items-center">
          <?php echo isset( $data->header[ $TRUY_CAP_CAO_DIEM ] ) ? $data->header[ $TRUY_CAP_CAO_DIEM ] : ''; ?>
          <?php
          $TRUY_CAP_CAO_DIEM_string = isset( $data->row[ 0 ][ $TRUY_CAP_CAO_DIEM + 1 ] ) ? $super->convertStringToArray_Center( $data->row[ 0 ][ $TRUY_CAP_CAO_DIEM + 1 ] ) : '';
          if ( count( $TRUY_CAP_CAO_DIEM_string ) >= 3 ) :
            $tool_tip_arr = $TRUY_CAP_CAO_DIEM_string;
            ?>

            <span class=" flex flex-col group cursor-pointer center">
              <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
              <span class="absolute w-52 top-10 left-2 flex flex-col items-center mb-6 group-hover:flex hidden">
                <span
                  class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#fff] text-[#525666] shadow-lg p-2.5 w-full flex gap-4 flex-col">
                  <p class="text-[#525666] font-semibold text-xs">
                    <?= $tool_tip_arr[ 0 ] ?>
                  </p>
                  <p class="font-normal text-xs text-[#525666]">
                    <?= $tool_tip_arr[ 1 ] ?>
                  </p>
                  <a href="<?= $tool_tip_arr[ 2 ] ?>" rel="nofollow" class="font-normal text-xs text-[#38A7FF]">Xem thêm
                    <i class="fas fa-arrow-right ml-2"></i></a>
                </span>
              </span>
            </span>
            <?php
          endif;
          ?>
        </div>
      </td>
      <?php foreach ( $data->row as $key => $value ) : ?>
        <td style="width:12.5%" class="px-1 py-5 border-l border-[#F1F1F1] text-center font-bold <?php if ( $key === count( $data->row ) - 1 ) {
          echo 'border-r';
        } ?>">
          <?php echo isset( $value[ $TRUY_CAP_CAO_DIEM ] ) ? $value[ $TRUY_CAP_CAO_DIEM ] : ''; ?>
        </td>
      <?php endforeach; ?>
    </tr>
    <!-- /Truy cập lúc cao điểm -->

    <!-- check 1 -->
    <?php foreach ( $data->header as $key => $value ) :
      if ( in_array( $key, $check_row_1 ) ) :
        $col = $key;
        if ( $data->header[ $col ] == 'Chu kỳ' )
          continue;
        ?>
        <tr class="bg-white border-t border-[#F1F1F1] border-b flex">
          <td class="bg-[#F3F6F9] " style="width:25%">
            <div class="flex-1 h-full flex flex-row p-5 text-[#333] relative flex items-center">
              <?php echo $data->header[ $col ]; ?>
              <?php
              $col_string = isset( $data->row[ 0 ][ $col + 1 ] ) ? $super->convertStringToArray_Center( $data->row[ 0 ][ $col + 1 ] ) : '';
              if ( count( $col_string ) >= 3 ) :
                $tool_tip_arr = $col_string;
                ?>
                <span class=" flex flex-col group cursor-pointer center">
                  <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
                  <span class="absolute w-52 top-10 left-2 flex flex-col items-center mb-6 group-hover:flex hidden">
                    <span
                      class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#fff] text-[#525666] shadow-lg p-2.5 w-full flex gap-4 flex-col">
                      <p class="text-[#525666] font-semibold text-xs">
                        <?= isset( $tool_tip_arr[ 0 ] ) ? $tool_tip_arr[ 0 ] : ''; ?>
                      </p>
                      <p class="font-normal text-xs text-[#525666]">
                        <?= isset( $tool_tip_arr[ 1 ] ) ? $tool_tip_arr[ 1 ] : ''; ?>
                      </p>
                      <a href="<?= isset( $tool_tip_arr[ 2 ] ) ? $tool_tip_arr[ 2 ] : '#' ?>" rel="nofollow"
                        class="font-normal text-xs text-[#38A7FF]">Xem thêm <i class="fas fa-arrow-right ml-2"></i></a>
                    </span>
                  </span>
                </span>
              <?php endif; ?>
            </div>
          </td>
          <?php foreach ( $data->row as $key2 => $value ) : ?>
            <td style="width:12.5%" class="p-5 border-l border-[#F1F1F1] text-center font-bold flex <?php if ( $key2 === count( $data->row ) - 1 ) {
              echo 'border-r';
            } ?>">
              <?php echo ( $value[ $col ] == 'TRUE' ) ? '<img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/08/Check.svg" alt="icon check">' : '<i class="far fa-times-circle text-red-600 text-sm" />' ?>
            </td>
          <?php endforeach; ?>
        </tr>
        <?php
      endif;
    endforeach; ?>
    <!-- /check 1 -->

    <!-- text-2 -->
    <?php foreach ( $data->header as $key => $value ) :
      if ( in_array( $key, $text_row_2 ) ) :
        $col = $key;
        if ( $data->header[ $col ] == 'Chu kỳ' ) {
          continue;
        }
        ?>
        <tr class="bg-white border-t border-[#F1F1F1] border-b flex">
          <td class="bg-[#F3F6F9] " style="width:25%">
            <div class="flex-1 h-full flex flex-row p-5 text-[#333] relative flex items-center">
              <?php echo $data->header[ $col ] ?>
              <?php
              $col_string = isset( $data->row[ 0 ][ $col + 1 ] ) ? $super->convertStringToArray_Center( $data->row[ 0 ][ $col + 1 ] ) : '';
              if ( count( $col_string ) >= 3 ) :
                $tool_tip_arr = $col_string;
                ?>
                <span class=" flex flex-col group cursor-pointer center">
                  <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
                  <span class="absolute w-52 top-10 left-2 flex flex-col items-center mb-6 group-hover:flex hidden">
                    <span
                      class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#fff] text-[#525666] shadow-lg p-2.5 w-full flex gap-4 flex-col">
                      <p class="text-[#525666] font-semibold text-xs">
                        <?= isset( $tool_tip_arr[ 0 ] ) ? $tool_tip_arr[ 0 ] : ''; ?>
                      </p>
                      <p class="font-normal text-xs text-[#525666]">
                        <?= isset( $tool_tip_arr[ 1 ] ) ? $tool_tip_arr[ 1 ] : ''; ?>
                      </p>
                      <a href="<?= isset( $tool_tip_arr[ 2 ] ) ? $tool_tip_arr[ 2 ] : '#' ?>" rel="nofollow"
                        class="font-normal text-xs text-[#38A7FF]">Xem thêm <i class="fas fa-arrow-right ml-2"></i></a>
                    </span>
                  </span>
                </span>
              <?php endif; ?>
            </div>
          </td>
          <?php foreach ( $data->row as $key2 => $value ) : ?>
            <td style="width:12.5%" class="p-5 border-l border-[#F1F1F1] text-center font-bold <?php if ( $key2 === count( $data->row ) - 1 ) {
              echo 'border-r';
            } ?>">
              <?php echo $value[ $col ] ?>
            </td>
          <?php endforeach; ?>
        </tr>
        <?php
      endif;
    endforeach; ?>
    <!-- /text-2 -->
    <!-- check 2 -->
    <?php foreach ( $data->header as $key => $value ) :

      if ( in_array( $key, $check_row_2 ) ) :
        $col = $key;
        if ( $data->header[ $col ] == 'Chu kỳ' ) {
          continue;
        }
        ?>
        <tr class="bg-white border-t border-[#F1F1F1] border-b flex">
          <td class="bg-[#F3F6F9] " style="width:25%">
            <div class="flex-1 h-full flex flex-row p-5 text-[#333] relative flex items-center">
              <?php echo $data->header[ $col ] ?>
              <?php
              $col_string = isset( $data->row[ 1 ][ $col + 1 ] ) ? $super->convertStringToArray_Center( $data->row[ 1 ][ $col + 1 ] ) : '';
              if ( $col_string && count( $col_string ) >= 3 ) :
                $tool_tip_arr = $col_string;
                ?>
                <span class=" flex flex-col group cursor-pointer center">
                  <i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
                  <span
                    class="absolute w-52 <?= ( ( count( $data->header ) - 1 ) < $key + 6 ) ? 'bottom-3.5' : 'top-10' ?> left-2 flex flex-col items-center mb-6 group-hover:flex hidden">
                    <span
                      class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#fff] text-[#525666] shadow-lg p-2.5 w-full flex gap-4 flex-col">
                      <p class="text-[#525666] font-semibold text-xs">
                        <?= isset( $tool_tip_arr[ 0 ] ) ? $tool_tip_arr[ 0 ] : ''; ?>
                      </p>
                      <p class="font-normal text-xs text-[#525666]">
                        <?= isset( $tool_tip_arr[ 1 ] ) ? $tool_tip_arr[ 1 ] : ''; ?>
                      </p>
                      <a href="<?= isset( $tool_tip_arr[ 2 ] ) ? $tool_tip_arr[ 2 ] : '#' ?>" rel="nofollow"
                        class="font-normal text-xs text-[#38A7FF]">Xem thêm <i class="fas fa-arrow-right ml-2"></i></a>
                    </span>
                  </span>
                </span>
              <?php endif; ?>
            </div>
          </td>
          <?php foreach ( $data->row as $key2 => $value ) : ?>
            <td style="width:12.5%" class="p-5 border-l border-[#F1F1F1] text-center font-bold flex <?php if ( $key2 === count( $data->row ) - 1 ) {
              echo 'border-r';
            } ?>">
              <?php echo ( $value[ $col ] == 'TRUE' ) ? '<img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/08/Check.svg" alt="icon check">' : '<i class="far fa-times-circle text-red-600 text-sm" />' ?>
            </td>
          <?php endforeach; ?>
        </tr>
        <?php
      endif;
    endforeach; ?>
    <!-- /check 2 -->

  </tbody>

  <thead class="block">
    <!-- BUTTON -->
    <tr class="bg-white flex w-full mt-4">
      <th class="px-3 pr-4 pl-7" style="width:25%">
        <div class="flex-1 h-full flex flex-row p-5 text-[#333] relative flex items-center">

        </div>
      </th>
      <?php foreach ( $data->row as $key => $value ) : ?>
        <th style="width:12.5%" class="py-5 text-center font-bold">
          <a href="<?php echo isset( $value[ $URL ] ) ? $value[ $URL ] : '#' ?>" rel="nofollow"
            data-price="<?php echo isset( $value[ $TONG ] ) ? $value[ $TONG ] : ''; ?>"
            data-period="<?php echo isset( $value[ $CHUKY ] ) ? $value[ $CHUKY ] : ''; ?>"
            data-product-name="<?php echo isset( $header_array[ $key ] ) ? $header_array[ $key ] : ''; ?>"
            data-product-category="<?php echo "WORDPRESS HOSTING"; ?>"
            class="text-white bg-[#38A7FF] font-medium rounded-lg text-sm px-4 py-4">Đăng ký ngay</a>
        </th>
      <?php endforeach; ?>
    </tr>
    <!-- /BUTTON -->
  </thead>
</table>