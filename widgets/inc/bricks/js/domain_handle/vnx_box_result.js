if (!window.DomainMixinResult) {
  window.DomainMixinResult = {
    data() {
      return {
        DomainCartCookie: "vnx_domain_carts",
        ProgressRAF: null,
        DomainIntCart: false,
        ComboIntCart: false,
        ListDomain: [],
        ListComboDomain: [],
        ComboDisplayCount: 0,
        ResultError: false,
        IsLoading: false,
        ListTLDCalled: [],
        ListTLDSorted: getDataLocalStorage("ListTLDSorted") || [],
        isLoadFullTLD: false,
        IsLoadingMore: false,
        IsHiddenResultMore: false,
        remainingTLDChunks: [],
        currentChunkIndex: 0,
        currentTab: 0,
        DomainVnError: false,
      };
    },
    watch: {
      ListTLDCalled() {
        // -1 là case trừ ra domain chính nó
        this.isLoadFullTLD =
          this.ListTLDSorted.length - 1 <= this.ListTLDCalled.length;
      },
      ListDomain: {
        handler(newVal, oldVal) {
          // Cập nhật trạng thái isInCart cho combo mỗi khi ListDomain thay đổi
          this.updateComboInCartStatus();
        },
        deep: true,
        immediate: false,
      },
      CombinationResult: {
        handler(newVal, oldVal) {
          // Cập nhật trạng thái proseInCart khi CombinationResult thay đổi
          if (newVal && newVal.availableDomains) {
            this.updateCombinationResultProseInCart();
            this.updateCombinationResultDisabledStatus();
          }
        },
        deep: true,
        immediate: false,
      },
      ListComboDomain: {
        handler(newVal, oldVal) {
          // Cập nhật số lượng combo đang hiển thị
          this.updateComboDisplayCount();
        },
        deep: true,
        immediate: true,
      },
    },
    methods: {
      clickAddToCart(sld, tld) {
        var obj = {
          domain: `${sld}.${tld}`,
          register: "1",
          authen_code: "",
          sld: sld,
          tld: tld,
          isInCart: false,
        };
        var cookie_age = $("input#cart_cookie_age").val() || "30";

        if (!checkCookieDomain(this.DomainCartCookie, obj)) {
          addDomaininCookie(this.DomainCartCookie, obj, cookie_age, cookie_age);

          // Cập nhật trạng thái inACart cho domain trong CombinationResult
          this.updateCombinationResultDomainStatus(sld, tld, true);

          this.loadDomainSuggestinCart();
        }

        let newData = JSON.parse(
          JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
        );

        // Cập nhật DomainIntCart dựa trên cookie để đảm bảo đồng bộ
        // Chỉ cập nhật DomainIntCart nếu domain được click là domain chính
        if (this.DomainNameSld === sld && this.DomainNameTld === tld) {
          let mainDomain = `${this.DomainNameSld}.${this.DomainNameTld}`;
          let isMainDomainInCart = newData.some(
            (item) => item.domain === mainDomain
          );
          this.DomainIntCart = isMainDomainInCart;
        }

        this.$eventBus.$emit("clickAddToCart", newData);
        this.$eventBus.$emit("domainAddedToCart", obj.domain);

        // Cập nhật trạng thái disable cho tất cả combo
        this.updateComboDisabledStatus();
        this.updateCombinationResultDisabledStatus();

        // Cập nhật trạng thái proseInCart cho combo
        this.updateCombinationResultProseInCart();
      },

      // Function mới: Click add domain riêng lẻ với cập nhật trạng thái isInCart
      clickAddToCartTwo(sld, tld) {
        var obj = {
          domain: `${sld}.${tld}`,
          register: "1",
          authen_code: "",
          sld: sld,
          tld: tld,
          isInCart: false,
        };
        var cookie_age = $("input#cart_cookie_age").val() || "30";

        if (!checkCookieDomain(this.DomainCartCookie, obj)) {
          addDomaininCookie(this.DomainCartCookie, obj, cookie_age, cookie_age);

          // Cập nhật trạng thái isInCart cho domain trong ListDomain
          this.updateDomainInCartStatus(sld, tld, true);

          // Cập nhật trạng thái inACart cho domain trong CombinationResult
          this.updateCombinationResultDomainStatus(sld, tld, true);

          this.loadDomainSuggestinCart();
        }

        let newData = JSON.parse(
          JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
        );

        // Chỉ cập nhật DomainIntCart nếu domain được click là domain chính
        if (this.DomainNameSld === sld && this.DomainNameTld === tld) {
          this.DomainIntCart = true;
        }

        this.$eventBus.$emit("clickAddToCart", newData);
        this.$eventBus.$emit("domainAddedToCart", obj.domain);

        // Cập nhật trạng thái disable cho tất cả combo
        this.updateComboDisabledStatus();
        this.updateCombinationResultDisabledStatus();

        // Cập nhật trạng thái proseInCart cho combo
        this.updateCombinationResultProseInCart();
      },

      // Function mới: Cập nhật trạng thái isInCart cho domain cụ thể
      updateDomainInCartStatus(sld, tld, isInCart) {
        // Cập nhật trong ListDomain
        if (this.ListDomain && this.ListDomain.length > 0) {
          const domainToUpdate = this.ListDomain.find(
            (domain) => domain.sld === sld && domain.tld === tld
          );
          if (domainToUpdate) {
            this.$set(domainToUpdate, "isInCart", isInCart);
          }
        }
      },

      // Function mới: Cập nhật trạng thái proseInCart của CombinationResult
      updateCombinationResultProseInCart() {
        if (
          !this.CombinationResult ||
          !this.CombinationResult.availableDomains
        ) {
          return;
        }

        // Kiểm tra xem combo có thực sự tồn tại trong cart không
        const cartData = getCookiesDomainCart(this.DomainCartCookie);
        const comboExistsInCart = cartData.some(
          (item) =>
            item.isCombo &&
            item.comboData &&
            item.comboId && // Thêm kiểm tra comboId để đảm bảo combo còn active
            item.comboData.stt === this.CombinationResult.stt
        );


        // Nếu combo không tồn tại trong cart, set proseInCart = false
        if (!comboExistsInCart) {
          this.$set(this.CombinationResult, "proseInCart", false);
          this.proseInCart = false;
          return;
        }

        // Kiểm tra xem tất cả domain trong combo có trong cart không
        const allDomainsInCart = this.CombinationResult.availableDomains.every(
          (domain) => domain.inACart
        );


        // Cập nhật trạng thái proseInCart của CombinationResult
        this.$set(this.CombinationResult, "proseInCart", allDomainsInCart);

        // Cập nhật trạng thái proseInCart của component
        this.proseInCart = allDomainsInCart;
      },

      // Function mới: Cập nhật trạng thái inACart cho domain trong CombinationResult
      updateCombinationResultDomainStatus(sld, tld, isInCart) {
        if (
          !this.CombinationResult ||
          !this.CombinationResult.availableDomains
        ) {
          return;
        }

        // Tìm domain trong availableDomains của CombinationResult
        const domainInCombo = this.CombinationResult.availableDomains.find(
          (domain) => domain.sld === sld && domain.tld === tld
        );

        if (domainInCombo) {
          // Cập nhật trạng thái inACart cho domain trong combo
          this.$set(domainInCombo, "inACart", isInCart);

          // Cập nhật trạng thái proseInCart
          this.updateCombinationResultProseInCart();
        }
      },

      clickBuyDomain(sld, tld) {
        var obj = {
          domain: `${sld}.${tld}`,
          register: "1",
          authen_code: "",
          sld: sld,
          tld: tld,
        };
        var cookie_age = $("input#cart_cookie_age").val() || "30";

        if (!checkCookieDomain(this.DomainCartCookie, obj)) {
          addDomaininCookie(this.DomainCartCookie, obj, cookie_age, cookie_age);
        }
        window.location.href = "https://portal.vietnix.vn/cart.php?a=view";
      },

      runProgressBar(duration = 20000) {
        let $progressBar = $(".vnx-loading_animate .progress-bar");
        let startTime = null;

        const animateProgress = (timestamp) => {
          if (!startTime) startTime = timestamp;
          let elapsed = timestamp - startTime;
          let progress = Math.min((elapsed / duration) * 100, 100);
          $progressBar.css("width", progress + "%");

          if (progress < 100) {
            this.ProgressRAF = requestAnimationFrame(animateProgress);
          }
        };

        this.ProgressRAF = requestAnimationFrame(animateProgress);
      },

      completeProgressBar() {
        if (this.ProgressRAF) {
          cancelAnimationFrame(this.ProgressRAF);
          this.ProgressRAF = null;
        }

        $(".vnx-loading_animate .progress-bar").css({
          width: "100%",
          transition: "width 0.5s ease-in-out",
        });

        setTimeout(() => {
          $(".vnx-loading_animate .progress-bar").css({
            width: "0%",
            transition: "width 0s ease-in-out",
          });
        }, 300);
      },

      checkDomainCart(removedDomain) {
        let index = this.ListDomain.findIndex(
          (d) => d.domainName === removedDomain
        );
        if (index !== -1) {
          this.$set(this.ListDomain[index], "isInCart", false);
        }

        // Cập nhật trạng thái isInCart cho domain bị xóa
        const domainParts = removedDomain.split(".");
        if (domainParts.length >= 2) {
          const sld = domainParts[0];
          const tld = domainParts.slice(1).join(".");
          this.updateDomainInCartStatus(sld, tld, false);
        }

        // Kiểm tra nếu domain bị xóa là domain chính
        if (this.DomainNameSld && this.DomainNameTld) {
          let mainDomain = `${this.DomainNameSld}.${this.DomainNameTld}`;
          if (removedDomain === mainDomain) {
            this.DomainIntCart = false;
            this.ComboIntCart = false;
          }
        }
      },

      loadDomainSuggestinCart() {
        // Lấy danh sách domain từ cookie
        var data = getCookiesDomainCart(this.DomainCartCookie);

        // Cập nhật trạng thái isInCart cho từng domain trong ListDomain
        this.ListDomain.forEach((domain) => {
          let isInCart = data.some((item) => item.domain === domain.domainName);
          this.$set(domain, "isInCart", isInCart);
        });

        // Cập nhật trạng thái DomainIntCart cho domain chính
        if (this.DomainNameSld && this.DomainNameTld) {
          let mainDomain = `${this.DomainNameSld}.${this.DomainNameTld}`;
          let isMainDomainInCart = data.some(
            (item) => item.domain === mainDomain
          );
          this.DomainIntCart = isMainDomainInCart;

          // Kiểm tra xem combo có trong cart không
          if (
            this.CombinationResult &&
            this.CombinationResult.availableDomains
          ) {
            // Kiểm tra xem combo có trong cart không
            this.ComboIntCart = this.isComboInCart(this.CombinationResult);

            // Cập nhật trạng thái inACart cho từng domain trong CombinationResult
            this.CombinationResult.availableDomains.forEach((domain) => {
              const domainName = domain.domain || `${domain.sld}.${domain.tld}`;
              const isInCart = data.some((item) => item.domain === domainName);
              this.$set(domain, "inACart", isInCart);
            });

            // Cập nhật trạng thái proseInCart của CombinationResult
            this.updateCombinationResultProseInCart();
          } else {
            this.ComboIntCart = false;
          }
        }

        // Cập nhật trạng thái disable cho tất cả combo
        this.updateComboDisabledStatus();

        // Cập nhật trạng thái isInCart cho tất cả combo
        this.updateComboInCartStatus();
      },

      emitSuccessEvent() {
        this.$eventBus.$emit("completeGetDomain", {
          success: true,
        });
      },

      handleErrors(error) {
        console.error("Error getDataDomain:", error);
        this.ResultError = true;
        this.IsLoading = false;
        this.emitSuccessEvent();
      },

      hiddenResultMore() {
        this.IsHiddenResultMore = !this.IsHiddenResultMore;
        const listEL = $("#show-domain-suggest .box");
        if (this.IsHiddenResultMore) {
          listEL.slice(5).hide();
          // scroll to top
          $("html, body").animate(
            {
              scrollTop: $("#show-domain-suggest").offset().top - 100,
            },
            300
          );
        } else {
          listEL.show();
        }
      },

      initTLDPopular(response, listTLD) {
        try {
          const listTLDPopular = response.data
            .filter((item) => item?.pricing?.categories[0] === "Popular")
            .map((item) => item.tld);

          const ListTLDSorted = [...new Set([...listTLDPopular, ...listTLD])];
          setLocalStorage("ListTLDSorted", ListTLDSorted);
          this.ListTLDSorted = ListTLDSorted;
        } catch (error) {
          console.error("Error initTLDPopular:", error);
        }
      },

      // Function mới: Kiểm tra xem combo có bị disable không (cùng SLD và có ít nhất 1 TLD trùng)
      checkComboDisabled(combo) {
        if (!combo || !combo.availableDomains) return false;
        const cartData = getCookiesDomainCart(this.DomainCartCookie);

        // Lấy SLD và TLDs của combo hiện tại
        const currentSld = combo.sld;
        const currentTlds = combo.availableDomains.map((d) => d.tld);

        // Lấy tất cả domain cùng SLD trong cart
        const sameSldItems = cartData.filter((item) => item.sld === currentSld);

        // Nếu không có domain nào cùng SLD trong cart → Cho phép thêm combo
        if (sameSldItems.length === 0) {
          return false;
        }

        // Kiểm tra xem có combo nào trong cart có TLD trùng với combo hiện tại không
        const conflictingCombos = sameSldItems.filter((item) => {
          // Chỉ kiểm tra combo thực sự đang active trong cart
          // Kiểm tra cả isCombo và comboId để đảm bảo combo còn hoạt động
          if (!item.isCombo || !item.comboData || !item.comboId) {
            return false;
          }

          // 🔥 QUAN TRỌNG: Không kiểm tra conflict với chính combo đang được kiểm tra
          // Nếu combo trong cart có cùng stt với combo hiện tại → Không phải conflict
          if (item.comboData.stt === combo.stt) {
            return false;
          }

          let cartTlds = [];
          if (item.comboData.tlds) {
            cartTlds = item.comboData.tlds.split("+").map((tld) => tld.trim());
          } else if (item.tld) {
            cartTlds = [item.tld];
          } else {
            return false;
          }

          // Nếu combo trong cart có TLD trùng với combo hiện tại → Conflict
          const hasConflict = currentTlds.some((tld) => cartTlds.includes(tld));
          return hasConflict;
        });

        const isDisabled = conflictingCombos.length > 0;

        // Chỉ disable combo nếu có combo khác trùng TLD, không disable khi có domain lẻ
        return isDisabled;
      },

      // Function mới: Kiểm tra xem combo có thể thêm vào cart không
      checkCanAddComboToCart(combo) {
        // Nếu combo đã trong cart thì không thể thêm
        if (this.isComboInCart(combo)) return false;

        // Nếu combo bị disable thì không thể thêm
        if (this.checkComboDisabled(combo)) return false;

        return true;
      },

      // Function mới: Cập nhật trạng thái disable cho tất cả combo
      updateComboDisabledStatus() {
        if (this.ListComboDomain && this.ListComboDomain.length > 0) {
          this.ListComboDomain.forEach((combo) => {
            // Thêm thuộc tính isDisabled vào combo
            this.$set(combo, "isDisabled", this.checkComboDisabled(combo));
          });
        }
      },

      // Function mới: Cập nhật trạng thái isInCart cho tất cả combo
      updateComboInCartStatus() {
        if (this.ListComboDomain && this.ListComboDomain.length > 0) {
          this.ListComboDomain.forEach((combo) => {
            // Kiểm tra combo có hợp lệ không
            if (!combo || (!combo.stt && !combo.combo?.stt)) {
              return;
            }

            // Cập nhật trạng thái isInCart cho combo
            const isInCart = this.isComboInCart(combo);
            const comboStt = combo.stt || combo.combo?.stt;
            this.$set(combo, "isInCart", isInCart);
          });
        }
      },

      // Function mới: Cập nhật trạng thái isDisabled cho CombinationResult
      updateCombinationResultDisabledStatus() {
        if (this.CombinationResult && this.CombinationResult.isCombo) {
          this.$set(
            this.CombinationResult,
            "isDisabled",
            this.checkComboDisabled(this.CombinationResult)
          );
        }
      },

      // 🔄 Function mới: Cập nhật trạng thái combo sau khi cart thay đổi
      updateComboStatusAfterCartChange() {

        // Cập nhật trạng thái disable cho tất cả combo
        this.updateComboDisabledStatus();

        // Cập nhật trạng thái isInCart cho tất cả combo
        this.updateComboInCartStatus();

        // Cập nhật trạng thái disable cho CombinationResult
        this.updateCombinationResultDisabledStatus();

        // Cập nhật trạng thái proseInCart cho CombinationResult
        this.updateCombinationResultProseInCart();

        // Force update UI để đảm bảo thay đổi được hiển thị
        this.$nextTick(() => {
          this.$forceUpdate();
        });

      },

      // 🔄 Function mới: Cập nhật trạng thái domain trong result
      updateDomainStatusInResult(cartDomains) {
        // 🔄 Chỉ cập nhật trạng thái inACart cho CombinationResult
        if (this.CombinationResult && this.CombinationResult.availableDomains) {
          this.CombinationResult.availableDomains.forEach((domain) => {
            const domainName = `${domain.sld}.${domain.tld}`;
            const inACart = cartDomains.includes(domainName);
            this.$set(domain, "inACart", inACart);
          });
        }
      },

      updateComboDisplayCount() {
        // Đếm số combo thực sự được hiển thị trên giao diện
        const visibleCombos = this.ListComboDomain.filter((combo) => {
          // Kiểm tra combo có data hợp lệ
          if (!combo || !combo.sld || !combo.tld || combo.status === "error") {
            return false;
          }

          // Kiểm tra combo có trong tab hiện tại không
          if (combo.tab && Array.isArray(combo.tab)) {
            const currentTab = this.currentTab || 0;
            if (!combo.tab.includes(currentTab)) {
              return false;
            }
          }

          return true;
        });

        this.ComboDisplayCount = visibleCombos.length;
      },

      btoaUnicode(str) {
        return btoa(unescape(encodeURIComponent(str)));
      },

      clickComboAddToCart(combo) {
        // Kiểm tra xem combo có thể thêm vào cart không
        if (!this.checkCanAddComboToCart(combo)) {
          return;
        }

        // Nếu combo là object mới (có availableDomains)
        if (
          combo &&
          combo.availableDomains &&
          Array.isArray(combo.availableDomains)
        ) {
          // Sử dụng combo.stt thay cho combo.combo.stt
          const comboId = `combo_${combo.stt}_${Date.now()}`;
          const domains = [];

          // Tạo domain objects cho tất cả domains trong combo
          combo.availableDomains.forEach((domainInfo) => {
            let domainName =
              domainInfo.domainName ||
              domainInfo.domain ||
              (domainInfo.sld && domainInfo.tld
                ? `${domainInfo.sld}.${domainInfo.tld}`
                : "");
            let sld = domainInfo.sld;
            let tld = domainInfo.tld;
            if (!domainName || !sld || !tld) {
              console.error(
                "Thiếu thông tin domain trong combo.availableDomains",
                domainInfo
              );
              return;
            }
            // Chỉ giữ lại các trường số/id cần thiết cho comboData
            const { stt, Tlds, tlds, combopricing, defaultpricing } =
              combo || {};
            const domainObj = {
              domain: domainName,
              register: "1",
              authen_code: "",
              sld: sld,
              tld: tld,
              isInCart: false,
              comboId: comboId,
              isCombo: true,
              comboData: {
                stt: stt,
                Tlds: Tlds || tlds,
                combopricing: combopricing,
                defaultpricing: defaultpricing,
              },
            };
            domains.push(domainObj);
          });

          var cookie_age = $("input#cart_cookie_age").val() || "30";

          // Kiểm tra giới hạn domain trước khi thêm combo
          const currentCartData = getCookiesDomainCart(this.DomainCartCookie);
          const newDomainsCount = domains.filter(
            (domainObj) => !checkCookieDomain(this.DomainCartCookie, domainObj)
          ).length;

          if (currentCartData.length + newDomainsCount > 20) {
            showNoticeMessage(
              "error",
              `Giỏ hàng chỉ cho phép tối đa 20 domain. Hiện tại có ${currentCartData.length} domain, nếu cần mua thêm domain vui lòng liên hệ với chúng tôi để được hỗ trợ.`,
              null,
              null
            );
            return;
          }
          // Thêm tất cả domains vào cookie
          domains.forEach((domainObj) => {
            if (!checkCookieDomain(this.DomainCartCookie, domainObj)) {
              addDomaininCookie(
                this.DomainCartCookie,
                domainObj,
                cookie_age,
                cookie_age
              );
            }
          });

          this.loadDomainSuggestinCart();

          // Lấy dữ liệu mới từ cookie
          let newData = JSON.parse(
            JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
          );

          // Cập nhật trạng thái combo trong cart
          // this.ComboIntCart = true;

          // Cập nhật trạng thái isInCart cho tất cả domain trong combo
          domains.forEach((domainObj) => {
            this.updateDomainInCartStatus(domainObj.sld, domainObj.tld, true);

            // Cập nhật trạng thái inACart cho domain trong CombinationResult
            this.updateCombinationResultDomainStatus(
              domainObj.sld,
              domainObj.tld,
              true
            );
          });

          // Emit events
          this.$eventBus.$emit("clickAddToCart", newData);
          domains.forEach((domainObj) => {
            this.$eventBus.$emit("domainAddedToCart", domainObj.domain);
          });

          // Cập nhật trạng thái disable cho tất cả combo
          this.updateComboDisabledStatus();

          // Cập nhật trạng thái isInCart cho tất cả combo
          this.updateComboInCartStatus();
        } else {
          // Logic cũ cho backward compatibility
          const sld = combo.sld || combo;
          const tld = combo.tld || arguments[1];
          const combiTld = combo.combiTld || arguments[2];

          const domain1 = {
            domain: `${sld}.${tld}`,
            register: "1",
            authen_code: "",
            sld: sld,
            tld: tld,
            isInCart: false,
            comboId: `${sld}_${tld}_${combiTld}`,
            isCombo: true,
          };

          const domain2 = {
            domain: `${sld}.${combiTld}`,
            register: "1",
            authen_code: "",
            sld: sld,
            tld: combiTld,
            isInCart: false,
            comboId: `${sld}_${tld}_${combiTld}`,
            isCombo: true,
          };

          var cookie_age = $("input#cart_cookie_age").val() || "30";

          // Kiểm tra giới hạn domain trước khi thêm combo
          const currentCartData = getCookiesDomainCart(this.DomainCartCookie);
          const newDomainsCount =
            (!checkCookieDomain(this.DomainCartCookie, domain1) ? 1 : 0) +
            (!checkCookieDomain(this.DomainCartCookie, domain2) ? 1 : 0);

          if (currentCartData.length + newDomainsCount > 20) {
            showNoticeMessage(
              "error",
              `Giỏ hàng chỉ cho phép tối đa 20 domain. Hiện tại có ${currentCartData.length} domain, nếu cần mua thêm domain vui lòng liên hệ với chúng tôi để được hỗ trợ.`,
              null,
              null
            );
            return;
          }

          if (!checkCookieDomain(this.DomainCartCookie, domain1)) {
            addDomaininCookie(
              this.DomainCartCookie,
              domain1,
              cookie_age,
              cookie_age
            );
          }
          if (!checkCookieDomain(this.DomainCartCookie, domain2)) {
            addDomaininCookie(
              this.DomainCartCookie,
              domain2,
              cookie_age,
              cookie_age
            );
          }

          if (this.DomainNameSld === sld && this.DomainNameTld === tld) {
            this.ComboIntCart = true;
          }

          // Cập nhật trạng thái isInCart cho cả hai domain trong combo
          this.updateDomainInCartStatus(domain1.sld, domain1.tld, true);
          this.updateDomainInCartStatus(domain2.sld, domain2.tld, true);

          // Cập nhật trạng thái inACart cho domain trong CombinationResult
          this.updateCombinationResultDomainStatus(
            domain1.sld,
            domain1.tld,
            true
          );
          this.updateCombinationResultDomainStatus(
            domain2.sld,
            domain2.tld,
            true
          );

          this.loadDomainSuggestinCart();
          let newData = JSON.parse(
            JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
          );
          this.$eventBus.$emit("clickAddToCart", newData);
          this.$eventBus.$emit("domainAddedToCart", domain1.domain);
          this.$eventBus.$emit("domainAddedToCart", domain2.domain);

          // Cập nhật trạng thái disable cho tất cả combo
          this.updateComboDisabledStatus();

          // Cập nhật trạng thái isInCart cho tất cả combo
          this.updateComboInCartStatus();

          // Cập nhật trạng thái proseInCart cho combo
          this.updateCombinationResultProseInCart();
        }
      },

      isComboInCart(combo) {
        if (!combo || !combo.availableDomains) return false;
        const cartData = getCookiesDomainCart(this.DomainCartCookie);

        // Lấy stt của combo (có thể từ combo.stt hoặc combo.combo.stt)
        const comboStt = combo.stt || combo.combo?.stt;
        if (!comboStt) {
          return false;
        }

        // Lấy danh sách domain của combo
        const comboDomains = combo.availableDomains
          .map((d) => d.domain || d.sld + "." + d.tld)
          .sort();

        // Kiểm tra xem có combo nào trong cart có cùng stt và tất cả domain không
        const comboExistsInCart = cartData.some((item) => {
          // Kiểm tra xem item có phải là combo active không
          if (!item.isCombo || !item.comboData || !item.comboId) return false;

          // Kiểm tra xem có cùng stt không
          if (item.comboData.stt !== comboStt) return false;

          // Kiểm tra xem tất cả domain của combo có trong cart không
          const cartDomains = cartData
            .filter(cartItem => cartItem.comboId === item.comboId)
            .map(cartItem => cartItem.domain)
            .sort();

          return comboDomains.every((domain) => cartDomains.includes(domain));
        });

        return comboExistsInCart;
      },

      clickRemoveComboFromCart(combo) {
        if (!combo || !combo.availableDomains) return;

        // Kiểm tra và lấy stt từ combo object
        let comboStt = null;
        if (combo.combo && combo.combo.stt) {
          comboStt = combo.combo.stt;
        } else if (combo.stt) {
          comboStt = combo.stt;
        } else {
          console.error("Không tìm thấy stt trong combo object:", combo);
          return;
        }

        const comboId = `combo_${comboStt}_`;
        const cartData = getCookiesDomainCart(this.DomainCartCookie);

        // Xóa tất cả domains thuộc combo này khỏi cart
        combo.availableDomains.forEach((domainInfo) => {
          // Kiểm tra và lấy domain name
          let domainName = domainInfo.domain;
          if (!domainName && domainInfo.sld && domainInfo.tld) {
            domainName = `${domainInfo.sld}.${domainInfo.tld}`;
          }

          if (!domainName) {
            console.error(
              "Không tìm thấy domain name trong domainInfo:",
              domainInfo
            );
            return;
          }

          const domainToRemove = cartData.find(
            (cartItem) =>
              cartItem.domain === domainName &&
              cartItem.comboId &&
              cartItem.comboId.startsWith(comboId)
          );

          if (domainToRemove) {
            this.$eventBus.$emit(
              "deleteDomainCartPropose",
              domainToRemove.domain
            );
          }
        });

        // Cập nhật trạng thái isInCart cho tất cả domain trong combo
        combo.availableDomains.forEach((domainInfo) => {
          // Kiểm tra và lấy domain name
          let domainName = domainInfo.domain;
          if (!domainName && domainInfo.sld && domainInfo.tld) {
            domainName = `${domainInfo.sld}.${domainInfo.tld}`;
          }

          if (!domainName) {
            console.error(
              "Không tìm thấy domain name trong domainInfo:",
              domainInfo
            );
            return;
          }

          const sld = domainName.split(".")[0];
          const tld = domainInfo.tld;
          this.updateDomainInCartStatus(sld, tld, false);

          // Cập nhật trạng thái inACart cho domain trong CombinationResult
          this.updateCombinationResultDomainStatus(sld, tld, false);
        });

        // Cập nhật trạng thái combo trong cart
        this.ComboIntCart = false;
        this.loadDomainSuggestinCart();

        // Cập nhật trạng thái disable cho tất cả combo
        this.updateComboDisabledStatus();

        // Cập nhật trạng thái isInCart cho tất cả combo
        this.updateComboInCartStatus();

        // Cập nhật trạng thái proseInCart cho combo
        this.updateCombinationResultProseInCart();
      },

      clickRemoveDomainCartAvailable(domain) {
        // Tách sld và tld từ domain name
        const domainParts = domain.split(".");
        if (domainParts.length >= 2) {
          const sld = domainParts[0];
          const tld = domainParts.slice(1).join(".");

          // Cập nhật trạng thái inACart cho domain trong CombinationResult
          this.updateCombinationResultDomainStatus(sld, tld, false);
        }

        this.$eventBus.$emit("deleteDomainCartAvailable", domain);

        // Cập nhật trạng thái disable cho tất cả combo
        this.updateComboDisabledStatus();

        // Cập nhật trạng thái proseInCart cho combo
        this.updateCombinationResultProseInCart();
      },

      clickGetListTLD(tab) {
        let listTld = JSON.parse(localStorage.getItem("Data_Category_TLD"));
        return listTld[tab];
      },

      getListTLDFromCookie() {
        return getCookieTldList("vnx_listpropose_tld");
      },

      getListTLDFromCookieOnePage() {
        return getCookieTldList("vnx_listpropose_tld_one_page");
      },

      getListcateTLDFromCookie() {
        let listTld = JSON.parse(localStorage.getItem("Data_Category_TLD"));
        const merged = [...new Set(listTld.flat())];
        return merged;
      },

      async handleComboDomainsInTab(
        sld,
        listTLD,
        listComboTLD,
        CombinationResult
      ) {
        // Handle combo domains in tab, filter out propose combo if needed
        let comboResults = [];
        if (listComboTLD && listComboTLD.length > 0) {
          try {
            comboResults = await this.checkCombosInTLDList(
              sld,
              listTLD, // Sử dụng toàn bộ listTLD
              listComboTLD
            );
            if (comboResults && comboResults.length > 0 && CombinationResult) {
              const proposeComboStt = CombinationResult.combo?.stt;
              if (proposeComboStt) {
                comboResults = comboResults.filter((combo) => {
                  const comboStt =
                    combo.combo?.stt || combo.combo?.id || combo.combo?.name;
                  return comboStt !== proposeComboStt;
                });
              }
            }
          } catch (error) {
            console.error("Error checking combos:", error);
            comboResults = [];
          }
        }
        return comboResults;
      },

      async checkCombosInTLDList(sld, listTLD, comboData) {
        const comboResults = [];
        if (!comboData || !Array.isArray(comboData) || comboData.length === 0) {
          return comboResults;
        }
        // Lọc combo có TLD thuộc danh sách TLD của tab
        const relevantCombos = comboData.filter((combo) => {
          // Kiểm tra cả tlds và Tlds (chữ thường và chữ hoa)
          const tldsProperty =
            combo.tlds || combo.Tlds || combo.tld || combo.TLD;
          if (!combo || !tldsProperty) {
            return false;
          }
          const tldsInCombo = tldsProperty.split("+").map((tld) => tld.trim());

          // Kiểm tra xem tất cả TLD trong combo có thuộc danh sách TLD của tab không
          const allTldsInTab = tldsInCombo.every((tld) =>
            listTLD.includes(tld)
          );

          // Kiểm tra xem có ít nhất 1 TLD trong combo thuộc danh sách TLD của tab không
          const hasRelevantTld = tldsInCombo.some((tld) =>
            listTLD.includes(tld)
          );
          return allTldsInTab;
        });

        for (let i = 0; i < relevantCombos.length; i++) {
          const combo = relevantCombos[i];
          const tldsProperty =
            combo.tlds || combo.Tlds || combo.tld || combo.TLD;
          const tldsInCombo = tldsProperty.split("+").map((tld) => tld.trim());

          // Lọc chỉ những TLD trong combo thuộc danh sách TLD của tab
          const availableTldsInTab = tldsInCombo.filter((tld) =>
            listTLD.includes(tld)
          );

          if (availableTldsInTab.length === 0) {
            continue;
          }

          // Kiểm tra tất cả TLD có sẵn trong tab có available không
          let allTldsAvailable = true;
          let availableDomains = [];

          for (let j = 0; j < availableTldsInTab.length; j++) {
            const tld = availableTldsInTab[j];
            const domainToCheck = sld + "." + tld;

            try {
              const response = await this.handleApiCheckDomain(domainToCheck);
              const result = response.data;

              if (
                result.result === "success" &&
                result.status === "available"
              ) {
                availableDomains.push({
                  domain: domainToCheck,
                  tld: tld,
                  result: result,
                });
              } else {
                allTldsAvailable = false;
                break;
              }
              // Thêm delay 500ms giữa các request để tránh rate limiting
              await new Promise((resolve) => setTimeout(resolve, 500));
            } catch (error) {
              console.error("Error checking domain:", domainToCheck, error);
              allTldsAvailable = false;
              // Thêm delay ngay cả khi có lỗi
              await new Promise((resolve) => setTimeout(resolve, 500));
              break;
            }
          }

          // Nếu tất cả TLD có sẵn trong tab đều available, tạo combo result
          if (allTldsAvailable && availableDomains.length > 0) {
            // Lấy title và content combo
            const titlecombo =
              combo.titlecombo ||
              combo["Title Combo"] ||
              combo.title_combo ||
              `Combo ${combo.stt || combo.id || combo.name}`;
            const contentcombo =
              combo.contentcombo ||
              combo["Content Combo"] ||
              combo.content_combo ||
              `Gói combo ${availableTldsInTab.join(", ")} (${availableTldsInTab.length
              }/${tldsInCombo.length} TLDs)`;

            // Tính toán giá combo chỉ cho những TLD có sẵn trong tab
            let comboTotalPrice = 0;
            let comboOriginalPrice = 0;

            // Sử dụng cả defaultpricing và Default Pricing
            const defaultPricingProperty =
              combo.defaultpricing ||
              combo["Default Pricing"] ||
              combo.default_pricing;
            if (defaultPricingProperty) {
              const defaultPrices = defaultPricingProperty
                .split("+")
                .map((price) => {
                  return parseInt(price.trim().replace(/\./g, "")) || 0;
                });
              // Chỉ tính giá gốc cho những TLD có sẵn trong tab
              const availableTldCount = availableTldsInTab.length;
              const totalTldCount = tldsInCombo.length;
              if (availableTldCount === totalTldCount) {
                comboOriginalPrice = defaultPrices.reduce(
                  (sum, price) => sum + price,
                  0
                );
              } else {
                // Tính tỷ lệ giá gốc dựa trên số TLD có sẵn
                const pricePerTld =
                  defaultPrices.reduce((sum, price) => sum + price, 0) /
                  totalTldCount;
                comboOriginalPrice = Math.round(
                  pricePerTld * availableTldCount
                );
              }
            }

            const comboPricingProperty =
              combo.combopricing ||
              combo["Combo Pricing"] ||
              combo.combo_pricing;
            if (comboPricingProperty) {
              const comboPrices = comboPricingProperty
                .split("+")
                .map((price) => {
                  return parseInt(price.trim().replace(/\./g, "")) || 0;
                });
              // Chỉ tính giá combo cho những TLD có sẵn trong tab
              const availableTldCount = availableTldsInTab.length;
              const totalTldCount = tldsInCombo.length;
              if (availableTldCount === totalTldCount) {
                comboTotalPrice = comboPrices.reduce(
                  (sum, price) => sum + price,
                  0
                );
              } else {
                // Tính tỷ lệ giá combo dựa trên số TLD có sẵn
                const pricePerTld =
                  comboPrices.reduce((sum, price) => sum + price, 0) /
                  totalTldCount;
                comboTotalPrice = Math.round(pricePerTld * availableTldCount);
              }
            } else {
              comboTotalPrice = availableDomains.reduce((sum, domain) => {
                const price =
                  parseInt(domain.result.pricedomain.replace(/\./g, "")) || 0;
                return sum + price;
              }, 0);
            }

            // Tính phần trăm giảm giá
            let discountPercent = 0;
            if (comboOriginalPrice > 0 && comboTotalPrice > 0) {
              discountPercent = Math.round(
                ((comboOriginalPrice - comboTotalPrice) / comboOriginalPrice) *
                100
              );
            }

            // Tạo combo result object
            const comboResult = {
              domainName: `${sld}.${availableTldsInTab.join("+")}`,
              sld: sld,
              tld: availableTldsInTab.join("+"),
              status: "available",
              price: comboTotalPrice,
              premium: false,
              originalPrice: comboOriginalPrice,
              discount: discountPercent,
              isInCart: false,
              tab: [tab],
              domainVN: "available",
              tooltipTitle: titlecombo,
              tooltipContent: contentcombo,
              isCombo: true,
              combo: combo,
              availableDomains: availableDomains,
              comboId: `combo_${combo.stt || combo.id || combo.name
                }_${Date.now()}`,
              totalTldsInCombo: tldsInCombo.length,
              availableTldsInTab: availableTldsInTab.length,
              titlecombo: titlecombo,
              contentcombo: contentcombo,
            };

            comboResults.push(comboResult);
          }
        }
        return comboResults;
      },

      // Function mới: Click add combo với kiểm tra disable
      clickAddComboToCartWithCheck(combo) {
        // Kiểm tra xem combo có thể thêm vào cart không
        if (!this.checkCanAddComboToCart(combo)) {
          return false;
        }

        // Gọi function gốc để thêm combo
        this.clickComboAddToCart(combo);
        return true;
      },

      // Function mới: Click remove domain riêng lẻ với cập nhật trạng thái isInCart
      clickRemoveFromCartTwo(sld, tld) {
        const domainToRemove = `${sld}.${tld}`;

        // Xóa domain khỏi cart
        this.$eventBus.$emit("deleteDomainCartAvailable", domainToRemove);

        // Cập nhật trạng thái isInCart cho domain
        this.updateDomainInCartStatus(sld, tld, false);

        // Cập nhật trạng thái inACart cho domain trong CombinationResult
        this.updateCombinationResultDomainStatus(sld, tld, false);

        // Cập nhật DomainIntCart nếu domain được xóa là domain chính
        if (this.DomainNameSld === sld && this.DomainNameTld === tld) {
          this.DomainIntCart = false;
        }

        // Cập nhật trạng thái disable cho tất cả combo
        this.updateComboDisabledStatus();
      },

      normalizeCombo(combo) {
        if (!combo) return null;
        // Nếu đã chuẩn, trả về luôn
        if (combo.availableDomains) return combo;
        // Nếu là CombinationResult đặc biệt, build lại
        if (combo.combo && combo.comboTLDs && combo.availableDomains) {
          return {
            ...combo,
            availableDomains: combo.availableDomains.map((d) => ({
              domain:
                d.domain ||
                d.domainName ||
                (d.sld && d.tld ? d.sld + "." + d.tld : ""),
              sld: d.sld,
              tld: d.tld,
              // ... các trường khác nếu cần
            })),
          };
        }
        return null;
      },

      async checkCombinationTLDs(sld, combinationTLDs) {
        // Convert string to array if needed (trường hợp cũ)
        if (typeof combinationTLDs === "string") {
          let tldArray = combinationTLDs.split(",").map((tld) => tld.trim());
          for (let i = 0; i < tldArray.length; i++) {
            const tld = tldArray[i];
            const domainToCheck = sld + "." + tld;
            try {
              const response = await this.handleApiCheckDomain(domainToCheck);
              const result = response.data;
              if (
                result.result === "success" &&
                result.status === "available"
              ) {
                // Tính giá gốc và giá combo cho trường hợp đơn giản
                let originalPrice = 0;
                let price = 0;
                if (result.pricedomain) {
                  const priceVal =
                    parseInt(result.pricedomain.replace(/\./g, "")) || 0;
                  originalPrice = priceVal;
                  price = priceVal;
                }
                // Trả về object chuẩn
                return {
                  availableDomains: [
                    {
                      domain: domainToCheck,
                      sld: sld,
                      tld: tld,
                    },
                  ],
                  sld: sld,
                  tlds: tld,
                  isCombo: true,
                  price: price,
                  originalPrice: originalPrice,
                };
              }
            } catch (error) {
              continue;
            }
          }
          return null;
        }

        // Trường hợp mới: combinationTLDs là array của combo objects
        if (
          Array.isArray(combinationTLDs) &&
          combinationTLDs.length > 0 &&
          typeof combinationTLDs[0] === "object"
        ) {
          for (
            let comboIndex = 0;
            comboIndex < combinationTLDs.length;
            comboIndex++
          ) {
            const combo = combinationTLDs[comboIndex];
            if (!combo.tlds) continue;
            const tldsInCombo = combo.tlds.split("+").map((tld) => tld.trim());
            let allTldsAvailable = true;
            let availableDomains = [];
            for (let tldIndex = 0; tldIndex < tldsInCombo.length; tldIndex++) {
              const tld = tldsInCombo[tldIndex];
              const domainToCheck = sld + "." + tld;
              try {
                const response = await this.handleApiCheckDomain(domainToCheck);
                const result = response.data;
                if (
                  result.result === "success" &&
                  result.status === "available"
                ) {
                  availableDomains.push({
                    domain: domainToCheck,
                    sld: sld,
                    tld: tld,
                  });
                } else {
                  allTldsAvailable = false;
                  break;
                }
                await new Promise((resolve) => setTimeout(resolve, 500));
              } catch (error) {
                allTldsAvailable = false;
                await new Promise((resolve) => setTimeout(resolve, 500));
                break;
              }
            }
            if (allTldsAvailable && availableDomains.length > 0) {
              // Chỉ giữ lại các trường số/id cần thiết cho combo
              const { stt, Tlds, tlds, combopricing, defaultpricing } = combo;
              // Tính tổng giá gốc (originalPrice)
              let originalPrice = 0;
              if (defaultpricing) {
                const defaultPrices = (defaultpricing || "")
                  .split("+")
                  .map((p) => parseInt(p.trim().replace(/\./g, "")) || 0);
                originalPrice = defaultPrices.reduce(
                  (sum, price) => sum + price,
                  0
                );
              }
              // Tính tổng giá combo (price)
              let price = 0;
              if (combopricing) {
                const comboPrices = (combopricing || "")
                  .split("+")
                  .map((p) => parseInt(p.trim().replace(/\./g, "")) || 0);
                price = comboPrices.reduce((sum, price) => sum + price, 0);
              }
              return {
                availableDomains: availableDomains,
                sld: sld,
                tlds: combo.tlds || combo.Tlds || combo.tld || combo.TLD,
                isCombo: true,
                stt: stt,
                combopricing: combopricing,
                defaultpricing: defaultpricing,
                price: price,
                originalPrice: originalPrice,
                isDisabled: false, // Thêm thuộc tính isDisabled
              };
            }
          }
          return null;
        }
        return null;
      },
    },
  };
}

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.domain-result")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        Domain: "",
        ResultEmpty: true,
        ResultError: false,
      },
      mounted() {
        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          this.ResultEmpty = false;
          if (id === data.box_result && data.domainInput !== "") {
            this.hanleTriggerResult(data.domainInput);
          }
        });

        this.$eventBus.$on("deleteDomainCart", (domain) => {
          this.checkDomainCart(domain);
        });
      },

      methods: {
        async hanleTriggerResult(domainInput) {
          try {
            this.IsLoading = true;
            this.ListTLDCalled = [];
            this.Domain = domainInput;

            let count = 0;
            let totalResultDomain = [];
            while (totalResultDomain.length < 5 && count < 10) {
              count++;
              // reset list tld called
              let listTLD = [];
              let isInitPopular = false;

              // Nếu có dữ liệu đã sắp xếp theo thứ tự phổ biến
              // thì lấy 5 tld đầu tiên
              // nếu không thì lấy tất cả tld

              if (this.ListTLDSorted?.length > 0) {
                listTLD = this.ListTLDSorted.filter((item) => {
                  return !this.ListTLDCalled.includes(item);
                }).slice(0, 5);
              } else {
                let storedData = sessionStorage.getItem("Data_TLD_Sussgest");
                listTLD = JSON.parse(storedData);
                isInitPopular = true;
              }

              const resultDomain = await this.getDataDomain(
                domainInput,
                listTLD,
                isInitPopular
              );
              totalResultDomain = [...totalResultDomain, ...resultDomain];
              // lưu lại danh sách tld đã gọi
              this.ListTLDCalled = [...this.ListTLDCalled, ...listTLD];
            }

            this.ListDomain = totalResultDomain;
            this.IsLoading = false;
            this.loadDomainSuggestinCart();
          } catch (error) {
            console.error("Error getDataDomain:", error);
            this.IsLoading = false;
          }
        },

        async loadMoreDomain() {
          try {
            this.IsLoadingMore = true;

            //Khơi tạo biến đếm
            let newDomains = [];

            let count = 0;
            while (newDomains.length < 5 && count < 5) {
              count++;
              const listTLD = this.ListTLDSorted.filter(
                (item) => !this.ListTLDCalled.includes(item)
              ).slice(0, 5);

              // Nếu đã gọi hết TLD thì không gọi nữa
              if (listTLD.length === 0) {
                break;
              }

              const resultDomain = await this.getDataDomain(
                this.Domain,
                listTLD,
                false
              );

              // lưu lại kết quả tìm kiếm, sau đó đếm lại ở trên điều kiện loop
              newDomains = [...newDomains, ...resultDomain];
              this.ListTLDCalled = [...this.ListTLDCalled, ...listTLD];
            }

            // Cập nhật danh sách domain
            this.ListDomain = [...this.ListDomain, ...newDomains];
            this.IsLoadingMore = false;

            this.loadDomainSuggestinCart();

            // trả về danh sách domain
            return this.ListDomain;
          } catch (error) {
            this.IsLoadingMore = false;
            console.error("Error in loadMoreDomain:", error);
          }
        },

        async getDataDomain(domainInput, listTLD, isInitPopular) {
          try {
            return new Promise((resolve) => {
              vnx_post_ajax_DataSugguest(
                "POST",
                "get_listDomian_suggest_center",
                domainInput,
                listTLD.join(", ")
              )
                .then((response) => {
                  if (response.success == true) {
                    let filteredDomains = response.data.filter(
                      (item) => !item.error && item.isAvailable !== false
                    );

                    filteredDomains = filteredDomains.map((item) => {
                      return {
                        ...item,
                        isPremium: !item?.pricing?.register["1"]
                          ? true
                          : item.isPremium,
                      };
                    });
                    if (isInitPopular) {
                      this.initTLDPopular(response, listTLD);
                    }

                    resolve(filteredDomains);
                  }
                })
                .catch((error) => {
                  console.error("Error getDataDomain:", error);
                  this.IsLoading = false;
                  this.ResultEmpty = false;
                  this.ResultError = true;
                });
            });
          } catch (error) {
            this.handleErrors(error);
          }
        },
      },
    });
  });

//----------------------------------------------
document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-main-domain-page-whois")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        DomainShowResult: false,
        LoadingResult: true,
        DomainPremium: false,
        DomainAvailable: false,
        DomainError: false,
        DomainSRError: "",
        DomainName: "",
        DomainPrice: "",
        DomainNameSld: "",
        DomainNameTld: "",
        DomainPriceReduction: "",
        DomainPricePercent: "",
        DomainVnError: false,
        DomainCookie: ".vietnix.vn",
        DomainGetCookie: "",
        DomainDatatCart: "",
        WhoisURL: window.whoisUrl,
      },
      created() { },
      mounted() {
        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          if (data.box_result.includes(id) && data.domainInput !== "") {
            this.DomainShowResult = false;
            this.getMainDomain(data.domainInput);
          }
        });
        this.$eventBus.$on("deleteDomainCart", (domain) => {
          let domainInputCart = this.DomainName;
          if (domain == domainInputCart) {
            this.changerButtonCart();
          }
        });
      },

      methods: {
        getMainDomain(domainInput) {
          this.LoadingResult = true;
          this.DomainVnError = false;

          let storedData = sessionStorage.getItem("Data_TLD_Sussgest");
          if (storedData) {
            // lấy thông tin domain chính
            vnx_post_ajax_Data("POST", "check_domain_data_center", {
              data: {
                domain: domainInput,
              },
            }).then((data) => {
              var response = data.data;
              this.LoadingResult = false;
              this.DomainShowResult = true;
              this.DomainError = false;
              this.DomainName = domainInput;
              if (response.result === "success") {
                const status = response.status;
                const domainVn = response.domainVn;
                this.DomainNameSld = response.sld;
                this.DomainNameTld = response.tld;
                this.DomainPrice = response.pricedomain;

                if (response?.tld === "vn" && response?.sld?.length <= 2) {
                  this.DomainVnError = true;
                }

                this.DomainPremium =
                  response?.pricedomain == 0 ? true : !!response.premium;

                if (domainVn === "available") {
                  this.DomainAvailable = status === "available" ? true : false;
                  this.DomainPriceReduction = getPriceDomainData(response.tld);
                  this.DomainPricePercent = calculatePriceDomain(
                    response.pricedomain,
                    this.DomainPriceReduction
                  );
                  if (this.DomainPricePercent === 0) {
                    this.DomainPriceReduction = "";
                  }

                  var obj = {
                    domain: domainInput,
                    register: "1",
                    authen_code: "",
                    sld: this.DomainNameSld,
                    tld: this.DomainNameTld,
                  };
                  if (checkCookieDomain(this.DomainCartCookie, obj) != true) {
                    this.DomainIntCart = false;
                  } else {
                    this.DomainIntCart = true;
                  }
                  setTimeout(() => {
                    let domainInputCart = $(this.$el).find(".add_domain_cart");
                    domainInputCart.attr("data-domain", domainInput);
                    domainInputCart.attr("data-sld", this.DomainNameSld);
                    domainInputCart.attr("data-tld", this.DomainNameTld);
                  }, 100);
                } else if (
                  status === "available" &&
                  domainVn === "unavailable"
                ) {
                  this.DomainAvailable = false;
                  this.DomainError = false;
                } else {
                  this.DomainAvailable = false;
                  // alert('Tên miền đã tồn tại');
                }
              } else {
                this.DomainError = true;
                this.DomainAvailable = false;
                // alert('tên miền không hợp lệ');
              }
              this.DisableButton = false;
              this.$eventBus.$emit("completeGetMainDomain", { success: true });
            });
          }
        },
        changerButtonCart() {
          // Kiểm tra trạng thái thực tế trong cart thay vì reset
          this.DomainIntCart = false;
          this.DomainPremium = false;
        },
      },
    });
  });

//----------------------------------------------

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.domain-result-page-whois")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],

      data: {
        InitHidden: true,
        ResultEmpty: true,
        DomainAvailable: true,
        DomainPremium: false,
        DomaininCart: false,
        WhoisURL: window.whoisUrl,
      },
      created() { },
      mounted() {
        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          this.ResultEmpty = false;
          this.IsLoading = false;
          if (data.box_result.includes(id) && data.domainInput !== "") {
            this.ListDomain = [];
            this.hanleTriggerResult(data.domainInput);
          }
        });
        this.$eventBus.$on("deleteDomainCart", (domain) => {
          this.checkDomainCart(domain);
        });
      },

      methods: {
        async hanleTriggerResult(domainInput) {
          try {
            this.runProgressBar();
            this.IsLoading = true;
            this.ListTLDCalled = [];
            let listTLD = [];
            let isInitPopular = false;
            this.Domain = domainInput;

            // Nếu có dữ liệu đã sắp xếp theo thứ tự phổ biến
            // thì lấy 5 tld đầu tiên
            // nếu không thì lấy tất cả tld
            if (this.ListTLDSorted?.length > 0) {
              listTLD = this.ListTLDSorted.filter(
                (item) =>
                  !this.ListTLDCalled.includes(item) &&
                  item !== splitDomain(domainInput).tld
              ).slice(0, 5);
            } else {
              let storedData = sessionStorage.getItem("Data_TLD_Sussgest");
              listTLD = JSON.parse(storedData);
              isInitPopular = true;
            }

            // lưu lại danh sách tld đã gọi
            const response = await this.getDataDomain(
              domainInput,
              listTLD,
              isInitPopular
            );

            this.ListTLDCalled = listTLD;

            this.completeProgressBar();
            this.loadDomainSuggestinCart();

            setTimeout(() => {
              this.emitSuccessEvent();
              this.ListDomain = response;
              this.IsLoading = false;
              this.loadDomainSuggestinCart();
            }, 300);
          } catch (error) {
            console.error("Error hanleTriggerResult:", error);
          }
        },

        async loadMoreDomain() {
          try {
            this.IsLoadingMore = true;
            // lấy 5 tld tiếp theo
            const listTLD = this.ListTLDSorted.filter(
              (item) =>
                !this.ListTLDCalled.includes(item) &&
                item != splitDomain(this.Domain).tld
            ).slice(0, 5);

            const response = await this.getDataDomain(
              this.Domain,
              listTLD,
              false
            );

            this.ListTLDCalled = [...this.ListTLDCalled, ...listTLD];
            this.ListDomain = [...this.ListDomain, ...response];
            this.IsLoadingMore = false;

            this.loadDomainSuggestinCart();
          } catch (error) {
            this.IsLoadingMore = false;
            console.error("Error in loadMoreDomain:", error);
          }
        },

        async getDataDomain(domainInput, listTLD, isInitPopular) {
          try {
            return new Promise((resolve) => {
              vnx_post_ajax_DataSugguest(
                "POST",
                "get_listDomian_suggest_center",
                domainInput,
                listTLD.join(", ")
              )
                .then((response) => {
                  if (response.success == true) {
                    let filteredDomains = response.data
                      .filter(
                        (item) => !item.error && item.domainName != domainInput
                      )
                      .map((item) => {
                        return {
                          ...item,
                          errorDomainVN:
                            item.tld === "vn" && item?.sld?.length <= 2,
                          isPremium: !item?.pricing?.register["1"]
                            ? true
                            : item.isPremium,
                        };
                      });

                    // khởi tạo danh sách tld phổ biến
                    // 1. lấy danh sách tld phổ biến
                    // 2. khi isInitPopular true thì listTLD là tất cả tld
                    // 3. sắp xếp đưa tld phổ biên lên đầu
                    // 4. lưu vào local storage
                    if (isInitPopular) {
                      this.initTLDPopular(response, listTLD);
                    }
                    resolve(filteredDomains);
                  }
                })
                .catch((error) => {
                  console.error("Error vnx_post_ajax_DataSugguest:", error);
                });
            });
          } catch (error) {
            console.error("Error getDataDomain:", error);
          }
        },
      },
    });
  });

//----------------------------------------------

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-whois")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        Domain: {
          sld: "Ontask",
          tld: "com",
        },
        WhoisURL: window.whoisUrl,
        IsLoading: false,
        IsEmpty: true,
        IsError: false,
        Whois: {
          domainName: "",
          creationDate: "",
          registrarExpirationDate: "",
          nameServers: "",
          status: "",
          dnssec: "",
        },
      },
      created() { },
      mounted() {
        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          this.IsLoading = false;
          if (data.box_result.includes(id) && data.domainInput !== "") {
            this.setDataWhois(data.domainInput);
          }
        });
      },

      methods: {
        setDataWhois(domainInput) {
          this.Domain = splitDomain(domainInput);
          this.IsLoading = true;
          // Chạy thành tiến trình loading
          this.runProgressBar();
          this.resetData();
          vnx_post_ajax_whois("POST", "get_whois_domain_center", domainInput).then(
            (response) => {
              if (response.success == true) {
                const result = this.convertArrayToObject(response.data.data);
                if (result.status != "" && result.status != "undefined") {
                  this.Whois = result;
                  this.IsEmpty = false;

                  if (this.Domain.tld === "vn" && this.Domain.sld.length <= 2) {
                    this.IsEmpty = true;
                  }

                  this.completeProgressBar();
                  setTimeout(() => {
                    this.IsLoading = false;
                  }, 300);
                } else {
                  // nếu trạng thái là rỗng thì sẽ chuyển sang check available
                  this.IsError = true;
                  this.IsEmpty = true;
                  this.IsLoading = false;
                  // this.checkAvailabel(domainInput);
                }

                // phát sự kiện tới input tìm kiếm
                this.emitSuccessEvent();
              } else {
                this.IsError = true;
              }
            }
          );
        },
        convertArrayToObject(dataArray) {
          const data = dataArray.reduce((acc, item) => {
            acc[item.label.toLowerCase().replace(/\s+/g, "")] = item.value;
            return acc;
          }, {});

          return {
            domainName: data?.domainname || "",
            creationDate: data?.creationdate || "",
            registrarExpirationDate: data?.registrarexpirationdate || "",
            nameServers: data?.nameservers || "",
            status: data?.domainstatus || "",
            dnssec: data?.dnssec || "",
          };
        },

        resetData() {
          this.Whois = {
            domainName: "",
            creationDate: "",
            registrarExpirationDate: "",
            nameServers: "",
            status: "",
            dnssec: "",
          };
          this.IsError = false;
          this.IsEmpty = true;
        },

        checkAvailabel(domainInput) {
          vnx_post_ajax_Data("POST", "check_domain_data_center", {
            data: {
              domain: domainInput,
            },
          }).then((response) => {
            if (
              response.result === "success" &&
              response.status === "available"
            ) {
              window.location.href = this.WhoisURL + "?domain=" + domainInput;
            }

            this.completeProgressBar();
          });
        },
      },
    });
  });

//----------------------------------------------

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-suggets-detail-whois")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        IsLoading: false,
        IsEmpty: true,
        ListDomain: [],
      },
      created() { },
      mounted() {
        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          if (data.box_result.includes(id) && data.domainInput !== "") {
            this.setDataDomainSuggest(data.domainInput);
          }
        });
      },

      methods: {
        async setDataDomainSuggest(domainInput) {
          try {
            this.IsLoading = true;
            this.ListDomain = [];
            this.IsEmpty = true;
            this.ListTLDCalled = [];
            let listTLD = [];
            let isInitPopular = false;
            this.Domain = domainInput;

            // Nếu có dữ liệu đã sắp xếp theo thứ tự phổ biến
            // thì lấy 5 tld đầu tiên
            // nếu không thì lấy tất cả tld
            if (this.ListTLDSorted?.length > 0) {
              listTLD = this.ListTLDSorted.slice(0, 4);
            } else {
              let storedData = sessionStorage.getItem("Data_TLD_Sussgest");
              listTLD = JSON.parse(storedData);
              isInitPopular = true;
            }

            // Khởi tạo biến đếm số lần gọi API
            let apiCallCount = 0;
            const maxApiCalls = 15; // Giới hạn số lần gọi API

            // Vòng lặp while để gọi API cho đến khi đủ 4 domain hoặc đạt giới hạn gọi API
            while (this.ListDomain.length <= 4 && apiCallCount < maxApiCalls) {
              const response = await vnx_post_ajax_DataSugguest(
                "POST",
                "get_listDomian_suggest_center",
                domainInput,
                listTLD.join(", ")
              );

              if (response.success) {
                const filteredDomains = response.data.filter(
                  (item) =>
                    !item.error &&
                    item.domainName != domainInput &&
                    item.isAvailable &&
                    !!item?.pricing?.register["1"] &&
                    item.isPremium === false &&
                    !(item.sld.length <= 2 && item.tld === "vn")
                );

                this.ListDomain = [...this.ListDomain, ...filteredDomains];

                // Nếu đã gọi lần đầu và cần khởi tạo TLD phổ biến
                if (apiCallCount === 0 && isInitPopular) {
                  this.initTLDPopular(response, listTLD);
                }

                // Cập nhật danh sách TLD đã gọi
                this.ListTLDCalled = [...this.ListTLDCalled, ...listTLD];

                // Lấy danh sách TLD tiếp theo nếu cần
                const remainingTLDs = this.ListTLDSorted.filter(
                  (tld) => !this.ListTLDCalled.includes(tld)
                );
                listTLD = remainingTLDs.slice(0, 4);

                // Tăng số lần gọi API
                apiCallCount++;

                // Nếu không còn TLD nào để gọi thì thoát vòng lặp
                if (listTLD.length === 0) break;
              } else {
                break;
              }
            }
            // Giới hạn kết quả chỉ lấy 5 domain đầu tiên
            this.ListDomain = this.ListDomain.slice(0, 4);
            this.IsEmpty = this.ListDomain.length === 0;
            this.IsLoading = false;
          } catch (error) {
            console.error("Error in setDataDomainSuggest:", error);
            this.IsLoading = false;
            this.IsEmpty = true;
          }
        },
      },
    });
  });

//----------------------------------------------

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-muti-whois")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        InitHidden: true,
        ResultEmpty: true,
        DomainAvailable: true,
        DomainPremium: false,
        DomaininCart: false,
        TotalDomainSearch: 0,
        TotalDomainSuccess: 0,
        WhoisURL: window.whoisUrl,
      },

      created() { },
      mounted() {
        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          if (data.box_result.includes(id) && data.domainArray.length > 0) {
            this.ListDomain = [];
            this.getMutiDomain(data.domainArray);
          }
        });
      },

      methods: {
        async getMutiDomain(arrayDomain) {
          const classDomains = classifyDomains(arrayDomain);
          //----reset data ----------------
          this.TotalDomainSearch = classDomains.length;
          this.TotalDomainSuccess = 0;
          this.ResultEmpty = true;
          this.ResultError = false;
          this.IsLoading = true;
          this.ListDomain = [];
          this.runProgressBar();

          //----get data domain ----------------
          classDomains.map(async (item, index) => {
            const response = await this.getDataDomain(item.name, item.tld);

            if (response.length > 0) {
              // mở hiện thị kết quả
              this.ResultEmpty = false;

              this.TotalDomainSuccess += 1;

              // loại bỏ domain có lỗi
              const listdata = response.filter((item) => !item.error);
              this.ListDomain = this.ListDomain.concat(listdata);

              if (this.TotalDomainSearch <= this.TotalDomainSuccess) {
                this.IsLoading = false;
                this.completeProgressBar();

                this.emitSuccessEvent();
              }
            }
          });
        },
        async getDataDomain(domain, listTLD) {
          try {
            const response = await vnx_post_ajax_DataSugguest(
              "POST",
              "get_listDomian_whois_center",
              domain,
              listTLD.join(", ")
            );
            if (response.success) {
              let filteredDomains = response.data.map((item) => ({
                ...item,
                errorDomainVN: item.tld === "vn" && item?.sld?.length <= 2,
                error: !item.legacyStatus,
                isPremium: !item?.pricing?.register["1"]
                  ? true
                  : item.isPremium,
              }));

              return filteredDomains;
            } else {
              return [];
            }
          } catch (error) {
            this.handleErrors(error);
            return [];
          }
        },
      },
    });
  });

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-muti-domain")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        ResultEmpty: true,
        ElShowTotalResult: window.elShowTotalResult,
        TotalDomainSearch: 0,
        TotalDomainSuccess: 0,
      },

      created() { },
      mounted() {
        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          if (data.box_result.includes(id) && data.domainArray.length > 0) {
            this.ListDomain = [];
            this.getMutiDomain(data.domainArray);
          }
        });

        this.$eventBus.$on("deleteDomainCart", (domain) => {
          this.checkDomainCart(domain);
        });
      },

      methods: {
        async getMutiDomain(arrayDomain) {
          const classDomains = classifyDomains(arrayDomain);

          //----reset data ----------------
          this.TotalDomainSearch = classDomains.length;
          this.TotalDomainSuccess = 0;
          this.ResultEmpty = false;
          this.ResultError = false;
          this.IsLoading = true;
          this.ListDomain = [];
          this.runProgressBar();

          //----get data domain ----------------
          classDomains.map(async (item, index) => {
            const response = await this.getDataDomain(item.name, item.tld);

            if (response.length > 0) {
              // mở hiện thị kết quả
              this.ResultEmpty = false;

              this.TotalDomainSuccess += 1;

              // loại bỏ domain có lỗi
              const listdata = response.filter((item) => !item.error);
              this.ListDomain = this.ListDomain.concat(listdata);

              if (this.TotalDomainSearch <= this.TotalDomainSuccess) {
                this.IsLoading = false;
                this.completeProgressBar();
                this.loadDomainSuggestinCart();
                this.updateTotalResult();
                this.emitSuccessEvent();
              }
            }
          });
        },
        async getDataDomain(domain, listTLD) {
          try {
            const response = await vnx_post_ajax_DataSugguest(
              "POST",
              "get_listDomian_whois_center",
              domain,
              listTLD.join(", ")
            );
            if (response.success) {
              let filteredDomains = response.data.map((item) => ({
                ...item,
                errorDomainVN: item.tld === "vn" && item?.sld?.length <= 2,
                error: !item.legacyStatus,
                isPremium: !item?.pricing?.register["1"]
                  ? true
                  : item.isPremium,
              }));
              return filteredDomains;
            } else {
              return [];
            }
          } catch (error) {
            this.handleErrors(error);
            return [];
          }
        },
        updateTotalResult() {
          let totalResult = this.ListDomain.length;
          let elTotalResult = document.querySelector(this.ElShowTotalResult);
          if (elTotalResult) {
            elTotalResult.innerHTML = totalResult;
          }
        },
      },
    });
  });

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-domain-ai-onpage")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        Domain: "",
        ResultEmpty: true,
        ResultError: false,
        ResultAgain: false,
        ResultNewEmpty: true,
        TotalDomainSearch: [],
        TotalDomainSuccess: 0,
        // Add timeout and retry tracking
        searchTimeout: null,
        maxSearchTime: 30000, // 30 seconds timeout
        retryCount: 0,
        maxRetries: 2,
        triedDomainNames: new Set(),
        // Add request tracking for cancellation
        activeRequests: [],
        isSearchCancelled: false,
      },
      created() {
        this.$eventBus.$on("triggerLoadingAi", (data) => {
          this.IsLoading = data.IsLoading;
          this.ResultEmpty = false;
          this.ResultError = false;
          // Reset retry count on new search
          this.retryCount = 0;
          this.triedDomainNames = new Set();
          // Reset cancellation flag
          this.isSearchCancelled = false;
        });
      },
      mounted() {
        this.$eventBus.$on("triggerResultAi", (data) => {
          let id = "#" + this.$el.id;
          if (data.idBoxResult.includes(id) && data.domainArray !== "") {
            this.ListDomain = [];
            if (data.domainInput) {
              this.Domain = data.domainInput;
            }

            // Handle error case
            if (data.domainArray === "error") {
              this.ResultError = true;
              this.ResultAgain = false; // Show error HTML
              this.IsLoading = false;
              this.$eventBus.$emit("triggerLoadingAiBtn", {
                DomainLoading: false,
              });
              return;
            }

            // Check if domainArray is valid
            if (!Array.isArray(data.domainArray)) {
              this.ResultError = true;
              this.ResultAgain = false; // Show error HTML
              this.IsLoading = false;
              this.$eventBus.$emit("triggerLoadingAiBtn", {
                DomainLoading: false,
              });
              return;
            }

            if (
              data.domainArray.length === 1 &&
              data.domainArray[0] === "none"
            ) {
              this.ResultAgain = true;
              this.ResultError = true;
              this.IsLoading = false;
              this.$eventBus.$emit("triggerLoadingAiBtn", {
                DomainLoading: false,
              });
              return;
            } else {
              this.getMutiDomain(data.domainArray);
            }
          }
        });

        this.$eventBus.$on("triggerLoadingAi", (data) => {
          this.IsLoading = data.IsLoading;

          // If loading is stopped due to error, cancel current search
          if (!data.IsLoading && this.IsLoading) {
            this.isSearchCancelled = true;
          }
        });

        this.$eventBus.$on("deleteDomainCart", (domain) => {
          this.checkDomainCart(domain);
        });
        this.$eventBus.$on("triggerOffResultEmptyAi", (data) => {
          this.ResultNewEmpty = data.ResultNewEmpty;
        });
      },

      methods: {
        async getMutiDomain(domainArray) {
          // Clear any existing timeout
          if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
          }

          // Clear any existing requests
          this.cancelAllRequests();

          const result = {};
          domainArray.forEach((domain) => {
            domain = domain.toLowerCase();
            this.triedDomainNames.add(domain);
            const parts = domain.split(".");
            const name = parts[0];
            const tld = parts.slice(1).join(".");
            if (!result[name]) {
              result[name] = new Set();
            }
            result[name].add(tld);
          });
          const classDomains = Object.entries(result).map(([name, tlds]) => ({
            name,
            tld: Array.from(tlds),
          }));

          this.ResultEmpty = false;
          this.ResultError = false;
          this.IsLoading = true;
          this.IsLoadingMore = true;
          this.ListDomain = [];
          this.isSearchCancelled = false;

          // Set timeout for entire search process
          this.searchTimeout = setTimeout(() => {
            this.stopSearch("Search timeout reached");
          }, this.maxSearchTime);

          const batchSize = 10;
          const maxResults = 10;
          let availableCount = 0;

          try {
            for (let i = 0; i < classDomains.length; i += batchSize) {
              // Check if search was cancelled
              if (this.isSearchCancelled) {
                break;
              }

              // Check if search was stopped
              if (this.ResultError && this.ResultAgain) {
                break;
              }

              // Đã đủ domain hiển thị, không cần gọi thêm batch nữa
              if (
                this.ListDomain.length >= maxResults ||
                availableCount >= maxResults
              ) {
                break;
              }

              const batch = classDomains.slice(i, i + batchSize);

              const promises = batch.map(async (item) => {
                if (item.name != "string") {
                  try {
                    const response = await this.getDataDomainWithTimeout(
                      item.name,
                      item.tld
                    );
                    if (response.length > 0) {
                      const listdata = response.filter((item) => !item.error);
                      // Render dần: domain nào lấy được dữ liệu là hiển thị
                      // ngay, không đợi cả batch/toàn bộ danh sách xong.
                      if (
                        listdata.length > 0 &&
                        !this.isSearchCancelled &&
                        this.ListDomain.length < maxResults
                      ) {
                        const remainingSlots =
                          maxResults - this.ListDomain.length;
                        const toAdd = listdata.slice(0, remainingSlots);
                        this.ListDomain = this.ListDomain.concat(toAdd);
                        availableCount += toAdd.filter(
                          (domain) =>
                            domain.isAvailable === true &&
                            domain.isPremium === false
                        ).length;
                        this.IsLoading = false;
                        this.loadDomainSuggestinCart();
                      }
                    }
                  } catch (error) {
                    // Bỏ qua domain lỗi/timeout, các domain khác vẫn tiếp tục render
                  }
                } else {
                  this.ResultAgain = true;
                }
              });

              await Promise.all(promises);
            }

            // Clear timeout since search completed
            if (this.searchTimeout) {
              clearTimeout(this.searchTimeout);
              this.searchTimeout = null;
            }

            // Don't process results if search was cancelled
            if (this.isSearchCancelled) {
              this.IsLoadingMore = false;
              return;
            }

            if (this.ListDomain.length > 0) {
              this.IsLoading = false;
              this.IsLoadingMore = false;
              this.ResultError = false;
              this.ResultAgain = false;
            } else if (this.Domain && this.retryCount < this.maxRetries) {
              this.retryCount++;
              const moreSuggestions = await this.fetchMoreAiSuggestions();
              const newNames = moreSuggestions.filter(
                (d) => d !== "none" && !this.triedDomainNames.has(d.toLowerCase())
              );
              if (newNames.length > 0 && !this.isSearchCancelled) {
                return this.getMutiDomain(newNames);
              }
              this.IsLoading = false;
              this.IsLoadingMore = false;
              // ResultAgain = true để hiện "không tìm thấy kết quả", không phải lỗi đường truyền
              this.ResultError = true;
              this.ResultAgain = true;
            } else {
              this.IsLoading = false;
              this.IsLoadingMore = false;
              this.ResultError = true;
              this.ResultAgain = true;
            }
          } catch (error) {
            this.stopSearch("Critical error occurred");
          }

          this.$eventBus.$emit("triggerLoadingAiBtn", {
            DomainLoading: false,
          });
        },

        fetchMoreAiSuggestions() {
          return new Promise((resolve) => {
            $.ajax({
              url: admin_ajax_url,
              type: "POST",
              dataType: "json",
              data: {
                action: "get_domain_suggest_ai_center",
                query: this.Domain,
              },
              success: (response) => {
                if (
                  response.success &&
                  response.data &&
                  Array.isArray(response.data.suggestions_ai)
                ) {
                  resolve(response.data.suggestions_ai);
                } else {
                  resolve([]);
                }
              },
              error: () => resolve([]),
            });
          });
        },

        async getDataDomainWithTimeout(domain, listTLD) {
          const timeout = 10000; // 10 seconds per domain check

          return new Promise((resolve, reject) => {
            const timeoutId = setTimeout(() => {
              reject(new Error(`Timeout for domain ${domain}`));
            }, timeout);

            // Check if search was cancelled before making request
            if (this.isSearchCancelled) {
              clearTimeout(timeoutId);
              reject(new Error("Search was cancelled"));
              return;
            }

            const request = this.getDataDomain(domain, listTLD);
            this.activeRequests.push(request);

            request
              .then((result) => {
                clearTimeout(timeoutId);
                // Remove from active requests
                const index = this.activeRequests.indexOf(request);
                if (index > -1) {
                  this.activeRequests.splice(index, 1);
                }
                resolve(result);
              })
              .catch((error) => {
                clearTimeout(timeoutId);
                // Remove from active requests
                const index = this.activeRequests.indexOf(request);
                if (index > -1) {
                  this.activeRequests.splice(index, 1);
                }
                reject(error);
              });
          });
        },

        async getDataDomain(domain, listTLD) {
          // Check if search was cancelled before making request
          if (this.isSearchCancelled) {
            return [];
          }

          try {
            const response = await vnx_post_ajax_DataSugguest(
              "POST",
              "get_listDomian_whois_center",
              domain,
              listTLD.join(", ")
            );

            // Check if search was cancelled after request completed
            if (this.isSearchCancelled) {
              return [];
            }

            if (response.success) {
              // Check if response.data contains error
              if (response.data && response.data.error) {
                this.ResultError = true;
                return [];
              }

              let filteredDomains = response.data
                .filter(
                  (item) =>
                    item.isAvailable === true && item.isPremium === false
                )
                .map((item) => ({
                  ...item,
                  errorDomainVN: item.tld === "vn" && item?.sld?.length <= 2,
                  error: !item.legacyStatus,
                  isPremium: !item?.pricing?.register["1"]
                    ? true
                    : item.isPremium,
                }))
                // Domain không có giá đăng ký hợp lệ thì không hiển thị, vì
                // không có gì để hiện giá lẫn nút thêm giỏ hàng.
                .filter((item) => item.isPremium === false)
                .slice(0, 10);
              return filteredDomains;
            } else {
              return [];
            }
          } catch (error) {
            this.handleErrors(error);
            return [];
          }
        },

        stopSearch(reason) {
          this.IsLoading = false;
          this.IsLoadingMore = false;
          this.ResultError = true;
          this.ResultAgain = false; // Set to false to show error HTML
          this.isSearchCancelled = true;

          // Cancel all active requests
          this.cancelAllRequests();

          if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = null;
          }

          this.$eventBus.$emit("triggerLoadingAiBtn", {
            DomainLoading: false,
          });
        },

        cancelAllRequests() {
          this.activeRequests = [];
        },

        clickRemoveDomainAi(domain) {
          this.$eventBus.$emit("deleteDomainAICart", domain);
        },

        retrySearch() {
          window.location.reload();
        },
      },
    });
  });

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-domain-onpage")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        activeTab: 0,
        isDragging: false,
        startX: 0,
        scrollLeft: 0,
        Domain: "",
        DomainShowResult: false,
        DomainAvailable: false,
        DomainPremium: false,
        DomainVnError: false,
        DomainError: false,
        DomainName: "",
        DomainNameSld: "",
        DomainNameTld: "",
        DomainPrice: "",
        DomainPriceReduction: "",
        DomainPricePercent: "",
        DomainTooltipTitle: "",
        DomainTooltipContent: "",
        DomainNameTldOld: "",
        LoadingResult: false,
        ResultEmpty: true,
        ResultError: false,
        InCart: false,
        HasTld: false,
        // Domain propose
        IsPropose: false,
        LoadingPropose: false,
        proseInCart: false,
        // Domain combination
        Combination: false,
        DomainCombination: "",
        CombinationResult: [],
        // Domain list
        ListStatus: false,
        availableTldsInTab: [],
        //Loading button
        LoadingButton: false,
        TabEmpty: false,
        //Cache domain
        ArrayCache: [],
        ShowDomain: [],
        visibleDomainCount: 10,
        //Filter TLD
        filter_tld: [],
        // Combo hidden option
        domainComboHidden: false,
      },
      computed: {
        showDomainList() {
          return this.ShowDomain ? this.ShowDomain.slice() : [];
        },
        visibleDomains() {
          // Helper function để check price và originalPrice
          const isValidDomainPrice = (item) => {
            const hasValidPrice = item.price != null && item.price !== 0 && item.price !== "0";
            const hasValidOriginalPrice = item.originalPrice != null && item.originalPrice !== 0 && item.originalPrice !== "0";
            return hasValidPrice && hasValidOriginalPrice;
          };

          let domains = this.ListDomain.filter((item) =>
            item.tab.includes(this.activeTab) && isValidDomainPrice(item)
          );

          if (this.filter_tld && this.filter_tld.length > 0) {
            const normalizedFilter = this.filter_tld.map((tld) =>
              tld.replace(/^\./, "")
            );
            domains = domains.filter((item) => normalizedFilter.includes(item.tld));
          }

          return domains;
        },
      },
      watch: {
        ListDomain: {
          handler(newVal, oldVal) { },
          deep: true, // Nếu ListDomain là mảng object, nên dùng deep để bắt mọi thay đổi bên trong
          immediate: false, // Nếu muốn chạy ngay khi mounted, để true
        },

        // Watch CombinationResult để xóa combo trùng lặp khỏi ListComboDomain
        CombinationResult: {
          handler(newVal, oldVal) {
            // Không xử lý nếu option tắt combo
            if (this.domainComboHidden) {
              return;
            }
            if (newVal && newVal.availableDomains) {
              this.removeDuplicateCombosFromList();
            }
          },
          deep: true,
          immediate: false,
        },
      },
      mounted() {
        // Lấy giá trị domain_combo_hidden từ sessionStorage hoặc data attribute
        const sessionValue = sessionStorage.getItem('Domain_Combo_Hidden');
        if (sessionValue !== null) {
          this.domainComboHidden = sessionValue === 'true';
        } else {
          // Lấy từ data attribute của container
          let containerElement = this.$el.closest('.result-domain-container');

          if (!containerElement) {
            containerElement = document.querySelector('.result-domain-container');
          }

          if (!containerElement && this.$el.parentElement) {
            containerElement = this.$el.parentElement.closest('.result-domain-container');
          }

          if (containerElement) {
            const comboHiddenAttr = containerElement.getAttribute('data-domain-combo-hidden');
            this.domainComboHidden = comboHiddenAttr === 'true';
          } else {
            this.domainComboHidden = false;
          }
        }
        this.updateDomainList();
        this.TabEmpty = false;
        // Drag to scroll cho tab
        const tabContainer = this.$el.querySelector(".result-domain-tabs");
        if (tabContainer) {
          tabContainer.addEventListener("mousedown", this.onTabMouseDown);
          tabContainer.addEventListener("mousemove", this.onTabMouseMove);
          tabContainer.addEventListener("mouseup", this.onTabMouseUp);
          tabContainer.addEventListener("mouseleave", this.onTabMouseUp);
        }

        this.$eventBus.$on("triggerResult", (data) => {
          this.ArrayCache = [];
          this.ShowDomain = [];
          this.ListComboDomain = [];
          this.ListDomain = [];
          let id = "#" + this.$el.id;
          this.ResultEmpty = false;
          let fillter_tld = this.getListTLDFromCookieOnePage();
          // Set filter_tld để computed visibleDomains có thể filter
          this.filter_tld = fillter_tld;
          let resultDomainTab = $(this.$el).find(".result-domain-tab");
          if (id === data.box_result && data.domainInput !== "") {
            const processedDomainInput = data.domainInput
              .toLowerCase()
              .replace(/\s+/g, "");
            let tabactive = 0;
            if (processedDomainInput.includes(".") == false) {
              // không có tld
              this.HasTld = false;
              // Khi có filter thì active tab cuối cùng, không có filter thì active tab đầu tiên
              tabactive =
                fillter_tld.length > 0 ? resultDomainTab.length - 1 : 0;
              // Cập nhật activeTab để computed visibleDomains hoạt động đúng
              this.activeTab = tabactive;
              this.DomainName = processedDomainInput;
              this.clickGetListTLDTab(processedDomainInput, tabactive, null);
            } else {
              // có tld
              this.HasTld = true;
              // Khi có filter thì active tab cuối cùng, không có filter thì active tab đầu tiên
              tabactive =
                fillter_tld.length > 0 ? resultDomainTab.length - 1 : 0;
              // Cập nhật activeTab để computed visibleDomains hoạt động đúng
              this.activeTab = tabactive;
              let tld = processedDomainInput.split(".")[1];
              this.clickGetListTLDTab(processedDomainInput, tabactive, tld);
              this.clickSearchButton(processedDomainInput);
            }
            //active tab - chỉ set data-sld, không set active ở đây để tránh xung đột
            resultDomainTab.each((index, element) => {
              let $button = $(element);
              $button.attr("data-sld", processedDomainInput);
            });
          }
        });

        this.$eventBus.$on("deleteDomainCart", (domain) => {
          // Lấy cart data mới nhất từ cookie để đảm bảo đồng bộ
          const cartData = getCookiesDomainCart(this.DomainCartCookie);

          // Cập nhật trạng thái isInCart cho domain bị xóa trong ListDomain
          const domainIndex = this.ListDomain.findIndex(
            (item) => item.domainName === domain
          );
          if (domainIndex !== -1) {
            this.$set(this.ListDomain[domainIndex], "isInCart", false);
          }

          // Cập nhật trạng thái isInCart cho domain bị xóa trong ArrayCache
          const cacheIndex = this.ArrayCache.findIndex(
            (item) => item.domainName === domain
          );
          if (cacheIndex !== -1) {
            this.$set(this.ArrayCache[cacheIndex], "isInCart", false);
          }

          // Cập nhật trạng thái isInCart cho domain bị xóa trong ShowDomain
          const showIndex = this.ShowDomain.findIndex(
            (item) => item.domainName === domain
          );
          if (showIndex !== -1) {
            this.$set(this.ShowDomain[showIndex], "isInCart", false);
          }

          // Kiểm tra nếu domain bị xóa là domain chính
          if (this.DomainNameSld && this.DomainNameTld) {
            const mainDomain = `${this.DomainNameSld}.${this.DomainNameTld}`;
            if (domain === mainDomain) {
              this.DomainIntCart = false;
            }
          }

          // Lấy tất cả các button add_domain_cart
          let domainInputCarts = $(this.$el).find(".add_domain_cart");

          // Duyệt qua từng button để kiểm tra
          domainInputCarts.each((index, element) => {
            let $button = $(element);
            let sld = $button.attr("data-sld");
            let tld = $button.attr("data-tld");
            let combiTld = $button.attr("data-combi-tld");
            let buttonDomain = $button.attr("data-domain");

            // Kiểm tra combo domain
            if (sld && tld && combiTld) {
              // Tìm domain trong cart có comboId và match với domain hiện tại
              const comboDomain = cartData.find(
                (item) =>
                  item.isCombo &&
                  (item.domain === `${sld}.${tld}` ||
                    item.domain === `${sld}.${combiTld}`)
              );

              // Nếu domain bị xóa là một trong các domain của combo này
              if (
                domain === `${sld}.${tld}` ||
                domain === `${sld}.${combiTld}`
              ) {
                // Kiểm tra xem combo còn tồn tại trong cart không
                const comboStillExists = cartData.some(
                  (item) =>
                    item.isCombo &&
                    item.comboId &&
                    (item.domain === `${sld}.${tld}` ||
                      item.domain === `${sld}.${combiTld}`)
                );

                // Nếu combo không còn tồn tại, cập nhật button
                if (!comboStillExists) {
                  this.changerButtonCart();
                }
              }

              // Cập nhật trạng thái combo
              this.ComboIntCart = comboDomain ? true : false;
            } else {
              // Kiểm tra domain đơn lẻ
              if (domain === buttonDomain) {
                // Kiểm tra domain còn trong cart không
                const domainInCart = cartData.some(
                  (item) => item.domain === buttonDomain
                );

                if (!domainInCart) {
                  this.changerButtonCart();
                }
              }
            }
          });

          // Luôn gọi loadDomainSuggestinCart để cập nhật tất cả domain trong list
          this.loadDomainSuggestinCart();

          // 🔄 Cập nhật trạng thái combo sau khi xóa domain
          this.updateComboStatusAfterCartChange();
        });
      },
      methods: {
        checkDomainVnError(domaininput) {
          let tld = domaininput.split(".")[1];
          let sld = domaininput.split(".")[0];
          const vietnameseChars =
            /[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i;
          const hasVnTld = tld.includes("vn");
          if (vietnameseChars.test(sld) && hasVnTld) {
            return true;
          } else {
            return false;
          }
        },

        async clickSearchButton(domainInput) {
          this.initializeSearchState();

          if (!domainInput.includes(".")) {
            const tldSuggest = $(this.$el).find(".vnx-domain-result").data("domain-suggest");
            domainInput = domainInput + "." + tldSuggest;
            return;
          }

          try {
            const response = await this.handleApiCheckDomain(domainInput);
            await this.processDomainResponse(response.data, domainInput);
          } catch (error) {
            this.handleDomainError();
          } finally {
            this.DisableButton = false;
          }
        },

        initializeSearchState() {
          this.DisableButton = true;
          this.LoadingButton = true;
          this.LoadingResult = true;
          this.DomainShowResult = false;
          this.DomainVnError = false;
          this.Combination = false;
          this.IsPropose = false;
          this.LoadingPropose = true;
        },

        async processDomainResponse(response, domainInput) {

          this.DomainShowResult = true;
          this.DomainError = false;
          this.DomainAvailable = false;
          this.DomainNameTldOld = response.tld;

          if (response.result !== "success") {
            this.handleDomainError();
            return;
          }

          const { status, domainVn } = response;

          if (status === "available" && domainVn === "available") {
            await this.handleAvailableDomain(response, domainInput);
          } else if (domainVn === "unavailable") {
            await this.handleUnavailableDomain(response, domainInput);
          } else {
            await this.handleOtherDomainStatus(response, domainInput);
          }
        },

        async handleAvailableDomain(response, domainInput) {
          this.DomainAvailable = true;
          this.pushDataOneDomain(response, domainInput);

          if (this.checkDomainVnError(domainInput)) {
            this.handleDomainVnError();
            return;
          }

          await this.processCombinationDomains(response);
        },

        async handleUnavailableDomain(response, domainInput) {
          this.DomainVnError = true;
          this.DomainAvailable = false;
          this.DomainError = false;
          this.Domain = domainInput;

          const availableDomain = await this.checkDomainSuggest(domainInput);
          if (availableDomain) {
            await this.processSuggestedDomain(availableDomain, response.sld);
          }
        },

        async handleOtherDomainStatus(response, domainInput) {
          this.Domain = domainInput;

          if (this.checkDomainVnError(domainInput)) {
            this.handleDomainVnError();
            return;
          }

          const availableDomain = await this.checkDomainSuggest(domainInput);
          if (availableDomain) {
            await this.processSuggestedDomain(availableDomain, response.sld);
          } else {
            this.handleNoAvailableDomain();
          }
        },

        async processCombinationDomains(response) {
          // Không xử lý combo nếu option tắt combo
          if (this.domainComboHidden) {
            this.LoadingResult = false;
            this.LoadingPropose = false;
            return;
          }

          this.DomainCombination = getCombinationDomain(response.tld);
          this.LoadingResult = false;

          if (this.DomainCombination?.length > 0) {
            const combinationResult = await this.checkCombinationTLDs(
              response.sld,
              this.DomainCombination
            );
            if (combinationResult) {
              this.CombinationResult = combinationResult;
              this.Combination = true;
            }
          }

          this.LoadingPropose = false;
        },

        async processSuggestedDomain(availableDomain, sld) {
          this.pushDataOneDomain(availableDomain.response, availableDomain.domain);
          this.LoadingPropose = true;
          this.IsPropose = true;
          this.LoadingResult = false;

          // Không xử lý combo nếu option tắt combo
          if (!this.domainComboHidden) {
            this.DomainCombination = getCombinationDomain(availableDomain.tld);

            if (this.DomainCombination?.length > 0) {
              const combinationResult = await this.checkCombinationTLDs(
                sld,
                this.DomainCombination
              );
              if (combinationResult) {
                this.CombinationResult = combinationResult;
                this.Combination = true;
              }
            }
          }

          this.LoadingPropose = false;
        },

        handleDomainVnError() {
          this.LoadingResult = false;
          this.LoadingPropose = false;
          this.ListStatus = false;
          this.ListComboDomain = [];
          this.triggerActiveButton();
        },

        handleDomainError() {
          this.DomainError = true;
          this.DomainAvailable = false;
          this.LoadingResult = false;
          this.LoadingPropose = false;
          this.ListStatus = false;
          this.ListComboDomain = [];
        },

        handleNoAvailableDomain() {
          this.DomainVnError = false;
          this.DomainAvailable = false;
          this.LoadingResult = false;
          this.LoadingPropose = false;
          this.ListStatus = false;
          this.ListComboDomain = [];
          this.triggerActiveButton();
        },

        triggerActiveButton() {
          setTimeout(() => {
            this.$eventBus.$emit("triggerActiveBtn", {
              DomainLoading: false,
            });
          }, 100);
        },

        async checkDomainSuggest(domain) {
          let ListDomainSuggest = sessionStorage.getItem("Data_TLD_Sussgest");
          // console.log("ListDomainSuggest", ListDomainSuggest);
          let sld = domain.includes(".") ? domain.split(".")[0] : domain;
          if (!ListDomainSuggest) {
            return null;
          }
          try {
            ListDomainSuggest = JSON.parse(ListDomainSuggest);
            if (!Array.isArray(ListDomainSuggest)) {
              console.error(
                "ListDomainSuggest is not an array:",
                ListDomainSuggest
              );
              return null;
            }
            if (ListDomainSuggest.length === 0) {
              return null;
            }
            // Kiểm tra từng batch 5 TLD cho đến khi tìm được domain available
            for (
              let startIndex = 0;
              startIndex < ListDomainSuggest.length;
              startIndex += 5
            ) {
              let listDomainSuggest = ListDomainSuggest.slice(
                startIndex,
                startIndex + 5
              );
              // Kiểm tra từng TLD trong batch hiện tại
              for (let i = 0; i < listDomainSuggest.length; i++) {
                const tld = listDomainSuggest[i]; // ListDomainSuggest là array string, không phải object
                const domainToCheck = sld + "." + tld;
                try {
                  const response = await this.handleApiCheckDomain(
                    domainToCheck
                  );
                  const result = response.data;
                  if (
                    result.result === "success" &&
                    result.status === "available" &&
                    result.domainVn === "available"
                  ) {
                    return {
                      domain: domainToCheck,
                      sld: sld,
                      tld: tld,
                      response: result,
                    };
                  }
                  // Thêm delay 500ms giữa các request để tránh rate limiting
                  await new Promise((resolve) => setTimeout(resolve, 500));
                } catch (error) {
                  console.error("Error checking domain:", domainToCheck, error);
                  // Thêm delay ngay cả khi có lỗi
                  await new Promise((resolve) => setTimeout(resolve, 500));
                  continue;
                }
              }
              // Thêm delay 1000ms giữa các batch
              if (startIndex + 5 < ListDomainSuggest.length) {
                await new Promise((resolve) => setTimeout(resolve, 1000));
              }
            }
            return null;
          } catch (error) {
            console.error("Error parsing ListDomainSuggest:", error);
            return null;
          }
        },

        // push data domain
        pushDataOneDomain(response, domainInput) {
          this.DomainName = domainInput;
          this.DomainPrice = response.pricedomain;
          this.DomainNameSld = response.sld;
          this.DomainNameTld = response.tld;
          this.DomainPriceReduction = getPriceDomainData(response.tld);
          this.DomainPricePercent = calculateDiscountPercent(
            response.pricedomain,
            this.DomainPriceReduction
          );
          this.DomainTooltipTitle = getTooltipTitleDomainData(response.tld);
          this.DomainTooltipContent = getTooltipContentDomainData(response.tld);
          if (this.DomainPricePercent === 0) {
            this.DomainPriceReduction = "";
          }
          this.DomainPremium =
            response?.pricedomain == 0 ? true : !!response.premium;
          var obj = {
            domain: domainInput,
            register: "1",
            authen_code: "",
            sld: this.DomainNameSld,
            tld: this.DomainNameTld,
          };
          if (checkCookieDomain(this.DomainCartCookie, obj) != true) {
            this.DomainIntCart = false;
          } else {
            this.DomainIntCart = true;
          }
          setTimeout(() => {
            let domainInputCart = $(this.$el).find(".add_domain_cart");
            domainInputCart.attr("data-domain", domainInput);
            domainInputCart.attr("data-sld", this.DomainNameSld);
            domainInputCart.attr("data-tld", this.DomainNameTld);
          }, 100);
        },

        // External function
        handleApiCheckDomain(domain) {
          var data = {
            data: {
              domain: domain,
            },
          };
          return vnx_post_ajax_Data("POST", "check_domain_data_center", data);
        },

        getVisibleCount(arr, used) {
          if (used >= this.visibleDomainCount) return 0;
          return Math.min(arr.length, this.visibleDomainCount - used);
        },

        showMoreDomains() {
          this.visibleDomainCount += 10;
        },

        changerButtonCart() {
          // Kiểm tra trạng thái thực tế trong cart thay vì reset
          this.DomainIntCart = false;
          this.loadDomainSuggestinCart();
        },
        // Drag to scroll cho tab list domain
        selectTab(idx) {
          this.activeTab = idx;
          this.visibleDomainCount = 10;
          this.updateDomainList();
        },

        updateDomainList() {
          // Ẩn/hiện domain theo tab
          const items = this.$el.querySelectorAll(".result-domain-item");
          let visibleCount = 0;

          items.forEach((item) => {
            const dataTab = item.getAttribute("data-tab");
            if (typeof dataTab !== "string" || dataTab.trim() === "") {
              item.style.display = "none";
              return;
            }

            // Xử lý data-tab có thể là string hoặc array
            let tabList;
            try {
              // Thử parse nếu là JSON array
              tabList = JSON.parse(dataTab);
            } catch (e) {
              // Nếu không phải JSON, split theo dấu phẩy
              tabList = dataTab.split(",").map(Number);
            }

            // Đảm bảo tabList là array
            if (!Array.isArray(tabList)) {
              tabList = [tabList];
            }

            // Chỉ hiển thị domain nếu activeTab có trong tabList
            if (tabList.includes(this.activeTab)) {
              item.style.display = "";
              visibleCount++;
            } else {
              item.style.display = "none";
            }
          });
          // Set TabEmpty = true nếu không có item nào được hiển thị
          this.TabEmpty = visibleCount === 0;

          // Cập nhật class active cho tab
          const tabs = this.$el.querySelectorAll(".result-domain-tab");
          tabs.forEach((tab, idx) => {
            if (idx === this.activeTab) {
              tab.classList.add("active");
            } else {
              tab.classList.remove("active");
            }
          });
        },

        onTabMouseDown(e) {
          this.isDragging = true;
          this.startX = e.pageX - e.currentTarget.offsetLeft;
          this.scrollLeft = e.currentTarget.scrollLeft;
          e.currentTarget.classList.add("dragging");
        },

        onTabMouseMove(e) {
          if (!this.isDragging) return;
          e.preventDefault();
          const x = e.pageX - e.currentTarget.offsetLeft;
          const walk = (x - this.startX) * 1.2; // tốc độ kéo
          e.currentTarget.scrollLeft = this.scrollLeft - walk;
        },

        onTabMouseUp(e) {
          this.isDragging = false;
          if (e.currentTarget) e.currentTarget.classList.remove("dragging");
        },

        async clickGetListTLDTab(sld, tab, tldold) {
          this.currentTab = tab;
          this.activeTab = tab;
          this.ResultError = false;
          this.ShowDomain = [];
          this.TabEmpty = false;
          this.visibleDomainCount = 10;

          // Set tab active và disable tất cả tab
          const resultDomainTabList = $(".result-domain-tab");
          resultDomainTabList.removeClass("active").attr("disabled", true);
          resultDomainTabList.eq(tab).addClass("active");

          this.checkComboinTab(tab);
          this.updateComboDisplayCount();

          sld = sld.toLowerCase().replace(/\s+/g, "");
          const fillter_tld = this.getListTLDFromCookieOnePage();
          // Set filter_tld để computed visibleDomains có thể filter
          this.filter_tld = fillter_tld;
          let listComboTLD = [];
          try {
            const comboData = sessionStorage.getItem("Data_Combo_TLD");
            if (comboData) {
              const parsed = JSON.parse(comboData);
              // Check if it's an error object or not an array
              if (Array.isArray(parsed) && !parsed.status) {
                listComboTLD = parsed;
              }
            }
          } catch (error) {
            console.error("Error parsing Data_Combo_TLD in clickGetListTLDTab:", error);
          }
          let listTLD = this.clickGetListTLD(tab);
          const allTabTLD = [...listTLD];

          // Lấy SLD từ tab result
          const resultDomainTab = resultDomainTabList.eq(tab);
          let sldresult = resultDomainTab.data("sld");
          if (sld === "" && sldresult !== sld) {
            sld = sldresult;
          }
          sld = sld.split(".")[0];

          // Kiểm tra cache trước khi gọi API
          const cachedDomains = this.ArrayCache.filter(item => allTabTLD.includes(item.tld));
          if (cachedDomains.length >= allTabTLD.length) {
            this.ShowDomain = cachedDomains.map(item => Object.assign({}, item));
            this.IsLoading = false;
            this.TabEmpty = cachedDomains.length === 0;
            this.enableAllTabs(resultDomainTabList);
            resultDomainTabList.eq(tab).addClass("active");
            this.checkComboinTab(tab);
            this.updateComboDisplayCount();
            this.$eventBus.$emit("triggerActiveBtn", { DomainLoading: false });
            return;
          }

          // Nếu chưa đủ domain, mới loading và gọi API
          this.IsLoading = true;
          try {
            // Lọc TLD theo filter nếu có
            if (fillter_tld?.length > 0) {
              const normalizedFilterTld = fillter_tld.map(tld => tld.replace(/^\./, ""));
              listTLD = listTLD.filter(tld => normalizedFilterTld.includes(tld));

              if (listTLD.length === 0) {
                this.IsLoading = false;
                this.LoadingButton = false;
                this.ShowDomain = [];
                this.TabEmpty = true;
                this.enableAllTabs(resultDomainTabList);
                resultDomainTabList.eq(tab).addClass("active");
                this.$eventBus.$emit("triggerActiveBtn", { DomainLoading: false });
                return;
              }
            }

            // Cập nhật ShowDomain từ cache hiện có
            this.ShowDomain = this.ArrayCache
              .filter(item => allTabTLD.includes(item.tld))
              .map(item => Object.assign({}, item));

            // Loại các TLD đã gọi khỏi listTLD
            listTLD = listTLD.filter(tld => !this.ShowDomain.some(item => item.tld === tld));
            if (listTLD.length === 0) {
              this.IsLoading = false;
              this.LoadingButton = false;
              this.TabEmpty = this.ShowDomain.length === 0;
              this.enableAllTabs(resultDomainTabList);
              resultDomainTabList.eq(tab).addClass("active");
              this.$eventBus.$emit("triggerActiveBtn", { DomainLoading: false });
              return;
            }

            this.TabEmpty = false;

            // Gọi API nếu còn thiếu domain
            const response = await vnx_post_ajax_DataSugguest(
              "POST",
              "get_listDomian_Tab_center",
              sld,
              listTLD.join(", ")
            );

            if (response.success && Array.isArray(response.data)) {

              const processedResults = response.data.map(item => {
                // Bỏ qua item có error
                if (Array.isArray(item) && item.some(i => i.error)) {
                  return null;
                }

                const domainVn = item.tld === "vn" ? "available" : "unavailable";
                const price = formatPrice(item.pricing?.register?.["1"] || 0);
                const priceReduction = getPriceDomainData(item.tld);
                const pricePercent = calculateDiscountPercent(price, priceReduction);
                const tooltipTitle = getTooltipTitleDomainData(item.tld);
                const tooltipContent = getTooltipContentDomainData(item.tld);
                const premium = price === 0 ? true : !!item.isPremium;

                // Xác định domain thuộc các danh mục nào
                const listcateTLD = JSON.parse(localStorage.getItem("Data_Category_TLD"));
                let domainTabs = [tab];
                if (Array.isArray(listcateTLD)) {
                  domainTabs = listcateTLD
                    .map((arr, idx) => arr.includes(item.tld) ? idx : -1)
                    .filter(idx => idx !== -1);
                  if (domainTabs.length === 0) domainTabs = [tab];
                }
                domainTabs = [...new Set(domainTabs)];

                // Check domain có trong cookie cart không
                const domainInCart = checkCookieDomain(this.DomainCartCookie, {
                  domain: item.domainName,
                  sld: item.sld,
                  tld: item.tld,
                });

                return {
                  domainName: item.domainName,
                  sld: item.sld,
                  tld: item.tld,
                  status: item.isAvailable ? "available" : "unavailable",
                  price: price || null,
                  premium: premium || false,
                  originalPrice: pricePercent === 0 ? "" : priceReduction || null,
                  discount: pricePercent || null,
                  isInCart: domainInCart || false,
                  tab: domainTabs,
                  domainTabs: domainTabs,
                  domainVN: domainVn,
                  tooltipTitle: tooltipTitle,
                  tooltipContent: tooltipContent,
                };
              }).filter(result => result !== null);

              // Cập nhật ListDomain
              processedResults.forEach(result => {
                const existingIndex = this.ListDomain.findIndex(item => item.tld === result.tld);
                if (existingIndex === -1) {
                  this.ListDomain.push(result);
                } else {
                  this.ListDomain[existingIndex] = result;
                }
              });

              // Lưu vào ArrayCache
              this.ArrayCache = this.ArrayCache.concat(
                processedResults.filter(result =>
                  result.status !== "error" && 
                  !this.ArrayCache.some(item => item.domainName === result.domainName)
                )
              );

              this.ShowDomain = this.ArrayCache
                .filter(item => allTabTLD.includes(item.tld))
                .map(item => Object.assign({}, item));

              this.IsLoading = false;
              this.LoadingButton = false;
              this.emitSuccessEvent();
              this.updateComboDisplayCount();
              this.enableAllTabs(resultDomainTabList);
              resultDomainTabList.eq(tab).addClass("active");
              this.checkComboinTab(tab);
              this.ListStatus = true;

              setTimeout(() => {
                this.$eventBus.$emit("triggerActiveBtn", { DomainLoading: false });
              }, 100);

              this.TabEmpty = this.ShowDomain.length === 0;
              return this.ShowDomain;
            }

            this.IsLoading = false;
            this.enableAllTabs(resultDomainTabList);
            resultDomainTabList.eq(tab).addClass("active");
            this.$eventBus.$emit("triggerActiveBtn", { DomainLoading: false });

          } catch (error) {
            this.IsLoading = false;
            this.ResultError = true;
            this.ShowDomain = [];
            this.enableAllTabs(resultDomainTabList);
            resultDomainTabList.eq(tab).addClass("active");
            this.emitSuccessEvent();
            this.handleErrors(error);
            this.$eventBus.$emit("triggerActiveBtn", { DomainLoading: false });
            return [];
          }
        },

        enableAllTabs(resultDomainTabList) {
          resultDomainTabList.attr("disabled", false);
        },

        // Gọi lại đúng request vừa lỗi (không reload cả trang)
        retrySearch() {
          this.ResultError = false;
          this.clickGetListTLDTab(this.DomainName, this.activeTab, this.DomainNameTld);
        },

        // Kiểm tra xem combo có trùng với CombinationResult không
        isComboInCombinationResult(combo, sld) {
          if (
            !this.CombinationResult ||
            !this.CombinationResult.availableDomains
          ) {
            return false;
          }

          const comboDomains = this.CombinationResult.availableDomains;
          if (Array.isArray(comboDomains)) {
            return comboDomains.some(
              (domain) =>
                domain.sld === sld &&
                (domain.tld === combo.tlds ||
                  domain.tld === combo.Tlds ||
                  domain.tld === combo.tld ||
                  domain.tld === combo.TLD)
            );
          } else if (comboDomains.sld === sld) {
            return (
              comboDomains.tld === combo.tlds ||
              comboDomains.tld === combo.Tlds ||
              comboDomains.tld === combo.tld ||
              comboDomains.tld === combo.TLD
            );
          }

          return false;
        },

        // Xóa combo trùng lặp khỏi ListComboDomain khi có CombinationResult
        removeDuplicateCombosFromList() {
          if (
            !this.CombinationResult ||
            !this.CombinationResult.availableDomains
          ) {
            return;
          }

          this.ListComboDomain = this.ListComboDomain.filter((combo) => {
            if (!combo.isCombo) return true;

            const comboDomains = this.CombinationResult.availableDomains;
            if (Array.isArray(comboDomains)) {
              return !comboDomains.some(
                (domain) =>
                  domain.sld === combo.sld &&
                  (domain.tld === combo.tld || combo.tld.includes(domain.tld))
              );
            } else if (comboDomains.sld === combo.sld) {
              return (
                comboDomains.tld !== combo.tld &&
                !combo.tld.includes(comboDomains.tld)
              );
            }

            return true;
          });
        },

        checkComboinTab(tab) {
          // Không xử lý combo nếu option tắt combo
          if (this.domainComboHidden) {
            return;
          }

          // Bắt đầu loading combo
          let listComboTLD = [];
          try {
            const comboData = sessionStorage.getItem("Data_Combo_TLD");
            if (comboData) {
              const parsed = JSON.parse(comboData);
              // Check if it's an error object or not an array
              if (Array.isArray(parsed) && !parsed.status) {
                listComboTLD = parsed;
              }
            }
          } catch (error) {
            console.error("Error parsing Data_Combo_TLD:", error);
            return;
          }

          if (!listComboTLD || listComboTLD.length === 0) {
            return;
          }

          // Lấy danh sách TLD của tab hiện tại
          let listTLD = this.clickGetListTLD(tab);
          const tabTLDs = listTLD;
          if (!tabTLDs || tabTLDs.length === 0) {
            return;
          }

          // Lấy danh sách stt của combo đã có trong CombinationResult (nếu có)
          let excludeComboStt = [];
          if (this.CombinationResult && Array.isArray(this.CombinationResult)) {
            excludeComboStt = this.CombinationResult.map(
              (c) => c.combo?.stt
            ).filter(Boolean);
          } else if (
            this.CombinationResult &&
            this.CombinationResult.combo?.stt
          ) {
            excludeComboStt = [this.CombinationResult.combo.stt];
          }

          // Thêm logic để loại trừ combo từ CombinationResult dựa trên domain name
          let excludeComboDomains = [];
          if (
            this.CombinationResult &&
            this.CombinationResult.availableDomains
          ) {
            if (Array.isArray(this.CombinationResult.availableDomains)) {
              excludeComboDomains = this.CombinationResult.availableDomains.map(
                (domain) => domain.domain
              );
            } else if (this.CombinationResult.availableDomains.domain) {
              excludeComboDomains = [
                this.CombinationResult.availableDomains.domain,
              ];
            }
          }

          // Giữ lại các combo đã có sẵn, chỉ thêm combo mới
          const existingComboStts = this.ListComboDomain.map(
            (combo) => combo.stt
          );

          // Nhóm domain theo SLD từ ListDomain
          const domainsBySLD = {};
          this.ListDomain.forEach((domain) => {
            if (domain.status === "available" && tabTLDs.includes(domain.tld)) {
              if (!domainsBySLD[domain.sld]) {
                domainsBySLD[domain.sld] = [];
              }
              domainsBySLD[domain.sld].push(domain);
            }
          });

          // Kiểm tra từng SLD có đủ domain để tạo combo
          Object.keys(domainsBySLD).forEach((sld) => {
            const domains = domainsBySLD[sld];
            const tlds = domains.map((d) => d.tld);

            for (const combo of listComboTLD) {
              const tldsProperty =
                combo.tlds || combo.Tlds || combo.tld || combo.TLD;
              if (!tldsProperty) continue;

              // Loại trừ combo đã có trong CombinationResult
              if (excludeComboStt.includes(combo.stt)) continue;

              // Loại trừ combo đã có trong ListComboDomain
              if (existingComboStts.includes(combo.stt)) continue;

              // Loại trừ combo có domain trùng với CombinationResult
              if (this.isComboInCombinationResult(combo, sld)) {
                continue;
              }

              const comboTlds = tldsProperty
                .split("+")
                .map((tld) => tld.trim());

              // Chỉ show combo khi tab đủ tất cả các tld để tạo thành combo
              const allTldsInTab = comboTlds.every((tld) => tlds.includes(tld));
              if (!allTldsInTab) continue;

              // Lấy thông tin domain cho các TLD trong combo
              const availableDomains = domains.filter((domain) =>
                comboTlds.includes(domain.tld)
              );

              // Lấy intersection các tab của từng domain trong combo
              const tabsArr = comboTlds.map((tld) => {
                const domain = availableDomains.find((d) => d.tld === tld);
                return domain ? domain.tab : [];
              });
              if (tabsArr.length === 0) continue;
              const comboTabs = tabsArr.reduce(
                (a, b) => a.filter((tab) => b.includes(tab)),
                tabsArr[0]
              );
              if (!comboTabs || comboTabs.length === 0) continue;

              // Tính toán giá combo
              let comboTotalPrice = 0;
              let comboOriginalPrice = 0;

              // Tính giá gốc từ Default Pricing
              const defaultPricingProperty =
                combo.defaultpricing || combo["Default Pricing"];
              if (defaultPricingProperty) {
                const defaultPrices = defaultPricingProperty
                  .split("+")
                  .map(
                    (price) => parseInt(price.trim().replace(/\./g, "")) || 0
                  );
                comboOriginalPrice = defaultPrices.reduce(
                  (sum, price) => sum + price,
                  0
                );
              }

              // Tính giá combo từ Combo Pricing
              const comboPricingProperty =
                combo.combopricing || combo["Combo Pricing"];
              if (comboPricingProperty) {
                const comboPrices = comboPricingProperty
                  .split("+")
                  .map(
                    (price) => parseInt(price.trim().replace(/\./g, "")) || 0
                  );
                comboTotalPrice = comboPrices.reduce(
                  (sum, price) => sum + price,
                  0
                );
              } else {
                // Nếu không có Combo Pricing, sử dụng giá từ availableDomains
                comboTotalPrice = availableDomains.reduce((sum, domain) => {
                  const price = parseInt(domain.price?.replace(/\./g, "")) || 0;
                  return sum + price;
                }, 0);
              }

              // Tính phần trăm giảm giá
              let discountPercent = 0;
              if (comboOriginalPrice > 0 && comboTotalPrice > 0) {
                discountPercent = Math.round(
                  ((comboOriginalPrice - comboTotalPrice) /
                    comboOriginalPrice) *
                  100
                );
              }

              // Lấy title và content combo
              const titlecombo =
                combo.titlecombo ||
                combo["Title Combo"] ||
                `Combo ${combo.stt}`;
              const contentcombo =
                combo.contentcombo ||
                combo["Content Combo"] ||
                `Gói combo ${comboTlds.join(", ")} (${comboTlds.length} TLDs)`;

              // Tạo combo result object
              const comboResult = {
                domainName: `${sld}.${comboTlds.join("+")}`,
                sld: sld,
                tld: comboTlds.join("+"),
                status: "available",
                price: comboTotalPrice,
                premium: false,
                originalPrice: comboOriginalPrice,
                discount: discountPercent,
                isInCart: false,
                tab: comboTabs,
                domainVN: "available",
                tooltipTitle: titlecombo,
                tooltipContent: contentcombo,
                isCombo: true,
                combo: combo,
                availableDomains: availableDomains,
                comboId: `combo_${combo.stt}_${Date.now()}`,
                totalTldsInCombo: comboTlds.length,
                availableTldsInTab: comboTlds.length,
                titlecombo: titlecombo,
                contentcombo: contentcombo,
              };

              // Thêm combo vào ListComboDomain nếu chưa có, nếu đã có thì cập nhật
              const existingComboIndex = this.ListComboDomain.findIndex(
                (item) =>
                  item.isCombo &&
                  item.sld === sld &&
                  item.combo?.stt === combo.stt
              );

              if (existingComboIndex === -1) {
                this.ListComboDomain.push(comboResult);
              } else {
                this.ListComboDomain[existingComboIndex] = comboResult;
              }
            }
          });

          this.updateDomainList();

          // Cập nhật trạng thái disable cho tất cả combo sau khi load tab
          this.updateComboDisabledStatus();

          // Cập nhật số lượng combo hiển thị
          this.updateComboDisplayCount();

          // Xóa combo trùng lặp với CombinationResult
          this.removeDuplicateCombosFromList();

          // Kết thúc loading combo
          this.IsLoadingCombo = false;
        },

        async checkCombinationTLDs(sld, combinationTLDs) {
          // Không xử lý combo nếu option tắt combo
          if (this.domainComboHidden) {
            return null;
          }

          // Convert string to array if needed (trường hợp cũ)
          if (typeof combinationTLDs === "string") {
            let tldArray = combinationTLDs.split(",").map((tld) => tld.trim());
            for (let i = 0; i < tldArray.length; i++) {
              const tld = tldArray[i];
              const domainToCheck = sld + "." + tld;
              try {
                const response = await this.handleApiCheckDomain(domainToCheck);
                const result = response.data;
                if (
                  result.result === "success" &&
                  result.status === "available"
                ) {
                  // Trả về object chuẩn
                  return {
                    availableDomains: [
                      {
                        domain: domainToCheck,
                        sld: sld,
                        tld: tld,
                      },
                    ],
                    sld: sld,
                    tlds: tld,
                    isCombo: true,
                  };
                }
              } catch (error) {
                continue;
              }
            }
            return null;
          }
          // Trường hợp mới: combinationTLDs là array của combo objects
          if (
            Array.isArray(combinationTLDs) &&
            combinationTLDs.length > 0 &&
            typeof combinationTLDs[0] === "object"
          ) {
            for (
              let comboIndex = 0;
              comboIndex < combinationTLDs.length;
              comboIndex++
            ) {
              const combo = combinationTLDs[comboIndex];
              if (!combo.tlds) continue;
              const tldsInCombo = combo.tlds
                .split("+")
                .map((tld) => tld.trim());
              let allTldsAvailable = true;
              let availableDomains = [];
              for (
                let tldIndex = 0;
                tldIndex < tldsInCombo.length;
                tldIndex++
              ) {
                const tld = tldsInCombo[tldIndex];
                const domainToCheck = sld + "." + tld;
                try {
                  const response = await this.handleApiCheckDomain(
                    domainToCheck
                  );
                  const result = response.data;
                  let inACart = false;
                  if (
                    result.result === "success" &&
                    result.status === "available"
                  ) {
                    var obj = {
                      domain: domainToCheck,
                      register: "1",
                      authen_code: "0",
                      sld: sld,
                      tld: tld,
                    };
                    if (checkCookieDomain(this.DomainCartCookie, obj) != true) {
                      inACart = false;
                    } else {
                      inACart = true;
                    }
                    availableDomains.push({
                      domain: domainToCheck,
                      register: "1",
                      authen_code: "0",
                      sld: sld,
                      tld: tld,
                      inACart: inACart,
                    });
                  } else {
                    allTldsAvailable = false;
                    break;
                  }
                  await new Promise((resolve) => setTimeout(resolve, 500));
                } catch (error) {
                  allTldsAvailable = false;
                  await new Promise((resolve) => setTimeout(resolve, 500));
                  break;
                }
              }
              if (allTldsAvailable && availableDomains.length > 0) {
                const {
                  stt,
                  Tlds,
                  tlds,
                  combopricing,
                  defaultpricing,
                  titlecombo,
                  contentcombo,
                } = combo;
                // console.log("combo", combo);
                // giá trị "combopricing": "249.000 + 100.000" => 249000 + 100000 = 349000
                let comboprice = combopricing
                  .split("+")
                  .map(
                    (price) => parseInt(price.trim().replace(/\./g, "")) || 0
                  );
                comboprice = comboprice.reduce((sum, price) => sum + price, 0);
                // Chỉ giữ lại các trường số/id cần thiết cho combo
                let defaultprice = defaultpricing
                  .split("+")
                  .map(
                    (price) => parseInt(price.trim().replace(/\./g, "")) || 0
                  );
                defaultprice = defaultprice.reduce(
                  (sum, price) => sum + price,
                  0
                );
                let discountPercent = Math.round(
                  ((defaultprice - comboprice) / defaultprice) * 100
                );
                //{ "availableDomains": [ { "domain": "linhduytru.vn", "register": "1", "authen_code": "0", "sld": "linhduytru", "tld": "vn", "inACart": false }, { "domain": "linhduytru.info", "register": "1", "authen_code": "0", "sld": "linhduytru", "tld": "info", "inACart": false } ], "sld": "linhduytru", "tlds": "vn + info", "isCombo": true, "stt": "combo 1", "discount": 70, "combopricing": 349000, "defaultpricing": 1170000, "titlecombo": "Áp dụng khi mua 1 năm đầu", "contentcombo": "Combo được vn + info" }
                // kiểm tra tất cả domain trong availableDomains có inACart là true hết thì set proseInCart là true, nếu có 1 domain inACart là false thì set proseInCart là false
                let proseInCartData = availableDomains.every(
                  (domain) => domain.inACart
                );
                this.proseInCart = proseInCartData;
                return {
                  availableDomains: availableDomains,
                  sld: sld,
                  tlds: combo.tlds || combo.Tlds || combo.tld || combo.TLD,
                  isCombo: true,
                  stt: stt,
                  discount: discountPercent,
                  combopricing: comboprice,
                  defaultpricing: defaultprice,
                  titlecombo: titlecombo,
                  contentcombo: contentcombo,
                  proseInCart: proseInCartData,
                };
              }
            }
            return null;
          }
          return null;
        },
      },
    });
  });

document
  .querySelectorAll(".brxe-vnx-domain-result-v2.result-muti-domain-onpage")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixinResult],
      data: {
        activeTab: 0,
        isDragging: false,
        startX: 0,
        scrollLeft: 0,
        ResultEmpty: true,
        ElShowTotalResult: window.elShowTotalResult,
        TotalDomainSearch: 0,
        TotalDomainSuccess: 0,
        ListDomain: [],
        ArrayCache: [],
        ShowDomain: [],
        visibleDomainCount: 10,
        ListStatus: false,
        TabEmpty: false,
        IsLoading: false,
        IsLoadingMore: false,
        filteredDomains: [],
        visibleCountAll: 0,
        visibleCountTab: 0,
        TotalTldSearch: [],
      },
      computed: {
        visibleDomains() {
          return this.ListDomain.filter((domain) => {
            let tabList = [];
            if (typeof domain.tab === "string") {
              tabList = domain.tab.split(",").map(Number);
            } else if (Array.isArray(domain.tab)) {
              tabList = domain.tab;
            } else if (typeof domain.tab === "number") {
              tabList = [domain.tab];
            }
            return tabList.includes(this.activeTab);
          }).slice(0, this.visibleDomainCount); // chỉ lấy số lượng theo visibleDomainCount
        },

        totalDomainsInTab() {
          return this.ListDomain.filter((domain) => {
            let tabList = [];
            if (typeof domain.tab === "string") {
              tabList = domain.tab.split(",").map(Number);
            } else if (Array.isArray(domain.tab)) {
              tabList = domain.tab;
            } else if (typeof domain.tab === "number") {
              tabList = [domain.tab];
            }
            return tabList.includes(this.activeTab);
          }).length;
        },

        domainsWithPricingCount() {
          this.TotalTldSearch = this.ListDomain;
          return this.TotalTldSearch.filter((domain) => domain.price != null).length;
        }
      },
      created() { },
      mounted() {
        // Drag to scroll cho tab
        const tabContainer = this.$el.querySelector(".result-domain-tabs");
        if (tabContainer) {
          tabContainer.addEventListener("mousedown", this.onTabMouseDown);
          tabContainer.addEventListener("mousemove", this.onTabMouseMove);
          tabContainer.addEventListener("mouseup", this.onTabMouseUp);
          tabContainer.addEventListener("mouseleave", this.onTabMouseUp);
        }

        this.$eventBus.$on("triggerResult", (data) => {
          let id = "#" + this.$el.id;
          if (data.box_result.includes(id) && data.domainArray.length > 0) {
            this.ListDomain = [];
            let resultDomainTab = $(".result-domain-tabs .result-domain-tab");
            // Set active tab trước khi gọi getMutiDomain
            if (data.tlds.length > 0 && data.tab !== undefined) {
              this.activeTab = data.tab;
              resultDomainTab.each(function () {
                $(this).removeClass("active");
                let tab = $(this).data("tab");
                if (tab == data.tab) {
                  $(this).addClass("active");
                }
              });
            }
            this.getMutiDomain(data.domainArray);
          }
        });

        this.$eventBus.$on("deleteDomainCart", (domain) => {
          this.checkDomainCart(domain);
        });
      },
      methods: {
        // Function để gọi từ HTML onclick
        handleTabClick(idx) {
          this.selectTab(idx);
        },
        // Drag to scroll cho tab list domain
        selectTab(idx) {
          this.activeTab = idx;
          $(this.$el).find(".result-domain-tabs").find("button").removeClass("active");
          $(this.$el).find(".result-domain-tabs").find("button").eq(idx).addClass("active");
          this.visibleDomainCount = 10; // reset về 10 khi đổi tab
          this.updateDomainList();
        },
        //ẩn hiện các item domain theo tab
        updateDomainList() {
          const items = this.$el.querySelectorAll(".result-domain-item");
          let count = 0;
          items.forEach((item) => {
            let dataTab = item.getAttribute("data-tab");
            if (!dataTab) {
              item.classList.add("domain-off");
              return;
            }
            let tabList = dataTab.split(",").map(Number);
            if (tabList.includes(this.activeTab)) {
              if (count < this.visibleDomainCount) {
                item.classList.remove("domain-off");
              } else {
                item.classList.add("domain-off");
              }
              count++;
            } else {
              item.classList.add("domain-off");
            }
          });
          this.getVisibleDomainCount();
        },

        // tôi cần 1 function đếm số lượng domain hiển thị trong tab khi tôi click vào tab
        getVisibleDomainCount() {
          const visibleItems = this.$el.querySelectorAll(
            ".result-domain-item:not(.domain-off)"
          );
          this.visibleCountAll = visibleItems.length;
        },
        //drag to scroll cho tab
        onTabMouseDown(e) {
          this.isDragging = true;
          this.startX = e.pageX - e.currentTarget.offsetLeft;
          this.scrollLeft = e.currentTarget.scrollLeft;
          e.currentTarget.classList.add("dragging");
        },

        onTabMouseMove(e) {
          if (!this.isDragging) return;
          e.preventDefault();
          const x = e.pageX - e.currentTarget.offsetLeft;
          const walk = (x - this.startX) * 1.2; // tốc độ kéo
          e.currentTarget.scrollLeft = this.scrollLeft - walk;
        },

        onTabMouseUp(e) {
          this.isDragging = false;
          if (e.currentTarget) e.currentTarget.classList.remove("dragging");
        },
        //lấy dữ liệu domain multi
        async getMutiDomain(arrayDomain) {
          const classDomains = classifyDomains(arrayDomain);
          // Reset data
          this.TotalDomainSearch = classDomains.length;
          this.TotalDomainSuccess = 0;
          this.ResultEmpty = false;
          this.ResultError = false;
          this.IsLoading = true;
          this.ListDomain = [];
          this.ListStatus = true;
          this.runProgressBar();

          // Process domains sequentially to avoid race conditions
          for (const item of classDomains) {
            item.name = item.name.toLowerCase();
            try {
              const response = await this.getDataDomain(item.name, item.tld);

              if (response.length > 0) {
                this.ResultEmpty = false;
                this.TotalDomainSuccess += 1;

                // Filter out domains with errors
                const validDomains = response.filter((domain) => !domain.error);
                this.ListDomain = this.ListDomain.concat(validDomains);
              }
            } catch (error) {
              console.error(`Error processing domain ${item.name}.${item.tld}:`, error);
              this.ResultError = true;
            }
          }

          // Complete processing
          this.IsLoading = false;

          // Normalize tab data to ensure it's always a string
          this.ListDomain = this.ListDomain.map(d => {
            if (Array.isArray(d.tab)) {
              d.tab = d.tab.join(",");
            } else if (typeof d.tab === 'number') {
              d.tab = d.tab.toString();
            } else if (typeof d.tab !== 'string') {
              d.tab = '';
            }
            return d;
          });

          this.completeProgressBar();
          this.loadDomainSuggestinCart();
          this.updateTotalResult();
          this.emitSuccessEvent();

          // Ensure active tab is maintained after processing
          this.$nextTick(() => {
            if (this.activeTab !== undefined) {
              $(this.$el).find(".result-domain-tabs").find("button").removeClass("active");
              $(this.$el).find(".result-domain-tabs").find("button").eq(this.activeTab).addClass("active");
              this.updateDomainList();
            }
          });

          this.$eventBus.$emit("triggerLoadingStopBtn", {
            isLoading: false,
          });
        },
        //lấy dữ liệu domain từ API
        async getDataDomain(domain, listTLD) {
          try {
            const response = await vnx_post_ajax_DataSugguest(
              "POST",
              "get_listDomian_whois_center",
              domain,
              listTLD.join(", ")
            );
            if (response.success) {
              const listcateTLD = JSON.parse(localStorage.getItem("List_Category_TLD"));
              let filteredDomains = response.data.map((item) => {
                // Check if item has required properties for domain processing
                if (!item.sld || !item.tld) {
                  // Handle error cases where domain data is incomplete
                  return {
                    domainName: item.domainName || "Unknown Domain",
                    error: item.error || "Invalid domain data",
                    isAvailable: false,
                    status: false,
                    sld: null,
                    tld: null,
                    price: null,
                    premium: false,
                    originalPrice: null,
                    discount: null,
                    isInCart: false,
                    tab: [],
                    domainTabs: [],
                    domainVN: false,
                    tooltipTitle: "",
                    tooltipContent: "",
                    result: null
                  };
                }

                const domainToCheck = `${item.sld}.${item.tld}`;
                const priceReduction = getPriceDomainData(item.tld);
                const pricePercent = calculatePriceDomain(
                  formatPrice(item.pricing?.register?.["1"]),
                  priceReduction
                );
                const premium = !item?.pricing?.register?.["1"]
                  ? true
                  : item.isPremium;
                let domainTabs = [];
                if (Array.isArray(listcateTLD)) {
                  listcateTLD.forEach((arr, idx) => {
                    if (Array.isArray(arr)) {
                      const arrNoDot = arr.map((tld) => tld.replace(/^\./, ""));
                      if (arrNoDot.includes(item.tld.replace(/^\./, ""))) {
                        domainTabs.push(idx);
                      }
                    }
                  });
                }
                const tooltipTitle = getTooltipTitleDomainData(item.tld);
                const tooltipContent = getTooltipContentDomainData(item.tld);

                return {
                  result: item.result,
                  domainName: domainToCheck,
                  sld: item.sld,
                  tld: item.tld,
                  status: item.isAvailable,
                  price: item.pricing?.register?.["1"] || null,
                  premium: premium,
                  originalPrice: priceReduction || null,
                  discount: pricePercent || null,
                  isInCart: false,
                  tab: domainTabs,
                  domainTabs: domainTabs,
                  domainVN: item.tld === "vn" && item?.sld?.length <= 2,
                  tooltipTitle: tooltipTitle,
                  tooltipContent: tooltipContent,
                  error: !item.legacyStatus,
                };
              });
              return filteredDomains;
            } else {
              return [];
            }
          } catch (error) {
            this.handleErrors(error);
            return [];
          }
        },

        // External function : gọi API kiềm tra domain
        handleApiCheckDomain(domain) {
          var data = {
            data: {
              domain: domain,
            },
          };
          return vnx_post_ajax_Data("POST", "check_domain_data_center", data);
        },
        //cập nhật số tổng tên miền
        updateTotalResult() {
          let totalResult = this.ListDomain.length;
          let elTotalResult = this.$el.querySelector(this.ElShowTotalResult);
          if (elTotalResult) {
            elTotalResult.innerHTML = totalResult;
          }
        },
        //tính số lượng domain đc hiển thị
        getVisibleCount(arr, used) {
          if (used >= this.visibleDomainCount) return 0;
          return Math.min(arr.length, this.visibleDomainCount - used);
        },
        //click để xem thếm 10 domain nữa
        showMoreDomains() {
          this.visibleDomainCount += 10;
          this.updateDomainList();
        },
        // cập nhật hiển thị domain empty
        updateEmptyItemVisibility() {
          const items = this.$el.querySelectorAll(
            ".result-domain-item:not(.empty)"
          );
          const emptyItem = this.$el.querySelector(".result-domain-item.empty");
          // Kiểm tra tất cả item thường đều bị ẩn
          const allHidden = Array.from(items).every(
            (item) => item.style.display === "none"
          );
          if (emptyItem) {
            emptyItem.style.display = allHidden ? "" : "none";
          }
        },

      },
    });
  });

Vue.prototype.clickOnLoadCartSussgest = function () {
  let newData = JSON.parse(
    JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
  );
  this.$eventBus.$emit("clickAddToCart", newData);
};
