<?php
if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
  return;
}
$settings = $data->settings;
$link_args = isset($settings['link_to_portal']) ? $settings['link_to_portal'] : [];
$link_url = isset($link_args['url']) ? $link_args['url'] : '#';
$nofollow = isset($link_args['rel']) && $link_args['rel'] ? ' rel="' . $link_args['rel'] . '"' : '';
$new_tab = isset($link_args['newTab']) ? ' target="_blank"' : '';
$sticky_cart_mobile = isset($settings['show_sticky_cart_mobile']) ? true : false;
?>
<input type="hidden" id="cart_cookie_age" value="<?= isset($settings['cart_cookie_age']) ? $settings['cart_cookie_age'] : '30'; ?>">
<div class="grid grid-cols-3 gap-8 w-full bg-white">
  <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-4 h-fit lg:col-span-3 col-span-3 vnx_cart_domain">
    <div class="flex items-center justify-between mb-2">
      <p class="text-xl font-bold leading-none text-gray-900">Giỏ Hàng</p>
    </div>
    <div class="flow-root">
      <!-- Cart empty -->
      <div class="p-3 mt-2" v-show="Emptycart == true" style="display: none;">
        <div class="p-8 cart_empty_icon"><img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/05/Vector-1.png" alt="Giỏ Hàng Rỗng"></div>
        <p class="text-base text-center tracking-tight text-gray-900 font-medium">Đừng bỏ lỡ các tên miền của riêng
          bạn</p>
        <p class="text-sm text-center tracking-tight text-gray-900 font-light px-5">Chọn tên miền và “Thêm
          vào giỏ hàng” để bắt đầu đăng ký</p>
      </div>
      <ul role="list" class="divide-y divide-gray-200" id="cart_list" v-show="Emptycart == false" style="display: none;">
        <!-- List domain -->
        <li class="p-3 border-gray-200 rounded-lg shadow bg-[#EEF2F5] mt-2" v-for="(item, index) in DomainDatatCart" :key="index">
          <div class="flex items-center space-x-4">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{item.sld}}<span class="cart_after_dot">.{{item.tld}}</span></p>
            </div>
            <div class="inline-flex items-center text-base font-semibold text-gray-900">
              <div class="vnx_trash_icon remove_domain_cart" @click="deleteDomainCart(item.domain)" :data-domain="item.domain"></div>
            </div>
          </div>
          <div class="flex items-center mt-1">
            <select class="price_domain_cart bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full py-1.5 px-1.5" :data-domain="item.domain" :data-tld="item.tld" @change="updateSelectedYear($event, item.domain)"
              v-model="item.register">
              <option v-for="(price, year) in getPriceByTLD(item.tld)" :key="year" :value="year" :data-price="parseInt(price)[year]"> {{ formatPrice(price) }} đ / {{ year }} Năm</option>
            </select>
          </div>
        </li>
      </ul>
      <div class="vnx_cart_footer" v-show="DomainCartFooter == true" style="display: none;">
        <div class="flex items-center justify-between mb-4 mt-4">
          <div class="grid grid-cols-3 gap-4 w-full items-center">
            <div class="col-span-1 col-start-1">
              <div class="text-xl font-bold leading-none text-gray-900">Tổng tiền</div>
            </div>
            <div class="col-span-2 col-end-4">
              <p class="text-[#F2994A] text-xl font-bold leading-none float-right" id="vnx_total_domain_cart">
                {{ formatPrice(totalPrice) }} đ
              </p>
            </div>
          </div>
        </div>
        <div class="flex items-center mb-4 mt-4">
          <div class="grid grid-cols-1 w-full">
            <div class="col-span-1 col-start-1">
              <a href="<?php echo esc_attr($link_url); ?>" <?php echo $nofollow . $new_tab; ?> class="vnx_domain_buy_btn border border-gray-300 focus:outline-none font-medium rounded-lg px-5 py-2.5 w-full block text-center">Mua
                ngay</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if ($sticky_cart_mobile): ?>
  <div class="lg:hidden bottom-0 left-0 right-0 z-10 w-full p-2 bg-white border-t border-gray-200 shadow flex items-center justify-between vnx_cart_fixed animate_show">
    <div class="flex text-sm text-gray-500 sm:text-center w-3/5 gap-x-3">
      <div class="flex px-2 vnx_button_show_cart_popup" @click="clickShowCartMobi()"><img class="vnx_show_cart_popup" src="https://vietnix.vn/wp-content/uploads/2023/08/Menu.svg" alt=""></div>
      <div class="flex flex-col">
        <div class="text-sm font-normal	text-[#4F4F4F] text-left count_domain_cart">{{totalDomains}} sản phẩm</div>
        <div class="text-2xl font-bold text-[#FE9842] text-left" id="vnx_total_domain_cart_mobile">{{ formatPrice(totalPrice) }} đ</div>
      </div>
    </div>
    <div class="flex flex-wrap items-center text-sm font-medium text-gray-500 mt-0 w-2/5 justify-end">
      <a href="<?php echo esc_attr($link_url); ?>" <?php echo $nofollow . $new_tab; ?>>
        <button type="button" class="text-white vnx-bg-orange font-medium rounded-lg text-sm p-3 w-36">Mua
          ngay</button>
      </a>
    </div>
  </div>

  <div class=" w-full pb-7 px-6 bg-white border border-gray-200 shadow fixed top-0 left-0 h-screen z-20 vnx_cart_popup " style="display: none;" v-show="ShowCartMobile == true">
    <div class="flex flex-col gap-y-8 h-full overflow-y-scroll hide_scroll_bar justify-between">
      <div class="flex mt-3">
        <div class="w-6/12">
          <div class="relative w-fit">
            <img src="https://vietnix.vn/wp-content/uploads/2023/08/xe.svg" alt="cart icon">
            <div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-[#F14C2E] border-2 border-white rounded-full -top-2 -right-2 count_domain_cart_mobile">
              {{totalDomains}}</div>
          </div>
        </div>
        <div class="w-6/12 flex items-center justify-end vnx_button_close_cart_popup" @click="clickHiddenCartMobi()">
          <img class="close_cart_popup" src="https://vietnix.vn/wp-content/uploads/2023/08/Quit.svg" alt="close cart">
        </div>
      </div>
      <div class="flex flex-col h-full gap-y-3">
        <p class="text-xl font-bold leading-none text-[#4F4F4F]">Đăng ký tên miền</p>
        <div class="flow-root overflow-y-auto" style="max-height: 75vh">
          <ul role="list" class="divide-y divide-gray-200 flex flex-col gap-y-3" id="cart_list_mobile">
            <li class="p-3 mt-2" v-show="DomainCartFooter == false" style="display: none;">
              <div class="p-8 cart_empty_icon"><img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/05/Vector-1.png" alt="Giỏ Hàng Rỗng"></div>
              <p class="text-base text-center tracking-tight text-gray-900 font-medium">Đừng bỏ lỡ các tên miền của riêng
                bạn</p>
              <p class="text-sm text-center tracking-tight text-gray-900 font-light px-5">Chọn tên miền và “Thêm
                vào giỏ hàng” để bắt đầu đăng ký</p>
            </li>
            <!-- List domain -->
            <li class="p-3 border-gray-200 rounded-lg shadow bg-[#EEF2F5] mt-2" v-for="(item, index) in DomainDatatCart" :key="index">
              <div class="flex items-center space-x-4">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{item.sld}}<span class="cart_after_dot">.{{item.tld}}</span></p>
                </div>
                <div class="inline-flex items-center text-base font-semibold text-gray-900">
                  <div class="vnx_trash_icon remove_domain_cart" @click="deleteDomainCart(item.domain)" :data-domain="item.domain"></div>
                </div>
              </div>
              <div class="flex items-center mt-1">
                <select class="price_domain_cart bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full py-1.5 px-1.5" :data-domain="item.domain" :data-tld="item.tld" @change="updateSelectedYear($event, item.domain)"
                  v-model="item.register">
                  <option v-for="(price, year) in getPriceByTLD(item.tld)" :key="year" :value="year" :data-price="parseInt(price)[year]"> {{ formatPrice(price) }} đ / {{ year }} Năm</option>
                </select>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="content-end popup_cart_footer">
        <div class="flex flex-col gap-y-3">
          <div class="w-full flex flex-row justify-between">
            <div class="text-left uppercase text-lg font-normal text-[#333]">Tổng cộng</div>
            <div class="text-right popup_price text-[#FE9842] text-lg font-bold">{{ formatPrice(totalPrice) }} đ</div>
          </div>
          <div class="w-full">
            <a href="<?php echo esc_attr($link_url); ?>" <?php echo $nofollow . $new_tab; ?>>
              <button type="button" class="text-white vnx-bg-orange font-medium rounded-lg text-sm p-3 w-full">Thanh
                Toán</button>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>