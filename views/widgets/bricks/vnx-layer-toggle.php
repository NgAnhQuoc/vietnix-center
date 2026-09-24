<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
$settings = $data->settings;

$layers = $settings['layer_toggle'] ? $settings['layer_toggle'] : [];
$data->set_attribute('_root', 'class', 'vnx-layer-toggle');
if (count($layers) ==  0) {
  return;
}
$i = 0;
echo "<div {$data->render_attributes('_root')}>";
?>

<div class="flex flex-col gap-4 vnx-layer-togle">
  <?php while ($i < count($layers)) :
    if (isset($layers[$i]['title']) && isset($layers[$i]['description'])) {
  ?>
      <!-- Begin row -->
      <div class="flex flex-row relative vnx-layer-toggle-row <?php if ($i == 0) echo 'active'; ?>">
        <!-- Begin Content -->
        <div class="flex flex-1 flex-row p-4 vnx-layer-toggle-content cursor-pointer">
          <?php if (isset($layers[$i]['image'])) { ?>
            <div class="flex md:hidden w-16 h-9 mr-4 vnx-layer-toggle-image">
              <img src="<?php esc_attr_e($layers[$i]['image']['url']) ?>" class="bg-cover bg-no-repeat">
            </div>
          <?php } ?>
          <div class="flex flex-col flex-1">
            <div class="font-bold text-base text-[#333333]">
              <?php esc_html_e($layers[$i]['title']) ?>
            </div>
            <div class="font-normal text-xs text-[#828282]">
              <?php esc_html_e($layers[$i]['description']) ?>
            </div>
          </div>
        </div>

        <!-- Line -->

        <div class="hidden md:flex relative items-center">
          <div class="vnx-layer-line  w-16 border border-solid border-[#6D6E71]">
          </div>
        </div>
        <div class="hidden relative items-center  md:flex ml-4 min-w-[190px] vnx-layer-toggle-image" style="z-index: <?php esc_attr_e(count($layers) - $i) ?>;">
          <?php if (isset($layers[$i]['image'])) { ?>
            <div class="absolute vnx-layer-toggle-image-wrap">
              <img src="<?php esc_attr_e($layers[$i]['image']['url']) ?>" class="bg-cover bg-no-repeat w-full h-full">
            </div>
          <?php } ?>
        </div>
      </div>

  <?php
    }
    $i++;
  endwhile;
  ?>
</div>
<?php
echo "</div>";
?>
<script>
  window.addEventListener("DOMContentLoaded", (event) => {
    jQuery('.vnx-layer-toggle-content').click(function() {
      jQuery('.vnx-layer-toggle-row').removeClass("active");
      jQuery(this).parent().addClass("active");
    });
  });
</script>