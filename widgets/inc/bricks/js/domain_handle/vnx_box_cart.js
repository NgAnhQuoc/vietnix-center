window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + "/wp-admin/admin-ajax.php";
document.querySelectorAll(".brxe-vnx-domain-cart-v2.default").forEach((el) => {
  new Vue({
    el: `#${el.id}`,
    data: {
      DomainCartCookie: "vnx_domain_carts",
      DomainCookie: ".vietnix.vn",
      DomainCartFooter: false,
      Emptycart: true,
      DomainDatatCart: "",
      PriceofDomain: [],
      Data_Price_TLD: [],
      ShowCartMobile: false,
    },
    mounted() {
      // this.$eventBus.$on("triggerLoadcart", () => {
      //   this.onLoadCart();
      // });
      this.onLoadCart();
    },
    computed: {
      // tính tổng giá lấy từ cookie
      totalPrice() {
        this.DomainDatatCart = JSON.parse(
          JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
        );
        return this.DomainDatatCart.reduce((total, item) => {
          let tld = item.tld;
          let year = item.register || 1; // Giá trị mặc định là 1 năm
          let priceData = this.getPriceByTLD(tld);
          let price = 0;

          // Kiểm tra priceData không null và có thuộc tính year
          if (priceData && priceData[year]) {
            price = parseInt(priceData[year]);
          }

          return total + price;
        }, 0);
      },
      // đếm tổng số domain từ cookie
      totalDomains() {
        return this.DomainDatatCart ? this.DomainDatatCart.length : 0;
      },
    },
    created() {
      // Lắng nghe sự kiện từ eventBus thêm doamin vào giỏ hàng
      this.$eventBus.$on("clickAddToCart", (newData) => {
        this.DomainDatatCart = newData;
        this.onLoadCart();
        setTimeout(() => {
          var domains = $("#cart_list").find(
            ".vnx_trash_icon.remove_domain_cart"
          );
          if (domains.length != 0) {
            this.Emptycart = false;
            this.DomainCartFooter = true;
          } else {
            this.Emptycart = true;
            this.DomainCartFooter = false;
          }
        }, 100);
      });
    },
    methods: {
      // Internal function
      onLoadCart() {
        this.DomainDatatCart = JSON.parse(
          JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
        );
        let sessionData = localStorage.getItem("Data_Price_TLD");
        if (sessionData) {
          this.Data_Price_TLD = JSON.parse(sessionData);
        }
        setTimeout(() => {
          var domains = $("#cart_list").find(
            ".vnx_trash_icon.remove_domain_cart"
          );
          if (domains.length != 0) {
            this.Emptycart = false;
            this.DomainCartFooter = true;
          } else {
            this.Emptycart = true;
            this.DomainCartFooter = false;
          }
        }, 100);
      },

      getPriceByTLD(tld) {
        let found = this.Data_Price_TLD.find(([key]) => key === tld);
        return found ? found[1] : null;
      },

      deleteDomainCart(domain) {
        var domains = $("#cart_list").find(
          ".vnx_trash_icon.remove_domain_cart"
        );
        if (domains.length != 1) {
          this.Emptycart = false;
          this.DomainCartFooter = true;
        } else {
          this.Emptycart = true;
          this.DomainCartFooter = false;
        }
        removeDomaininCookie(this.DomainCartCookie, domain);
        this.$eventBus.$emit("deleteDomainCart", domain);
        showNoticeMessage("success", "Đã xoá tên miền khỏi giỏ hàng");
        this.onLoadCart();

        // Kiểm tra lại combo cho các domain còn lại
        if (this.DomainDatatCart.length >= 2) {
          this.checkAndUpdateComboPricing();
        } else {
          // Nếu chỉ còn 1 domain hoặc không còn domain nào, reset combo
          this.resetComboForRemainingDomains();
        }

        // Kiểm tra trạng thái empty cart
        setTimeout(() => {
          var domains = $("#cart_list").find(
            ".vnx_trash_icon.remove_domain_cart"
          );
          if (domains.length != 0) {
            this.Emptycart = false;
            this.DomainCartFooter = true;
          } else {
            this.Emptycart = true;
            this.DomainCartFooter = false;
          }
        }, 100);
      },

      updateDomaininCookie(key, domain, register, expires = 30) {
        let data = getCookiesDomainCart(key); // Lấy dữ liệu hiện tại từ cookie
        let index = data.findIndex((item) => item.domain === domain);
        if (index !== -1) {
          data[index] = {
            ...data[index], // Giữ nguyên các thuộc tính khác
            register: register.toString(), // Chắc chắn cập nhật selectedYear
          };
          setCookieDomainCart(key, data, expires); // Cập nhật vào cookie (will normalize automatically)
        } else {
          console.error(`⛔ Domain ${domain} không tồn tại trong cookie`);
        }
      },

      updateSelectedYear(event, domain) {
        let register = parseInt(event.target.value) || 1;
        let index = this.DomainDatatCart.findIndex(
          (item) => item.domain === domain
        );
        if (index !== -1) {
          this.$set(this.DomainDatatCart, index, {
            ...this.DomainDatatCart[index],
            register: register,
          });
          // Cập nhật vào cookie sau khi thay đổi
          this.updateDomaininCookie(this.DomainCartCookie, domain, register);
          this.onLoadCart();
        }
      },

      clickShowCartMobi() {
        document.body.style.overflow = "hidden";
        this.ShowCartMobile = true;
      },
      clickHiddenCartMobi() {
        document.body.style.overflow = "";
        this.ShowCartMobile = false;
      },
      // External function
    },
  });
});

document.querySelectorAll(".brxe-vnx-domain-cart-v2.cart-v1").forEach((el) => {
  new Vue({
    el: `#${el.id}`,
    data: {
      DomainCartCookie: "vnx_domain_carts",
      DomainCookie: ".vietnix.vn",
      DomainCartFooter: false,
      Emptycart: true,
      DomainDatatCart: [],
      PriceofDomain: [],
      Data_Price_TLD: [],
      Data_Price_TLD_Renew: {},
      ShowCartMobile: false,
      ShowCartDesktop: false,
      currentMonth: new Date().getMonth() + 1,
      currentYear: new Date().getFullYear(),
    },
    mounted() {
      this.onLoadCart();
      this.getPriceRenewByTLD();
    },
    computed: {
      // tính tổng giá lấy từ cookie
      totalPrice() {
        if (!this.DomainDatatCart || !Array.isArray(this.DomainDatatCart)) {
          return 0;
        }
        return this.DomainDatatCart.reduce((total, item) => {
          let price = item.new_price ? parseInt(item.new_price) : 0;
          return total + price;
        }, 0);
      },
      // đếm tổng số domain từ cookie
      totalDomains() {
        return this.DomainDatatCart ? this.DomainDatatCart.length : 0;
      },

      currentMonth() {
        return new Date().getMonth() + 1;
      },
      currentYear() {
        return new Date().getFullYear();
      },
    },
    created() {
      // Lắng nghe sự kiện từ eventBus thêm doamin vào giỏ hàng
      this.$eventBus.$on("clickAddToCart", (newData) => {
        this.DomainDatatCart = newData;
        this.onLoadCart();
        setTimeout(() => {
          var domains = $(this.$el)
            .find(".vnx-cart-modal-body")
            .find(".vnx-cart-item-remove");
          if (domains.length != 0) {
            this.Emptycart = false;
            this.DomainCartFooter = true;
          } else {
            this.Emptycart = true;
            this.DomainCartFooter = false;
          }
        }, 100);
      });

      this.$eventBus.$on("deleteDomainAICart", (domain) => {
        this.deleteDomainCart(domain);
      });
      this.$eventBus.$on("deleteDomainCartAvailable", (domain) => {
        this.deleteDomainCart(domain);
      });
      this.$eventBus.$on("deleteDomainCartPropose", (domain) => {
        this.deleteDomainCart(domain);
      });
    },
    methods: {
      // Internal function
      onLoadCart() {
        this.DomainDatatCart = JSON.parse(
          JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
        );
        let sessionData = localStorage.getItem("Data_Price_TLD");
        if (sessionData) {
          this.Data_Price_TLD = JSON.parse(sessionData);
        }

        // 🔄 Tái cấu trúc xử lý combo mỗi khi giỏ hàng thay đổi
        this.reprocessComboAfterChange();

        setTimeout(() => {
          var domains = $(this.$el)
            .find(".vnx-cart-modal-body")
            .find(".vnx-cart-item-remove");
          if (domains.length != 0) {
            this.Emptycart = false;
            this.DomainCartFooter = true;
          } else {
            this.Emptycart = true;
            this.DomainCartFooter = false;
            this.clickHiddenCart();
          }
        }, 100);
      },

      getPriceRenewByTLD(tld) {
        let existingData = localStorage.getItem("Data_Price_TLD_Renew");
        if (!existingData) {
          vnx_post_ajax_Data("POST", "get_listpriceRenewDomain_center").then(
            (response) => {
              var data_list = response.data;
              // Extract only renew pricing data from each TLD
              this.Data_Price_TLD_Renew = {};
              if (data_list.pricing) {
                Object.keys(data_list.pricing).forEach((tld) => {
                  if (data_list.pricing[tld].renew) {
                    this.Data_Price_TLD_Renew[tld] =
                      data_list.pricing[tld].renew;
                  }
                });
              }
              localStorage.setItem(
                "Data_Price_TLD_Renew",
                JSON.stringify(this.Data_Price_TLD_Renew)
              );
            }
          );
        } else {
          // Load from localStorage if exists
          this.Data_Price_TLD_Renew = JSON.parse(existingData);
        }
      },

      getPriceByTLD(tld) {
        let found = this.Data_Price_TLD.find(([key]) => key === tld);
        return found ? found[1] : null;
      },

      getRenewPriceByTLD(tld) {
        if (this.Data_Price_TLD_Renew && this.Data_Price_TLD_Renew[tld]) {
          return this.Data_Price_TLD_Renew[tld];
        }
        return null;
      },

      deleteDomainCart(domain) {
        // Lưu thông tin domain trước khi xóa để kiểm tra combo
        const domainToDelete = this.DomainDatatCart.find(
          (item) => item.domain === domain
        );

        // Xóa domain được chọn (không phân biệt combo hay thường)
        removeDomaininCookie(this.DomainCartCookie, domain);
        showNoticeMessage("success", "Đã xoá tên miền khỏi giỏ hàng");

        // Cập nhật trạng thái giỏ hàng và tự động gom combo mới hoặc trả về domain thường
        this.onLoadCart();

        // Đảm bảo combo được xử lý đúng cách sau khi xóa
        this.$nextTick(() => {
          // Kiểm tra nếu domain bị xóa thuộc combo, reset combo đó
          if (domainToDelete && domainToDelete.isCombo && domainToDelete.comboId) {
            this.resetComboById(domainToDelete.comboId);
            // Reload cart sau khi reset combo để đảm bảo combo được xử lý lại
            this.onLoadCart();
          }

          // Force re-render và cập nhật UI
          this.$forceUpdate();

          // 🔥 QUAN TRỌNG: Emit event deleteDomainCart SAU KHI đã xử lý combo hoàn toàn
          // Thêm delay để đảm bảo cookie được cập nhật và combo được xử lý xong
          setTimeout(() => {
            // Kiểm tra lại cart data từ cookie để đảm bảo đồng bộ trước khi emit
            const currentCartData = JSON.parse(
              JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
            );

            // Cập nhật cart data nếu có sự khác biệt
            if (currentCartData.length !== this.DomainDatatCart.length) {
              this.DomainDatatCart = currentCartData;
              this.$forceUpdate();
            }

            // Emit event sau khi đảm bảo cart đã được cập nhật
            this.$eventBus.$emit("deleteDomainCart", domain);

            // Cập nhật trạng thái empty cart UI
            setTimeout(() => {
              var domains = $(this.$el)
                .find(".vnx-cart-modal-body")
                .find(".vnx-cart-item-remove");

              if (domains.length != 0 && this.DomainDatatCart.length > 0) {
                this.Emptycart = false;
                this.DomainCartFooter = true;
              } else {
                this.Emptycart = true;
                this.DomainCartFooter = false;
              }
            }, 100);
          }, 200);
        });
      },

      updateDomaininCookie(key, domain, register, expires = 30) {
        let data = getCookiesDomainCart(key); // Lấy dữ liệu hiện tại từ cookie
        let index = data.findIndex((item) => item.domain === domain);
        if (index !== -1) {
          data[index] = {
            ...data[index], // Giữ nguyên các thuộc tính khác
            register: register.toString(), // Chắc chắn cập nhật selectedYear
          };
          setCookieDomainCart(key, data, expires); // Cập nhật vào cookie (will normalize automatically)
        } else {
          console.error(`⛔ Domain ${domain} không tồn tại trong cookie`);
        }
      },

      updateSelectedYear(event, domain) {

        let register = parseInt(event.target.value) || 1;
        let index = this.DomainDatatCart.findIndex(
          (item) => item.domain === domain
        );
        if (index !== -1) {
          let currentDomain = this.DomainDatatCart[index];
          let tld = currentDomain.tld;
          let priceList = this.getPriceByTLD(tld);
          let newPrice = 0;

          if (priceList && priceList[register]) {
            newPrice = parseInt(priceList[register]);
          }
          let oldPrice = this.getPriceDomainCart(
            currentDomain.tld,
            currentDomain.register
          );
          // Kiểm tra nếu domain đang trong combo
          if (currentDomain.isCombo && currentDomain.comboId) {
            const comboId = currentDomain.comboId;
            const comboDomains = this.DomainDatatCart.filter(
              (item) => item.comboId === comboId
            );

            if (oldPrice == 0) {
              oldPrice = getPriceDomainData(currentDomain.tld);
              rawPrice = oldPrice.toString().replace(/\./g, "");
              oldPrice = formatPrice(
                parseInt(rawPrice) * currentDomain.register
              );
            }

            // Cập nhật domain hiện tại với chu kỳ mới nhưng giữ nguyên combo
            this.$set(this.DomainDatatCart, index, {
              ...this.DomainDatatCart[index],
              register: register,
              new_price: newPrice,
              old_price: oldPrice,
            });

            // Cập nhật giá combo theo chu kỳ mới
            this.updateComboPriceForDomain(
              domain,
              register,
              currentDomain.comboData
            );
          } else {
            if (oldPrice == 0) {
              oldPrice = getPriceDomainData(currentDomain.tld);
              rawPrice = oldPrice.toString().replace(/\./g, "");
              oldPrice = formatPrice(
                parseInt(rawPrice) * currentDomain.register
              );
            }

            // Cập nhật domain hiện tại với chu kỳ mới
            this.$set(this.DomainDatatCart, index, {
              ...this.DomainDatatCart[index],
              register: register,
              new_price: newPrice,
              old_price: oldPrice,
            });
          }

          this.updateDomaininCookie(this.DomainCartCookie, domain, register);
        } else {
          console.error(
            `[Cart] ❌ Không tìm thấy domain ${domain} trong giỏ hàng`
          );
        }
      },

      getPriceDomainCart(tld, register = 1) {
        let priceList = getPriceDomainData(tld);
        if (priceList > 0) {
          let oldPrice = parseFloat(priceList) * register * 1000;
          return formatPrice(oldPrice);
        } else {
          return 0;
        }
      },

      getPriceDomainDataCombo(tld, register = 1) {
        let priceList = getPriceDomainData(tld);
        if (priceList > 0) {
          let oldPrice = parseFloat(priceList) * register * 1000;
          return formatPrice(oldPrice);
        } else {
          let oldPrice = parseFloat(priceList) * register * 1000;
          return formatPrice(oldPrice);
        }
      },

      // Kiểm tra và cập nhật giá combo tự động
      checkAndUpdateComboPricing() {

        // Lấy danh sách combo từ sessionStorage
        let listComboTLD = [];
        try {
          const comboData = sessionStorage.getItem("Data_Combo_TLD");
          if (comboData) {
            listComboTLD = JSON.parse(comboData);
          }
        } catch (error) {
          console.error("Error parsing Data_Combo_TLD:", error);
          return;
        }

        // Bước 1: Gom các domain theo name (SLD)
        const domainsBySLD = {};
        this.DomainDatatCart.forEach((domain) => {
          if (!domainsBySLD[domain.sld]) {
            domainsBySLD[domain.sld] = [];
          }
          domainsBySLD[domain.sld].push(domain);
        });

        // Bước 2: Sắp xếp các combo theo priority giảm dần (ưu tiên combo lớn hơn)
        const sortedCombos = listComboTLD.sort((a, b) => {
          const tldsA = (a.tlds || a.Tlds || "").split("+").length;
          const tldsB = (b.tlds || b.Tlds || "").split("+").length;
          // Ưu tiên combo có nhiều TLD hơn (combo lớn hơn)
          return tldsB - tldsA;
        });

        // Bước 3: Duyệt từng nhóm name, kiểm tra combo có thể áp dụng
        Object.keys(domainsBySLD).forEach((sld) => {
          const domains = domainsBySLD[sld];

          // Chỉ xử lý khi có từ 2 domain trở lên cùng SLD
          if (domains.length >= 2) {
            const tlds = domains.map((d) => d.tld);

            // Tìm combo phù hợp nhất theo thứ tự ưu tiên
            let bestCombo = null;
            let bestMatchScore = 0;

            for (const combo of sortedCombos) {
              const tldsProperty =
                combo.tlds || combo.Tlds || combo.tld || combo.TLD;
              if (!tldsProperty) continue;

              const comboTlds = tldsProperty
                .split("+")
                .map((tld) => tld.trim());

              // console.log(
              //   `[Cart] So sánh combo: ${tldsProperty} với TLDs hiện tại: ${tlds.join(
              //     ", "
              //   )}`
              // );

              // Kiểm tra xem tất cả TLD có thuộc combo này không
              const allTldsInCombo = tlds.every((tld) =>
                comboTlds.includes(tld)
              );

              if (allTldsInCombo) {
                // Tính điểm ưu tiên: combo có nhiều TLD hơn sẽ có điểm cao hơn
                const matchScore = comboTlds.length;

                // Nếu combo hoàn toàn khớp (số TLD bằng nhau), ưu tiên cao nhất
                const isPerfectMatch = tlds.length === comboTlds.length;
                const finalScore = isPerfectMatch ? matchScore * 2 : matchScore;

                if (finalScore > bestMatchScore) {
                  bestMatchScore = finalScore;
                  bestCombo = combo;
                  // console.log(
                  //   `[Cart] Tìm thấy combo tốt hơn: ${tldsProperty} với điểm: ${finalScore}`
                  // );
                }
              }
            }

            // Áp dụng combo tốt nhất tìm được
            if (bestCombo) {
              // console.log("[Cart] Áp dụng combo tốt nhất:", bestCombo);
              this.updateDomainsToCombo(domains, bestCombo);
            } else {
              // console.log(
              //   "[Cart] Không tìm thấy combo phù hợp, reset về domain thường"
              // );
              // Không tìm thấy combo phù hợp, reset về domain thường
              this.resetDomainsToRegular(domains);
            }
          } else {
            // console.log("[Cart] Chỉ còn 1 domain, reset về domain thường");
            // Chỉ có 1 domain, reset về domain thường
            this.resetDomainsToRegular(domains);
          }
        });
      },

      // Cập nhật domains thành combo
      updateDomainsToCombo(domains, combo) {
        // console.log("[Cart] updateDomainsToCombo với combo:", combo);

        const comboId = `combo_${combo.stt}_${Date.now()}`;
        const comboPricing = combo.combopricing || combo["Combo Pricing"];

        if (!comboPricing) {
          // console.log("[Cart] Không có combo pricing");
          return;
        }

        // console.log("[Cart] Combo pricing:", comboPricing);

        // Tách các giá trị từ Combo Pricing
        const comboPrices = comboPricing
          .split("+")
          .map((price) => parseInt(price.trim().replace(/\./g, "")) || 0);

        // console.log("[Cart] Parsed combo prices:", comboPrices);

        // Lấy danh sách TLD trong combo
        const tldsInCombo = (combo.tlds || combo.Tlds || "")
          .split("+")
          .map((tld) => tld.trim());

        // console.log("[Cart] TLDs in combo:", tldsInCombo);

        // Cập nhật từng domain với giá tương ứng theo TLD thực tế
        domains.forEach((domain) => {
          const cartIndex = this.DomainDatatCart.findIndex(
            (item) => item.domain === domain.domain
          );

          // Tìm vị trí của TLD này trong combo để lấy giá đúng
          const tldIndex = tldsInCombo.indexOf(domain.tld);
          const baseComboPrice = tldIndex !== -1 ? comboPrices[tldIndex] : 0;
          // Tính giá combo theo chu kỳ thực tế của domain
          const actualComboPrice = baseComboPrice * domain.register;

          // console.log(
          //   `[Cart] Cập nhật domain ${domain.domain} (TLD: ${domain.tld}, ${domain.register} năm) với giá combo: ${actualComboPrice} (base: ${baseComboPrice} x ${domain.register})`
          // );

          if (cartIndex !== -1 && baseComboPrice !== undefined) {
            let oldPrice = this.getPriceDomainCart(domain.tld, domain.register);
            if (oldPrice == 0) {
              oldPrice = getPriceDomainData(domain.tld);
              rawPrice = oldPrice.toString().replace(/\./g, "");
              oldPrice = formatPrice(parseInt(rawPrice) * domain.register);
            }
            // Cập nhật thông tin combo
            this.$set(this.DomainDatatCart, cartIndex, {
              ...this.DomainDatatCart[cartIndex],
              isCombo: true,
              comboId: comboId,
              comboData: {
                stt: combo.stt,
                tlds: combo.tlds || combo.Tlds,
                defaultpricing:
                  combo.defaultpricing || combo["Default Pricing"],
                combopricing: combo.combopricing || combo["Combo Pricing"],
                titlecombo: combo.titlecombo || combo["Title Combo"],
                contentcombo: combo.contentcombo || combo["Content Combo"],
              },
              new_price: actualComboPrice,
              old_price: oldPrice,
            });


            // Cập nhật vào cookie
            this.updateComboInCookie(
              domain.domain,
              this.DomainDatatCart[cartIndex]
            );
          }
        });
      },

      // Cập nhật thông tin combo trong cookie
      updateComboInCookie(domain, updatedItem) {
        try {
          let data = getCookiesDomainCart(this.DomainCartCookie);
          let index = data.findIndex((item) => item.domain === domain);

          if (index !== -1) {
            // setCookieDomainCart will normalize the data automatically
            data[index] = updatedItem;
            setCookieDomainCart(
              this.DomainCartCookie,
              data,
              30
            );
          }
        } catch (error) {
          console.error("Error updating combo in cookie:", error, updatedItem);
        }
      },

      // Reset combo cho các domain còn lại khi không đủ điều kiện
      resetComboForRemainingDomains() {
        // Cập nhật tất cả domain còn lại về trạng thái thường
        this.DomainDatatCart.forEach((domain, index) => {
          if (domain.isCombo) {
            // Tính lại giá thường
            let priceList = this.getPriceByTLD(domain.tld);
            let newPrice = 0;

            if (priceList && priceList[domain.register]) {
              newPrice = parseInt(priceList[domain.register]);
            }
            let oldPrice = this.getPriceDomainCart(domain.tld, domain.register);
            if (oldPrice == 0) {
              oldPrice = getPriceDomainData(domain.tld);
              rawPrice = oldPrice.toString().replace(/\./g, "");
              oldPrice = formatPrice(parseInt(rawPrice) * domain.register);
            }

            // Reset về domain thường
            this.$set(this.DomainDatatCart, index, {
              ...this.DomainDatatCart[index],
              isCombo: false,
              comboId: null,
              comboData: null,
              new_price: newPrice,
              old_price: oldPrice,
            });

            // Cập nhật vào cookie
            this.updateComboInCookie(
              domain.domain,
              this.DomainDatatCart[index]
            );
          }
        });
      },

      // Reset domains về trạng thái thường
      resetDomainsToRegular(domains) {

        domains.forEach((domain) => {
          const cartIndex = this.DomainDatatCart.findIndex(
            (item) => item.domain === domain.domain
          );

          if (cartIndex !== -1) {
            // Tính lại giá thường
            let priceList = this.getPriceByTLD(domain.tld);
            let newPrice = 0;

            if (priceList && priceList[domain.register]) {
              newPrice = parseInt(priceList[domain.register]);
            }

            let oldPrice = this.getPriceDomainCart(domain.tld, domain.register);
            if (oldPrice == 0) {
              oldPrice = getPriceDomainData(domain.tld);
              rawPrice = oldPrice.toString().replace(/\./g, "");
              oldPrice = formatPrice(parseInt(rawPrice) * domain.register);
            }

            // Reset về domain thường
            this.$set(this.DomainDatatCart, cartIndex, {
              ...this.DomainDatatCart[cartIndex],
              isCombo: false,
              comboId: null,
              comboData: null,
              new_price: newPrice,
              old_price: oldPrice,
            });

            this.updateComboInCookie(
              domain.domain,
              this.DomainDatatCart[cartIndex]
            );
          }
        });
      },

      // 🔄 Tái xử lý combo khi có thay đổi giỏ hàng
      reprocessComboAfterChange() {
        // Bước 1: Reset toàn bộ giá domain về giá gốc
        this.resetAllDomainsToOriginalPrice();

        // Bước 2: Kiểm tra và reset combo không hợp lệ
        this.checkAndResetInvalidCombos();

        // Bước 3: Gom domain theo name (SLD)
        const domainsBySLD = this.groupDomainsBySLD();

        // Bước 4: Xét danh sách COMBO theo priority DESC
        const sortedCombos = this.getSortedCombosByPriority();

        // Bước 5: Với mỗi name trong giỏ hàng, duyệt qua danh sách combo theo thứ tự ưu tiên
        this.applyCombosToDomains(domainsBySLD, sortedCombos);

        // Bước 6: Cập nhật cookie với trạng thái mới
        this.updateAllDomainsInCookie();
      },

      // Bước 1: Reset toàn bộ giá domain về giá gốc
      resetAllDomainsToOriginalPrice() {
        this.DomainDatatCart.forEach((domain, index) => {
          // Reset TẤT CẢ domain về giá gốc, không phân biệt combo hay thường
          // Vì khi có thay đổi trong cart (thêm/xóa domain), combo có thể không còn hợp lệ

          // Tính lại giá gốc cho domain
          let priceList = this.getPriceByTLD(domain.tld);
          let originalPrice = 0;

          if (priceList && priceList[domain.register]) {
            originalPrice = parseInt(priceList[domain.register]);
          }

          // Reset về domain thường
          this.$set(this.DomainDatatCart, index, {
            ...this.DomainDatatCart[index],
            isCombo: false,
            comboId: null,
            comboData: null,
            new_price: originalPrice,
            old_price: this.getPriceDomainCart(domain.tld, domain.register),
            used: false, // Đánh dấu domain chưa được sử dụng cho combo
          });
        });
      },

      // Bước 2: Gom domain theo name (SLD)
      groupDomainsBySLD() {
        const domainsBySLD = {};
        this.DomainDatatCart.forEach((domain) => {
          if (!domainsBySLD[domain.sld]) {
            domainsBySLD[domain.sld] = [];
          }
          domainsBySLD[domain.sld].push(domain);
        });

        return domainsBySLD;
      },

      // Bước 3: Xét danh sách COMBO theo priority DESC
      getSortedCombosByPriority() {
        let listComboTLD = [];
        try {
          const comboData = sessionStorage.getItem("Data_Combo_TLD");
          if (comboData) {
            listComboTLD = JSON.parse(comboData);
          }
        } catch (error) {
          console.error("Error parsing Data_Combo_TLD:", error);
          return [];
        }

        // Sắp xếp combo theo priority giảm dần (ưu tiên combo lớn hơn)
        const sortedCombos = listComboTLD.sort((a, b) => {
          const tldsA = (a.tlds || a.Tlds || "").split("+").length;
          const tldsB = (b.tlds || b.Tlds || "").split("+").length;
          // Ưu tiên combo có nhiều TLD hơn (combo lớn hơn)
          return tldsB - tldsA;
        });

        return sortedCombos;
      },

      // Bước 4: Với mỗi name trong giỏ hàng, duyệt qua danh sách combo theo thứ tự ưu tiên
      applyCombosToDomains(domainsBySLD, sortedCombos) {
        Object.keys(domainsBySLD).forEach((sld) => {
          let domains = domainsBySLD[sld];

          // Lặp qua các combo theo priority
          for (const combo of sortedCombos) {
            const tldsProperty =
              combo.tlds || combo.Tlds || combo.tld || combo.TLD;
            if (!tldsProperty) continue;
            const comboTlds = tldsProperty.split("+").map((tld) => tld.trim());

            // Tìm tất cả nhóm domain chưa dùng combo, đủ điều kiện (đủ TLD, KHÔNG kiểm tra register)
            let group = [];
            comboTlds.forEach((tld) => {
              const found = domains.find(
                (d) => !d.used && d.tld === tld
              );
              if (found) group.push(found);
            });

            if (group.length === comboTlds.length) {
              // Áp dụng combo cho nhóm này
              this.applyComboToDomains(group, combo);
            }
          }
        });
      },

      canApplyComboToDomains(domains, combo) {
        const tldsProperty = combo.tlds || combo.Tlds || combo.tld || combo.TLD;
        if (!tldsProperty) return false;
        const comboTlds = tldsProperty.split("+").map((tld) => tld.trim());
        // KHÔNG kiểm tra register nữa, chỉ kiểm tra đủ TLD
        const availableTlds = domains
          .filter((domain) => !domain.used)
          .map((domain) => domain.tld);
        const allTldsAvailable = comboTlds.every((tld) =>
          availableTlds.includes(tld)
        );
        const availableDomainsForCombo = domains.filter(
          (domain) => !domain.used
        );
        const hasEnoughDomains =
          availableDomainsForCombo.length >= comboTlds.length;
        return allTldsAvailable && hasEnoughDomains;
      },

      applyComboToDomains(domains, combo) {
        const comboId = `combo_${combo.stt}_${Date.now()}`;
        const comboPricing = combo.combopricing || combo["Combo Pricing"];

        if (!comboPricing) {
          return;
        }

        // Tách các giá trị từ Combo Pricing (giá combo cho 1 năm)
        const comboPrices = comboPricing
          .split("+")
          .map((price) => parseInt(price.trim().replace(/\./g, "")) || 0);

        // Lấy danh sách TLD trong combo
        const tldsInCombo = (combo.tlds || combo.Tlds || "")
          .split("+")
          .map((tld) => tld.trim());

        // Tìm các domain phù hợp và áp dụng combo
        let appliedCount = 0;
        const appliedDomains = []; // Lưu danh sách domain đã áp dụng combo
        const maxDomainsToApply = tldsInCombo.length; // Số lượng domain tối đa có thể áp dụng combo

        // Lọc các domain chưa sử dụng và phù hợp với combo
        const availableDomains = domains.filter((domain) => !domain.used);

        // Áp dụng combo cho từng TLD trong combo
        tldsInCombo.forEach((targetTld, tldIndex) => {
          // Tìm domain chưa sử dụng có TLD phù hợp
          const matchingDomain = availableDomains.find(
            (domain) => domain.tld === targetTld && !domain.used
          );

          if (matchingDomain && appliedCount < maxDomainsToApply) {
            const cartIndex = this.DomainDatatCart.findIndex(
              (item) => item.domain === matchingDomain.domain
            );

            if (cartIndex !== -1) {
              // Tính giá combo theo chu kỳ thực tế của domain
              const baseComboPrice = comboPrices[tldIndex]; // Giá combo cho 1 năm
              const actualComboPrice = baseComboPrice * matchingDomain.register; // Nhân với số năm

              // Lấy giá gốc (old_price) theo logic domain thường
              let oldPrice = this.getPriceDomainCart(
                matchingDomain.tld,
                matchingDomain.register
              );
              if (oldPrice == 0) {
                oldPrice = getPriceDomainData(matchingDomain.tld);
                rawPrice = oldPrice.toString().replace(/\./g, "");
                oldPrice = formatPrice(
                  parseInt(rawPrice) * matchingDomain.register
                );
              }
              // Cập nhật thông tin combo
              this.$set(this.DomainDatatCart, cartIndex, {
                ...this.DomainDatatCart[cartIndex],
                isCombo: true,
                comboId: comboId,
                comboData: {
                  stt: combo.stt,
                  tlds: combo.tlds || combo.Tlds,
                  defaultpricing:
                    combo.defaultpricing || combo["Default Pricing"],
                  combopricing: combo.combopricing || combo["Combo Pricing"],
                  titlecombo: combo.titlecombo || combo["Title Combo"],
                  contentcombo: combo.contentcombo || combo["Content Combo"],
                },
                new_price: actualComboPrice,
                old_price: oldPrice,
                used: true, // Đánh dấu domain đã được sử dụng
              });

              appliedCount++;
              appliedDomains.push(matchingDomain.domain);
            }
          }
        });
      },

      // Bước 5: Cập nhật cookie với trạng thái mới
      updateAllDomainsInCookie() {
        this.DomainDatatCart.forEach((domain) => {
          this.updateComboInCookie(domain.domain, domain);
        });
      },

      // Function mới: Kiểm tra và reset combo không hợp lệ
      checkAndResetInvalidCombos() {
        // Lấy danh sách combo từ sessionStorage
        let listComboTLD = [];
        try {
          const comboData = sessionStorage.getItem("Data_Combo_TLD");
          if (comboData) {
            listComboTLD = JSON.parse(comboData);
          }
        } catch (error) {
          console.error("Error parsing Data_Combo_TLD:", error);
          return;
        }

        // Gom domain theo SLD
        const domainsBySLD = {};
        this.DomainDatatCart.forEach((domain) => {
          if (!domainsBySLD[domain.sld]) {
            domainsBySLD[domain.sld] = [];
          }
          domainsBySLD[domain.sld].push(domain);
        });

        // Kiểm tra từng nhóm SLD
        Object.keys(domainsBySLD).forEach((sld) => {
          const domains = domainsBySLD[sld];

          // Kiểm tra từng domain có combo
          domains.forEach((domain) => {
            if (domain.isCombo && domain.comboData) {
              const combo = domain.comboData;
              const tldsProperty = combo.tlds || combo.Tlds || "";
              const comboTlds = tldsProperty
                .split("+")
                .map((tld) => tld.trim());

              // Kiểm tra xem combo này còn hợp lệ không
              const isValidCombo = this.isComboStillValid(domains, comboTlds);

              if (!isValidCombo) {
                console.log(
                  `[Cart] ❌ Combo ${combo.stt} không còn hợp lệ cho domain ${domain.domain}, reset về giá thường`
                );
                this.resetSingleDomainCombo(domain);
              }
            }
          });
        });
      },

      // Function mới: Kiểm tra combo có còn hợp lệ không
      isComboStillValid(domains, comboTlds) {
        // Kiểm tra xem có đủ TLD trong combo không
        const availableTlds = domains.map((domain) => domain.tld);
        const allTldsAvailable = comboTlds.every((tld) =>
          availableTlds.includes(tld)
        );

        // Kiểm tra có đủ số lượng domain không
        const hasEnoughDomains = domains.length >= comboTlds.length;

        return allTldsAvailable && hasEnoughDomains;
      },

      // Function mới: Reset combo khi xóa domain
      resetComboWhenDomainRemoved(removedDomain) {

        // Tìm domain bị xóa trong cart trước khi xóa
        const domainToDelete = this.DomainDatatCart.find(
          (item) => item.domain === removedDomain
        );

        if (
          domainToDelete &&
          domainToDelete.isCombo &&
          domainToDelete.comboId
        ) {
          const comboId = domainToDelete.comboId;

          // Reset toàn bộ combo này
          this.resetComboById(comboId);
        }
      },

      // Reset combo cho một domain đơn lẻ
      resetSingleDomainCombo(domain) {
        const cartIndex = this.DomainDatatCart.findIndex(
          (item) => item.domain === domain.domain
        );

        if (cartIndex !== -1) {
          // Tính lại giá thường
          let priceList = this.getPriceByTLD(domain.tld);
          let newPrice = 0;

          if (priceList && priceList[domain.register]) {
            newPrice = parseInt(priceList[domain.register]);
          }
          let oldPrice = this.getPriceDomainCart(domain.tld, domain.register);
          if (oldPrice == 0) {
            oldPrice = getPriceDomainData(domain.tld);
            rawPrice = oldPrice.toString().replace(/\./g, "");
            oldPrice = formatPrice(parseInt(rawPrice) * domain.register);
          }
          // Reset về domain thường
          this.$set(this.DomainDatatCart, cartIndex, {
            ...this.DomainDatatCart[cartIndex],
            isCombo: false,
            comboId: null,
            comboData: null,
            new_price: newPrice,
            old_price: oldPrice,
          });

          this.updateComboInCookie(
            domain.domain,
            this.DomainDatatCart[cartIndex]
          );
        }
      },

      // Cập nhật giá combo cho domain theo chu kỳ mới
      updateComboPriceForDomain(domain, register, comboData) {

        if (!comboData || !comboData.combopricing) {
          return;
        }

        // Tách các giá trị từ Combo Pricing (giá combo cho 1 năm)
        const comboPrices = comboData.combopricing
          .split("+")
          .map((price) => parseInt(price.trim().replace(/\./g, "")) || 0);

        // Lấy danh sách TLD trong combo
        const tldsInCombo = (comboData.tlds || "")
          .split("+")
          .map((tld) => tld.trim());

        // Tìm domain trong cart
        const cartIndex = this.DomainDatatCart.findIndex(
          (item) => item.domain === domain
        );

        if (cartIndex !== -1) {
          const currentDomain = this.DomainDatatCart[cartIndex];

          // Tìm vị trí của TLD này trong combo để lấy giá đúng
          const tldIndex = tldsInCombo.indexOf(currentDomain.tld);
          const baseComboPrice = tldIndex !== -1 ? comboPrices[tldIndex] : 0;

          // Lấy giá gia hạn cho TLD này
          const renewPriceData = this.getRenewPriceByTLD(currentDomain.tld);

          let totalPrice = baseComboPrice; // Bắt đầu với giá combo năm đầu

          // Tính giá gia hạn cho các năm tiếp theo
          if (register > 1 && renewPriceData) {
            // Chỉ thêm giá gia hạn cho năm thứ (register-1)
            const renewYear = register - 1;
            const renewPrice =
              renewPriceData[renewYear] || renewPriceData[1] || 0;
            totalPrice += parseInt(renewPrice);

          }


          // Cập nhật giá mới cho domain
          this.$set(this.DomainDatatCart, cartIndex, {
            ...this.DomainDatatCart[cartIndex],
            new_price: totalPrice,
          });

          // Cập nhật vào cookie
          this.updateComboInCookie(domain, this.DomainDatatCart[cartIndex]);
        }
      },

      // Reset toàn bộ combo theo comboId
      resetComboById(comboId) {

        // Tìm tất cả domain thuộc combo này và reset về giá ban đầu
        this.DomainDatatCart.forEach((domain, index) => {
          if (domain.comboId === comboId) {

            // Tính lại giá gốc
            let priceList = this.getPriceByTLD(domain.tld);
            let originalPrice = 0;

            if (priceList && priceList[domain.register]) {
              originalPrice = parseInt(priceList[domain.register]);
            }
            let oldPrice = this.getPriceDomainCart(domain.tld, domain.register);
            if (oldPrice == 0) {
              oldPrice = getPriceDomainData(domain.tld);
              rawPrice = oldPrice.toString().replace(/\./g, "");
              oldPrice = formatPrice(parseInt(rawPrice) * domain.register);
            }
            // Reset về domain thường
            this.$set(this.DomainDatatCart, index, {
              ...this.DomainDatatCart[index],
              isCombo: false,
              comboId: null,
              comboData: null,
              new_price: originalPrice,
              old_price: oldPrice,
              used: false, // Đánh dấu domain chưa được sử dụng
            });

            // Cập nhật vào cookie
            this.updateComboInCookie(
              domain.domain,
              this.DomainDatatCart[index]
            );
          }
        });

        // Cập nhật UI sau khi reset combo
        this.$nextTick(() => {
          this.$forceUpdate();
        });
      },

      clickShowCart() {
        document.body.style.overflow = "hidden";
        this.ShowCartDesktop = true;
      },
      clickHiddenCart() {
        const modal = document.querySelector(".vnx-cart-modal");
        if (modal) {
          // Thêm class để trigger animation đóng
          modal.classList.add("slide-out");

          // Đợi animation hoàn thành rồi mới ẩn cart
          setTimeout(() => {
            document.body.style.overflow = "";
            this.ShowCartDesktop = false;
            // Xóa class animation sau khi ẩn
            modal.classList.remove("slide-out");
          }, 400); // Thời gian bằng với animation-duration
        } else {
          document.body.style.overflow = "";
          this.ShowCartDesktop = false;
        }
      },
      // External function
    },
  });
});

function updatePopupPadding() {
  let header = document.querySelector("header");
  let popup = document.querySelector(".vnx_cart_popup");

  if (header && popup) {
    let headerBottom = header.getBoundingClientRect().bottom;
    popup.style.paddingTop = headerBottom + "px";
  }
}

// Gọi khi trang load
window.addEventListener("load", updatePopupPadding);

// Gọi khi resize màn hình
window.addEventListener("resize", updatePopupPadding);

// Gọi khi scroll để cập nhật nếu header thay đổi chiều cao
window.addEventListener("scroll", updatePopupPadding);
