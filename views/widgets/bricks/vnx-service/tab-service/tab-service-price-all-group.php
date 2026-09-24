<?php
$settings = $data->settings;
$show_only = isset($settings['show_only']) ? $settings['show_only'] : false;
$list_service = isset($settings['list-service']) ? $settings['list-service'] : [];
$text_button = isset($settings['text_button']) ? $settings['text_button'] : 'Mua ngay';
$icon_img = isset($settings['icon_img']['url']) ? $settings['icon_img']['url'] : '';
$icon_copy = isset($settings['icon_copy']) ? $settings['icon_copy'] : '';
$icon_paste = isset($settings['icon_paste']) ? $settings['icon_paste'] : '';
$default_service = isset($settings['default_service']) ? $settings['default_service'] : '';
$turn_on_icon_popular = isset($settings['turn_on_icon_popular']) ? $settings['turn_on_icon_popular'] : false;
$icon_popular_background = isset($settings['icon_popular_background']) ? $settings['icon_popular_background'] : '';
$icon_text_popular = isset($settings['icon_text_popular']) ? $settings['icon_text_popular'] : '';
$icon_popular_icon = isset($settings['icon_popular_icon']['url']) ? $settings['icon_popular_icon']['url'] : '';
$icon_special = isset($settings['icon_special']['url']) ? $settings['icon_special']['url'] : '';

?>

<div class="vnx-container">
  <!-- Desktop Navigation Bar -->
  <?php if (!$show_only) { ?>
    <div class="vnx-service-nav vnx-desktop-only">
      <?php
      if (is_array($list_service)) {
        foreach ($list_service as $key => $service) {
          $active = $service['title'] == $default_service ? 'active' : '';
          if ($key != 0) {
            echo '<div class="vnx-service-nav-item-separator"></div>';
          }
          echo '<div class="vnx-service-nav-item ' . $active . '" data-service="' . $service['title'] . '">' . $service['title'] . '</div>';
        }
      }
      ?>
    </div>

    <!-- Mobile Navigation Dropdown -->
    <?php
    $service_show = '';
    if ($show_only) {
      $service_show = $key == 0 ? '' : 'hidden';
    }
    ?>
    <div class="vnx-service-nav-mobile vnx-mobile-only <?php echo $service_show; ?>">
      <label class="vnx-dropdown-label">Dịch vụ</label>
      <div class="vnx-dropdown-wrapper">
        <button class="vnx-dropdown-button" type="button">
          <span class="vnx-dropdown-selected">
            <?php
            $selected_service = $default_service ?: (is_array($list_service) && !empty($list_service) ? $list_service[0]['title'] : '');
            echo $selected_service;
            ?>
          </span>
          <svg class="vnx-dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none">
            <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <div class="vnx-dropdown-menu">
          <?php
          if (is_array($list_service)) {
            foreach ($list_service as $key => $service) {
              $active = $service['title'] == $default_service ? 'active' : '';
              echo '<div class="vnx-dropdown-item ' . $active . '" data-service="' . $service['title'] . '">' . $service['title'] . '</div>';
            }
          }
          ?>
        </div>
      </div>
    </div>
  <?php } ?>

  <!-- Package List -->
  <div class="vnx-tab-package-list">
    <?php
    if (is_array($list_service)) {
      foreach ($list_service as $key => $service) {
        $csv_result = $data->read_csv_file($service['import-csv']);
        if ($csv_result['status'] === 'success') {
          $packages_result = $data->transform_csv_to_services($csv_result);
        }
        $item_popular = $service['item_popular'];
        $item_special = $service['item_special'];
        $period = $service['period'];
        $category = $service['category'];
        $item_show = '';
        if ($show_only) {
          $item_show = $key == 0 ? '' : 'hidden';
        }
        ?>
        <div class="vnx-tab-package-item <?php echo $item_show; ?>" data-service="<?php echo $service['title']; ?>">
          <!-- Desktop Duration Selector -->
          <div class="vnx-duration-selector vnx-desktop-only">
            <?php
            if (is_array($packages_result['cycle_data'])) {
              foreach ($packages_result['cycle_data'] as $key => $cycle) {
                $cycle = explode('|', $cycle);
                $cycle_title = trim($cycle[0]);
                $cycle_discount = isset($cycle[1]) ? $cycle[1] : '';
                $active = $cycle_title == $period ? 'active popular' : '';

                if ($key !== 0) {
                  echo '<div class="vnx-service-nav-item-separator"></div>';
                }
                ?>
                <div class="vnx-duration-item <?php echo $active; ?> ">
                  <?php if ($active && $service['period_tag']) { ?>
                    <img class="vnx-duration-tag dis-lazyload-img" src="<?php echo $service['period_tag']['url']; ?>" alt="<?php echo $cycle_title; ?>">
                  <?php } ?>
                  <span class="vnx-duration-title">
                    <?php echo $cycle_title; ?>
                  </span>
                  <?php if (!empty($cycle_discount)) { ?>
                    <span class="vnx-duration-discount">
                      <?php echo $cycle_discount; ?>
                    </span>
                  <?php } ?>
                </div>
                <?php
              }
            }
            ?>
          </div>

          <!-- Mobile Duration Dropdown -->
          <div class="vnx-duration-selector-mobile vnx-mobile-only">
            <label class="vnx-dropdown-label">Chu kỳ</label>
            <div class="vnx-dropdown-wrapper">
              <button class="vnx-dropdown-button" type="button">
                <div class="vnx-dropdown-selected">
                  <?php
                  $selected_cycle = '';
                  if (is_array($packages_result['cycle_data'])) {
                    foreach ($packages_result['cycle_data'] as $key => $cycle) {
                      $cycle_data = explode('|', $cycle);
                      $cycle_title = trim($cycle_data[0]);
                      if ($cycle_title == $period) {
                        $selected_cycle = '<span class="vnx-duration-title">' . $cycle_title . '</span>' . (!empty($cycle_data[1]) ? ' <span class="vnx-duration-discount">' . $cycle_data[1] . '</span>' : '');
                        break;
                      }
                    }
                    if (!$selected_cycle && !empty($packages_result['cycle_data'])) {
                      $first_cycle = explode('|', $packages_result['cycle_data'][0]);
                      $first_cycle_title = trim($first_cycle[0]);
                      $selected_cycle = '<span class="vnx-duration-title">' . $first_cycle_title . '</span>' . (!empty($first_cycle[1]) ? ' <span class="vnx-duration-discount">' . $first_cycle[1] . '</span>' : '');
                    }
                  }
                  echo $selected_cycle;
                  ?>
                </div>
                <svg class="vnx-dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none">
                  <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
              <div class="vnx-dropdown-menu">
                <?php
                if (is_array($packages_result['cycle_data'])) {
                  foreach ($packages_result['cycle_data'] as $key => $cycle) {
                    $cycle = explode('|', $cycle);
                    $cycle_title = trim($cycle[0]);
                    $cycle_discount = isset($cycle[1]) ? $cycle[1] : '';
                    $active = $cycle_title == $period ? 'active' : '';
                    ?>
                    <div class="vnx-dropdown-item vnx-duration-dropdown-item <?php echo $active; ?>" data-cycle-index="<?php echo $key; ?>">
                      <span class="vnx-duration-title"><?php echo $cycle_title; ?></span>
                      <?php if (!empty($cycle_discount)) { ?>
                        <span class="vnx-duration-discount"><?php echo $cycle_discount; ?></span>
                      <?php } ?>
                    </div>
                    <?php
                  }
                }
                ?>
              </div>
            </div>
          </div>
          <!-- Price Cards Grid -->
          <div class="vnx-price-cards-grid-wrapper">
            <div class="vnx-price-cards-grid">
              <?php
              if (is_array($packages_result['services'])) {
                foreach ($packages_result['services'] as $key => $item) {
                  // $discount = array_map('trim', explode('|', $item[1]));
                  $price_list = $item[1];
                  $btn_list = $item[2];
                  $tech_list = $item[3];
                  $features_list = $item[4];
                  if ($key > 1) {
                    ?>
                    <!-- Card 1: Business 1 - Popular Choice -->
                    <div class="vnx-price-card has-popular-label">
                      <?php if ($item_popular == $item[0]) { ?>
                        <div class="vnx-card-label popular-choice" style="<?php echo $icon_popular_background; ?>">
                          <?php if ($turn_on_icon_popular) { ?>
                            <img class="vnx-icon-popular dis-lazyload-img" src="<?php echo $icon_popular_icon; ?>" alt="<?php echo $icon_text_popular; ?>">
                          <?php } else { ?>
                            <span class="vnx-icon-popular"><?php echo $icon_text_popular; ?></span>
                          <?php } ?>
                        </div>
                      <?php } ?>
                      <div class="vnx-card-content">
                        <?php if ($item_special == $item[0]) { ?>
                          <div class="vnx-card-label special-price">
                            <?php if ($icon_special) { ?>
                              <img class="vnx-icon-special dis-lazyload-img" src="<?php echo $icon_special; ?>" alt="Special">
                            <?php } ?>
                          </div>
                        <?php } ?>
                        <div class="vnx-plan-title">
                          <p class="vnx-plan-title-text">
                            <?php echo $item[0]; ?>
                          </p>
                          <?php
                          foreach ($packages_result['cycle_data'] as $key_discount => $cycle_item) {
                            $cycle_data = array_map('trim', explode(' | ', $cycle_item));
                            $cycle_title = $cycle_data[0];
                            $discount_data = array_map('trim', explode('|', $price_list[$key_discount]));
                            $discount_value = isset($discount_data[4]) ? $discount_data[4] : '';
                            $show = $cycle_title == $period ? '' : 'hidden';
                            ?>
                            <p class="vnx-plan-title-discount <?php echo $show; ?>"><?php echo $discount_value; ?></p>
                          <?php } ?>
                        </div>
                        <div class="vnx-price-list">
                          <?php
                          foreach ($packages_result['cycle_data'] as $key_cycle => $cycle) {
                            $price = explode('|', $price_list[$key_cycle]);
                            $cycle = explode('|', $cycle);
                            $cycle_title = trim($cycle[0]);
                            $show = $cycle_title == $period ? '' : 'hidden';
                            ?>
                            <div class="vnx-price-section <?php echo $show; ?>">
                              <div class="vnx-price-section-item">
                                <?php if (!empty($price[0])) { ?>
                                  <span class="vnx-original-price"><?php echo $price[0]; ?></span>
                                <?php } ?>
                                <span class="vnx-discounted-price"><?php echo $price[1]; ?></span>
                                <span class="vnx-price-unit"><?php echo trim($service['cycle']); ?></span>
                              </div>
                            </div>
                            <?php
                          } ?>
                        </div>
                        <div class="vnx-promotion-banner-list">
                          <?php
                          foreach ($packages_result['cycle_data'] as $key_cycle => $cycle) {
                            $discount = explode('|', $price_list[$key_cycle]);
                            $cycle = explode('|', $cycle);
                            $cycle_title = trim($cycle[0]);
                            $show = $cycle_title == $period ? '' : 'hidden';
                            ?>
                            <div class="vnx-promotion-banner <?php echo $show; ?>">
                              <div class="vnx-promotion-discount"><?php echo '<span class="vnx-promotion-discount-text">' . $discount[2] . ':</span>'; ?>
                                <?php if (!empty($discount[3])) { ?>
                                  <?php echo '<span class="vnx-promotion-discount-value">' . $discount[3] . '</span>'; ?>
                                <?php } ?>
                              </div>
                              <div class="vnx-promotion-action">
                                <button class="vnx-promotion-action-button" @click="actionCopy">
                                  <img src="<?php echo $icon_copy['url']; ?>" class="vnx-icon-copy dis-lazyload-img" alt="Action">
                                  <img src="<?php echo $icon_paste['url']; ?>" class="vnx-icon-paste hidden dis-lazyload-img" alt="Action">
                                </button>
                              </div>
                            </div>
                          <?php } ?>
                        </div>
                        <div class="vnx-register-list">
                          <?php
                          foreach ($packages_result['cycle_data'] as $key_cycle => $cycle) {
                            $price = explode('|', $price_list[$key_cycle]);
                            $btn_item = trim($btn_list[$key_cycle]);
                            $cycle = explode('|', $cycle);
                            $cycle_title = trim($cycle[0]);
                            $cycle_discount = isset($cycle[1]) ? $cycle[1] : '';
                            $show = $cycle_title == $period ? '' : 'hidden';
                            ?>
                            <a rel="nofollow" href="<?php echo $btn_item; ?>" class="vnx-btn-conversion vnx-button-register <?php echo $show; ?>" data-product-name="<?php echo $item[0]; ?>" data-product-category="<?php echo $category; ?>"
                              data-period="<?php echo $cycle_title; ?>" data-price="<?php echo $price[1]; ?>"><?php echo $text_button; ?></a>
                          <?php } ?>
                        </div>
                        <div class="vnx-feature-top">
                          <?php
                          if (is_array($tech_list)) {
                            foreach ($tech_list as $key_tech => $tech) {
                              $tech = explode('|', $tech);
                              ?>
                              <div class="vnx-feature-item">
                                <?php if (!empty(trim($tech[2]))) { ?>
                                  <span class="vnx-feature-icon"><img class="dis-lazyload-img" src="<?php echo $tech[2]; ?>" alt="<?php echo $tech[0]; ?>"></span>
                                <?php } ?>
                                <span class="vnx-feature-text"><?php echo $tech[0]; ?></span>
                                <?php if (!empty(trim($tech[1]))) { ?>
                                  <div class="vnx-feature-item-separator">
                                    <span class="vnx-feature-separator">|</span>
                                    <span class="vnx-feature-icon one"><img class="dis-lazyload-img" src="<?php echo $tech[3]; ?>" alt="Check"></span>
                                    <span data-tooltip="<?php echo $tech[4]; ?>" class="vnx-feature-text vnx-tech-info"><?php echo $tech[1]; ?></span>
                                  </div>
                                <?php } ?>
                              </div>
                              <?php
                            }
                          }
                          ?>
                        </div>
                        <div class="vnx-feature-separator-line"></div>
                        <div class="vnx-features-list">
                          <?php
                          if (is_array($features_list)) {
                            foreach ($features_list as $key_feature => $feature) {
                              $feature = explode('|', $feature);
                              ?>
                              <div class="vnx-feature-item">
                                <?php if (!empty($feature[0])) { ?>
                                  <img class="vnx-feature-icon dis-lazyload-img" src="<?php echo $feature[1]; ?>" alt="<?php echo $feature[0]; ?>">
                                <?php } ?>
                                <span class="vnx-feature-text"><?php echo trim($feature[0]); ?></span>
                              </div>
                              <?php
                            }
                          }
                          ?>
                        </div>
                      </div>
                    </div>
                  <?php }
                }
              }
              ?>
            </div>
          </div>
        </div>
      <?php }
    }
    ?>
  </div>
</div>