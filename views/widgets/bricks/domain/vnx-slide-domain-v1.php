<?php
use Bricks\Breakpoints;
try {
  $settings = $data->settings;
  $get_csv = $data->get_upload_file_data();
  $data_slider = array_slice($get_csv['data'], 1);
  $button_register = $settings['button_register'] ?? 'Kiểm tra tên miền';
  $button_icon = $settings['button_register_icon']['icon'] ?? 'fa-solid fa-arrow-right';
  $breakpoints = [];
  $media_query = Breakpoints::$is_mobile_first ? 'min' : 'max';
  $type = $settings['loop_slide'] ?? 'loop';

  $link_rel = isset($settings['rel']) ? 'rel="' . $settings['rel'] . '"' : "";
  $link_label = isset($settings['ariaLabel']) ? 'aria-label="' . $settings['ariaLabel'] . '"' : "";
  $link_title = isset($settings['title']) ? 'title="' . $settings['title'] . '"' : "";
  $link_target = $settings['new_tab'] ? 'target="_blank"' : '';
  $splide_options = [
    'type' => $type,
    'gap' => !empty($settings['gap']) ? $settings['gap'] : '10px',
    'start' => !empty($settings['item_start']) ? $settings['item_start'] : 0,
    'perPage' => !empty($settings['perPage']) && $type !== 'fade' ? $settings['perPage'] : 1,
    'perMove' => !empty($settings['perMove']) && $type !== 'fade' ? $settings['perMove'] : 1,
    'arrows' => true,
    'classes' => [
      'arrows' => 'splide__arrows',
      'arrow' => 'splide__arrow',
      'prev' => 'splide__arrow--prev',
      'next' => 'splide__arrow--next',
    ],
    'pagination' => false,
    'mediaQuery' => $media_query,
  ];
  foreach (Breakpoints::$breakpoints as $breakpoint) {
    foreach (array_keys($splide_options) as $option) {
      $setting_key = $breakpoint['key'] === 'desktop' ? $option : "$option:{$breakpoint['key']}";
      $breakpoint_width = $breakpoint['width'] ?? false;
      $setting_value = $settings[$setting_key] ?? false;

      // Spacing requires a unit
      if ($option === 'gap') {
        // Add default unit
        if (is_numeric($setting_value)) {
          $setting_value = "{$setting_value}px";
        }
      }

      if ($option === 'perPage' && isset($breakpoint['base']) && $setting_value) {
        $splide_options['perPage'] = intval($setting_value);
      }

      if ($breakpoint_width && $setting_value !== false) {
        $breakpoints[$breakpoint_width][$option] = $setting_value;
      }
    }
  }
  if (count($breakpoints)) {
    $splide_options['breakpoints'] = $breakpoints;
  }
  if (is_array($splide_options)) {
    $splide_options = wp_json_encode($splide_options);
  }
  ?>
  <div class="vnx_wrap_box">
    <div class="vnx_box_listslide splide" data-splide='<?php echo trim($splide_options); ?>' data-vnx_destroy="478" v-show="datascroll == false">
      <div class="splide__arrows splide__arrows--ltr service-price-arrow">
        <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Previous slide" aria-controls="splide01-track">
          <i class="fas fa-angle-left"></i>
        </button>
        <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide" aria-controls="splide01-track">
          <i class="fas fa-angle-right"></i>
        </button>
      </div>
      <div class="splide__track">
        <div class="splide__list">
          <?php
          if (is_array($data_slider)) {
            foreach ($data_slider as $key => $value) {
              ?>
              <div class="vnx_box_slide splide__slide border border-[#E0E0E0] rounded-[8px] overflow-hidden">
                <div class="vnx_slide">
                  <div class="box first">
                    <div class="box_img h-10 lg:h-12">
                      <img src="<?php echo $value[1]; ?>" alt="icon domain">
                    </div>
                    <div class="box_content">
                      <p class="text"><?php echo $value[2]; ?></p>
                    </div>
                  </div>
                  <div class="box second">
                    <div class="box_price">
                      <?php
                      $price_infor = explode(" | ", $value[3]);
                      if (isset($price_infor[1]) && !empty($price_infor[1])) {
                        ?>
                        <div class="price_original flex items-center gap-2">
                          <p class="text"><?php echo $price_infor[1]; ?>đ</p><span class="discount"><?php echo $price_infor[2]; ?></span>
                        </div>
                      <?php } ?>
                      <div class="price_reduced flex items-center gap-1">
                        <p class="text"><?php echo $price_infor[0]; ?>đ</p><span class="year">/Năm</span>
                      </div>
                    </div>
                    <div class="box_button">
                      <?php
                      echo '<a href="' . $value[4] . '" ' . $link_target . ' ' . $link_label . ' ' . $link_title . ' ' . $link_rel . ' class="vnx_btn_redirect lg:p-2 p-1 w-full"><span>' . $button_register . '</span> <i class="vnx_icon ' . $button_icon . '"></i></a>';
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php }
          } ?>
        </div>
      </div>
    </div>
    <div class="vnx_box_listscroll" v-show="datascroll == true">
      <?php
      if (is_array($data_slider)) {
        foreach ($data_slider as $key => $value) {
          ?>
          <div class="vnx_box_slide splide__slide border border-[#E0E0E0] rounded-[8px] overflow-hidden">
            <div class="vnx_slide">
              <div class="box first">
                <div class="box_img h-10 lg:h-12">
                  <img src="<?php echo $value[1]; ?>" alt="icon domain">
                </div>
                <div class="box_content">
                  <p class="text"><?php echo $value[2]; ?></p>
                </div>
              </div>
              <div class="box second">
                <div class="box_price">
                  <?php
                  $price_infor = explode(" | ", $value[3]);
                  if (isset($price_infor[1]) && !empty($price_infor[1])) {
                    ?>
                    <div class="price_original flex items-center gap-2">
                      <p class="text"><?php echo $price_infor[1]; ?>đ</p><span class="discount"><?php echo $price_infor[2]; ?></span>
                    </div>
                  <?php } ?>
                  <div class="price_reduced flex items-center gap-1">
                    <p class="text"><?php echo $price_infor[0]; ?>đ</p><span class="year">/Năm</span>
                  </div>
                </div>
                <div class="box_button">
                  <?php
                  echo '<a href="' . $value[4] . '" ' . $link_target . ' ' . $link_label . ' ' . $link_title . ' ' . $link_rel . ' class="vnx_btn_redirect lg:p-2 p-1 w-full"><span>' . $button_register . '</span> <i class="vnx_icon ' . $button_icon . '"></i></a>';
                  ?>
                </div>
              </div>
            </div>
          </div>
        <?php }
      } ?>
    </div>
  </div>
  <script>

  </script>
  <?php
} catch (Exception $e) {
  echo $e->getMessage();
}