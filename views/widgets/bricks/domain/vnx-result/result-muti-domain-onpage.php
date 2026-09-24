<?php
$settings = $data->settings;
$icon_add_to_cart = $settings['icon_add_to_cart'];
$icon_add_to_cart_remove = $settings['icon_add_to_cart_remove'];
$whois_url = $settings['whois_url'] ?? '';
$domain_category_data = $data->get_upload_file_data() ?? [];
$list_tld = $data->get_list_tld() ?? [];
?>
<script>
  localStorage.setItem('Data_Category_TLD', '<?php echo json_encode($domain_category_data); ?>');
  localStorage.setItem('List_Category_TLD', '<?php echo json_encode($list_tld); ?>');
</script>
<div class="vnx-result-muti-domain-onpage">
  <div class="vnx-domain-list">
    <div class="vnx-domain-list-body">
      <div class="result-domain-total" v-if="ListDomain.length > 0 && IsLoading == false">
        <span class="result-domain-total-count">{{domainsWithPricingCount}} Kết quả tìm kiếm cho <span class="result-domain-total-domain">{{TotalDomainSearch}} tên miền</span> </span>
      </div>
      <div class="result-domain-tabs" v-show="ListStatus == true" style="display: none;">
        <?php
        if (is_array($domain_category_data) && count($domain_category_data) > 0) {
          foreach ($domain_category_data as $key => $value) {
            $tab_icon = explode('|', $value['name_tab']); // Tách tên tab thành mảng
            $tab_name = $tab_icon[0]; // Lấy tên tab từ phần tử thứ nhất
            $tab_icon = $tab_icon[1]; // Lấy icon tab từ phần tử thứ hai
            ?>
                <button class="result-domain-tab" data-tab="<?php echo $key; ?>" @click="handleTabClick(<?php echo $key; ?>)">
                  <div class="result-domain-tab-icon flex items-center justify-center">
                    <i class="<?php echo $tab_icon; ?>"></i>
                  </div>
                  <span class="result-domain-tab-text"><?php echo $tab_name; ?></span>
                </button>
            <?php }
        } ?>
      </div>
      <ul class="result-domain-list" v-show="ListStatus == true">
        <!-- không có tên miền -->
        <li class="result-domain-item-empty" v-show=" IsLoading == false && visibleDomains.length == 0">
          <div class="vnx-result-empty">
            <div class="flex flex-col items-center pb-10">
              <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
              <h5 class="mb-1 text-lg font-medium text-[#000000]">Không có tên miền phù hợp cho danh mục</h5>
              <span class="text-sm font-normal text-[#828282]">Bạn vui lòng nhập tên miền khác để kiểm tra</span>
            </div>
          </div>
        </li>
        <!-- tên miền có combo -->
        <li class="result-domain-item " :class="item.domainClass" v-if="item.price != null" :data-combo="item.isCombo" :data-tld="item.tld" :data-tab="item.tab" :data-sld="item.sld" v-for="item in visibleDomains"
          :key="item.domainName">
          <div class="item-available">
            <!-- tên miền khả dụng -->
            <div class="item-bottom" v-if="item.status == true && item.premium == false && item.domainVN == false">
              <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                <div class="item-bottom-left-title">
                  <p class="domain-name">{{item.sld}}<span class="domain-tld">.{{item.tld}}</span></p>
                </div>
              </div>
              <div class="item-bottom-right flex items-center flex-nowrap">
                <div class="item-domain">
                  <div class="item-domain-tooltip" v-if="item.tooltipContent">
                    <div class="item-domain-tooltip-icon">
                      <i class="fa-light fa-circle-info"></i>
                      <p class="domain-tooltip-text-hidden">{{item.tooltipContent}}</p>
                    </div>
                    <div class="item-domain-tooltip-content" v-if="item.tooltipTitle">
                      <p class="domain-tooltip-text">{{item.tooltipTitle}}</p>
                    </div>
                  </div>
                  <div class="item-domain-price">
                    <div class="item-domain-original-price flex flex-row gap-2 items-center" v-if="item.originalPrice > item.price">
                      <p class="domain-price-text">{{item.originalPrice}}đ</p>
                      <div class="item-domain-tag">
                        <div class="item-domain-tag-icon flex items-center justify-center">
                          <i class="fa-light fa-tag text-white"></i>
                        </div>
                        <span class="domain-tag-text">-{{item.discount}}%</span>
                      </div>
                    </div>
                    <div class="item-domain-discount-price">
                      <p class="domain-price-text">{{formatPrice(item.price)}}đ /Năm đầu</p>
                    </div>
                  </div>
                </div>
                <div class="item-button vnx_addto_cart">
                  <div class="vnx_wrapper">
                    <button v-show="item.isInCart == false" :data-sld="item.sld" :data-tld="item.tld" :data-domain="item.domainName" class="add_domain_cart button_detail_mb vnx-btn-register" @click="clickAddToCartTwo(item.sld,item.tld)">
                      <?php
                      if (!empty($icon_add_to_cart)) {
                        echo Bricks\Element::render_icon($icon_add_to_cart, ['vnx_icon']);
                      }
                      ?>
                    </button>
                  </div>
                  <div class="vnx_wrapper">
                    <button v-show="item.isInCart == true" class="vnx_haveto_cart button_detail_mb" :data-sld="item.sld" :data-tld="item.tld" :data-domain="item.domainName" @click="clickRemoveFromCartTwo(item.sld,item.tld)">
                      <?php
                      if (!empty($icon_add_to_cart_remove)) {
                        echo Bricks\Element::render_icon($icon_add_to_cart_remove, ['vnx_icon']);
                      }
                      ?>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <!-- tên miền đặt biệt -->
            <div class="item-bottom" v-if="item.premium == true && item.status == true && item.domainVN == false">
              <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                <div class="item-bottom-left-title">
                  <p class="domain-name">{{item.sld}}<span class="domain-tld">.{{item.tld}}</span></p>
                </div>
              </div>
              <div class="item-bottom-right flex items-center flex-nowrap">
                <div class="item-domain">
                  <div class="vnx-domain-status special">
                    <span class="title">Tên miền đặc biệt</span>
                  </div>
                </div>
                <div class="item-button">
                  <div class="vnx_wrapper relative">
                    <button class="btn_tawk btn-contact">
                      <span class="btn-contact-text">Liên hệ</span>
                      <div class="btn-contact-icon">
                        <img src="https://vietnix.vn/wp-content/uploads/2025/07/ChatsCircle.svg" alt="Liên hệ">
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <!-- tên miền đã được đăng ký -->
            <div class="item-bottom" v-if="item.status == false && item.domainVN == false">
              <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                <div class="item-bottom-left-title">
                  <p class="domain-name">{{item.sld}}<span class="domain-tld">.{{item.tld}}</span></p>
                </div>
              </div>
              <div class="item-bottom-right flex items-center flex-nowrap">
                <div class="item-domain">
                  <div class="vnx-domain-status unavailable">
                    <span class="title">Tên miền đã được đăng ký</span>
                  </div>
                </div>
                <div class="item-button">
                  <div class="vnx_wrapper relative">
                    <a :href="'<?php echo $whois_url; ?>?domain=' + item.domainName" target="_blank" class=" btn-view">
                      <span class="btn-view-text">Xem Whois</span>
                      <div class="btn-view-icon">
                        <img src="https://vietnix.vn/wp-content/uploads/2025/07/Eye.svg" alt="Xem whois">
                      </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <!-- tên miền .vn chưa thể đăng ký -->
            <div class="item-bottom" v-if="item.status == true && item.domainVN == true && item.premium == false">
              <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                <div class="item-bottom-left-title">
                  <p class="domain-name">{{item.sld}}<span class="domain-tld">.{{item.tld}}</span></p>
                </div>
              </div>
              <div class="item-bottom-right flex items-center flex-nowrap">
                <div class="item-domain">
                  <div class="vnx-domain-status unavailable">
                    <span class="title">Tên miền .{{item.tld}} chưa thể đăng ký</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
      <!-- Loading  -->
      <div class="vnx-result-loading" v-show="IsLoading == true || IsLoadingMore == true" style="display: none;">
        <div class="loading_small">
          <div class="loading_wrapper">
            <div class="loading_icon"></div>
          </div>
        </div>
      </div>
      <div class="vnx-result-empty" v-show="ListDomain.length == 0 && IsLoading == false && ArrayCache.length == 0 || ShowDomain.length == 0 && ListDomain.length == 0 && TabEmpty == true" style="display: none;">
        <div class="flex flex-col items-center pb-10">
          <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
          <h5 class="mb-1 text-lg font-medium text-[#000000]">Bạn muốn kiểm tra tên miền của mình ?</h5>
          <span class="text-sm font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào ô tìm kiếm</span>
        </div>
      </div>
        <div class="result-domain-more" v-if="visibleDomainCount < totalDomainsInTab">
        <button class="result-domain-more-btn" @click="showMoreDomains">
          <span>Xem thêm tên miền khác</span>
          <i class="fa-light fa-angle-down"></i>
        </button>
      </div>
    </div>
  </div>
</div>