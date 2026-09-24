window.jQuery = window.$ = jQuery;
$(document).ready(function () {
  //js splide slider
  //bảng giá hosting ver 1
  $('.hosting_price_v1').each(function () {
    var id_sec = $(this).data('id-splide');
    var splide_options = $(this).data('splide');
    new Splide('#brxe-' + id_sec + ' .splide.vnx-splide-service-price',
      splide_options
    ).mount();
    VnxHostingTable();
    VnxHoverTooltip();
  });
  //- Danh sách bảng giá
  $('.list_hosting_price_v1').each(function () {
    var id_sec = $(this).data('id-splide');
    var splide_options = $(this).data('splide');
    new Splide('#brxe-' + id_sec + ' .splide.vnx-splide-service-price',
      splide_options
    ).mount();
    VnxHoverTooltip();
    VnxButtonRegister();
  });
  // Bảng giá Firewall 1
  if ($('.vnx_element_firewall').length) {
    VnxtableFirewall();
  }
  // Bảng giá maxspeed 1
  if ($('.vnx_element_maxspeed').length) {
    var id_section = $('.vnx_element_maxspeed').attr('id');
    var splide_options = $('.vnx_div_data_maxspeed').data('splide');
    new Splide('#' + id_section + ' #vnx_body_slider_maxspeed',
      splide_options
    ).mount();
    VnxMaxSpeed();
    VnxHoverTooltip();
  }
  $('.vnx-cyc-price').click(function () {
    var parentElement = $(this).closest('.brxe-vnx-service-price');
    var id_closet = '#' + parentElement.attr('id');
    $(id_closet + ' .vnx-cyc-price').removeClass('tab-active');
    $(this).addClass('tab-active');
  });

  // Bảng giá so sánh 1 
  if ($('.compare_hosting_v1').length) {
    vnxHostingCompare();
  }
  // Bảng giá so sánh 2
  if ($('.compare_hosting_v2').length) {
    vnxHostingCompareV2();
  }
  // Danh sách dịch vụ
  if ($('.multiple_service_hosting').length) {
    VnxMultipleService();
  }

  if ($('.compare_ssl').length) {
    vnxHostingCompareSSL();
  }
  if ($('.table_compare_v1').length) {
    vnxHostingCompareTable();
  }


  if ($(".compare_server").length) {
    vnxHostingCompareServer();
  }

  VnxScrollSplide();
});
function VnxHostingTable() {
  $('.vnx-cyc-price').each(function () {
    var parentElement = $(this).closest('.hosting_price_v1');
    var id_closet = '#' + parentElement.attr('id');
    if ($(this).hasClass('tab-active')) {
      var dataTarget = $(this).data('target');
      var dataDiscount = $(this).data('discount');
      var dataPeriod = $(this).data('period');
      $(id_closet + ' .splide__slide').each(function () {
        $(this).find('.vnx-price-cyc').each(function () {
          var dataPrice = $(this).data('tab');
          if (dataTarget === dataPrice) {
            var cost = $(this).data('cost');
            var regular_price = $(this).data('price');
            var year_price = $(this).data('price-year');
            var dataProduct = $(this).data('product-name');
            var dataProductCT = $(this).data('product-category');
            $(this).closest('.card-content-price').find('.vnx-price-reduced').text(regular_price);
            if (year_price == null || year_price === "") {
              $(this).closest('.card-content-price').find('.box-vnx-price-reduced-year').addClass('hidden');
            } else {
              $(this).closest('.card-content-price').find('.vnx-price-reduced-year').text(year_price);
            }
            if (cost == null || cost === "") {
              $(this).closest('.card-content-price').find('.vnx-tab.vnx-cost').addClass('hidden');
            } else {
              $(this).closest('.card-content-price').find('.vnx-tab.vnx-cost').removeClass('hidden');
              $(this).closest('.card-content-price').find('.vnx-price-cost').text(cost);
            }
            $(this).closest('.card-content-price').find('.el-custom-tab-discount').text(dataDiscount);
            //nút đăng ký ngay
            $(this).closest('.card-content-price').find('.el-custom-text-price-year span').text(dataPeriod);
            $(this).closest('.card-content-price').find('.vnx-price-cyc-year').text(dataPeriod);
            $(this).closest('.card-content-price').find('.url-register').attr("data-period", dataPeriod);
            $(this).closest('.card-content-price').find('.url-register').attr("data-price", year_price);
            // $(this).closest('.card-content-price').find('.url-register').attr("data-price-year", year_price);
            $(this).closest('.card-content-price').find('.url-register').attr("data-product-name", dataProduct);
            $(this).closest('.card-content-price').find('.url-register').attr("data-product-category", dataProductCT);
          }
        });
        $(this).find('.vnx-price-url').each(function () {
          var dataPrice = $(this).data('tab');
          if (dataTarget === dataPrice) {
            var dataUrl = $(this).data('url');
            $(this).closest('.card-content-price').find('.url-register').attr("href", dataUrl);
          }
        });
      });
    }
  });
  $('.vnx-cyc-price').click(function () {
    let parentElement = $(this).closest('.brxe-vnx-service-price');
    let id_closet = '#' + parentElement.attr('id');
    let dataTarget = $(this).data('target');
    let dataDiscount = $(this).data('discount');
    let dataPeriod = $(this).data('period');
    $(id_closet + ' .splide__slide').each(function () {
      $(this).find('.vnx-price-cyc').each(function () {
        var data_price = $(this).data('tab');
        if (dataTarget == data_price) {
          var cost = $(this).data('cost');
          var regular_price = $(this).data('price');
          var year_price = $(this).data('price-year');
          var dataProduct = $(this).data('product-name');
          var dataProductCT = $(this).data('product-category');
          $(this).closest('.card-content-price').find('.vnx-price-reduced').text(regular_price);
          if (year_price == null || year_price === "") {
            $(this).closest('.card-content-price').find('.box-vnx-price-reduced-year').addClass('hidden');
          } else {
            $(this).closest('.card-content-price').find('.vnx-price-reduced-year').text(year_price);
          }
          if (cost == null || cost === "") {
            $(this).closest('.card-content-price').find('.vnx-tab.vnx-cost').addClass('hidden');
          } else {
            $(this).closest('.card-content-price').find('.vnx-tab.vnx-cost').removeClass('hidden');
            $(this).closest('.card-content-price').find('.vnx-price-cost').text(cost);
          }
          $(this).closest('.card-content-price').find('.el-custom-tab-discount').text(dataDiscount);
          //nút đăng ký ngay
          $(this).closest('.card-content-price').find('.el-custom-text-price-year span').text(dataPeriod);
          $(this).closest('.card-content-price').find('.vnx-price-cyc-year').text(dataPeriod);
          $(this).closest('.card-content-price').find('.url-register').attr("data-period", dataPeriod);
          $(this).closest('.card-content-price').find('.url-register').attr("data-price", year_price);
          // $(this).closest('.card-content-price').find('.url-register').attr("data-price-year", year_price);
          $(this).closest('.card-content-price').find('.url-register').attr("data-product-name", dataProduct);
          $(this).closest('.card-content-price').find('.url-register').attr("data-product-category", dataProductCT);
        }
      });
      $(this).find('.vnx-price-url').each(function () {
        var dataPrice = $(this).data('tab');
        if (dataTarget === dataPrice) {
          var dataUrl = $(this).data('url');
          $(this).closest('.card-content-price').find('.url-register').attr("href", dataUrl);
        }
      });
    });
  });
  //click load more
  $('.hosting_price_v1 .vnx-button-more').click(function () {
    let parentSplide = $(this).closest('.splide__list');
    parentSplide.find('.card-content-infor').each(function () {
      if ($(this).hasClass('collapsed')) {
        $(this).removeClass('collapsed').addClass('expanded'); // Thêm lớp expanded để mở rộng
        $(this).css('overflow', 'unset');
      }
    });
    parentSplide.find('.vnx-button-more').addClass('hidden');
    parentSplide.find('.vnx-button-close').removeClass('hidden');
  });

  $('.hosting_price_v1 .vnx-button-close').click(function () {
    let parentSplide = $(this).closest('.splide__list');
    parentSplide.find('.card-content-infor').each(function () {
      if (!$(this).hasClass('collapsed')) {
        $(this).removeClass('expanded').addClass('collapsed'); // Thêm lớp collapsed để thu gọn
        $(this).css('overflow', 'hidden');
      }
    });
    parentSplide.find('.vnx-button-more').removeClass('hidden');
    parentSplide.find('.vnx-button-close').addClass('hidden');
    var targetCard = $(this).closest('.card-price');
    $('html, body').animate({
      scrollTop: targetCard.offset().top - 100
    }, 600);
  });
}
function VnxMaxSpeed() {
  $('.vnx-cyc-price').each(function () {
    var parentElement = $(this).closest('.vnx_element_maxspeed');
    var id_closet = '#' + parentElement.attr('id');
    if ($(this).hasClass('tab-active')) {
      var dataTarget = $(this).data('target');
      var dataPeriod = $(this).data('period');
      $(id_closet + ' .box-card-hosting').each(function () {
        $(this).find('.vnx-price-cyc').each(function () {
          var dataPrice = $(this).data('tab');
          if (dataTarget === dataPrice) {
            var cost = $(this).data('cost');
            var discount = $(this).data('discount');
            var discount_label = $(this).data('discount-label');
            var year_price = $(this).data('price-year');
            var total = $(this).data('total');
            var category = $(this).data('product-category');
            $(this).closest('.box-card-hosting').find('.price-reduced-text').text(cost);
            $(this).closest('.box-card-hosting').find('.vnx-price-popular').text(year_price);
            $(this).closest('.box-card-hosting').find('.vnx-price-reduced-year').text(total);
            $(this).closest('.box-card-hosting').find('.vnx-price-cyc-year').text(dataPeriod);
            $(this).closest('.box-card-hosting').find('.vnx-button-register').attr("data-product-category", category);
            if (discount == null) {
              $(this).closest('.box-card-hosting').find('.price-reduced-discount').addClass('hidden');
            }
            else {
              $(this).closest('.box-card-hosting').find('.price-reduced-discount img').attr('src', discount);
            }
            if (discount_label == "" || discount_label == null) {
              $(this).closest('.box-card-hosting').find('.discount-label').addClass('hidden');
            } else {
              $(this).closest('.box-card-hosting').find('.discount-label').removeClass('hidden');
              $(this).closest('.box-card-hosting').find('.discount-label').attr('src', discount_label);
            }
            if (year_price == null || year_price === "") {
              $(this).closest('.box-card-hosting').find('.card-price-reduced').addClass('hidden');
              $(this).closest('.box-card-hosting').find('.vnx-price-popular').text(cost);
            } else {
              $(this).closest('.box-card-hosting').find('.card-price-reduced').removeClass('hidden');
            }
            if (total == null || total === "") {
              $(this).closest('.card-content-price').find('.box-vnx-price-reduced-year').addClass('hidden');
            } else {
              $(this).closest('.card-content-price').find('.box-vnx-price-reduced-year').removeClass('hidden');
            }
          }
        });
      });
      $(id_closet + ' .url-register').each(function () {
        var dataBox = $(this).data('box');
        var dataPrice = '';
        var dataCyc = dataPeriod;
        var dataName = '';
        $('.maxspeed_hosting .box-card-hosting').each(function () {
          var idBox = $(this).data('box');
          if (idBox == dataBox) {
            dataPrice = $(this).find('.vnx-price-reduced-year').text();
            if (dataPrice == null || dataPrice === "") {
              dataPrice = $(this).find('.price.vnx-price-popular').text();
            }
            dataName = $(this).find('.card-content-price .title-card').text();
          }
        });
        $(this).find('.vnx-price-cyc-url').each(function () {
          var dataUrl = $(this).data('tab');
          if (dataTarget === dataUrl) {
            var dataUrlValue = $(this).data('url');
            $(this).closest('.url-register').find('.vnx-button-register').attr("href", dataUrlValue);
            $(this).closest('.url-register').find('.vnx-button-register').attr("data-price", dataPrice);
            $(this).closest('.url-register').find('.vnx-button-register').attr("data-period", dataCyc);
            $(this).closest('.url-register').find('.vnx-button-register').attr("data-product-name", dataName);
          }
        });
      });
    }
  });
  //click
  $('.vnx-cyc-price').click(function () {
    let parentElement = $(this).closest('.vnx_element_maxspeed');
    let id_closet = '#' + parentElement.attr('id');
    let dataTarget = $(this).data('target');
    let dataDiscount = $(this).data('discount');
    let dataPeriod = $(this).data('period');
    var dataPerCat = '';
    parentElement.find('.vnx-cyc-price').removeClass('vnx-tab-active');
    // Thêm 'tab-active' vào các phần tử có data-target giống với dataTarget
    parentElement.find('.vnx-cyc-price').each(function () {
      if ($(this).data('target') === dataTarget) {
        console.log($(this).data('target'))
        $(this).addClass('vnx-tab-active');
      }
    });
    $(id_closet + ' .box-card-hosting').each(function () {
      $(this).find('.vnx-price-cyc').each(function () {
        var data_price = $(this).data('tab');
        dataPerCat = $(this).data('product-category');
        if (dataTarget == data_price) {
          var cost = $(this).data('cost');
          var discount = $(this).data('discount');
          var discount_label = $(this).data('discount-label');
          var year_price = $(this).data('price-year');
          var total = $(this).data('total');
          var category = $(this).data('product-category');
          $(this).closest('.box-card-hosting').find('.price-reduced-text').text(cost);
          $(this).closest('.box-card-hosting').find('.vnx-price-popular').text(year_price);
          $(this).closest('.box-card-hosting').find('.vnx-price-reduced-year').text(total);
          $(this).closest('.box-card-hosting').find('.vnx-price-cyc-year').text(dataPeriod);
          $(this).closest('.box-card-hosting').find('.vnx-button-register').attr("data-product-category", category);
          if (discount == null) {
            $(this).closest('.box-card-hosting').find('.price-reduced-discount').addClass('hidden');
          }
          else {
            $(this).closest('.box-card-hosting').find('.price-reduced-discount img').attr('src', discount);
          }
          if (discount_label == "" || discount_label == null) {
            $(this).closest('.box-card-hosting').find('.discount-label').addClass('hidden');
          } else {
            $(this).closest('.box-card-hosting').find('.discount-label').removeClass('hidden');
            $(this).closest('.box-card-hosting').find('.discount-label').attr('src', discount_label);
          }
          if (year_price == null || year_price === "") {
            $(this).closest('.box-card-hosting').find('.card-price-reduced').addClass('hidden');
            $(this).closest('.box-card-hosting').find('.vnx-price-popular').text(cost);
          } else {
            $(this).closest('.box-card-hosting').find('.card-price-reduced').removeClass('hidden');
          }
          if (total == null || total === "") {
            $(this).closest('.card-content-price').find('.box-vnx-price-reduced-year').addClass('hidden');
          } else {
            $(this).closest('.card-content-price').find('.box-vnx-price-reduced-year').removeClass('hidden');
          }
        }
      });
    });
    $(id_closet + ' .url-register').each(function () {
      var dataBox = $(this).data('box');
      var dataPrice = '';
      var dataCyc = dataPeriod;
      var dataName = '';
      $('.maxspeed_hosting .box-card-hosting').each(function () {
        var idBox = $(this).data('box');
        if (idBox == dataBox) {
          dataPrice = $(this).find('.vnx-price-reduced-year').text();
          if (dataPrice == null || dataPrice === "") {
            dataPrice = $(this).find('.price.vnx-price-popular').text();
          }
          dataName = $(this).find('.card-content-price .title-card').text();
        }
      });
      $(this).find('.vnx-price-cyc-url').each(function () {
        var dataUrl = $(this).data('tab');
        if (dataTarget === dataUrl) {
          var dataUrlValue = $(this).data('url');
          $(this).closest('.url-register').find('.vnx-button-register').attr("href", dataUrlValue);
          $(this).closest('.url-register').find('.vnx-button-register').attr("data-price", dataPrice);
          $(this).closest('.url-register').find('.vnx-button-register').attr("data-period", dataCyc);
          $(this).closest('.url-register').find('.vnx-button-register').attr("data-product-name", dataName);
          // $(this).closest('.url-register').find('.vnx-button-register').attr("data-product-category", dataCat);
        }
      });
    });
  });
}
function VnxtableFirewall() {
  $('.vnx-cyc-price').each(function () {
    var parentElement = $(this).closest('.vnx_element_firewall');
    var id_closet = '#' + parentElement.attr('id');
    if ($(this).hasClass('tab-active')) {
      var dataTarget = $(this).data('target');
      var dataPeriod = $(this).data('period');
      var dataPerCat = '';
      $(id_closet + ' .box-infor').each(function () {
        $(this).find('.vnx-price-cyc').each(function () {
          var dataPrice = $(this).data('tab');
          dataPerCat = $(this).data('product-category');
          if (dataTarget === dataPrice) {
            var cost = $(this).data('cost');
            var year_price = $(this).data('price-year');
            $(this).closest('.box-infor-col.price').find('.vnx-price-reduced .vnx-price-reduced-text').text(cost);
            $(this).closest('.box-infor-col.price').find('.vnx-price-reduced-year').text(year_price);
            if (year_price == null || year_price === "") {
              $(this).closest('.box-infor-col.price').find('.box-vnx-price-reduced-year').addClass('hidden');
            } else {
              $(this).closest('.box-infor-col.price').find('.box-vnx-price-reduced-year').removeClass('hidden');
              $(this).closest('.box-infor-col.price').find('.box-infor-text-price-year span').text(dataPeriod);
              $(this).closest('.box-infor-col.price').find('.vnx-price-cyc-year').text(dataPeriod);
            }
          }
        });
      });
      $(id_closet + ' .url-register').each(function () {
        var dataBox = $(this).data('box');
        var dataPrice = '';
        var dataCyc = dataPeriod;
        var dataName = '';
        $('.vnx_firewall_package .box-infor').each(function () {
          var idBox = $(this).data('box');
          if (idBox == dataBox) {
            dataPrice = $(this).find('.vnx-price-reduced .vnx-price-reduced-text').text();
            dataName = $(this).find('.vnx-price-reduced-name p').text();
          }
        });
        $(this).find('.vnx-price-cyc-url').each(function () {
          var dataUrl = $(this).data('tab');
          if (dataTarget === dataUrl) {
            var dataUrlValue = $(this).data('url');
            $(this).closest('.url-register').find('.vnx-button-register').attr("href", dataUrlValue);
            $(this).closest('.url-register').find('.vnx-button-register').attr("data-price", dataPrice);
            $(this).closest('.url-register').find('.vnx-button-register').attr("data-period", dataCyc);
            $(this).closest('.url-register').find('.vnx-button-register').attr("data-product-name", dataName);
            $(this).closest('.url-register').find('.vnx-button-register').attr("data-product-category", dataPerCat);
          }
        });
      });
    }
  });
  $('.vnx-cyc-price').click(function () {
    let parentElement = $(this).closest('.vnx_element_firewall');
    let id_closet = '#' + parentElement.attr('id');
    let dataTarget = $(this).data('target');
    let dataPeriod = $(this).data('period');
    var dataPerCat = '';
    parentElement.find('.vnx-cyc-price').removeClass('vnx-tab-active');
    // Thêm 'tab-active' vào các phần tử có data-target giống với dataTarget
    parentElement.find('.vnx-cyc-price').each(function () {
      if ($(this).data('target') === dataTarget) {
        console.log($(this).data('target'))
        $(this).addClass('vnx-tab-active');
      }
    });
    $(id_closet + ' .box-infor').each(function () {
      $(this).find('.vnx-price-cyc').each(function () {
        var data_price = $(this).data('tab');
        dataPerCat = $(this).data('product-category');
        if (dataTarget == data_price) {
          var cost = $(this).data('cost');
          var year_price = $(this).data('price-year');
          $(this).closest('.box-infor-col.price').find('.vnx-price-reduced .vnx-price-reduced-text').text(cost);
          $(this).closest('.box-infor-col.price').find('.vnx-price-reduced-year').text(year_price);
          if (year_price == null || year_price === "") {
            $(this).closest('.box-infor-col.price').find('.box-vnx-price-reduced-year').addClass('hidden');
          } else {
            $(this).closest('.box-infor-col.price').find('.box-vnx-price-reduced-year').removeClass('hidden');
            $(this).closest('.box-infor-col.price').find('.box-infor-text-price-year span').text(dataPeriod);
            $(this).closest('.box-infor-col.price').find('.vnx-price-cyc-year').text(dataPeriod);
          }
        }
      });
    });
    $(id_closet + ' .url-register').each(function () {
      var dataBox = $(this).data('box');
      var dataPrice = '';
      var dataCyc = dataPeriod;
      var dataName = '';
      $('.vnx_firewall_package .box-infor').each(function () {
        var idBox = $(this).data('box');
        if (idBox == dataBox) {
          dataPriceRu = $(this).find('.vnx-price-reduced .vnx-price-reduced-text').text();
          dataPriceCycle = $(this).find('.box-vnx-price-reduced-year .vnx-price-reduced-year').text();
          if (dataPriceCycle == null || dataPriceCycle === "") {
            dataPrice = dataPriceRu;
          } else {
            dataPrice = dataPriceCycle;
          }
          dataCat = $(this).find('.vnx-price-reduced-name .vnx_title_cat').text();
          dataName = $(this).find('.vnx-price-reduced-name p').text();
        }
      });
      $(this).find('.vnx-price-cyc-url').each(function () {
        var dataUrl = $(this).data('tab');
        if (dataTarget === dataUrl) {
          var dataUrlValue = $(this).data('url');
          $(this).closest('.url-register').find('.vnx-button-register').attr("href", dataUrlValue);
          $(this).closest('.url-register').find('.vnx-button-register').attr("data-price", dataPrice);
          $(this).closest('.url-register').find('.vnx-button-register').attr("data-period", dataCyc);
          $(this).closest('.url-register').find('.vnx-button-register').attr("data-product-name", dataName);
          $(this).closest('.url-register').find('.vnx-button-register').attr("data-product-category", dataPerCat);
        }
      });
    });
  });
  //gói special
  $(".firewall_anti .vnx_firewall_name .box-title.special").each(function () {
    var tabActiveValue = $(this).data("tab-active");
    $(".firewall_anti .vnx_firewall_url .box-title").each(function () {
      var dataBoxValue = $(this).data("box");
      if (dataBoxValue == tabActiveValue) {
        $(this).addClass("special");
      }
    });
    $(".firewall_anti .vnx_firewall_package .box-infor").each(function () {
      var dataBoxValue = $(this).data("box");
      if (dataBoxValue == tabActiveValue) {
        $(this).addClass("special");
      }
    });
  });
  $('.box-infor-text-price-year').hover(
    function () { // Khi hover vào phần tử
      $(this).closest('.box-vnx-price-reduced-year').find('.box-infor-tooltip-price-year').removeClass('hidden');
    },
    function () { // Khi rời chuột khỏi phần tử
      $(this).closest('.box-vnx-price-reduced-year').find('.box-infor-tooltip-price-year').addClass('hidden');
    }
  );
  $('.box-infor-tooltip-price-year').hover(
    function () { // Khi hover vào phần tử
      $(this).removeClass('hidden');
    },
    function () { // Khi rời chuột khỏi phần tử
      $(this).addClass('hidden');
    }
  );
  var id_section = $('.vnx_element_firewall').attr('id');
  var splide_options = $('.vnx_div_data_firewall').data('splide');
  new Splide('#' + id_section + ' #vnx_body_slider_firewall',
    splide_options
  ).mount();
}
function initSwiperSlide(containerID) {
  SwiperMB = new Swiper(containerID + ' .swiper-container-mobile', {
    slidesPerView: 3,
    loop: true,
    allowTouchMove: false,
    allowSlidePrev: true,
    allowSlideNext: true,
    speed: 100,
    navigation: {
      nextEl: containerID + ' .swiper-button-next',
      prevEl: containerID + ' .swiper-button-prev',
    },
    speed: 100,
  });
  SwiperDT = new Swiper(containerID + ' .swiper-container', {
    slidesPerView: 4,
    loop: true,
    allowTouchMove: false,
    allowSlidePrev: true,
    allowSlideNext: true,
    speed: 100,
    navigation: {
      nextEl: containerID + ' .swiper-button-next',
      prevEl: containerID + ' .swiper-button-prev',
    },
    speed: 100,
  });
}
function vnxHostingCompare() {
  $('.brxe-vnx-service-price[id]').each(function () {
    var $container = $(this);
    var containerID = "#" + $container.attr('id');
    initSwiperSlide(containerID);
    $(window).on('resize', function () {
      SwiperMB = new Swiper(containerID + ' .swiper-container-mobile', {
        slidesPerView: 3,
        loop: true,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        navigation: {
          nextEl: containerID + ' .swiper-button-next',
          prevEl: containerID + ' .swiper-button-prev',
        },
        speed: 100,
      });
      SwiperDT = new Swiper(containerID + ' .swiper-container', {
        slidesPerView: 4,
        loop: true,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        navigation: {
          nextEl: containerID + ' .swiper-button-next',
          prevEl: containerID + ' .swiper-button-prev',
        },
        speed: 100,
      });
    });
    // Tooltip hover events
    $container.find('.swiper-container').on('mouseenter', '.tooltip_ru', function () {
      $(this).find('.el-custom-tooltip-price-year').removeClass('hidden');
    }).on('mouseleave', '.tooltip_ru', function () {
      $(this).find('.el-custom-tooltip-price-year').addClass('hidden');
    });

    // Load more/close actions
    $container.find('.vnx-button-more').on('click', function () {
      let parentElement = $(this).closest('.vnx_box_infor_hosting');
      parentElement.removeClass('vnx_box_infor_hosting_second')
        .find('.vnx_load_more').removeClass('collapsed').addClass('expanded')
        .end().find('.vnx_content_box_hosting').removeClass('overflow-hidden')
        .end().find('.vnx-button-more').addClass('hidden')
        .end().find('.vnx-button-close').removeClass('hidden');
    });

    $container.find('.vnx-button-close').on('click', function () {
      let parentElement = $(this).closest('.vnx_box_infor_hosting');
      parentElement.addClass('vnx_box_infor_hosting_second')
        .find('.vnx_load_more').removeClass('expanded').addClass('collapsed')
        .end().find('.vnx_content_box_hosting').addClass('overflow-hidden')
        .end().find('.vnx-button-more').removeClass('hidden')
        .end().find('.vnx-button-close').addClass('hidden');
    });
  });
}

function vnxHostingCompareV2() {
  $('.vnx_icon_hidden').on('click', function () {
    var box_hidden = $(this).closest('.vnx_box_infor_hosting');
    box_hidden.find('.vnx_icon_show').removeClass('hidden');
    box_hidden.find('.vnx_content_box_hosting').addClass('vnx_hidden vnx_visible');
    $(this).addClass('hidden');
  });
  $('.vnx_icon_show').on('click', function () {
    var box_show = $(this).closest('.vnx_box_infor_hosting');
    box_show.find('.vnx_icon_hidden').removeClass('hidden');
    box_show.find('.vnx_content_box_hosting').removeClass('vnx_hidden vnx_visible');
    $(this).addClass('hidden');
  });

  $('.tooltip_ru').hover(
    function () {
      $(this).find('.el-custom-tooltip-price-year').removeClass('hidden');
    },
    function () {
      $(this).find('.el-custom-tooltip-price-year').addClass('hidden');
    }
  );
  $('.tooltip_ru').hover(
    function () {
      $(this).find('.el-custom-tooltip-price-year').removeClass('hidden');
    },
    function () {
      $(this).find('.el-custom-tooltip-price-year').addClass('hidden');
    }
  );
  //click load more
  $('.compare_hosting_v2 .vnx-button-more').click(function () {
    let parentElement = $(this).closest('.vnx_box_infor_hosting');
    if (parentElement.hasClass('vnx_box_infor_hosting_second')) {
      parentElement.removeClass('vnx_box_infor_hosting_second');
      parentElement.find('.vnx_load_more').removeClass('collapsed').addClass('expanded');
      parentElement.find('.vnx_content_box_hosting').removeClass('overflow-hidden');
    }
    parentElement.find('.vnx-button-more').addClass('hidden');
    parentElement.find('.vnx-button-close').removeClass('hidden');
  });

  $('.compare_hosting_v2 .vnx-button-close').click(function () {
    let parentElement = $(this).closest('.vnx_box_infor_hosting');
    if (!parentElement.hasClass('vnx_box_infor_hosting_second')) {
      parentElement.addClass('vnx_box_infor_hosting_second');
      parentElement.find('.vnx_load_more').removeClass('expanded').addClass('collapsed');
      parentElement.find('.vnx_content_box_hosting').addClass('overflow-hidden');
    }
    parentElement.find('.vnx-button-more').removeClass('hidden');
    parentElement.find('.vnx-button-close').addClass('hidden');
  });

  $(window).on('load resize', function () {
    if ($(window).width() <= 1024) {
      var mySwiper_mb = new Swiper('.swiper-container-mobile', {
        slidesPerView: 3,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      });
    } else {
      var mySwiper_ds = new Swiper('.swiper-container', {
        slidesPerView: 3,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        loop: true,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      });
    }
  });
  $('#swiper-button-next').on('click', function () {
    var sliders = $('.swiper-container#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slideNext();
      }
    });
  });

  $('#swiper-button-prev').on('click', function () {
    var sliders = $('.swiper-container#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slidePrev();
      }
    });
  });
  $('.swiper-container-mobile #swiper-button-next').on('click', function () {
    var sliders = $('.swiper-container-mobile#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slideNext();
      }
    });
  });

  $('.swiper-container-mobile #swiper-button-prev').on('click', function () {
    var sliders = $('.swiper-container-mobile#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slidePrev();
      }
    });
  });
}
function VnxMultipleService() {
  $('.multiple_service_hosting .vnx_tab_title').on('click', function () {
    const thisTarget = $(this).attr("data-target");
    $(this).parent().find(".tab_active").removeClass("tab_active");
    $(this).addClass("tab_active");
    $('.multiple_service_hosting .vnx_service_body').find(".vnx_tab_body.tab_active").removeClass("tab_active");
    $('.multiple_service_hosting .vnx_service_body').find(".vnx_tab_body." + thisTarget).addClass("tab_active");
  });
  $(".multiple_service_hosting .vnx_tab_body").each(function () {
    var item = $(this).find(".vnx_package");
    if (item.length > 6) {
      $(this).find(".vnx_package").slice(6).css('cssText', 'display: none !important;');
    } else {
      $(this).find('.vnx_button').css('cssText', 'display: none !important;');
    }
  });
  $('.multiple_service_hosting a#vnx_button_loadmore').click(function () {
    $(this).addClass('hidden');
    $(this).closest('.vnx_tab_body').find('#vnx_button_closemore').removeClass('hidden');
    $(this).closest('.vnx_tab_body').find(".vnx_package").slice(6).removeAttr('style');
  });
  $('.multiple_service_hosting a#vnx_button_closemore').click(function () {
    $(this).addClass('hidden');
    $(this).closest('.vnx_tab_body').find('#vnx_button_loadmore').removeClass('hidden');
    $(this).closest('.vnx_tab_body').find(".vnx_package").slice(6).css('cssText', 'display: none !important;');
  });
  new Swiper('.multiple_service_hosting_mobile .swiper_service_multiple', {
    slidesPerView: 1,
    allowTouchMove: false,
    allowSlidePrev: true,
    allowSlideNext: true,
    speed: 100,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });
}
function VnxScrollSplide() {
  var naviga = $('.splide__arrows.splide__arrows--ltr');
  if (naviga.length === 0) return;
  var menuOffset = naviga.offset().top;
  $(window).scroll(function () {
    var scrollPos = $(window).scrollTop();
    if (scrollPos >= menuOffset) {
      naviga.addClass('sticky_nav');
    } else {
      naviga.removeClass('sticky_nav');
    }
  });
}
function VnxHoverTooltip() {
  $('.el-custom-text-price-year').hover(
    function () { // Khi hover vào phần tử
      $(this).closest('.box-vnx-price-reduced-year').find('.el-custom-tooltip-price-year').removeClass('hidden');
    },
    function () { // Khi rời chuột khỏi phần tử
      $(this).closest('.box-vnx-price-reduced-year').find('.el-custom-tooltip-price-year').addClass('hidden');
    }
  );
  $('.el-custom-tooltip-price-year').hover(
    function () { // Khi hover vào phần tử
      $(this).removeClass('hidden');
    },
    function () { // Khi rời chuột khỏi phần tử
      $(this).addClass('hidden');
    }
  );
}
function VnxButtonRegister() {
  $('.vnx-cyc-price').each(function () {
    var parentElement = $(this).closest('.brxe-vnx-service-price');
    var id_closet = '#' + parentElement.attr('id');
    if ($(this).hasClass('tab-active')) {
      var dataTarget = $(this).data('target');
      var dataDiscount = $(this).data('discount');
      var dataPeriod = $(this).data('period');
      $(id_closet + ' .splide__slide').each(function () {
        $(this).find('.vnx-price-cyc').each(function () {
          var dataPrice = $(this).data('tab');
          if (dataTarget === dataPrice) {
            var cost = $(this).data('cost');
            var regular_price = $(this).data('price');
            var year_price = $(this).data('price-year');
            var dataProduct = $(this).data('product-name');
            var dataProductCT = $(this).data('product-category');
            $(this).closest('.card-content-price').find('.vnx-price-reduced').text(regular_price);
            if (year_price == null || year_price === "") {
              $(this).closest('.card-content-price').find('.box-vnx-price-reduced-year').addClass('hidden');
            } else {
              $(this).closest('.card-content-price').find('.vnx-price-reduced-year').text(year_price);
            }
            if (cost == null || cost === "") {
              $(this).closest('.card-content-price').find('.vnx-tab.vnx-cost').addClass('hidden');
            } else {
              $(this).closest('.card-content-price').find('.vnx-tab.vnx-cost').removeClass('hidden');
              $(this).closest('.card-content-price').find('.vnx-price-cost').text(cost);
            }
            $(this).closest('.card-content-price').find('.el-custom-tab-discount').text(dataDiscount);
            //nút đăng ký ngay
            $(this).closest('.card-content-price').find('.el-custom-text-price-year span').text(dataPeriod);
            $(this).closest('.card-price').find('.vnx-price-cyc-year').text(dataPeriod);
            $(this).closest('.card-price').find('.url-register').attr("data-period", dataPeriod);
            $(this).closest('.card-price').find('.url-register').attr("data-price", year_price);
            // $(this).closest('.card-price').find('.url-register').attr("data-price-year", year_price);
            $(this).closest('.card-price').find('.url-register').attr("data-product-name", dataProduct);
            $(this).closest('.card-price').find('.url-register').attr("data-product-category", dataProductCT);
          }
        });
        $(this).find('.vnx-price-url').each(function () {
          var dataPrice = $(this).data('tab');
          if (dataTarget === dataPrice) {
            var dataUrl = $(this).data('url');
            $(this).closest('.card-price').find('.url-register').attr("href", dataUrl);
          }
        });
      });
    }
  });
  $('.list_hosting_price_v1 .vnx-button-more').click(function () {
    let parentSplide = $(this).closest('.splide__list');
    parentSplide.find('.card-content-infor').each(function () {
      if ($(this).hasClass('collapsed')) {
        $(this).removeClass('collapsed').addClass('expanded'); // Thêm lớp expanded để mở rộng
        $(this).css('overflow', 'unset');
      }
    });
    parentSplide.find('.vnx-button-more').addClass('hidden');
    parentSplide.find('.vnx-button-close').removeClass('hidden');
  });

  $('.list_hosting_price_v1 .vnx-button-close').click(function () {
    let parentSplide = $(this).closest('.splide__list');
    parentSplide.find('.card-content-infor').each(function () {
      if (!$(this).hasClass('collapsed')) {
        $(this).removeClass('expanded').addClass('collapsed'); // Thêm lớp collapsed để thu gọn
        $(this).css('overflow', 'hidden');
      }
    });
    parentSplide.find('.vnx-button-more').removeClass('hidden');
    parentSplide.find('.vnx-button-close').addClass('hidden');
    var targetCard = $(this).closest('.card-price');
    $('html, body').animate({
      scrollTop: targetCard.offset().top - 100
    }, 600);
  });
}
function vnxHostingCompareSSL() {
  $('.vnx_icon_hidden').on('click', function () {
    var box_hidden = $(this).closest('.vnx_box_infor_hosting');
    box_hidden.find('.vnx_icon_show').removeClass('hidden');
    box_hidden.find('.vnx_content_box_hosting').addClass('vnx_hidden vnx_visible');
    $(this).addClass('hidden');
  });
  $('.vnx_icon_show').on('click', function () {
    var box_show = $(this).closest('.vnx_box_infor_hosting');
    box_show.find('.vnx_icon_hidden').removeClass('hidden');
    box_show.find('.vnx_content_box_hosting').removeClass('vnx_hidden vnx_visible');
    $(this).addClass('hidden');
  });
  //click load more
  $('.compare_ssl .vnx-button-more').click(function () {
    let parentElement = $(this).closest('.vnx_box_infor_hosting');
    if (parentElement.hasClass('vnx_box_infor_hosting_second')) {
      parentElement.removeClass('vnx_box_infor_hosting_second');
      parentElement.find('.vnx_load_more').removeClass('collapsed').addClass('expanded');
      parentElement.find('.vnx_content_box_hosting').removeClass('overflow-hidden');
    }
    parentElement.find('.vnx-button-more').addClass('hidden');
    parentElement.find('.vnx-button-close').removeClass('hidden');
  });

  $('.compare_ssl .vnx-button-close').click(function () {
    let parentElement = $(this).closest('.vnx_box_infor_hosting');
    if (!parentElement.hasClass('vnx_box_infor_hosting_second')) {
      parentElement.addClass('vnx_box_infor_hosting_second');
      parentElement.find('.vnx_load_more').removeClass('expanded').addClass('collapsed');
      parentElement.find('.vnx_content_box_hosting').addClass('overflow-hidden');
    }
    parentElement.find('.vnx-button-more').removeClass('hidden');
    parentElement.find('.vnx-button-close').addClass('hidden');
  });

  $(window).on('load resize', function () {
    if ($(window).width() <= 1024) {
      var mySwiper_mb = new Swiper('.swiper-container-mobile', {
        slidesPerView: 3,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      });
    } else {
      var mySwiper_ds = new Swiper('.swiper-container', {
        slidesPerView: 4,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        loop: true,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      });
    }
  });
  $('#swiper-button-next').on('click', function () {
    var sliders = $('.swiper-container#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slideNext();
      }
    });
  });

  $('#swiper-button-prev').on('click', function () {
    var sliders = $('.swiper-container#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slidePrev();
      }
    });
  });
  $('.swiper-container-mobile #swiper-button-next').on('click', function () {
    var sliders = $('.swiper-container-mobile#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slideNext();
      }
    });
  });

  $('.swiper-container-mobile #swiper-button-prev').on('click', function () {
    var sliders = $('.swiper-container-mobile#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slidePrev();
      }
    });
  });
}

function vnxHostingCompareTable() {
  $('.vnx_box_infor_hosting').each(function () {
    const $box = $(this);
    const $rows = $box.find('.box-list-row-infor .box-row-infor');
    const $column2s = $box.find('.box-row-infor-price');
    const rowCount = $rows.length;

    for (let i = 0; i < rowCount; i++) {
      let maxHeight = 0;
      $rows.eq(i).css('height', '');
      $column2s.each(function () {
        $(this).find('.row-price').eq(i).css('height', '');
      });
      const rowHeight = $rows.eq(i).outerHeight();
      if (rowHeight > maxHeight) {
        maxHeight = Math.ceil(rowHeight);
      }
      $column2s.each(function () {
        const $row2 = $(this).find('.row-price').eq(i);
        const row2Height = $row2.outerHeight();
        if (row2Height > maxHeight) {
          maxHeight = Math.ceil(row2Height);
        }
      });
      $rows.eq(i).css('height', maxHeight + 'px');
      $column2s.each(function () {
        $(this).find('.row-price').eq(i).css('height', maxHeight + 'px');
      });
    }
    $('.vnx_box_infor_hosting.vnx_box_infor_hosting_second').each(function () {
      const $box = $(this);
      const $rows = $box.find('.box-row-infor').slice(0, 4); // lấy 4 cái đầu

      let totalHeight = 0;

      $rows.each(function () {
        totalHeight += Math.ceil($(this).outerHeight(true)); // true để bao gồm margin
      });
      $box.find('.vnx_content_box_hosting').css('max-height', totalHeight + 'px');

      const $buttonMore = $box.find('.vnx-button-more');
      const $buttonClose = $box.find('.vnx-button-close');

      //click load more
      $buttonMore.click(function () {
        let parentElement = $buttonMore.closest('.vnx_box_infor_hosting');
        if (parentElement.hasClass('vnx_box_infor_hosting_second')) {
          parentElement.removeClass('vnx_box_infor_hosting_second');
          parentElement.find('.vnx_load_more').removeClass('collapsed').addClass('expanded');
          parentElement.find('.vnx_content_box_hosting').removeClass('overflow-hidden');
          parentElement.find('.vnx_content_box_hosting').css('max-height', '100%');
        }
        parentElement.find('.vnx-button-more').addClass('hidden');
        parentElement.find('.vnx-button-close').removeClass('hidden');
      });

      $buttonClose.click(function () {
        let parentElement = $buttonClose.closest('.vnx_box_infor_hosting');
        if (!parentElement.hasClass('vnx_box_infor_hosting_second')) {
          parentElement.addClass('vnx_box_infor_hosting_second');
          parentElement.find('.vnx_load_more').removeClass('expanded').addClass('collapsed');
          parentElement.find('.vnx_content_box_hosting').addClass('overflow-hidden');
        }
        parentElement.find('.vnx-button-more').removeClass('hidden');
        parentElement.find('.vnx-button-close').addClass('hidden');
        parentElement.find('.vnx_content_box_hosting').css('max-height', totalHeight + 'px');
      });

    });
  });

  $('.vnx_icon_hidden').on('click', function () {
    var box_hidden = $(this).closest('.vnx_box_infor_hosting');
    box_hidden.find('.vnx_icon_show').removeClass('hidden');
    box_hidden.find('.vnx_content_box_hosting').addClass('vnx_hidden vnx_visible');
    $(this).addClass('hidden');
  });
  $('.vnx_icon_show').on('click', function () {
    var box_show = $(this).closest('.vnx_box_infor_hosting');
    box_show.find('.vnx_icon_hidden').removeClass('hidden');
    box_show.find('.vnx_content_box_hosting').removeClass('vnx_hidden vnx_visible');
    $(this).addClass('hidden');
  });

  $(window).on('load resize', function () {
    $('.box-mobile .vnx_box_infor_hosting.vnx_box_infor_hosting_second').each(function () {
      const $box = $(this);
      const $rows = $box.find('.vnx_content_body > div').slice(0, 8);
      let totalHeight = 0;

      $rows.each(function () {
        totalHeight += Math.ceil($(this).outerHeight(true)); // true để bao gồm margin
      });
      $box.find('.vnx_content_box_hosting').css('max-height', totalHeight + 'px');

      const $buttonMore = $box.find('.vnx-button-more');
      const $buttonClose = $box.find('.vnx-button-close');

      //click load more
      $buttonMore.click(function () {
        let parentElement = $buttonMore.closest('.vnx_box_infor_hosting');
        if (parentElement.hasClass('vnx_box_infor_hosting_second')) {
          parentElement.removeClass('vnx_box_infor_hosting_second');
          parentElement.find('.vnx_load_more').removeClass('collapsed').addClass('expanded');
          parentElement.find('.vnx_content_box_hosting').removeClass('overflow-hidden');
          parentElement.find('.vnx_content_box_hosting').css('max-height', '100%');
        }
        parentElement.find('.vnx-button-more').addClass('hidden');
        parentElement.find('.vnx-button-close').removeClass('hidden');
      });

      $buttonClose.click(function () {
        let parentElement = $buttonClose.closest('.vnx_box_infor_hosting');
        if (!parentElement.hasClass('vnx_box_infor_hosting_second')) {
          parentElement.addClass('vnx_box_infor_hosting_second');
          parentElement.find('.vnx_load_more').removeClass('expanded').addClass('collapsed');
          parentElement.find('.vnx_content_box_hosting').addClass('overflow-hidden');
        }
        parentElement.find('.vnx-button-more').removeClass('hidden');
        parentElement.find('.vnx-button-close').addClass('hidden');
        parentElement.find('.vnx_content_box_hosting').css('max-height', totalHeight + 'px');
      });

    });

    if ($(window).width() <= 1024) {
      var mySwiper_mb = new Swiper('.swiper-container-mobile', {
        slidesPerView: 3,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      });
    } else {
      var mySwiper_ds = new Swiper('.swiper-container', {
        slidesPerView: 4,
        allowTouchMove: false,
        allowSlidePrev: true,
        allowSlideNext: true,
        speed: 100,
        loopAdditionalSlides: 0,
        loop: true,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      });
    }
  });
  $('#swiper-button-next').on('click', function () {
    var sliders = $('.swiper-container#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slideNext();
      }
    });
  });

  $('#swiper-button-prev').on('click', function () {
    var sliders = $('.swiper-container#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slidePrev();
      }
    });
  });
  $('.swiper-container-mobile #swiper-button-next').on('click', function () {
    var sliders = $('.swiper-container-mobile#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slideNext();
      }
    });
  });

  $('.swiper-container-mobile #swiper-button-prev').on('click', function () {
    var sliders = $('.swiper-container-mobile#compare_hosting');
    sliders.each(function () {
      var swiper = $(this).get(0).swiper;
      if (swiper) {
        swiper.slidePrev();
      }
    });
  });
}

function vnxHostingCompareServer() {
  const initSwiper = () => {
    $(".vnx_icon_hidden").on("click", function () {
      var box_hidden = $(this).closest(".vnx_box_infor_hosting");
      box_hidden.find(".vnx_icon_show").removeClass("hidden");
      box_hidden
        .find(".vnx_content_box_hosting")
        .addClass("vnx_hidden vnx_visible");
      $(this).addClass("hidden");
    });
    $(".vnx_icon_show").on("click", function () {
      var box_show = $(this).closest(".vnx_box_infor_hosting");
      box_show.find(".vnx_icon_hidden").removeClass("hidden");
      box_show
        .find(".vnx_content_box_hosting")
        .removeClass("vnx_hidden vnx_visible");
      $(this).addClass("hidden");
    });
    //click load more
    $(".compare_server .vnx-button-more").click(function () {
      let parentElement = $(this).closest(".vnx_box_infor_hosting");
      if (parentElement.hasClass("vnx_box_infor_hosting_second")) {
        parentElement.removeClass("vnx_box_infor_hosting_second");
        parentElement
          .find(".vnx_load_more")
          .removeClass("collapsed")
          .addClass("expanded");
        parentElement
          .find(".vnx_content_box_hosting")
          .removeClass("overflow-hidden");
      }
      parentElement.find(".vnx-button-more").addClass("hidden");
      parentElement.find(".vnx-button-close").removeClass("hidden");
    });

    $(".compare_server .vnx-button-close").click(function () {
      let parentElement = $(this).closest(".vnx_box_infor_hosting");
      if (!parentElement.hasClass("vnx_box_infor_hosting_second")) {
        parentElement.addClass("vnx_box_infor_hosting_second");
        parentElement
          .find(".vnx_load_more")
          .removeClass("expanded")
          .addClass("collapsed");
        parentElement
          .find(".vnx_content_box_hosting")
          .addClass("overflow-hidden");
      }
      parentElement.find(".vnx-button-more").removeClass("hidden");
      parentElement.find(".vnx-button-close").addClass("hidden");
    });

    $(window).on("load resize", function () {
      if ($(window).width() <= 1024) {
        var mySwiper_mb = new Swiper(".swiper-container-mobile", {
          slidesPerView: 3,
          allowTouchMove: false,
          allowSlidePrev: true,
          allowSlideNext: true,
          speed: 100,
          navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
          },
        });
      } else {
        var mySwiper_ds = new Swiper(".swiper-container", {
          slidesPerView: 4,
          allowTouchMove: false,
          allowSlidePrev: true,
          allowSlideNext: true,
          speed: 100,
          loop: true,
          navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
          },
        });
      }
    });
    $("#swiper-button-next").on("click", function () {
      var sliders = $(".swiper-container#compare_hosting");
      sliders.each(function () {
        var swiper = $(this).get(0).swiper;
        if (swiper) {
          swiper.slideNext();
        }
      });
    });

    $("#swiper-button-prev").on("click", function () {
      var sliders = $(".swiper-container#compare_hosting");
      sliders.each(function () {
        var swiper = $(this).get(0).swiper;
        if (swiper) {
          swiper.slidePrev();
        }
      });
    });
    $(".swiper-container-mobile #swiper-button-next").on("click", function () {
      var sliders = $(".swiper-container-mobile#compare_hosting");
      sliders.each(function () {
        var swiper = $(this).get(0).swiper;
        if (swiper) {
          swiper.slideNext();
        }
      });
    });

    $(".swiper-container-mobile #swiper-button-prev").on("click", function () {
      var sliders = $(".swiper-container-mobile#compare_hosting");
      sliders.each(function () {
        var swiper = $(this).get(0).swiper;
        if (swiper) {
          swiper.slidePrev();
        }
      });
    });
  };
  /*
  Ý tưởng logic bảng giá so sánh:
  1. Khi khởi tạo Vue, lưu lại DOM gốc của header và body bảng giá để phục vụ việc so sánh.
  2. Nếu không chọn filter (selectedServers rỗng), hiển thị bảng giá gốc.
  3. Nếu có filter, ẩn bảng giá gốc và hiển thị bảng giá so sánh (bản sao).
  4. Dùng DOM gốc để truy vấn và lấy dữ liệu khi cần thay đổi nội dung so sánh.
  5. Khi người dùng chọn filter, dùng hàm changeIndexRow để cập nhật nội dung các cột so sánh theo lựa chọn.
  */
  new Vue({
    el: ".compare_server",
    data: function () {
      return {
        selectedServers: ["", "", "", ""],
        dropdownOpen: [false, false, false, false],
        titles: [],
        originalDomPricePack: "",
        isTwoSelected: false,
        isShowCompare: true,
      };
    },
    created: function () {
      this.$eventBus.$on("changeCompare", (compare) => {
        this.selectedServers = compare;

        if (this.selectedServers.every((value) => value === "")) {
          this.isShowCompare = false;
        }
      });
      this.$eventBus.$on("compareNow", () => {
        console.log(this.selectedServers);
        this.handleCompare();
      });
    },
    mounted: function () {
      initSwiper();
      // delay 2s để clone dom slide
      setTimeout(() => {
        this.originalDomPricePack = $(".compare_server").clone(true);
        this.initDomDuplicate();
        this.$nextTick(() => {
          initSwiper();
        });
      }, 2000);
      this.titles = this.getPricePackTitle();
    },
    watch: {
      selectedServers: {
        handler: function (newValue) {
          this.isTwoSelected = this.hasTwoValues(newValue);
        },
        deep: true,
      },
    },
    methods: {
      handleBlur: function (idx) {
        this.$set(this.dropdownOpen, idx, false);
      },
      handleCompare: function () {
        if (this.isTwoSelected) {
          this.initSelectDomDuplicate();

          this.isShowCompare = true;
          this.selectedServers.forEach((value, index) => {
            this.changeIndexRow(parseInt(value), index);
          });
        }
      },
      getPricePackTitle: function () {
        const titles = $(".compare_server .vnx_price_pack_title")
          .map(function () {
            return $(this).text().trim();
          })
          .get();

        // Loại bỏ trùng bằng Set:
        return [...new Set(titles)];
      },
      changeIndexRow: function (indexOriginal, currentIndex) {
        const currentHTML = $(
          '.compare_server .vnx_header_price_duplicate [data-swiper-slide-index="' +
          currentIndex +
          '"]:not(.swiper-slide-duplicate)'
        );

        const originalContentHTML = this.originalDomPricePack.find(
          '.vnx_header_price [data-swiper-slide-index="' +
          indexOriginal +
          '"]:not(.swiper-slide-duplicate)'
        );

        currentHTML.each(function (i, el) {
          const originalEl = originalContentHTML[i];
          if (originalEl) {
            el.innerHTML = originalEl.innerHTML;
          }
        });

        const currentHTML2 = $(
          '.compare_server .vnx_body_prices_duplicate [data-swiper-slide-index="' +
          currentIndex +
          '"]:not(.swiper-slide-duplicate)'
        );

        const originalContentHTML2 = this.originalDomPricePack.find(
          '.vnx_body_prices [data-swiper-slide-index="' +
          indexOriginal +
          '"]:not(.swiper-slide-duplicate)'
        );

        currentHTML2.each(function (i, el) {
          const originalEl = originalContentHTML2[i];
          if (originalEl) {
            el.innerHTML = originalEl.innerHTML;
          }
        });
      },
      initSelectDomDuplicate: function () {
        var self = this;
        // Reset prices in duplicate rows
        $(
          ".compare_server .vnx_body_prices_duplicate .vnx_box_infor_hosting"
        ).each(function (boxIdx) {
          $(this)
            .find(".vnx_content_box_hosting .box-row-infor-all-price")
            .each(function () {
              var $removed = $(this).find(
                ".box-row-infor-price:not(.duplicate)"
              );
              $($removed).find(".row-price").text("");
            });
        });

        // Remove old buttons and dropdowns
        $(
          ".compare_server .vnx_header_price_duplicate .swiper_button"
        ).remove();
        $(".compare_server .vnx_header_price_duplicate .vnx_box_tag").remove();
        $(
          ".compare_server .vnx_header_price_duplicate .vnx_box_content"
        ).empty();
        $(
          ".compare_server .vnx_header_price_duplicate .swiper-slide"
        ).removeClass("vnx_price_pack_popular");
        $(
          ".compare_server .vnx_body_prices_duplicate .swiper-slide"
        ).removeClass("vnx_price_pack_popular_row");

        // Dropdown open state for each box
        if (!self._dropdownOpenDuplicate) {
          self._dropdownOpenDuplicate = [false, false, false, false];
        }

        // Close all dropdowns when clicking outside
        $(document)
          .off("mousedown.vnxDropdownDuplicate")
          .on("mousedown.vnxDropdownDuplicate", function (e) {
            $(
              ".compare_server .vnx_header_price_duplicate .vnx_box_content .vnx-dropdown-duplicate"
            ).each(function (idx) {
              if (!$(e.target).closest(this).length) {
                self._dropdownOpenDuplicate[idx] = false;
                $(this).find(".vnx-dropdown-list").addClass("hidden");
              }
            });
          });

        // Render tất cả dropdown lần đầu
        $(
          ".compare_server .vnx_header_price_duplicate .swiper-slide:not(.swiper-slide-duplicate) .vnx_box_content"
        ).each(function (boxIdx) {
          self.renderDropdownDuplicate(boxIdx);
        });
      },
      initDomDuplicate: function () {
        $(".compare_server .vnx_header_price_duplicate").html(
          this.originalDomPricePack.find(".vnx_header_price").html()
        );
        $(".compare_server .vnx_body_prices_duplicate").html(
          this.originalDomPricePack.find(".vnx_body_prices").html()
        );
      },
      renderDropdownDuplicate(boxIdx) {
        var self = this;

        var selectedIdx = self.selectedServers[boxIdx];
        var label =
          selectedIdx !== "" &&
            selectedIdx !== null &&
            selectedIdx !== undefined
            ? self.titles[selectedIdx]
            : "Chọn máy chủ";

        var $dropdown = $(
          '<div class="vnx-dropdown-duplicate flex flex-col items-start w-full relative"></div>'
        );
        if (boxIdx === 3) {
          $dropdown.addClass("last-dropdown");
        }
        var $button = $(
          '<button type="button" class="w-full h-10 px-3 border border-[#0F0F0F] rounded focus:outline-none focus:ring-2 focus:ring-blue-400 flex items-center justify-between bg-white"></button>'
        );

        $button.append("<span>" + label + "</span>");
        $button.append(
          '<svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>'
        );

        var $list = $(
          '<div class="vnx-dropdown-list w-[248px] p-2 left-0 top-[50px] absolute bg-white rounded shadow-[0px_1px_8px_0px_rgba(0,0,0,0.12)] inline-flex flex-col justify-start items-start z-20 max-h-[216px] overflow-auto border border-gray-200' +
          (self._dropdownOpenDuplicate?.[boxIdx] ? "" : " hidden") +
          '"></div>'
        );

        self.titles.forEach(function (title, idx) {
          if (
            !self.selectedServers.includes(idx) ||
            self.selectedServers[boxIdx] === idx
          ) {
            var isSelected = self.selectedServers[boxIdx] === idx;
            var $option = $(
              '<div class="self-stretch p-2 rounded inline-flex justify-start items-center gap-2 cursor-pointer transition-colors ' +
              (isSelected ? "bg-[#f2f2f4]" : "") +
              ' hover:bg-[#f2f2f4]"></div>'
            );
            $option.append(
              '<div class="flex-1 justify-start text-[#282828] text-base font-normal leading-normal">' +
              title +
              "</div>"
            );
            $option.on("click", function (e) {
              e.stopPropagation();
              self.selectedServers.splice(boxIdx, 1, idx);
              self._dropdownOpenDuplicate[boxIdx] = false;
              self.renderDropdownDuplicate(boxIdx); // chỉ render lại dropdown này
              self.changeIndexRow(idx, boxIdx); // cập nhật nội dung so sánh
            });
            $list.append($option);
          }
        });

        $button.on("click", function (e) {
          e.stopPropagation();
          self._dropdownOpenDuplicate = self._dropdownOpenDuplicate.map(
            function (v, i) {
              return i === boxIdx ? !v : false;
            }
          );
          self.renderDropdownDuplicate(boxIdx); // chỉ render lại dropdown này
        });

        $dropdown.append($button).append($list);
        $(
          ".compare_server .vnx_header_price_duplicate .swiper-slide:not(.swiper-slide-duplicate) .vnx_box_content"
        )
          .eq(boxIdx)
          .empty()
          .append($dropdown);
      },
      removeFilter: function () {
        this.isShowCompare = false;
        this.selectedServers = ["", "", "", ""];
        this.dropdownOpen = [false, false, false, false];
        this.$eventBus.$emit("removeCompare");
      },
      hasTwoValues: function (arr) {
        const nonEmpty = arr.filter((value) => value !== "");
        return nonEmpty.length >= 2;
      },
      handleDropdown: function (idx) {
        // Đóng tất cả, chỉ mở idx
        this.dropdownOpen = this.dropdownOpen.map((v, i) =>
          i === idx ? !v : false
        );
      },
      selectDropdown: function (serverIdx, dropdownIdx) {
        this.selectedServers.splice(dropdownIdx, 1, serverIdx);
        this.dropdownOpen.splice(dropdownIdx, 1, false);

        this.renderDropdownDuplicate(dropdownIdx);
      },
      getDropdownLabel: function (idx) {
        const val = this.selectedServers[idx];
        if (val !== "" && val !== null && val !== undefined) {
          return this.titles[val];
        }
        return "Chọn máy chủ";
      },
    },
  });
}