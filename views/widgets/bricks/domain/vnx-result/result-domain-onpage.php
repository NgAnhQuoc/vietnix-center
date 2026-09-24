<?php
$settings = $data->settings;
$icon_add_to_cart = $settings['icon_add_to_cart'];
$icon_add_to_cart_remove = $settings['icon_add_to_cart_remove'];
$whois_url = $settings['whois_url'] ?? '';
$domain_suggest = $settings['domain_suggest'] ?? 'vn';
$domain_category_data = $data->get_upload_file_data();
$list_tld = $data->get_list_tld();
$list_combo_data = $data->get_upload_file_custom($settings['domain_combo_data']['url'], 'row');
$domain_combo_hidden = isset($settings['domain_combo_hidden']) ? $settings['domain_combo_hidden'] : false;
// Validate and sanitize domain_category_data
if (empty($domain_category_data) || !is_array($domain_category_data)) {
    $domain_category_data = [];
    // Log for debugging if needed
}
// Validate and sanitize list_tld
if (empty($list_tld) || !is_array($list_tld)) {
    $list_tld = [];
}
// Validate and sanitize list_combo_data
// Check if it's an error object (has 'status' => 'error') or not an array
if (empty($list_combo_data) || !is_array($list_combo_data) || (isset($list_combo_data['status']) && $list_combo_data['status'] === 'error')) {
    $list_combo_data = [];
}
?>
<script>
  localStorage.setItem('Data_Category_TLD', '<?php echo json_encode($list_tld); ?>');
  sessionStorage.setItem('Data_Combo_TLD', '<?php echo json_encode($list_combo_data); ?>');
  // Lưu giá trị domain_combo_hidden vào sessionStorage để Vue có thể đọc
  sessionStorage.setItem('Domain_Combo_Hidden', '<?php echo $domain_combo_hidden ? 'true' : 'false'; ?>');
</script>
<div class="result-domain-container" data-domain-combo-hidden="<?php echo $domain_combo_hidden ? 'true' : 'false'; ?>">
  <div class="vnx-domain-check">
    <div class="vnx-domain-result" data-domain-suggest="<?php echo $domain_suggest; ?>">
      <div class="vnx-content-result" v-show="HasTld == true">
        <div class="vnx-domain-box">
          <!-- Tên miền đã được đăng ký -->
          <div class="vnx-domain-alert" v-show="DomainShowResult == true && DomainAvailable == false && DomainVnError == false && DomainError == false " style="display: none;">
            <div class="vnx-domain-alert-content flex flex-row flex-nowrap gap-1 w-full">
              <div class="vnx-domain-alert-icon">
                <i class="fa-regular fa-circle-info text-[#F73131]"></i>
              </div>
              <div class="vnx-domain-alert-text">
                <p class="vnx-domain-alert-title"><span>Tên miền {{Domain}} đã được đăng ký.</span> Chúng tôi đã tìm ra tên miền khác có thể phù hợp với bạn!</p>
              </div>
            </div>
            <div class="vnx-domain-alert-btn">
              <a class="vnx-btn" class="vnx-btn" :href="'<?php echo $whois_url; ?>?domain=' + Domain" target="_blank" disabled>Xem Whois</a>
            </div>
          </div>
          <!-- Tên miền .vn 2 ký tự -->
          <div class="vnx-domain-alert" v-show="DomainAvailable == false && DomainVnError == true " style="display: none;">
            <div class="vnx-domain-alert-content flex flex-row flex-nowrap gap-1 w-full">
              <div class="vnx-domain-alert-icon">
                <i class="fa-regular fa-circle-info text-[#F73131]"></i>
              </div>
              <div class="vnx-domain-alert-text">
                <p class="vnx-domain-alert-title"><span>Tên miền {{Domain}} chưa thể đăng ký.</span> Chưa thể đăng ký tên miền .vn có 1,2 kí tự.</p>
              </div>
            </div>
          </div>
          <!-- Tên miền đặc biệt -->
          <div class="vnx-domain-alert" v-show="DomainPremium == true && DomainAvailable == true" style="display: none;">
            <div class="vnx-domain-alert-content flex flex-row flex-nowrap gap-1 w-full">
              <div class="vnx-domain-alert-icon">
                <i class="fa-regular fa-circle-info text-[#FF6F00]"></i>
              </div>
              <div class="vnx-domain-alert-text">
                <p class="vnx-domain-alert-title "><span class="text-[#FF6F00]">Tên miền {{DomainNameSld}}.{{DomainNameTld}} là tên miền đặc biệt.</span> Liên hệ chúng tôi để biết thêm chi tiết!</p>
              </div>
            </div>
            <div class="vnx-domain-alert-btn">
              <button class="vnx-btn btn_tawk text-[#FCFCFC] bg-[#007CFC] hover:bg-[#007CFC]/80">Liên hệ</button>
            </div>
          </div>
          <!-- Tên miền phù hợp -->
          <div class="item-available" v-show="(DomainAvailable == true && DomainShowResult == true && DomainPremium == false) || (IsPropose == true && DomainAvailable == false)" style="display: none;">
            <div class="item-top">
              <div class="vnx-domain-badge suggest">
                <span class="title" v-if="IsPropose == false">KẾT QUẢ PHÙ HỢP</span>
                <span class="title" v-if="IsPropose == true">TÊN MIỀN ĐỀ XUẤT</span>
              </div>
            </div>
            <div class="item-bottom">
              <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                <div class="item-bottom-left-title">
                  <p class="domain-name">{{DomainNameSld}}<span class="domain-tld">.{{DomainNameTld}}</span></p>
                </div>
              </div>
              <div class="item-bottom-right flex items-end">
                <div class="item-domain">
                  <div class="item-domain-tooltip" v-if="DomainTooltipTitle && DomainTooltipContent">
                    <div class="item-domain-tooltip-icon">
                      <i class="fa-light fa-circle-info"></i>
                      <p class="domain-tooltip-text-hidden">{{DomainTooltipContent}}</p>
                    </div>
                    <div class="item-domain-tooltip-content">
                      <p class="domain-tooltip-text">{{DomainTooltipTitle}}</p>
                    </div>
                  </div>
                  <div class="item-domain-price">
                    <div class="item-domain-original-price flex flex-row gap-2 items-center" v-if="DomainPriceReduction">
                      <p class="domain-price-text">{{DomainPriceReduction}}đ</p>
                      <div class="item-domain-tag">
                        <div class="item-domain-tag-icon flex items-center justify-center">
                          <i class="fa-light fa-tag text-white"></i>
                        </div>
                        <span class="domain-tag-text">-{{DomainPricePercent}}%</span>
                      </div>
                    </div>
                    <div class="item-domain-discount-price">
                      <p class="domain-price-text">{{DomainPrice}}đ /Năm đầu</p>
                    </div>
                  </div>
                </div>
                <div class="item-button vnx_addto_cart">
                  <div class="vnx_wrapper relative">
                    <button :data-sld="DomainNameSld" :data-tld="DomainNameTld" :data-domain="DomainName" class="add_domain_cart button_detail_mb vnx-btn-register" v-show="DomainIntCart == false" @click="clickAddToCart(DomainNameSld,DomainNameTld)">
                      <?php
                      if (!empty($icon_add_to_cart)) {
                        echo Bricks\Element::render_icon($icon_add_to_cart, ['vnx_icon']);
                      }
                      ?>
                    </button>
                    <button v-show="DomainIntCart == false" :data-sld="DomainNameSld" :data-tld="DomainNameTld" :data-domain="DomainName" class="add_domain_cart button_detail_dk vnx-btn-register rounded-lg" 
                      @click="clickAddToCart(DomainNameSld,DomainNameTld)">
                      <span>Chọn mua</span>
                      <i class="fa-light fa-plus-circle text-[#FCFCFC]"></i>
                    </button>
                  </div>
                  <div class="vnx_wrapper relative">
                    <button class="vnx_haveto_cart button_detail_mb" v-show="DomainIntCart == true" @click="clickRemoveDomainCartAvailable(DomainName)">
                      <?php
                      if (!empty($icon_add_to_cart_remove)) {
                        echo Bricks\Element::render_icon($icon_add_to_cart_remove, ['vnx_icon']);
                      }
                      ?>
                    </button>
                    <button :data-domain="DomainName" class="vnx_haveto_cart button_detail_dk vnx-btn-register rounded-lg" v-show="DomainIntCart == true" @click="clickRemoveDomainCartAvailable(DomainName)">
                      <span>Bỏ chọn</span>
                      <i class=" fa-light fa-minus-circle text-[#F73131]"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Loading -->
          <div class="vnx-result-loading" v-show="LoadingResult == true" style="display: none;">
            <div class="loading_small">
              <div class="loading_wrapper">
                <div class="loading_icon"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="vnx-domain-propose" v-show="domainComboHidden == false">
          <!-- Tên miền lựa chọn tiết kiệm -->
          <div class="item-available" v-show="Combination == true" style="display: none;">
            <div class="item-top">
              <div class="vnx-domain-badge choise">
                <span class="title">LỰA CHỌN TIẾT KIỆM</span>
              </div>
            </div>
            <div class="item-bottom">
              <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                <div class="item-bottom-left-title">
                  <p class="domain-name">{{DomainNameSld}}<span v-for="(domain, domainIndex) in CombinationResult.availableDomains" :key="domainIndex">
                      <span class="domain-tld">.{{domain.tld}}</span>
                      <span v-if="domainIndex < CombinationResult.availableDomains.length - 1"> + </span>
                    </span>
                  </p>
                </div>
              </div>
              <div class="item-bottom-right flex items-end">
                <div class="item-domain">
                  <div class="item-domain-tooltip" v-if="CombinationResult.contentcombo">
                    <div class="item-domain-tooltip-icon">
                      <i class="fa-light fa-circle-info"></i>
                      <p class="domain-tooltip-text-hidden">{{CombinationResult.contentcombo}}</p>
                    </div>
                    <div class="item-domain-tooltip-content" v-if="CombinationResult.titlecombo">
                      <p class="domain-tooltip-text">{{CombinationResult.titlecombo}}</p>
                    </div>
                  </div>
                  <div class="item-domain-price">
                    <div class="item-domain-original-price flex flex-row gap-2 items-center">
                      <p class="domain-price-text">{{Number(CombinationResult.defaultpricing).toLocaleString("vi-VN")}}đ</p>
                      <div class="item-domain-tag">
                        <div class="item-domain-tag-icon flex items-center justify-center">
                          <i class="fa-light fa-tag text-white"></i>
                        </div>
                        <span class="domain-tag-text">-{{CombinationResult.discount}}%</span>
                      </div>
                    </div>
                    <div class="item-domain-discount-price">
                      <p class="domain-price-text">{{Number(CombinationResult.combopricing).toLocaleString("vi-VN")}}đ /Năm đầu</p>
                    </div>
                  </div>
                </div>
                <div class="item-button vnx_addto_cart">
                  <div class="vnx_wrapper relative">
                    <button v-show="ComboIntCart == false && !CombinationResult.isDisabled" :data-sld="DomainNameSld" :data-tld="DomainNameTld" :data-combi-tld="CombinationResult.comboTLDs" @click="clickComboAddToCart(CombinationResult)"
                      class="add_domain_cart button_detail_mb vnx-btn-register">
                      <?php
                      if (!empty($icon_add_to_cart)) {
                        echo Bricks\Element::render_icon($icon_add_to_cart, ['vnx_icon']);
                      }
                      ?>
                    </button>
                    <button v-show="ComboIntCart == false && CombinationResult.isDisabled" :data-sld="DomainNameSld" :data-tld="DomainNameTld" :data-combi-tld="CombinationResult.comboTLDs"
                      class="add_domain_cart button_detail_mb vnx-btn-register disabled" disabled>
                      <?php
                      if (!empty($icon_add_to_cart)) {
                        echo Bricks\Element::render_icon($icon_add_to_cart, ['vnx_icon']);
                      }
                      ?>
                    </button>
                    <button v-show="CombinationResult.proseInCart == false && !CombinationResult.isDisabled" :data-sld="DomainNameSld" :data-tld="DomainNameTld" :data-combi-tld="CombinationResult.comboTLDs" @click="clickComboAddToCart(CombinationResult)"
                      class="add_domain_cart button_detail_dk vnx-btn-register rounded-lg">
                      <span v-if="LoadingButton == false">Chọn mua</span>
                      <i class="fa-light fa-plus-circle text-[#FCFCFC]" v-if="LoadingButton == false"></i>
                      <div class="loading_small" v-if="LoadingButton == true">
                        <div class="loading_wrapper">
                          <div class="loading_icon"></div>
                        </div>
                      </div>
                    </button>
                    <button v-show="CombinationResult.proseInCart == false && CombinationResult.isDisabled" :data-sld="DomainNameSld" :data-tld="DomainNameTld" :data-combi-tld="CombinationResult.comboTLDs"
                      class="add_domain_cart button_detail_dk vnx-btn-register rounded-lg disabled" disabled>
                      <span v-if="LoadingButton == false">Chọn mua</span>
                      <i class="fa-light fa-plus-circle text-[#FCFCFC]" v-if="LoadingButton == false"></i>
                      <div class="loading_small" v-if="LoadingButton == true">
                        <div class="loading_wrapper">
                          <div class="loading_icon"></div>
                        </div>
                      </div>
                    </button>
                  </div>
                  <div class="vnx_wrapper relative">
                    <button v-show="ComboIntCart == true" @click="clickRemoveComboFromCart(CombinationResult)" class="vnx_haveto_cart button_detail_mb vnx-btn-register">
                      <?php
                      if (!empty($icon_add_to_cart_remove)) {
                        echo Bricks\Element::render_icon($icon_add_to_cart_remove, ['vnx_icon']);
                      }
                      ?>
                    </button>
                    <button @click="clickRemoveComboFromCart(CombinationResult)" v-if="proseInCart == true" class="vnx_haveto_cart button_detail_dk vnx-btn-register rounded-lg">
                      <span v-if="LoadingButton == false">Bỏ chọn</span>
                      <i class="fa-light fa-minus-circle text-[#F73131]" v-if="LoadingButton == false"></i>
                      <div class="loading_small" v-if="LoadingButton == true">
                        <div class="loading_wrapper">
                          <div class="loading_icon"></div>
                        </div>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Loading -->
          <div class="vnx-result-loading" v-show="LoadingPropose == true" style="display: none;">
            <div class="loading_small">
              <div class="loading_wrapper">
                <div class="loading_icon"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="vnx-result-empty" v-show="ResultEmpty == true && IsLoading == false">
        <div class="flex flex-col items-center pb-10">
          <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
          <h5 class="mb-1 text-lg font-medium text-[#000000]">Bạn muốn tìm kiếm tên miền ?</h5>
          <span class="text-sm font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào ô tìm kiếm</span>
        </div>
      </div>
    </div>
    <div class="vnx-domain-list">
      <div class="vnx-domain-list-body">
        <div class="result-domain-tabs" v-show="ListStatus == true" style="display: none;">
          <?php
          $valid_tabs_count = 0;
          if (is_array($domain_category_data) && count($domain_category_data) > 0) {
            foreach ($domain_category_data as $key => $value) {
              if (!isset($value['name_tab']) || empty($value['name_tab'])) {
                  continue;
              }
              $tab_active = $key == 0 ? 'active' : '';
              $tab_parts = explode('|', $value['name_tab']);
              if (count($tab_parts) < 2) {
                  continue;
              }
              $tab_name = $tab_parts[0];
              $tab_icon = $tab_parts[1];
              if (empty($tab_name) || empty($tab_icon)) {
                  continue;
              }
              $valid_tabs_count++;
          ?>
              <button class="result-domain-tab " :data-sld="DomainNameSld" :data-domain="DomainName" data-tab="<?php echo $key; ?>" v-on:click="clickGetListTLDTab(DomainName, <?php echo $key; ?>,DomainNameTld)">
                <div class="result-domain-tab-icon flex items-center justify-center">
                  <i class="<?php echo htmlspecialchars($tab_icon); ?>"></i>
                </div>
                <span class="result-domain-tab-text"><?php echo htmlspecialchars($tab_name); ?></span>
              </button>
          <?php }
          } ?>
        </div>
        <!-- Loading tải combo -->
        <ul class="result-domain-list" v-show="ListStatus == true" style="display: none;">
          <!--combo domain -->
          <li class="result-domain-item" :data-tab="item.tab" v-if="ListComboDomain.length > 0 " v-for="(item, idx) in ListComboDomain.slice(0, getVisibleCount(ListComboDomain, 0))" :key="'combo-' + idx">
            <div class="item-available">
              <!-- tên miền khả dụng -->
              <div class="item-bottom">
                <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                  <div class="item-bottom-left-title">
                    <p class="domain-name">{{item.sld}}
                      <span v-for="(domain, domainIndex) in item.availableDomains" :key="domainIndex">
                        <span class="domain-tld">.{{domain.tld}}</span>
                        <span v-if="domainIndex < item.availableDomains.length - 1"> + </span>
                      </span>
                    </p>
                  </div>
                </div>
                <div class="item-bottom-right flex items-end">
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
                      <div class="item-domain-original-price flex flex-row gap-2 items-center" v-if="item.originalPrice">
                        <p class="domain-price-text">{{Number(item.originalPrice).toLocaleString("vi-VN")}}đ</p>
                        <div class="item-domain-tag">
                          <div class="item-domain-tag-icon flex items-center justify-center">
                            <i class="fa-light fa-tag text-white"></i>
                          </div>
                          <span class="domain-tag-text">-{{item.discount}}%</span>
                        </div>
                      </div>
                      <div class="item-domain-discount-price">
                        <p class="domain-price-text">{{Number(item.price).toLocaleString("vi-VN")}}đ /Năm đầu</p>
                      </div>
                    </div>
                  </div>
                  <div class="item-button vnx_addto_cart">
                    <div class="vnx_wrapper">
                      <button v-show="!isComboInCart(item) && !item.isDisabled" :data-sld="item.sld" :data-combi-tld="item.tld.replace(/\+/g, ', ')" :data-tld="item.tld" :data-domain="item.domainName"
                        class="add_domain_cart combo_domain button_detail_mb vnx-btn-register" @click="clickComboAddToCart(item)">
                        <?php
                        if (!empty($icon_add_to_cart)) {
                          echo Bricks\Element::render_icon($icon_add_to_cart, ['vnx_icon']);
                        }
                        ?>
                      </button>
                      <button v-show="!isComboInCart(item) && item.isDisabled" :data-sld="item.sld" :data-combi-tld="item.tld.replace(/\+/g, ', ')" :data-tld="item.tld" :data-domain="item.domainName"
                        class="add_domain_cart combo_domain button_detail_mb vnx-btn-register disabled" disabled>
                        <?php
                        if (!empty($icon_add_to_cart)) {
                          echo Bricks\Element::render_icon($icon_add_to_cart, ['vnx_icon']);
                        }
                        ?>
                      </button>
                    </div>
                    <div class="vnx_wrapper">
                      <button v-show="isComboInCart(item)" class="vnx_haveto_cart button_detail_mb" :data-sld="item.sld" :data-tld="item.tld" :data-domain="item.domainName" @click="clickRemoveComboFromCart(item)">
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
            </div>
          </li>
          <!-- tên miền có tld đã gọi -->
          <!-- tên miền có combo -->
          <li v-if="DomainNameTld != item.tld && DomainNameTldOld != item.tld  &&  item.originalPrice != null && item.price != null " v-for="(item, idx) in visibleDomains.slice(0, (visibleDomainCount - ComboDisplayCount))" :key="item.domainName + '-' + idx" class="result-domain-item" :data-tld="item.tld" :data-tab="item.tab">
            <div class="item-available">
              <!-- tên miền khả dụng -->
              <div class="item-bottom" v-show="item.status == 'available' && item.premium == false && item.domainVN == 'unavailable' || item.domainVN == 'available' && item.sld.length > 2 && item.status == 'available' && item.premium == false ">
                <div class="item-bottom-left flex items-center gap-1 flex-nowrap">
                  <div class="item-bottom-left-title">
                    <p class="domain-name">{{item.sld}}<span class="domain-tld">.{{item.tld}}</span></p>
                  </div>
                </div>
                <div class="item-bottom-right flex items-end">
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
                      <div class="item-domain-original-price flex flex-row gap-2 items-center" v-if="item.originalPrice">
                        <p class="domain-price-text">{{item.originalPrice}}đ</p>
                        <div class="item-domain-tag">
                          <div class="item-domain-tag-icon flex items-center justify-center">
                            <i class="fa-light fa-tag text-white"></i>
                          </div>
                          <span class="domain-tag-text">-{{item.discount}}%</span>
                        </div>
                      </div>
                      <div class="item-domain-discount-price">
                        <p class="domain-price-text">{{item.price}}đ /Năm đầu</p>
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
              <div class="item-bottom" v-if="item.premium == true && item.status == 'available'">
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
              <div class="item-bottom" v-if="item.status == 'unavailable'">
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
                      <a :href="'<?php echo $whois_url; ?>?domain=' + item.domainName" target="_blank" class="btn-view">
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
              <div class="item-bottom" v-if="item.domainVN == 'available' && item.sld.length < 3">
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
        <div class="vnx-result-error" v-show="ResultError == true && IsLoading == false" style="display: none;">
          <div class="flex flex-col items-center pb-10">
            <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="error" />
            <h5 class="mb-1 text-lg font-medium text-[#000000]">Có vấn đề đường truyền. Vui lòng thử lại!</h5>
            <button @click="retrySearch" type="button" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
              Thử lại
            </button>
          </div>
        </div>
        <div class="vnx-result-empty" v-show="(ListStatus == true && ListDomain.length == 0 && IsLoading == false && ArrayCache.length == 0 || TabEmpty == true) && ResultError == false" style="display: none;">
          <div class="flex flex-col items-center pb-10">
            <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
            <h5 class="mb-1 text-lg font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
            <span class="text-sm font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào ô tìm kiếm</span>
          </div>
        </div>
        <div class="result-domain-more" v-show="visibleDomains.length > (visibleDomainCount - ComboDisplayCount) && IsLoading == false" style="display: none;">
          <button class="result-domain-more-btn" @click="showMoreDomains">
            <span>Xem thêm tên miền khác</span>
            <i class="fa-light fa-angle-down"></i>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>