<?php
$data = isset($data) ? $data : new stdClass();

$title = isset($data->title) ? $data->title : "";
$desc = isset($data->desc) ? $data->desc : 'Hạ tầng lớn - cam kết ổn định - hỗ trợ nhanh chóng';
$image = isset($data->image) ? VNX_PLUGIN_URL_CENTER . $data->image : VNX_PLUGIN_URL_CENTER . 'assets/img/web/colocation/banner.png';
$background = isset($data->background) ? $data->background : '';

$hideCta = isset($data->hideCta) ? $data->hideCta : false;
?>

<div class="bg-gray-100 border-b border-gray-200">
  <div class="container flex flex-col lg:flex-row py-5">
    <div class="w-full lg:w-1/2 flex flex-col justify-center py-5 lg:py-16 text-center lg:text-left">
      <h1 class="text-primary vnx-page-title">
        <?= $title ?>
      </h1>

      <div class="f-16 lg:f-18 mt-3 text-gray-700">
        <?= html_entity_decode($desc); ?>
      </div>

      <?php if (!$hideCta) : ?>
        <div class="mt-8">
          <!-- <button onclick="(function(){ Tawk_API.toggle() })();" class="py-2 px-4 pr-6 rounded-full bg-warning outline-none font-semibold text-white">
            <i class="fas fa-life-ring mr-2"></i>
            Liên hệ tư vấn
          </button> -->

          <!--  -->
          <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <div class="rounded-md h-12 relative overflow-hidden">
              <i class="fas fa-search absolute text-gray-400 f-16" style="top: 15px; left: 15px;"></i>
              <input name="s" type="text" class="f-16 rounded-md w-80 h-full outline-none pl-10 pr-4 placeholder-gray-400 border-gray-300 focus:border-b" placeholder="Tìm kiếm" value="<?php echo get_search_query(); ?>">
            </div>
          </form>
          <!--  -->
        </div>
      <?php endif; ?>
    </div>

    <div class="w-full lg:w-1/2 flex justify-center lg:justify-end items-center" style="min-height: 360px;">
      <img src="<?= $image ?>" width="auto" height="auto" alt="Colocation" style="max-height: 260px;" />
    </div>
  </div>
</div>