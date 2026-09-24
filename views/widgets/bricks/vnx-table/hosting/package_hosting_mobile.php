<?php
$data = isset($data) ? $data : new stdClass();
if (!defined('TEN_GOI'))
  define('TEN_GOI', findIndexInObject_Center($data->array[0], 'Tên gói'));
if (!defined('LOAI_GOI'))
  define('LOAI_GOI', findIndexInObject_Center($data->array[0], 'Loại gói'));
if (!defined('MO_TA_NGAN'))
  define('MO_TA_NGAN', findIndexInObject_Center($data->array[0], 'Mô tả ngắn'));
if (!defined('ICON'))
  define('ICON', findIndexInObject_Center($data->array[0], 'Icon'));
if (!defined('DON_VI'))
  define('DON_VI', findIndexInObject_Center($data->array[0], 'Đơn vị'));
if (!defined('GIAM_TONG'))
  define('GIAM_TONG', findIndexInObject_Center($data->array[0], 'Giảm tổng'));
if (!defined('CPU'))
  define('CPU', findIndexInObject_Center($data->array[0], 'CPU'));
if (!defined('RAM'))
  define('RAM', findIndexInObject_Center($data->array[0], 'Ram'));
if (!defined('NVME'))
  define('NVME', findIndexInObject_Center($data->array[0], 'Dung lượng NVMe'));
if (!defined('DUNG_LUONG_THEM'))
define('DUNG_LUONG_THEM', findIndexInObject_Center($data->array[0], 'Dung lượng thêm'));
if (!defined('DOMAIN'))
  define('DOMAIN', findIndexInObject_Center($data->array[0], 'Domain chính'));
if (!defined('TRAFFIC'))
  define('TRAFFIC', findIndexInObject_Center($data->array[0], 'Lượng truy cập'));
if (!defined('UU_DIEM'))
  define('UU_DIEM', findIndexInObject_Center($data->array[0], 'Ưu điểm'));
if (!defined('SPOTLIGHT'))
  define('SPOTLIGHT', findIndexInObject_Center($data->array[0], 'Spotlight'));
if (!defined('MOBILE'))
  define('MOBILE', findIndexInObject_Center($data->array[0], 'Hiện trên mobile'));


$iconIndex = array_search('Icon', $data->array[0]);
$priceIndex = array_search('Đơn vị', $data->array[0]);
$cpuIndex = array_search('CPU', $data->array[0]);
$count_row = count($data->array[0]);

$row_cyc = [];
for ($i = 0; $i < $count_row; $i++) {
  if ($i > $iconIndex && $i < $priceIndex) {
    array_push($row_cyc, $data->info[$i]);
  }
}
for ($col = 1; $col < count($data->info[0]); $col++) :
  $background = empty($data->info[SPOTLIGHT][$col]) ? 'bg-white' : 'vnx-bg-brand text-white';
  $btn_spt = empty($data->info[SPOTLIGHT][$col]) ? '' : 'bg-btn-spotlight';
  $color = empty($data->info[SPOTLIGHT][$col]) ? '' : 'spotlight';
  $active = empty($data->info[SPOTLIGHT][$col]) ? 'hidden' : 'show';
  $extra_capacity = empty($data->info[SPOTLIGHT][$col]) ? 'text-[#FF9038]' : 'text-[#FFF000]';
  $show = empty($data->info[MOBILE][$col]) ? 'hidden' : '';

  $row_gia = [];
  for ($i = 0; $i < $count_row; $i++) {
    if ($i > $iconIndex && $i < $priceIndex) {
      array_push($row_gia, $data->info[$i][$col]);
    }
  }
?>
  <div class="vnx-table-price-hosting table-package-hosting text-sm rounded-md  <?php echo $background; ?> lg:table border-2 rounded-md border-[#38A7FF] <?php echo $show; ?>">
    <div class="w-full flex items-center justify-center">
      <div class="top-spotlight <?php echo $active; ?>">
        <i class="fa-solid fa-star"></i>
        <span><?php echo $data->info[SPOTLIGHT][$col] ?></span>
      </div>
    </div>
    <div class="text-center table-title min-h-[80px]">
      <p class="vnx-title-table text-2xl"><?php echo $data->info[TEN_GOI][$col] ?></p>
      <p class="text-sm font-normal"></p><?php echo $data->info[MO_TA_NGAN][$col] ?></p>
    </div>
    <?php 
    $img_arr = explode(" - ", $data->info[ICON][$col]);
    ?>
    <div class="flex justify-center my-8">
      <img src="<?php echo $img_arr[0]; ?>"  alt="<?php echo $img_arr[1]; ?>" class="w-24 h-24">
    </div>
    <div class="price-content">
      <?php
      $id_slug = 0;
      foreach ($row_gia as $key => $price) {
        if ($price) {
          $str_price = $price;
          $arr_price = explode(" - ", $str_price);
          if (isset($arr_price[1])) {
            $sub_price = str_replace(['[', ']'], ['', ''], $arr_price[1]);
            $arr_sub_price = explode("/", $sub_price);
          }
          $default = isset($arr_sub_price[1]) && $arr_sub_price[1] != " " && $arr_sub_price[1] != NULL ? "show" : "hidden";

          if (isset($arr_sub_price[0]) && $arr_sub_price[0] != null && $arr_sub_price[0] != " ") {
      ?>
            <div class="price-row text-center <?php echo $default; ?>" id="price-id-<?php echo $id_slug; ?>">
              <span class=" <?php echo $default; ?> el-custom-text-price leading-5 font-bold"><?php echo $arr_sub_price[0]; ?></span>
              <span class="el-custom-text-price-month">/<?php echo $data->info[DON_VI][$col] ?></span></br>
              <span class="el-custom-text-price-base leading-5 text-base <?php echo  $color; ?>"><?php echo $arr_price[0]; ?></span>
              <?php if (isset($data->info[GIAM_TONG][$col]) && !empty($data->info[GIAM_TONG][$col])) { ?>
                <span class="text-xs p-1 el-custom-text-discount"><?php echo $data->info[GIAM_TONG][$col]; ?></span>
              <?php } ?>
            </div>
          <?php } else { ?>
            <div class="price-row text-center <?php echo $default; ?>" id="price-id-<?php echo $id_slug; ?>">
              <span class=" <?php echo $default; ?> el-custom-text-price leading-5 font-bold"><?php echo $arr_price[0] ?></span>
              <span class="el-custom-text-price-month text-[#D7DBE2]">/<?php echo $data->info[DON_VI][$col] ?></span></br>
            </div>
          <?php } ?>
      <?php
        }
        $id_slug++;
      }
      ?>
    </div>
    <div class="selected my-8 w-full flex">
      <div class="vnx-button-select w-3/5 <?php echo $btn_spt; ?>">
        <p class="text"> </p>
        <i class="fa-solid fa-caret-down"></i>
      </div>
      <a rel="nofollow" data-price="" data-period="" data-product-name="<?php echo $data->info[TEN_GOI][$col] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $data->info[TEN_GOI][$col])); ?>" href="#" class="vnx-button-register w-2/5 <?php echo $btn_spt; ?> text url-register">Đăng ký</a>

    </div>
    <div class="hidden-url-table hidden">
      <?php
      $row_url = [];
      for ($i = 0; $i < $count_row; $i++) {
        if ($i > $priceIndex && $i < $cpuIndex) {
          array_push($row_url, $data->info[$i][$col]);
        }
      }
      $id_url = 0;
      foreach ($row_url as $key => $item_url) {
        if (!empty($item_url)) {
      ?>
          <p class="url-price" id="url-id-<?php echo $id_url; ?>"><?php echo $item_url; ?></p>
      <?php }
        $id_url++;
      } ?>
    </div>
    <div class="hidden-table-select">
      <?php
      $id_package = 0;
      foreach ($row_cyc as $key => $cyc) {
        $str_cyc = $cyc[$col];
        $arr_cyc = explode(" - ", $str_cyc);
        if (isset($arr_cyc[1])) {
          $sub_cyc = str_replace(['[', ']'], ['', ''], $arr_cyc[1]);
          $arr_sub_cyc = explode("/", $sub_cyc);
        }
        $default = isset($arr_sub_cyc[1]) && $arr_sub_cyc[1] != " " ? "active" : "";
        if (isset($cyc[$col])) {
      ?>
          <div class="vnx-select-option <?php echo $default; ?>" data-id="price-id-<?php echo $id_package; ?>" data-url="url-id-<?php echo $id_package; ?>">
            <div class="relative icon-estimating inline-block">
              <img class="h-8 cursor-pointer icon-estimating m-auto" src="https://vietnix.vn/wp-content/uploads/2021/09/tag-type1.svg" alt="icon tạm tính giá hosting wordpress" />
              <div class="absolute bg-white w-48 text-left estimating-cost py-2 px-4 rounded-lg table-package">
                <span class="font-bold text-sm text-[#525666]">Tạm tính</span>
                <?php if (isset($arr_cyc[2])) { ?>
                  <span class="el-custom-text-price text-sm leading-5 font-bold" id-price="<?php echo $arr_cyc[2]; ?>">
                    <?php echo $arr_cyc[2]; ?>
                  </span>
                <?php } else { ?>
                  <span class="el-custom-text-price text-sm leading-5 font-bold" id-price="last-update">
                    Đang cập nhật
                  </span>
                <?php } ?>
              </div>
            </div>
            <span class="text-base p-1 title"><?php echo $cyc[0]; ?></span>
            <?php if (isset($arr_sub_cyc[1]) && $arr_sub_cyc[1] != null && $arr_sub_cyc[1] != " ") { ?>
              <span class="text-xs p-1 green"><?php echo $arr_sub_cyc[1]; ?></span>
            <?php }
            if (isset($arr_sub_cyc[2]) && $arr_sub_cyc[2] != null && $arr_sub_cyc[2] != " ") { ?>
              <span class="text-xs p-1 red"><?php echo $arr_sub_cyc[2]; ?></span>
            <?php } ?>
          </div>
      <?php
        }
        $id_package++;
      } ?>
    </div>
    <div class="line"></div>
    <div class="param <?php echo $btn_spt; ?>">
      <p class="text-base font-normal">Thông số</p>
      <div class="vnx-row-param">
        <?php
        if (!empty(vnxCutString_Center($data->info[CPU][$col]))) {
          $cpu = vnxCutString_Center($data->info[CPU][$col]);
        ?>
          <span><?php echo $cpu[1]; ?></span>
          <span><?php echo $cpu[0]; ?> Core</span>
        <?php } ?>
      </div>
      <div class="vnx-row-param">
        <?php
        if (!empty(vnxCutString_Center($data->info[RAM][$col]))) {
          $ram = vnxCutString_Center($data->info[RAM][$col]);
        ?>
          <span><?php echo $ram[1]; ?></span>
          <span><?php echo $ram[0]; ?> Ram</span>
        <?php } ?>
      </div>
      <div class="vnx-row-param">
        <?php
        if (!empty(vnxCutString_Center($data->info[NVME][$col]))) {
          $nvme = vnxCutString_Center($data->info[NVME][$col]);
        ?>
          <span><?php echo $nvme[1]; ?></span>
          <span><?php echo $nvme[0]; ?> NVMe</span>
            <?php
              if (isset($data->info[DUNG_LUONG_THEM][$col]) && !empty(vnxCutString_Center($data->info[DUNG_LUONG_THEM][$col]))) {
                $dlthem = vnxCutString_Center($data->info[DUNG_LUONG_THEM][$col]);
              ?>
              <span class="<?=$extra_capacity?>">+<?php echo $dlthem[0]; ?> Free</span>
            <?php } ?>
        <?php } ?>
      </div>
      <div class="vnx-row-param">
        <?php
        if (!empty(vnxCutString_Center($data->info[DOMAIN][$col]))) {
          $domain = vnxCutString_Center($data->info[DOMAIN][$col]);
        ?>
          <span><?php echo $domain[1]; ?></span>
          <span><?php echo $domain[0]; ?> Domain</span>
        <?php } ?>
      </div>
      <div class="vnx-row-param">
        <?php
        if (!empty(vnxCutString_Center($data->info[TRAFFIC][$col]))) {
          $traffic = vnxCutString_Center($data->info[TRAFFIC][$col]);
        ?>
          <span><?php echo $traffic[2]; ?></span>
          <span><?php echo $traffic[0]; ?> - <?php echo $traffic[1]; ?> Traffic/tháng</span>
        <?php } ?>
      </div>
    </div>
    <div class="line"></div>
    <div class="point">
      <p class="text-base font-normal">Ưu điểm sử dụng</p>
      <div class="vnx-list-point <?php echo $color; ?>">
        <?php
        $uu = explode("- ", $data->info[UU_DIEM][$col]);
        foreach ($uu as $key => $uu_diem) {
          if (isset($uu_diem) && $uu_diem != "") {
        ?>
            <div class="item-point flex mt-[22px]">
              <i aria-hidden="true" class="fas fa-check-circle"></i>
              <p class="ml-1"><?php echo $uu_diem; ?></p>
            </div>
        <?php }
        } ?>
      </div>
    </div>
  </div>
<?php
endfor;
?>