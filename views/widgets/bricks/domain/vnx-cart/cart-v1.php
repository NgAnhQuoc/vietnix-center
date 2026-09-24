<?php
$settings = $data->settings;
$link_args = isset($settings['link_to_portal']) ? $settings['link_to_portal'] : [];
$link_url = isset($link_args['url']) ? $link_args['url'] : '#';
$nofollow = isset($link_args['rel']) && $link_args['rel'] ? ' rel="' . $link_args['rel'] . '"' : '';
$new_tab = isset($link_args['newTab']) ? ' target="_blank"' : '';
$sticky_cart_mobile = isset($settings['show_sticky_cart_mobile']) ? true : false;
$icon_cart_domain = isset($settings['icon_cart_domain']) ? $settings['icon_cart_domain'] : '';
$icon_cart_domain_buy = isset($settings['icon_cart_domain_buy']) ? $settings['icon_cart_domain_buy'] : '';
$icon_cart_domain_trash = isset($settings['icon_cart_domain_trash']) ? $settings['icon_cart_domain_trash'] : '';
?>

<input type="hidden" id="cart_cookie_age" value="<?= isset($settings['cart_cookie_age']) ? $settings['cart_cookie_age'] : '30'; ?>">
<div class="vnx-domain-cart relative">
  <div class="vnx-domain-cart-header" v-show="ShowCartDesktop == false && Emptycart == false" style="display: none;">
    <div class="vnx-wapper">
      <div class="cart-header-left">
        <p class="cart-header-left-title">{{totalDomains}} sản phẩm</p>
        <div class="cart-header-left-price-container">
          <span class="cart-price-total">Tổng tiền: {{formatPrice(totalPrice)}}đ</span>
          <span class="cart-price-vat">(Đã bao gồm VAT)</span>
        </div>
      </div>
      <div class="cart-header-right">
        <div class="cart-header-right-button">
          <button type="button" class="button-view relative" @click="clickShowCart()">
            <span class="cart-header-right-button-count">{{totalDomains}}</span>
            <div class="icon-cart-domain-view items-center justify-center">
              <?php
              if (isset($icon_cart_domain) && !empty($icon_cart_domain)) {
                echo Bricks\Element::render_icon($icon_cart_domain, ['vnx_icon']);
              }
              ?>
            </div>
            <span class="cart-header-right-button-text">Xem tên miền đã chọn</span>
          </button>
           <a href="<?php echo esc_attr($link_url); ?>" <?php echo $nofollow . $new_tab; ?>>
             <button type="button" class="vnx_domain_buy_btn">
               <div class="icon-cart-domain-buy items-center justify-center">
                 <?php
                 if (isset($icon_cart_domain_buy) && !empty($icon_cart_domain_buy)) {
                   echo Bricks\Element::render_icon($icon_cart_domain_buy, ['vnx_icon']);
                 }
                 ?>
               </div>
               <span>Tiếp tục</span>
             </button>
           </a>
        </div>
      </div>
    </div>
  </div>
  <div class="vnx-domain-cart-body" v-show="ShowCartDesktop == true" style="display: none;">
    <!-- Cart Overlay -->
    <div class="vnx-cart-overlay" @click="clickHiddenCart()">
      <div class="vnx-cart-modal" @click.stop>
        <div class="vnx-cart-modal-header">
          <div class="vnx-cart-modal-header-left w-full">
            <button class="vnx-cart-modal-close" type="button" @click="clickHiddenCart()">
              <i class="ti-close"></i>
            </button>
          </div>
          <div class="vnx-cart-modal-header-right w-full">
            <span class="vnx-cart-modal-title">Tên miền đã chọn</span>
          </div>
        </div>
        <div class="vnx-cart-modal-body">
          <!-- Cart Item -->
          <div class="vnx-cart-item"  v-for="(item, index) in DomainDatatCart" :key="index" :data-combo-id="item.comboId" :data-is-combo="item.isCombo" :data-register="item.register" :data-tld="item.tld" :data-sld="item.sld" :data-domain="item.domain">
            <div class="vnx-cart-item-info">
              <div class="cart-item-domain">
                <span class="vnx-cart-item-domain">{{item.sld}}.{{item.tld}}</span>
              </div>
              <div class="cart-item-remove">
                <button class="vnx-cart-item-remove" type="button" :data-domain="item.domain" :data-combo-id="item.comboId" :data-is-combo="item.isCombo" @click="deleteDomainCart(item.domain)">
                  <?php
                  if (isset($icon_cart_domain_trash) && !empty($icon_cart_domain_trash)) {
                    echo Bricks\Element::render_icon($icon_cart_domain_trash, ['vnx_icon']);
                  } else {
                    echo '<i class="ti-close"></i>';
                  }
                  ?>
                </button>
              </div>
            </div>
            <div class="vnx-cart-item-price">
              <div class="cart-option">
                <select class="vnx-cart-item-duration" :data-domain="item.domain" :data-tld="item.tld" :data-combo-id="item.comboId" :data-is-combo="item.isCombo" @change="updateSelectedYear($event, item.domain)" v-model="item.register">
                  <option v-for="(price, year) in getPriceByTLD(item.tld)" :key="year" :value="year">
                    {{year}} Năm
                  </option>
                </select>
              </div>
              <div class="cart-price">
                <span
                  class="vnx-cart-item-old-price"
                  v-if="getPriceDomainCart(item.tld, item.register) !== formatPrice(item.new_price) && getPriceDomainCart(item.tld, item.register) !== 0">
                  {{ getPriceDomainCart(item.tld, item.register) }}đ
                </span>
                 <span
                  class="vnx-cart-item-old-price"
                  v-if="getPriceDomainCart(item.tld, item.register) == 0 && item.isCombo == true">
                  {{ item.old_price }}đ
                </span>
                <span class="vnx-cart-item-new-price">{{formatPrice(item.new_price)}}đ</span>
              </div>
            </div>
            <div class="vnx-cart-tip">
              <span>
                <template v-if="getRenewPriceByTLD(item.tld) && getRenewPriceByTLD(item.tld)[item.register]">
                  Gia hạn tháng {{ currentMonth }} năm {{ currentYear + parseInt(item.register) }} với giá
                  {{ formatPrice(getRenewPriceByTLD(item.tld)[item.register]) }}đ
                </template>
              </span>
            </div>
          </div>
          <!-- End Cart Item -->
        </div>
        <div class="vnx-cart-modal-footer">
          <div class="vnx-cart-modal-summary">
            <div class="vnx-cart-modal-summary-top">
              <span class="vnx-cart-modal-summary-count">{{totalDomains}} sản phẩm</span>
              <span class="vnx-cart-modal-total">Tổng tiền: <span class="total-price">{{ formatPrice(totalPrice) }}đ</span></span>
            </div>
            <div class="vnx-cart-modal-vat">
              <span>Sản phẩm đã bao gồm VAT</span>
            </div>
          </div>
          <div class="vnx-cart-modal-footer-button">
            <a href="<?php echo esc_attr($link_url); ?>" <?php echo $nofollow . $new_tab; ?>>
              <button class="vnx-cart-modal-continue" type="button">
                <?php
                if (isset($icon_cart_domain_buy) && !empty($icon_cart_domain_buy)) {
                  echo Bricks\Element::render_icon($icon_cart_domain_buy, ['vnx_icon']);
                } else {
                  echo '<i class="ti-arrow-right"></i>';
                }
                ?>
                <span>Tiếp tục</span>
              </button>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>