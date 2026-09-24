<?php
$widget = isset($data) ? $data : new stdClass();
$settings = $widget->settings;
$text_description = isset($settings['text_description']) ? $settings['text_description'] : 'Thông tin sản phẩm';
$category_button_register = isset($settings['category_button_register']) ? $settings['category_button_register'] : '';
$button_register_text = isset($settings['button_register_text']) ? $settings['button_register_text'] : 'ĐĂNG KÝ NGAY';
$button_register_unit = isset($settings['button_register_unit']) ? $settings['button_register_unit'] : '/th';
$dataCSV = $widget->geFileDataUpload();

$transformed = $widget->transform_columns_to_packages($dataCSV);
$packages = $transformed['packages'];
$rangeCount = count($packages) - 2;
?>
<div class="vnx-price-range-wrapper">
  <!-- Left Side: Range Selector -->
  <div class="vnx-range-section">
    <?php if (!empty($text_description)) { ?>
      <span class="vnx-range-title ">
        <?php echo $text_description; ?>
      </span>
    <?php } ?>
    <!-- Range Tabs -->
    <div class="vnx-range-tabs">
      <div class="vnx-range-tabs-wrapper">
        <?php foreach ($packages as $key => $item) {
          if ($key > 1) {
            ?>
            <button class="vnx-range-tab-btn" data-tab-index="<?php echo $key - 2; ?>">
              <div class="svg-circle"></div>
              <span class="tab-label"><?php echo $item['range']; ?></span>
            </button>
          <?php }
        } ?>
      </div>
    </div>
    <div class="vnx-service-info-wrapper">
      <?php
      foreach ($packages as $key => $item_info) {
        if ($key > 1) {
          $info = $item_info['groups']['group_3'];
          ?>
          <div class="vnx-service-info-wrapper-item">
            <!-- Service Info -->
            <div class="vnx-service-info">
              <?php foreach ($info as $key => $item) {
                $value = $packages[1]['groups']['group_3'][$key]['value'];
                $label = $item['label'];
                $item = explode(' | ', $item['value']);
                $under = !empty($value) ? 'under' : '';
                ?>

                <div class="vnx-service-info-item">
                  <div class="img-content">
                    <img class="dis-lazyload-img" src="<?php echo $item[2]; ?>" alt="<?php echo $item[0]; ?>">
                  </div>
                  <div class="content relative">
                    <p class="vnx-text-info <?php echo $under; ?>">
                      <?php echo $label; ?>:
                      <strong class="special-text" data-spec="disk"> <?php echo $item[0]; ?></strong>
                    </p>
                    <!-- Tooltip Info -->
                    <div class="vnx-tooltip-box">
                      <p class="text-info-tooltip">
                        <?php echo $value; ?>
                      </p>
                    </div>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        <?php }
      } ?>
    </div>
    <div class="vnx-img-tab-wrapper">
      <?php if (!empty($packages)) {
        foreach ($packages as $key => $image_tab) {
          if ($key > 1) {
          ?>
            <div class="vnx-img-tab">
              <img class="dis-lazyload-img" src="<?php echo $image_tab['image']; ?>">
            </div>
          <?php }
        }
      } ?>
    </div>
  </div>
  <!-- Right Side: Service Card -->
  <div class="vnx-service-card-wrapper">
    <?php foreach ($packages as $key => $card) {
      if ($key > 1) {
        $price = explode('|', $card['groups']['group_1'][0]['value']);
        $period = $card['groups']['group_1'][0]['label'];
        $features = $card['groups']['group_2'];
        $bonus = $card['groups']['group_4'];
        ?>
        <div class="vnx-service-card">
          <div class="vnx-service-card-content">
            <!-- Card Header -->
            <div class="vnx-service-card-header">
              <!-- Service Icon -->
              <div class="vnx-service-card-icon">
                <img class="dis-lazyload-img" src="<?php echo $card['image']; ?>" alt="<?php echo $card['name']; ?>">
              </div>
              <div class="vnx-service-card-name">
                <!-- Service Name -->
                <div>
                  <p class="vnx-service-card-name-title"><?php echo $card['name']; ?></p>
                </div>
                <?php if ($price[3] != ' ' && !empty($price[3])) { ?>
                  <!-- Discount Badge -->
                  <span class="vnx-service-card-discount-badge">
                    -<?php echo $price[3]; ?>
                  </span>
                <?php } ?>
              </div>
            </div>
            <!-- Price Section -->
            <div class="vnx-service-card-price">
              <div class="vnx-service-card-price-content">
                <span class="vnx-service-card-price-old"><?php echo trim($price[0]); ?></span>
                <span class="vnx-service-card-price-new"><?php echo trim($price[1]); ?></span>
                <span class="vnx-service-card-price-unit"><?php echo $button_register_unit; ?></span>
                <div class="vnx-price-tooltip">
                  <div class="vnx-price-icon">
                    <?php if (!empty($settings['tooltip_icon']['svg']['url'])) { ?>
                      <img src="<?php echo $settings['tooltip_icon']['svg']['url']; ?>" alt="Tooltip">
                    <?php } else { ?>
                      <i class="<?php echo $settings['tooltip_icon']['icon']; ?>"></i>
                    <?php } ?>
                  </div>
                  <div class="vnx-temp-calc-popup">
                    <div class="vnx-temp-calc-content">
                      <div class="vnx-temp-calc-item first-item">
                        <span class="title">Tạm tính</span>
                      </div>
                      <div class="vnx-temp-calc-item second-item">
                        <div style="max-width: 64px; width: 100%;">Chu Kỳ: </div>
                        <span><?php echo $period; ?></span>
                      </div>
                      <div class="vnx-temp-calc-item">
                        <div style="max-width: 64px; width: 100%;">Tổng:</div>
                        <span class="total"><?php echo trim($price[2]); ?>đ</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- CTA Button -->
            <a class="vnx-button vnx-btn-conversion" href="<?php echo $price[4]; ?>" data-product-name="<?php echo $card['name']; ?>" data-product-category="<?php echo $category_button_register; ?>" data-period="<?php echo $period; ?>"
              data-price="<?php echo trim($price[1]); ?>">
              <?php echo $button_register_text; ?>
            </a>
            <!-- Features List -->
            <div class="vnx-features-list">
              <?php foreach ($features as $key => $feature) {
                $tooltip = $packages[1]['groups']['group_2'][$key]['value'];
                $feature = explode('|', $feature['value']);
                $status = $packages[1]['groups']['group_2'][$key]['value'];
                $under = !empty($status) ? 'under' : '';
                ?>
                <div class="vnx-feature-item">
                  <div class="vnx-feature-icon">
                    <img src="<?php echo $feature[2]; ?>" alt="<?php echo $feature[0]; ?>">
                  </div>
                  <div class="vnx-feature-text <?php echo $under; ?>">
                    <span class="text-info"><?php echo $feature[0]; ?></span>
                  </div>
                  <?php if (!empty($tooltip)) { ?>
                    <div class="vnx-tooltip-box">
                      <p class="text-info-tooltip">
                        <?php echo $tooltip; ?>
                      </p>
                    </div>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
            <div class="vnx-bonus-wrapper">
              <?php foreach ($bonus as $bonus) {
                $bonus = explode('|', $bonus['value']);
                ?>
                <!-- Bonus Section -->
                <div class="vnx-bonus-item">
                  <div class="vnx-bonus-icon">
                    <img src="<?php echo $bonus[2]; ?>" alt="<?php echo $bonus[0]; ?>">
                  </div>
                  <div class="vnx-bonus-text">
                    <p class="vnx-bonus-text-content">
                      <?php echo $bonus[0]; ?>
                    </p>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      <?php }
    } ?>
  </div>
</div>