<?php
$data = isset($data) ? $data : new stdClass();
define_if_not_defined_Center('CSV_FILE_ROW_GIA_GOC', 1);
define_if_not_defined_Center('CSV_FILE_ROW_GIAM_GIA', 2);
define_if_not_defined_Center('CSV_FILE_ROW_GIA_SAU_GIAM', 3);
define_if_not_defined_Center('CSV_FILE_ROW_DON_VI', 4);
define_if_not_defined_Center('CSV_FILE_ROW_URL_DANG_KY', 5);
?>

<table class="w-full text-sm el-hosting-bang-so-sanh-v2">
	<colgroup>
		<col width="14%" />
	</colgroup>
	<tbody>
		<!-- Thông số kỹ thuật -->
		<tr>
			<td class="p-3 font-medium text-lg" style="background-color: #f3f6f9;">
				<?php echo $data->info[0][0] ?>
			</td>

			<?php for ($col = 1; $col < count($data->info[0]); $col++) : ?>
				<td class="p-1 text-center pt-4" style="font-size: 10px;">
					<p class="font-medium text-xs text-primary">
						<?php
						if (stripos($data->info[0][$col], "SEO") !== false || stripos($data->info[0][$col], "WP") !== false || stripos($data->info[0][$col], "Email") !== false || stripos($data->info[0][$col], "Reseller") !== false) :
							echo $data->info[0][$col];
							$labelHosting = '';
						else :
							echo 'HOSTING ' . $data->info[0][$col];
							$labelHosting = 'HOSTING ';
						endif;
						?>
					</p>

					<?php if ($data->info[CSV_FILE_ROW_GIAM_GIA][$col]) : ?>
						<div class="flex flex-row center">
							<div class="font-bold text-[#F2994A]">
								<?php echo $data->info[CSV_FILE_ROW_GIA_SAU_GIAM][$col] ?>
							</div>

							<div class="text-gray-3">
								<?php echo '/' . $data->info[CSV_FILE_ROW_DON_VI][$col] ?>
							</div>
						</div>

						<div class="flex flex-row items-center justify-center">
							<div class="line-through mr-1 text-gray-3">
								<?php echo $data->info[CSV_FILE_ROW_GIA_GOC][$col] ?>
							</div>

							<div>
								<span class="text-white bg-[#EB5757] p-0.5 rounded-sm">
									<?php echo $data->info[CSV_FILE_ROW_GIAM_GIA][$col] ?>
								</span>
							</div>
						</div>
					<?php else : ?>
						<div class="mt-1 flex flex-row center justify-center">
							<div class="el-custom-text-price font-bold el-custom-text-mini text-[#F2994A]">
								<?php echo $data->info[CSV_FILE_ROW_GIA_GOC][$col] ?>
							</div>

							<div class="text-gray-3">
								<?php echo '/' . $data->info[CSV_FILE_ROW_DON_VI][$col] ?>
							</div>
						</div>

						<div class="h-[18px]">

						</div>
					<?php endif; ?>
					<div class="mb-2 mt-4">
						<?php
						if (isset($data->info[CSV_FILE_ROW_GIAM_GIA][$col])) {
							 $price_g = trim($data->info[CSV_FILE_ROW_GIA_SAU_GIAM][$col], " ");
						} else {
							 $price_g = trim($data->info[CSV_FILE_ROW_GIA_GOC][$col], " ");
						} ?>
						<a class="px-2 py-1 el-custom-btn-register font-medium text-xs vnx-btn-conversion border" rel="nofollow" data-price="<?php echo $price_g; ?>" data-period="1 <?php echo $data->info[CSV_FILE_ROW_DON_VI][$col]; ?>" 
						data-product-name="<?php echo $labelHosting; echo $data->info[0][$col] ?>" data-product-category="<?php echo $labelHosting; echo trim(preg_replace('/\d+/', '', $data->info[0][$col])); ?>" 
						href="<?php echo $data->info[CSV_FILE_ROW_URL_DANG_KY][$col] ?>">
							Đăng ký
						</a>
					</div>
				</td>
			<?php endfor; ?>
		</tr>

		<!-- /Thông số kỹ thuật -->
		<?php for ($row = 6; $row < count($data->info); $row++) : ?>
			<tr>
				<td class="p-3 el-hosting-bang-so-sanh-v2__name">
					<?php echo str_replace('\n', '<br />', $data->info[$row][0]) ?>
				</td>

				<?php for ($col = 1; $col < count($data->info[$row]); $col++) : ?>
					<td class="el-hosting-bang-so-sanh-v2__info">
						<?php
						if ($data->info[$row][$col] === 'TRUE') : ?>
							<i class="fas fa-check text-green-600 text-xs" />
						<?php else : ?>
							<?php echo $data->info[$row][$col] ?>
						<?php endif; ?>
					</td>
				<?php endfor; ?>
			</tr>
		<?php endfor; ?>
		<!-- /Thông số kỹ thuật -->

	</tbody>
</table>