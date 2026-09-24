<?php wp_nonce_field('domain_suggest_checking', 'vnx_domain_suggest_security');
$settings = $data->settings;
?>
<script>
  window.whoisUrl = <?php echo json_encode($settings["whois_url"]); ?>;
</script>

<div class=" gap-8 w-full bg-white ">
  <div id="suggestion_domain" class=" ">

    <div class="w-full h-full bg-white border border-gray-200 rounded-lg shadow  items-center justify-center"
      v-show="DomainError && LoadingResult === false" style="display: none;">
      <div class="flex flex-col items-center pb-10">
        <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg"
          alt="none domain" />
        <h5 class="mb-1 text-lg text-center font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
        <span class="text-sm text-center font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào
          ô tìm
          kiếm</span>
      </div>
    </div>

    <!-- Hiển thị kết quả   -->
    <div class="vnx_custom_result h-[233px] vnx_tablet:h-auto px-8 rounded-lg vnx_tablet:px-3 vnx_tablet:py-5" v-show="DomainShowResult && DomainError === false"
      style="display: none;">
      <div id="show-domain-suggest" class="mb-3">
        <div v-if="DomainShowResult" class="flex justify-between items-center vnx-main-domain vnx_tablet:flex-col">
          <div class="w-full flex justify-center">
            <img v-if="DomainAvailable === true"
              src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/illustrations/congratulations.svg">

            <img v-if="DomainAvailable === false"
              src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/illustrations/search-image.svg">
          </div>
          <div class="w-full vnx_tablet:pb-5">
            <div class="vnx-domain-price flex">
              <span class="vnx-sld">
                {{DomainNameSld}}
              </span>
              <span class="vnx-tld">
                .{{DomainNameTld}}
              </span>
            </div>

            <div class="vnx-domain-status">
              <span v-if="DomainAvailable === true && DomainVnError == false && DomainPremium == false">
                <div class="flex flex-row items-center domain-info">
                  <img class="status_icon"
                    src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/tick.svg"
                    alt="status icon">
                  <div class="ml-1 text-[#219653]">
                    Tên miền có thể đăng ký </div>
                </div>
              </span>

              <!-- Tên miền đã đăng ký  -->
              <span v-if="DomainAvailable === false && DomainVnError == false">
                <div class="flex flex-row items-center domain-info">
                  <img class="status_icon"
                    src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/x-icon.svg"
                    alt="status icon">
                  <div class="status ml-1">
                    Tên miền đã được đăng ký </div>
                </div>
              </span>

  
              <!-- Tên miền 2 kí tự -->
              <span v-if="DomainVnError == true">
                <div class="flex flex-row items-center domain-info">
                  <img class="status_icon"
                    src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/x-icon.svg"
                    alt="status icon">
                  <div class="status ml-1 flex gap-1">
                    Chưa thể đăng ký tên miền .vn có 1,2 kí tự
                    <div class="relative vnx-tooltip-price">
                      <i class="icon-tooltip fa-thin fa-circle-exclamation"></i>
                      <p class="absolute tooltip-content">
                        Theo quy định của Trung tâm Internet Việt Nam (VNNIC) tên miền cấp 2 có 1, 2 kí tự hiện chưa thể
                        đăng ký.
                      </p>

                    </div>
                  </div>
                </div>
              </span>

              <!-- Tên miền đặc biệt-->
              <span v-if="DomainAvailable && DomainVnError == false && DomainPremium == true">
                <div class="flex flex-row items-center domain-info">
                  <img class="status_icon"
                    src="https://stag.vietnix.dev/wp-content/plugins/vietnix-plugin/assets/images/icons/x-icon.svg"
                    alt="status icon">
                  <div class="status ml-1 flex gap-1">
                    Tên miền đặc biệt
                    <div class="relative vnx-tooltip-price">
                      <i class="icon-tooltip fa-thin fa-circle-exclamation"></i>
                      <p class="absolute tooltip-content">
                        Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký.
                      </p>
                    </div>
                  </div>
                </div>
              </span>
            </div>
          </div>

          <div class="w-full vnx-domain-price flex flex-col justify-end text-end items-end vnx_tablet:items-start vnx_tablet:gap-3">
            <div class="flex gap-1 items-end justify-end"
              v-if="DomainAvailable && DomainVnError == false && DomainPremium == false">
              <span class="ori-price line-through pb-1"
                v-if="DomainPriceReduction && DomainAvailable && DomainVnError == false">
                {{DomainPriceReduction}}
              </span>
              <span class="sale-price flex gap-1 items-center justify-end">
                {{DomainPrice}} đ
                <div class="relative vnx-tooltip-price">
                  <i class="tooltip-icon fa-solid fa-circle-question"></i>
                  <p class="absolute tooltip-content">
                    Giá áp dụng cho năm đầu tiên.
                  </p>
                </div>
              </span>
            </div>


           
            <!-- Tooltip -->
            <div v-if="DomainVnError == false" class="vnx_tablet:w-full">
              <button type="button" v-if="DomainPremium == false && DomainIntCart == false && DomainAvailable"
                @click="clickAddToCart(DomainNameSld, DomainNameTld)" :data-domain="DomainName"
                class="vnx_tablet:w-full btn-add-cart rounded-sm bg-[#38A7FF] text-[#fff] text-sm py-[10px] flex justify-center min-w-[257px]">
                <span class="max-md:hidden lg:block">Đăng ký ngay</span>
              </button>

              <button type="button" v-if="DomainIntCart == true" disabled :data-domain="DomainName"
                class="border border-[#38A7FF] bg-[#38A7FF] flex justify-center opacity-50 text-[#fff] text-sm rounded-sm vnx_tablet:w-full py-[10px] min-w-[257px]">
                <span class="max-md:hidden lg:block">Đã thêm</span>
              </button>


              <a :href="`${WhoisURL}?domain=${DomainName}`" type="button" v-if="DomainAvailable == false"
                class="btn-whois  border border-[#38A7FF] text-[#38A7FF] flex justify-center vnx_tablet:w-full rounded-sm text-sm py-[10px] min-w-[257px]">
                <span class="max-md:hidden lg:block">Xem Whois</span>
              </a>

              <button type="button" v-if="DomainAvailable && DomainVnError == false && DomainPremium == true"
                class="btn_tawk border border-[#38A7FF] text-[#38A7FF] text-sm rounded-sm">
                <span class="max-md:hidden lg:block">Liên hệ</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>