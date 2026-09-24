<?php
// Hardcoded data for testing
try {
  $settings = $data->settings;
  $highlight_badge_text = isset($settings['img-popular']) ? $settings['img-popular'] : '';
  $button_text = isset($settings['item-textbutton']) ? $settings['item-textbutton'] : 'Đăng ký ngay';
  $background_image_header = isset($settings['background-image-header']) ? $settings['background-image-header']['url'] : '';
  $sticky_header = isset($settings['sticky-header']) ? $settings['sticky-header'] : '65px';
  $img_buttonchat = isset($settings['img-chat']) ? $settings['img-chat'] : '';
  $text_buttonchat = isset($settings['button-chat']) ? $settings['button-chat'] : 'Nhận tư vấn';
  $class_chatbutton = isset($settings['class-chat']) ? $settings['class-chat'] : '';
  $item_popular = isset($settings['item-popular']) ? $settings['item-popular'] : '';
  $category_text = isset($settings['category-text']) ? $settings['category-text'] : '';
  $get_csv = $data->get_data_from_csv();
  if ($get_csv['status'] == 'success' && !empty($get_csv['data'])) {
    $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
    $header_data = $csv_data[0];
    $header_link = $csv_data[1];
    $header_price = $csv_data[2];
    $header_price_total = $csv_data[3];
    $hosting_count = count($header_data) - 3;
  } else {
    if (current_user_can('update_core'))
      echo '<div class="vnx_error no_data"><b>' . $get_csv['message'] . '</div>';
    return;
  }
  ?>
  <div class="vnx-table-compare-wrapper" data-count-items="<?php echo esc_attr($hosting_count); ?>">
    <?php if ($hosting_count > 3) { ?>
      <!-- Navigation Buttons for Mobile -->
      <div class="vnx-table-navigation vnx-table-navigation-mobile">
        <div class="vnx-table-navigation-wrapper">
          <button class="vnx-nav-button prev" aria-label="Previous packages" disabled>
            <i class="ion-ios-arrow-back"></i>
          </button>
          <button class="vnx-nav-button next" aria-label="Next packages">
            <i class="ion-ios-arrow-forward"></i>
          </button>
        </div>
      </div>
    <?php } ?>

    <div class="vnx-table-compare-container" data-table-count="<?php echo esc_attr($hosting_count); ?>">
      <!-- Sticky Header Row -->
      <div class="vnx-table-header sticky-header" style="top: <?php echo esc_attr($sticky_header); ?>; background-image: url(<?php echo esc_url($background_image_header); ?>);">
        <?php if ($hosting_count > 6) { ?>
          <!-- Navigation Buttons for Desktop -->
          <div class="vnx-table-navigation vnx-table-navigation-desktop">
            <div class="vnx-table-navigation-wrapper">
              <button class="vnx-nav-button prev" aria-label="Previous packages" disabled>
                <i class="ion-ios-arrow-back"></i>
              </button>
              <button class="vnx-nav-button next" aria-label="Next packages">
                <i class="ion-ios-arrow-forward"></i>
              </button>
            </div>
          </div>
        <?php } ?>
        <div class="vnx-table-cell sticky-col header-sticky-col">
          <div class="header-action">
            <?php if (!empty($img_buttonchat['url'])) { ?>
              <img src="<?php echo esc_url($img_buttonchat['url']); ?>" alt="image button">
            <?php } ?>
            <button class="vnx-button-chat <?php echo esc_attr($class_chatbutton); ?>"><?php echo esc_html($text_buttonchat); ?></button>
          </div>
        </div>
        <div class="vnx-table-cell-list" style="grid-template-columns: repeat(<?php echo esc_attr($hosting_count); ?>, 1fr);">
          <?php
          if (is_array($header_data)) {
            $i = 0;
            foreach ($header_data as $key => $value) {
              if ($key >= 3) {
                $i++;
                $is_popular = $i == $item_popular ? 'active' : '';
                $price_item = explode("|", $header_price[$key]);
                $price_total = explode("|", $header_price_total[$key]);
                ?>
                <div class="vnx-table-cell-item" data-col-index="<?php echo esc_attr($i); ?>" tabindex="0">
                  <div class="cell-item-wrapper">
                    <p class="title-name"><?php echo esc_html($value); ?></p>
                    <div class="item-price-wrapper">
                      <div class="item-price-box">
                        <p class="item-price-item-text"><?php echo esc_html(trim($price_item[0])); ?><span class="item-price-item-text-unit">/<?php echo esc_html(trim($price_item[1])); ?></span></p>
                        <div class="item-price-item-icon">
                          <div class="item-price-item-icon-wrapper">
                            <img src="<?php echo esc_url($settings['item-tooltip']['url']); ?>" alt="image tooltip">
                          </div>
                          <div class="item-price-item-tooltip-wrapper">
                            <div class="item-price-item-tooltip">
                              <div class="tooltip-title">
                                <p>Tạm tính</p>
                              </div>
                              <div class="tooltip-cycle">
                                <p style="width:64px;">Chu kỳ:</p>
                                <p><?php echo esc_html(trim($price_total[1])); ?></p>
                              </div>
                              <div class="tooltip-price-total">
                                <p style="width:64px;">Tổng:</p>
                                <p class="total-price"><?php echo esc_html(trim($price_total[0])); ?></p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="item-link-wrapper">
                    <a href="<?php echo esc_url($header_link[$key]); ?>" rel="nofollow" data-price="<?php echo esc_html(trim($price_item[0])); ?>" data-period="<?php echo esc_html(trim($price_item[1])); ?>" data-product-name="<?php echo esc_html($value); ?>"
                      data-product-category="<?php echo esc_html($category_text); ?>" class="item-link vnx-btn-conversion vnx-button-register <?php echo esc_attr($is_popular); ?>">
                      <p class="item-link-text-desktop">
                        <?php echo esc_html($button_text); ?>
                      </p>
                      <p class="item-link-text-mobile">
                        <?php echo esc_html(trim($price_item[0])); ?><span>/<?php echo esc_html(trim($price_item[1])); ?></span>
                      </p>
                    </a>
                  </div>
                  <?php if (!empty($highlight_badge_text['url']) && $i == $item_popular) { ?>
                    <div class="highlight-badge desktop-only">
                      <img src="<?php echo esc_url($highlight_badge_text['url']); ?>" alt="image popular">
                    </div>
                  <?php } ?>
                </div>
                <?php
              }
            }
          } ?>
        </div>
      </div>
      <?php
      if (is_array($csv_data)) {
        $i = 0;
        foreach ($csv_data as $key => $value) {
          if ($key >= 5) {
            $i++;
            $underline_line = !empty($value[2]) ? 'underline' : 'none';
            $text_title = explode("(", $value[0]);
            $text_title_1 = $text_title[0];
            $text_title_2 = trim($text_title[1], ')');
            ?>
            <!-- Data Rows -->
            <div class="vnx-table-row" data-row-index="<?php echo esc_attr($i); ?>">
              <div class="vnx-table-cell sticky-col" data-row-index="0">
                <div class="vnx-table-cell-title">
                  <p class="vnx-table-cell-title-text" style="text-decoration-line: <?php echo esc_attr($underline_line); ?>;">
                    <?php
                    echo esc_html($text_title_1);
                    if (!empty($text_title_2)) {
                      echo '<span class="vnx-table-cell-title-text-sub">' . esc_html($text_title_2) . '</span>';
                    }
                    ?>
                  </p>
                  <?php if (!empty($value[2])) { ?>
                    <div class="vnx-table-cell-tooltip">
                      <?php echo esc_html($value[2]); ?>
                    </div>
                  <?php } ?>
                </div>
                <?php if (!empty($value[1])) {
                  $text_sticky = explode("|", $value[1]);
                  ?>
                  <span class="vnx-table-cell-secpoter"></span>
                  <div class="vnx-table-cell-content">
                    <span class="vnx-table-cell-content-text">
                      <?php echo esc_html($text_sticky[0]); ?>
                    </span>
                    <div class="vnx-table-cell-content-img">
                      <img src="<?php echo esc_url($text_sticky[1]); ?>" alt="<?php echo esc_attr($text_sticky[0]); ?>">
                    </div>
                  </div>
                <?php } ?>
              </div>
              <div class="vnx-table-cell-list" style="grid-template-columns: repeat(<?php echo esc_attr($hosting_count); ?>, 1fr);">
                <?php
                $j = 1;
                foreach ($value as $key => $value):
                  if ($key >= 3) {
                    $parse_value = $data->parse_package_value($value);
                    $ctitle = explode("(", $parse_value['unit']);
                    $ctitle_1 = $ctitle[0];
                    $ctitle_2 = trim($ctitle[1], ')');
                    ?>
                    <div class="vnx-table-cell-item" data-col-index="<?php echo esc_attr($j); ?>">
                      <div class="vnx-table-cell-item-wrapper">
                        <p class="vnx-table-cell-item-text"><?php echo $ctitle_1; ?></p>
                        <span class="vnx-table-cell-item-unit"><?php echo esc_html($ctitle_2); ?></span>
                      </div>
                    </div>
                    <?php
                    $j++;
                  }
                endforeach;
                ?>
              </div>
            </div>
            <!-- End Data Rows -->
          <?php }
        }
      } ?>
    </div>
  </div>
  <?php
} catch (\Exception $e) {
  error_log($e->getMessage());
}
?>