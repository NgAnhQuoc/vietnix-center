<?php wp_nonce_field('domain_suggest_checking', 'vnx_domain_suggest_security');
$settings = $data->settings;
?>

<script>
  window.elShowTotalResult = <?php echo json_encode($settings["el_show_total_result"]); ?>;
</script>

<div class=" gap-8 w-full bg-white ">
  <div id="suggestion_domain">

    <!-- Kết quả trống -->
    <div class="w-full h-full bg-white border border-gray-200 rounded-lg shadow  items-center justify-center"
      v-show="ResultEmpty" style="display: none;">
      <div class="flex flex-col items-center pb-10">
        <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg"
          alt="none domain" />
        <h5 class="mb-1 text-lg text-center font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
        <span class="text-sm text-center font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra và ô tìm
          kiếm</span>
      </div>
    </div>

   

    <!-- Loading  -->
    <div class="vnc_loading_result w-full items-center justify-center gap-2" v-show="IsLoading" style="display: none;">
      <div class="vnx-loading_animate flex items-center">
        <img src="<?php echo VNX_PLUGIN_URL_CENTER; ?>assets/images/icons/search-icon-brand.svg" alt="search icon"
          class="vnx_icon mb-5 md:mb-0 mr-0 md:mr-5">
        <div class="col_right">
          <p class="loading_text text-brand text-xl font-medium mb-6">Đã tra cứu
            {{TotalDomainSuccess}}/{{TotalDomainSearch}} tên miền ...</p>
          <div class="progress_outline relative w-full">
            <div class="progress-bar absolute top-0 left-0 h-full"></div>
          </div>
        </div>
      </div>
    </div>


    <!-- Hiển thị kết quả   -->
    <div v-show="ResultEmpty === false && IsLoading === false" style="display: none;">
      <div id="show-domain-suggest" class="rounded-lg border mb-3">
        <div v-for="(domain, index) in ListDomain" :key="index">
          <div
            class="vnx-item-domain py-6 px-8 border-b justify-between flex items-center vnx_tablet:px-3 flex-wrap gap-3"> 


            <!-- Tên miền -->
            <div class="flex items-center vnx_tablet:items-start  gap-2 vnx_tablet:gap-2">
              <div>
                <p class="whitespace-nowrap">
                  <span class="vnx-domain-sld">{{domain.sld}}<span class="vnx-domain-tld"
                      style="word-break: keep-all">.{{domain.tld}}</span> </span>
                </p>

                <!-- label giảm giá  -->
                <div
                  v-if="domain.isPremium == false && domain.errorDomainVN == false && calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0 && domain.isAvailable"
                  class="items-center rounded-[4px] bg-[#EB5757] px-1 text-white text-[12px] w-fit vnx_tablet:flex hidden">
                  -{{calculatePriceDomain(formatPrice(domain.pricing.register['1']),
                  getPriceDomainData(domain.tld))}}%
                </div>
              </div>

              <!-- Tooltip giá -->
              <div class="relative vnx-tooltip-price"
                v-if="domain.isPremium == false && domain.errorDomainVN == false && calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0 && domain.isAvailable">
                <i class="tooltip-icon text-xs fa-light fa-circle-info vnx_tablet:mt-2"></i>
                <p class="absolute tooltip-content italic">
                  Giá áp dụng cho năm đầu tiên.
                </p>

              </div>

              <div class="relative vnx-tooltip-price"
                v-if="domain.isPremium && domain.isAvailable == true && domain.errorDomainVN == false">
                <i class="tooltip-icon text-xs fa-light fa-circle-info vnx_tablet:mt-2"></i>
                <p class="absolute tooltip-content italic">
                  Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký.
                </p>

              </div>
            </div>



            <div class="flex items-center justify-end vnx_tablet:items-start  gap-5 vnx_tablet:gap-5 grow">
              <!-- giá  -->
              <div class="vnx-price"
                v-if="domain.isPremium == false && domain.isAvailable && domain.errorDomainVN == false">
                <span class="sale-price flex gap-1 items-center">
                  {{formatPrice(domain.pricing.register['1'])}}đ <span class="text-[#828282] text-sm">/năm</span>

                  <div
                    v-if="domain.isPremium == false && domain.errorDomainVN == false && calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0 && domain.isAvailable"
                    class="flex items-center rounded-[4px] bg-[#EB5757] px-1 text-white text-[12px] vnx_tablet:hidden">
                    -{{calculatePriceDomain(formatPrice(domain.pricing.register['1']),
                    getPriceDomainData(domain.tld))}}%
                  </div>
                </span>
                <small class="text-xs text-[#828282] line-through pb-1"
                  v-if="calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0">{{getPriceDomainData(domain.tld)}}
                  đ</small>
              </div>

              <!-- Đã thêm  -->
              <button v-if="domain.isInCart && domain.isPremium == false"
                class="vnx-btn-register text-sm font-semibold text-white p-3 opacity-50 bg-[#38A7FF] rounded-md w-40 vnx_tablet:w-10"
                :data-sld="domain.sld" :data-tld="domain.tld" :data-domain="domain.domainName">
                <span class="vnx_tablet:hidden">Đã thêm</span>
                <i class="fa-regular fa-cart-plus vnx_tablet:block hidden"></i>

              </button>


              <!-- Đăng ký ngay  -->
              <button
                v-if="!domain.isInCart && domain.isPremium == false && domain.isAvailable == true && domain.errorDomainVN == false"
                class="vnx-btn-register text-sm font-semibold text-white p-3 bg-[#38A7FF] rounded-md w-40 vnx_tablet:w-10"
                :data-sld="domain.sld" :data-tld="domain.tld" @click="clickAddToCart(domain.sld,domain.tld)"
                :data-domain="domain.domainName">
                <span class="vnx_tablet:hidden">Thêm vào giỏ hàng</span>
                <i class="fa-regular fa-cart-plus vnx_tablet:block hidden"></i>
              </button>

              <!-- Xem whois  -->
              <div class="whitespace-nowrap w-fit px-3 py-[6px] bg-[#F4E8E8] rounded-full text-[#EB5757] text-sm"
                v-if="domain.isAvailable == false && domain.errorDomainVN == false">
                <span class="">Tên miền đã có chủ sở hữu</span>
                <i class="fa-light fa-circle-info"></i>
              </div>

              <!-- Domain error  -->
              <div class=" px-3 py-[6px] bg-[#F4E8E8] rounded-full text-[#EB5757] text-sm"
                v-if="domain.errorDomainVN">
                <span>Chưa thể đăng ký tên miền .vn có 1,2 kí tự
                </span>
                <i class="fa-light fa-circle-info"></i>
              </div>

              <!-- Liên hệ  -->
              <div class="" v-if="domain.isPremium && domain.isAvailable == true && domain.errorDomainVN == false">
                <button
                  class="vnx-btn-register text-sm font-semibold text-[#38A7FF] border border-[#38A7FF] p-3 rounded-md w-40 btn_tawk vnx_tablet:w-10">
                  <span class="vnx_tablet:hidden">Liên hệ</span>
                  <i class="fa-light fa-messages vnx_tablet:block hidden"></i>

                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

     <!-- Kết quả lỗi -->
     <div class="flex justify-center w-full h-full bg-white border border-gray-200 rounded-lg shadow" v-if="ResultError">
      <div class=" py-5 flex flex-col items-center gap-2"><img class="mx-auto" src="https://vietnix.vn/wp-content/uploads/2023/08/timeout.svg">
        <p class="text-center">Đã xảy ra lỗi trong quá trình tìm kiếm. <br> Vui lòng thử lại.</p>
        <button class="rounded flex items-center vnx_reload_searchdomain" onclick="window.location.reload();" id="reloadButton_searchDomain">
          <i class="fa-solid fa-rotate-right"></i> Tải lại trang
        </button>
      </div>
    </div>

  </div>
</div>