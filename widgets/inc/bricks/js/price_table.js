window.jQuery = window.$ = jQuery;
$(document).ready(function () {
  vnxPriceTable();
  vnxButtonLoadmore();
  vnxButtonEstimating();
  vnxTablePackage();
});
function vnxPriceTable() {
  bricksQuerySelectorAll(document, ".brxe-vnx-table").forEach(function (
    element
  ) {
    if (
      $(element).hasClass("is-active-element") ||
      !$(element).hasClass("is-initialized")
    ) {
      view = vnxPriceTableFn(element);
    }
  });
}
const vnxPriceTableFn = function (element) {
  function addLineBreakAfterFirstWord(inputString) {
    var trimmedString = inputString.trim();
    var words = trimmedString.split(" ");
    words.splice(1, 0, "<br>");
    var modifiedString = words.join(" ");
    return modifiedString;
  }
  $(element).on("click", ".vnx-tab", function (e) {
    e.preventDefault();
    const thisTarget = $(this).attr("data-target");
    $(this).parent().find(".tab-active").removeClass("tab-active");
    $(this).addClass("tab-active");
    $(element).find(".vnx-tab-content.is_active").removeClass("is_active");
    $(element)
      .find("." + thisTarget)
      .addClass("is_active");
  });

  // Add conversion value for register button
  $(".vnx-btn-conversion").click(function (event) {
    event.preventDefault();
    if ($(this).closest(".vnx_not_loading_btn").length == 0) {
      $(element)
        .find(".vnx-btn-conversion.loading")
        .each(function (index, thisE) {
          $(thisE).removeClass("loading");
          $(thisE).html("Đăng ký");
        });
      const thisBtn = $(this);
      $(thisBtn).addClass("loading");
      $(thisBtn).html(
        '<img class="loading_icon" src="' + vnx_table.loading_icon + '">'
      );
      setTimeout(function () {
        $(thisBtn).removeClass("loading");
        $(thisBtn).html("Đăng ký");
      }, 6000);
    }
    try {
      const price = event.target.attributes["data-price"].value;
      const cartPrice = price.replace(/\$|\,|đ|\./gi, "");
      window.dataLayer.push({ event: "cartOrder", cartPrice: cartPrice });
    } catch (error) {
      console.log(error);
      console.log("Can't add conversion value for register button.");
    }
    window.open(event.target.href, "_self");
  });

  $(element).on("click", ".vnx_expand_btn", function (e) {
    e.preventDefault();
    const thisTarget = $(this).parent().find(".default-hidden");
    $(thisTarget).toggleClass("hidden");
    $(this).toggleClass("active");
  });

  if ($(element).hasClass("compare_wp_hosting")) {
    $(element).on("click", ".vnx-custom-btn-expand", function (e) {
      $(this).toggleClass("expanding");
      $(this).parent().find(".el-custom-default-hidden").toggle();
      $(this)
        .siblings()
        .find("tbody.vnx_wp_host_mobile")
        .toggleClass("vnx-fixed-height");
    });

    $(".vnx-price-name").each(function () {
      $(this).html(addLineBreakAfterFirstWord($(this).text()));
    });
  }

  $(element).addClass("is-initialized");

  console.log("Script initialized");
};
function vnxButtonLoadmore() {
  $(".vnx-tab-content").each(function () {
    $(this).find(".vnx_table_mobile:gt(2)").hide();
  });

  $(".vnx_button_loadmore").click(function () {
    $(this).parent().parent().find(".vnx_table_mobile").show();
    $(this).parent().parent().find(".vnx_div_loadmore").addClass("hidden");
  });
}
function vnxButtonEstimating() {
  $(".icon-estimating.mobile").click(function () {
    if ($(this).find(".estimating-cost").hasClass("show")) {
      $(this).find(".estimating-cost").addClass("hidden");
      $(this).find(".estimating-cost").removeClass("show");
    } else {
      $(this).find(".estimating-cost").addClass("show");
      $(this).find(".estimating-cost").removeClass("hidden");
    }
  });
}
function vnxTablePackage() {
  $(".vnx-table-price-hosting").each(function () {
    var text = $(this).find(".vnx-select-option.active span.title").text();
    var active_url = $(this).find(".vnx-select-option.active").attr("data-url");
    var price = $(this).find(".vnx-select-option.active span.el-custom-text-price").attr("id-price");
    var url_id = $(this)
      .find("#" + active_url)
      .attr("id");
    $(this).find(".vnx-button-select p.text").text(text);
    $(this).find(" a.vnx-button-register").attr("data-period", text);
    $(this).find(" a.vnx-button-register").attr("data-price", price);
    if (active_url == url_id) {
      var url = $(this)
        .find("#" + url_id)
        .text();
      $(this).find(" a.vnx-button-register.url-register").attr("href", url);
    }
  });

  $(".vnx-select-option").on("click", function (event) {
    var text = $(this).find("span.title").text();
    var price = $(this).find("span.el-custom-text-price").attr("id-price");
    $(this).parent().parent().find(".vnx-button-select p.text").text(text);
    $(this)
      .closest(".vnx-table-price-hosting")
      .find("a.vnx-button-register.url-register")
      .attr("data-period", text);
    $(this)
      .closest(".vnx-table-price-hosting")
      .find("a.vnx-button-register.url-register")
      .attr("data-price", price);
    var id_cyc = $(this).attr("data-id");
    var id_cyc_url = $(this).attr("data-url");
    var id_url = $(this)
      .closest(".vnx-table-price-hosting")
      .find("#" + id_cyc_url)
      .attr("id");
    var id_price = $(this)
      .closest(".vnx-table-price-hosting")
      .find("#" + id_cyc)
      .attr("id");
    if (id_cyc == id_price) {
      $(this)
        .closest(".vnx-table-price-hosting")
        .find(".price-row")
        .removeClass("show");
      $(this)
        .closest(".vnx-table-price-hosting")
        .find(".price-row span.el-custom-text-price")
        .removeClass("show");
      $(this)
        .closest(".vnx-table-price-hosting")
        .find(".price-row")
        .addClass("hidden");
      $(this)
        .closest(".vnx-table-price-hosting")
        .find("#" + id_cyc)
        .removeClass("hidden");
      $(this)
        .closest(".vnx-table-price-hosting")
        .find("#" + id_cyc)
        .addClass("show");
      $(this)
        .closest(".vnx-table-price-hosting")
        .find("#" + id_cyc + " span.el-custom-text-price")
        .removeClass("hidden");
      $(this)
        .closest(".vnx-table-price-hosting")
        .find("#" + id_cyc + " span.el-custom-text-price")
        .addClass("show");
    }
    if (id_cyc_url == id_url) {
      var url = $(this)
        .closest(".vnx-table-price-hosting")
        .find("#" + id_cyc_url)
        .text();
      $(this)
        .closest(".vnx-table-price-hosting")
        .find("a.vnx-button-register.url-register")
        .attr("href", url);
    }
    $(this).closest(".hidden-table-select").removeClass("open");
  });

  $(".vnx-table-price-hosting .hidden-table-select").hide();
  $(".vnx-table-price-hosting .vnx-button-select").on(
    "click",
    function (event) {
      $(this)
        .closest(".vnx-table-price-hosting")
        .find(".hidden-table-select")
        .toggleClass("open");
    }
  );
}

document.querySelectorAll(".brxe-vnx-table.domain_price_v3").forEach((el) => {
  new Vue({
    el: `#${el.id}`,
    data: () => ({
      isDown: false,
      startX: 0,
      scrollLeft: 0,
      showAll: false,
      maxRows: 20,
    }),
    mounted() {
      this.checkRows();

      const slider = this.$el;
      slider.addEventListener("mousedown", this.startDrag);
      slider.addEventListener("mouseleave", this.stopDrag);
      slider.addEventListener("mouseup", this.stopDrag);
      slider.addEventListener("mousemove", this.doDrag);
    },
    methods: {

      checkRows() {
        const tbody = this.$el.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const btnMore = this.$el.querySelector(".vnx_more_button:first-child");
        const btnLess = this.$el.querySelector(".vnx_more_button:last-child");

        if (rows.length > this.maxRows) {
          // Ẩn các dòng ở giữa (giữ lại 20 dòng đầu và dòng cuối)
          rows.forEach((row, index) => {
            if (index >= this.maxRows && index < rows.length - 1) {
              row.style.display = "none";
            }
          });

          // Hiện nút "Xem thêm", ẩn nút "Thu gọn"
          btnMore.style.display = "flex";
          btnLess.style.display = "none";

          // Gắn sự kiện click cho nút "Xem thêm"
          btnMore.addEventListener("click", () => {
            this.showAll = true;
            rows.forEach((row) => (row.style.display = ""));
            btnMore.style.display = "none";
            btnLess.style.display = "flex";
          });

          // Gắn sự kiện click cho nút "Thu gọn"
          btnLess.addEventListener("click", () => {
            this.showAll = false;
            rows.forEach((row, index) => {
              if (index >= this.maxRows && index < rows.length - 1) {
                row.style.display = "none";
              }
            });
            btnMore.style.display = "flex";
            btnLess.style.display = "none";
          });
        }
      },
      startDrag(e) {
        console.log("startDrag");
        this.isDown = true;
        this.startX = e.pageX - this.$el.offsetLeft;
        this.scrollLeft = this.$el.scrollLeft;
      },
      stopDrag() {
        this.isDown = false;
      },
      doDrag(e) {
        if (!this.isDown) return;
        e.preventDefault();
        const x = e.pageX - this.$el.offsetLeft;
        const walk = (x - this.startX) * 1; // tốc độ kéo
        this.$el.scrollLeft = this.scrollLeft - walk;
      },
    },
  });
});