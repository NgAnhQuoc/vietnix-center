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

<table class="w-full text-sm rounded-md hidden lg:table">
  <colgroup>
    <col width="15%" />
    <col width="14%" />
    <col width="14%" />
    <col width="14%" />
    <col width="15%" />
    <col width="5%" />
    <col width="15%" />
  </colgroup>
  <thread>
    <tr class="border border-b-2 rounded-md text-base">
      <th class="pl-8 pr-1 text-left">
        <?php echo $data->header[CSV_FILE_COLUMN_GOI_DICH_VU] ?>
      </th>
      <th class="p-3">
        <?php echo $data->header[CSV_FILE_COLUMN_SO_EMAIL] ?>
      </th>
      <th class="p-3">
        <?php echo $data->header[CSV_FILE_COLUMN_DUNG_LUONG] ?>
      </th>
      <th class="p-3">
        <?php echo $data->header[CSV_FILE_COLUMN_DOMAIN] ?>
      </th>
      <th class="p-3 text-right">
        <?php echo $data->header[CSV_FILE_COLUMN_GIA_GOC] ?>
      </th>
      <th class="p-3"></th>
      <th class="py-3 px-8"></th>
    </tr>
  </thread>
  <tbody>
    <?php foreach ($data->row as $key => $item): ?>

      <tr class="border <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
        echo 'opacity-50';
      } ?> <?php if ($key % 2 === 0) {
          echo 'bg-gray-100';
        } ?>">
        <td class="pl-8 pr-1 py-6 font-bold text-base">
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

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4" alt="icon số email bảng giá hosting cheap"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-hosting-cheap.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
                <img class="h-4" alt="icon số email bảng giá business hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                  <img class="h-4" alt="icon số email bảng giá Web Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-web-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
                    <img class="h-4" alt="icon số email bảng giá Seo Hosting"
                      src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
                      <img class="h-4" alt="icon số email bảng giá WordPress Hosting"
                        src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'email_hosting') { ?>
                        <img class="h-4" alt="icon số email bảng giá Email Hosting"
                          src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-email-bang-gia-email-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_SO_EMAIL] ?>
          </div>
        </td>

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4" alt="icon số domain bảng giá hosting cheap"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-hosting-cheap.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
                <img class="h-4" alt="icon số domain bảng giá business hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                  <img class="h-4" alt="icon số domain bảng giá Web Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-web-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
                    <img class="h-4" alt="icon số domain bảng giá Seo Hosting"
                      src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
                      <img class="h-4" alt="icon số domain bảng giá WordPress Hosting"
                        src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'email_hosting') { ?>
                        <img class="h-4" alt="icon số domain bảng giá Email Hosting"
                          src="https://vietnix.vn/wp-content/uploads/2022/06/icon-dung-luong-bang-gia-email-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_DUNG_LUONG] ?>
          </div>
        </td>

        <td class="px-3 py-6 text-center">
          <div class="flex flex-col items-center justify-center leading-8">
            <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
              <img class="h-4" alt="icon số domain bảng giá hosting cheap"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-hosting-cheap.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
                <img class="h-4" alt="icon số domain bảng giá business hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-business-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                  <img class="h-4" alt="icon số domain bảng giá Web Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-web-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'seo_hosting') { ?>
                    <img class="h-4" alt="icon số domain bảng giá Seo Hosting"
                      src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-seo-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'wp_hosting') { ?>
                      <img class="h-4" alt="icon số domain bảng giá WordPress Hosting"
                        src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-wp-hosting.svg" />
            <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'email_hosting') { ?>
                        <img class="h-4" alt="icon số domain bảng giá Email Hosting"
                          src="https://vietnix.vn/wp-content/uploads/2022/06/icon-so-domain-bang-gia-email-hosting.svg" />
            <?php } ?>
            <?php echo $item[CSV_FILE_COLUMN_DOMAIN] ?>
          </div>
        </td>

        <td class="px-3 py-6 text-right text-base">
          <?php if ($item[CSV_FILE_COLUMN_GIAM]) { ?>
            <span class="el-custom-text-price leading-5 font-bold">
              <?php echo $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?>
            </span>
            <span class="text-xs p-1 el-custom-text-discount">
              <?php echo $item[CSV_FILE_COLUMN_GIAM] ?>
            </span><br>
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

        <td class="p-3">
          <div class="relative icon-estimating inline-block">
            <?php if (!$item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
              switch ($item[CSV_FILE_COLUMN_LOAI_DICH_VU]) {
                case 'cheap_hosting':
                  echo '<img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag price host rẻ"
                src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-host-re.svg" />';
                  break;
                case 'business_hosting':
                  echo '<img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag price business hosting"
                src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-business-hosting.svg" />';
                  break;
                case 'web_hosting':
                  echo '<img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag price Web Hosting"
                src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-web-hosting.svg" />';
                  break;
                case 'seo_hosting':
                  echo '<img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag price Seo Hosting"
                src="https://vietnix.vn/wp-content/uploads/2022/06/tag-price-seo-hosting.svg" />';
                  break;
                case 'wp_hosting':
                  echo '<img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag price WordPress Hosting"
                src="https://vietnix.vn/wp-content/uploads/2022/06/tag-price-wp-hosting.svg" />';
                  break;
                case 'email_hosting':
                  echo '<img class="h-8 cursor-pointer icon-estimating m-auto" alt="Tag price Email Hosting"
                src="https://vietnix.vn/wp-content/uploads/2022/06/tag-price-email-hosting.svg" />';
                  break;
                default:
                  echo '<img class="h-8 cursor-pointer" src="https://vietnix.vn/wp-content/uploads/2021/09/tag-type1.svg" alt="icon tam tinh" />';
                  break;
              }
              ?>
              <div class="absolute bg-white w-48 text-left estimating-cost p-4 rounded-lg z-10">
                <span class="font-bold py-2">Tạm tính</span>
                <div class="py-2">
                  <span class="mr-6">Chu kỳ :</span>
                  <span>
                    <?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>
                  </span>
                </div>
                <div>
                  <span class="mr-6">Tổng :</span>
                  <span class="el-custom-text-price leading-5 font-bold">
                    <?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>
                  </span>
                </div>
              </div>
            </div>
          <?php } ?>
        </td>

        <td class="py-3 pr-8 text-right">
          <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
            <span class="avalible-text text-base font-bold">Áp dụng từ
              <?php echo $item[CSV_FILE_COLUMN_TT_TOI_THIEU] ?> tháng
            </span>
          <?php } else { ?>
            <a class="px-5 py-2 el-custom-btn-register font-bold vnx-btn-conversion" rel="nofollow"
              data-price="<?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>"
              data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>"
              data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>"
              data-product-category="<?php echo trim(preg_replace('/\d+/', '', $item[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>"
              href="<?php echo $item[CSV_FILE_COLUMN_URL_DANG_KY] ?>">
              Đăng ký
            </a>
          <?php } ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>