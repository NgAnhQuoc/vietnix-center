<?php
$data = isset($data) ? $data : new stdClass();

define_if_not_defined_Center('CSV_FILE_COLUMN_CHU_KY', 0);
define_if_not_defined_Center('CSV_FILE_COLUMN_GOI_DICH_VU', 1);
define_if_not_defined_Center('CSV_FILE_COLUMN_NHAN', 2);
define_if_not_defined_Center('CSV_FILE_COLUMN_TT_TOI_THIEU', 3);
define_if_not_defined_Center('CSV_FILE_COLUMN_CPU', 4);
define_if_not_defined_Center('CSV_FILE_COLUMN_RAM', 5);
define_if_not_defined_Center('CSV_FILE_COLUMN_O_CUNG', 6);
define_if_not_defined_Center('CSV_FILE_COLUMN_O_CUNG_THEM', 7);
define_if_not_defined_Center('CSV_FILE_COLUMN_DOMAIN', 8);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_GOC', 9);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIAM', 10);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_SAU_GIAM', 11);
define_if_not_defined_Center('CSV_FILE_COLUMN_DON_VI', 12);
define_if_not_defined_Center('CSV_FILE_COLUMN_TAM_TINH', 13);
define_if_not_defined_Center('CSV_FILE_COLUMN_URL_DANG_KY', 14);
define_if_not_defined_Center('CSV_FILE_COLUMN_LOAI_DICH_VU', 15);
define_if_not_defined_Center('CSV_FILE_COLUMN_KM', 16);
?>

<?php foreach ($data->row as $item) : ?>
  <table class=" vnx_table_mobile lg:hidden w-full rounded-lg  text-base mb-5 <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
                                                                                echo 'opacity-50';
                                                                              } ?>">
    <colgroup class="bg-white">
      <col width="50%" />
      <col width="50%" />
    </colgroup>
    <tbody>
      <tr>
        <td class="p-3 font-bold pl-4 pb-0">
          <div class="flex flex-col justify-center text-[#38A7FF] text-lg">
            <?php
            if (!empty($item[CSV_FILE_COLUMN_NHAN])) :
              $label = explode(", ", trim($item[CSV_FILE_COLUMN_NHAN]));
            ?>
              <div class="text-white text-xs font-medium leading-[14px] p-1 mb-2 rounded-[3px] inline w-fit" style="background-color: <?php echo $label[1]; ?>;">
                <?php echo $label[0]; ?>
              </div>
            <?php endif; ?>
            <?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>
          </div>
        </td>
        <td class="p-3 text-end pl-4 pb-0">
          <?php if ($item[CSV_FILE_COLUMN_GIAM] && !$item[CSV_FILE_COLUMN_KM]) { ?>
            <span class="el-custom-text-price leading-5 font-bold text-base"><?php echo $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?></span>
            <span class="text-xs p-0.5 el-custom-text-discount"><?php echo $item[CSV_FILE_COLUMN_GIAM] ?></span>
            <br>
            <span class="el-custom-text-price-base leading-5 text-xs"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } else { ?>
            <span class="el-custom-text-price leading-5 font-bold text-xs"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } ?>
          <span class="text-xs text-[#828282]"><?php echo '/' . $item[CSV_FILE_COLUMN_DON_VI] ?></span>
          <?php if ($item[CSV_FILE_COLUMN_KM]) : ?>
            <div class="flex flex-row bg-[#F8E2E2] rounded-md border border-[#FF0000] justify-center text-xs font-bold px-2 py-1 mt-1">
              <img src="https://vietnix.vn/wp-content/uploads/2023/05/price-table-gif.png" />
              <span>+ <?php echo $item[CSV_FILE_COLUMN_KM]; ?></span>
            </div>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="w-full py-3.5">
          <div class="h-px bg-[#F2F2F2]"></div>
        </td>
      </tr>
      <tr>
        <td class="p-3 text-center pl-6">
          <div class="flex flex-row items-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-5 w-6 mr-2" alt="icon CPU bảng giá Hosting Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2023/10/icon-cpu-bang-gia-hosting-gia-re.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-5 w-6 mr-2" alt="icon CPU bảng giá business hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
              <img class="h-5 w-6 mr-2" alt="icon CPU bảng giá Web Hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-web-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_CPU] ?>
          </div>
        </td>
        <td class="p-3 text-center pr-6">
          <div class="flex flex-row items-center leading-8 justify-end">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá Hosting Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2023/10/icon-ram-bang-gia-hosting-gia-re.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá business hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá Web Hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-web-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_RAM] ?>
          </div>
        </td>
      </tr>
      <tr>
        <td class="p-3 text-center pl-6">
          <div class="flex flex-row items-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon ổ cứng SSD bảng giá Hosting Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2023/10/icon-o-cung-ssd-bang-gia-hosting-gia-re.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon ổ cứng SSD bảng giá business hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-o-cung-ssd-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon ổ cứng SSD bảng giá Web Hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-o-cung-ssd-bang-gia-web-hosting.svg" />
            <?php } ?>
            <div class="flex flex-col items-start gap-1 leading-5">
              <?php echo $item[CSV_FILE_COLUMN_O_CUNG] ?><span class="text-[#FF9038]"><?php echo($item[CSV_FILE_COLUMN_O_CUNG_THEM]) ? " + ".$item[CSV_FILE_COLUMN_O_CUNG_THEM].' Free' : "" ?></span>
            </div>
          </div>
        </td>
        <td class="p-3 pr-6">
          <div class="flex flex-row items-center leading-6 justify-end">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá Hosting Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2023/10/icon-so-domain-bang-gia-hosting-gia-re.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá business hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá Web Hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-web-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_DOMAIN] ?>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="text-center px-6 pt-3 pb-5">
          <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
            <span class="avalible-text text-base font-bold">Áp dụng từ <?php echo $item[CSV_FILE_COLUMN_TT_TOI_THIEU] ?> tháng</span>
          <?php } else { ?>
            <div class="flex flex-row justify-between items-center">
              <a class="px-5 py-2 el-custom-btn-register-mobile rounded-md flex justify-center vnx-btn-conversion w-11/12 text-[#38A7FF] " rel="nofollow" href="<?php echo $item[CSV_FILE_COLUMN_URL_DANG_KY] ?>" data-price="<?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>" data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>" data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $item[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>">
                Đăng ký
              </a>
              <div class="relative icon-estimating mobile ml-2.5">
                <?php if (!$item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
                  <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
                    <img class="h-8 cursor-pointer icon-estimating" alt="Tag price Hosting Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2023/10/tag-price-hosting-gia-re.svg" />
                  <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
                    <img class="h-8 cursor-pointer icon-estimating" alt="Tag price business hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-business-hosting.svg" />
                  <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                    <img class="h-8 cursor-pointer icon-estimating" alt="Tag price Web Hosting" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-web-hosting.svg" />
                  <?php } ?>
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