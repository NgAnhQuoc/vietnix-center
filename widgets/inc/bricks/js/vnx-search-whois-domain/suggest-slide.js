if ($(".vnx-suggest-slide-whois-domain").length != 0) {
  new Vue({
    el: ".vnx-suggest-slide-whois-domain",
    data: {
      DomainCartCookie: "vnx_domain_carts",
      domain: null,
      tld: "",
      sld: "",
      listTLD: window.listTLD,
      listTLDCalled: [],
      listResultDomain: [],
      TempResultDomain: [],
      availableDomains: null,
      isLoading: false,
    },

    mounted() {
      // Initialize perfect scrollbar
      const elements = document.querySelectorAll(
        ".vnx-suggest-slide-whois-domain .vnx-sidebar-xscroll"
      );
      initPerfectScrollbar(elements);

      // Lắng nghe sự kiện submit domain
      this.$eventBus.$on("vnx-emit-submit-whois", (domain) => {
        this.domain = domain;
        this.sld = splitDomain(domain).sld;
        this.tld = splitDomain(domain).tld;

        // Reset dữ liệu cũ
        this.listTLDCalled = [this.tld];
        this.listResultDomain = [];

        this.checkTempResultDomain(this.sld, this.tld);
        this.checkAvailableDomain(this.sld);
      });
    },

    methods: {
      // Hàm chính để kiểm tra domain available
      checkAvailableDomain(sld) {
        // Kiểm tra điều kiện dừng
        if (!this.shouldContinueSearching()) {
          return;
        }

        // Lấy batch TLD chưa gọi APIi
        const tldBatch = this.listTLD
          .filter((domain) => !this.listTLDCalled.includes(domain.tld))
          .map((domain) => domain.tld)
          .slice(0, 5);

        if (tldBatch.length === 0) {
          this.stopLoading();
          return;
        }

        this.isLoading = true;
        this.listTLDCalled.push(...tldBatch);

        this.callAPIForTLDBatch(sld, tldBatch)
          .then((results) => this.processAPIResults(results, sld))
          .catch((error) => this.handleAPIError(error));
      },

      // Kiểm tra trong danh sách tạm thời
      checkTempResultDomain(sld, tld) {
        this.TempResultDomain.filter((domain) => {
          if (domain.sld === sld && domain.tld != tld) {
            this.listResultDomain.push(domain);
            this.listTLDCalled.push(domain.tld);
          }
        });
      },

      // Dừng trạng thái loading
      stopLoading() {
        this.isLoading = false;
      },

      // Gọi API cho một batch TLD
      callAPIForTLDBatch(sld, tldBatch) {
        return new Promise((resolve, reject) => {
          $.ajax({
            url: vnx_search_whois_domain.ajax_url,
            type: "POST",
            data: {
              action: "vnx_check_available_domain_center",
              sld: sld,
              tld: tldBatch.join(","),
              nonce: vnx_search_whois_domain.nonce,
            },
            success: (response) => {
              response.success
                ? resolve(response.data)
                : reject(new Error(response.data.message));
            },
            error: (xhr, status, error) => {
              reject(new Error(error));
            },
          });
        });
      },

      // Xử lý kết quả từ API
      processAPIResults(results, sld) {
        this.stopLoading();
        this.addAvailableDomainsToResult(results);

        if (this.shouldContinueSearching()) {
          this.checkAvailableDomain(sld);
        }
      },

      // Thêm domain available vào kết quả
      addAvailableDomainsToResult(domainList) {
        if (!Array.isArray(domainList)) return;

        domainList.forEach((domain) => {
          if (
            domain.isAvailable &&
            this.listResultDomain.length < 5 &&
            domain?.pricing
          ) {
            this.listResultDomain.push(domain);
            this.TempResultDomain.push(domain);
          }
        });
      },

      // Kiểm tra có nên tiếp tục tìm kiếm không
      shouldContinueSearching() {
        return (
          this.listResultDomain.length < 5 &&
          this.listTLDCalled.length < this.listTLD.length
        );
      },

      // Bỏ qua batch TLD lỗi, tiếp tục với các TLD còn lại
      handleAPIError(error) {
        showNoticeMessage("error", "Có lỗi xảy ra khi kiểm tra một số tên miền, đang thử tên miền khác.");
        if (this.shouldContinueSearching()) {
          this.checkAvailableDomain(this.sld);
        } else {
          this.stopLoading();
        }
      },

      orderProduct(domainName) {
        const { sld, tld } = splitDomain(domainName);
        const obj = {
          domain: domainName,
          register: "1",
          authen_code: "",
          sld: sld,
          tld: tld,
        };

        addDomaininCookie(this.DomainCartCookie, obj);
        window.location.href = "https://portal.vietnix.vn/cart.php?a=view";
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
    },
  });
}
