<?php
if ( !isset( $data->settings ) ) {
  if ( current_user_can( 'update_core' ) )
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
  return;
}
$settings = $data->settings;
$link_args = isset( $settings[ 'link_to_portal' ] ) ? $settings[ 'link_to_portal' ] : [];
$link_url = isset( $link_args[ 'url' ] ) ? $link_args[ 'url' ] : '#';
$nofollow = isset( $link_args[ 'rel' ] ) && $link_args[ 'rel' ] ? ' rel="' . $link_args[ 'rel' ] . '"' : '';
$new_tab = isset( $link_args[ 'newTab' ] ) ? ' target="_blank"' : '';
$sticky_cart_mobile = isset( $settings[ 'show_sticky_cart_mobile' ] ) ? true : false;
?>
<input type="hidden" id="cart_cookie_age"
  value="<?= isset( $settings[ 'cart_cookie_age' ] ) ? $settings[ 'cart_cookie_age' ] : '30'; ?>">
<div class="grid grid-cols-3 gap-8 w-full bg-white">
  <div
    class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-4 h-fit lg:col-span-3 col-span-3 vnx_cart_domain">
    <div class="flex items-center justify-between mb-2">
      <p class="text-xl font-bold leading-none text-gray-900">Giỏ Hàng</p>
    </div>
    <div class="flow-root">
      <ul role="list" class="divide-y divide-gray-200" id="cart_list">
        <li class="p-3 mt-2">
          <div class="p-8 cart_empty_icon"><img class="m-auto"
              src="https://vietnix.vn/wp-content/uploads/2023/05/Vector-1.png" alt="Giỏ Hàng Rỗng"></div>
          <p class="text-base text-center tracking-tight text-gray-900 font-medium">Đừng bỏ lỡ các tên miền của riêng
            bạn</p>
          <p class="text-sm text-center tracking-tight text-gray-900 font-light px-5">Chọn tên miền và “Thêm
            vào giỏ hàng” để bắt đầu đăng ký</p>
        </li>
      </ul>
    </div>
    <div class="vnx_cart_footer">
      <div class="flex items-center justify-between mb-4 mt-4">
        <div class="grid grid-cols-3 gap-4 w-full items-center">
          <div class="col-span-1 col-start-1">
            <div class="text-xl font-bold leading-none text-gray-900">Tổng tiền</div>
          </div>
          <div class="col-span-2 col-end-4">
            <p class="text-[#F2994A] text-xl font-bold leading-none float-right" id="vnx_total_domain_cart">
              0 đ</p>
          </div>
        </div>
      </div>
      <div class="flex items-center mb-4 mt-4">
        <div class="grid grid-cols-1 w-full">
          <div class="col-span-1 col-start-1">
            <a href="<?php echo esc_attr( $link_url ); ?>" <?php echo $nofollow . $new_tab; ?>
              class="vnx_domain_buy_btn border border-gray-300 focus:outline-none font-medium rounded-lg px-5 py-2.5 w-full block text-center">Mua
              ngay</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if ( $sticky_cart_mobile ) : ?>
  <div
    class="lg:hidden bottom-0 left-0 right-0 z-10 w-full p-2 bg-white border-t border-gray-200 shadow flex items-center justify-between vnx_cart_fixed animate_show">
    <div class="flex text-sm text-gray-500 sm:text-center w-3/5 gap-x-3">
      <div class="flex px-2"><img class="vnx_show_cart_popup" src="https://vietnix.vn/wp-content/uploads/2023/08/Menu.svg"
          alt=""></div>
      <div class="flex flex-col">
        <div class="text-sm font-normal	text-[#4F4F4F] text-left count_domain_cart">0 sản phẩm</div>
        <div class="text-2xl font-bold text-[#FE9842] text-left" id="vnx_total_domain_cart_mobile">0 đ</div>
      </div>
    </div>
    <div class="flex flex-wrap items-center text-sm font-medium text-gray-500 mt-0 w-2/5 justify-end">
      <a href="<?php echo esc_attr( $link_url ); ?>" <?php echo $nofollow . $new_tab; ?>>
        <button type="button" class="text-white vnx-bg-orange font-medium rounded-lg text-sm p-3 w-36">Mua
          ngay</button>
      </a>
    </div>
  </div>

  <div
    class="block w-full pb-7 pt-16 px-6 bg-white border border-gray-200 shadow fixed top-0 left-0 h-screen z-20 lg:hidden vnx_cart_popup hidden">
    <div class="flex flex-col gap-y-8 h-full overflow-y-scroll hide_scroll_bar justify-between">
      <div class="flex mt-3">
        <div class="w-6/12">
          <div class="relative w-fit">
            <img src="https://vietnix.vn/wp-content/uploads/2023/08/xe.svg" alt="cart icon">
            <div
              class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-[#F14C2E] border-2 border-white rounded-full -top-2 -right-2 count_domain_cart_mobile">
              0</div>
          </div>
        </div>
        <div class="w-6/12 flex items-center justify-end">
          <img class="close_cart_popup" src="https://vietnix.vn/wp-content/uploads/2023/08/Quit.svg" alt="close cart">
        </div>
      </div>
      <div class="flex flex-col h-full gap-y-3">
        <p class="text-xl font-bold leading-none text-[#4F4F4F]">Đăng ký tên miền</p>
        <div class="flow-root overflow-y-scroll" style="max-height: 75vh">
          <ul role="list" class="divide-y divide-gray-200 flex flex-col gap-y-3" id="cart_list_mobile">
          </ul>
        </div>
      </div>
      <div class="content-end popup_cart_footer">
        <div class="flex flex-col gap-y-3">
          <div class="w-full flex flex-row justify-between">
            <div class="text-left uppercase text-lg font-normal text-[#333]">Tổng cộng</div>
            <div class="text-right popup_price text-[#FE9842] text-lg font-bold">0 đ</div>
          </div>
          <div class="w-full">
            <a href="<?php echo esc_attr( $link_url ); ?>" <?php echo $nofollow . $new_tab; ?>>
              <button type="button" class="text-white vnx-bg-orange font-medium rounded-lg text-sm p-3 w-full">Thanh
                Toán</button>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<style>
  span.cart_after_dot {
    color: #FE9842;
  }

  .animate_show {
    animation: show_cart_sticky 0.5s ease-in-out;
  }

  .vnx_cart_fixed {
    height: fit-content;
    z-index: 5;
  }

  .vnx_noti {
    right: -250px;
  }

  .hide_scroll_bar::-webkit-scrollbar {
    display: none;
  }

  .hide_scroll_bar {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }

  .vnx_cart_footer {
    display: none;
  }

  .vnx-bg-orange {
    background: linear-gradient(97.32deg, #FFBD2A 5%, #FD7659 170.21%);
  }

  .vnx_domain_buy_btn {
    background: linear-gradient(97.32deg, #FFBD2A 5%, #FD7659 170.21%);
    font-weight: 600;
    font-size: 20px;
    line-height: 23px;
    color: #fff
  }

  .vnx_trash_icon:before {
    content: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAARCAYAAADtyJ2fAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAADtSURBVHgB5VPRDYIwEL1DBnADcQOcQEdwBNgAPo2aaJT4WTYoIzgCTqAbyAgsoGcPqNZCUL99SdPj9b0rvV4RLCRC+neAucm5ANkiDguTQ/PjIKR3QzyrcGjlKwdEE9PsmKvKJLEWjVdRgDw41mtvOyZpdiUAD36ASl64QLSFOltORKdeA+JUTbPKw9inGe2E3MAHsIa1YJ+Rwb9eJRMy4mFyJlyb0Oclo7JdNWjt+C3+wdiqKhKFTcajzXUZSwdxxMEyDrNmvmiR5lijrqZ8GVULEaJQXRFAD6iS1u32fFaqnfgN+tCPfB2HOQcPLXxeFKfXI7oAAAAASUVORK5CYII=');
    width: 20px;
    height: 20px;
    overflow: hidden;
    cursor: pointer;
  }


  @media only screen and (max-width: 690px) {
    .domain_search_req {
      max-width: 85%;
    }
  }

  @media only screen and (max-width: 410px) {
    .price_text {
      width: 70%;
    }
  }

  @media only screen and (min-width: 1024px) {
    .query-domain-res {
      width: 35%;
    }

    .domain_search_req {
      max-width: 80%;
    }
  }

  @keyframes show_cart_sticky {
    0% {
      margin-bottom: -60px;
    }

    100% {
      margin-bottom: 0px;
    }
  }

  @keyframes hide_cart_sticky {
    0% {
      margin-bottom: -60px;
    }

    100% {
      margin-bottom: 0px;
    }
  }
</style>