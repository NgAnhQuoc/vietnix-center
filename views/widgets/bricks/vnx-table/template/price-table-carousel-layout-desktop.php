<?php

$data = isset($data) ? $data : new stdClass();

define_if_not_defined_Center('CSV_FILE_COLUMN_CHU_KY', 0);
define_if_not_defined_Center('CSV_FILE_COLUMN_GOI_DICH_VU', 1);
define_if_not_defined_Center('CSV_FILE_COLUMN_NHAN', 2);
define_if_not_defined_Center('CSV_FILE_COLUMN_CPU', 4);
define_if_not_defined_Center('CSV_FILE_COLUMN_RAM', 5);
define_if_not_defined_Center('CSV_FILE_COLUMN_O_CUNG', 6);
define_if_not_defined_Center('CSV_FILE_COLUMN_DOMAIN', 7);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_GOC', 8);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIAM', 9);
define_if_not_defined_Center('CSV_FILE_COLUMN_GIA_SAU_GIAM', 10);
define_if_not_defined_Center('CSV_FILE_COLUMN_TAM_TINH', 12);
define_if_not_defined_Center('CSV_FILE_COLUMN_URL_DANG_KY', 13);

try {
  $gift_tittle = $data->info['list_gift_title'];
  $list_gift_description = $data->info['list_gift_description'];
  $button_icon['icon'] = !empty($data->button_icon) ? \Bricks\Element::render_icon($data->button_icon) : false;
  $button_icon['button_text'] = $data->button_text;
  $order_by = $data->order_by;
  $csvdata = [];
  $custom_url = ($data->info['custom_button_url']['url']) ?? '';
  $button_attributes = '';
  $custom_button_switch = ($data->info['custom_button_url_switch']) ?? false;
  $gift_tool_box = $data->info['list_gift_title_show'];
  $searchValue = '3 Tháng';
  if (isset($data->info['list_cycle']) && $data->info['list_cycle'] != '') {
    $searchValue = $data->info['list_cycle'];
  }
  if ($custom_button_switch == 1) {
    if ( isset($data->info['custom_button_url']['newTab']) && $data->info['custom_button_url']['newTab'] !== '') {
      $button_attributes .= 'target="_blank" ';
    }
    if ( isset($data->info['custom_button_url']['rel']) && $data->info['custom_button_url']['rel'] !== '') {
      $button_attributes .= 'rel="'.$data->info['custom_button_url']['rel'].'" ';
    }
  }
  $random_string = $data->random_string;
  $slug_tab = $data->slug;
  // print_r($data->info['list_upload'][url]);
  // die();
  $link = $data->info['list_upload']['url'];
  $regex = '/wp-content\/(.*)/';
  $file = str_replace(get_site_url(), ABSPATH, $data->info['list_upload']['url']);
  // Sử dụng preg_match để tìm kiếm
  if (preg_match($regex, $link, $matches)) {
    $file = ABSPATH . $matches[0];
  }
  if (file_exists($file)) {
    if (($handle = fopen($file, "r")) !== FALSE) {
      while (($data = fgetcsv($handle)) !== FALSE) {
        $csvdata[] = $data;
      }
    }

    fclose($handle);
    $index_loai_dich_vu = array_search('Loại dịch vụ', $csvdata[0]);
    $loai_dich_vu = $csvdata[1][$index_loai_dich_vu];
    $index_of_searchValue = findValueIndex_Center($csvdata, $searchValue);
    $csvdata = array_slice($csvdata, $index_of_searchValue);
    $new_csv_data = [];
    foreach ($csvdata as $key => $value) {
      if ($value[0] == $searchValue) {
        array_push($new_csv_data, $value);
      } else
        break;
    }
    ;
    $csvdata = $new_csv_data;
    ?>
    <div class="owl-wrapper">
      <div class="loop<?= $random_string ?> owl-carousel owl-theme ">
        <?php
        if ($order_by == "yes") {
          $new_array = [];
          foreach ($csvdata as $key => $value) {
            if ($value[CSV_FILE_COLUMN_NHAN] != null) {
              $newArray = move_variable_and_following_elements_to_first_index_Center($csvdata, $key);
              break;
            }
          }
          $csvdata = isset($newArray) ? $newArray : $csvdata;
        }

        foreach ($csvdata as $key => $value) {

          if ($value[0] != $searchValue)
            break;
          else {

            ?>
            <article class="card <?php echo ($value[CSV_FILE_COLUMN_NHAN] != null) ? "has_header_bandage" : ""; ?>">
              <div class="card__header">
                <p class="card__header_title">
                  <?= $value[1] ?>
                </p>
                <?php
                if ($value[CSV_FILE_COLUMN_NHAN] != null) {
                  $bandage_data = explode(',', $value[CSV_FILE_COLUMN_NHAN]);
                  ?>
                  <span class="header_bandage" style="background: <?= $bandage_data[1] ?>;"><?= $bandage_data[0] ?></span>
                  <?php
                  // print_r($bandage_data);
                }
                ?>
              </div>
              <div class="card__content">
                <div class="card__price">
                  <?php if ($value[CSV_FILE_COLUMN_GIA_SAU_GIAM] != null) { ?>
                    <div class="card__price__value">
                      <span>
                        <?= $value[CSV_FILE_COLUMN_GIA_SAU_GIAM] ?>
                      </span>
                      <?php if ($value[CSV_FILE_COLUMN_GIAM] != "") { ?>
                        <span class="card__price_bandage">
                          <?= $value[CSV_FILE_COLUMN_GIAM] ?>
                        </span>
                      <?php } ?>
                    </div>
                    <div class="card__price__period">
                      <p class="card__line-through">
                        <?= $value[CSV_FILE_COLUMN_GIA_GOC] ?>
                      </p>
                      /Tháng
                    </div>
                  <?php } else { ?>
                    <div class="card__price__value">
                      <span>
                        <?= $value[CSV_FILE_COLUMN_GIA_GOC] ?>
                      </span>
                    </div>
                    <div class="card__price__period">
                      /Tháng
                    </div>
                  <?php } ?>

                </div>
                <div class="card__title">
                  <div class="card__system_info">
                    <div class="image_box">
                      <img decoding="async" class="w-100" alt="icon CPU bảng giá Cloud Server"
                        src="https://vietnix.vn/wp-content/uploads/2023/07/cpu-icon.svg">
                    </div>
                    <div class="text_box">
                      <span class="system_info_title">
                        <?= $value[CSV_FILE_COLUMN_CPU] ?>
                      </span>
                    </div>
                  </div>
                  <div class="card__system_info">
                    <div class="image_box">
                      <img decoding="async" class="w-100" alt="icon RAM bảng giá"
                        src="https://vietnix.vn/wp-content/uploads/2023/07/ram-icon.svg">
                    </div>
                    <div class="text_box">
                      <span class="system_info_title">
                        <?= $value[CSV_FILE_COLUMN_RAM] ?>
                      </span>
                    </div>
                  </div>
                  <div class="card__system_info">
                    <div class="image_box">
                      <img decoding="async" class="w-100" alt="icon SSD bảng giá"
                        src="https://vietnix.vn/wp-content/uploads/2023/07/ssd-icon.svg">
                    </div>
                    <div class="text_box">
                      <span class="system_info_title">
                        <?= $value[CSV_FILE_COLUMN_O_CUNG] ?>
                      </span>
                    </div>
                  </div>
                  <?php if ($value[CSV_FILE_COLUMN_DOMAIN] != null) {
                    if ($loai_dich_vu == 'cheap_hosting' || $loai_dich_vu == 'web_hosting' || $loai_dich_vu == 'business_hosting' || $loai_dich_vu == 'vps_gpu') { ?>
                      <div class="card__system_info">
                        <div class="image_box">
                          <?php if ($loai_dich_vu == 'vps_gpu') { ?>
                            <img decoding="async" class="w-100" alt="icon GPU bảng giá"
                              src="https://vietnix.vn/wp-content/uploads/2023/07/gpu-icon.svg">
                          <?php } else { ?>
                            <img decoding="async" class="w-100" alt="icon Domain bảng giá"
                              src="https://vietnix.vn/wp-content/uploads/2023/04/icon_domain_banggia_vpsv4.svg">
                          <?php } ?>

                        </div>
                        <div class="text_box">
                          <span class="system_info_title">
                            <?= $value[CSV_FILE_COLUMN_DOMAIN] ?>
                          </span>
                        </div>
                      </div>
                    <?php }
                  } ?>
                  <?php if ($loai_dich_vu == 'cheap_hosting' || $loai_dich_vu == 'web_hosting' || $loai_dich_vu == 'business_hosting') { ?>
                    <div class="card__system_info">
                      <div class="image_box">
                        <img decoding="async" class="w-100" alt="icon SSL bảng giá"
                          src="https://vietnix.vn/wp-content/uploads/2023/04/icon_ssl_banggia_vpsv4.svg">
                      </div>
                      <div class="text_box">
                        <span class="system_info_title">SSL Miễn Phí</span>
                      </div>
                    </div>
                  <?php } ?>

                </div>
                <?php if($gift_tool_box != '') { ?>
                <div class="card__gift">
                  <div class="card__gift_title">
                    <img decoding="async" class="w-100" alt="icon Gift bảng giá Cloud Server"
                      src="https://vietnix.vn/wp-content/uploads/2023/04/gift_box.svg">
                    <span class="card__gift_title_info" data-gift-id="vnx-tab-gift-<?= $random_string.$slug_tab ?>">
                      <?= $gift_tittle ?>
                    </span>
                  </div>
                </div>
                <?php } ?>

                <div class="card__button_box">
                  <?php if ($custom_button_switch == true) { ?>
                    <a href="<?= $custom_url ?>" <?= $button_attributes ?> class="btn_coversion_post" data-price="<?php echo $value[CSV_FILE_COLUMN_TAM_TINH] ?>" data-period="<?php echo $value[CSV_FILE_COLUMN_CHU_KY] ?>" data-product-name="<?php echo $value[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $value[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>">
                      <button class="bg-[#38A7FF] hover:bg-[#FF8000] text-white font-bold py-2 px-4 rounded card__button_btn">
                        <?= $button_icon['icon'] ?>
                        <?= $button_icon['button_text'] ?>
                      </button>
                    </a>
                  <?php } else { ?>
                    <a href="<?= $value[CSV_FILE_COLUMN_URL_DANG_KY] ?>" class="btn_coversion_post" data-price="<?php echo $value[CSV_FILE_COLUMN_TAM_TINH] ?>" data-period="<?php echo $value[CSV_FILE_COLUMN_CHU_KY] ?>" data-product-name="<?php echo $value[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $value[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>">
                      <button class="bg-[#38A7FF] hover:bg-[#FF8000] text-white font-bold py-2 px-4 rounded card__button_btn">
                        <?= $button_icon['icon'] ?>
                        <?= $button_icon['button_text'] ?>
                      </button>
                    </a>
                  <?php } ?>
                </div>

            </article>
            <?php
          }

        }
        ?>
      </div>
    </div>
  <?php } else {
    echo 'CSV File not found.';
    return;
  }
} catch (Exception $e) {
  echo "view_price_table_carousel have error. Please fix that first!";
}
?>