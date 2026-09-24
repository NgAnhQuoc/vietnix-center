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

<?php foreach ($data->row as $key => $item) : ?>
  <table class="vnx_table_mobile lg:hidden w-full text-base mb-5 rounded-md<?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
                                                                              echo 'opacity-50';
                                                                            } ?>">
    <colgroup>
      <col width="50%" />
      <col width="50%" />
    </colgroup>
    <tbody>
      <tr>
        <td class="p-3 font-bold pl-6 pt-6 text-lg text-[#38A7FF]"><?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?></td>
        <td class="p-3 text-right pr-6 pt-6">
          <?php if ($item[CSV_FILE_COLUMN_GIAM]) { ?>
            <span class="el-custom-text-price leading-5 font-bold text-base"><?php echo $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?></span>
            <span class="text-xs p-0.5 el-custom-text-discount"><?php echo $item[CSV_FILE_COLUMN_GIAM] ?></span>
            <br>
            <span class="el-custom-text-price-base leading-5 text-xs"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } else { ?>
            <span class="el-custom-text-price leading-5 font-bold text-xs"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } ?>
          <span style="color: #828282" class="text-xs"><?php echo '/' . $item[CSV_FILE_COLUMN_DON_VI] ?></span>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="w-full py-3">
          <div class="line h-px bg-[#F2F2F2] ml-6 mr-6"></div>
        </td>
      </tr>
      <tr>
        <td class="p-3 text-left pl-6">
          <div class="flex flex-row items-center leading-0">
            <img class="h-5 w-6 mr-2" src="https://vietnix.vn/wp-content/uploads/2021/09/microchip-1-1-1.svg" />
            <?php echo $item[CSV_FILE_COLUMN_CPU] ?>
          </div>
        </td>
        <td class="p-3 text-center pr-6">
          <div class="flex flex-row items-center leading-8 justify-end">
            <img class="h-4 w-6 mr-2" src="https://vietnix.vn/wp-content/uploads/2021/09/memory-1-1.svg" />
            <?php echo $item[CSV_FILE_COLUMN_RAM] ?>
          </div>
        </td>
      </tr>
      <tr>
        <td class="p-3 text-center pl-6">
          <div class="flex flex-row items-center leading-8">
            <img class="h-4 w-6 mr-2" src="https://vietnix.vn/wp-content/uploads/2021/09/hdd-1-1.svg" />
            <?php echo $item[CSV_FILE_COLUMN_O_CUNG] ?>
          </div>
        </td>
        <td class="p-3 pr-6">
          <div class="flex flex-row items-center leading-6 justify-end">
            <img class="h-4 w-6 mr-2" src="https://vietnix.vn/wp-content/uploads/2022/02/icon-bang-thong.png" />
            <?php echo $item[CSV_FILE_COLUMN_BANG_THONG] ?>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="text-center px-6 pt-3 pb-6">
          <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
            <span class="avalible-text text-base font-bold">Áp dụng từ <?php echo $item[CSV_FILE_COLUMN_TT_TOI_THIEU] ?> tháng</span>
          <?php } else { ?>
            <div class="flex flex-row justify-between items-center">
              <a class="px-5 py-2 el-custom-btn-register-mobile rounded-md flex justify-center vnx-btn-conversion w-11/12" rel="nofollow" data-price="<?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>" data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>" data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="DEDICATED SERVER" href="<?php echo $item[CSV_FILE_COLUMN_URL_DANG_KY] ?>">
                Đăng ký
              </a>
              <div class="relative icon-estimating mobile ml-2.5">
                <?php if (!$item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
                  <?php if (isset($item[13]) && $item[13] === 'cloud_server') { ?>
                    <img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag Price Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-cloud-server.svg" />
                  <?php } else if (isset($item[13]) && $item[13] === 'vps_server') { ?>
                    <img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag Price VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-vps-server.svg" />
                  <?php } else { ?>
                    <img class="h-8 cursor-pointer icon-estimating m-auto" src="https://vietnix.vn/wp-content/uploads/2021/09/tag-type1.svg" /><?php } ?>
                  <div class="absolute bg-white w-48 text-left estimating-cost py-2 px-4 rounded-lg z-10">
                    <span class="el-custom-text-price-title font-bold">Tạm tính</span>
                    <span class="el-custom-text-price leading-5 font-bold">
                      <?php echo $item[CSV_FILE_COLUMN_TAM_TINH]; ?>
                    </span>
                  </div>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        </td>
      </tr>
    </tbody>
  </table>
<?php endforeach; ?>