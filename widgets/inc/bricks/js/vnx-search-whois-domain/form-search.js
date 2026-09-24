Vue.prototype.$eventBus = new Vue();

if ($(".vnx-search-whois-domain").length != 0) {
  new Vue({
    el: ".vnx-search-whois-domain",
    data: {
      domain: "",
      isLoading: false,
      linkRedirect: window.linkRedirect || '',
      isRedirect: window.isRedirect || false,
      resultWhoisDomain: null,
      errorMessage: "",
      cacheWhoisDomain: [],
      cacheAvailableDomain: [],
    },

    mounted() {
      this.domain = getDomainFromUrl();
      if (this.domain) {
        this.domain = this.cleanDomain(this.domain);
        if (!this.isValidDomain(this.domain)) {
          return;
        }

        this.searchDomain();
      }
    },

    methods: {
    

      // Hàm xử lý submit search domain
      searchDomain() {
        if (!this.domain) {
          showNoticeMessage("error", "Vui lòng nhập tên miền cần kiểm tra.");
          return;
        }

        // Clean domain (nếu chưa có tld thì tự động thêm .vn)
        this.domain = this.cleanDomain(this.domain);

        if (!this.isValidDomain(this.domain)) {
          return;
        }

        // chuyển hướng nếu có linkRedirect
        if (this.linkRedirect && this.isRedirect) {
          const redirectUrl = new URL(this.linkRedirect, window.location.origin);
          redirectUrl.searchParams.set("domain", this.domain);
          window.location.href = redirectUrl.toString();
          return;
        }

        // Gửi sự kiện submit domain
        this.sendSubmitDomain(this.domain);
        this.errorMessage = "";

        // Whois và available kiểm tra/gọi cache độc lập nhau
        if (this.cacheWhoisDomain[this.domain]) {
          this.resultWhoisDomain = this.cacheWhoisDomain[this.domain];
          this.sendResultWhois(this.resultWhoisDomain);
        } else {
          this.isLoading = true;
          this.resultWhoisDomain = null;
          this.fetchWhoisDomain();
        }

        // Available: dùng cache nếu có, không thì gọi AJAX
        if (this.cacheAvailableDomain[this.domain]) {
          this.sendAvailableDomain(this.cacheAvailableDomain[this.domain]);
        } else {
          this.fetchAvailableDomain();
        }

        this.updateUrlWithDomain();
      },

      // Gọi AJAX để kiểm tra tên miền
      fetchWhoisDomain() {
        $.ajax({
          url: vnx_search_whois_domain.ajax_url,
          type: "POST",
          data: {
            action: "vnx_search_domain_center",
            domain: this.domain,
            nonce: vnx_search_whois_domain.nonce,
          },
          success: (response) => {
            this.isLoading = false;
            if (response && response.success) {
              this.resultWhoisDomain = this.formatResultData(response.data);

              // Gửi kết quả cho component hiển thị
              this.cacheWhoisDomain[this.domain] = this.resultWhoisDomain;
              this.sendResultWhois(this.resultWhoisDomain);
              this.updateUrlWithDomain();
            } else {
              this.sendResultWhois({
                domainStatus: "undefined"
              });
              this.errorMessage = response?.data?.message || "Không thể lấy được thông tin tên miền này. Vui lòng thử lại với tên miền khác.";
            }
          },
          error: (xhr, status, error) => {
            this.isLoading = false;
            this.sendResultWhois({ domainStatus: "undefined" });
            showNoticeMessage("error", "Có lỗi xảy ra khi kiểm tra tên miền.");
          },
        });
      },

      fetchAvailableDomain() {
        const { sld, tld } = splitDomain(this.domain);
        $.ajax({
          url: vnx_search_whois_domain.ajax_url,
          type: "POST",
          data: {
            action: "vnx_check_available_domain_center",
            sld: sld,
            tld: tld,
            nonce: vnx_search_whois_domain.nonce,
          },
          success: (response) => {
            if (response.success) {
              const availableDomains = response.data[0] || [];
              this.cacheAvailableDomain[this.domain] = availableDomains;
              this.sendAvailableDomain(availableDomains);
            } else {
              // null để component hiển thị không giữ lại dữ liệu domain trước đó
              this.sendAvailableDomain(null);
              showNoticeMessage("error", "Có lỗi xảy ra khi kiểm tra tên miền khả dụng #1.");
            }
          },
          error: (xhr, status, error) => {
            this.sendAvailableDomain(null);
            showNoticeMessage("error", "Có lỗi xảy ra khi kiểm tra tên miền khả dụng #2.");
          },
        });
      },

      // Tạo hàm cleanDomain nếu chưa có tld thì tự động thêm .vn
      cleanDomain(domain) {
        domain = domain.trim();

        // Bỏ tiền tố "www." vì whois tra theo domain gốc, không phải subdomain
        domain = domain.replace(/^www\./i, "");

        if (!domain.includes(".") && domain !== "") {
          return domain + ".vn";
        }
        return domain;
      },

      // check rule doamin hợp lệ
      isValidDomain(domain) {
        const domainPattern = /^(?!-)(?:[a-zA-Z0-9-]{1,63}\.)+[a-zA-Z]{2,}$/;
        let isPass = domainPattern.test(domain);

        const { tld, sld } = splitDomain(domain);

        if (tld === "vn" && sld.length < 2) {
          showNoticeMessage("error", "Tên miền .vn phải có ít nhất 2 ký tự");
          return false;
        }
        if (!isPass) {
          showNoticeMessage("error", "Tên miền không hợp lệ");
        }
        return isPass;
      },

      // Cập nhật URL với domain đã search
      updateUrlWithDomain() {
        const url = new URL(window.location);

        url.searchParams.set("domain", this.domain);
        window.history.replaceState({}, "", url);
      },

      formatResultData(data) {
        // Danh sách mapping key hợp lệ
        const keyMap = {
          expirationDate: [
            "Registrar Registration Expiration Date",
            "RegistrarRegistrationExpirationDate",
            "Registry Expiry Date"
          ],
          updatedDate: ["Updated Date", "updatedDate"],
        };

        const formatted = {};

        for (const [key, value] of Object.entries(data)) {
          // Tìm xem key này nằm trong mapping nào
          const targetKey = Object.keys(keyMap).find((k) => keyMap[k].includes(key));

          // Nếu tìm thấy → dùng key chuẩn
          if (targetKey) {
            formatted[targetKey] = value;
          } else {
            // Nếu không nằm trong danh sách → giữ nguyên
            formatted[key] = value;
          }
        }

        return formatted;
      },

      // Reset form
      resetSearch() {
        this.domain = "";
        this.resultWhoisDomain = null;
        this.errorMessage = "";

        // Xóa domain khỏi URL
        const url = new URL(window.location);
        url.searchParams.delete("domain");
        window.history.replaceState({}, "", url);
      },

      // Gửi kết quả cho component hiển thị
      sendResultWhois(data) {
        this.$eventBus.$emit("vnx-emit-result-whois", data);
      },

      // Gửi sự kiện submit domain
      sendSubmitDomain(domain) {
        this.$eventBus.$emit("vnx-emit-submit-whois", domain);
      },

      // Gửi sự kiện available domain
      sendAvailableDomain(data) {
        this.$eventBus.$emit("vnx-emit-available-domain", data);
      },
    },
  });
}
