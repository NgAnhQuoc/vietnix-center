<?php
$data = isset($data) ? $data : new stdClass();
define_if_not_defined_Center('CSV_FILE_COLUMN_CHU_KY', 0);
define_if_not_defined_Center('CSV_FILE_COLUMN_GOI_DICH_VU', 1);
define_if_not_defined_Center('CSV_FILE_COLUMN_NHAN', 2);
define_if_not_defined_Center('CSV_FILE_COLUMN_TT_TOI_THIEU', 3);
define_if_not_defined_Center('CSV_FILE_COLUMN_SO_EMAIL', 4);
define_if_not_defined_Center('CSV_FILE_COLUMN_DUNG_LUONG', 5);
define_if_not_defined_Center('CSV_FILE_COLUMN_DOMAIN', 6);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_GOC', 7);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIAM', 8);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_SAU_GIAM', 9);
define_if_not_defined_Center('CSV_FILE_COLUMN_DON_VI', 10);
define_if_not_defined_Center('CSV_FILE_COLUMN_TAM_TINH', 11);
define_if_not_defined_Center('CSV_FILE_COLUMN_URL_DANG_KY', 12);
define_if_not_defined_Center('CSV_FILE_COLUMN_LOAI_DICH_VU', 13);
?>

<?php foreach ($data->row as $item): ?>
  <table class="w-full rounded-lg overflow-hidden text-base mb-5 vnx_table_mobile lg:hidden <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
    echo 'opacity-50';
  } ?>">
    <colgroup class="bg-white">
      <col width="50%" />
      <col width="50%" />
    </colgroup>
    <tbody>
      <tr>
        <td class="p-3 font-bold">
          <div class="flex flex-col justify-center">
            <?php
            if (!empty($item[CSV_FILE_COLUMN_NHAN])):
              $label = explode(", ", trim($item[CSV_FILE_COLUMN_NHAN]));
              ?>
              <div class="text-white text-xs font-medium leading-[14px] p-1 mb-2 rounded-[3px] inline w-fit"
                style="background-color: <?php echo $label[1]; ?>;">
                <?php echo $label[0]; ?>
              </div>
            <?php endif; ?>
            <?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>
          </div>
        </td>
        <td class="p-3">
          <?php if ($item[CSV_FILE_COLUMN_GIAM]) { ?>
            <span class="el-custom-text-price leading-5 font-bold">
              <?php echo $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?>
            </span>
            <span class="text-xs p-1 el-custom-text-discount">
              <?php echo $item[CSV_FILE_COLUMN_GIAM] ?>
            </span>
            <br>
            <span class="el-custom-text-price-base leading-5 text-base">
              <?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?>
            </span>
          <?php } else { ?>
            <span class="el-custom-text-price leading-5 font-bold">
              <?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?>
            </span>
          <?php } ?>
          <span>
            <?php echo '/' . $item[CSV_FILE_COLUMN_DON_VI] ?>
          </span>
        </td>
      </tr>

      <tr>
        <td class="p-3 text-center">
          <div class="flex flex-row items-center leading-6">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá hosting cheap"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-hosting-cheap.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
                <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá business hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                  <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá Web Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-web-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
                    <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá Seo Hosting"
                      src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
                      <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá WP Hosting"
                        src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'email_hosting') { ?>
                        <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá Email Hosting"
                          src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-email-bang-gia-email-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_SO_EMAIL] ?>
          </div>
        </td>
        <td class="p-3 text-center">
          <div class="flex flex-row items-center leading-6">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon CPU bảng giá hosting cheap"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-hosting-cheap.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
                <img class="h-4 w-6 mr-2" alt="icon CPU bảng giá business hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                  <img class="h-4 w-6 mr-2" alt="icon CPU bảng giá Web Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-web-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
                    <img class="h-4 w-6 mr-2" alt="icon CPU bảng giá Seo Hosting"
                      src="https://vietnix.vn/wp-content/uploads/2022/06/icon-cpu-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
                      <img class="h-4 w-6 mr-2" alt="icon CPU bảng giá WordPress Hosting"
                        src="https://vietnix.vn/wp-content/uploads/2022/06/icon-cpu-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'email_hosting') { ?>
                        <img class="h-4 w-6 mr-2" alt="icon CPU bảng giá Email Hosting"
                          src="https://vietnix.vn/wp-content/uploads/2022/06/icon-dung-luong-bang-gia-email-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_DUNG_LUONG] ?>
          </div>
        </td>
      </tr>

      <tr>
        <td class="p-3">
          <div class="flex flex-row items-center leading-6">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá hosting cheap"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-hosting-cheap.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
                <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá business hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                  <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá Web Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-web-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
                    <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá Seo Hosting"
                      src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ram-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
                      <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá WordPress Hosting"
                        src="https://vietnix.vn/wp-content/uploads/2022/06/icon-ram-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'email_hosting') { ?>
                        <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá Email Hosting"
                          src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-email-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_DOMAIN] ?>
          </div>
        </td>
        <td></td>
      </tr>

      <tr>
        <td colspan="2" class="text-center px-3 pt-3 pb-5">
          <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
            <span class="avalible-text text-base font-bold">Áp dụng từ
              <?php echo $item[CSV_FILE_COLUMN_TT_TOI_THIEU] ?> tháng
            </span>
          <?php } else { ?>
            <a class="px-5 py-2 el-custom-btn-register-mobile rounded-md flex justify-center vnx-btn-conversion"
              rel="nofollow" href="<?php echo $item[CSV_FILE_COLUMN_URL_DANG_KY] ?>"
              data-price="<?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>"
              data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>"
              data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>"
              data-product-category="<?php echo trim(preg_replace('/\d+/', '', $item[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>">
              Đăng ký
            </a>
          <?php } ?>
        </td>
      </tr>
    </tbody>
  </table>
<?php endforeach; ?>