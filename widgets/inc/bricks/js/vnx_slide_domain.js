window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + "/wp-admin/admin-ajax.php";
Vue.prototype.$eventBus = new Vue();
document.querySelectorAll(".brxe-vnx-slide-domain-v1.vnx_element").forEach(el => {
  new Vue({
    el: `#${el.id}`,
    data: {
      splide: null,
      datadestroy: 0,
      datascroll: false,
      isDragging: false,
      startX: 0,
      scrollLeft: 0
    },
    mounted() {
      this.checkScreen(); // Kiểm tra ngay khi trang load
      this.getDestroyValue();
      this.createSlider();
      window.addEventListener("resize", this.checkScreen);
    },
    beforeUnmount() {
      this.destroySlider();
      window.removeEventListener("resize", this.checkScreen);
    },
    methods: {
      getDestroyValue() {
        var id_section = `#${this.$el.id}`;
        var splideElement = document.querySelector(id_section + " .vnx_box_listslide");
        if (splideElement) {
          this.datadestroy = parseInt(splideElement.getAttribute("data-vnx_destroy"), 10) || 0;
        }
      },

      createSlider() {
        var id_section = '#' + this.$el.id;
        var splideElement = document.querySelector(id_section + " .vnx_box_listslide");
        if (!splideElement) return;
        this.destroySlider();
        if (window.innerWidth <= this.datadestroy) {
          splideElement.classList.remove("splide");
          this.$el.querySelector(".splide__list")?.classList.add("vnx_scroll");
          return;
        }
        splideElement.classList.add("splide");
        this.$el.querySelector(".splide__list")?.classList.remove("vnx_scroll");
        var splide_options = $(splideElement).data("splide");
        this.splide = new Splide(id_section + " .vnx_box_listslide", splide_options);
        this.splide.mount();
      },

      destroySlider() {
        if (this.splide) {
          this.splide.destroy();
          this.splide = null;
        }
      },

      checkScreen() {
        this.getDestroyValue(); // Đảm bảo giá trị `datadestroy` chính xác
        this.destroySlider(); // Hủy slider trước khi kiểm tra kích thước
        this.createSlider();
        this.datascroll = window.innerWidth <= this.datadestroy;
      },
    }
  });
});
