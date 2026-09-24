window.jQuery = window.$ = window.$_cart = jQuery;

var cart_cookie = 'vnx_domain_carts';
var cookie_age = $_cart('input#cart_cookie_age').val();

$_cart(document).on("click",".close_cart_popup",function(){
  $_cart('.vnx_cart_popup').addClass('hidden');
})

$_cart(document).on("click",".vnx_show_cart_popup",function(){
  $_cart('.vnx_cart_popup').removeClass('hidden');
})

$_cart(document).on("click", ".remove_domain_cart", function () {
  remove_domain_from_cart($_cart(this).data('domain'));
  check_button_on_remove_domain($_cart(this).data('domain'));
});
//Nút thêm giỏ hàng
$_cart(document).on("click", ".add_domain_cart", function () {
  add_domain_to_cart($_cart(this).data('domain'));
  $_cart(this).prop("disabled", true);
  $_cart(this).removeClass("bg-[#38A7FF] hover:bg-[#38A7FF]");
  $_cart(this).addClass("cursor-not-allowed lg:bg-[#81AFD3] bg-[#38A7FF] lg:opacity-100 opacity-50");
  $_cart(this).html(`<span class="hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
  viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block">
  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
</svg> Đã thêm</span>
<img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/added_cart_icon.svg" alt="added to cart icon">
`);
  if ($_cart('#type_form_search').length && $_cart('#type_form_search').val() == "whois_domain" && $_cart("#back_portal").val() == "yes") {
    window.location = $_cart('.vnx_link_portal').val();
  }
});

// nút đăng ký ngay
$_cart(document).on("click", ".add_domain_cart_whois", function () {
  add_domain_to_cart($_cart(this).data('domain'));
  $_cart(this).prop("disabled", true);
  $_cart(this).removeClass("bg-[#38A7FF] hover:bg-[#38A7FF]");
  $_cart(this).addClass("cursor-not-allowed lg:bg-[#81AFD3] bg-[#38A7FF] lg:opacity-100 opacity-50");
  if ($_cart('#type_form_search').length && $_cart('#type_form_search').val() == "whois_domain" && $_cart("#back_portal").val().toLowerCase() == "yes") {
    window.location = $_cart('.vnx_link_portal').val();
  }
});

//Chỉnh sửa chu kỳ
$_cart(document).on("change", ".price_domain_cart", function () {
  var domain = $_cart(this).data('domain');
  var update_register = getObjectFromCookie(cart_cookie, domain)
  update_register.register = $_cart(this).val();
  updateObjectInCookie(cart_cookie, domain, update_register, cookie_age);
  total_cart();
});
// Thêm giỏ hàng
function add_domain_to_cart(domain) {
  var obj = { domain: domain, register: '1', authen_code: '' };
  if (checkObjectInCookie(cart_cookie, obj) != true) {
    addObjectToCookie(cart_cookie, obj, cookie_age, cookie_age);
  }
  display_cart();
  return
}
//Hiển thị giỏ hàng
function display_cart() {
  var domain_cart_array = getDataFromCookie(cart_cookie);
  var domain_price_array = JSON.parse(sessionStorage.getItem('domain_price_Data'));
  if (domain_cart_array.length == 0) {
    deleteCookie(cart_cookie);
    $_cart('#cart_list').html('<li class="p-3 mt-2"><div class="p-8 cart_empty_icon"><img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/05/Vector-1.png" alt="Giỏ Hàng Rỗng"></div><p class="text-base text-center tracking-tight text-gray-900 font-medium">Đừng bỏ lỡ các tên miền của riêng bạn</p><p class="text-sm text-center tracking-tight text-gray-900 font-light px-5">Chọn tên miền và “Thêm vào giỏ hàng” để bắt đầu đăng ký</p></li>');
    $_cart('#cart_list_mobile').html('<li class="p-3 mt-2"><div class="p-8 cart_empty_icon"><img class="m-auto" src="https://vietnix.vn/wp-content/uploads/2023/05/Vector-1.png" alt="Giỏ Hàng Rỗng"></div><p class="text-base text-center tracking-tight text-gray-900 font-medium">Đừng bỏ lỡ các tên miền của riêng bạn</p><p class="text-sm text-center tracking-tight text-gray-900 font-light px-5">Chọn tên miền và “Thêm vào giỏ hàng” để bắt đầu đăng ký</p></li>');
    $_cart('.vnx_cart_footer').hide();
    $_cart('.vnx_cart_fixed').hide();
    $_cart('.vnx_cart_fixed').removeClass('fixed');
    $_cart('.popup_cart_footer').hide();
  }
  else {
    var domain_cart = '';
    $_cart.each(domain_cart_array, function (key, value) {
      var afterDot = value.domain.substring(value.domain.indexOf(".") + 1)
      var register = value.register
      var option = '';
      $_cart.each(domain_price_array[afterDot]['register'], function (key, value) {
        var money = value.split('.')[0].toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
        var selected = '';
        if (key == register) {
          selected = 'selected';
        }
        option += '<option value="' + key + '" ' + selected + ' data-price="' + value.split('.')[0] + '">' + money + ' đ / ' + key + ' Năm</option>'
      });
      domain_cart += '<li class="p-3 border-gray-200 rounded-lg shadow bg-[#EEF2F5] mt-2"><div class="flex items-center space-x-4"><div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900 truncate">' + value.domain.split(".")[0] + '<span class="cart_after_dot">.' + afterDot + '</p></div><div class="inline-flex items-center text-base font-semibold text-gray-900"><div class="vnx_trash_icon remove_domain_cart" data-domain="' + value.domain + '"></div></div></div><div class="flex items-center mt-1"><select class="price_domain_cart bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full py-1.5 px-1.5" data-domain="' + value.domain + '">' + option + '</select></div></li>';
    })
    $_cart('#cart_list').html(domain_cart);
    $_cart('#cart_list_mobile').html(domain_cart);
    $_cart('.vnx_cart_footer').show();
    $_cart('.vnx_cart_fixed').show();
    $_cart('.vnx_cart_fixed').addClass('fixed animate_show');
    $_cart('.vnx_cart_fixed').removeClass('hidden');
    $_cart('.popup_cart_footer').show();
  }
  total_cart();
  return
}

//Xoá domain khỏi giỏ hàng
function remove_domain_from_cart(domain) {
  removeObjectFromCookie(cart_cookie, domain, cookie_age)
  alert_popup_delete_domain()
  display_cart();
}

//Tổng tiền giỏ hàng
function total_cart() {
  var cookie_cart = getDataFromCookie(cart_cookie);
  var domain_price_array = JSON.parse(sessionStorage.getItem('domain_price_Data'));
  var total_price = 0;
  let total_count = 0;
  $_cart.each(cookie_cart, function(index, item) {
    total_count++
    var domain = item.domain
    var tld = domain.substring(domain.indexOf('.') + 1);
    var price = domain_price_array[tld].register[item.register];
    price = price.substring(0,price.indexOf('.'))
    total_price += parseInt(price);
  })
  // $_cart(".price_domain_cart option:selected").each(function () {
  //   var price = $_cart(this).data('price');
  //   total_price += parseInt(price);
  // })
  $_cart('.count_domain_cart').html(total_count+' sản phẩm');
  $_cart('.count_domain_cart_mobile').html(total_count);
  $_cart('#vnx_total_domain_cart').html(total_price.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.") + ' đ');
  $_cart('#vnx_total_domain_cart_mobile').html(total_price.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.") + ' đ');
  $_cart('.popup_price').html(total_price.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.") + ' đ');
}

//Thông báo xoá domain
function alert_popup_delete_domain() {
  var notification = $_cart('<div>', {
    class: 'vnx_noti fixed top-40 z-30',
  });
  $_cart('body').append(notification);
  $_cart('.vnx_noti').append('<div id="toast-success" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow" role="alert"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg"><svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg><span class="sr-only">Check icon</span></div><div class="ml-3 text-sm font-normal">Đã xoá tên miền khỏi giỏ hàng!</div></div>');

  $_cart('.vnx_noti').animate({ right: '0' });
  setTimeout(function () {
    notification.remove();
  }, 2000);
}

function check_button_on_remove_domain(domain) {
  var buttons = $_cart('.add_domain_cart')
  buttons.each(function () {
    var dataInfo = $_cart(this).data('domain');
    if (dataInfo == domain) {
      $_cart(this).prop("disabled", false);
      $_cart(this).addClass("bg-[#38A7FF] hover:bg-[#38A7FF]");
      $_cart(this).removeClass("cursor-not-allowed lg:bg-[#81AFD3] lg:opacity-100 opacity-50");
      $_cart(this).html(`
      <span class="hidden lg:block">Thêm vào giỏ hàng</span>
      <img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/add_cart_mobile.svg" alt="add to cart icon">
      `)
    }
  });
}

$_cart(document).ready(function() {
    if( $(".vnx_cart_footer").length > 0){
      var previousScrollTop = $_cart(window).scrollTop();
      $_cart(window).scroll(function() {
        var currentScrollTop = $(window).scrollTop();
        var cart_footer = $(".vnx_cart_footer").offset().top;
        if(previousScrollTop > currentScrollTop){
          // $_cart('.vnx_cart_fixed').addClass('hidden');
          // $_cart('.vnx_cart_fixed').removeClass('animate_show');
          if(cart_footer <= currentScrollTop + 700){
            $_cart('.vnx_cart_fixed').addClass('hidden');
            $_cart('.vnx_cart_fixed').removeClass('animate_show');
          }
          else{
            $_cart('.vnx_cart_fixed').removeClass('hidden');
            if (!$_cart('.vnx_cart_fixed').hasClass("animate_show")) {
              $_cart('.vnx_cart_fixed').addClass('animate_show');
            }
          }
        }
        else{
          if(cart_footer <= currentScrollTop + 700){
            $_cart('.vnx_cart_fixed').addClass('hidden');
            $_cart('.vnx_cart_fixed').removeClass('animate_show');
          }
          else{
            $_cart('.vnx_cart_fixed').removeClass('hidden');
            if (!$_cart('.vnx_cart_fixed').hasClass("animate_show")) {
              $_cart('.vnx_cart_fixed').addClass('animate_show');
            }
          }
        }
        previousScrollTop = currentScrollTop
      });
    }
});