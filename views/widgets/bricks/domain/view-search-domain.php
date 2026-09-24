<?php

$data = isset( $data ) ? $data : new stdClass();

$domain_search = $data->domain_search;
$dotPosition = strpos( $domain_search, '.' );
$afterDot = ( $dotPosition !== false ) ? substr( $domain_search, $dotPosition + 1 ) : '';
$beforeDot = ( $dotPosition !== false ) ? substr( $domain_search, 0, $dotPosition ) : '';
$period = "năm";
$text = "Tên miền đã có chủ sở hữu";
$userInput = 'example';
function generateRandomString_Center( $userInput, $count )
{
	$randomNums = [];
	$suggestedDomain = [];
	for ( $i = 0; $i < $count; $i++ ) {
		$randomNum = str_pad( mt_rand( 0, 99 ), 2, '0', STR_PAD_LEFT );
		$randomNums[] = $randomNum;
	}
	for ( $i = 0; $i < $count; $i++ ) {
		$variations = array();
		$variations[] = $randomNums[ $i ] . $userInput;
		$variations[] = $userInput . $randomNums[ $i ];
		$suggestedDomain[] = $variations[ array_rand( $variations ) ];
	}
	return $suggestedDomain;
}

?>
<div class="custom">
	<script>
		jQuery(document).ready(function ($) {
			var after_dot = '<?= $afterDot ?>'
			var before_dot = '<?= $beforeDot ?>'
			var domain_data_array = get_sessionStorage('domain_Data');
			let prioritize = ['vn', 'com', 'com.vn', 'net']
			if (typeof prioritize_tld != "undefined" && prioritize_tld != "") {
				prioritize = prioritize_tld
			}

			const group_index = 5
			let data_first = []
			let back_arr = {}
			function reorderObject(obj, order) {
				var reorderedObj = {};
				order.forEach(function (key) {
					if (obj.hasOwnProperty(key)) {
						reorderedObj[key] = obj[key];
					}
				});
				return reorderedObj;
			}
			prioritize.forEach(async function (key) {
				if (!(key === after_dot)) {
					var data = {
						"data": {
							security: $('#vnx_domain_security').val(),
							"domain": before_dot + '.' + key,
						}
					}
					data_first.push(before_dot + '.' + key)
					if (before_dot.length <= 2 && key == 'vn') {
						return true
					}
					else {
						// console.log(key)
						await post_ajax_Data('POST', 'domain_vailid_checking_Center', data)
							.then(function (data) {
								back_arr[key] = data
							})
							.catch(function (error) {
								// console.log('Error occurred:', error);
							});
					}
				}

			});
			setTimeout(function () {
				var orderedDataObj = reorderObject(back_arr, prioritize);
				$.each(orderedDataObj, function (item, value) {
					show_suggestion(value, before_dot, item)
				})
			}, 3300)

			var keys = Object.keys(domain_data_array['pricing']);
			var index = 0;
			var html = '';
			var count = 0;
			let domain_random_string = get_sessionStorage('domain_random_Data');
			// $('#vnx_search_domain').prop("disabled", true);
			let group_data = []
			$.each(domain_data_array['pricing'], async function (key, value) {
				count++;
				if (key == after_dot) {
					return true;
				}
				if (count % group_index === 0 && count < Object.keys(domain_data_array['pricing']).length) {
					if ($.inArray(key, prioritize) == -1) {
						group_data.push(before_dot + '.' + key)
					}
					let data_group_obj = {
						"data": {
							security: $('#vnx_domain_suggest_security').val(),
							"domain": group_data,
							"random_string": domain_random_string
						}
					}
					setTimeout(function () {
						post_ajax_Data('POST', 'get_api_listDomain_whmcs_center', data_group_obj)
							.then(function (data) {
								const res = data.data[0]
								let domain_random_string_n = get_sessionStorage('domain_random_Data');
								// console.log(res)
								if (domain_random_string_n == data.data[1]) {
									$.each(res, function (item, value) {
										if (value != null) {
											show_suggestion(value, before_dot, item)
										}
									})
								}
							})
							.catch(function (error) {
								// console.log('Error occurred:', error);
							});
					}, 500)
					group_data = []
				}
				else if (count % group_index != 0 && count < Object.keys(domain_data_array['pricing']).length) {
					if (key == after_dot) {
						return true;
					}
					if ($.inArray(key, prioritize) !== -1) {
						// return true;
						// console.log(key)
					}
					else {
						group_data.push(before_dot + '.' + key)
					}
				}

				else if (count >= Object.keys(domain_data_array['pricing']).length) {
					if (key == after_dot) {
						return true;
					}
					if ($.inArray(key, prioritize) !== -1) {
						// return true;
						// console.log(key)
					}
					else {
						group_data.push(before_dot + '.' + key)
					}
					let data_group_obj = {
						"data": {
							security: $('#vnx_domain_suggest_security').val(),
							"domain": group_data,
							"random_string": domain_random_string
						}
					}
					setTimeout(function () {
						post_ajax_Data('POST', 'get_api_listDomain_whmcs_center', data_group_obj)
							.then(function (data) {
								const res = data.data[0]
								let domain_random_string_n = get_sessionStorage('domain_random_Data');
								if (domain_random_string_n == data.data[1]) {
									$.each(res, function (item, value) {
										if (value != null) {
											show_suggestion(value, before_dot, item)
										}
									})
								}
							})
							.catch(function (error) {
								// console.log('Error occurred:', error);
							});
					}, 500)
				}




				if (count == Object.keys(domain_data_array['pricing']).length) {
					setTimeout(function () {
						$('#suggest_loading').remove()
						// $('#vnx_search_domain').prop("disabled", false);
						// $('#vnx_search_domain_btn').prop("disabled", false);
					}, 10000);
				}
			});

			// setTimeout(function () {
			// 	var random_sld = <?= json_encode( generateRandomString_Center( $beforeDot, 3 ) ) ?>;
			// 	$.each(random_sld, function (key, value) {
			// 		var random_tld = keys[Math.floor(Math.random() * keys.length)];
			// 		var data = {
			// 			"action": "DomainWhois",
			// 			"data": {
			// 				"domain": value + '.' + random_tld,
			// 			}
			// 		}
			// 		post_ajax_Data('POST', 'get_api_whmcs_Center', data)
			// 			.then(function (data) {
			// 				show_suggestion(data, value, random_tld)
			// 			})
			// 			.catch(function (error) {
			// 				console.log('Error occurred:', error);
			// 			});
			// 	})
			// 	$('#suggest_loading').remove()
			// }, 3000)


		})
	</script>
	<?php wp_nonce_field( 'domain_suggest_checking', 'vnx_domain_suggest_security' ); ?>
	<div id='show-domain-suggest'></div>
	<div class="mt-4" id='suggest_loading'>
		<div class="text-center">
			<div class="vnx_loader-square vnx_square vnx_reg vnx_positioning">
				<div class="vnx_loading_block">
					<div class="vnx_loading_box"></div>
				</div>
				<div class="vnx_text_animate_loading">Loading...</div>
			</div>
		</div>
	</div>

</div>

<style>
	.domain_bought .info {
		display: inline-flex;
		align-items: center;
	}

	.vnx_icon_info:before {
		display: block;
		height: 1em;
		content: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAPCAYAAADUFP50AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAFSSURBVHgBdVLbbYNAEIQDxC8luAS7gtgVEFdgXIGTT4QQzwKSChJXkKQCQwWhA1NCCuCRGXRYpxOctPJ62ZmdfZiG9oqi2I/j6JumueV/+A38nziOKzXPnJ00TTeWZX0gcY/ECtZK4IYxuK3jOIcwDNsHkCAhxC+SG9u2z/NHjfSLJPg9RFHUTMA8z+8EQc5RSfZc1/VUErRxI3gYhp0NUMAgKr2qVcB867qO7m6O9X1/hLI7LBCodIJT6fLwMmmq5D9UvALjCzaO0rWhTZYfjYXHloDZCmPlgWwVjOcR2NJRo9wZWOs1EDGUWuvMHBhiT6wK2c+aVJ8zgYlP9lmW5Ull5QHQOH6VkLmYeDbtMcuyN04XdobM7yV98hR5BO9JkqSPk5Pgi1RwxV5bxrFLnhzVBBL0Mkle6O3CcWt9VeRWD91cksVzQx8TGNfScPF6zj/Mf7y2TFpsVQAAAABJRU5ErkJggg==");
	}

	.vnx_icon_info {
		display: inline-block;
		margin: 0;
		font-size: inherit;
		line-height: inherit;
	}

	.domain_bought {
		margin: 10px 0;
	}

	.box {
		font-size: 18px;
		font-weight: 500;
		font-style: normal;
		font-family: "Roboto";
		color: #4f4f4f;
	}

	.domain_bought .fa-info {
		color: #eb5757 !important;
		border: 1px solid #eb5757 !important;
		border-radius: 100%;
		content: "\f129";
		font-family: 'Font Awesome 5 Free';
		height: 14px;
		width: 14px;
		line-height: 11px;
		border-radius: 100%;
		padding: 0px 4px;
		font-size: 9px;
		margin-left: 5px;
		text-align: center;
		top: -4px;
	}

	.info {
		color: #EB5757;
		font-family: 'Roboto';
		font-style: normal;
		font-weight: 400;
		font-size: 14px;
		line-height: 16px;
		background: #F4E8E8;
		padding: 6px 12px;
		border-radius: 90px;
	}

	.tooltip {
		display: inline-block;
		vertical-align: middle;
		font-size: inherit;
		line-height: 1em;
	}

	.year {
		font-size: 14px;
		color: #828282;
	}

	.tooltip .tooltiptext {
		display: none;
		max-width: 226px;
		width: 226px;
		top: 100%;
		left: -88px;
		box-shadow: 0px 5px 25px rgba(0, 0, 0, 0.3);
		/* opacity: 0; */
		transition: opacity 0.3s;
		min-width: 200px;
	}

	.tooltip .tooltiptext::after {
		content: "";
		position: absolute;
		bottom: 100%;
		left: 50%;
		margin-left: -5px;
		border-width: 5px;
		border-style: solid;
		border-color: transparent transparent #fff transparent;
	}

	.tooltip .vnx_icon_info {
		z-index: 2;
	}

	.tooltip:hover .tooltiptext {
		display: block;
		z-index: 1;

	}

	.sub_box {
		width: 50%;
	}

	.box {
		padding: 15px;


	}

	.box:first-child {
		border-top-left-radius: 10px;
		border-top-right-radius: 10px;
		border-bottom: 0px solid #fff;
		border-top: 1px solid #DDE1E8;
	}

	.box:last-child {
		border-top: 1px solid #fff;
		border-bottom-left-radius: 10px;
		border-bottom-right-radius: 10px;
	}

	.unit {
		font-size: 14px;
		line-height: 16px;
		color: #828282;
	}

	.dots {
		font-size: 18px;
		font-weight: 500;
		font-style: normal;
		font-family: "Roboto";
		color: #FE9842;
		margin-right: 3px;
	}

	.domains {

		font-size: 18px;
		font-weight: 500;
		font-style: normal;
		font-family: "Roboto";
		color: #4f4f4f;
	}


	.text_sale_percent {
		color: #fff;
		padding: 6px 12px;
		font-size: 12px;
		font-family: "Roboto";
		font-weight: 500;
		font-style: normal;
		background: #EB5757;
		float: right;
		border-radius: 4px;
		padding: 0px 5px;
		height: 17px;
		width: 32px;
	}

	.text_đescription {
		font-family: 'Roboto';
		font-style: normal;
		font-weight: 400;
		font-size: 12px;
		line-height: 14px;

	}

	.text_sale_price {
		font-family: 'Roboto';
		font-style: normal;
		font-weight: 400;
		font-size: 16px;
		line-height: 20px;
		margin-right: 8px;
	}

	.text_regular_price {
		font-family: 'Roboto';
		font-style: normal;
		font-weight: 400;
		font-size: 12px;
		line-height: 20px;
		text-decoration-line: line-through;
		color: #828282;
	}

	.button_detail {
		width: fit-content;
		padding: 12px 10px;
		background: #38A7FF;
		color: #fff;
		border-radius: 6px;
		font-family: "Roboto";
		font-size: 14px;
		line-height: 16px;
		font-style: normal;
		font-weight: 600;
		text-align: center;
		min-width: 9rem;
	}

	.sub_box.first {
		width: 47%;

	}

	.sub_box.second {
		width: 53%;
	}

	i.special_domain {
		filter: brightness(0) saturate(100%) invert(37%) sepia(41%) saturate(3167%) hue-rotate(21deg) brightness(100%) contrast(101%);
	}

	@media (min-width: 768px) {
		.box_general {
			min-height: 40px;
		}

		.box {
			padding: 16px 12px;
			border-right: 1px solid #DDE1E8;
			border-bottom: 1px solid #DDE1E8;
			border-left: 1px solid #DDE1E8;
		}

		.box:first-child {
			border-bottom: 1px solid #DDE1E8;
			border-top: 1px solid #DDE1E8;
			border-top-left-radius: 10px;
			border-top-right-radius: 10px;

		}

		.box:last-child {
			border-top: 1px solid #fff;
			border-bottom-left-radius: 10px;
			border-bottom-right-radius: 10px;
		}
	}

	@media (max-width: 767px) {
		.sub_box.first {
			width: 50%;
		}

		.sub_box.special {
			width: 40%;
		}


		.box {
			border-right: 1px solid #DDE1E8;
			border-bottom: 1px solid #DDE1E8;
			border-left: 1px solid #DDE1E8;
		}

		.box:first-child {
			border-bottom: 1px solid #DDE1E8;
			border-top: 1px solid #DDE1E8;
			border-top-left-radius: 10px;
			border-top-right-radius: 10px;

		}

		.box:last-child {
			border-top: 01px solid #fff;
			border-bottom-left-radius: 10px;
			border-bottom-right-radius: 10px;
		}

		.sub_box.second {
			width: 50%;
		}

		.sub_box.second.special {
			width: 60%;
		}

		.sub_box.second .button {
			min-width: 45px;
		}

		.box_general {
			display: flex !important;
		}

		.button_detail {
			right: 15px;
		}

		.sub_box.first {
			margin-top: 0;
		}

		.text_sale_price {
			margin: 0;
		}
	}

	@media only screen and (max-width: 1023px) {
		.button_detail {
			width: fit-content;
			min-width: 0rem;
		}

		.sub_box.first {
			width: 60%;
		}

		.sub_box.second {
			width: 40%;
		}

		.button_detail {
			padding: 4px;
		}
	}

	@media only screen and (min-width: 1023px) {
		.box {
			padding: 16px 28px;
		}
	}
</style>