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
$icon_tooltip_feature = isset($settings['icon_tooltip_feature']) ? $settings['icon_tooltip_feature'] : '';
// var_dump($icon_tooltip_feature);
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
                    <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
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
					$packages_result = $data->transform_csv_to_objectstorage($csv_result);
				}
				$item_popular = $service['item_popular'];
				$period = $service['period'];
				$category = $service['category'];
				$unit_price = $service['unit_price'];
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
                    <img class="vnx-duration-tag dis-lazyload-img" src="<?php echo $service['period_tag']['url']; ?>"
                        alt="<?php echo $cycle_title; ?>">
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
                            <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
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
                        <div class="vnx-dropdown-item vnx-duration-dropdown-item <?php echo $active; ?>"
                            data-cycle-index="<?php echo $key; ?>">
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

									$price_range = $item[2];
									$price_stg = $item[3];
									$price_list = $item[4];
									$btn_list = $item[5];
									$tech_list = $item[6];
									$features_list = $item[7];
									$image_popular = isset($service['image_popular']['url']) ? $service['image_popular']['url'] : '';
									if ($key > 1) {
										$is_popular = ($item_popular == $item[0]);

							?>
                    <!-- Price Card -->
                    <div class="vnx-price-card <?php echo $is_popular ? 'is-popular' : ''; ?>">
                        <div class="vnx-card-inner">
                            <?php if (!empty($image_popular) && $item_popular == $item[0]) { ?>
                            <div class="vnx-card-special-badge">
                                <img class="vnx-icon-special dis-lazyload-img" src="<?php echo $image_popular; ?>"
                                    alt="Special">
                            </div>
                            <?php } ?>

                            <!-- Title Row: Tên gói + Storage badge -->
                            <div class="vnx-os-title-row">
                                <span class="vnx-os-plan-name"><?php echo $item[0]; ?></span>
                                <?php if (!empty($item[1])) { ?>
                                <span class="vnx-os-storage-badge"><?php echo $item[1]; ?></span>
                                <?php } ?>
                            </div>

                            <!-- Price Section: Giá gốc + Giá sale + /th + icon coin -->
                            <div class="vnx-os-price-section">
                                <?php
													foreach ($packages_result['cycle_data'] as $key_cycle => $cycle) {
														$price = explode('|', $price_list[$key_cycle]);
														$cycle_arr = explode('|', $cycle);
														$cycle_title = trim($cycle_arr[0]);
														$price_regular = trim($price[0]);
														$price_discount = trim($price[1]);
														$price_total = trim($price[2]);
														$price_icontooltip = trim($price[3]);
														$show = $cycle_title == $period ? '' : 'hidden';
													?>
                                <div class="vnx-os-price-row <?php echo $show; ?>">
                                    <div class="vnx-os-price-numbers">
                                        <?php if (!empty($price_regular)) { ?>
                                        <span class="vnx-os-original-price"><?php echo $price_regular; ?></span>
                                        <?php } ?>
                                        <span class="vnx-os-discounted-price"><?php echo $price_discount; ?></span>
                                        <span class="vnx-os-price-unit">/<?php echo trim($unit_price); ?></span>
                                    </div>
                                    <?php if ($price_icontooltip) { ?>
                                    <div class="vnx-os-price-icon">
                                        <img class="dis-lazyload-img" src="<?php echo $price_icontooltip; ?>"
                                            alt="coin">
                                        <div class="vnx-os-price-tooltip">
                                            <div class="vnx-os-tooltip-arrow"></div>
                                            <div class="vnx-os-tooltip-header">Tạm tính</div>
                                            <div class="vnx-os-tooltip-divider"></div>
                                            <div class="vnx-os-tooltip-row">
                                                <span class="vnx-os-tooltip-label">Chu Kỳ:</span>
                                                <span class="vnx-os-tooltip-value"><?php echo $cycle_title; ?></span>
                                            </div>
                                            <div class="vnx-os-tooltip-row">
                                                <span class="vnx-os-tooltip-label">Tổng:</span>
                                                <span class="vnx-os-tooltip-total"><?php echo $price_total; ?>đ</span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            </div>

                            <!-- Price Range Banner -->
                            <div class="vnx-os-price-range-list">
                                <div class="vnx-os-price-range">
                                    <?php if (!empty($price_range)) { ?>
                                    <span class="vnx-os-range-label"><?php echo $price_range; ?></span>
                                    <?php } ?>
                                    <?php if (!empty($price_range) && !empty($price_stg)) { ?>
                                    <span class="vnx-os-range-divider"></span>
                                    <?php } ?>
                                    <?php if (!empty($price_stg)) {
															$price_stg_arr = explode('/', $price_stg);
														?>
                                    <span class="vnx-os-range-price">
                                        <span><?php echo $price_stg_arr[0]; ?></span>/
                                        <?php echo $price_stg_arr[1]; ?>
                                    </span>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Register Button -->
                            <div class="vnx-os-register-list">
                                <?php
													foreach ($packages_result['cycle_data'] as $key_cycle => $cycle) {
														$price = explode('|', $price_list[$key_cycle]);
														$btn_item = trim($btn_list[$key_cycle]);
														$cycle_arr = explode('|', $cycle);
														$cycle_title = trim($cycle_arr[0]);
														$show = $cycle_title == $period ? '' : 'hidden';
													?>
                                <a rel="nofollow" href="<?php echo $btn_item; ?>"
                                    class="vnx-btn-conversion vnx-os-btn-register <?php echo $show; ?>"
                                    data-product-name="<?php echo $item[0]; ?>"
                                    data-product-category="<?php echo $category; ?>"
                                    data-period="<?php echo $cycle_title; ?>"
                                    data-price="<?php echo trim($price[1]); ?>">
                                    <span><?php echo $text_button; ?></span>
                                </a>
                                <?php } ?>
                            </div>

                            <!-- Check List (tech_list) -->
                            <div class="vnx-os-check-list">
                                <?php
													if (is_array($tech_list)) {
														foreach ($tech_list as $key_tech => $tech) {
															$tech_arr = explode('|', $tech);
													?>
                                <div class="vnx-os-check-item">
                                    <span class="vnx-os-check-icon">
                                        <?php if (!empty(trim($tech_arr[3] ?? ''))) { ?>
                                        <img class="dis-lazyload-img" src="<?php echo trim($tech_arr[3]); ?>"
                                            alt="check">
                                        <?php } else { ?>
                                        <i class="fa-sharp fa-solid fa-check"></i>
                                        <?php } ?>
                                    </span>
                                    <div class="vnx-os-check-content">
                                        <div class="vnx-os-check-extra">
                                            <?php if (!empty(trim($tech_arr[0] ?? ' '))) { ?>
                                            <span
                                                class="vnx-os-check-text is-bold"><?php echo trim($tech_arr[0]); ?></span>
                                            <?php } ?>
                                            <?php if (!empty(trim($tech_arr[1] ?? ' '))) { ?>
                                            <span class="vnx-tech-info"><?php echo trim($tech_arr[1]); ?></span>
                                            <?php } ?>
                                        </div>
                                        <?php if (!empty($icon_tooltip_feature)) { ?>
                                        <div class="vnx-os-check-tooltip" data-tooltip="<?php echo $tech_arr[2]; ?>">
                                            <?php if ($icon_tooltip_feature['library'] == 'svg') { ?>
                                            <img src="<?php echo $icon_tooltip_feature['url']; ?>" alt="icon-tooltip">
                                            <?php } else { ?>
                                            <i class=" <?php echo $icon_tooltip_feature['icon']; ?>"></i>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
														}
													}
													?>
                            </div>

                            <!-- Bottom Description Box -->
                            <?php if (is_array($features_list) && !empty($features_list)) { ?>
                            <div class="vnx-os-desc-box">
                                <?php foreach ($features_list as $feature_raw) {
															$feature = explode('|', $feature_raw);
														?>
                                <p class="vnx-os-desc-text"><?php echo trim($feature[0]); ?></p>
                                <?php } ?>
                            </div>
                            <?php } ?>
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