if ($(".vnx-show-result-whois-domain").length != 0) {
  new Vue({
    el: ".vnx-show-result-whois-domain",
    data: {
      DomainCartCookie: "vnx_domain_carts",
      resultWhoisDomain: null,
      resultAvailableDomain: null,
      firstScreen: getDomainFromUrl() ? false : true,
      isCopied: false,
      isLoading: false,
      isFirstLoad: true,
      isNoti: false,
      listTLD: window.listTLD2 || [],
      //   listTLD = [
      //     {
      //         "tld": "vn",
      //         "pricing": 200000
      //     },
      //     {
      //         "tld": "net",
      //         "pricing": 200000
      //     },
      //     {
      //         "tld": "com",
      //         "pricing": 200000
      //     },

      // ]

      listPriceDomain0d: convertToObject(window.listPriceDomain0d) || {},
      listPriceDomainCombo: convertToObject(window.listPriceDomainCombo) || {},
    },

    mounted() {
      this.$eventBus.$on("vnx-emit-submit-whois", (domain) => {
        if (this.isFirstLoad) {
          this.isFirstLoad = false;
        } else {
          this.isLoading = true;
        }

        this.isNoti = false;
        this.firstScreen = false;
      });

      this.$eventBus.$on("vnx-emit-result-whois", (data) => {
        this.firstScreen = false;
        this.isLoading = false;

        if (!this.isNoti) {
          showNoticeMessage("success", "Kiểm tra thông tin WHOIS thành công.");
          this.isNoti = true;
        }

        //      data = {
        //     "caole.vn": "Domain Name not found !",
        //     "domainStatus": "",
        //     "domain": "caole.vn",
        //     "sld": "caole",
        //     "tld": "vn",
        //     "domain_type": "vn"
        // }
        this.resultWhoisDomain = data;
      });

      this.$eventBus.$on("vnx-emit-available-domain", (data) => {
        //     data={
        //     "domainName": "caole.vn",
        //     "isAvailable": true,
        //     "legacyStatus": "available",
        //     "isPremium": false,
        //     "sld": "caole",
        //     "tld": "vn",
        //     "currency": "VND",
        //     "pricing": {
        //         "categories": [
        //             "Other"
        //         ],
        //         "group": "",
        //         "register": {
        //             "1": "558000.00",
        //             "2": "1016000.00",
        //             "3": "1474000.00",
        //             "4": "1932000.00",
        //             "5": "2390000.00"
        //         },
        //         "renew": {
        //             "1": "558000.00",
        //             "2": "1016000.00",
        //             "3": "1474000.00",
        //             "4": "1932000.00",
        //             "5": "2390000.00"
        //         }
        //     }
        // }

        this.firstScreen = false;

        if (data?.isAvailable) {
          this.isLoading = false;

          if (!this.isNoti) {
            showNoticeMessage("success", "Tên miền sẵn sàng đăng ký.");
            this.isNoti = true;
          }
        }

        this.resultAvailableDomain = data;
      });
    },

    watch: {
      resultAvailableDomain(newVal) {
        if (newVal) {
          this.$nextTick(() => {
            this.resetPerfectScrollbar();
          });
        }
      },
      resultWhoisDomain(newVal) {
        if (newVal) {
          this.$nextTick(() => {
            this.resetPerfectScrollbar();
          });
        }
      },
    },

    methods: {
      // Kiểm tra 1 giá trị whois có dữ liệu để hiển thị hay không
      hasValue(value) {
        if (Array.isArray(value)) {
          return value.some((item) => this.hasValue(item));
        }
        if (value === null || value === undefined) {
          return false;
        }
        return String(value).trim() !== "";
      },

      // Kiểm tra 1 nhóm giá trị, dùng để ẩn cả khối khi không có dữ liệu nào
      hasAnyValue(...values) {
        return values.some((value) => this.hasValue(value));
      },

      getPriceOriginal(tld) {
        if (!Array.isArray(this.listTLD)) {
          return 0;
        }
        const found = this.listTLD.find((item) => item.tld === tld);
        if (!found) {
          return 0;
        }
        return found.pricing;
      },

      copyText(text) {
        try {
          navigator.clipboard.writeText(text);
          this.isCopied = true;
          setTimeout(() => {
            this.isCopied = false;
          }, 2000);
        } catch (err) {
          showNoticeMessage("error", "Có lỗi xảy ra khi sao chép.");
        }
      },

      checkComboDomain() {
        const { tld } = splitDomain(this.resultAvailableDomain.domainName);
        if (this.listPriceDomain0d[tld]?.length > 0) {
          return "0d";
        }

        if (this.listPriceDomainCombo[tld]?.length > 0) {
          return "combo";
        }

        return "";
      },

      getRowDataPriceCSV(index) {
        const type = this.checkComboDomain();
        const { tld } = splitDomain(this.resultAvailableDomain.domainName);

        let rowData = "";
        if (type === "0d") {
          rowData = this.listPriceDomain0d[tld][index] || [];
        }

        if (type === "combo") {
          rowData = this.listPriceDomainCombo[tld][index] || [];
        }
        rowData = String(rowData || "").split("|");
        return rowData;
      },

      resetPerfectScrollbar() {
        const elements = document.querySelectorAll(
          ".vnx-show-result-whois-domain .vnx-sidebar-xscroll"
        );
        initPerfectScrollbar(elements);

        const elements2 = document.querySelectorAll(
          ".vnx-show-result-whois-domain .vnx-sidebar-xscroll2"
        );
        initPerfectScrollbar(elements2);
      },

      async orderProduct() {
        const { sld, tld } = splitDomain(this.resultAvailableDomain.domainName);
        const obj = {
          domain: this.resultAvailableDomain.domainName,
          register: "1",
          authen_code: "",
          sld: sld,
          tld: tld,
        };

        addDomaininCookie(this.DomainCartCookie, obj);

        window.location.href = "https://portal.vietnix.vn/cart.php?a=view";
      },
    },
  });
}
