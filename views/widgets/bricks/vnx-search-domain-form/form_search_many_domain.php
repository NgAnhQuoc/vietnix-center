<?php
$settings = isset($data) ? $data : new stdClass();
?>
<div id="vnx_hidden_form_search"
	style="<?php echo (!isset($settings->settings['show_form_search']) && $settings->settings["form_style"] == "form_search_many_domain") ? 'display: none; ' : ''; ?>">
	<?php wp_nonce_field('domain_checking', 'vnx_domain_security'); ?>
	<form method="POST"
		action="<?= isset($settings->settings["link_redirect"]) ? $settings->settings["link_redirect"] : ''; ?>"
		id="form_search_many_domain">
		<div class="vnx-bgform-search">
			<div class=" pr-6 pl-6 pt-5 pb-5">
				<div class="flex flex-row gap-4 pb-2 justify-between w-full">
					<div class="text-white text-base sm:w-4/6 w-6/12">
						<p class="vnx-custom-title-popup">Nhập hay tải lên danh sách các miền</p>
						<span class="vnx-custom-result-domain">Đã nhận
							<?php echo (isset($_POST["count_key"])) ? $_POST["count_key"] : 0; ?> tên miền</span>
						<input type="hidden" name="count_key" class="count_key"
							value="<?php echo (isset($_POST["count_key"])) ? $_POST["count_key"] : 0; ?>">
					</div>
					<div class="text-right vnx-white-space-text sm:w-2/6 w-6/12">
						<button type="button" id="open-popup"
							class=" font-bold py-2 px-4 text-sm whitespace-nowrap cursor-pointer">
							<img src="https://vietnix.vn/wp-content/uploads/2023/06/icon-file-import.png"
								class="float-left mr-1">
							<span> Import File</span>
						</button>
					</div>
				</div>
				<textarea name="input_text_search"
					class="w-full py-3 pl-3 pr-3 rounded-lg background-textarea opacity-100 lowercase"
					id="vnx-textarea-search" placeholder="vietnix&#10;vietnix.vn&#10;vietnix.net"></textarea>
				<div
					class="grid lg:grid lg:grid-cols-4 gap-4 pb-2 align-middle pt-3 md:grid-cols-2 sm:grid-cols-2 grid-cols-4">
					<div class="lg:col-span-2 text-white vnx-white-space-text pt-3  md:col-span-2  sm:col-span-1 col-span-3 "
						style="width: 95%;">
						<span id="open-popup-extension" class="cursor-pointer py-1 px-2 rounded-md <?php if ($settings->settings["form_style"] == 'whois_domain') {
							echo 'hidden';
						} ?>">
							<div class="tooltip relative w-fit hidden_tooltip_button_search_hover whitespace-nowrap">
								<img class="float-left mr-1 mt-1"
									src="https://vietnix.vn/wp-content/uploads/2023/06/icon-setting.png"> <span> Chọn
									phần mở rộng</span>
								<div class="hidden_tooltip_button_search  tooltiptext bg-white text-danger text-center rounded-md absolute px-2 py-1"
									style="top: 110%;left: -10px; width: 248px;">
									<div class="text-center font-bold w-full flex flex-row items-center justify-between flex-nowrap"
										style="font-size: 12px;">
										<svg xmlns="http://www.w3.org/2000/svg" width="12" height="11"
											viewBox="0 0 12 11" fill="none">
											<path
												d="M5.99434 7.9375C6.33809 7.96875 6.51778 8.15625 6.53341 8.5C6.50216 8.84375 6.32247 9.03125 5.99434 9.0625C5.66622 9.03125 5.48653 8.84375 5.45528 8.5C5.48653 8.15625 5.66622 7.96875 5.99434 7.9375ZM5.99434 7C5.75997 6.98438 5.63497 6.85938 5.61934 6.625V3.25C5.65059 3.01562 5.77559 2.89062 5.99434 2.875C6.21309 2.89062 6.33028 3.01562 6.34591 3.25V6.625C6.33028 6.85938 6.21309 6.98438 5.99434 7ZM11.8068 8.80469C12.0412 9.22656 12.049 9.65625 11.8303 10.0938C11.5647 10.5156 11.1818 10.7344 10.6818 10.75H1.30684C0.806843 10.7344 0.42403 10.5156 0.158405 10.0938C-0.0603448 9.65625 -0.0525323 9.22656 0.181843 8.80469L4.86934 0.882812C5.11934 0.476562 5.49434 0.265625 5.99434 0.25C6.49434 0.265625 6.87716 0.476562 7.14278 0.882812L11.8068 8.80469ZM11.1506 9.69531C11.26 9.53906 11.2678 9.375 11.174 9.20312L6.48653 1.28125C6.37716 1.09375 6.21309 1 5.99434 1C5.77559 1 5.61153 1.09375 5.50216 1.28125L0.814655 9.20312C0.720905 9.375 0.720905 9.54688 0.814655 9.71875C0.92403 9.90625 1.08809 10 1.30684 10H10.6584C10.8928 10 11.0568 9.89844 11.1506 9.69531Z"
												fill="#EB5757" />
										</svg> Vui lòng chọn phần mở rộng để kiểm tra
									</div>
								</div>
							</div>
						</span>
						<p id="result_extension" class="mt-3 <?php if ($settings->settings["form_style"] == 'whois_domain') {
							echo 'hidden';
						} ?>"></p>
						<p class="hidden_tooltip_input_search <?php if ($settings->settings["form_style"] == 'whois_domain') {
							echo 'hidden';
						} ?>">Vui lòng chọn phần mở rộng cho các tên miền còn thiếu</p>
					</div>
					<div class="lg:col-span-1 text-white text-right md:col-span-2 sm:col-span-1 col-span-1 pt-3">
						<span class="text-sm mr-3 cursor-pointer" id="deleteDataTextarea">Xóa </span>
					</div>
					<input type="hidden" name="type_form_search" id="type_form_search"
						value="<?= $settings->settings["form_style"] ?>">
					<?php
					if ($settings->settings["form_style"] == 'whois_domain') {
						echo '<input type="hidden" name="default_tld_whois" id="default_tld_whois" value="' . $settings->settings["default_tld_whois"] . '">';
					}
					?>
					<div class="lg:col-span-1 text-white text-center  md:col-span-4 sm:col-span-2  col-span-4 pt-2">

						<button type="submit" disabled
							class="sm:w-full md:w-full  font-bold py-2 px-4 text-sm whitespace-nowrap vnx-cs-width-bottom cursor-pointer flex flex-nowrap justify-center submmit_form_search">
							<span class="loadding_button hidden"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
									xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
									<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
										stroke-width="4"></circle>
									<path class="opacity-75" fill="currentColor"
										d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
									</path>
								</svg></span>
							Tìm kiếm
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<!-- popup import file-->
<div class="popup hidden fixed inset-0 backdrop vnx-bg-popup-overlay flex justify-center items-start">
	<!-- Tạo popup -->
	<div
		class="bg-white rounded-lg overflow-y-auto max-h-screen shadow-xl transform transition-all vn-cus-width-popup-extensiton my-auto">
		<!-- Thêm nút đóng popup -->
		<button class="absolute top-0 right-0 mr-4 mt-2 text-gray-400 hover:text-gray-500 focus:outline-none"
			id="closePopup">
			<img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/06/icon-close.png">
		</button>
		<!-- Thêm nội dung cho popup -->
		<div class="px-4 py-5 sm:p-6">

			<div class="container mx-auto my-10 pb-3">
				<div class="text-lg font-medium  text-gray-900 mb-2 vnx-text-input-file">Import file tên miền</div>
				<div id="drop-zone" class="vnx-drop-zone drop-zone  m-auto">
					<div class="mt-12">
						<img src="https://vietnix.vn/wp-content/uploads/2023/06/icon-import-file.png" class="m-auto">
					</div>
					<div>
						<label for="csv-file" class="vnx-text-input-file">Kéo thả file excel (Định dạng: .csv,
							xlsx) vào đây hoặc ấn vào chọn file. </label>
					</div>
					<div class="pb-1">
						<span class="vnx-text-input-file">Xem file mẫu </span>
						<a href="<?= isset($settings->settings["link_file_domain"]) ? $settings->settings["link_file_domain"] : ''; ?>"
							rel="nofollow" class="text-color-sky vnx-text-input-file">tại đây.</a>
					</div>
					<div class="text-center pt-3 mb-8">
						<label for="csv-file" class="vnx-cus-csv-file border-2 py-2  px-7">
							Chọn file
							<input type="file" name="csv-file" id="csv-file" accept=".xlsx, .xls, .csv" class="w-0">
						</label>
					</div>
				</div>
				<p class="mt-2 vnx-text-note">Lưu ý: Vietnix sẽ lấy 300 mục đầu tiên</p>
				<span id="name-file"></span>
			</div>
		</div>
		<hr>
		<div class="container px-4 py-1 sm:p-3 text-right">
			<button type="button"
				class="border btn pt-1 pb-1 pr-3 pl-3 text-black rounded-md  mr-2 emty-input-file">Huỷ</button>
			<button type="button" id="vnx-upload-file"
				class="rounded-md btn pt-1 pb-1 pr-14 pl-14 btn-primary mr-4 text-white">Import</button>
		</div>
	</div>
</div>
<!-- end popup import-->


<!-- popup cho phần đuôi mở rộng-->
<div class="extension hidden fixed inset-0 backdrop vnx-bg-popup-overlay flex justify-center items-start">
	<!-- Tạo popup -->
	<div
		class="bg-white rounded-lg overflow-auto shadow-xl transform transition-all vn-cus-width-popup-extensiton my-auto max-h-screen">
		<!-- Thêm nút đóng popup -->
		<button class="absolute top-0 right-0 mr-4 mt-2 text-gray-400 hover:text-gray-500 focus:outline-none"
			id="closePopupExtension">
			<img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/06/icon-close.png">
		</button>
		<!-- Thêm nội dung cho popup -->
		<div class="px-4 py-5 sm:p-6">
			<div class="container mx-auto my-10 pb-3">
				<div class="text-lg font-medium  text-gray-900 mb-2 vnx-text-input-file">Chọn phần mở rộng</div>
				<div class="search-input mb-4 w-full">
					<input type="text" class="border w-full pt-2 pb-2" id="search_TLD"
						placeholder="Tìm kiếm phần mở rông...">
					<span class="search-icon">
						<img src="https://vietnix.vn/wp-content/uploads/2023/06/search.png">
					</span>
				</div>
				<p class=" result-tld-search"></p>
				<div class="grid grid-cols-3 gap-4 result-input-search">
				</div>
				<div class="mb-4">
					<label for="checkAll">
						<input type="checkbox" name="checkAll" id="checkAll" class=" custom-checkbox ml-2"> Chọn tất cả
					</label>
				</div>
				<div>
					<p class="mb-4">Phần mở rộng phổ biến</p>
					<div class="grid grid-cols-2 gap-4">
						<label for="exvn" class="cursor-pointer">
							<div class="bg-gray-200 p-4 rounded-lg border-exvn">
								<input type="checkbox" name="nameExtention['vn']" id="exvn" value="vn" width="15%"
									class="vnx-float-left custom-checkbox ">
								<img src="https://vietnix.vn/wp-content/uploads/2023/06/vn.png" class="m-auto">
							</div>
						</label>
						<label for="excom" class="cursor-pointer">
							<div class="bg-gray-200 p-4 rounded-lg border-excom">
								<input type="checkbox" name="nameExtention['com']" id="excom" value="com" width="15%"
									class="vnx-float-left custom-checkbox ">
								<img src="https://vietnix.vn/wp-content/uploads/2023/06/com.png" class="m-auto">
							</div>
						</label>
					</div>

					<div class="grid grid-cols-2 gap-4 mt-4 mb-4">
						<label for="excom_vn" class="cursor-pointer ">
							<div class="bg-gray-200 p-4 rounded-lg border-excom_vn">
								<input type="checkbox" name="nameExtention['com.vn']" id="excom_vn" value="com_vn"
									width="15%" class="vnx-float-left custom-checkbox ">
								<img src="https://vietnix.vn/wp-content/uploads/2023/06/comvn.png" class="m-auto">
							</div>
						</label>

						<label for="exnet" class="cursor-pointer ">
							<div class="bg-gray-200 p-4 rounded-lg border-exnet">
								<input type="checkbox" name="nameExtention['net']" id="exnet" value="net" width="15%"
									class="vnx-float-left custom-checkbox ">
								<img src="https://vietnix.vn/wp-content/uploads/2023/06/net.png" class="m-auto">
							</div>
						</label>
					</div>
					<p class="mb-4">Phần mở rộng khác</p>
					<div class="grid grid-cols-3 gap-4 " id="resultTldDomain">
					</div>
				</div>
			</div>
		</div>
		<hr>
		<div class="container px-4 py-1 sm:p-3 text-right">
			<button type="button"
				class="border btn pt-1 pb-1 pr-3 pl-3 text-black rounded-md mr-2 cursor-pointer uncheckAndClosePopup">Huỷ</button>
			<button type="button" id="vnx-submit-extension"
				class="rounded-md btn pt-1 pb-1 pr-14 pl-14 btn-primary mr-4 cursor-pointer text-white bg-[#38A7FF]">Lưu</button>
		</div>
	</div>
</div>
<!-- end popup cho phần đuôi mở rộng-->