<?php
$vnxTable = new VNX_Service_Price_Center();
$data = isset($data) ? $data : new stdClass();
define_if_not_defined_Center('CSV_FILE_ROW_GIA_DICH_VU', 3);
define_if_not_defined_Center('CSV_FILE_ROW_DON_VI', 4);
define_if_not_defined_Center('CSV_FILE_ROW_BANG_THONG', 5);
define_if_not_defined_Center('CSV_FILE_ROW_TAN_SO_GOI_TIN', 6);
define_if_not_defined_Center('COLUMN_TITLE', 0);
define_if_not_defined_Center('COLUMN_EXPLAIN', 1);
$settings = isset($data->settings) ? $data->settings : [];
$yes_icon = isset($settings['yes_icon']) ? $settings['yes_icon'] : '';
$no_icon = isset($settings['no_icon']) ? $settings['no_icon'] : '';
$firewall_popular = isset($settings['firewall_popular']) ? $settings['firewall_popular'] : '';

$DATA_INFO_0 = isset($data->info[0]) ? $data->info[0] : '';
$COLUMN_TITLE = isset($data->info[COLUMN_TITLE]) ? $data->info[COLUMN_TITLE] : [];


$CSV_FILE_ROW_GIA_DICH_VU = isset($data->info[CSV_FILE_ROW_GIA_DICH_VU]) ? $data->info[CSV_FILE_ROW_GIA_DICH_VU] : [];
$CSV_FILE_ROW_GIA_DICH_VU_TITLE = isset($CSV_FILE_ROW_GIA_DICH_VU[COLUMN_TITLE]) ? $CSV_FILE_ROW_GIA_DICH_VU[COLUMN_TITLE] : '';
$CSV_FILE_ROW_GIA_DICH_VU_EXPLAIN = isset($CSV_FILE_ROW_GIA_DICH_VU[COLUMN_EXPLAIN]) ? $CSV_FILE_ROW_GIA_DICH_VU[COLUMN_EXPLAIN] : '';

$CSV_FILE_ROW_DON_VI = isset($data->info[CSV_FILE_ROW_DON_VI]) ? $data->info[CSV_FILE_ROW_DON_VI] : [];

$CSV_FILE_ROW_BANG_THONG = isset($data->info[CSV_FILE_ROW_BANG_THONG]) ? $data->info[CSV_FILE_ROW_BANG_THONG] : [];
$CSV_FILE_ROW_BANG_THONG_TITLE = isset($CSV_FILE_ROW_BANG_THONG[COLUMN_TITLE]) ? $CSV_FILE_ROW_BANG_THONG[COLUMN_TITLE] : '';
$CSV_FILE_ROW_BANG_THONG_EXPLAIN = isset($CSV_FILE_ROW_BANG_THONG[COLUMN_EXPLAIN]) ? $CSV_FILE_ROW_BANG_THONG[COLUMN_EXPLAIN] : '';

$CSV_FILE_ROW_TAN_SO_GOI_TIN = isset($data->info[CSV_FILE_ROW_TAN_SO_GOI_TIN]) ? $data->info[CSV_FILE_ROW_TAN_SO_GOI_TIN] : [];
$CSV_FILE_ROW_TAN_SO_GOI_TIN_TITLE = isset($CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_TITLE]) ? $CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_TITLE] : '';
$CSV_FILE_ROW_TAN_SO_GOI_TIN_EXPLAIN = isset($CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_EXPLAIN]) ? $CSV_FILE_ROW_TAN_SO_GOI_TIN[COLUMN_EXPLAIN] : '';


$csv_data = $data->info;
$result = array();
for ($i = 0; $i < count($csv_data[0]); $i++) {
	$row = array();
	for ($j = 0; $j < count($csv_data); $j++) {
		$row[] = $csv_data[$j][$i];
	}
	$result[] = $row;
}
$boundaries = $vnxTable->findCycleBoundaries($result[0]);
$the_last_row = count($boundaries) - 1;
?>
<table class="w-full text-sm hidden lg:table">
	<colgroup>
		<col width="28%" />
	</colgroup>
	<tbody>
		<!-- Thông số kỹ thuật -->
		<tr class="bg-white">
			<td class="px-3 pr-4 pl-7 font-bold text-2xl rounded-tl-md" style="color: #013A52">
				<?php
				$result_title = explode(" | ", $result[0][1]);
				printf($result_title[0]);
				?>
			</td>
			<?php
			for ($col = 2; $col < $boundaries[0][1]; $col++):
				$border = ($col === $boundaries[0][1] - 1) ? ' rounded-tr-md' : '';
				$relative = '';
				if (isset($COLUMN_TITLE[$col]))
					$relative = $COLUMN_TITLE[$col] == $firewall_popular ? ' relative' : '';
				?>
				<td class="border-l border-[#F1F1F1] text-center leading-6<?php echo $border; ?>">
					<div class="flex flex-col items-center justify-center<?php echo $relative; ?>">
						<?php
						if ($relative)
							echo '<img src="https://vietnix.vn/wp-content/uploads/2023/01/recom.svg" class="absolute -right-[1px] -top-[1px]" />';
						?>
						<img class="mb-5 mt-5" src="<?php echo isset($data->info[2][$col]) ? $data->info[2][$col] : ''; ?>" />
					</div>
				</td>
				<?php
			endfor;
			?>
		</tr>
		<!-- /Thông số kỹ thuật -->
		<!-- Giá dịch vụ - Đơn vị -->
		<tr class="bg-white border-t border-[#F1F1F1]">
			<td>
				<div class="flex-1 h-full flex flex-row px-3 pl-8 pr-4">
					<?php
					echo $CSV_FILE_ROW_GIA_DICH_VU_TITLE;
					if ($CSV_FILE_ROW_GIA_DICH_VU_EXPLAIN):
						?>
						<!-- Tooltip Start -->
						<span class="relative flex flex-col items-center group cursor-pointer">
							<i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
							<span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
								<span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
									<?php esc_html_e($CSV_FILE_ROW_GIA_DICH_VU_EXPLAIN); ?>
								</span>
								<span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
							</span>
						</span>
						<!-- Tooltip End  -->
					<?php endif; ?>
				</div>
			</td>
			<?php for ($col = 2; $col < count($CSV_FILE_ROW_GIA_DICH_VU); $col++): ?>
				<td class="px-3 py-4 border-l text-center "><span class="el-custom-text-price font-semibold ">
						<?php echo isset($CSV_FILE_ROW_GIA_DICH_VU[$col]) ? $CSV_FILE_ROW_GIA_DICH_VU[$col] : ''; ?>
					</span></br><span class="text-[#828282]">/
						<?php echo isset($CSV_FILE_ROW_DON_VI[$col]) ? $CSV_FILE_ROW_DON_VI[$col] : ''; ?>
					</span></td>
			<?php endfor; ?>
		</tr>
		<!-- /Giá dịch vụ - Đơn vị -->
		<!-- Băng thông -->
		<tr class="bg-white border-t border-[#F1F1F1]">
			<td>
				<div class="flex-1 h-full flex flex-row px-3 pl-8 pr-4">
					<?php
					echo $CSV_FILE_ROW_BANG_THONG_TITLE;
					if ($CSV_FILE_ROW_BANG_THONG_EXPLAIN):
						?>
						<!-- Tooltip Start -->
						<span class="relative flex flex-col items-center group cursor-pointer">
							<i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
							<span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
								<span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
									<?php esc_html_e($CSV_FILE_ROW_BANG_THONG_EXPLAIN); ?>
								</span>
								<span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
							</span>
						</span>
					<?php endif; ?>
					<!-- Tooltip End  -->
				</div>
			</td>
			<?php for ($col = 2; $col < count($CSV_FILE_ROW_BANG_THONG); $col++): ?>
				<td class="px-3 py-4 border-l border-[#F1F1F1] text-center font-bold">
					<?php echo isset($CSV_FILE_ROW_BANG_THONG[$col]) ? $CSV_FILE_ROW_BANG_THONG[$col] : ''; ?>
				</td>
			<?php endfor; ?>
		</tr>
		<!-- /Băng thông -->
		<!-- Tần số gói tin -->
		<tr class="bg-white border-t border-[#F1F1F1]">
			<td>
				<div class="flex-1 h-full flex flex-row px-3 pl-8 pr-4">
					<?php
					echo $CSV_FILE_ROW_TAN_SO_GOI_TIN_TITLE;
					if ($CSV_FILE_ROW_TAN_SO_GOI_TIN_EXPLAIN):
						?>
						<!-- Tooltip Start -->
						<span class="relative flex flex-col items-center group cursor-pointer">
							<i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
							<span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
								<span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
									<?php esc_html_e($CSV_FILE_ROW_TAN_SO_GOI_TIN_EXPLAIN); ?>
								</span>
								<span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
							</span>
						</span>
					<?php endif; ?>
					<!-- Tooltip End  -->
				</div>
			</td>
			<?php for ($col = 2; $col < count($CSV_FILE_ROW_TAN_SO_GOI_TIN); $col++): ?>
				<td class="px-3 py-4 border-l border-[#F1F1F1] text-center">
					<?php echo isset($CSV_FILE_ROW_TAN_SO_GOI_TIN[$col]) ? $CSV_FILE_ROW_TAN_SO_GOI_TIN[$col] : ''; ?>
				</td>
			<?php endfor; ?>
		</tr>
		<!-- /Tần số gói tin -->
		<!-- Start - Chống Botnet -->
		<?php
		$data_info = $vnxTable->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[0]);
		$key_limit = $boundaries[1][0];
		$data_info_tool = $vnxTable->getToSearchExcelCompare($boundaries[1][0], $boundaries[1][1], $result[1]);
		foreach ($data_info as $key => $value) {
			?>
			<tr class="bg-white border-t border-[#F1F1F1]">
				<td>
					<div class="flex-1 h-full flex flex-row px-3 pl-8 pr-4">
						<?php
						echo $value;
						if (!empty($data_info_tool[$key])):
							?>
							<!-- Tooltip Start -->
							<span class="relative flex flex-col items-center group cursor-pointer">
								<i class="far fa-question-circle text-gray-3 w-5 h-5 flex center"></i>
								<span class="absolute w-52 bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
									<span class="relative z-10 p-2 text-xs rounded-[4px] cursor-pointer leading-5 text-white whitespace-no-wrap bg-[#38A7FF] shadow-lg">
										<?php esc_html_e($data_info_tool[$key]); ?>
									</span>
									<span class="w-3 h-3 -mt-2 rotate-45 bg-[#38A7FF]"></span>
								</span>
							</span>
						<?php endif; ?>
						<!-- Tooltip End  -->
					</div>
				</td>
				<?php
				foreach ($result as $key_r => $value_row) {
					if ($key_r > 1) {
						?>
						<td class="px-3 py-4 border-l text-center ">
							<?php
							if ($key_limit < $boundaries[1][1]) {
								$text_value = explode(" | ", $value_row[$key_limit + 1]);
								if (is_array($text_value) && $text_value[1] === "*") {
									echo $text_value[0];
								} else {
									if (isset($text_value[0]) && $text_value[0] === '1') {
										echo $yes_icon ? Bricks\Element::render_icon($yes_icon, ['vnx_icon vnx_icon_yes', 'text-sm', 'inline']) : '<i class="fas fa-check-circle text-[#219653] text-sm" />';
									} else {
										echo $no_icon ? Bricks\Element::render_icon($no_icon, ['vnx_icon vnx_icon_no', 'text-sm', 'inline']) : '<i class="far fa-times-circle text-red-600 text-sm" />';
									}
								}
							}
							?>
						</td>
						<?php
					}
				}
				?>
			</tr>
			<?php
			$key_limit++;
		}
		?>
		<tr>
			<td></td>
			<?php
			$data_register = $vnxTable->getToSearchExcelCompare($boundaries[2][0], $boundaries[2][1], $result[0]);
			foreach ($data_register as $key => $value_res) {
						if ($key_r > 1) {
					for ($col = 2; $col < count($result); $col++):
						$border = $col === 2 ? ' rounded-bl-md border-l-0' : '';
						$border_1 = $col === count($result) - 1 ? ' rounded-br-md border-[#F1F1F1] border-r-0' : '';
						$data_price = isset($CSV_FILE_ROW_GIA_DICH_VU[$col]) ? $CSV_FILE_ROW_GIA_DICH_VU[$col] : '';
						$data_period = isset($CSV_FILE_ROW_DON_VI[$col]) ? $CSV_FILE_ROW_DON_VI[$col] : '';
						$data_product_name = 'FIREWALL ' . ($col - 1);
						$href = isset($result[$col][$boundaries[2][1] - 1]) ? $result[$col][$boundaries[2][1] - 1] : '';
						?>
						<td class="border border-b-0 border-[#F1F1F1]<?php echo $border; ?> py-6 px-2 text-center bg-white<?php echo $border_1; ?>">
							<a class="px-8 py-3 text-white vnx-btn-conversion rounded-[4px] f-15 hover:underline" style="background: #38A7FF" rel="nofollow" data-price="<?php echo $data_price; ?>" data-period="<?php echo '1 ' . $data_period; ?>"
								data-product-name="<?php echo $data_product_name; ?>" data-product-category="FIREWALL" href="<?php echo $href; ?>">
								ĐĂNG KÝ
							</a>
						</td>
					<?php 
					endfor;
					}
				}
			?>
		</tr>
	</tbody>
</table>