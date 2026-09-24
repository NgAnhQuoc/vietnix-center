/**
 * Tối ưu hóa & Hợp nhất logic Calculator Object Storage (V1 & V2)
 * - Tách script khỏi file PHP sang file JS dùng chung.
 * - Hỗ trợ nhiều instance trên cùng một trang bằng cách dùng data-settings.
 */

document.addEventListener("DOMContentLoaded", function () {
  // 1. Khởi tạo các widget chuẩn V1
  document.querySelectorAll(".vnx-dynamic-price-obj-storage").forEach((el) => {
    const settings = JSON.parse(el.getAttribute("data-settings") || "{}");
    initV1(el, settings);
  });

  // 2. Khởi tạo các widget chuẩn V2
  document.querySelectorAll(".vnx-obj-v2").forEach((el) => {
    const settings = JSON.parse(el.getAttribute("data-settings") || "{}");
    initV2(el, settings);
  });
});

/**
 * ==========================================
 * LOGIC CHO WIDGET V1
 * ==========================================
 */
function initV1(el, rawSettings) {
  if (typeof Vue === "undefined") return;

  function formatInitialData(data) {
    if (!data) return null;
    return {
      ...data,
      value: Number(data.value || 0) - Number(data.min || 0),
    };
  }

  new Vue({
    el: el,
    data: {
      resource: formatInitialData(rawSettings) || {},
      sortedPrices: [],
      totalBill: 0,
      isLoading: false,
      currentPrice: 0,
      selectedPriceIndex: -1,
      noticeMessage: "",
    },
    mounted() {
      this.prepareData();
      this.setupSliderEvents();
      this.refreshAll();
    },
    watch: {
      "resource.value": function () {
        this.refreshAll();
      },
    },
    methods: {
      prepareData() {
        if (this.resource?.prices && Array.isArray(this.resource.prices)) {
          this.sortedPrices = [...this.resource.prices]
            .map((item, index) => ({ ...item, originalIndex: index }))
            .sort((a, b) => Number(a.range) - Number(b.range));
        }
      },
      refreshAll() {
        this.pickCurrentPrice();
        this.calculateTotalBill();
        this.$nextTick(() => {
          this.updateSliderStyles();
        });
      },
      increment() {
        const step = Number(this.resource.step) || 1;
        const maxOffset = (Number(this.resource.max) || 0) - (Number(this.resource.min) || 0);
        const currentValue = Number(this.resource.value) || 0;
        this.resource.value = Math.min(maxOffset, currentValue + step);
      },
      decrement() {
        const step = Number(this.resource.step) || 1;
        const currentValue = Number(this.resource.value) || 0;
        this.resource.value = Math.max(0, currentValue - step);
      },
      onchangeInput(e) {
        const min = Number(this.resource.min) || 0;
        const max = Number(this.resource.max) || 0;
        const inputValue = Number(e.target.value) || 0;
        if (inputValue < min) {
          this.noticeMessage = `Cần mua tối thiểu <strong>${min} ${this.resource.unit ?? ""}</strong>`;
          this.resource.value = 0;
          e.target.blur();
        } else if (inputValue > max) {
          this.resource.value = max - min;
          this.noticeMessage = `Được mua tối đa <strong>${max} ${this.resource.unit ?? ""}</strong>`;
          e.target.blur();
        } else {
          this.resource.value = inputValue - min;
          this.noticeMessage = "";
        }
      },
      resetMessage() {
        this.noticeMessage = "";
      },
      setupSliderEvents() {
        const slider = this.$el.querySelector(".range-slider .slider");
        const tooltip = this.$el.querySelector(".range-slider .tooltip");
        if (!slider || !tooltip) return;
        slider.addEventListener("focus", () => (tooltip.style.display = "block"));
        slider.addEventListener("blur", () => (tooltip.style.display = "none"));
        slider.addEventListener("input", this.updateSliderStyles);
        window.addEventListener("resize", this.updateSliderStyles);
      },
      updateSliderStyles() {
        const container = this.$el.querySelector(".range-slider");
        if (!container) return;
        const slider = container.querySelector(".slider");
        const thumb = container.querySelector(".slider-thumb");
        const progress = container.querySelector(".progress");
        if (!slider || !thumb || !progress) return;
        const minVal = Number(slider.min) || 0;
        const maxVal = Number(slider.max) || 100;
        const value = Number(this.resource?.value || 0);
        const percent = Math.max(0, Math.min(1, (value - minVal) / (maxVal - minVal))) || 0;
        const left = percent * (slider.offsetWidth - thumb.offsetWidth);
        const progressPercent = ((left + thumb.offsetWidth / 2) / slider.offsetWidth) * 100;
        thumb.style.left = `${left}px`;
        progress.style.width = `${Math.max(0, Math.min(100, progressPercent))}%`;
      },
      calculateTotalBill() {
        const totalQuantity = (Number(this.resource.value) || 0) + (Number(this.resource.min) || 0);
        let applicablePrice = 0;
        if (this.selectedPriceIndex >= 0 && this.resource.prices?.[this.selectedPriceIndex]) {
          applicablePrice = Number(this.resource.prices[this.selectedPriceIndex].price) || 0;
        } else {
          applicablePrice = Number(this.resource.price) || 0;
        }
        this.totalBill = applicablePrice * totalQuantity;
      },
      pickCurrentPrice() {
        if (!this.sortedPrices.length) {
          this.selectedPriceIndex = -1;
          this.currentPrice = 0;
          return;
        }
        const totalValue = Number(this.resource.value) + Number(this.resource.min);
        let found = this.sortedPrices[this.sortedPrices.length - 1];
        for (const item of this.sortedPrices) {
          if (totalValue <= Number(item.range)) {
            found = item;
            break;
          }
        }
        this.selectedPriceIndex = found.originalIndex;
        this.currentPrice = Number(found.price) || 0;
      },
      formatCurrency(value) {
        return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" })
          .format(value)
          .replace(/\s₫/, "đ");
      },
      orderProduct() {
        if (this.isLoading) return;
        let pid = this.resource.pid || 0;
        if (this.selectedPriceIndex >= 0 && this.resource.prices?.[this.selectedPriceIndex]) {
          pid = this.resource.prices[this.selectedPriceIndex].pid || pid;
        }
        const orderConfig = window.vietnix_order_product || window.vnxAppConfig || {};
        if (!pid) {
          alert("Vui lòng cấu hình ID sản phẩm");
          return;
        }
        this.isLoading = true;
        const form = document.createElement("form");
        form.method = "POST";
        form.action = orderConfig.ajax_url || orderConfig.ajaxurl || window.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php');
        form.style.display = "none";
        const fields = {
          action: "vietnix_order_product_center",
          nonce: orderConfig.nonce || "",
          pid: pid,
          billingcycle: this.resource.billingcycle,
          "options[user_quota]": Number(this.resource.value) + Number(this.resource.min),
        };
        for (const [key, val] of Object.entries(fields)) {
          const input = document.createElement("input");
          input.type = "hidden";
          input.name = key;
          input.value = val;
          form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
        // Gỡ form khỏi DOM để không tích lũy khi quay lại trang từ bfcache
        form.remove();
      },
    },
  });
}

/**
 * ==========================================
 * LOGIC CHO WIDGET V2 (Dynamic Resource)
 * ==========================================
 */
function initV2(el, rawSettings) {
  if (typeof Vue === "undefined") return;

  /* ─── Helpers ─── */
  function findField(type) {
    var fields = rawSettings.storage_field || [];
    for (var i = 0; i < fields.length; i++) {
        if (fields[i].type === type) return fields[i];
    }
    return {};
  }

  var storageFld = findField('storage');
  var dtFld = findField('data_transfer');
  var reqFld = findField('request');

  var TIER_COLORS = [
    { tagBg: 'bg-[#d6f2ff]', tagColor: 'text-[#085fc5]' },
    { tagBg: 'bg-[#e6f9f0]', tagColor: 'text-[#0a7a45]' },
    { tagBg: 'bg-[#fff3e0]', tagColor: 'text-[#b45000]' },
    { tagBg: 'bg-[#f3e8ff]', tagColor: 'text-[#7e22ce]' },
  ];

  var STORAGE_TIERS = (storageFld.prices || []).map(function(tier, i) {
    var col = TIER_COLORS[i] || TIER_COLORS[TIER_COLORS.length - 1];
    return {
        priceType: tier.range_price || 'between',
        actionPrice: tier.action_price || 'price',
        rangeFrom: parseFloat(tier.range || 0),
        rangeTo: parseFloat(tier.range_to || 0),
        price: (tier.action_price === 'contact') ? null : (tier.price ? parseFloat(tier.price) : null),
        label: tier.title || '',
        tagBg: col.tagBg,
        tagColor: col.tagColor,
        idContactBtn: tier.id_contact_btn || '',
    };
  });

  var DT_TIERS = (function() {
    var dtPrices = dtFld.prices || [];
    if (dtPrices.length > 0) {
        return dtPrices.map(function(tier) {
            var to = (tier.range_price === 'greater_than') ? Infinity : parseFloat(tier.range_to || tier.range || 0);
            return { upTo: to, price: parseFloat(tier.price || 0) };
        }).sort((a, b) => a.upTo - b.upTo);
    }
    return [
        { upTo: 1024, price: 240 },
        { upTo: 5120, price: 220 },
        { upTo: Infinity, price: 180 },
    ];
  }());

  var DT_FREE_QUOTA_RATIO = parseFloat(dtFld.free_quota_ratio || 10);
  var REQ_FREE_TR = parseFloat(reqFld.min || 103680000) / 1000000;
  var REQ_UNIT_PRICE = parseFloat(reqFld.unit_price || 50000);

  var CYCLES = (rawSettings.cycle_field || []).map(function(c) {
    var months = parseInt(c.months || 1);
    var discount = parseFloat(c.percent || 0);
    return {
        label: c.title,
        months: months,
        multiplier: parseFloat((months * (1 - discount / 100)).toFixed(2)),
        discount: discount,
        pid: c.pid
    };
  });

  function fmt(n) {
    if (!n && n !== 0) return '0';
    return Math.round(n).toLocaleString('vi-VN');
  }

  new Vue({
    el: el,
    data: function() {
        return {
            isLoading: false,
            storageGB: parseFloat(storageFld.value || 50),
            dtTotalGB: parseFloat(storageFld.value || 50) * DT_FREE_QUOTA_RATIO,
            reqTr: REQ_FREE_TR,
            cycleIndex: 0,
            cycles: CYCLES,
            storageField: storageFld,
            dtField: dtFld,
            reqField: reqFld,
            leftHeading: rawSettings.left_heading || 'Tuỳ chỉnh tài nguyên',
            rightHeading: rawSettings.right_heading || 'Ước tính chi phí/ tháng',
            vatNote: rawSettings.vat_note || 'Giá chưa bao gồm VAT',
            ctaLabel: rawSettings.cta_label || 'Đăng ký ngay',
            ctaContactLabel: rawSettings.cta_contact_label || 'Liên hệ',
            ctaContactUrl: rawSettings.cta_contact_url || '',
            cfg: {
                storage: { min: parseFloat(storageFld.min || 50), max: parseFloat(storageFld.max || 10240), step: parseFloat(storageFld.step || 50) },
                dt: { min: parseFloat(dtFld.min || 0), max: parseFloat(dtFld.max || 100000), step: parseFloat(dtFld.step || 1000) },
                req: { min: REQ_FREE_TR, max: parseFloat(reqFld.max || 1000000000) / 1000000, step: parseFloat(reqFld.step || 1000000) / 1000000 },
            },
            isTypingDt: false,
        };
    },
    computed: {
        isContactTier: function() { return this.storageTier.actionPrice === 'contact'; },
        dtSliderMax: function() {
            var configuredMax = parseFloat(this.dtField.max || 100000);
            return Math.max(configuredMax, this.dtFreeQuotaGB);
        },
        storageTier: function() {
            for (var i = 0; i < STORAGE_TIERS.length; i++) {
                var t = STORAGE_TIERS[i];
                if (t.priceType === 'between' && this.storageGB >= t.rangeFrom && this.storageGB <= t.rangeTo) return t;
                if (t.priceType === 'greater_than' && this.storageGB > t.rangeFrom) return t;
                if (t.priceType === 'less_than' && this.storageGB < t.rangeFrom) return t;
            }
            return STORAGE_TIERS[STORAGE_TIERS.length - 1] || {};
        },
        storageCost: function() {
            return (this.storageTier.price && this.storageTier.actionPrice !== 'contact') ? this.storageGB * this.storageTier.price : 0;
        },
        dtFreeQuotaGB: function() { return this.storageGB * DT_FREE_QUOTA_RATIO; },
        dtExceededGB: function() { return Math.max(0, this.dtTotalGB - this.dtFreeQuotaGB); },
        dtTierPrice: function() {
            for (var i = 0; i < DT_TIERS.length; i++) { if (this.dtExceededGB <= DT_TIERS[i].upTo) return DT_TIERS[i].price; }
            return DT_TIERS[DT_TIERS.length - 1].price;
        },
        dtCost: function() { return this.dtExceededGB > 0 ? this.dtExceededGB * this.dtTierPrice : 0; },
        reqFreeQuota: function() { return REQ_FREE_TR; },
        reqExceeded: function() { return Math.max(0, this.reqTr - REQ_FREE_TR); },
        reqCost: function() { return this.reqExceeded * REQ_UNIT_PRICE; },
        reqUnitPricePer1000: function() { return fmt(REQ_UNIT_PRICE / 1000); },
        selectedCycle: function() { return this.cycles[this.cycleIndex] || this.cycles[0]; },
        monthlyTotal: function() { return this.storageCost + this.dtCost + this.reqCost; },
        totalCost: function() { return this.monthlyTotal * this.selectedCycle.multiplier; },
        hasWarning: function() { return this.dtExceededGB > 0 || this.reqExceeded > 0; },
        storagePct: function() {
            var r = this.cfg.storage.max - this.cfg.storage.min;
            return r ? Math.max(0, Math.min(100, ((this.storageGB - this.cfg.storage.min) / r) * 100)) : 0;
        },
        dtPct: function() { return this.dtSliderMax ? Math.max(0, Math.min(100, (this.dtTotalGB / this.dtSliderMax) * 100)) : 0; },
        reqPct: function() {
            var r = this.cfg.req.max - this.cfg.req.min;
            return r ? Math.max(0, Math.min(100, ((this.reqTr - this.cfg.req.min) / r) * 100)) : 0;
        },
    },
    watch: {
        storageGB: function(newVal, oldVal) {
            var oldFQ = parseFloat(oldVal || 0) * DT_FREE_QUOTA_RATIO;
            // if (this.dtTotalGB <= oldFQ + 0.001) { this.dtTotalGB = newVal * DT_FREE_QUOTA_RATIO; }
            this.dtTotalGB = newVal * DT_FREE_QUOTA_RATIO;
        },
        dtTotalGB: function(val) {
            // var fq = this.dtFreeQuotaGB;
            // if (val < fq) { this.dtTotalGB = fq; }
            if (!this.isTypingDt) {
        var fq = this.dtFreeQuotaGB;
        if (val < fq) { this.dtTotalGB = fq; }
    }
        },
    },
    methods: {
        fmt: fmt,
        sliderBg: function(pct) { return { background: 'linear-gradient(to right,#007cfc 0%,#007cfc ' + pct + '%,#dedfe0 ' + pct + '%,#dedfe0 100%)' }; },
        fmtDec: function(n, d) { return parseFloat(n).toFixed(d !== undefined ? d : 2); },
        clamp: function(key, min, max) {
            var v = parseFloat(this[key]);
            if (isNaN(v)) v = min;
            this[key] = Math.min(max, Math.max(min, v));
        },
        limitInput: function(key, maxLen) {
            var valStr = this[key].toString();
            if (valStr.length > maxLen) {
                this[key] = parseFloat(valStr.slice(0, maxLen));
            }
        },
        resetForm: function() {
            this.storageGB = parseFloat(storageFld.value || 50);
            this.reqTr = REQ_FREE_TR;
            this.cycleIndex = 0;
            // dtTotalGB will be auto-updated via watcher on storageGB
        },
        orderContact: function() {
            var url = this.ctaContactUrl;
            if (!url) return;
            if (url.charAt(0) === '#') {
                var target = document.querySelector(url);
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            window.open(url, '_blank');
        },
        orderProduct: function() {
            var self = this;
            var orderConfig = window.vnx_order_obj_storage || window.vnxAppConfig || window.vietnix_order_product || {};
            self.isLoading = true;
            if (typeof jQuery !== 'undefined') {
                jQuery.ajax({
                    url: orderConfig.ajaxurl || orderConfig.ajax_url || window.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php'),
                    type: 'POST',
                    data: {
                        action: 'vnx_order_obj_storage_center',
                        nonce: orderConfig.nonce,
                        pid: Number(rawSettings.id_product),
                        "options[user_quota]": Number(self.storageGB),
                        "options[data_transfer]": Number(self.dtTotalGB),
                        "options[api_req]": Number(self.reqTr),
                        billingcycle: self.selectedCycle.pid,
                    },
                    success: function(res) {
                        self.isLoading = false;
                        if (res && res.data && res.data.redirect) {
                            window.location.href = res.data.redirect;
                        }
                    },
                    error: function() { self.isLoading = false; },
                });
            } else {
                console.error("jQuery is not defined");
                self.isLoading = false;
            }
        },

    }
  });

}

/**
 * ==========================================
 * GLOBAL HANDLER FOR BACK BUTTON / BFCACHE (V1 & V2)
 * ==========================================
 */
(function () {
  // V1 chỉ cần gỡ trạng thái loading của nút đăng ký; widget không có resetForm.
  function resetV1Widgets() {
    const widgets = document.querySelectorAll(".vnx-dynamic-price-obj-storage");
    widgets.forEach((el) => {
      const vm = el.__vue__;
      if (vm) {
        vm.isLoading = false;
      }
    });
  }

  function resetV2Widgets() {
    const widgets = document.querySelectorAll(".vnx-obj-v2");
    widgets.forEach((el) => {
      const vm = el.__vue__;
      if (vm && typeof vm.resetForm === "function") {
        vm.storageGB = null;
        vm.dtTotalGB = null;
        vm.reqTr = null;
        vm.cycleIndex = null;
        vm.$nextTick(() => {
          vm.resetForm();
          vm.isLoading = false;
        });
      }
    });
  }

  window.addEventListener("pageshow", function (event) {
    var isBackNavigation = event.persisted;
    
    if (!isBackNavigation && window.performance) {
      var navEntries = typeof window.performance.getEntriesByType === "function" 
        ? window.performance.getEntriesByType('navigation') 
        : null;
      if (navEntries && navEntries.length > 0) {
        isBackNavigation = navEntries[0].type === 'back_forward';
      } else {
        isBackNavigation = window.performance.navigation.type === 2;
      }
    }

    if (isBackNavigation) {
      resetV1Widgets();
      resetV2Widgets();
    }
  });
})();
