<?php
/**
 * Block Table Coupon Template
 */

$fields = function_exists('get_fields') ? get_fields() : array();
$title_table = empty($fields['title_table_coupon']) ? '' : $fields['title_table_coupon'];
$link_table = empty($fields['link_table_coupon']) ? '#' : $fields['link_table_coupon'];
$table_coupn = get_field('list_table_coupon') ?? [];
?>

<div class="vnx-table-coupon">
	<div class="vnx-table-coupon-header">
		<h3><a href="<?php echo $link_table; ?>" class="vnx-table-coupon-link"><?php echo $title_table; ?></a></h3>
		<i class="fa-solid fa-arrow-up-right-from-square"></i>
	</div>
	<table class="vnx-coupon-table">
		<thead>
			<tr>
				<th>Chu kỳ (tháng)</th>
				<th>Giảm giá</th>
				<th>Mã giảm giá</th>
			</tr>
		</thead>
		<tbody>
			<!-- Nhóm gói coupon -->
			<?php if (is_array($table_coupn) && count($table_coupn) > 0) {
				foreach ($table_coupn as $coupn) {
					$title_group = $coupn['title_list_coupon'] ?? '';
					$cycle = $coupn['group_coupon'] ?? [];
					?>
					<tr class="vnx-group-header">
						<td colspan="3"><?php echo $title_group; ?></td>
					</tr>
					<?php if (is_array($cycle) && count($cycle) > 0) { ?>
						<?php foreach ($cycle as $cycle_item) { ?>
							<tr class="vnx-coupon-row">
								<td><span class="vnx-cycle-text"><?php echo $cycle_item['cycle_title']; ?></span></td>
								<td><span class="vnx-cycle-text"><?php echo $cycle_item['cycle_percent']; ?></span></td>
								<td>
									<div class="vnx-coupon-code">
										<span class="vnx-code"><?php echo $cycle_item['cycle_coupon']; ?></span>
										<button class="vnx-get-code-btn" data-code="<?php echo $cycle_item['cycle_coupon']; ?>">Sao chép</button>
										<button class="vnx-get-code-btn mobile-btn" data-code="<?php echo $cycle_item['cycle_coupon']; ?>"><i class="fa-regular fa-copy"></i><i class="fa-solid fa-check" style="display: none;"></i></button>
									</div>
								</td>
							</tr>
						<?php }
					}
				}
			} ?>
		</tbody>
	</table>
</div>
