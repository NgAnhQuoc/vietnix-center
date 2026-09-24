<?php wp_nonce_field('domain_suggest_checking', 'vnx_domain_suggest_security');
$settings = $data->settings;
?>
<script>
  window.whoisUrl = <?php echo json_encode($settings["whois_url"]); ?>;
</script>

<div class=" gap-8 w-full bg-white ">
  <div id="suggestion_domain">

    <!-- Kết quả trống -->
    <div class="w-full h-full bg-white border border-gray-200 rounded-lg shadow  items-center justify-center"
      v-show="ResultEmpty == true && InitHidden == false" style="display: none;">
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
          <p class="loading_text text-brand text-xl font-medium mb-6">Đang tra cứu tên miền, vui lòng chờ giây lát..</p>
          <div class="progress_outline relative w-full">
            <div class="progress-bar absolute top-0 left-0 h-full"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hiển thị kết quả   -->
    <div v-show="IsLoading == false" style="display: none;">
      <div id="show-domain-suggest" class="mb-3 relative">
        <div v-for="(domain, index) in ListDomain" :key="index" class="box">
          <div
            class="vnx-item-domain px-8 py-6 vnx_tablet:px-4 border-b flex justify-between items-center vnx_tablet:items-start vnx_tablet:gap-5">
            <div class="flex flex-col items-start justify-start w-full gap-2 vnx_tablet:gap-2">
              <p class="whitespace-nowrap">
                <span class="vnx-domain-sld">{{domain.sld}}<span class="vnx-domain-tld"
                    style="word-break: keep-all">.{{domain.tld}}</span> </span>
              </p>

              <!-- Trạng thái tên miền -->

              <!-- Tên miền có thể đăng ký    -->
              <div class="flex items-center"
                v-if="domain.isAvailable == true && domain.isPremium == false && domain.errorDomainVN == false">
                <img class="status_icon"
                  src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/tick.svg"
                  alt="status icon">
                <div class="ml-1 text-[#219653] text-sm">
                  Tên miền có thể đăng ký </div>
              </div>

              <!-- Tên miền đã được đăng ký -->
              <div class="flex items-center" v-if="domain.isAvailable == false && domain.errorDomainVN == false">
                <img class="status_icon"
                  src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/x-icon.svg"
                  alt="status icon">
                <div class="ml-1 text-[#4F4F4F] text-sm">
                  Tên miền đã được đăng ký </div>
              </div>

              <!-- Tên miền VN 2 ký tự -->
              <div class="flex items-center vnx-status-domain" v-if="domain.errorDomainVN">
                <img class="status_icon"
                  src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/x-icon.svg"
                  alt="status icon">
                <div class="ml-1 text-[#4F4F4F] flex gap-1 items-center text-sm">
                  Chưa thể đăng ký tên miền .vn có 1,2 kí tự
                  <div class="relative vnx-tooltip-status">
                    <i class="icon-tooltip fa-thin fa-circle-exclamation"></i>
                    <p class="absolute tooltip-content ">
                      Theo quy định của Trung tâm Internet Việt Nam (VNNIC) tên miền cấp 2 có 1, 2 kí tự hiện chưa thể
                      đăng ký.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Tên miền cao cấp-->
              <div class="flex items-center vnx-status-domain"
                v-if="domain.isPremium && domain.errorDomainVN == false && domain.isAvailable == true">
                <img class="status_icon"
                  src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/x-icon.svg"
                  alt="status icon">
                <div class="ml-1 text-[#4F4F4F] flex gap-1 items-center text-sm">
                  Tên miền đặc biệt
                  <div class="relative vnx-tooltip-status">
                    <i class="icon-tooltip fa-thin fa-circle-exclamation"></i>
                    <p class="absolute tooltip-content">
                      Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký
                    </p>
                  </div>
                </div>
              </div>
            </div>


            <div class="flex flex-col items-end vnx_tablet:items-start w-full gap-2 vnx_tablet:gap-5">

              <!-- giá  -->
              <div class="vnx-price flex gap-1 items-end"
                v-if="DomainAvailable == true && domain.isPremium == false && domain.isAvailable && domain.errorDomainVN == false">

                <small class="origin-price line-through pb-1"
                  v-if="calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0">{{getPriceDomainData(domain.tld)}}
                  đ</small>


                <span class="sale-price flex gap-1 items-center">
                  {{formatPrice(domain.pricing.register['1'])}} đ

                  <div class="relative vnx-tooltip-price"
                    v-if="calculatePriceDomain(formatPrice(domain.pricing.register['1']), getPriceDomainData(domain.tld)) > 0">
                    <i class="tooltip-icon fa-solid fa-circle-question"></i>
                    <p class="absolute tooltip-content">
                      Giá áp dụng cho năm đầu tiên.
                    </p>

                  </div>
                </span>


              </div>


              <button v-if="domain.isInCart && domain.isPremium == false" class="vnx-btn-register"
                :data-domain="domain.domainName" disabled="">
                Đã thêm
              </button>


              <button
                v-if="!domain.isInCart && domain.isPremium == false && domain.isAvailable == true && domain.errorDomainVN == false"
                class="vnx-btn-register" :data-sld="domain.sld" :data-tld="domain.tld"
                @click="clickAddToCart(domain.sld,domain.tld)" :data-domain="domain.domainName">
                <span>Đăng ký ngay</span>
              </button>
              <div class="vnx_tablet:w-full" v-if="domain.isAvailable == false && domain.errorDomainVN == false">
                <a class="vnx-btn-whois vnx_tablet:w-full" :href="WhoisURL+ '?domain=' + domain.domainName">
                  <span>Xem whois</span>
                </a>
              </div>

              <div class="vnx_tablet:w-full"
                v-if="domain.isPremium && domain.isAvailable && domain.errorDomainVN == false">
                <button class="vnx-btn-whois vnx_tablet:w-full btn_tawk">
                  <span>Liên hệ</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>



      <!-- Load more -->
      <div class="w-full flex items-center justify-center gap-2 text-[#007CFC] my-4 font-medium" v-if="IsLoadingMore"
        style="display: none;">
        <p>Đang tải thêm</p>
        <svg aria-hidden="true" role="status" class="inline w-4 h-4  animate-spin " viewBox="0 0 100 101" fill="red"
          xmlns="http://www.w3.org/2000/svg">
          <path
            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
            fill="#E5E7EB" />
          <path
            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
            fill="#007CFC" />
        </svg>
      </div>

      <!-- 
      1. IsLoadingMore = true khi loading more đang chạy
      2. isLoadFullTLD = true khi kết tổng TLD đã gọi = list tld 
      3. IsHiddenResultMore = true khi bị ẩn kết quả tìm kiếm thêm
      -->
      <div v-if="ResultEmpty == false && IsLoadingMore == false" class="w-full flex items-center justify-center gap-2 text-[#007CFC] my-4">
        <!-- Hiện nút xem thêm  -->
        <div class="" v-if="isLoadFullTLD == false && IsLoadingMore == false">
          <button class="vnx-btn-load-more font-medium flex gap-1 items-center" @click="loadMoreDomain">Xem thêm tuỳ
            chọn <i class="fa-thin fa-angle-down mt-[3px]"></i></button>
        </div>

        <!-- Hiện nút ẩn bớt kết quả   -->
        <div class=" flex justify-center flex-col items-center gap-2"
          v-if="isLoadFullTLD === true && IsHiddenResultMore === false">
          <p class="text-center px-10 text-gray-500 italic text-sm">
            Đây là tất cả gợi ý cho bạn, nếu bạn vẫn không tìm được tên miền phù hợp, vui lòng tìm kiếm lại với từ khóa
            khác!
          </p>
          <button class="vnx-btn-load-more font-medium flex gap-1 items-center" @click="hiddenResultMore">Xem ít tùy
            chọn hơn <i class="fa-thin fa-angle-up"></i></button>
        </div>

        <!-- Hiện nút xem thêm  -->
        <div class=" flex justify-center flex-col items-center gap-2"
          v-if="isLoadFullTLD === true && IsHiddenResultMore">
          <p class="text-center px-10 text-gray-500 italic text-sm">
            Các kết quả gợi ý đã được ẩn đi. Vui lòng chọn “Hiển thị kết quả gợi ý” để hiển thị lại!
          </p>
          <button class="vnx-btn-load-more font-medium flex gap-1 items-center" @click="hiddenResultMore">Hiển thị kết
            quả gợi ý <i class="fa-thin fa-angle-down mt-[3px]"></i></button>
        </div>
      </div>
    </div>

    <!-- Kết quả lỗi -->
    <div class="flex justify-center w-full h-full bg-white border border-gray-200 rounded-lg shadow" v-if="ResultError">
      <div class=" py-5 flex flex-col items-center gap-2"><img class="mx-auto"
          src="https://vietnix.vn/wp-content/uploads/2023/08/timeout.svg">
        <p class="text-center">Đã xảy ra lỗi trong quá trình tìm kiếm. <br> Vui lòng thử lại.</p>
        <button class="rounded flex items-center vnx_reload_searchdomain" onclick="window.location.reload();"
          id="reloadButton_searchDomain">
          <i class="fa-solid fa-rotate-right"></i> Tải lại trang
        </button>
      </div>
    </div>
  </div>
</div>