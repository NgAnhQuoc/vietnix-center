<?php
$data = isset($data) ? $data : new stdClass();

define_if_not_defined_Center('CSV_FILE_COLUMN_CHU_KY', 0);
define_if_not_defined_Center('CSV_FILE_COLUMN_GOI_DICH_VU', 1);
define_if_not_defined_Center('CSV_FILE_COLUMN_NHAN', 2);
define_if_not_defined_Center('CSV_FILE_COLUMN_TT_TOI_THIEU', 3);
define_if_not_defined_Center('CSV_FILE_COLUMN_CPU', 4);
define_if_not_defined_Center('CSV_FILE_COLUMN_RAM', 5);
define_if_not_defined_Center('CSV_FILE_COLUMN_O_CUNG', 6);
define_if_not_defined_Center('CSV_FILE_COLUMN_DOMAIN', 7);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_GOC', 8);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIAM', 9);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_SAU_GIAM', 10);
define_if_not_defined_Center('CSV_FILE_COLUMN_DON_VI', 11);
define_if_not_defined_Center('CSV_FILE_COLUMN_TAM_TINH', 12);
define_if_not_defined_Center('CSV_FILE_COLUMN_URL_DANG_KY', 13);
define_if_not_defined_Center('CSV_FILE_COLUMN_LOAI_DICH_VU', 14);
define_if_not_defined_Center('CSV_FILE_COLUMN_KM', 15);
?>
<?php foreach ($data->row as $item): ?>
  <div class=" vnx_table_mobile lg:hidden w-full rounded-lg  text-base mb-5 flex py-8 flex-col <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) {
    echo 'opacity-50';
  } ?>">
    <div class="flex flex-col justify-center text-[#013A52] text-lg text-center w-full font-bold">
      <?php
      if (!empty($item[CSV_FILE_COLUMN_NHAN])):
        $label = explode(", ", trim($item[CSV_FILE_COLUMN_NHAN]));
        ?>
      <?php endif; ?>
      <?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>
    </div>
    <div class="w-full grid grid-cols-2 mt-8">
      <div class="w-full flex p-5 justify-center flex-col items-center gap-y-3.5 border-r border-b border-[#D9E1EE]">
        <span class="text-[#013A52] text-base font-medium leading-5 text-center"><?= $data->header[CSV_FILE_COLUMN_CPU] ?></span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
            <img class="h-5 w-6" alt="icon CPU bảng giá Hosting Giá Rẻ"
              src="https://vietnix.vn/wp-content/uploads/2023/10/icon-cpu-bang-gia-hosting-gia-re.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-5 w-6" alt="icon CPU bảng giá business hosting"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-business-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                <img class="h-5 w-6" alt="icon CPU bảng giá Web Hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-web-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'shared_hosting') { ?>
                  <img class="h-5 w-6" alt="icon CPU bảng giá Shared Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-web-hosting.svg" />
          <?php } ?>
        </span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php echo $item[CSV_FILE_COLUMN_CPU] ?>
        </span>
      </div>
      <div class="w-full flex p-5 justify-center flex-col items-center gap-y-3.5 border-b border-[#D9E1EE]">
        <span class="text-[#013A52] text-base font-medium leading-5 text-center"><?= $data->header[CSV_FILE_COLUMN_RAM] ?></span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
            <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá Hosting Giá Rẻ"
              src="https://vietnix.vn/wp-content/uploads/2023/10/icon-ram-bang-gia-hosting-gia-re.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá business hosting"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-business-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                <img class="h-4 w-6 mr-2" alt="icon RAM bảng giá Web Hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-web-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'shared_hosting') { ?>
                  <img class="h-5 w-6" alt="icon RAM bảng giá Shared Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-web-hosting.svg" />
          <?php } ?>
        </span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php echo $item[CSV_FILE_COLUMN_RAM] ?>
        </span>
      </div>
      <div class="w-full flex p-5 justify-center flex-col items-center gap-y-3.5 border-r border-[#D9E1EE]">
        <span class="text-[#013A52] text-base font-medium leading-5 text-center"><?= $data->header[CSV_FILE_COLUMN_O_CUNG] ?></span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
            <img class="h-5 w-6" alt="icon CPU bảng giá Hosting Giá Rẻ"
              src="https://vietnix.vn/wp-content/uploads/2023/10/icon-cpu-bang-gia-hosting-gia-re.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-5 w-6" alt="icon CPU bảng giá business hosting"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-business-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                <img class="h-5 w-6" alt="icon CPU bảng giá Web Hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-web-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'shared_hosting') { ?>
                  <img class="h-5 w-6" alt="icon CPU bảng giá Shared Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-web-hosting.svg" />
          <?php } ?>
        </span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php echo $item[CSV_FILE_COLUMN_O_CUNG] ?>
        </span>
      </div>
      <div class="w-full flex p-5 justify-center flex-col items-center gap-y-3.5">
        <span class="text-[#013A52] text-base font-medium leading-5 text-center"><?= $data->header[CSV_FILE_COLUMN_DOMAIN] ?></span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'cheap_hosting') { ?>
            <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá Hosting Giá Rẻ"
              src="https://vietnix.vn/wp-content/uploads/2023/10/icon-so-domain-bang-gia-hosting-gia-re.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'business_hosting') { ?>
              <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá business hosting"
                src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-business-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'web_hosting') { ?>
                <img class="h-4 w-6 mr-2" alt="icon số domain bảng giá Web Hosting"
                  src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-web-hosting.svg" />
          <?php } else if ($item[CSV_FILE_COLUMN_LOAI_DICH_VU] === 'shared_hosting') { ?>
                  <img class="h-5 w-6" alt="icon Domain bảng giá Shared Hosting"
                    src="https://vietnix.vn/wp-content/uploads/2021/12/icon-so-domain-bang-gia-web-hosting.svg" />
          <?php } ?>
        </span>
        <span class="text-[#013A52] text-base font-medium leading-5 text-center">
          <?php echo $item[CSV_FILE_COLUMN_DOMAIN] ?>
        </span>
      </div>
    </div>
    <div class="w-full flex justify-center mt-5 gap-x-3">
      <span class="text-[#013A52] text-base font-medium leading-5 text-center">Giá</span>
      <span class="el-custom-text-price leading-5 font-bold text-base flex items-center">
        <?php if ($item[CSV_FILE_COLUMN_GIAM] && !$item[CSV_FILE_COLUMN_KM]) { ?>
          <?php echo $item[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?>
        <?php } else { ?>
          <?php echo $item[CSV_FILE_COLUMN_GIA_GOC] ?>
        <?php } ?>
        <p class="text-sm font-normal text-[#828282]">
          <?php echo '/' . $item[CSV_FILE_COLUMN_DON_VI] ?>
        </p>
      </span>
    </div>
    <div class="w-full flex justify-center mt-2.5 gap-x-3">
      <?php if ($item[CSV_FILE_COLUMN_TT_TOI_THIEU]) { ?>
        <span class="avalible-text text-base font-bold">Áp dụng từ
          <?php echo $item[CSV_FILE_COLUMN_TT_TOI_THIEU] ?> tháng
        </span>
      <?php } else { ?>
        <div class="flex flex-row justify-between items-center w-full px-5">
          <a class="px-8 py-2.5 rounded-md flex justify-center vnx-btn-conversion w-full rounded-md border-2 border-[#38A7FF] text-[#38A7FF] text-base font-medium leading-normal hover:bg-[#38A7FF] hover:text-white"
            rel="nofollow" href="<?php echo $item[CSV_FILE_COLUMN_URL_DANG_KY] ?>"
            data-price="<?php echo $item[CSV_FILE_COLUMN_TAM_TINH] ?>"
            data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>"
            data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>"
            data-product-category="<?php echo trim(preg_replace('/\d+/', '', $item[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>">
            Đăng ký
          </a>
        </div>
      <?php } ?>
    </div>
  </div>
<?php endforeach; ?>