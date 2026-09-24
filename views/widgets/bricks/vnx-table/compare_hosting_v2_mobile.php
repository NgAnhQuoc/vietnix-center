<?php
$data = isset($data) ? $data : new stdClass();
define_if_not_defined_Center('CSV_FILE_ROW_THONG_SO_KY_THUAT', 0);
define_if_not_defined_Center('CSV_FILE_ROW_GIA_GOC', 1);
define_if_not_defined_Center('CSV_FILE_ROW_GIAM_GIA', 2);
define_if_not_defined_Center('CSV_FILE_ROW_GIA_SAU_GIAM', 3);
define_if_not_defined_Center('CSV_FILE_ROW_DON_VI', 4);
define_if_not_defined_Center('CSV_FILE_ROW_URL_DANG_KY', 5);
define_if_not_defined_Center('CSV_FILE_ROW_DUNG_LUONG_NVME', 6);
define_if_not_defined_Center('CSV_FILE_ROW_BANG_THONG', 7);
define_if_not_defined_Center('CSV_FILE_ROW_EMAIL_ACCOUNT', 10);
define_if_not_defined_Center('CSV_FILE_ROW_DOMAIN_CHINH', 11);
define_if_not_defined_Center('CSV_FILE_ROW_BACKUP_DU_LIEU', 12);
define_if_not_defined_Center('CSV_FILE_ROW_RAM', 13);
define_if_not_defined_Center('CSV_FILE_ROW_IOPS', 14);
define_if_not_defined_Center('CSV_FILE_ROW_DISK_IO', 15);
define_if_not_defined_Center('CSV_FILE_ROW_ENTRY_PROCESS', 16);
define_if_not_defined_Center('CSV_FILE_ROW_NUMBER_PROCESS', 17);
define_if_not_defined_Center('CSV_FILE_ROW_ANTI_DDOS', 18);
define_if_not_defined_Center('CSV_FILE_ROW_TANG_PLUGIN', 28);

define_if_not_defined_Center('VALUE_EXPAND_COLLAPSE', 21);

define_if_not_defined_Center('COLUMN_TITLE', 0);
?>
<div class="p-2 el-custom-bg-mobile text-sm" style="box-shadow: 0px 8px 15px rgba(0, 17, 29, 0.05); border: 1px solid #F2F2F2;">
	<?php for ($col = 1; $col < count($data->info[0]); $col++) :  ?>
		<div>
			<table class="w-full mt-5 bg-white el-hosting-bang-so-sanh-mobile-v2" id="tbl-<?php echo $col; ?>">
				<tbody>
					<!-- Thông số kỹ thuật -->
					<tr>
						<td class="p-3 font-bold text-lg" style="color: #333333;">
							<?php echo $data->info[0][0] ?>
						</td>

						<td class="p-3 font-bold">
							<div class="flex flex-col items-end">
								<div class="text-primary font-bold text-right flex flex-col">
									<?php
									if (stripos($data->info[0][$col], "SEO") !== false || stripos($data->info[0][$col], "WP") !== false || stripos($data->info[0][$col], "Email") !== false || stripos($data->info[0][$col], "Reseller") !== false) :
										echo "";
										$labelHosting = '';
									else :
										echo '<div>HOSTING</div>';
										$labelHosting = 'HOSTING ';
									endif;
									?>
									<?php echo $data->info[0][$col] ?>
								</div>

								<?php if ($data->info[CSV_FILE_ROW_GIAM_GIA][$col]) : ?>
									<div class="flex flex-row items-center mt-1">
										<div class="font-bold text-base mr-2" style="color: #F2994A;">
											<?php echo $data->info[CSV_FILE_ROW_GIA_SAU_GIAM][$col] ?>
										</div>

										<div class="text-white text-sm h-5 px-1 font-medium" style="background: #EB5757; border-radius: 4px;">
											<?php echo $data->info[CSV_FILE_ROW_GIAM_GIA][$col] ?>
										</div>
									</div>

									<div class="mt-1 flex flex-row items-center font-normal" style="color: #828282;">
										<div class="line-through">
											<?php echo $data->info[CSV_FILE_ROW_GIA_GOC][$col]; ?>
										</div>

										<div>
											<?php echo  '/' . $data->info[CSV_FILE_ROW_DON_VI][$col]; ?>
										</div>
									</div>
								<?php else : ?>
									<div>
										<div class="el-custom-text-price font-bold el-custom-text-mini">
											<?php echo $data->info[CSV_FILE_ROW_GIA_GOC][$col] . '/' . $data->info[CSV_FILE_ROW_DON_VI][$col] ?>
										</div>
									</div>
								<?php endif; ?>

							</div>
						</td>
					</tr>
					<!-- /Thông số kỹ thuật -->


					<!-- /Thông số kỹ thuật -->
					<?php for ($row = 6; $row < count($data->info); $row++) : ?>
						<tr class="<?php if ($row > 7) { ?> <?php echo 'el-custom-default-hidden' ?> <?php } ?>">
							<td class="el-hosting-bang-so-sanh-v2__name">
								<?php echo str_replace('\n', '<br />', $data->info[$row][0]) ?>
							</td>

							<td class="el-hosting-bang-so-sanh-v2__info text-right pr-3">
								<?php
								if ($data->info[$row][$col] === 'TRUE') : ?>
									<i class="fas fa-check text-green-600 text-xs" />
								<?php else : ?>
									<?php echo $data->info[$row][$col] ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endfor; ?>
					<!-- /Thông số kỹ thuật -->

					<!--  -->
					<tr>
						<td colspan="2" class="p-3">
							<div class="flex justify-center">
								<div class="text-center py-2 bg-white el-custom-btn-expand cursor-pointer">
									Chi tiết
								</div>
								<div class="text-center py-2 bg-white el-custom-btn-collapse cursor-pointer el-custom-default-hidden">
									Thu gọn
								</div>
							</div>
						</td>
					</tr>
					<!--  -->
					<tr>
						<td colspan="2" class="p-3">
						<?php if (isset($data->info[CSV_FILE_ROW_GIAM_GIA][$col])) {
								$price_g = trim($data->info[CSV_FILE_ROW_GIA_SAU_GIAM][$col], " ");
							} else {
								$price_g = trim($data->info[CSV_FILE_ROW_GIA_GOC][$col], " ");
							} ?>
							<a class="px-5 py-2 el-custom-btn-register-mobile rounded-md flex justify-center vnx-btn-conversion" rel="nofollow" data-price="<?php echo $price_g; ?>" data-period="1 <?php echo $data->info[CSV_FILE_ROW_DON_VI][$col]; ?>" data-product-name="<?php echo $labelHosting; echo $data->info[0][$col]; ?>" data-product-category="<?php echo $labelHosting; echo trim(preg_replace('/\d+/', '', $data->info[0][$col])); ?>" href="<?php echo $data->info[CSV_FILE_ROW_URL_DANG_KY][$col] ?>">
								Đăng ký
							</a>
						</td>
					</tr>
					<!--  -->
				</tbody>
			</table>

		</div>
	<?php endfor; ?>
</div>