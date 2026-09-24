<?php
wp_nonce_field('domain_checking', 'vnx_domain_security');

$settings = $data->settings;
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';

$get_csv = $data->get_data_tld_search();

if (isset($get_csv['status']) && $get_csv['status'] == 'success') {
  $csvdata = isset($get_csv['data']) ? $get_csv['data'] : [];
  $data_sussgest = [];
  foreach ($csvdata as $key => $value) {
    if (!empty($value[0])) {
      $data_sussgest[] = $value[0];
    }
  }
}
?>
<script>

  // truyền dữ liệu import từ bricks sang vue
  window.urlDomainSampleCSV = <?php echo json_encode($settings["domain_sample_csv"]); ?>;
  window.boxResult = <?php echo json_encode($settings["link_result_id"]); ?>;
  window.isRedirect = <?php echo json_encode($settings["is_redirect"]); ?>;
  window.urlRedirectSearchMuti = <?php echo json_encode($settings["url_redirect_search_muti"]); ?>;


  var tld_data = <?php echo json_encode($csvdata, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD', JSON.stringify(tld_data));
  var data_sussgest = <?php echo json_encode($data_sussgest, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD_Sussgest', JSON.stringify(data_sussgest));
</script>


<form class="vnx-form-search-muti relative p-6 vnx_tablet:px-3 vnx_tablet:py-5">
  <div class="">
    <div class="vnx-header-search flex justify-between items-center mb-2 ">
      <div class="flex flex-col text-white text-base font-medium">
        <span class="text-base font-medium">
          Nhập hay tải lên danh sách các miền
        </span>

        <span class="text-sm font-normal">
          Đã nhập {{LineCount}} tên miền
        </span>
      </div>

      <div id="vnx-btn-import" @click="showPopupImportFile"
        class="mb-3 float-right cursor-pointer py-2 px-4 bg-white border border-[#E0E0E0] rounded">
        <i class="fa-regular fa-file-import"></i>
        <span class="vnx_tablet:hidden">Import File</span>
      </div>
    </div>

    <div>
      <textarea v-model="InputDomain" rows="5" class="px-6 py-3 rounded text-gray-700" id="vnx-input-domain"
        placeholder="vietnix
vietnix.vn
vietnix.net">
    </textarea>
    </div>

    <div class="vnx-foter-search flex justify-between items-center mt-4 gap-2 flex-wrap">
      <div>
        <div @click="showPopupExtension"
          class="vnx-btn-extension cursor-pointer p-1 rounded w-fit whitespace-nowrap relative">
          <i class="fa-solid fa-gear"></i>
          <span class="vnx_tablet:hidden">
            Chọn phần mở rộng
          </span>


          <span
            class="vnx-tooltip-extention shadow-md flex items-center gap-1 text-red-500 bg-white rounded px-2 py-1 absolute top-10 -left-2 hidden">
            <i class="fa-regular fa-triangle-exclamation"></i>
            Vui lòng chọn phần mở rộng để kiểm tra
          </span>
        </div>
        <span class="text-[12px] text-inherit vnx_tablet:hidden">
          Vui lòng chọn phần mở rộng cho các tên miền còn thiếu
        </span>
      </div>


      <div class="flex gap-4">
        <button id="vnx-btn-remove" @click="removeInput" class="opacity-50">Xoá</button>
        <button id="vnx-btn-submit"
          class="whitespace-nowrap bg-[#38A7FF] px-8 py-2 rounded font-bold disabled:opacity-50 flex justify-center items-center"
          @click="handleSubmit" :disabled="IsLoading">
          <span class="loadding_button" v-if="IsLoading"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white "
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg></span>
          Tìm kiếm</button>
      </div>
    </div>

    <div class="flex gap-2 flex-wrap mt-1 vnx_tablet:mt-3" >
      <div v-for="(domain, index) in ListDomainChecked" :key="index" @click="deleteDomainChecked(index)"
        class="vnx-item-checked-domain select-none flex text-xs items-center gap-1 px-2 py-1 bg-white rounded-full cursor-pointer">
        <span class="text-[#4F4F4F] mb-[2px]">
          .{{ domain }}
        </span class="text-[#BDBDBD]">
        <i class="fa-regular fa-xmark "></i>
      </div>
    </div>
  </div>
</form>


<!-- popup import file-->
<div id="vnx-popup-import-file"
  class="popup hidden  fixed inset-0 backdrop vnx-bg-popup-overlay flex justify-center items-start">
  <!-- Tạo popup -->
  <div
    class="vnx_tablet:mx-2 bg-white rounded-lg overflow-y-auto max-h-screen shadow-xl transform transition-all vn-cus-width-popup-extensiton my-auto">
    <!-- Thêm nút đóng popup -->
    <button class="closePopup absolute top-2 right-2 text-gray-400 hover:text-gray-500 focus:outline-none"
      id="closePopup">
      <img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/06/icon-close.png">
    </button>
    <!-- Thêm nội dung cho popup -->
    <div class="px-4 py-5 sm:p-6 ">

      <div class="container mx-auto mt-10 pb-3">
        <div class="text-lg font-medium  text-gray-900 mb-2 vnx-text-input-file">Import file tên miền</div>
        <div id="drop-zone" class="vnx-drop-zone drop-zone  m-auto">
          <div class="mt-12">
            <img src="https://vietnix.vn/wp-content/uploads/2023/06/icon-import-file.png" class="m-auto">
          </div>
          <div>
            <label class="vnx-text-input-file">Kéo thả file excel (Định dạng: .csv,
              xlsx) vào đây hoặc ấn vào chọn file. </label>
          </div>
          <div class="pb-1">
            <span class="vnx-text-input-file">Xem file mẫu </span>
            <a :href="urlDomainSampleCSV" rel="nofollow" class="text-color-sky vnx-text-input-file">tại đây.</a>
          </div>
          <div class="text-center pt-3 mb-8">
            <label for="csv-file" class="vnx-cus-csv-file border-2 py-2  px-7 cursor-pointer rounded-md border-gray-600 text-gray-600">
              Chọn file
              <input type="file" name="csv-file" id="csv-file" accept=".xlsx, .xls, .csv" class="w-0">
            </label>
          </div>
        </div>
        <p class="mt-2 vnx-text-note">Lưu ý: Vietnix sẽ lấy 300 mục đầu tiên</p>
        <div id="name-file" class="h-10"></div>
      </div>
    </div>
    <hr>
    <div class="container px-4 py-1 sm:p-3 text-right">
      <button type="button"
        class=" closePopup border btn pt-1 pb-1 pr-3 pl-3 text-black rounded-md  mr-2 emty-input-file">Huỷ</button>
      <button type="button" @click="handleImportFile"
        class="rounded-md btn pt-1 pb-1 pr-14 pl-14 btn-primary mr-4 text-white">Import</button>
    </div>
  </div>
</div>
<!-- end popup import-->

<!-- popup cho phần đuôi mở rộng-->
<div id="vnx-popup-extention" style="z-index: 1;"
  class="extension hidden fixed inset-0 backdrop vnx-bg-popup-overlay flex justify-center items-start">
  <!-- Tạo popup -->
  <div
    class="bg-white rounded-lg overflow-auto shadow-xl transform transition-all vn-cus-width-popup-extensiton my-auto max-h-screen">
    <!-- Thêm nút đóng popup -->
    <button class="absolute top-2 right-2 mr-4 mt-2 text-gray-400 hover:text-gray-500 focus:outline-none"
      id="closePopupExtension">
      <img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/06/icon-close.png">
    </button>
    <!-- Thêm nội dung cho popup -->
    <div class="px-4 py-5 sm:p-6">
      <div class="container mx-auto mt-10 mb-2 pb-3">
        <div class="text-lg font-medium  text-gray-700 mb-2 vnx-text-input-file">Chọn phần mở rộng</div>
        <div class="search-input mb-4 w-full">
          <input type="text" class="border w-full pt-2 pb-2 rounded-md" id="search_TLD"
            placeholder="Tìm kiếm phần mở rộng...">
          <span class="search-icon">
            <img src="https://vietnix.vn/wp-content/uploads/2023/06/search.png">
          </span>
        </div>
        <p class=" result-tld-search"></p>
        <div class="flex flex-wrap justify-start overflow-auto max-h-40" id="result-input-search">
        </div>
        <div class="mb-4 ml-2 cursor-pointer">
          <label for="checkAll">
            <input type="checkbox" name="checkAll" id="checkAll" class=" custom-checkbox ml-2"> Chọn tất cả
          </label>
        </div>
        <div id="result-tld">
          <p class="mb-4 text-gray-700">Phần mở rộng phổ biến</p>
          <div id="result-popular-domain" class="grid grid-cols-2 gap-4">
          </div>


          <p class="my-4 text-gray-700">Phần mở rộng khác</p>
          <div class="flex flex-wrap justify-start  overflow-auto" id="resultTldDomain">
          </div>
        </div>
      </div>
    </div>
    <hr>
    <div class="container px-4 py-1 sm:p-3 text-right">
      <button type="button"
        class="border btn pt-1 pb-1 pr-3 pl-3 text-black rounded-md mr-2 cursor-pointer uncheckAndClosePopup">Huỷ</button>
      <button type="button" id="vnx-submit-extension" @click="handleSubmitExtention"
        class="rounded-md btn pt-1 pb-1 pr-14 pl-14 btn-primary mr-4 cursor-pointer text-white bg-[#38A7FF]">Lưu</button>
    </div>
  </div>
</div>
<!-- end popup cho phần đuôi mở rộng-->