<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
if (!isset($data->settings)) {
	if (current_user_can('update_core'))
		echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
	return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();

if ($get_csv['status'] == 'error') {
	if (current_user_can('update_core'))
		echo '<div class="vnx_error no_data"><b>' . $get_csv['message'] . '</div>';
	return;
}
if ($get_csv['status'] == 'success' && !empty($get_csv['data']))
	$csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
if (empty($csv_data)) {
	if (current_user_can('update_core'))
		echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html(__FILE__) . '</div>';
	return;
}
//phần xử lý gói dịch vụ, cột dọc
$result = array();
for ($i = 0; $i < count($csv_data[0]); $i++) {
	$row = array();
	for ($j = 0; $j < count($csv_data); $j++) {
		$row[] = $csv_data[$j][$i];
	}
	$result[] = $row;
}
$boundaries = $data->findCycleBoundaries($result[0]);
$the_last_row = count($boundaries) - 1;
if (is_array($boundaries) && !empty($boundaries)) {
	$url_row = $data->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[0]);
	$cycle_row = $data->getToSearchExcelCompare($boundaries[2][0], $boundaries[2][1], $result[0]);
	$gt_row = $data->getToSearchExcelCompare($boundaries[$the_last_row][0], $boundaries[$the_last_row][1], $result[0]);
}
//tab active
$featured_tab = $settings['cycle_price'];
?>
<!-- fillter -->
<div class="w-full flex justify-center lg:mb-1 mb-5 vnx-fillter-service-price hidden">
	<div class="grid-cols-2 gap-3 lg:gap-4 w-full lg:w-auto grid lg:flex lg:flex-row bg-white py-3 px-2.5 lg:px-2 rounded-2xl lg:rounded-full text-gray-600 font-bold border">
		<?php
		if (!empty($cycle_row)) {
			foreach ($cycle_row as $index => $value) {
				$arr_price = explode(" | ", $value);
				if (is_array($arr_price)) {
					?>
					<div
						class="vnx-cyc-price border lg:border-0 lg:w-[8em] text-center cursor-pointer lg:bg-white bg-[#38A7FF1A] hover:bg-[#38A7FF1A] py-1.5 lg:py-1 lg:rounded-full rounded-md leading-8 relative <?php echo ($arr_price[0] == $featured_tab) ? ' tab-active' : '' ?>"
						data-target="vnx-tab-<?php echo esc_attr($index); ?>" data-discount="<?php echo esc_html($arr_price[1]); ?>" data-period="<?php echo esc_html($arr_price[0]); ?>">
						<?php
						echo esc_html($arr_price[0]);
						if ($arr_price[1]) {
							?>
							<span class="el-custom-tab-discount text-xs py-1 px-1 rounded-2xl">-
								<?php echo esc_html($arr_price[1]) ?>
							</span>
							<?php
						}
						?>
					</div>
					<?php
				}
			}
		}
		?>
	</div>
</div>
<!-- fillter -->
<!-- content -->
<div class="vnx-body-service-price">
	<div class="splide vnx-splide-service-price " id="vnx-splide-service-price" aria-label="Vietnix Service Price">
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
				<!-- Danh sách giá  -->
				<?php
				if (!empty($result)) {
					foreach ($result as $key => $slides) {
						if ($key !== 0) {
							?>
							<div class="splide__slide flex">
								<div class="card-price rounded-lg p-6 border border-[#38A7FF] relative w-full">
									<div class="card-content-price">
										<div class="card-box-title">
											<div class="text-[#525666] text-center font-roboto font-bold text-[24px] leading-[32px] pb-4">
												<?php echo $slides[0]; ?>
											</div>
											<?php
											if (!empty($slides[2])) {
												?>
												<p class="text-[16px] pb-4 text-center">
													<?php echo $slides[2]; ?>
												</p>
											<?php } ?>
										</div>
										<div class="card-box-price pb-6">
											<div class="text-center text-[16px] vnx-tab vnx-cost flex flex-row justify-center items-center gap-2.5">
												<span class="line-through text-[#757885] vnx-price-cost">0đ</span>
												<span class="no-underline el-custom-tab-discount text-xs text-[#FFB800] py-1 px-2 rounded-2xl border-[#FFB800] border bg-[#FFFDE5] ">
													0%
												</span>
											</div>
											<div class="el-custom-text-price text-center text-[16px] vnx-tab flex flex-row justify-center items-end gap-2.5 ">
												<span class="text-[36px] leading-[44px] font-bold vnx-price-reduced">0đ</span>
												<span class="text-[#757885] text-[16px] leading-[32px]">
													/tháng
												</span>
											</div>
											<div class="flex flex-row justify-center text-sm box-vnx-price-reduced-year">
												<p class="el-custom-text-price-year">Tổng&nbsp;<span>1 năm</span>&nbsp;
													<i aria-hidden="true" class="ion-md-information-circle-outline"></i>
												</p>
												<div class="el-custom-tooltip-price-year flex flex-col gap-1 hidden">
													<span class="text-base font-bold">Tạm tính</span>
													<div>
														Chu kỳ:&nbsp;<span class="vnx-price-cyc-year font-semibold"></span>
													</div>
													<div>
														Tổng:&nbsp;<span class="vnx-price-reduced-year font-semibold bg-text"></span>
													</div>
												</div>
											</div>
										</div>
										<div class="hidden-price-cyc hidden">
											<?php
											if (!empty($cycle_row)) {
												foreach ($cycle_row as $pc => $price_rows) {
													$priceStart = array_search($price_rows, $result[0]);
													$price_infor = explode(" | ", $slides[$priceStart]);
													$cost_price = isset($price_infor[0]) ? $price_infor[0] : null;
													$reduced_price = isset($price_infor[1]) ? $price_infor[1] : null;
													$year_price = isset($price_infor[2]) ? $price_infor[2] : null;
													if (!empty($reduced_price)) {
														?>
														<span class="vnx-price-cyc" data-tab="vnx-tab-<?php echo esc_attr($pc); ?>" data-cost="<?php echo $cost_price; ?>" data-price="<?php echo $reduced_price; ?>" data-price-year="<?php echo $year_price; ?>"
															data-product-name="<?php echo $slides[0]; ?>" data-product-category="<?php echo trim(preg_replace('/[^a-zA-Z\s.-]/', '', $slides[0])); ?>">
															<?php echo $reduced_price; ?>
														</span>
														<?php
													}
												}
											}
											?>
											<?php
											if (!empty($url_row)) {
												foreach ($url_row as $url_key => $url_rows) {
													$url_id = array_search($url_rows, $result[0]);
													?>
													<span class="vnx-price-url" data-tab="vnx-tab-<?php echo esc_attr($url_key); ?>" data-url="<?php echo $slides[$url_id]; ?>"></span>
													<?php
												}
											}
											?>
										</div>
									</div>
									<?php
									$height_item_show = !empty($settings['height_item_show']) ? $settings['height_item_show'] : 'auto';
									?>
									<div class="card-content-infor collapsed">
										<!-- thông tin gói -->
										<?php
										if (!empty($boundaries) && is_array($boundaries)) {
											foreach ($boundaries as $index => $row) {
												if ($index > 2 && $index < $the_last_row) {
													$kt_row = $data->getToSearchExcelCompare($boundaries[$index][0], $boundaries[$index][1], $result[0]);
													$kt_infor_title = explode(" | ", $result[0][$boundaries[$index][0]]);
													?>
													<div class="box-content-infor">
														<p class="infor-title">
															<?php echo $kt_infor_title[0]; ?>
														</p>
														<?php
														if (is_array($kt_row)) {
															foreach ($kt_row as $kt_rows) {
																$ktStart = array_search($kt_rows, $result[0]);
																$kt_infor = explode("|", $slides[$ktStart]);
																$kt_icon_hl = explode("|", $result[0][$ktStart]);
																if (!empty($kt_infor[0])) {
																	?>
																	<div class="box-row-infor">
																		<div class="infor-text">
																			<span class="infor-icon">
																				<?php if (trim($kt_infor[2]) == 'yes' || trim($kt_infor[2]) == 'true' || trim($kt_infor[2]) != 'no') {
																					if (is_array($kt_icon_hl) && !empty($kt_icon_hl[1]) && trim($kt_icon_hl[1]) != "no") {
																						print ('<i aria-hidden="true" class="vnx_highlight_icon ' . $settings['highlight_icon']['icon'] . '"></i>');
																					} else {
																						print ('<i aria-hidden="true" class="vnx_icon_yes ' . $settings['yes_icon']['icon'] . '"></i>');
																					}
																					?>
																				<?php } else if (trim($kt_infor[2]) == 'no') { ?>
																						<i aria-hidden="true" class="vnx_icon_no <?php echo $settings['no_icon']['icon']; ?>"></i>
																				<?php } ?>
																			</span>
																			<span class="text-base <?php echo $opacity = trim($kt_infor[2]) == 'no' ? "opacity-60" : ""; ?>">
																				<?php
																				echo $kt_infor[0];
																				if (!empty($kt_infor[1]) && trim($kt_infor[1]) != "") {
																					echo '<strong>' . $kt_infor[1] . '</strong>';
																				}
																				?>
																			</span>
																		</div>
																		<?php
																		$keys = array_keys($result[0], $kt_rows);
																		if (!empty($keys[1])) {
																			?>
																			<div class="infor-tooltip">
																				<span class="infor-icon"><i aria-hidden="true" class="vnx_tooltip_icon <?php echo $settings['tooltip_icon']['icon']; ?>"></i></span>
																				<?php
																				$tooltip = isset($keys[1]) ? $keys[1] : null;
																				if (!empty($result[1][$tooltip])) {
																					echo '<p class="text-tooltip text-xs">';
																					echo $result[1][$tooltip];
																					echo '</p>';
																				}
																				?>
																			</div>
																			<?php
																		}
																		?>
																	</div>
																	<?php
																}
															}
														} ?>
													</div>
													<?php
												}
											}
										}
										?>
									</div>
									<div class="box-div-link w-full">
										<div class="box-content-open button-give-more flex flex-row justify-center">
											<button type="button" class="vnx-button-more toggle-btn">Xem thêm <i class="fa-solid fa-angle-down ml-1.5"></i></button>
											<button type="button" class="vnx-button-close hidden">Thu gọn<i class="fa-solid fa-angle-up ml-1.5"></i></button>
										</div>
										<div class="button-register pt-6">
											<a rel="nofollow" data-price="0đ" data-period="" data-product-name="" data-product-category="" href="1" class="vnx-btn-conversion vnx-button-register url-register"><?php echo $settings['button_register']; ?></a>
										</div>
										<?php if (isset($settings['button_show'])) {
											$url_page = isset($slides[3]) && trim($slides[3]) != "" ? $slides[3] : "#";
											?>
											<div class="box-content-infor button-give-table">
												<a class="button-title" href="<?php echo $url_page; ?>">
													<?php echo $settings['list_button_text']; ?>
												</a>
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<?php
						}
					}
				}
				?>
				<!-- <div class="line-end"></div> -->
				<!-- Danh sách giá  -->
			</div>
		</div>
		<style>
			.list_hosting_price_v1 .line-end {
				border-left: 0.5px solid #E0E0E0;
				border-top:none;
				border-bottom: none;
				border-right: none;
				margin-right: 24px;
				margin-left: 0px;
				margin-top: 25%;
				margin-bottom: 25%;
			}

			.card-content-infor.collapsed {
				height:
					<?php echo $height_item_show; ?>
				;
			}

			.card-content-infor.expanded {
				height: auto;
			}

			.vnx-price-reduced-year.bg-text {
				background: var(--Gradient-Orangle-01, linear-gradient(90deg, #F3B847 0%, #F49846 100%));
				background-clip: text;
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
			}

			.vnx-splide-service-price .sticky_nav {
				position: sticky !important;
				top: 50% !important;
				z-index: 1;
				animation: slideInFromTop 0.5s ease-out;
			}

			@keyframes slideInFromTop {
				0% {
					transform: translateY(-100%);
					opacity: 0;
				}

				100% {
					transform: translateY(0);
					opacity: 1;
				}
			}
		</style>
	</div>
</div>
<!-- content -->