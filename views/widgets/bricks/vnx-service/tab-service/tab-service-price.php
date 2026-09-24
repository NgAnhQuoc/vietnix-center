<?php
$settings = $data->settings;
$list_service = isset($settings['list-service']) ? $settings['list-service'] : [];
$text_button = isset($settings['text_button']) ? $settings['text_button'] : 'Mua ngay';
$icon_img = isset($settings['icon_img']['url']) ? $settings['icon_img']['url'] : '';
?>
<div class="vnx-container">
  <div class="vnx-tab-cycle-list">
    <?php foreach ($list_service as $key => $service) { ?>
      <div class="vnx-tab-cycle-item" data-service="<?php echo esc_attr($service['title']); ?>">
        <?php echo $service['title']; ?>
      </div>
    <?php } ?>
  </div>
  <div class="vnx-tab-package-list">
    <?php
    if (!empty($list_service)) {
      foreach ($list_service as $service) {
        $csv_result = $data->read_csv_file($service['import-csv']);
        if ($csv_result['status'] === 'success') {
          $packages_result = $data->transform_csv_to_packages($csv_result);
          if ($packages_result['status'] === 'success') {
            $packages = $packages_result['packages'];
            ?>
            <div class="vnx-tab-package-item" data-service="<?php echo esc_attr($service['title']); ?>">
              <?php foreach ($packages as $key => $package) {
                $price = $data->parse_price($package['Giá'] ?? '');
                $speed_image = $data->parse_feature_value($package['Tốc độ'] ?? '');
                $other_info_parts = array_map('trim', explode('|', $package['Thông tin khác'] ?? ''));
                $other_info = !empty($other_info_parts[1]) ? $other_info_parts[1] : '';
                $other_icon = !empty($other_info_parts[0]) ? $other_info_parts[0] : '';
                $tech_info_parts = array_map('trim', explode('|', $package['Thông tin công nghệ'] ?? ''));
                $tech_name = !empty($tech_info_parts[1]) ? $tech_info_parts[1] : '';
                $tech_icon = !empty($tech_info_parts[0]) ? $tech_info_parts[0] : '';
                $tech_description = !empty($tech_info_parts[2]) ? $tech_info_parts[2] : '';
                $groups = $package['groups'] ?? [];
                ?>
                <div class="vnx-package-card">
                  <h3 class="vnx-package-title"><?php echo esc_html($package['name'] ?? ''); ?></h3>
                  <?php if (!empty($package['Khuyến mãi'])) { ?>
                    <div class="vnx-promotion-banner">
                      <span class="vnx-promotion-icon">
                        <img class="dis-lazyload-img" src="<?php echo esc_url($icon_img); ?>" alt="icon promotion" />
                      </span>
                      <span class="vnx-promotion-text"><?php echo esc_html($package['Khuyến mãi']); ?></span>
                    </div>
                  <?php } ?>

                  <div class="vnx-price-section">
                    <?php if (!empty($price['original'])) { ?>
                      <span class="vnx-original-price"><?php echo esc_html($price['original']); ?></span>
                    <?php } ?>
                    <?php if (!empty($price['discounted'])) { ?>
                      <span class="vnx-discounted-price"><?php echo esc_html($price['discounted']); ?></span>
                    <?php } ?>
                    <?php if (!empty($price['unit'])) { ?>
                      <span class="vnx-price-unit">/<?php echo esc_html($price['unit']); ?></span>
                    <?php } ?>
                  </div>

                  <?php if (!empty($package['Link sản phẩm'])) {
                    $price_discounted = str_replace('đ', '', $price['discounted']);
                    ?>
                    <a href="<?php echo esc_url($package['Link sản phẩm']); ?>" rel="nofollow" class="vnx-buy-button vnx-btn-conversion vnx-button-register" data-price="<?php echo esc_attr($price_discounted); ?>"
                      data-period="<?php echo esc_attr($service['period']); ?>" data-product-name="<?php echo esc_attr($package['name']); ?>" data-product-category="<?php echo esc_attr($service['category']); ?>">
                      <?php echo esc_html($text_button); ?>
                    </a>
                  <?php } ?>

                  <div class="vnx-features-list">
                    <div class="vnx-feature-top">
                      <?php if (!empty($speed_image)) { ?>
                        <div class="vnx-feature-item">
                          <span class="vnx-feature-label">Tốc độ:</span>
                          <img src="<?php echo esc_url($speed_image); ?>" alt="Tốc độ" class="vnx-speed-bar dis-lazyload-img" />
                        </div>
                      <?php } ?>
                      <div class="vnx-feature-item">
                        <?php if (!empty($other_info_parts)) { ?>
                          <span class="vnx-feature-icon"><?php echo $other_icon; ?></span>
                          <span class="vnx-feature-text"><?php echo esc_html($other_info); ?></span>
                        <?php } ?>
                        <?php if (!empty($tech_info_parts)) { ?>
                          <span class="vnx-feature-separator">|</span>
                          <span class="vnx-feature-icon one"><?php echo $tech_icon; ?></span>
                          <span class="vnx-feature-text vnx-tech-info" data-tooltip="<?php echo esc_attr($tech_description); ?>">
                            <?php echo esc_html($tech_name); ?>
                          </span>
                        <?php } ?>
                      </div>
                    </div>
                    <?php if (!empty($groups)) {
                      foreach ($groups as $specs) { ?>
                    <div class="vnx-feature-separator-line" >
                    </div>
                    <?php if (!empty($specs)) { ?>
                      <div class="vnx-feature-bottom">
                        <?php foreach ($specs as $spec) { ?>
                          <div class="vnx-feature-item">
                            <?php if (!empty($spec['icon'])) { ?>
                              <span class="vnx-feature-icon"><?php echo $spec['icon']; ?></span>
                            <?php } ?>
                            <?php if (!empty($spec['value'])) { ?>
                              <span class="vnx-feature-text"><?php echo esc_html($spec['value']); ?></span>
                            <?php } ?>
                          </div>
                        <?php } ?>
                      </div>
                    <?php } } }?>
                  </div>
                </div>
              <?php } ?>
            </div>
            <?php
          }
        }
      }
    } ?>
  </div>
</div>