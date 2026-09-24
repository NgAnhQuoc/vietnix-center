<?php
$data = isset($data) ? $data : new stdClass();

define_if_not_defined_Center('CSV_FILE_COLUMN_CHU_KY', 0);
define_if_not_defined_Center('CSV_FILE_COLUMN_GOI_DICH_VU', 1);
define_if_not_defined_Center('CSV_FILE_COLUMN_NHAN', 2);
define_if_not_defined_Center('CSV_FILE_COLUMN_TT_TOI_THIEU', 3);
define_if_not_defined_Center('CSV_FILE_COLUMN_CPU', 4);
define_if_not_defined_Center('CSV_FILE_COLUMN_RAM', 5);
define_if_not_defined_Center('CSV_FILE_COLUMN_O_CUNG', 6);
define_if_not_defined_Center('CSV_FILE_COLUMN_TANG_DIRECT_ADMIN', 7);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_GOC', 8);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIAM', 9);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_SAU_GIAM', 10);
define_if_not_defined_Center('CSV_FILE_COLUMN_DON_VI', 11);
define_if_not_defined_Center('CSV_FILE_COLUMN_TAM_TINH', 12);
define_if_not_defined_Center('CSV_FILE_COLUMN_URL_DANG_KY', 13);
define_if_not_defined_Center('CSV_FILE_COLUMN_LOAI_DICH_VU', 14);
define_if_not_defined_Center('CSV_FILE_COLUMN_KM', 15);

$hide_col_list = isset($data->hide_col_list) ? $data->hide_col_list : '';
$chu_ky = isset($data->header[CSV_FILE_COLUMN_CHU_KY]) ? $data->header[CSV_FILE_COLUMN_CHU_KY] : '';
$goi_dich_vu = isset($data->header[CSV_FILE_COLUMN_GOI_DICH_VU]) ? $data->header[CSV_FILE_COLUMN_GOI_DICH_VU] : '';
$file_col_CPU = isset($data->header[CSV_FILE_COLUMN_CPU]) ? $data->header[CSV_FILE_COLUMN_CPU] : '';
$file_col_RAM = isset($data->header[CSV_FILE_COLUMN_RAM]) ? $data->header[CSV_FILE_COLUMN_RAM] : '';
$file_col_O_Cung = isset($data->header[CSV_FILE_COLUMN_O_CUNG]) ? $data->header[CSV_FILE_COLUMN_O_CUNG] : '';
$file_col_Direct = isset($data->header[CSV_FILE_COLUMN_TANG_DIRECT_ADMIN]) ? $data->header[CSV_FILE_COLUMN_TANG_DIRECT_ADMIN] : '';
$gia_goc = isset($data->header[CSV_FILE_COLUMN_GIA_GOC]) ? $data->header[CSV_FILE_COLUMN_GIA_GOC] : '';
foreach ($data->row as $key => $item):
  $CSV_FILE_COLUMN_NHAN = isset($item[CSV_FILE_COLUMN_NHAN]) ? $item[CSV_FILE_COLUMN_NHAN] : '';
  $CSV_FILE_COLUMN_GOI_DICH_VU = isset($item[CSV_FILE_COLUMN_GOI_DICH_VU]) ? $item[CSV_FILE_COLUMN_GOI_DICH_VU] : '';
  $CSV_FILE_COLUMN_LOAI_DICH_VU = isset($item[CSV_FILE_COLUMN_LOAI_DICH_VU]) ? $item[CSV_FILE_COLUMN_LOAI_DICH_VU] : '';
  if (!$CSV_FILE_COLUMN_LOAI_DICH_VU)
    $CSV_FILE_COLUMN_LOAI_DICH_VU = 'nvme';
  $CSV_FILE_COLUMN_CPU = isset($item[CSV_FILE_COLUMN_CPU]) ? $item[CSV_FILE_COLUMN_CPU] : '';
  $CSV_FILE_COLUMN_RAM = isset($item[CSV_FILE_COLUMN_RAM]) ? $item[CSV_FILE_COLUMN_RAM] : '';
  $CSV_FILE_COLUMN_O_CUNG = isset($item[CSV_FILE_COLUMN_O_CUNG]) ? $item[CSV_FILE_COLUMN_O_CUNG] : '';
  $CSV_FILE_COLUMN_TANG_DIRECT_ADMIN = isset($item[CSV_FILE_COLUMN_TANG_DIRECT_ADMIN]) ? $item[CSV_FILE_COLUMN_TANG_DIRECT_ADMIN] : '';
  $CSV_FILE_COLUMN_GIAM = isset($item[CSV_FILE_COLUMN_GIAM]) ? $item[CSV_FILE_COLUMN_GIAM] : '';
  $CSV_FILE_COLUMN_KM = isset($item[CSV_FILE_COLUMN_KM]) ? $item[CSV_FILE_COLUMN_KM] : '';
  $CSV_FILE_COLUMN_GIA_SAU_GIAM = isset($item[CSV_FILE_COLUMN_GIA_SAU_GIAM]) ? $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] : '';
  $CSV_FILE_COLUMN_GIAM = isset($item[CSV_FILE_COLUMN_GIAM]) ? $item[CSV_FILE_COLUMN_GIAM] : '';
  $CSV_FILE_COLUMN_GIA_GOC = isset($item[CSV_FILE_COLUMN_GIA_GOC]) ? $item[CSV_FILE_COLUMN_GIA_GOC] : '';
  $CSV_FILE_COLUMN_DON_VI = isset($item[CSV_FILE_COLUMN_DON_VI]) ? $item[CSV_FILE_COLUMN_DON_VI] : '';
  $CSV_FILE_COLUMN_TAM_TINH = isset($item[CSV_FILE_COLUMN_TAM_TINH]) ? $item[CSV_FILE_COLUMN_TAM_TINH] : '';
  $CSV_FILE_COLUMN_URL_DANG_KY = isset($item[CSV_FILE_COLUMN_URL_DANG_KY]) ? $item[CSV_FILE_COLUMN_URL_DANG_KY] : '#';
  $CSV_FILE_COLUMN_TT_TOI_THIEU = isset($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) ? $item[CSV_FILE_COLUMN_TT_TOI_THIEU] : '';
  $opacity = $CSV_FILE_COLUMN_TT_TOI_THIEU ? ' opacity-50' : '';
  $bgr = ($key % 2 === 0) ? ' bg-gray-100' : '';
  ?>
  <table class="vnx_table_mobile w-full lg:hidden text-base rounded-md mb-5<?php echo $opacity; ?>">
    <colgroup>
      <col width="50%" />
      <col width="50%" />
    </colgroup>
    <tbody>
      <tr>
        <td class="p-3 font-bold pl-3 pt-3 flex">
          <div class="flex flex-col justify-center text-[#38A7FF] text-lg">
            <?php
            if ($CSV_FILE_COLUMN_NHAN):
              $label = explode(", ", trim($CSV_FILE_COLUMN_NHAN));
              $bgr = isset($label[1]) ? ' style="background-color:' . $label[1] . '"' : '';
              ?>
              <div class="text-white text-xs font-medium leading-[14px] p-1 mb-2 rounded-[3px] inline w-fit" <?php echo $bgr; ?>>
                <?php echo isset($label[0]) ? $label[0] : ''; ?>
              </div>
              <?php
            endif;
            echo $CSV_FILE_COLUMN_GOI_DICH_VU;
            ?>
          </div>
        </td>
        <td class="p-3 text-end pr-3 pt-3">
          <?php
          if ($CSV_FILE_COLUMN_GIAM && !$CSV_FILE_COLUMN_KM) {
            ?>
            <div class="flex flex-row gap-2 justify-end items-center">
              <span class="el-custom-text-price leading-6 font-bold text-lg">
                <?php echo $CSV_FILE_COLUMN_GIA_SAU_GIAM; ?>
              </span>
              <span class="text-xs px-0.5 py-1 el-custom-text-discount">
                <?php echo $CSV_FILE_COLUMN_GIAM; ?>
              </span>
            </div>
            <div>
              <span class="el-custom-text-price-base leading-5 text-base ">
                <?php echo $CSV_FILE_COLUMN_GIA_GOC; ?>
              </span>
              <span class="text-base text-[#828282]">
                <?php echo '/' . $CSV_FILE_COLUMN_DON_VI; ?>
              </span>
            </div>
            <?php
          } else {
            ?>
            <span class="el-custom-text-price leading-5 font-bold text-base">
              <?php echo $CSV_FILE_COLUMN_GIA_GOC; ?>
            </span>
            <span class="text-base text-[#828282]">
              <?php echo '/' . $CSV_FILE_COLUMN_DON_VI; ?>
            </span>
            <?php
          }
          ?>
          <?php if ($CSV_FILE_COLUMN_KM): ?>
            <div class="flex flex-row bg-[#F8E2E2] rounded-md border border-[#FF0000] justify-center text-xs font-bold px-2 py-1 mt-1">
              <img src="https://vietnix.vn/wp-content/uploads/2023/05/price-table-gif.png" />
              <span>+
                <?php echo $CSV_FILE_COLUMN_KM; ?>
              </span>
            </div>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="w-full pb-3">
          <div class="line h-px bg-[#E0E0E0] ml-3 mr-3"></div>
        </td>
      </tr>
      <tr>
        <?php if (!in_array('4', $hide_col_list)): ?>
          <td class="p-3 text-center pl-6">
            <div class="flex flex-row items-center leading-8 text-[#013A52]">
              <?php
              $alt = '';
              $src = '';
              switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                case 'cloud_server':
                  $alt = 'icon CPU bảng giá Cloud Server';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-cloud-server.svg';
                  break;

                case 'vps_server':
                  $alt = 'icon CPU bảng giá VPS Server';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-vps-server.svg';
                  break;

                case 'cloud_vps':
                  $alt = 'icon cpu bảng giá VPS Cheap';
                  $src = 'https://vietnix.vn/wp-content/uploads/2023/10/icon-cpu-bang-gia-vps-cheap.svg';
                  break;

                case 'vps_gpu':
                  $alt = 'icon CPU bảng giá VPS GPU';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/09/microchip-1-1-1.svg';
                  break;

                default:
                  $alt = 'icon CPU bảng giá VPS NVMe';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/09/microchip-1-1-1.svg';
                  break;
              }
              echo '<img class="h-5 w-6 mr-2" alt="' . esc_attr($alt) . '" src="' . esc_attr($src) . '" />';
              echo $CSV_FILE_COLUMN_CPU;
              ?>
            </div>
          </td>
        <?php endif;
        if (!in_array('5', $hide_col_list)):
          ?>
          <td class="p-3 flex text-center pr-6">
            <div class="flex flex-row items-center leading-8 justify-end text-[#013A52]">
              <?php
              $alt = '';
              $src = '';
              switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                case 'cloud_server':
                  $alt = 'icon RAM bảng giá Cloud Server';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-cloud-server.svg';
                  break;

                case 'vps_server':
                  $alt = 'icon RAM bảng giá VPS Server';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-vps-server.svg';
                  break;

                case 'cloud_vps':
                  $alt = 'icon RAM bảng giá VPS Cheap';
                  $src = 'https://vietnix.vn/wp-content/uploads/2023/10/icon-ram-bang-gia-vps-cheap.svg';
                  break;

                case 'vps_gpu':
                  $alt = 'icon RAM bảng giá VPS GPU';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/09/memory-1-1.svg';
                  break;

                default:
                  $alt = 'icon RAM bảng giá VPS NVMe';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/09/memory-1-1.svg';
                  break;
              }
              echo '<img class="h-4 w-6 mr-2" alt="' . esc_attr($alt) . '" src="' . esc_attr($src) . '" />';
              echo $CSV_FILE_COLUMN_RAM;
              ?>
            </div>
          </td>
        <?php endif; ?>
      </tr>
      <tr>
        <?php if (!in_array('6', $hide_col_list)): ?>
          <td class="p-3 text-center pl-6">
            <div class="flex flex-row items-center leading-8 text-[#013A52]">
              <?php
              $alt = '';
              $src = '';
              switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                case 'cloud_server':
                  $alt = 'icon ổ cứng SSD bảng giá Cloud Server';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-o-cung-ssd-bang-gia-cloud-server.svg';
                  break;

                case 'vps_server':
                  $alt = 'icon ổ cứng SSD bảng giá VPS Server';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-o-cung-ssd-bang-gia-vps-server.svg';
                  break;

                case 'cloud_vps':
                  $alt = 'icon ổ cứng SSD bảng giá VPS Cheap';
                  $src = 'https://vietnix.vn/wp-content/uploads/2023/10/icon-o-cung-ssd-bang-gia-vps-cheap.svg';
                  break;

                case 'vps_gpu':
                  $alt = 'icon ổ cứng SSD bảng giá VPS GPU';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/09/hdd-1-1.svg';
                  break;

                default:
                  $alt = 'icon ổ cứng SSD bảng giá VPS NVMe';
                  $src = 'https://vietnix.vn/wp-content/uploads/2021/09/hdd-1-1.svg';
                  break;
              }
              echo '<img class="h-4 w-6 mr-2" alt="' . esc_attr($alt) . '" src="' . esc_attr($src) . '" />';
              echo $CSV_FILE_COLUMN_O_CUNG;
              ?>
            </div>
          </td>
        <?php endif;
        if (!in_array('7', $hide_col_list)):
          ?>
          <td class="p-3 flex pr-6">
            <div class="flex flex-row items-center leading-6 justify-end text-[#013A52]">
              <?php
              if ($CSV_FILE_COLUMN_TANG_DIRECT_ADMIN == '1') {

                switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {

                  case 'vps_gpu':
                    echo $CSV_FILE_COLUMN_TANG_DIRECT_ADMIN;
                    break;

                  case 'cloud_server':
                    echo '<img class="h-4 w-6 mr-2" alt="Tặng DirectAdmin khi đăng ký Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tang-directadmin-khi-dang-ky-cloud-server.svg" />';
                    break;

                  case 'vps_server':
                    echo '<img class="h-4 w-6 mr-2" alt="Tặng DirectAdmin khi đăng ký VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tang-directadmin-khi-dang-ky-vps-server.svg" />';
                    break;

                  case 'cloud_vps':
                    echo '<img class="h-4 w-6 mr-2" alt="Tặng DirectAdmin khi đăng ký VPS Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2021/08/times2-1-1.svg" />';
                    break;

                  default:
                    echo '<img class="h-4 w-6 mr-2" alt="Tặng DirectAdmin khi đăng ký VPS NVMe" src="https://vietnix.vn/wp-content/uploads/2021/08/times2-1-1.svg" />';
                    break;
                }
              } else {

                switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {

                  case 'vps_gpu':
                    echo '<img class="h-4 w-6 mr-2" src="https://vietnix.vn/wp-content/uploads/2022/05/microchip.svg" alt="icon GPU" />';
                    echo $CSV_FILE_COLUMN_TANG_DIRECT_ADMIN;
                    break;

                  case 'cloud_server':
                    echo '<img class="h-4 w-6 mr-2" alt="Không tặng DirectAdmin Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/khong-tang-DirectAdmin-Cloud-Server.svg" />';
                    break;

                  case 'vps_server':
                    echo '<img class="h-4 w-6 mr-2" alt="Không tặng DirectAdmin VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/khong-tang-DirectAdmin-Cloud-Server.svg" />';
                    break;

                  case 'cloud_vps':
                    echo '<img class="h-4 w-6 mr-2" alt="Không tặng DirectAdmin VPS Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2021/08/times-1.svg" />';
                    break;

                  default:
                    echo '<img class="h-4 w-6 mr-2" src="https://vietnix.vn/wp-content/uploads/2021/08/times-1.svg" alt="icon không tặng DirectAdmin" />';
                    break;
                }
              }
              if ($CSV_FILE_COLUMN_LOAI_DICH_VU !== 'vps_gpu')
                echo $file_col_Direct;
              ?>
            </div>
          </td>
        <?php endif; ?>
      </tr>
      <tr>
        <td colspan="2" class="text-center px-6 pt-3 pb-3">
          <?php
          if ($CSV_FILE_COLUMN_TT_TOI_THIEU) {
            ?>
            <span class="avalible-text text-base font-bold">Áp dụng từ
              <?php echo $CSV_FILE_COLUMN_TT_TOI_THIEU; ?> tháng
            </span>
            <?php
          } else {
            ?>
            <div class="flex flex-row justify-between items-center">
              <a class="px-5 py-2 el-custom-btn-register-mobile rounded-md flex justify-center vnx-btn-conversion w-11/12 font-medium" rel="nofollow" data-price="<?php echo $CSV_FILE_COLUMN_TAM_TINH; ?>" data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>"
                data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $item[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>" href="<?php echo $CSV_FILE_COLUMN_URL_DANG_KY; ?>">
                Đăng ký
              </a>
              <div class="relative icon-estimating mobile ml-2.5">
                <?php
                if (!$CSV_FILE_COLUMN_TT_TOI_THIEU) {
                  switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {

                    case 'cloud_server':
                      echo '<img class="h-8 cursor-pointer icon-estimating" alt="Tag Price Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-cloud-server.svg" />';
                      break;

                    case 'vps_server':
                      echo '<img class="h-8 cursor-pointer icon-estimating" alt="Tag Price VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-vps-server.svg" />';
                      break;

                    default:
                      echo '<img class="h-8 cursor-pointer icon-estimating" src="https://vietnix.vn/wp-content/uploads/2021/09/tag-type1.svg" alt="icon tam tinh" />';
                      break;
                  }
                  ?>
                  <div class="absolute bg-white w-48 text-left estimating-cost py-2 px-4 rounded-lg z-10">
                    <span class="el-custom-text-price-title font-bold">Tạm tính</span>
                    <span class="el-custom-text-price leading-5 font-bold">
                      <?php echo $item[CSV_FILE_COLUMN_TAM_TINH]; ?>
                    </span>
                  </div>
                </div>
                <?php
                }
                ?>
            </div>
            </div>
            <?php
          }
          ?>
        </td>
      </tr>
    </tbody>
  </table>
  <?php
endforeach;
?>