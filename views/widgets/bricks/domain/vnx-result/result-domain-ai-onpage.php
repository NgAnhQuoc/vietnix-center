<?php
$settings = $data->settings;
$icon_div_domain_ai = $settings['icon_div_domain_ai'];
$icon_add_to_cart = $settings['icon_add_to_cart'];
$icon_add_to_cart_remove = $settings['icon_add_to_cart_remove'];

?>
<style>
  /* Thu gọn khối "vnx-result-loading" khi dùng làm chỉ báo "đang tải thêm"
     bên dưới danh sách kết quả, thay vì khối loading to 120px mặc định. */
  .vnx-result-loading.vnx-loading-more {
    height: 60px !important;
    padding: 8px 0 !important;
    border: none !important;
    background: none !important;
    margin-top: 8px;
  }
</style>
<div class="vnx-result-domain-ai-onpage">
  <div class="vnx-result-domain-body">
    <div class="vnx-result">
      <div class="vnx-result-content" v-show=" IsLoading == false" v-cloak style="display: none;">
        <div class="item-available" v-for="(domain, index) in ListDomain" :key="index">
          <div class="item-bottom">
            <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
              <div class="item-bottom-left-icon">
                <?php
                if (!empty($icon_div_domain_ai)) {
                  echo Bricks\Element::render_icon($icon_div_domain_ai, ['vnx_icon']);
                }
                ?>
              </div>
              <div class="item-bottom-left-title">
                <p class="domain-name">{{domain.sld}}<span class="domain-tld">.{{domain.tld}}</span></p>
              </div>
            </div>
            <div class="item-bottom-right flex items-center flex-nowrap">
              <div class="item-domain">
                <div class="item-domain-tooltip" v-if="domain.isPremium == false && domain.errorDomainVN == false && calculatePriceDomain(formatPrice(domain.pricing?.register?.['1']), getPriceDomainData(domain.tld)) > 0 && domain.isAvailable && getTooltipContentDomainData(domain.tld) != ''">
                  <div class="item-domain-tooltip-icon">
                    <i class="fa-light fa-circle-info"></i>
                    <p class="domain-tooltip-text-hidden" v-if="getTooltipContentDomainData(domain.tld) != ''">{{getTooltipContentDomainData(domain.tld)}}</p>
                  </div>
                  <div class="item-domain-tooltip-content">
                    <p class="domain-tooltip-text">{{getTooltipTitleDomainData(domain.tld)}}</p>
                  </div>
                </div>
                <div class="item-domain-price" v-if="domain.isPremium == false && domain.pricing?.register?.['1']">
                  <div class="item-domain-original-price flex flex-row gap-2 items-center" v-if="calculatePriceDomain(formatPrice(domain.pricing?.register?.['1']), getPriceDomainData(domain.tld)) > 0">
                    <p class="domain-price-text" v-if="calculatePriceDomain(formatPrice(domain.pricing?.register?.['1']), getPriceDomainData(domain.tld)) > 0" v-html="getPriceDomainData(domain.tld)"></p>
                    <div class="item-domain-tag">
                      <div class="item-domain-tag-icon flex items-center justify-center">
                        <i class="fa-light fa-tag text-white"></i>
                      </div>
                      <span class="domain-tag-text">-{{calculatePriceDomain(formatPrice(domain.pricing?.register?.['1']),
                        getPriceDomainData(domain.tld))}}%</span>
                    </div>
                  </div>
                  <div class="item-domain-discount-price">
                    <p class="domain-price-text">{{formatPrice(domain.pricing?.register?.['1'])}} /Năm đầu</p>
                  </div>
                </div>
              </div>
              <div class="item-button vnx_addto_cart">
                <div class="vnx_wrapper relative" v-if="!domain.isInCart && domain.isPremium == false">
                  <button class="add_domain_cart button_detail vnx-btn-register" :data-sld="domain.sld" :data-tld="domain.tld" @click="clickAddToCart(domain.sld,domain.tld)" :data-domain="domain.domainName">
                    <?php
                    if (!empty($icon_add_to_cart)) {
                      echo Bricks\Element::render_icon($icon_add_to_cart, ['vnx_icon']);
                    }
                    ?>
                  </button>
                  <p class="vnx_text_addto_cart">Chọn mua tên miền</p>
                </div>
                <div class="vnx_wrapper relative" v-if="domain.isInCart && domain.isPremium == false">
                  <button class="vnx_haveto_cart button_detail" :data-domain="domain.domainName" @click="clickRemoveDomainAi(domain.domainName)">
                    <?php
                    if (!empty($icon_add_to_cart_remove)) {
                      echo Bricks\Element::render_icon($icon_add_to_cart_remove, ['vnx_icon']);
                    }
                    ?>
                  </button>
                  <p class="vnx_text_haveto_cart">Bỏ chọn mua tên miền</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="vnx-result-loading vnx-loading-more" v-show="IsLoadingMore == true" style="display: none;">
          <div class="loading_small">
            <div class="loading_wrapper">
              <div class="loading_icon"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="vnx-result-loading" v-show="IsLoading == true" style="display: none;">
        <div class="loading_small">
          <div class="loading_wrapper">
            <div class="loading_icon"></div>
          </div>
        </div>
      </div>
      <div class="vnx-result-error" v-show="ResultError == true && ResultAgain == false" style="display: none;">
        <div class="vnx-result-error-content flex flex-col items-center pb-10">
          <div class="vnx-result-error-content-icon">
            <img src="https://vietnix.vn/wp-content/uploads/2025/07/banner_result_none.png" alt="empty-result">
          </div>
          <div class="vnx-result-error-content-text">
            <p>Có vấn đề đường truyền. Vui lòng thử lại!</p>
          </div>
          <div class="vnx-result-error-content-button mt-4">
            <button @click="retrySearch" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
              Thử lại
            </button>
          </div>
        </div>
      </div>
      <div class="vnx-result-error" v-show="ResultError == true && ResultAgain == true" style="display: none;">
        <div class="vnx-result-error-content flex flex-col items-center pb-10">
          <div class="vnx-result-error-content-icon">
            <img src="https://vietnix.vn/wp-content/uploads/2025/07/banner_result_none.png" alt="empty-result">
          </div>
          <div class="vnx-result-error-content-text flex flex-col items-center">
            <h5 class="mb-1 text-lg font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
            <p>Bạn vui lòng nhập lại nội dung chi tiết hơn!</p>
          </div>
        </div>
      </div>
      <div class="vnx-result-empty" v-show="ResultEmpty == true && IsLoading == false && ResultNewEmpty == false" style="display: none;">
        <div class="flex flex-col items-center pb-10">
          <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
          <h5 class="mb-1 text-lg font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
          <span class="text-sm font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào ô tìm kiếm</span>
        </div>
      </div>
      <div class="vnx-result-empty" v-show=" ResultNewEmpty == true && IsLoading == false && ResultEmpty == true && ResultError == false" style="display: none;">
        <div class="flex flex-col items-center pb-10">
          <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
          <h5 class="mb-1 text-lg font-medium text-[#000000]">Bạn muốn tìm kiếm tên miền ?</h5>
          <span class="text-sm font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào ô tìm kiếm</span>
        </div>
      </div>
    </div>
  </div>
</div>