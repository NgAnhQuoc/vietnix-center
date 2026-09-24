<?php
try {
$data = isset($data) ? $data : new stdClass();


if (!defined('GIA'))
  define('GIA', findIndexInObject_Center($data->header, 'Giá'));
if (!defined('GIAM'))
  define('GIAM', findIndexInObject_Center($data->header, 'Giảm'));
if (!defined('GIA_GIAM'))
  define('GIA_GIAM', findIndexInObject_Center($data->header, 'Giá Giảm'));
if (!defined('DON_VI'))
  define('DON_VI', findIndexInObject_Center($data->header, 'Đơn vị'));
if (!defined('CPU'))
  define('CPU', findIndexInObject_Center($data->header, 'CPU'));
if (!defined('NVME'))
  define('NVME', findIndexInObject_Center($data->header, 'Dung lượng NVMe'));
if (!defined('TRUY_CAP_CAO_DIEM'))
  define('TRUY_CAP_CAO_DIEM', findIndexInObject_Center($data->header, 'Lưu lượng truy cập (Tháng)'));

if (!defined('DOMAIN_CHINH'))
  define('DOMAIN_CHINH', findIndexInObject_Center($data->header, 'Domain chính'));
if (!defined('BACKUP'))
  define('BACKUP', findIndexInObject_Center($data->header, 'Backup dữ liệu (Hàng ngày)'));
if (!defined('RAM'))
  define('RAM', findIndexInObject_Center($data->header, 'RAM'));

if (!defined('FREESSL'))
  define('FREESSL', findIndexInObject_Center($data->header, 'Free SSL'));
if (!defined('IMUNIFY360'))
  define('IMUNIFY360', findIndexInObject_Center($data->header, 'Imunify360'));
if (!defined('TANG_THEME'))
  define('TANG_THEME', findIndexInObject_Center($data->header, 'Tặng Theme & Plugin'));
if (!defined('MEMCACHED'))
  define('MEMCACHED', findIndexInObject_Center($data->header, 'Memcached Socket'));
if (!defined('REDIS'))
  define('REDIS', findIndexInObject_Center($data->header, 'Redis Socket'));
if (!defined('JETBACKUP'))
  define('JETBACKUP', findIndexInObject_Center($data->header, 'Jetbackup'));
if (!defined('URL'))
define('URL', findIndexInObject_Center($data->header, 'URL'));
if (!defined('TAM_TINH'))
define('TAM_TINH', findIndexInObject_Center($data->header, 'Tổng tạm tính'));
if (!defined('GOI_DV'))
define('GOI_DV', findIndexInObject_Center($data->header, 'Gói dịch vụ'));
if (!defined('CHU_KY'))
define('CHU_KY', findIndexInObject_Center($data->header, 'Chu kỳ'));

$price_data = $data->data;
$text_row_1 = [CPU, RAM,NVME, DOMAIN_CHINH, BACKUP, TRUY_CAP_CAO_DIEM];
$settings = $data->settings;
// print_r($data->header);  
$random_string = $data->random_string;
$gift_tittle = ($data->settings['list_tooltip_gift']) ?? 'title';
$list_gift_description = ($data->settings['list_tooltip_gift_description']) ?? 'des';
$custom_button_switch = (isset($data->settings['custom_button_url_switch'])) ? true : false;
$custom_url = ($data->settings['custom_button_url']['url']) ?? '#';
$button_attributes = '';
if ($custom_button_switch == true) {
  if (isset($settings['custom_button_url']['newTab']) && $settings['custom_button_url']['newTab'] !== '') {
    $button_attributes .= 'target="_blank" ';
  }
  if (isset($settings['custom_button_url']['rel']) && $settings['custom_button_url']['rel'] !== '') {
    $button_attributes .= 'rel="'.$settings['list_viewmore_url']['rel'].'" ';
  }
}
// print_r($data->settings);
$button_text = ($data->settings['list_button_text']) ?? 'Mua ngay';
?>
<div class="owl-wrapper">
    <div class="loop<?= $random_string ?> owl-carousel owl-theme ">
    <?php foreach($price_data as $price => $value){ ?>  
      <div class="w-full border rounded-md border-[#38A7FF] my-3 flex gap-y-5 flex-row flex-wrap py-5 <?php if($value[2] == 'Tiêu chuẩn') echo 'backg-tc' ?>">
      <?php if($value[2] == 'Tiêu chuẩn'):?>
          <div class="bandage-tc left-0 right-0 mx-auto w-full absolute flex justify-center"><span class="py-1 px-2.5 text-xs text-white rounded-md flex flex-nowrap justify-center items-center gap-x-1"><img src="https://vietnix.vn/wp-content/uploads/2023/08/Star.svg" alt="star" class="w-3"> Bán chạy nhất</span></div>
        <?php endif; ?>
        <div class="heading w-full px-2.5">
          <div class="text-center">
            <div class="text-2xl <?= ($value[2] == 'Tiêu chuẩn') ? 'text-white' : 'text-[#38A7FF]' ?> font-bold uppercase"><?= $value[2] ?></div>
            <div class="price">
              <div class="flex flex-nowrap justify-center items-center gap-x-1 mt-3.5">
                <div class="text-xs font-bold line-through  <?= ($value[2] == 'Tiêu chuẩn') ? 'text-white opacity-70' : ' text-[#B1B1B1]' ?> font-normal"><?= $value[GIA] ?></div>
                <div class="text-xs font-normal <?= ($value[2] == 'Tiêu chuẩn') ? 'text-white' :'text-[#525666]' ?>">/tháng</div>
                <div class="text-xs font-bold font-normal px-1 py-px bg-[#EB5757] text-white rounded"><?= $value[GIAM] ?></div>
              </div>
              <div class="font-black mt-3 text-2xl text-[#FFCE62]"><?= $value[GIA_GIAM] ?></div>
            </div>
          </div>
        </div>
        <div class="cart-body w-full px-2.5 flex gap-y-4 flex-column flex-wrap">
          <?php foreach($text_row_1 as $item): ?>
          <div class="flex flex-row gap-x-1 w-full items-center">
            <img src="https://vietnix.vn/wp-content/uploads/2023/08/circle-check.svg" alt="" class="w-3 <?php if($value[2] == 'Tiêu chuẩn') echo 'filter-white' ?>"><span class="<?= ($value[2] == 'Tiêu chuẩn') ? 'text-white' :'text-[#525666]' ?> text-sm"><?php if($item == BACKUP) echo "Backup tự động ";?><?=$value[$item] ?><?php if($item == DOMAIN_CHINH) echo " Domain"; else if($item == NVME) echo " NVME"; else if($item == TRUY_CAP_CAO_DIEM) echo " Traffic/tháng" ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="gift w-full px-2.5 relative">
            <div class="w-fit flex-row flex-nowrap items-center flex gap-x-1 cursor-pointer gift-title <?php if($value[2] == 'Tiêu chuẩn') echo 'filter-FFCE62' ?>">
              <img src="https://vietnix.vn/wp-content/uploads/2023/08/gift.svg" alt="" class="w-5">
              <span class="text-[#38A7FF] text-xs font-normal underline underline-offset-2 gift-text italic"><?=$gift_tittle?></span>
            </div>
            <div class="absolute bottom-full left-0 right-0 mx-auto w-full mb-5 gift-data">
              <div class="p-5 bg-white border rounded-md border-[#38A7FF] text-xs font-normal text-[#3F3F3F]">
                <?=$list_gift_description?>
              </div>
            </div>
        </div>
        <div class="price-button px-2.5 w-full">
          <?php if ($custom_button_switch == true) { ?>
            <a href="<?= $custom_url ?>" <?= $button_attributes ?> class="btn_coversion_post" data-price="<?php echo $value[TAM_TINH] ?>" data-period="<?php echo $value[CHU_KY] ?>" data-product-name="<?php echo $value[GOI_DV] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $value[GOI_DV])); ?>">
            <button type="button" class="text-white <?= ($value[2] == 'Tiêu chuẩn') ? 'button-tc ' : 'bg-[#38A7FF] hover:bg-[#0074CF]' ?> font-medium rounded-lg text-sm px-5 py-2.5 w-full font-normal text-base text-white"><?=$button_text?></button>
            </a>
          <?php } else { ?>
            <a href="<?=$value[URL]?>" rel="nofollow" class="btn_coversion_post" data-price="<?php echo $value[TAM_TINH] ?>" data-period="<?php echo $value[CHU_KY] ?>" data-product-name="<?php echo $value[GOI_DV] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $value[GOI_DV])); ?>">
            <button type="button" class="text-white <?= ($value[2] == 'Tiêu chuẩn') ? 'button-tc ' : 'bg-[#38A7FF] hover:bg-[#0074CF]' ?> font-medium rounded-lg text-sm px-5 py-2.5 w-full font-normal text-base text-white"><?=$button_text?></button>
            </a>
          <?php } ?>
        </div>
      </div>
      <? } ?>
    </div>
</div>       
<style>
  .gift-data{
    box-shadow: 0px 4px 10px 0px rgba(0, 0, 0, 0.10);
    display: none;
  }
  .gift-title:hover .gift-text, .gift-title:hover img {
    filter: brightness(0) saturate(100%) invert(88%) sepia(60%) saturate(7462%) hue-rotate(310deg) brightness(102%) contrast(114%);
  }
  .gift-title:hover + .gift-data{
    display: block;
  }
  .backg-tc{
    background: linear-gradient(205deg, #38A7FF 0%, #0065DE 100%);
    position: relative;
  }
  .filter-white{
    filter: brightness(0) saturate(100%) invert(100%) sepia(0%) saturate(7498%) hue-rotate(158deg) brightness(103%) contrast(102%);
  }
  .filter-FFCE62 .gift-text, .filter-FFCE62 img{
    filter: brightness(0) saturate(100%) invert(88%) sepia(60%) saturate(7462%) hue-rotate(310deg) brightness(102%) contrast(114%);
  }
  .button-tc{
    background: linear-gradient(153deg, #FFBD2A 0%, #FD7659 100%);
  }
  .button-tc:hover{
    background: linear-gradient(153deg, #FD7659 0%, #CF2500 100%);
  }
  .bandage-tc{
    top: -12px;
  }
  .bandage-tc span{
    background: linear-gradient(153deg, #FFBD2A 0%, #FD7659 100%);
  }
  .owl-carousel .owl-stage-outer{
    overflow-x: visible;
    overflow-y: unset;
  }
 #vnx_post_content article .single-article-content #vnx-wordpress-hosting-table ul{
    margin-top: 0px;
    margin-bottom: 0px;
    padding-left: 24px!important;
  }
  #vnx-wordpress-hosting-table #vnx_post_content article .single-article-content #vnx-wordpress-hosting-table ul>li{
    margin-bottom: 4px;
  }
</style>
<?php 
} catch (Exception $e) {
    echo "Hosing wordpress template mobile have error. Please fix that first!";
}
?>