<?php
$settings = $data->settings;
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$box_result = isset($settings['link_result_id']) ? $settings['link_result_id'] : '';
$button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
$button_icon = isset($settings['button_icon']) ? $settings['button_icon'] : array();
$clear_icon = isset($settings['clear_icon']) ? $settings['clear_icon'] : array();
$susggest_tld = isset($settings['susggest_tld']) ? $settings['susggest_tld'] : 'com';
$prioritize_tld = isset($settings['prioritize_tld']) ? $settings['prioritize_tld'] : '';
$prioritize_arr = explode(',', $prioritize_tld);
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
  var tld_data = <?php echo json_encode($csvdata, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD', JSON.stringify(tld_data));
  var data_sussgest = <?php echo json_encode($data_sussgest, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD_Sussgest', JSON.stringify(data_sussgest));
</script>

<?php wp_nonce_field('domain_checking', 'vnx_domain_security'); ?>
<input type="hidden" name="template" id="vnx_suggest_tld" value="<?php echo esc_attr($susggest_tld); ?>">
<input type="hidden" name="template" id="vnx_template_domain" value="view-search-domain">
<input type="hidden" name="vnx_box_result" id="vnx_box_result" value="<?php echo $box_result; ?>">
<form class="relative rounded-lg" data-tld='<?php echo esc_attr($susggest_tld); ?>' data-priority='<?php echo esc_attr($prioritize_arr); ?>'>
  <div class="vnx_wrapper overflow-hidden relative rounded-lg">
    <i class="vnx_icon absolute left-4 top-4 z-[1] ti-search"></i>
    <input type="text" id="vnx_search_domain_input" class="relative z-0 border outline-none" name="domain" placeholder="<?php echo esc_attr($placeholder); ?>">
    <?php
    if (!empty($clear_icon))
      echo '<i class="vnx_icon clear_icon  ' . $clear_icon['icon'] . '" @click="clickButtonClear"></i>';
    ?>

    <button type="submit" id="vnx_search_domain_btn" @click="clickSearchButton" class="absolute z-[1] rounded right-0 top-0 flex items-center" :disabled="DisableButton">
      <?php
      if (!empty($button_icon))
        echo Bricks\Element::render_icon($button_icon, ['vnx_icon']);
      echo '<span class="button_text">' . esc_html($button_text) . '</span>';
      ?>
    </button>
  </div>
  <div class="loading_domain text-center py-3 relative " v-show="LoadingResult" style="display: none;">
    <svg aria-hidden="true" role="status" class="inline w-4 h-4  animate-spin " viewBox="0 0 100 101" fill="red" xmlns="http://www.w3.org/2000/svg">
      <path
        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
        fill="#E5E7EB" />
      <path
        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
        fill="#3498db" />
    </svg>
  </div>
  <div class="domain_availble w-full bg-[#F7FAFC] rounded-b-lg relative " v-show="DomainShowResult" id="vail_domain" style="display: none;">
    <!-- availble &  prenium -->
    <div class="vnx_box_domain border-t border-[#DDE1E8] lg:inline-flex w-full items-center py-3 flex-wrap">
      <div class="flex items-center query-domain-res">
        <div class="inline-block">
          <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 rounded-full mx-2" v-if="DomainAvailable == true">
            <img src="https://vietnix.vn/wp-content/uploads/2023/06/check-domain.svg" alt="doamin checked">
            <span class="sr-only">Check icon</span>
          </div>
          <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2" v-if="DomainAvailable == false">
            <img src="https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban">
            <span class="sr-only">Error icon</span>
          </div>
        </div>
        <div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">{{DomainName}}</div>
      </div>
      <div class="vnx_box_status flex flex-col">
        <!-- availble start -->
        <div class="flex sm:mt-0 mt-3 ml-2 w-fit" v-if="DomainPremium == false && DomainAvailable == true">
          <div class="inline-flex px-3 py-1 items-center text-sm text-green-800 rounded-full bg-green-50 relative" role="alert">
            <div class="mr-1"><span class="font-normal">Tên miền đang sẵn sàng cho bạn </span></div>
          </div>
        </div>
        <!-- availble end -->
        <!-- prenium start -->
        <div class="flex sm:mt-0 mt-3 ml-2 w-fit" v-if="DomainPremium == true && DomainAvailable == true">
          <div class="inline-flex px-3 py-1 items-center text-sm text-[#CE6A00] rounded-full bg-[#FFF0BA] relative" role="alert">
            <div class="mr-1"><span class="font-normal">Tên miền đặc biệt, vui lòng liên hệ Vietnix để đăng ký</span>
            </div>
          </div>
        </div>
        <!-- prenium end -->
        <!-- unavailble -->
        <div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative w-fit" role="alert" v-if="DomainAvailable == false && DomainVnError == false && DomainError == false">
          <div class="mr-1"><span class="font-normal">Tên miền đã được đăng ký</span></div>
        </div>
        <!-- tên miền .vn -->
        <div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative w-fit" role="alert" v-if="DomainAvailable == false && DomainVnError == true">
          <div class="mr-1"><span class="font-normal">Chưa thể đăng ký tên miền .vn có 1,2 kí tự</span></div>
          <div class="search_tooltip"><img class="search_tooltip_icon" src="https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red">
            <div class="tool_tip_search absolute">
              <div class="tooltiptext_search w-full relative">
                <div class="text-xs text-left font-bold w-full"></div>
                <div class="text-xs text-left font-normal mt-1 w-full">Theo quy định của Trung tâm Internet Việt Nam (VNNIC) tên miền cấp 2 có 1,2 kí tự hiện chưa thể đăng ký. Quý khách có thể tham khảo tại đây: <a href=""><strong>Xem chi
                      tiết</strong></a></div>
              </div>
            </div>
          </div>
        </div>
        <!-- tên miền không hợp lệ -->
        <div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative w-fit" role="alert" v-if="DomainError == true">
          <div class="mr-1"><span class="font-normal">Tên miền không hợp lệ</span></div>
        </div>
      </div>
      <div class="lg:ml-auto lg:inline-flex flex lg:flex-nowrap flex-wrap lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto" v-if="DomainAvailable == true">
        <div class="items-center lg:w-fit w-full lg:justify-center px-3 lg:ml-auto lg:mt-0 mt-4 price_text flex" v-if="DomainPremium == false">
          <div class="text-left">
            <div class="font-sans font-semibold inline-flex text-[#828282] content-center items-center">
              <p class="text-[#F2994A] mr-1">{{DomainPrice}} đ</p>/năm<span class="bg-[#EB5757] text-white text-xs font-medium ml-2 px-1 py-0.5 rounded" v-if="DomainPriceReduction">-{{DomainPricePercent}}%</span>
            </div>
            <div class="text-xs line-through text-[#828282]" v-if="DomainPriceReduction">{{DomainPriceReduction}} đ</div>
          </div>
        </div>
        <div class="flex flex-col items-center justify-center px-3 lg:w-fit w-full lg:ml-auto lg:mt-0 sm:ml-auto mt-4 ml-auto price_button">
          <button type="button" data-domain="" data-sld="" data-tld="" class="text-white bg-[#38A7FF] hover:bg-[#38A7FF] font-medium rounded-lg max-md:w-full text-sm px-3 py-2.5 focus:outline-none add_domain_cart" style="min-width: 9rem"
            @click="clickAddToCart(DomainNameSld,DomainNameTld)" v-show="DomainPremium == false && DomainIntCart == false">Thêm
            vào giỏ hàng</button>
          <button type="button" data-domain="riback.tv" class="text-white font-medium rounded-lg max-md:w-full text-sm px-3 py-2.5 focus:outline-none add_domain_cart cursor-not-allowed lg:bg-[#81AFD3] bg-[#38A7FF] lg:opacity-100 opacity-50"
            disabled="" style="min-width: 9rem;" v-show="DomainIntCart == true">
            <span class="hidden lg:block">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
              </svg> Đã thêm</span>
            <img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/added_cart_icon.svg" alt="added to cart icon">
          </button>
          <button type="button" data-domain="" class="text-white bg-[#38A7FF] hover:bg-[#38A7FF] font-medium rounded-lg text-sm px-3 py-2.5 focus:outline-none btn_tawk " v-if="DomainPremium == true " style="min-width: 9rem">Liên hệ</button>
        </div>
      </div>
    </div>
  </div>
</form>