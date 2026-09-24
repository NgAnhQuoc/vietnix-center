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
define_if_not_defined_Center('CSV_FILE_COLUMN_IP', 8);
define_if_not_defined_Center('CSV_FILE_COLUMN_DOMAIN', 9);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_GOC', 10);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIAM', 11);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_SAU_GIAM', 12);
define_if_not_defined_Center('CSV_FILE_COLUMN_DON_VI', 13);
define_if_not_defined_Center('CSV_FILE_COLUMN_TAM_TINH', 14);
define_if_not_defined_Center('CSV_FILE_COLUMN_URL_DANG_KY', 15);
define_if_not_defined_Center('CSV_FILE_COLUMN_LOAI_DICH_VU', 16);
define_if_not_defined_Center('CSV_FILE_COLUMN_KM', 17);
?>

<table class="w-full text-sm rounded-md hidden lg:table">
  <colgroup>
    <col width="15%" />
    <col width="10%" />
    <col width="10%" />
    <col width="10%" />
    <col width="10%" />
    <col width="10%" />
    <col width="15%" />
    <col width="6%" />
    <col width="15%" />
  </colgroup>
  <thread>
    <tr class="border border-b-2 rounded-md text-base">
      <th class="pl-8 pr-1 text-left"><?php echo $data->header[CSV_FILE_COLUMN_GOI_DICH_VU] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_CPU] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_RAM] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_O_CUNG] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_IP] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_DOMAIN] ?></th>
      <th class="p-3"><?php echo $data->header[CSV_FILE_COLUMN_GIA_GOC] ?></th>
      <th class="p-3"></th>
      <th class="py-3 px-8"></th>
    </tr>
  </thread>
  <tbody>
    <?php foreach ($data->row as $key => $item) : ?>

      <tr class="border rounded-md <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
                                      echo 'opacity-50';
                                    } ?> <?php if ($key % 2 === 0) {
                                            echo 'bg-gray-100';
                                          } ?>">
        <td class="pl-8 pr-1 py-6 font-bold text-base">
          <div class="flex flex-col justify-center">
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

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
              <img class="h-4" alt="icon CPU bảng giá Seo Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-cpu-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
              <img class="h-4" alt="icon CPU bảng giá WordPress Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-cpu-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'reseller_hosting') { ?>
              <img class="h-4" alt="icon CPU bảng giá Reseller Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-cpu-bang-gia-reseller-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_CPU] ?>
          </div>
        </td>

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
              <img class="h-4" alt="icon RAM bảng giá Seo Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ram-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
              <img class="h-4" alt="icon RAM bảng giá WordPress Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ram-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'reseller_hosting') { ?>
              <img class="h-4" alt="icon RAM bảng giá Reseller Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ram-bang-gia-reseller-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_RAM] ?>
          </div>
        </td>

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
              <img class="h-4" alt="icon ổ cứng SSD bảng giá Seo Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-o-cung-ssd-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
              <img class="h-4" alt="icon ổ cứng SSD bảng giá WordPress Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-o-cung-ssd-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'reseller_hosting') { ?>
              <img class="h-4" alt="icon ổ cứng SSD bảng giá Reseller Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-o-cung-ssd-bang-gia-reseller-hosting.svg" />
            <?php } ?>
            <div class="flex flex-nowrap gap-1">
              <?php echo $item[CSV_FILE_COLUMN_O_CUNG] ?><span class="text-[#FF9038]" style="text-wrap: nowrap"><?php echo($item[CSV_FILE_COLUMN_O_CUNG_THEM]) ? " + ".$item[CSV_FILE_COLUMN_O_CUNG_THEM].' Free' : "" ?></span>
            </div>
          </div>
        </td>

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
              <img class="h-4" alt="icon IP bảng giá Seo Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ip-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
              <img class="h-4" alt="icon IP bảng giá Seo Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ip-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'reseller_hosting') { ?>
              <img class="h-4" alt="icon IP bảng giá Reseller Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ip-bang-gia-reseller-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_IP] ?>
          </div>
        </td>

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
              <img class="h-4" alt="icon số domain bảng giá Seo Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
              <img class="h-4" alt="icon số domain bảng giá WordPress Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'reseller_hosting') { ?>
              <img class="h-4" alt="icon số domain bảng giá Reseller Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-reseller-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_DOMAIN] ?>
          </div>
        </td>

        <td class="px-3 py-6 text-right text-base">
          <?php if ($item[CSV_FILE_COLUMN_GIAM] && !isset($item[CSV_FILE_COLUMN_KM])) {
          ?>
            <span class="el-custom-text-price leading-5 font-bold"><?php echo $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?></span>
            <span class="text-xs p-1 el-custom-text-discount"><?php echo $item[CSV_FILE_COLUMN_GIAM] ?></span><br>
            <span class="el-custom-text-price-base leading-5 text-base"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } else { ?>
            <span class="el-custom-text-price leading-5 font-bold"><?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?></span>
          <?php } ?>
          <span><?php echo '/' . $item[CSV_FILE_COLUMN_DON_VI] ?></span>
          <?php if (!empty($item[CSV_FILE_COLUMN_KM])) : ?>
            <div class="flex flex-row bg-[#F8E2E2] rounded-md border border-[#FF0000] justify-center text-xs font-bold px-2 py-1 mt-1">
              <img src="https://vietnix.vn/wp-content/uploads/2023/05/price-table-gif.png" />
              <span>+ <?php echo $item[CSV_FILE_COLUMN_KM]; ?></span>
            </div>
          <?php endif; ?>
        </td>

        <td class="px-3 py-6 text-center">
          <div class="relative icon-estimating inline-block">
            <?php if (!$item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
              <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
                <img class="h-8 cursor-pointer icon-estimating" alt="Tag price Seo Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/tag-price-seo-hosting.svg" />
              <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
                <img class="h-8 cursor-pointer icon-estimating" alt="Tag price WordPress Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/tag-price-wp-hosting.svg" />
              <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'reseller_hosting') { ?>
                <img class="h-8 cursor-pointer icon-estimating" alt="Tag price Reseller Hosting" src="https://vietnix.vn/wp-content/uploads/2022/06/tag-price-reseller-hosting.svg" />
              <?php } ?>
              <div class="absolute bg-white w-48 text-left estimating-cost p-4 rounded-lg z-10">
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

        <td class="py-3 pr-8 text-right">
          <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
            <span class="avalible-text text-base font-bold">Áp dụng từ <?php echo $item[CSV_FILE_COLUMN_TT_TOI_THIEU] ?> tháng</span>
          <?php } else { ?>
            <a class="px-5 py-2 el-custom-btn-register font-bold vnx-btn-conversion" rel="nofollow" data-price="<?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>" data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>" data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $item[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>" href="<?php echo $item[CSV_FILE_COLUMN_URL_DANG_KY] ?>">
              Đăng ký
            </a>
          <?php } ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>