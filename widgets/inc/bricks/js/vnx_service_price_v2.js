window.jQuery = window.$ = jQuery;
$(document).ready(function () {
  if ($(".vnx-price_hosting_v2").length) {
    $(".vnx-price_hosting_v2").each(function () {
      var id_sec = "#" + $(this).attr("id");
      priceHostingV1(id_sec);
      const $parent = $(this).find(" .vnx-container-price-desktop"); // Tìm container liên quan
      Vnx_wrapDivs($parent);
      $(window).resize(function () {
        Vnx_wrapDivs($parent);
      });
      window.addEventListener("resize", function () {
        Object.keys(splideInstances).forEach(function (id_sec) {
          const splideInstance = splideInstances[id_sec];
          const newType = window.innerWidth < 991 ? "loop" : "slide";

          // Kiểm tra nếu `type` thay đổi, thì khởi tạo lại Splide
          if (splideInstance.options.type !== newType) {
            splideInstance.destroy(); // Xóa slider hiện tại
            const newSplide = initializeSplide(id_sec); // Tạo lại slider
            newSplide.mount();
            splideInstances[id_sec] = newSplide; // Cập nhật instance mới
          }
        });
      });
    });
  }
  if ($(".vnx-price-have-compare").length) {
    function initJS() {
      $(".vnx-price-have-compare").each(function () {
        var id_sec = "#" + $(this).attr("id");
        priceHostingV1(id_sec);
        const $parent = $(this).find(" .vnx-container-price-desktop"); // Tìm container liên quan
        Vnx_wrapDivs($parent);
        $(window).resize(function () {
          Vnx_wrapDivs($parent);
        });
        window.addEventListener("resize", function () {
          Object.keys(splideInstances).forEach(function (id_sec) {
            const splideInstance = splideInstances[id_sec];
            const newType = window.innerWidth < 991 ? "loop" : "slide";

            // Kiểm tra nếu `type` thay đổi, thì khởi tạo lại Splide
            if (splideInstance.options.type !== newType) {
              splideInstance.destroy(); // Xóa slider hiện tại
              const newSplide = initializeSplide(id_sec); // Tạo lại slider
              newSplide.mount();
              splideInstances[id_sec] = newSplide; // Cập nhật instance mới
            }
          });
        });
      });
    }
    // khởi tạo emit toàn cục
    Vue.prototype.$eventBus = new Vue();
    // khởi tạo vue
    new Vue({
      el: ".vnx-price-have-compare",
      data: {
        compare: ["", "", "", ""],
        titles: [],
      },
      mounted() {
        initJS();
        this.titles = this.getPricePackTitle();
        this.$eventBus.$on("removeCompare", () => {
           this.closeCompare();
        });


        setTimeout(() => {
        this.emitChangeCompare();
        }, 500); 

      },

      methods: {
        addCompare(item) {
          // Kiểm tra nếu item đã tồn tại trong compare (so sánh theo id nếu có)
          const isDuplicate = this.compare.some(
            (i) => i && i.id !== undefined && item.id !== undefined ? i.id === item.id : i === item
          );
          if (isDuplicate) {
            return;
          }

          // Tìm vị trí trống đầu tiên ("" hoặc null hoặc undefined)
          const emptyIndex = this.compare.findIndex(
            (i) => i === "" || i == null
          );

          if (emptyIndex !== -1) {
            // Nếu có chỗ trống thì gán item vào đó
            this.compare[emptyIndex] = item;
          } else if (this.compare.length < 4) {
            // Nếu không có chỗ trống nhưng còn dưới 4 phần tử, thì thêm mới
            this.compare.push(item);
          } else {
            console.warn("Đã đủ 4 phần tử, không thể thêm");
            return;
          }

          // Loại bỏ ô trống để sắp xếp chính xác, sau đó thêm lại
          this.compare = this.compare
            .filter((i) => i !== "" && i != null)
            .sort((a, b) => a.id - b.id); // Giả sử sắp theo `id`

          // Thêm lại các ô trống phía sau để đủ 4 phần tử
          while (this.compare.length < 4) {
            this.compare.push("");
          }

          this.emitChangeCompare();
        },
        removeCompare(index) {
          this.compare.splice(index, 1);
          while (this.compare.length < 4) {
            this.compare.push("");
          }
          this.emitChangeCompare();
        },
        closeCompare() {
          this.compare = ["", "", "", ""];
          this.emitChangeCompare();
        },
        emitChangeCompare() {
          this.$eventBus.$emit("changeCompare", this.compare);
        },
        emitCompareNow() {
          this.$eventBus.$emit("compareNow", this.compare);
          const element = document.getElementById("top-compare-table");
          if (element) {
            element.scrollIntoView({
              behavior: "smooth",
              block: "start",
            });
          }
        },
        getPricePackTitle: function () {
          return $(".compare_server .vnx_price_pack_title")
            .map(function () {
              return $(this).text().trim();
            })
            .get();
        },
        hasTwoValues: function () {
          const nonEmpty = this.compare.filter((value) => value !== "");
          return nonEmpty.length >= 2;
        },
      },
    });
  }

  if ($(".vnx-price_scroll").length) {
    priceScroll();
  }

  // table ssl
  if ($(".vnx-price_ssl").length) {
    $(".vnx-price_ssl").each(function () {
      var id_sec = "#" + $(this).attr("id");
      priceHostingV1(id_sec);
      const $parent = $(this).find(" .vnx-container-price-desktop"); // Tìm container liên quan
      Vnx_wrapDivs_column($parent);
      $(window).resize(function () {
        Vnx_wrapDivs_column($parent);
      });
      window.addEventListener("resize", function () {
        Object.keys(splideInstances).forEach(function (id_sec) {
          const splideInstance = splideInstances[id_sec];
          const newType = window.innerWidth < 991 ? "loop" : "slide";

          // Kiểm tra nếu `type` thay đổi, thì khởi tạo lại Splide
          if (splideInstance.options.type !== newType) {
            splideInstance.destroy(); // Xóa slider hiện tại
            const newSplide = initializeSplide(id_sec); // Tạo lại slider
            newSplide.mount();
            splideInstances[id_sec] = newSplide; // Cập nhật instance mới
          }
        });
      });
    });
  }
});

let splideInstances = {}; // Lưu trữ các Splide theo id

function initializeSplide(id) {
  const isMobile = window.innerWidth < 991;
  const indexActiveService = $(id + " .splide_table").data("start");
  return new Splide(id + " .splide_table", {
    type: isMobile ? "loop" : "slide",
    perPage: 1,
    perMove: 1,
    autoplay: false,
    interval: 3000,
    pagination: true,
    arrows: true,
    start: indexActiveService,
    drag: isMobile,
  });
}

function priceHostingV1(id_sec) {
  $(".vnx-warp-icon-copy").each(function () {
    $(this).on("click", function () {
      var code = $(this).data("code");
      navigator.clipboard.writeText(code);

      $(this).find(".icon-copy").addClass("hidden");
      $(this).find(".icon-paste").removeClass("hidden");

      setTimeout(() => {
        $(this).find(".icon-paste").addClass("hidden");
        $(this).find(".icon-copy").removeClass("hidden");
      }, 3000);
    });
  });

  const $container = $(id_sec);

  // Chỉ xử lý các phần tử trong phạm vi của id_sec
  $container.find(".vnx-cycle .vnx-container-item").each(function (index) {
    $(this)
      .off("click")
      .on("click", function () {
        // Xóa class 'active' khỏi tất cả các phần tử trong phạm vi
        $container
          .find(".vnx-cycle .vnx-container-item")
          .removeClass("vnx-active");

        // Thêm class 'active' vào phần tử được click
        $(this).addClass("vnx-active");

        // Hiện thông tin giá gốc
        $container.find(".vnx-container-price .vnx-sale").each(function () {
          $(this)
            .find(".vnx-original-price")
            .each(function (originalIndex) {
              $(this).addClass("hidden");
              if (index === originalIndex) {
                $(this).removeClass("hidden");
                // $(this).addClass("mr-3");
              }
            });

          $(this)
            .find(".vnx-label")
            .each(function (originalIndex) {
              $(this).addClass("hidden");

              if (index === originalIndex) {
                $(this).removeClass("hidden");
              }
            });
        });

        // Hiện thông tin giá khuyến mãi
        $container.find(".vnx-container-price .vnx-item").each(function () {
          $(this)
            .find(".vnx-warp-discount-price")
            .each(function (discountIndex) {
              $(this).addClass("hidden");
              if (index === discountIndex) {
                $(this).removeClass("hidden");
              }
            });
        });

        // Hiện thông nhẫn đặc biệt
        $container
          .find(".vnx-container-price .vnx-item .vnx-sale")
          .each(function () {
            $(this)
              .find(".vnx-label-special")
              .each(function (discountIndex) {
                $(this).addClass("hidden");
                if (index === discountIndex) {
                  $(this).removeClass("hidden");
                }
              });
          });

        // Hiện thông tin nút sản phẩm
        $container
          .find(".vnx-container-price .vnx-info-product")
          .each(function () {
            $(this)
              .find(".vnx-button")
              .each(function (discountIndex) {
                $(this).addClass("hidden");
                if (index === discountIndex) {
                  $(this).removeClass("hidden");
                }
              });
          });
      });
    // $container.find(".vnx-container-price .vnx-sale").each(function () {
    //   $(this)
    //     .find(".vnx-original-price")
    //     .each(function () {
    //       if ($(this).text().trim() !== "") {
    //         $(this).addClass("mr-3");
    //       } else {
    //         $(this).removeClass("mr-3").addClass("hidden");
    //       }
    //     });
    // });
  });

  // Hủy Splide cũ nếu đã tồn tại
  if (splideInstances[id_sec]) {
    splideInstances[id_sec].destroy();
  }

  // Khởi tạo Splide mới và lưu lại instance
  const splideInstance = initializeSplide(id_sec);
  splideInstance.mount();
  splideInstances[id_sec] = splideInstance;
}

function priceScroll() {
  $(".vnx-warp-icon-copy").each(function () {
    $(this).on("click", function () {
      var code = $(this).data("code");
      navigator.clipboard.writeText(code);

      $(this).find(".vnx-icon-copy").addClass("hidden");
      $(this).find(".vnx-icon-paste").removeClass("hidden");

      setTimeout(() => {
        $(this).find(".vnx-icon-paste").addClass("hidden");
        $(this).find(".vnx-icon-copy").removeClass("hidden");
      }, 3000);
    });
  });

  // khởi tạo PerfectScrollbar
  const elements = document.querySelectorAll(".vnx-sidebar-xscroll");
  elements.forEach((element) => {
    const ps = new PerfectScrollbar(element, {
      wheelSpeed: 0.5,
      minScrollbarLength: 20,
      swipeEasing: true,
    });
    ps.update();
  });
}

function Vnx_wrapDivs($parent) {
  // Lọc các div con không có class 'splide__slide--clone'
  const childDivs = $parent.children(
    ".vnx-warp-item-price:not(.splide__slide--clone)"
  );
  const screenWidth = $(window).width();

  // Lấy trạng thái bọc từ thuộc tính data
  const isWrapped = $parent.data("isWrapped") || false;

  if (screenWidth > 1024 && !isWrapped) {
    // Gỡ bỏ các div bọc cũ nếu tồn tại
    $parent
      .find(".vnx-wrapper-desktop")
      .children(".vnx-warp-item-price")
      .unwrap();

    // Tạo div bọc mới cho mỗi nhóm 3 div con
    for (let i = 0; i < childDivs.length; i += 3) {
      childDivs
        .slice(i, i + 3)
        .wrapAll("<div class='vnx-wrapper-desktop'></div>");
    }

    // Cập nhật trạng thái bọc
    $parent.data("isWrapped", true);
  } else if (screenWidth <= 1024 && isWrapped) {
    // Gỡ bỏ các div bọc khi màn hình nhỏ hơn hoặc bằng 1024px
    $parent
      .find(".vnx-wrapper-desktop")
      .children(".vnx-warp-item-price")
      .unwrap();

    // Cập nhật trạng thái bọc
    $parent.data("isWrapped", false);
  }
}

function Vnx_wrapDivs_column($parent) {
  // Lọc các div con không có class 'splide__slide--clone'
  const childDivs = $parent.children(
    ".vnx-warp-item-price:not(.splide__slide--clone)"
  );
  const screenWidth = $(window).width();

  // Lấy trạng thái bọc từ thuộc tính data
  const isWrapped = $parent.data("isWrapped") || false;

  if (screenWidth > 1024 && !isWrapped) {
    // Gỡ bỏ các div bọc cũ nếu tồn tại
    $parent
      .find(".vnx-wrapper-desktop")
      .children(".vnx-warp-item-price")
      .unwrap();
    // Tạo div bọc mới cho mỗi nhóm 3 div con
    for (let i = 0; i < childDivs.length; i += 4) {
      childDivs
        .slice(i, i + 4)
        .wrapAll("<div class='vnx-wrapper-desktop'></div>");
    }
    // Cập nhật trạng thái bọc
    $parent.data("isWrapped", true);
  } else if (screenWidth <= 991 && isWrapped) {
    // Gỡ bỏ các div bọc khi màn hình nhỏ hơn hoặc bằng 1024px
    $parent
      .find(".vnx-wrapper-desktop")
      .children(".vnx-warp-item-price")
      .unwrap();

    // Cập nhật trạng thái bọc
    $parent.data("isWrapped", false);
  }
}

Vue.prototype.$eventBus = new Vue();

document.querySelectorAll('.vnx-price-have-range').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      currentIndex: 0,
      isDragging: false,
      rangeCount: 0,
      rangeValues: [],
    },
    mounted() {
      this.initializeElements();
      this.rangeCount = this.$tabButtons.length;
      this.rangeValues = Array.from(this.$tabButtons).map(btn => btn.textContent.trim());
      this.updateFullUI(0);
    },
    methods: {
      initializeElements() {
        this.$tabButtons = this.$el.querySelectorAll('.vnx-range-tab-btn');
        this.$serviceInfoWrapperItems = this.$el.querySelectorAll('.vnx-service-info-wrapper-item');
        this.$serviceCards = this.$el.querySelectorAll('.vnx-service-card-wrapper .vnx-service-card');
        this.$imgTabs = this.$el.querySelectorAll('.vnx-img-tab-wrapper .vnx-img-tab');

        this.setupEventListeners();
      },

      setupEventListeners() {
        this.$tabButtons.forEach((button, i) => {
          button.addEventListener('click', () => {
            this.updateFullUI(i);
          });
        });
      },

      updateContentVisibility(index) {
        this.$serviceInfoWrapperItems.forEach((item, i) => {
          if (i === index) {
            item.classList.remove('hidden');
            item.classList.add('active');
          } else {
            item.classList.add('hidden');
            item.classList.remove('active');
          }
        });

        this.$serviceCards.forEach((card, i) => {
          if (i === index) {
            card.classList.remove('hidden');
            card.classList.add('active');
          } else {
            card.classList.add('hidden'); 
            card.classList.remove('active');
          }
        });

        this.$imgTabs.forEach((img, i) => {
          if (i === index) {
            img.classList.remove('hidden');
            img.classList.add('active');
          } else {
            img.classList.add('hidden');
            img.classList.remove('active');
          }
        });
      },

      updateVisualOnly(index) {
        this.$tabButtons.forEach((btn, i) => {
          const svgCircle = btn.querySelector('.svg-circle');

          if (i <= index) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }

          if (svgCircle) {
            if (i === index) {
              svgCircle.classList.add('active');
            } else {
              svgCircle.classList.remove('active');
            }
          }
        });
      },

      updateFullUI(index) {
        this.currentIndex = index;
        this.updateVisualOnly(index);
        this.updateContentVisibility(index);
      },
    },
  });
});

if ($(".vnx-price-email").length) {
    $(".vnx-price-email").each(function () {
      var id_sec = "#" + $(this).attr("id");
      priceHostingV1(id_sec);
      const $parent = $(this).find(" .vnx-container-price-desktop"); // Tìm container liên quan
      Vnx_wrapDivs($parent);
      $(window).resize(function () {
        Vnx_wrapDivs($parent);
      });
      window.addEventListener("resize", function () {
        Object.keys(splideInstances).forEach(function (id_sec) {
          const splideInstance = splideInstances[id_sec];
          const newType = window.innerWidth < 991 ? "loop" : "slide";

          // Kiểm tra nếu `type` thay đổi, thì khởi tạo lại Splide
          if (splideInstance.options.type !== newType) {
            splideInstance.destroy(); // Xóa slider hiện tại
            const newSplide = initializeSplide(id_sec); // Tạo lại slider
            newSplide.mount();
            splideInstances[id_sec] = newSplide; // Cập nhật instance mới
          }
        });
      });
    });
  }