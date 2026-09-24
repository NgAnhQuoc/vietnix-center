function formatData(data) {
  return data.map((resource) => {
    const defaultValue = Math.max(resource.min, resource.value || resource.min);
    resource.value = defaultValue - resource.min;
    resource.warning = "";
    return resource;
  });
}

if (
  document.querySelector(".vnx-dynamic-price-table.dynamic") &&
  window.vnxDynamicPriceTableData
) {
new Vue({
  el: ".vnx-dynamic-price-table.dynamic",
  data: {
    resources: formatData(window.vnxDynamicPriceTableData.mainResources) || [],
    //     resources= [
    //     {
    //         "key": "price-cpu",
    //         "label": "CPU",
    //         "value": 0,
    //         "max": 100,
    //         "step": 1,
    //         "price": 7000,
    //         "id": "twfznw",
    //         "min": "2",
    //         "desUnit": "Số lượng:"
    //     },
    //     {
    //         "key": "price-ram",
    //         "label": "RAM",
    //         "value": 0,
    //         "max": "512",
    //         "step": 1,
    //         "price": "12000",
    //         "unit": "GB",
    //         "desUnit": "Số lượng:"
    //         "id": "twqgfd",
    //         "min": "2"
    //     }
    // ]
    extraResources: formatData(window.vnxDynamicPriceTableData.extraResources) || [],
    showExtraResources: false,
    totalBill: 0,
    isLoading: false,
    noticeMessage: "",
  },

  mounted() {
    this.initInputRange();
    this.calculateTotalBill();
    this.handleBFCacheRestore();
  },
  watch: {
    resources: {
      deep: true,
      handler() {
        this.calculateTotalBill();
      },
    },
    extraResources: {
      deep: true,
      handler() {
        this.calculateTotalBill();
      },
    },
  },

  methods: {
    increment(index, extraResources = false) {
      const resources = extraResources ? this.extraResources : this.resources;
      const max = Number(resources[index].max) || 100;
      const currentValue = Number(resources[index].value) || 0;
      if (currentValue + 1 <= max - Number(resources[index].min)) {
        resources[index].value = currentValue + 1;
      }

      this.initInputRange();
    },

    decrement(index, extraResources = false) {
      const resources = extraResources ? this.extraResources : this.resources;
      const currentValue = Number(resources[index].value) || 0;
      if (currentValue > 0) {
        resources[index].value = currentValue - 1;
      }

      this.initInputRange();
    },

    onchangeInput(e, index, extraResources = false) {
      const resources = extraResources ? this.extraResources : this.resources;
      const min = Number(resources[index].min);
      const max = Number(resources[index].max) - min;
      const inputValue = Number(e.target.value);
      const value = inputValue - min;

      if (value < 0) {
        this.noticeMessage = `Cần mua tối thiểu <strong>${min} ${resources[index].unit ?? ""} ${
          this.trimTitle(resources[index].label) ?? ""
        }</strong>`;
        resources[index].value = 0;

        // thoát focus input
        e.target.blur();
      } else if (value > max) {
        resources[index].value = max;
        this.noticeMessage = `Được mua tối đa <strong>${max + min} ${resources[index].unit ?? ""} ${
          this.trimTitle(resources[index].label) ?? ""
        }</strong>`;
        e.target.blur();
      } else {
        resources[index].value = value;
      }

      this.initInputRange();
    },

    resetMessage() {
      this.noticeMessage = "";
    },

    initInputRange() {
      const container = document.querySelectorAll(".range-slider");
      for (let i = 0; i < container.length; i++) {
        const slider = container[i].querySelector(".slider");
        const thumb = container[i].querySelector(".slider-thumb");
        const tooltip = container[i].querySelector(".tooltip");
        const progress = container[i].querySelector(".progress");
        const customSlider = () => {
          const minVal = slider.min || 0;
          const maxVal = slider.max || 100;
          const value = Number(
            this.resources[i]?.value || this.extraResources[i - this.resources.length]?.value || 0
          );
          const percent =
            Math.max(
              0,
              Math.min(1, (value - Number(minVal)) / (Number(maxVal) - Number(minVal)))
            ) || 0;
          const sliderWidth = slider.offsetWidth;
          const thumbWidth = thumb.offsetWidth;
          const left = percent * (sliderWidth - thumbWidth);
          const progressWidth = left + thumbWidth / 2;
          const progressPercent = (progressWidth / sliderWidth) * 100;
          thumb.style.left = left + "px";
          progress.style.width = Math.max(0, Math.min(100, progressPercent)) + "%";
        };
        tooltip.style.display = "none";

        customSlider();

        slider.addEventListener("focus", () => {
          tooltip.style.display = "block";
        });
        slider.addEventListener("blur", () => {
          tooltip.style.display = "none";
        });
        slider.addEventListener("input", () => {
          customSlider();
          window.addEventListener("resize", customSlider);
        });
      }
    },

    calculateTotalBill() {
      this.totalBill = 0;
      this.resources.forEach((resource) => {
        const price =
          parseFloat(resource.price.toString().replace(/\./g, "").replace(/,/g, ".")) || 0;
        const val = parseFloat(resource.value) || 0;
        const min = parseFloat(resource.min) || 0;
        this.totalBill += price * (val + min);
      });
      this.extraResources.forEach((resource) => {
        const price =
          parseFloat(resource.price.toString().replace(/\./g, "").replace(/,/g, ".")) || 0;
        const val = parseFloat(resource.value) || 0;
        const min = parseFloat(resource.min) || 0;
        this.totalBill += price * (val + min);
      });
    },

    formatCurrency(value) {
      return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
    },
    toggleExtraResources() {
      this.showExtraResources = !this.showExtraResources;
    },
    trimTitle(title) {
      const index = title.indexOf("(");
      return index !== -1 ? title.slice(0, index).trim() : title;
    },

    // Reset trạng thái loading khi trang được khôi phục từ bfcache (bấm Back)
    handleBFCacheRestore() {
      window.addEventListener("pageshow", (event) => {
        if (event.persisted) {
          this.isLoading = false;
        }
      });
    },

    //orderProduct
    async orderProduct() {
      if (this.isLoading) return;
      this.isLoading = true;

      const pid = window.vnxDynamicPriceTableData.pid || 0;
      if (pid == 0) {
        console.log("Vui lòng cấu hình ID sản phẩm");
        this.isLoading = false;
        return;
      }

      const options = [];
      this.resources.forEach((resource) => {
        const val = parseFloat(resource.value) || 0;
        const min = parseFloat(resource.min) || 0;
        options.push({
          name: resource.key,
          value: val + min,
        });
      });
      this.extraResources.forEach((resource) => {
        const val = parseFloat(resource.value) || 0;
        const min = parseFloat(resource.min) || 0;
        options.push({
          name: resource.key,
          value: val + min,
        });
      });

      //convert to object
      const optionsObj = {};
      options.forEach((option) => {
        optionsObj[option.name] = option.value;
      });

      // Tạo form để submit thay vì AJAX
      const form = document.createElement("form");
      form.method = "POST";
      form.action = vietnix_order_product.ajax_url;
      form.style.display = "none";

      // Tạo hidden inputs
      const fields = {
        action: "vietnix_order_product_center",
        nonce: vietnix_order_product.nonce,
        pid: pid,
        billingcycle: window.vnxDynamicPriceTableData.billingcycle,
      };

      // Thêm các field cơ bản
      for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = value;
        form.appendChild(input);
      }

      // Thêm options
      for (const [key, value] of Object.entries(optionsObj)) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = `options[${key}]`;
        input.value = value;
        form.appendChild(input);
      }

      // Thêm form vào body và submit
      document.body.appendChild(form);
      form.submit();
      // Gỡ form khỏi DOM để không tích lũy khi quay lại trang từ bfcache
      form.remove();
    },
  },
});
}

// Bản Static (Cấu hình tài nguyên + Ước tính chi phí, có Router miễn phí đơn vị đầu)
if (document.querySelector(".vnx-dynamic-price-table.vnx-dynamic-price-table-static") && window.vnxDynamicPriceTableData) {
  new Vue({
    el: ".vnx-dynamic-price-table.vnx-dynamic-price-table-static",
    data: {
      resources: formatData(window.vnxDynamicPriceTableData.mainResources) || [],
      extraResources: formatData(window.vnxDynamicPriceTableData.extraResources) || [],
      showExtraResources: false,
      totalBill: 0,
      isLoading: false,
    },

    mounted() {
      this.initInputRange();
      this.calculateTotalBill();
      this.handleBFCacheRestore();
    },
    watch: {
      resources: {
        deep: true,
        handler() {
          this.calculateTotalBill();
        },
      },
      extraResources: {
        deep: true,
        handler() {
          this.calculateTotalBill();
        },
      },
    },

    methods: {
      // Hiện cảnh báo dạng inline (màu cam) ngay dưới resource, tự ẩn sau vài giây
      flashWarning(resources, index, message) {
        const resource = resources[index];
        if (resource._warningTimeout) {
          clearTimeout(resource._warningTimeout);
        }
        resource.warning = message;
        resource._warningTimeout = setTimeout(() => {
          resource.warning = "";
        }, 3000);
      },

      increment(index, extraResources = false) {
        const resources = extraResources ? this.extraResources : this.resources;
        const max = Number(resources[index].max) || 100;
        const currentValue = Number(resources[index].value) || 0;
        if (currentValue + 1 <= max - Number(resources[index].min)) {
          resources[index].value = currentValue + 1;
          resources[index].warning = "";
        }

        this.initInputRange();
      },

      decrement(index, extraResources = false) {
        const resources = extraResources ? this.extraResources : this.resources;
        const currentValue = Number(resources[index].value) || 0;
        if (currentValue > 0) {
          resources[index].value = currentValue - 1;
          resources[index].warning = "";
        }

        this.initInputRange();
      },

      onchangeInput(e, index, extraResources = false) {
        const resources = extraResources ? this.extraResources : this.resources;
        const min = Number(resources[index].min);
        const max = Number(resources[index].max) - min;
        const inputValue = Number(e.target.value);
        const value = inputValue - min;
        const unit = resources[index].unit ?? "";
        const label = this.trimTitle(resources[index].label) ?? "";

        if (value < 0) {
          resources[index].value = 0;
          this.flashWarning(resources, index, `Cần mua tối thiểu ${min} ${unit} ${label}`);

          // thoát focus input
          e.target.blur();
        } else if (value > max) {
          resources[index].value = max;
          this.flashWarning(resources, index, `Chọn tối đa ${max + min} ${unit} ${label}`);
          e.target.blur();
        } else {
          resources[index].value = value;
          resources[index].warning = "";
        }

        this.initInputRange();
      },

      initInputRange() {
        const container = this.$el.querySelectorAll(".range-slider");
        for (let i = 0; i < container.length; i++) {
          const slider = container[i].querySelector(".slider");
          const thumb = container[i].querySelector(".slider-thumb");
          const tooltip = container[i].querySelector(".tooltip");
          const progress = container[i].querySelector(".progress");
          const customSlider = () => {
            const minVal = slider.min || 0;
            const maxVal = slider.max || 100;
            const value = Number(
              this.resources[i]?.value || this.extraResources[i - this.resources.length]?.value || 0
            );
            const percent =
              Math.max(
                0,
                Math.min(1, (value - Number(minVal)) / (Number(maxVal) - Number(minVal)))
              ) || 0;
            const sliderWidth = slider.offsetWidth;
            const thumbWidth = thumb.offsetWidth;
            const left = percent * (sliderWidth - thumbWidth);
            const progressWidth = left + thumbWidth / 2;
            const progressPercent = (progressWidth / sliderWidth) * 100;
            thumb.style.left = left + "px";
            progress.style.width = Math.max(0, Math.min(100, progressPercent)) + "%";
          };
          tooltip.style.display = "none";

          customSlider();

          slider.addEventListener("focus", () => {
            tooltip.style.display = "block";
          });
          slider.addEventListener("blur", () => {
            tooltip.style.display = "none";
          });
          slider.addEventListener("input", () => {
            customSlider();
            window.addEventListener("resize", customSlider);
          });
        }
      },

      // Router: 1 đơn vị đầu (min) được miễn phí đi kèm, chỉ tính tiền phần vượt (value)
      isRouterResource(resource) {
        return !!(resource && resource.key && resource.key.toString().toLowerCase().includes("router"));
      },

      resourceCost(resource) {
        const price =
          parseFloat(resource.price.toString().replace(/\./g, "").replace(/,/g, ".")) || 0;
        const val = parseFloat(resource.value) || 0;
        const min = parseFloat(resource.min) || 0;
        const billedQty = this.isRouterResource(resource) ? val : val + min;
        return price * billedQty;
      },

      calculateTotalBill() {
        this.totalBill = 0;
        this.resources.forEach((resource) => {
          this.totalBill += this.resourceCost(resource);
        });
        this.extraResources.forEach((resource) => {
          this.totalBill += this.resourceCost(resource);
        });
      },

      formatCurrency(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
      },
      toggleExtraResources() {
        this.showExtraResources = !this.showExtraResources;
      },
      trimTitle(title) {
        const index = title.indexOf("(");
        return index !== -1 ? title.slice(0, index).trim() : title;
      },

      // Reset trạng thái loading khi trang được khôi phục từ bfcache (bấm Back)
      handleBFCacheRestore() {
        window.addEventListener("pageshow", (event) => {
          if (event.persisted) {
            this.isLoading = false;
          }
        });
      },

      //orderProduct
      async orderProduct() {
        if (this.isLoading) return;
        this.isLoading = true;

        const pid = window.vnxDynamicPriceTableData.pid || 0;
        if (pid == 0) {
          console.log("Vui lòng cấu hình ID sản phẩm");
          this.isLoading = false;
          return;
        }

        const options = [];
        this.resources.forEach((resource) => {
          const val = parseFloat(resource.value) || 0;
          const min = parseFloat(resource.min) || 0;
          options.push({
            name: resource.key,
            value: val + min,
          });
        });
        this.extraResources.forEach((resource) => {
          const val = parseFloat(resource.value) || 0;
          const min = parseFloat(resource.min) || 0;
          options.push({
            name: resource.key,
            value: val + min,
          });
        });

        //convert to object
        const optionsObj = {};
        options.forEach((option) => {
          optionsObj[option.name] = option.value;
        });

        // Tạo form để submit thay vì AJAX
        const form = document.createElement("form");
        form.method = "POST";
        form.action = vietnix_order_product.ajax_url;
        form.style.display = "none";

        // Tạo hidden inputs
        const fields = {
          action: "vietnix_order_product_center",
          nonce: vietnix_order_product.nonce,
          pid: pid,
          billingcycle: window.vnxDynamicPriceTableData.billingcycle,
        };

        // Thêm các field cơ bản
        for (const [key, value] of Object.entries(fields)) {
          const input = document.createElement("input");
          input.type = "hidden";
          input.name = key;
          input.value = value;
          form.appendChild(input);
        }

        // Thêm options
        for (const [key, value] of Object.entries(optionsObj)) {
          const input = document.createElement("input");
          input.type = "hidden";
          input.name = `options[${key}]`;
          input.value = value;
          form.appendChild(input);
        }

        // Thêm form vào body và submit
        document.body.appendChild(form);
        form.submit();
        // Gỡ form khỏi DOM để không tích lũy khi quay lại trang từ bfcache
        form.remove();
      },
    },
  });
}
