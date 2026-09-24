<?php
$data = isset($data) ? $data : new stdClass();
define_if_not_defined_Center('CSV_FILE_COLUMN_CHU_KY', 0);
define_if_not_defined_Center('CSV_FILE_COLUMN_GOI_DICH_VU', 1);
define_if_not_defined_Center('CSV_FILE_COLUMN_TT_TOI_THIEU', 2);
define_if_not_defined_Center('CSV_FILE_COLUMN_CPU', 3);
define_if_not_defined_Center('CSV_FILE_COLUMN_RAM', 4);
define_if_not_defined_Center('CSV_FILE_COLUMN_O_CUNG', 5);
define_if_not_defined_Center('CSV_FILE_COLUMN_BANG_THONG', 6);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_GOC', 7);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIAM', 8);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_SAU_GIAM', 9);
define_if_not_defined_Center('CSV_FILE_COLUMN_DON_VI', 10);
define_if_not_defined_Center('CSV_FILE_COLUMN_TAM_TINH', 11);
define_if_not_defined_Center('CSV_FILE_COLUMN_URL_DANG_KY', 12);
?>

<table class="w-full text-sm hidden lg:table">
  <colgroup>
    <col width="11%" />
    <col width="15%" />
    <col width="9%" />
    <col width="18%" />
    <col width="15%" />
    <col width="15%" />
    <col width="5%" />
    <col width="13%" />
  </colgroup>
  <thread>
    <tr class="border text-base">
      <th class="p-3 text-left"><?php echo $data->header[CSV_FILE_COLUMN_GOI_DICH_VU] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_CPU] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_RAM] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_O_CUNG] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_BANG_THONG] ?></th>
      <th class="p-3 whitespace-nowrap"><?php echo $data->header[CSV_FILE_COLUMN_GIA_GOC] ?></th>
      <th class="p-3"></th>
      <th class="p-3"></th>
    </tr>
  </thread>
  <tbody>
    <?php foreach ($data->row as $key => $item) : ?>
      <tr class="border <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
                          echo 'opacity-50';
                        } ?> <?php if ($key % 2 === 0) {
                                echo 'bg-gray-100';
                              } ?>">
        <td class="p-3 font-bold text-base leading-5"><?php echo str_replace('\n', '<br />', $item[CSV_FILE_COLUMN_GOI_DICH_VU]) ?></td>
        <td class="py-3 px-1 text-center">
          <div class="flex flex-col items-center justify-center leading-5">
            <img class="h-4 mb-2" src="https://vietnix.vn/wp-content/uploads/2021/09/microchip-1-1-1.svg" />
            <?php echo $item[CSV_FILE_COLUMN_CPU] ?>
          </div>
        </td>
        <td class="py-3 px-1 text-center">
          <div class="flex flex-col items-center justify-center leading-5">
            <img class="h-4 mb-2" src="https://vietnix.vn/wp-content/uploads/2021/09/memory-1-1.svg" />
            <?php echo $item[CSV_FILE_COLUMN_RAM] ?>
          </div>
        </td>
        <td class="py-3 px-1 text-center">
          <div class="flex flex-col items-center justify-center leading-5">
            <img class="h-4 mb-2" src="https://vietnix.vn/wp-content/uploads/2021/09/hdd-1-1.svg" />
            <?php echo $item[CSV_FILE_COLUMN_O_CUNG] ?>
          </div>
        <td class="p-3 text-center">
          <div class="flex flex-col items-center justify-center leading-5">
            <img class="h-4 mb-2" src="https://vietnix.vn/wp-content/uploads/2022/02/icon-bang-thong.png" />
            <?php echo $item[CSV_FILE_COLUMN_BANG_THONG] ?>
          </div>
        </td>
        <td class="py-3 px-1 text-right text-base">
          <?php if ($item[CSV_FILE_COLUMN_GIAM]) { ?>
            <span class="el-custom-text-price leading-5 font-bold"><?php echo $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?></span>
            <span class="text-xs p-1 el-custom-text-discount"><?php echo $item[CSV_FILE_COLUMN_GIAM] ?></span><br>
            <span class="el-custom-text-price-base leading-5 text-base"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } else { ?>
            <span class="el-custom-text-price leading-5 font-bold"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } ?>
          <span><?php echo '/' . $item[CSV_FILE_COLUMN_DON_VI] ?></span>
        </td>
        <td class="py-3 px-1 text-center">
          <div class="relative icon-estimating inline-block">
            <?php if (!$item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
              <?php if (isset($item[13]) && $item[13] === 'cloud_server') { ?>
                <img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag Price Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-cloud-server.svg" />
              <?php } else if (isset($item[13]) && $item[13] === 'vps_server') { ?>
                <img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag Price VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-vps-server.svg" />
              <?php } else { ?>
                <img class="h-8 cursor-pointer icon-estimating m-auto" src="https://vietnix.vn/wp-content/uploads/2021/09/tag-type1.svg" /><?php } ?>
              <div class="absolute bg-white w-48 text-left estimating-cost p-4 rounded-lg">
                <span class="font-bold py-2">Tạm tính</span>
                <div class="py-2">
                  <span class="mr-6">Chu kỳ :</span>
                  <span><?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?></span>
                </div>
                <div>
                  <span class="mr-6">Tổng :</span>
                  <span class="el-custom-text-price leading-5 font-bold">
                    <?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>
                  </span>
                </div>
              </div>
            <?php } ?>
          </div>
        </td>
        <td class="p-3 text-center">
          <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
            <span class="avalible-text text-base font-bold">Áp dụng từ <?php echo $item[CSV_FILE_COLUMN_TT_TOI_THIEU] ?> tháng</span>
          <?php } else { ?>
            <a class="px-5 py-2 el-custom-btn-register font-bold vnx-btn-conversion" rel="nofollow" data-price="<?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>" data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>" data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="DEDICATED SERVER" href="<?php echo $item[CSV_FILE_COLUMN_URL_DANG_KY] ?>">
              Đăng ký
            </a>
          <?php } ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>