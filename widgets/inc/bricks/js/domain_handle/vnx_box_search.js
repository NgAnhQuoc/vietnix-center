window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + "/wp-admin/admin-ajax.php";
Vue.prototype.$eventBus = new Vue();
//function dùng cho search domain onpage

// tránh khai báo lại khi gọi file nhiều lần
if (!window?.DomainMixin) {
  window.DomainMixin = {
    data() {
      return {
        DomainCartCookie: "vnx_domain_carts",
        LoadingResult: false,
        DomainShowResult: false,
        DomainIntCart: false,
        DomainPremium: false,
      };
    },
    mounted() {
      this.setDataPriceDomain();
    },
    methods: {
      clickButtonClear() {
        if (this.DisableButton) {
          return;
        }
        let domainInput = $(this.$el).find("#vnx_search_domain_input");
        domainInput.val("");
        this.LoadingResult = false;
        this.DomainShowResult = false;
      },

      onLoadSearch() {
        const urlParams = new URLSearchParams(window.location.search);
        const domainParam = urlParams.get("domain");
        if (domainParam) {
          let domainInput = $(this.$el).find("#vnx_search_domain_input");
          domainInput.val(domainParam); // Gán vào data của Vue
          this.clickSearchButton({ preventDefault: () => { } });
        }
      },

      setDataPriceDomain() {
        let storedData = sessionStorage.getItem("Data_TLD_Sussgest");
        let existingData = localStorage.getItem("Data_Price_TLD");
        if (!existingData && storedData) {
          let dataPrice = [];
          let listdata = JSON.parse(storedData).join(", ");
          vnx_post_ajax_Data("POST", "get_listpriceDomain_center", listdata).then(
            (response) => {
              var data_list = response.data;
              data_list.forEach((item) => {
                if (item.tld != null && item.pricing != null) {
                  dataPrice.push([item.tld, item.pricing.register]);
                }
              });
              localStorage.setItem("Data_Price_TLD", JSON.stringify(dataPrice));
              this.$eventBus.$emit("triggerLoadcart");
            }
          );
        }
      },
      changerButtonCart() {
        // Kiểm tra trạng thái thực tế trong cart thay vì reset
        if (this.DomainNameSld && this.DomainNameTld) {
          let mainDomain = `${this.DomainNameSld}.${this.DomainNameTld}`;
          var data = getCookiesDomainCart(this.DomainCartCookie);
          let isMainDomainInCart = data.some(
            (item) => item.domain === mainDomain
          );
          this.DomainIntCart = isMainDomainInCart;
        } else {
          this.DomainIntCart = false;
        }
      },

      clickAddToCart(sld, tld) {
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

        let newData = JSON.parse(
          JSON.stringify(getCookiesDomainCart(this.DomainCartCookie))
        );

        // Chỉ cập nhật DomainIntCart nếu domain được click là domain chính
        if (this.DomainNameSld === sld && this.DomainNameTld === tld) {
          this.DomainIntCart = true;
        }

        this.$eventBus.$emit("clickAddToCart", newData);
      },
    },
  };
}

document
  .querySelectorAll(".brxe-vnx-search-domain-form-v2.onpage")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixin],
      data: {
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
        DomainCartCookie: "vnx_domain_carts",
        DomainCookie: ".vietnix.vn",
        DomainGetCookie: "",
        DomainDatatCart: "",
        DisableButton: false,
      },
      mounted() {
        this.onLoadSearch();
      },
      created() {
        // Lắng nghe sự kiện từ eventBus xoá domain khỏi giỏ hàng
        this.$eventBus.$on("deleteDomainCart", (domain) => {
          let domainInputCart = $(this.$el).find(".add_domain_cart");
          if (domain == domainInputCart.attr("data-domain")) {
            this.changerButtonCart();
          }
        });
      },
      methods: {
        // Internal function
        async clickSearchButton(e) {
          this.DisableButton = true;
          e.preventDefault();
          this.LoadingResult = true;
          this.DomainShowResult = false;
          let domainInput = $(this.$el)
            .find("#vnx_search_domain_input")
            .val()
            .toLowerCase();
          let box_result = $(this.$el).find("#vnx_box_result").val();
          const security = $(this.$el).find("#vnx_domain_security").val();
          domainInput = domainInput.trim();
          if (domainInput.includes(".") == false) {
            var tdl_suggest = $(this.$el).find("#vnx_suggest_tld").val();
            domainInput = domainInput + "." + tdl_suggest;
          }

          if (domainInput.length > 0 && isValidDomain(domainInput) == true) {
            // Check validation rules trước khi gọi API
            try {
              const validationResponse = await $.ajax({
                url: admin_ajax_url,
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json',
                data: {
                  action: 'validate_single_domain_center',
                  domain: domainInput
                }
              });

              if (validationResponse.success === false) {
                this.LoadingResult = false;
                this.DisableButton = false;
                showNoticeMessage("error", validationResponse.data.message || 'Tên miền không hợp lệ');
                return;
              }
            } catch (error) {
              console.error('Error checking validation rules:', error);
            }
            setTimeout(() => {
              this.$eventBus.$emit("triggerResult", {
                box_result,
                domainInput,
              });
            }, 100);
            this.handleApiCheckDomain(domainInput, security).then(
              (response) => {
                response = response.data;
                this.LoadingResult = false;
                this.DomainShowResult = true;
                this.DomainError = false;
                this.DomainName = domainInput;
                if (response.result === "success") {
                  const status = response.status;
                  const domainVn = response.domainVn;
                  if (status === "available" && domainVn === "available") {
                    this.DomainAvailable = true;
                    this.DomainPrice = response.pricedomain;
                    this.DomainNameSld = response.sld;
                    this.DomainNameTld = response.tld;
                    this.DomainPriceReduction = getPriceDomainData(
                      response.tld
                    );
                    this.DomainPricePercent = calculatePriceDomain(
                      response.pricedomain,
                      this.DomainPriceReduction
                    );
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
                      let domainInputCart = $(this.$el).find(
                        ".add_domain_cart"
                      );
                      domainInputCart.attr("data-domain", domainInput);
                      domainInputCart.attr("data-sld", this.DomainNameSld);
                      domainInputCart.attr("data-tld", this.DomainNameTld);
                    }, 100);
                  } else if (domainVn === "unavailable") {
                    this.DomainVnError = true;
                    this.DomainAvailable = false;
                    this.DomainError = false;
                  } else {
                    this.DomainVnError = false;
                    this.DomainAvailable = false;
                  }
                } else {
                  this.DomainError = true;
                  this.DomainAvailable = false;
                }
                this.DisableButton = false;
              }
            ).catch((error) => {
              console.error("Error checking domain:", error);
              this.LoadingResult = false;
              this.DisableButton = false;
              showNoticeMessage("error", "Có lỗi xảy ra khi kiểm tra tên miền, vui lòng thử lại.");
            });
          } else {
            this.DisableButton = false;
            this.LoadingResult = false;
            showNoticeMessage("error", "Vui lòng nhập tên miền.");
          }
        },
        // External function
        handleApiCheckDomain(domain, security) {
          var data = {
            data: {
              security: security,
              domain: domain,
            },
          };
          return vnx_post_ajax_Data("POST", "check_domain_data_center", data);
        },
      },
    });
  });
//function dùng cho search domain redirect
document
  .querySelectorAll(".brxe-vnx-search-domain-form-v2.redirect")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      data: {
        DomainLoading: false,
        domain: "",
      },
      methods: {
        async clickSearchButtonRedirect(e) {
          e.preventDefault();

          if (!this.domain.trim()) {
            showNoticeMessage("error", "Vui lòng nhập tên miền cần tìm kiếm.");
            return;
          }

          this.domain = this.domain.toLowerCase().trim();
          const sld = this.domain.split('.')[0];

          // Check validation rules trước khi submit
          try {
            const validationResponse = await $.ajax({
              url: admin_ajax_url,
              type: 'POST',
              contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
              dataType: 'json',
              data: {
                action: 'validate_single_domain_center',
                domain: sld
              }
            });

            if (validationResponse.success === false) {
              showNoticeMessage("error", validationResponse.data.message || 'Tên miền không hợp lệ');
              return;
            }
          } catch (error) {
            console.error('Error checking validation rules:', error);
          }

          // If validation passed, submit form
          this.DomainLoading = true;
          $(this.$el).find('form').submit();
        },
      },
    });
  });

//function dùng cho search domain on page whois
document
  .querySelectorAll(".brxe-vnx-search-domain-form-v2.page-whois")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixin],
      data: {
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
        DomainCartCookie: "vnx_domain_carts",
        DomainCookie: ".vietnix.vn",
        DomainGetCookie: "",
        DomainDatatCart: "",
        DisableButton: false,
      },
      mounted() {
        this.onLoadSearch();
      },
      created() {
        // Lắng nghe sự kiện từ eventBus xoá domain khỏi giỏ hàng
        this.$eventBus.$on("deleteDomainCart", (domain) => {
          let domainInputCart = $(this.$el).find(".add_domain_cart");
          if (domain == domainInputCart.attr("data-domain")) {
            this.changerButtonCart();
          }
        });

        this.$eventBus.$on("completeGetDomain", ({ success }) => {
          if (success) {
            this.DisableButton = false;
          }
        });
      },
      methods: {
        // Internal function
        async clickSearchButton(e) {
          this.DisableButton = true;
          e.preventDefault();
          this.LoadingResult = true;
          this.DomainShowResult = false;
          let domainInput = $(this.$el)
            .find("#vnx_search_domain_input")
            .val()
            .toLowerCase();
          let box_result = $(this.$el).find("#vnx_box_result").val();
          const security = $(this.$el).find("#vnx_domain_security").val();
          domainInput = domainInput.trim();
          if (domainInput.includes(".") == false) {
            var tdl_suggest = "vn";
            domainInput = domainInput + "." + tdl_suggest;
          }

          if (domainInput.length > 0 && isValidDomain(domainInput) == true) {
            this.setParameterUrl("domain", domainInput);
            setTimeout(() => {
              this.$eventBus.$emit("triggerResult", {
                box_result,
                domainInput,
              });
            }, 100);
          } else {
            this.DisableButton = false;
            this.LoadingResult = false;
            showNoticeMessage("error", "Vui lòng nhập tên miền.");
          }
        },

        // External function
        handleApiCheckDomain(domain, security) {
          var data = {
            data: {
              security: security,
              domain: domain,
            },
          };
          return vnx_post_ajax_Data("POST", "check_domain_data_center", data);
        },

        setParameterUrl(key, value) {
          const url = new URL(window.location);
          url.searchParams.set(key, value);
          window.history.pushState({}, "", url);
        },
      },
    });
  });

//function dùng cho search muti whois domain on page whois
document
  .querySelectorAll(".brxe-vnx-search-domain-form-v2.search-muti-whois")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixin],
      data: {
        IsLoading: false,
        ListDomain: [],
        UrlDomainSampleCSV: window.urlDomainSampleCSV,
        BoxResult: window.boxResult,
        NameCookiesCheckDomain: "vnx_extention_domain_checked",
        InputDomain: "",
        LineCount: 0,
        ListDomainChecked: [],

        // data php
        IsRedirect: window.isRedirect,
        UrlRedirectSearchMuti: window.urlRedirectSearchMuti,
      },
      mounted() {
        handlePopupImportFile();
        handlePopupExtentionDomain();
      },
      created() {
        this.$eventBus.$on("completeGetDomain", ({ success }) => {
          if (success) {
            this.IsLoading = false;
          }
        });

        this.ListDomainChecked = this.getDomainCheckedCookies();
        this.InputDomain = this.getInputDomainFromUrlParams();
      },
      watch: {
        // theo dỗi sự thay đổi của input domain để cập nhật số dòng
        InputDomain() {
          this.LineCount = this.InputDomain
            ? this.InputDomain.split("\n").filter((line) => line.trim() !== "")
              .length
            : 0;
        },

        ListDomainChecked() {
          let listDomainChecked = JSON.stringify(this.ListDomainChecked);
          document.cookie = `${this.NameCookiesCheckDomain
            }=${listDomainChecked}; path=/; max-age=${30 * 24 * 60 * 60}`;
        },
      },
      methods: {
        // Xử lý submit form
        async handleSubmit(e) {
          this.IsLoading = true;
          e.preventDefault();
          this.ListDomain = [];
          const domainInputEL = $(this.$el).find("#vnx-input-domain");
          const domainInput = domainInputEL.val().trim();
          const listDomainCookies = JSON.parse(
            getCookieKey(this.NameCookiesCheckDomain) || "[]"
          );

          if (!domainInput) {
            showNoticeMessage("error", "Vui lòng nhập tên miền.");
            this.IsLoading = false;
            return false;
          }
          // Tách các phần tử xuống hàng thành mảng
          let domainArray = domainInput
            .split(/[\n,]+/)
            .map((item) => item.trim().toLowerCase())
            .filter((item) => item);

          // Kiểm tra và thêm hậu tố nếu cần
          const checkDomain = this.checkDomain(domainArray);
          if (!checkDomain) {
            this.IsLoading = false;
            return false;
          }
          // Kiểm tra domain
          let flagError = false;
          domainArray = domainArray
            .map((domain) => {
              if (domain.includes(".")) {
                return domain;
              } else {
                // nếu đã chọn hậu tố thì thêm vào
                if (listDomainCookies.length > 0) {
                  return listDomainCookies.map(
                    (suffix) => `${domain}.${suffix}`
                  );
                } else {
                  //hiện thị thông báo phải chọn extention
                  this.showTooltipDomain();
                  flagError = true;
                  return [];
                }
              }
            })
            .flat();
          if (flagError) {
            this.IsLoading = false;
            return;
          }

          // Loại bỏ phần tử trùng lặp
          domainArray = [...new Set(domainArray)];

          // Validate domains trước khi xử lý
          try {
            for (let domain of domainArray) {
              const validationResponse = await $.ajax({
                url: admin_ajax_url,
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json',
                data: {
                  action: 'validate_single_domain_center',
                  domain: domain
                }
              });

              if (validationResponse.success === false) {
                showNoticeMessage("error", `Tên miền "${domain}": ${validationResponse.data.message}`);
                this.IsLoading = false;
                return false;
              }
            }
          } catch (error) {
            console.error('Error checking validation rules:', error);
          }

          const classify = classifyDomains(domainArray);

          const totalResult = classify.reduce(
            (total, item) => total + item.tld.length,
            0
          );
          const totalDomain = classify.length;

          // Tạo URL với thông tin cần thiết
          let url =
            "?domain=" +
            encodeURIComponent(JSON.stringify(this.InputDomain)) +
            "&totalDomain=" +
            totalDomain +
            "&totalResult=" +
            totalResult;

          if (this.IsRedirect) {
            // Chuyển hướng nếu IsRedirect = true
            window.location.href = this.UrlRedirectSearchMuti + url;
          } else {
            // Cập nhật URL mà không tải lại trang
            window.history.pushState({}, "", url);

            // Emit sự kiện cho event bus
            this.$eventBus.$emit("triggerResult", {
              box_result: this.BoxResult,
              domainArray,
            });
          }
        },

        // Xử lý import file
        handleImportFile() {
          var file = $("#csv-file")[0].files[0];
          if (file) {
            const reader = new FileReader();

            if (
              file.type ==
              "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            ) {
              reader.onload = (event) => {
                var data = new Uint8Array(event.target.result);
                var workbook = XLSX.read(data, { type: "array" });
                var sheet = workbook.Sheets[workbook.SheetNames[0]];
                var csvData = XLSX.utils.sheet_to_csv(sheet, {
                  blankrows: false,
                });

                // Cập nhật giá trị để Vue tự động nhận diện
                this.InputDomain = csvData.split("\n").join("\n");
              };
              reader.readAsArrayBuffer(file);
            } else {
              reader.onload = (e) => {
                this.InputDomain = e.target.result.split("\n").join("\n");
              };
              reader.readAsText(file);
            }

            $(".closePopup").click();
          } else {
            $("#name-file").html(
              '<small class="text-danger">(*) Vui lòng nhập file</small>'
            );
          }
        },

        // Xử lý submit form extention
        handleSubmitExtention(e) {
          e.preventDefault();
          let listDomainChecked = $(
            "#vnx-popup-extention #result-tld input.custom-checkbox:checked"
          )
            .map(function () {
              return $(this).val();
            })
            .get();
          this.ListDomainChecked = listDomainChecked;
          $(".extension").addClass("hidden").hide().fadeIn(300);
        },

        // Xử lý xóa input domain
        removeInput(e) {
          e.preventDefault();
          this.InputDomain = "";
        },
        //Hiện popup import file
        showPopupImportFile(e) {
          $("#vnx-popup-import-file").removeClass("hidden");
        },

        //Hiện popup chọn tên miền mở rộng
        showPopupExtension(e) {
          // Lấy danh sách domain đã chọn
          const listDomainCookies = JSON.parse(
            getCookieKey(this.NameCookiesCheckDomain) || "[]"
          );

          // Đánh dấu các domain đã chọn trước đó
          $(
            "#vnx-popup-extention input[name^='nameExtention'][name$=']']"
          ).each(function () {
            const value = $(this).val();
            $(this).prop("checked", listDomainCookies.includes(value)); // Sử dụng biến thay vì this.ListDomainCookies
          });

          $("#vnx-popup-extention").removeClass("hidden");
        },

        //Kiểm tra tên miền
        checkDomain(arrayDomain) {
          const pattern = /^[a-zA-Z0-9-\.]+$/;

          return arrayDomain.every((domain) => {
            if (!pattern.test(domain)) {
              showNoticeMessage("error", "Tên miền không hợp lệ.");
              return false;
            }
            return true;
          });
        },

        //Hiện tooltip chọn tên miền mở rộng
        showTooltipDomain() {
          $(".vnx-tooltip-extention").removeClass("hidden");

          setTimeout(() => {
            $(".vnx-tooltip-extention").addClass("hidden");
          }, 3000);
        },

        // Lấy input domain từ url params
        getInputDomainFromUrlParams() {
          try {
            let urlParams = new URLSearchParams(window.location.search);
            let domainParam = urlParams.get("domain");

            if (!domainParam) return "";

            return JSON.parse(domainParam);
          } catch (error) {
            console.error("Lỗi khi parse URL params:", error);
            return "";
          }
        },
        // Lấy danh sách domain extention đã chọn trong cookies
        getDomainCheckedCookies() {
          return JSON.parse(getCookieKey(this.NameCookiesCheckDomain) || "[]");
        },

        //xoá domain khỏi danh sách
        deleteDomainChecked(index) {
          this.ListDomainChecked.splice(index, 1);
        },

        getIPsForNameServers(nameServersValue) {
          const promises = nameServersValue.map((ns) =>
            fetch(`https://dns.google/resolve?name=${ns}&type=A`)
              .then((res) => res.json())
              .then((data) => {
                if (data.Answer && data.Answer.length > 0) {
                  return data.Answer.find((ans) => ans.type === 1)?.data;
                }
                return null;
              })
          );

          return Promise.all(promises).then((results) =>
            results.filter((ip) => ip)
          );
        },
      },
    });
  });

//function dùng cho search domain redirect
document
  .querySelectorAll(
    ".brxe-vnx-search-domain-form-v2.form-search-domain-redirect"
  )
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixin],
      data: {
        DomainLoading: false,
        domain: "",
        PopupTld: false,
        ListDomainChecked: [],
      },
      created() {
        //check xem đã có cookie chứa list tld chưa
        this.ListDomainChecked = getCookieTldList("vnx_listpropose_tld_one_page");
        // Cập nhật số lượng hiển thị
        $(".vnx-tag-filter").text(this.ListDomainChecked.length);
      },
      mounted() {
        this.checkSelectedCheckboxes();
        this.onLoadSearchRedirect();
        this.$eventBus.$on("triggerActiveBtn", (data) => {
          this.DomainLoading = data.DomainLoading;
        });
      },
      methods: {
        async clickSearchButtonRedirect(e) {
          e.preventDefault();

          if (!this.domain.trim()) {
            showNoticeMessage("error", "Vui lòng nhập tên miền cần tìm kiếm.");
            return;
          }

          this.domain = this.domain.toLowerCase().trim();

          // Xử lý trường hợp domain có dấu "." ở cuối
          if (this.domain.endsWith(".")) {
            this.domain = this.domain.slice(0, -1);
          }

          // Format domain: loại bỏ khoảng trắng và viết thường
          this.domain = this.domain.toLowerCase().replace(/\s+/g, "");

          // Kiểm tra SLD có dấu
          let sld = this.domain.split(".")[0];
          let tld = this.domain.split(".")[1];
          // Gọi AJAX để lấy validation rules và check domain
          try {
            const validationResponse = await $.ajax({
              url: admin_ajax_url,
              type: 'POST',
              contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
              dataType: 'json',
              data: {
                action: 'validate_single_domain_center',
                domain: sld
              }
            });

            if (validationResponse.success === false) {
              showNoticeMessage("error", validationResponse.data.message || 'Tên miền không hợp lệ');
              return;
            }
          } catch (error) {
            console.error('Error checking validation rules:', error);
          }

          if (sld) {
            // Kiểm tra các ký tự có dấu trong SLD
            const vietnameseChars =
              /[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i;
            // Regex cho ký tự đặc biệt (không bao gồm tiếng Việt)
            const specialChars =
              /[^a-zA-Z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ-]/;

            // Kiểm tra xem domain có chứa .vn không
            const hasVnTld = this.domain.includes(".vn");

            if (hasVnTld == false) {
              // Domain không phải .vn: không được có dấu tiếng Việt và ký tự đặc biệt
              if (vietnameseChars.test(sld)) {
                e.preventDefault();
                showNoticeMessage(
                  "error",
                  "Tên miền không được chứa ký tự có dấu."
                );
                return;
              }

              if (specialChars.test(sld)) {
                e.preventDefault();
                showNoticeMessage(
                  "error",
                  "Tên miền chỉ được chứa chữ cái, số và dấu gạch ngang."
                );
                return;
              }
            } else if (hasVnTld == true) {
              // Domain .vn: có thể có dấu tiếng Việt nhưng không được có ký tự đặc biệt
              if (specialChars.test(sld)) {
                e.preventDefault();
                showNoticeMessage(
                  "error",
                  "Tên miền .vn chỉ được chứa chữ cái, số, dấu gạch ngang và ký tự có dấu tiếng Việt."
                );
                return;
              }
            }

            // Kiểm tra ký tự đầu và cuối
            if (sld.startsWith("-") || sld.endsWith("-")) {
              e.preventDefault();
              showNoticeMessage(
                "error",
                "Tên miền không được bắt đầu hoặc kết thúc bằng dấu gạch ngang."
              );
              return;
            }

            // Kiểm tra độ dài SLD
            if (sld.length < 2 || sld.length > 63) {
              e.preventDefault();
              showNoticeMessage(
                "error",
                "Tên miền phải có độ dài từ 2-63 ký tự."
              );
              return;
            }
          }

          // Kiểm tra TLD hợp lệ
          // Lọc tên miền để lấy TLD
          let domainParts = this.domain.split(".");
          let domainTld = domainParts.length > 1 ? domainParts.slice(1).join(".") : "";
          let ListDomainSuggest = sessionStorage.getItem("Data_TLD_Sussgest");
          if (domainTld) {
            ListDomainSuggest = JSON.parse(ListDomainSuggest);
            if (!ListDomainSuggest.includes(domainTld)) {
              e.preventDefault();
              showNoticeMessage("error", "Tên miền không hợp lệ.");
              return;
            }
          }

          this.domain = this.domain.toLowerCase().trim();
          this.PopupTld = false;
          if ($(this.$el).find("form").hasClass("redirect")) {
            this.DomainLoading = true;
            $(this.$el).find("form").submit();
          } else {
            this.DomainLoading = true;
            this.IdBoxResult = $(this.$el).find("form").data("result");
            setTimeout(() => {
              this.$eventBus.$emit("triggerResult", {
                box_result: this.IdBoxResult,
                domainInput: this.domain,
              });
            }, 100);
          }
        },

        // Function để check các checkbox sau khi DOM cập nhật
        checkSelectedCheckboxes() {
          this.$nextTick(() => {
            this.ListDomainChecked.forEach((item) => {
              const id = `vnx_checkbox_tld${item.replace(/\./g, "_")}`;
              const checkbox = $(`#${id}`);
              if (checkbox.length) {
                checkbox.addClass("active");
                checkbox.prop("checked", true);
              }
            });

            // Cập nhật trạng thái của checkbox "Select All"
            const totalCheckboxes = $(".vnx_checkbox_tld").length;
            const checkedCheckboxes = this.ListDomainChecked.length;
            const selectAllCheckbox = $("#vnx_checkbox_all");

            if (totalCheckboxes === checkedCheckboxes) {
              selectAllCheckbox.prop("checked", true);
            } else {
              selectAllCheckbox.prop("checked", false);
            }
          });
        },

        showPopupTld() {
          this.PopupTld = true;
          this.$nextTick(() => {
            setTimeout(() => {
              let $popup = $(this.$el).find(".vnx_wrapper_list_tld");
              if ($popup.length > 0 && !$popup.hasClass("show")) {
                $popup.addClass("show");
              } else {
                $popup.removeClass("show");
              }
            }, 10);
          });
          if (window.innerWidth <= 768) {
            document.body.style.overflow = "hidden";
          }
          this.checkSelectedCheckboxes();
        },

        hidePopupTld(e) {
          e.preventDefault();
          $(".vnx_wrapper_list_tld").removeClass("show");
          setTimeout(() => {
            this.PopupTld = false;
          }, 250);
          if (window.innerWidth <= 768) {
            document.body.style.overflow = "auto";
          }
        },

        clickCheckboxTld(e) {
          const checkbox = $(e.target);
          const value = checkbox.attr("value");
          //khi click vào checkbox thì thêm vào mảng ListDomainChecked nếu đã có trước đó thì xóa khỏi mảng còn không có thì thêm vào
          let index = this.ListDomainChecked.findIndex(
            (item) => item === value
          );
          if (index > -1) {
            this.ListDomainChecked.splice(index, 1);
            checkbox.removeClass("active");
            checkbox.prop("checked", false);
          } else {
            this.ListDomainChecked.push(value);
            checkbox.addClass("active");
            checkbox.prop("checked", true);
          }
          // Lưu TLD list vào cookie (chỉ lưu string TLD, không normalize)
          setCookieTldList("vnx_listpropose_tld_one_page", this.ListDomainChecked, 30);
          // Cập nhật số lượng hiển thị
          $(".vnx-tag-filter").text(this.ListDomainChecked.length);
        },

        removeAllCheckbox(e) {
          e.preventDefault();
          this.ListDomainChecked = [];
          setCookieTldList("vnx_listpropose_tld_one_page", [], 30);
          $(".vnx-tag-filter").text(this.ListDomainChecked.length);
          // Bỏ check tất cả checkbox
          $(".vnx_checkbox_tld").removeClass("active").prop("checked", false);
          $(".vnx_checkbox_all").removeClass("active").prop("checked", false);
        },

        selectAllCheckbox(e) {
          const isChecked = $(e.target).prop("checked");
          if (isChecked) {
            // Lấy tất cả giá trị từ các checkbox
            this.ListDomainChecked = $(".vnx_checkbox_tld")
              .map(function () {
                return $(this).attr("value");
              })
              .get();
            // Check tất cả checkbox
            $(".vnx_checkbox_tld").addClass("active").prop("checked", true);
          } else {
            // Nếu bỏ check all thì xóa hết
            this.removeAllCheckbox(e);
            setTimeout(() => {
              $(".vnx_checkbox_all").prop("checked", false);
            }, 100);
          }
          setCookieTldList("vnx_listpropose_tld_one_page", this.ListDomainChecked, 30);
          $(".vnx-tag-filter").text(this.ListDomainChecked.length);
        },

        onLoadSearchRedirect() {
          const urlParams = new URLSearchParams(window.location.search);
          const domainParam = urlParams.get("domain");
          if (domainParam) {
            this.domain = domainParam;
            let domainInput = $(this.$el).find("input[name='domain']");
            domainInput.val(domainParam); // Gán vào data của Vue
            this.clickSearchButtonRedirect({ preventDefault: () => { } });
          }
        },
      },
    });
  });

//function dùng cho form-search-domain-ai
document
  .querySelectorAll(".brxe-vnx-search-domain-form-v2.form-search-domain-ai")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixin],
      data: {
        domainquery: "",
        DomainLoading: false,
        IdBoxResult: "",
      },
      mounted() {
        this.domainquery = window.domainquery;
        this.IdBoxResult = $(this.$el).find("form").data("result");
        let listDomainSuggest = $(this.$el)
          .find('input[name="get_domain_suggest_ai_center"]')
          .val();
        // Convert to array if it's a string
        if (
          typeof listDomainSuggest === "string" &&
          listDomainSuggest.length > 0
        ) {
          try {
            listDomainSuggest = JSON.parse(listDomainSuggest);
          } catch (e) {
            // If parsing fails, split by comma and trim
            listDomainSuggest = listDomainSuggest
              .split(",")
              .map((item) => item.trim())
              .filter((item) => item.length > 0);
          }
        } else if (!Array.isArray(listDomainSuggest)) {
          listDomainSuggest = [];
        }

        if (listDomainSuggest.length > 0) {
          setTimeout(() => {
            this.$eventBus.$emit("triggerResultAi", {
              idBoxResult: this.IdBoxResult,
              domainArray: listDomainSuggest,
            });
          }, 100);
        }

        // Tự động kích hoạt tìm kiếm AI nếu trang vừa load có query nhưng chưa có kết quả
        // Trì hoãn để widget hiển thị kết quả (vnx_box_result.js) kịp mount và đăng ký
        // lắng nghe "triggerLoadingAi"/"triggerOffResultEmptyAi" trước khi các event này
        // được emit — nếu không, khung kết quả sẽ bỏ lỡ tín hiệu loading (cùng lý do
        // "triggerResultAi" ở trên cũng được trì hoãn 100ms).
        if (this.domainquery && listDomainSuggest.length === 0) {
          setTimeout(() => {
            this.clickSearchButtonAi({ preventDefault: () => { } });
          }, 100);
        }

        this.$eventBus.$on("triggerLoadingAiBtn", (data) => {
          this.DomainLoading = data.DomainLoading;
        });

        // Chỉ tắt loading nút bấm khi toàn bộ quá trình kiểm tra tên miền hoàn tất
        this.$eventBus.$on("completeGetDomain", ({ success }) => {
          this.DomainLoading = false;
        });
      },
      methods: {
        clickSearchButtonAi(e) {
          // Luôn chặn hành vi submit mặc định trước tiên: nếu bất kỳ thao tác nào
          // bên dưới (vd. lỗi $ chưa sẵn sàng) throw exception, form vẫn không bị
          // submit thật (tránh reload trang kèm mất kết quả).
          e.preventDefault();
          // Chặn gọi trùng khi đang có request AI chạy dở (double-click, auto-retry)
          if (this.DomainLoading) {
            return;
          }
          let domainInput = this.domainquery ? this.domainquery.trim() : "";
          // cần kiểm tra input hơn 20 kí tự sẽ chuyển trang không thì hiện thông báo
          if (!domainInput || domainInput.length < 20) {
            showNoticeMessage(
              "error",
              "Vui lòng nhập mô tả cần tìm kiếm và ít nhất 20 kí tự."
            );
            return;
          } else if (!/[a-zA-Z0-9]/.test(domainInput)) {
            showNoticeMessage("error", "Vui lòng nhập chữ cái và số.");
            return;
          } else {
            try {
              // Nếu là form redirect, cho phép submit ngay để chuyển trang nhanh
              if ($(this.$el).find("form").hasClass("redirect")) {
                this.DomainLoading = true;
                $(this.$el).find("form.redirect").submit();
                return; // Để form tự submit
              }
            } catch (err) {
              this.DomainLoading = false;
              showNoticeMessage("error", "Đã có lỗi xảy ra, vui lòng thử lại.");
              return;
            }

            this.DomainLoading = true;
            this.$eventBus.$emit("triggerLoadingAi", {
              IsLoading: true,
            });
            this.$eventBus.$emit("triggerOffResultEmptyAi", {
              ResultNewEmpty: false,
            });
            // Gửi AJAX
            try {
              $.ajax({
                url: admin_ajax_url,
                type: "POST",
                dataType: "json",
                data: {
                  action: "get_domain_suggest_ai_center",
                  query: domainInput,
                },
                success: (response) => {
                  // Check if response is successful
                  if (!response.success) {
                    this.DomainLoading = false;
                    this.$eventBus.$emit("triggerLoadingAi", {
                      IsLoading: false,
                    });
                    // Trigger error state in result component
                    this.$eventBus.$emit("triggerResultAi", {
                      idBoxResult: this.IdBoxResult,
                      domainArray: "error",
                      typeSearch: "ai",
                    });
                    return;
                  }

                  // Check if response.data exists and has suggestions_ai
                  if (!response.data || !response.data.suggestions_ai) {
                    this.DomainLoading = false;
                    this.$eventBus.$emit("triggerLoadingAi", {
                      IsLoading: false,
                    });
                    // Trigger error state in result component
                    this.$eventBus.$emit("triggerResultAi", {
                      idBoxResult: this.IdBoxResult,
                      domainArray: "error",
                      typeSearch: "ai",
                    });
                    return;
                  }

                  if ($(this.$el).find("form").hasClass("redirect")) {
                    //form search domain ai redirect
                    const cookieValue = Array.isArray(
                      response.data.suggestions_ai
                    )
                      ? JSON.stringify(response.data.suggestions_ai)
                      : "[]";
                    setCookieDomainCart("vnx_suggest_ai_domain", cookieValue, 1);
                    this.DomainLoading = false;
                    $(this.$el)
                      .find('input[name="get_domain_suggest_ai_center"]')
                      .val(response.data.suggestions_ai);
                    $(this.$el).find("form.redirect").submit();
                  } else {
                    //form search domain ai onpage
                    $(this.$el)
                      .find('input[name="get_domain_suggest_ai_center"]')
                      .val(response.data.suggestions_ai);
                    this.IdBoxResult = $(this.$el).find("form").data("result");
                    setTimeout(() => {
                      this.$eventBus.$emit("triggerResultAi", {
                        idBoxResult: this.IdBoxResult,
                        domainInput: this.domainquery,
                        domainArray: response.data.suggestions_ai,
                        typeSearch: "ai",
                      });
                    }, 100);
                  }
                },
                error: (xhr, status, error) => {
                  this.DomainLoading = false;
                  this.$eventBus.$emit("triggerLoadingAi", {
                    IsLoading: false,
                  });

                  // Trigger error state in result component
                  this.$eventBus.$emit("triggerResultAi", {
                    idBoxResult: this.IdBoxResult,
                    domainArray: "error",
                    typeSearch: "ai",
                  });
                },
              });
            } catch (err) {
              // $ chưa sẵn sàng hoặc lỗi đồng bộ khác: đảm bảo không kẹt loading mãi mãi
              this.DomainLoading = false;
              this.$eventBus.$emit("triggerLoadingAi", { IsLoading: false });
              showNoticeMessage("error", "Đã có lỗi xảy ra, vui lòng thử lại.");
            }
          }
        },
      },
    });
  });

//function dùng cho form-search-whois-popup
document
  .querySelectorAll(".brxe-vnx-search-domain-form-v2.form-search-whois-popup")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixin],
      data: {
        domain: "",
        DomainLoading: false,
        PopupWhois: false,
        RegistryLock: false,
        CheckProtect: false,
        ProtectName: false,
        StatusResult: false,
        WhoisError: false,
        ProgressRAF: null,
        Whois: {
          domainName: "",
          creationDate: "",
          registrarExpirationDate: "",
          nameServers: "",
          nameServersIP: "",
          status: "",
          dnssec: "",
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

        clickSearchButtonWhoisPopup(e) {
          e.preventDefault();
          this.domain = this.domain.toLowerCase().trim();
          // Bỏ tiền tố "www." vì whois tra theo domain gốc, không phải subdomain
          this.domain = this.domain.replace(/^www\./i, "");
          if (this.domain.includes(".") == false) {
            showNoticeMessage("error", "Vui lòng nhập đúng tên miền.");
            return;
          }
          if (this.domain.length > 0 && isValidDomain(this.domain) == true) {
            this.PopupWhois = true;
            this.WhoisError = false;
            document.body.style.overflow = "hidden";
            this.DomainLoading = true;
            this.runProgressBar();
            vnx_post_ajax_whois("POST", "get_whois_domain_center", this.domain)
              .then((response) => {
                // API whois bên ngoài đôi khi trả text lỗi (vd "Error: whois
                // lookup failed") thay vì JSON, response.data khi đó là string
                // chứ không phải object { data: [...] } → không được coi là
                // "tên miền chưa đăng ký".
                if (
                  response.success == true &&
                  response.data &&
                  Array.isArray(response.data.data)
                ) {
                  const rawData = response.data.data;
                  // Tên miền chưa đăng ký: whois trả mảng rỗng (vd .com)
                  // hoặc 1 dòng thông báo "not found" (vd .vn)
                  const isNotRegistered =
                    rawData.length === 0 ||
                    rawData.some(
                      (item) =>
                        typeof item.value === "string" &&
                        /not found/i.test(item.value)
                    );
                  const data = this.setDataWhois(rawData);
                  this.StatusResult = !isNotRegistered;
                  this.Whois = data;
                  this.DomainLoading = false;
                } else {
                  this.DomainLoading = false;
                  this.StatusResult = false;
                  this.WhoisError = true;
                }
              })
              .catch(() => {
                this.DomainLoading = false;
                this.StatusResult = false;
                this.WhoisError = true;
                showNoticeMessage(
                  "error",
                  "Có lỗi xảy ra khi tìm kiếm thông tin tên miền."
                );
              });
          } else {
            showNoticeMessage("error", "Vui lòng nhập tên miền cần tìm kiếm.");
          }
        },

        runProgressBar() {
          let $progressBar = $(this.$el).find(
            ".vnc_loading_result .progress-bar"
          );
          let startTime = null;
          let duration = 1500;
          let isCompleted = false;

          const animateProgress = (timestamp) => {
            if (!startTime) startTime = timestamp;
            let elapsed = timestamp - startTime;
            let cycleProgress = (elapsed % duration) / duration;
            let barWidth = 150;
            let containerWidth = $progressBar.parent().width() || 300;
            let maxTranslate = containerWidth - barWidth;
            let translateX =
              cycleProgress * (maxTranslate + barWidth) - barWidth;
            translateX = Math.max(
              -barWidth,
              Math.min(maxTranslate, translateX)
            );

            $progressBar.css({
              width: barWidth + "px",
              transform: `translateX(${translateX}px)`,
              transition: "none",
            });

            if (!isCompleted) {
              this.ProgressRAF = requestAnimationFrame(animateProgress);
            }
          };

          this.ProgressRAF = requestAnimationFrame(animateProgress);
          this.progressBarState = { isCompleted: false };
        },

        setDataWhois(response) {
          // Group items by label to handle multiple items with same label
          const groupedData = response.reduce((acc, item) => {
            const key = item.label.toLowerCase().replace(/\s+/g, "");
            if (!acc[key]) {
              acc[key] = [];
            }
            acc[key].push(item.value);
            return acc;
          }, {});

          // Convert grouped data to final format
          const data = {};
          Object.keys(groupedData).forEach((key) => {
            if (groupedData[key].length === 1) {
              // Single value
              data[key] = groupedData[key][0];
              if (key === "registrantname") {
                this.ProtectName = true;
              }
            } else {
              // Multiple values - join with comma
              data[key] = groupedData[key].join(", ");
            }
          });

          const domainLink = "https://" + this.domain;
          // Reset RegistryLock before checking
          this.RegistryLock = false;
          return {
            domainName: data?.domainname || "",
            registrars: data?.registrar || "",
            registrarsName: data?.registrantname || "",
            creationDate: data?.creationdate || "",
            registrarExpirationDate:
              data?.registrarexpirationdate ||
              data?.registrarregistrationexpirationdate ||
              data?.registryexpirydate ||
              "",
            nameServers:
              this.checkNameServers(data?.nameservers)?.length > 0
                ? this.checkNameServers(data?.nameservers)
                : this.checkNameServers(data?.nameserver),
            status: this.checkStatus(data?.domainstatus),
            dnssec: this.checkDnssec(data?.dnssec),
            domainLink: domainLink,
            registryLock: this.RegistryLock,
          };
        },

        checkStatus(status) {
          let statusValue = [];
          if (status) {
            if (status.includes(",")) {
              statusValue = status
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
            } else {
              statusValue = [status.trim()];
            }
          }

          // Check Registry Lock - domain has all 3 required statuses
          const requiredStatuses = [
            "serverUpdateProhibited",
            "serverDeleteProhibited",
            "serverTransferProhibited",
          ];
          const hasAllRequiredStatuses = requiredStatuses.every((status) =>
            statusValue.includes(status)
          );

          if (hasAllRequiredStatuses) {
            this.RegistryLock = true;
          }

          return statusValue;
        },

        checkDnssec(dnssec) {
          // Xử lý logic dnssec theo yêu cầu
          let dnssecValue = "";
          if (!dnssec || dnssec.trim() === "") {
            // dnssecValue = "Chưa đăng ký";
            dnssecValue = "";
          } else {
            dnssecValue = dnssec;
          }
          return dnssecValue;
        },

        checkNameServers(nameServers) {
          // Xử lý name servers thành mảng
          let nameServersValue = [];
          if (nameServers) {
            if (nameServers.includes(",")) {
              nameServersValue = nameServers
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
            } else {
              nameServersValue = [nameServers.trim()];
            }
          }
          return nameServersValue;
        },

        clickHidePopupWhois(e) {
          e.preventDefault();
          this.PopupWhois = false;
          this.CheckProtect = false;
          this.ProtectName = false;
          this.RegistryLock = false;
          document.body.style.overflow = "auto";
        },

        clickShowElement() {
          this.CheckProtect = true;
        },

        handleClickOutside(event) {
          // Kiểm tra nếu popup đang mở
          if (this.PopupWhois) {
            const popupContent = $(this.$el).find(
              ".vnx_wrapper_popup_content"
            )[0];
            const clickedElement = event.target;

            // Nếu click ngoài popup content thì đóng popup
            if (popupContent && !popupContent.contains(clickedElement)) {
              this.PopupWhois = false;
              this.CheckProtect = false;
              this.ProtectName = false;
              this.RegistryLock = false;
              document.body.style.overflow = "auto";
            }
          }
        },
      },
    });
  });

//function dùng cho form-search-muti-domain
document
  .querySelectorAll(".brxe-vnx-search-domain-form-v2.form-search-muti-domain")
  .forEach((el) => {
    new Vue({
      el: `#${el.id}`,
      mixins: [DomainMixin],
      data() {
        return {
          domainInput: "",
          enteredDomains: [],
          selectedTlds: [],
          tldSearchQuery: "",
          popularTlds: (window.vnxPopularTlds || []).map((tld) => ({
            name: tld.startsWith(".") ? tld : "." + tld,
            checked: false, // hoặc true nếu muốn mặc định chọn
          })),
          allTlds: (window.vnxAllTlds || []).map((tld) => ({
            name: tld.startsWith(".") ? tld : "." + tld,
            checked: false,
          })),
          showMessStatus: false,
          messStatus: "",
          showInstructionsPopup: false,
          isLoading: false,
          redirectUrl: document.querySelector("input[name='redirect_url']").value,
          domainInputPost: "",
          tldInputPost: "",

        };
      },
      computed: {
        filteredAllTlds() {
          if (!this.tldSearchQuery) {
            return this.allTlds;
          }
          console.log("this.allTlds", this.allTlds);
          return this.allTlds.filter((tld) =>
            tld.name.toLowerCase().includes(this.tldSearchQuery.toLowerCase())
          );
        },
      },
      mounted() {
        this.$eventBus.$on("triggerLoadingStopBtn", (data) => {
          this.isLoading = data.isLoading;
        });

        if (localStorage.getItem('vnxDomaininput') && localStorage.getItem('vnxTldinput')) {
          //khi vừa vào page nếu có dữ liệu trong localStorage thì gán vào biến
          this.domainInputPost = localStorage.getItem('vnxDomaininput');
          this.tldInputPost = localStorage.getItem('vnxTldinput');
          if (this.domainInputPost) {
            this.enteredDomains = JSON.parse(this.domainInputPost);
          }
          if (this.tldInputPost) {
            this.selectedTlds = JSON.parse(this.tldInputPost);
          }
          setTimeout(() => {
            //xoá dữ liệu trong localStorage
            localStorage.removeItem('vnxDomaininput');
            localStorage.removeItem('vnxTldinput');
            this.searchDomains();
          }, 500);
        }

      },
      methods: {
        addDomain() {
          const domain = this.domainInput.trim();
          if (domain && this.enteredDomains.length < 300) {
            if (!this.enteredDomains.includes(domain)) {
              this.enteredDomains.push(domain);
            }
            this.domainInput = "";
          } else if (this.enteredDomains.length >= 300) {
            this.showMessage("Đã đạt giới hạn 300 tên miền. Không thể thêm thêm.", "error");
          }
        },
        removeDomain(index) {
          this.enteredDomains.splice(index, 1);
        },
        clearAllDomains() {
          this.enteredDomains = [];
        },
        handlePaste(event) {
          event.preventDefault();
          const pastedText = (
            event.clipboardData || window.clipboardData
          ).getData("text");
          const domains = pastedText
            .split(/[\n,]+/)
            .map((d) => d.trim())
            .filter((d) => d);

          if (this.enteredDomains.length >= 300) {
            this.showMessage("Đã đạt giới hạn 300 tên miền. Không thể thêm thêm.", "error");
            return;
          }

          const remainingSlots = 300 - this.enteredDomains.length;
          const domainsToAdd = domains.slice(0, remainingSlots);

          domainsToAdd.forEach((domain) => {
            if (
              domain &&
              !this.enteredDomains.includes(domain)
            ) {
              this.enteredDomains.push(domain);
            }
          });

          if (domains.length > remainingSlots) {
            this.showMessage(`Đã thêm ${domainsToAdd.length} tên miền từ clipboard. ${domains.length - remainingSlots} tên miền bị bỏ qua do giới hạn 300.`, "success");
          } else if (domainsToAdd.length > 0) {
            this.showMessage(`Đã thêm ${domainsToAdd.length} tên miền từ clipboard.`, "success");
          }
        },

        uploadFile() {
          // Kiểm tra giới hạn trước khi upload
          if (this.enteredDomains.length >= 300) {
            this.showMessage("Đã đạt giới hạn 300 tên miền. Không thể thêm thêm.", "error");
            return;
          }

          const input = document.createElement("input");
          input.type = "file";
          input.accept = ".xlsx,.xls,.csv";
          input.onchange = (event) => {
            const file = event.target.files[0];
            if (file) {
              // Validate file format
              const allowedExtensions = [".xlsx", ".xls", ".csv"];
              const fileName = file.name.toLowerCase();
              const isValidFormat = allowedExtensions.some((ext) =>
                fileName.endsWith(ext)
              );

              if (!isValidFormat) {
                this.showMessage(
                  "Tệp tải lên không đúng định dạng (.csv, .xlsx, .xls)", "error"
                );
                return;
              }

              // Xử lý file Excel
              if (fileName.endsWith('.xlsx') || fileName.endsWith('.xls')) {
                this.handleExcelFile(file);
              } else {
                // Xử lý file CSV
                this.handleCsvFile(file);
              }
            }
          };
          input.click();
        },

        handleExcelFile(file) {
          // Kiểm tra xem có thư viện SheetJS không
          if (typeof XLSX === 'undefined') {
            this.showMessage("Không thể đọc file Excel. Vui lòng chuyển sang file CSV.", "error");
            return;
          }

          const reader = new FileReader();
          reader.onload = (e) => {
            try {
              const data = new Uint8Array(e.target.result);
              const workbook = XLSX.read(data, { type: 'array' });
              const firstSheetName = workbook.SheetNames[0];
              const worksheet = workbook.Sheets[firstSheetName];
              const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

              // Lấy cột đầu tiên (giả sử domain ở cột đầu)
              const domains = jsonData
                .map(row => row[0])
                .filter(domain => domain && typeof domain === 'string')
                .map(domain => domain.trim())
                .filter(domain => domain)
                .slice(0, 300);

              this.processDomains(domains);
            } catch (error) {
              this.showMessage("Lỗi đọc file Excel. Vui lòng kiểm tra định dạng file.", "error");
            }
          };

          reader.onerror = () => {
            this.showMessage("Lỗi đọc file Excel. Vui lòng thử lại.", "error");
          };

          reader.readAsArrayBuffer(file);
        },

        handleCsvFile(file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            try {
              const content = e.target.result;
              const domains = content
                .split(/[\n,]+/)
                .map((d) => d.trim())
                .filter((d) => d)
                .slice(0, 300); // Chỉ lấy 300 dòng đầu tiên

              this.processDomains(domains);
            } catch (error) {
              this.showMessage("Lỗi đọc file CSV. Vui lòng kiểm tra định dạng file.", "error");
            }
          };

          reader.onerror = () => {
            this.showMessage("Lỗi đọc file CSV. Vui lòng thử lại.", "error");
          };

          reader.readAsText(file);
        },

        processDomains(domains) {
          if (domains.length === 0) {
            this.showMessage("File không chứa tên miền hợp lệ", "error");
            return;
          }

          let addedCount = 0;
          domains.forEach((domain) => {
            if (
              domain &&
              this.enteredDomains.length < 300 &&
              !this.enteredDomains.includes(domain)
            ) {
              this.enteredDomains.push(domain);
              addedCount++;
            }
          });

          if (addedCount > 0) {
            this.showMessage(`Đã thêm tên miền từ file`, "success");
          } else {
            this.showMessage("Không có tên miền mới nào được thêm", "error");
          }
        },

        showMessage(message, status) {
          this.showMessStatus = true;
          this.messStatus = message;
          //thêm class error vào messStatus
          $(this.$el).find(".vnx-message-display").addClass(status);
          setTimeout(() => {
            this.showMessStatus = false;
            $(this.$el).find(".vnx-message-display").removeClass(status);
          }, 3000);
        },

        clearAllTlds() {
          this.selectedTlds = [];
        },

        updateSelectedCount() {
          // This method is called when checkboxes change
          // The selectedTlds array is automatically updated by v-model
        },

        async searchDomains() {
          const idBoxResult = $(this.$el)
            .find(".form-search-multi-domain")
            .data("result");
          if (this.enteredDomains.length === 0) {
            this.showMessage(
              "Vui lòng nhập ít nhất 1 tên miền để tìm kiếm"
              , "error");
            return;
          }
          this.isLoading = true;

          // Validate domains trước khi xử lý
          if (!this.checkDomain(this.enteredDomains)) {
            return;
          }

          // Check validation rules với backend
          try {
            // Check each domain in enteredDomains
            for (let domain of this.enteredDomains) {
              const checkDomain = domain.includes('.') ? domain : `${domain}.com`;
              const validationResponse = await $.ajax({
                url: admin_ajax_url,
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json',
                data: {
                  action: 'validate_single_domain_center',
                  domain: checkDomain
                }
              });

              if (validationResponse.success === false) {
                this.showMessage(`Tên miền "${domain}": ${validationResponse.data.message}`, "error");
                this.isLoading = false;
                return;
              }
            }
          } catch (error) {
            console.error('Error checking validation rules:', error);
          }

          // Chuẩn hóa danh sách TLD hợp lệ và helper tìm TLD dài nhất khớp với domain
          const validTlds = (window.vnxAllTlds || []).map(tld =>
            tld.startsWith('.') ? tld.substring(1) : tld
          );
          const getLongestMatchingTld = (domain) => {
            const parts = domain.split('.');
            if (parts.length < 2) return null;
            for (let i = 1; i < parts.length; i += 1) {
              const candidate = parts.slice(i).join('.');
              if (validTlds.includes(candidate)) return candidate;
            }
            return null;
          };

          // Thu thập tất cả TLD từ input domains
          const tldsFromInputs = new Set();
          this.enteredDomains.forEach((domain) => {
            if (domain.includes(".")) {
              const matchedTld = getLongestMatchingTld(domain);
              if (matchedTld) tldsFromInputs.add(matchedTld);
            }
          });

          // Thu thập TLD từ filter (selectedTlds)
          const tldsFromFilter = new Set();
          this.selectedTlds.forEach((tld) => {
            const cleanTld = tld.startsWith(".") ? tld.substring(1) : tld;
            tldsFromFilter.add(cleanTld);
          });

          // Hợp nhất tất cả TLD cần thiết
          const allRequiredTlds = new Set([...tldsFromInputs, ...tldsFromFilter]);

          // Nếu không có TLD nào từ filter và có domain không có TLD, sử dụng TLD mặc định
          let tabValue = 6;
          if (this.selectedTlds.length === 0 && this.enteredDomains.some(domain => !domain.includes("."))) {
            const defaultTlds = window.vnxSuggestTld.split(",");
            defaultTlds.forEach(tld => {
              const cleanTld = tld.startsWith(".") ? tld.substring(1) : tld;
              allRequiredTlds.add(cleanTld);
            });
            tabValue = 0;
          }

          // Tạo mảng domain hoàn chỉnh với tất cả TLD cần thiết
          const arrayDomain = [];
          const finalTlds = Array.from(allRequiredTlds);

          this.enteredDomains.forEach((domain) => {
            if (domain.includes(".")) {
              // Domain đã có TLD - thêm domain gốc
              arrayDomain.push(domain);

              // Tách SLD và tạo domain với các TLD khác từ filter
              const domainParts = domain.split('.');
              if (domainParts.length >= 2) {
                const matchedTld = getLongestMatchingTld(domain);
                const tldLabels = matchedTld ? matchedTld.split('.') : [];
                const sld = matchedTld
                  ? domainParts.slice(0, domainParts.length - tldLabels.length).join('.')
                  : domainParts[0];
                const originalTld = matchedTld || domainParts[domainParts.length - 1];

                // Thêm domain với các TLD khác từ filter (trừ TLD gốc)
                finalTlds.forEach((tld) => {
                  if (tld !== originalTld && sld) {
                    arrayDomain.push(`${sld}.${tld}`);
                  }
                });
              }
            } else {
              // Domain chưa có TLD - tạo với tất cả TLD cần thiết
              finalTlds.forEach((tld) => {
                arrayDomain.push(`${domain}.${tld}`);
              });
            }
          });

          // Cập nhật selectedTlds với tất cả TLD cần thiết (để gửi lên API)
          this.selectedTlds = finalTlds.map(tld => `.${tld}`);
          const searchData = {
            domains: this.enteredDomains,
            tlds: this.selectedTlds,
            arrayDomain: arrayDomain,
            tab: tabValue,
          };

          if (this.$el.querySelector(".form-search-multi-domain").classList.contains("redirect")) {
            this.handleFormSubmit();
          } else {
            // Emit event để trigger search
            this.$eventBus.$emit("triggerResult", {
              box_result: idBoxResult,
              domainArray: arrayDomain,
              tlds: this.selectedTlds,
              tab: tabValue,
            });
          }
        },

        //Kiểm tra tên miền
        checkDomain(arrayDomain) {
          const pattern = /^[a-zA-Z0-9-\.]+$/;
          const validTlds = (window.vnxAllTlds || []).map(tld =>
            tld.startsWith('.') ? tld.substring(1) : tld
          );
          const getLongestMatchingTld = (domain) => {
            const parts = domain.split('.');
            if (parts.length < 2) return null;
            for (let i = 1; i < parts.length; i += 1) {
              const candidate = parts.slice(i).join('.');
              if (validTlds.includes(candidate)) return candidate;
            }
            return null;
          };
          return arrayDomain.every((domain) => {
            const domainParts = domain.split('.');
            if (!pattern.test(domain)) {
              showNoticeMessage("error", "Tên miền \"" + domain + "\" không hợp lệ.");
              this.isLoading = false;
              return false;
            } else {
              if (domainParts.length > 1) {
                const matchedTld = getLongestMatchingTld(domain);
                if (!matchedTld) {
                  const tld = domainParts.slice(1).join('.');
                  showNoticeMessage("error", "TLD \"" + tld + "\" không hợp lệ.");
                  this.isLoading = false;
                  return false;
                }
              }
            }
            return true;
          });
        },

        resetForm() {
          this.domainInput = "";
          this.enteredDomains = [];
          this.selectedTlds = [];
          this.tldSearchQuery = "";
        },

        // tôi cần 1 function khi click vào btn hướng dẫn .instructions-link sẽ hiển thị 1 popup như hình

        clickShowInstructionsPopup() {
          this.showInstructionsPopup = true;
        },

        clickCloseInstructionsPopup() {
          this.showInstructionsPopup = false;
        },

        handleFormSubmit() {
          // Tạo biến global
          localStorage.setItem('vnxDomaininput', JSON.stringify(this.enteredDomains));
          localStorage.setItem('vnxTldinput', JSON.stringify(this.selectedTlds));
          // Redirect sang trang khác
          window.location.href = this.redirectUrl;
        },

      },
    });
  });


document.querySelectorAll(".brxe-vnx-search-domain-form-v2.form-search-muti-domain-mini").forEach((el) => {
  new Vue({
    el: `#${el.id}`,
    mixins: [DomainMixin],
    data: {
      DomainLoading: false,
      domain: "",
      enteredDomains: [],
      PopupTld: false,
      ListDomainChecked: [],
      isLoading: false,
      redirectUrl: document.querySelector("input[name='redirect_url']").value,
      showMessStatus: false,
      messStatus: "",
    },
    created() {
      //check xem đã có cookie chứa list tld chưa
      if (!getCookieKey("vnx_listpropose_tld")) {
        this.ListDomainChecked = [];
      } else {
        this.ListDomainChecked = JSON.parse(
          JSON.stringify(getCookiesDomainCart("vnx_listpropose_tld"))
        );
      }
      // Cập nhật số lượng hiển thị
      $(".vnx-tag-filter").text(this.ListDomainChecked.length);
    },
    mounted() {
      this.checkSelectedCheckboxes();
      this.$eventBus.$on("triggerActiveBtn", (data) => {
        this.DomainLoading = data.DomainLoading;
      });
    },
    methods: {
      addDomain() {
        const domain = this.domain.trim();
        if (domain && this.enteredDomains.length < 300) {
          if (!this.enteredDomains.includes(domain)) {
            this.enteredDomains.push(domain);
          }
          this.domain = "";
        } else if (this.enteredDomains.length >= 300) {
          this.showMessage("Đã đạt giới hạn 300 tên miền. Không thể thêm thêm.", "error");
        }
      },
      removeDomain(index) {
        this.enteredDomains.splice(index, 1);
      },

      // Function để check các checkbox sau khi DOM cập nhật
      checkSelectedCheckboxes() {
        this.$nextTick(() => {
          this.ListDomainChecked.forEach((item) => {
            const id = `vnx_checkbox_tld${item.replace(/\./g, "_")}`;
            const checkbox = $(`#${id}`);
            if (checkbox.length) {
              checkbox.addClass("active");
              checkbox.prop("checked", true);
            }
          });

          // Cập nhật trạng thái của checkbox "Select All"
          const totalCheckboxes = $(".vnx_checkbox_tld").length;
          const checkedCheckboxes = this.ListDomainChecked.length;
          const selectAllCheckbox = $("#vnx_checkbox_all");

          if (totalCheckboxes === checkedCheckboxes) {
            selectAllCheckbox.prop("checked", true);
          } else {
            selectAllCheckbox.prop("checked", false);
          }
        });
      },

      showPopupTld() {
        this.$nextTick(() => {
          setTimeout(() => {
            let $popup = $(this.$el).find(".vnx_wrapper_list_tld");
            if ($popup.length > 0 && !$popup.hasClass("show")) {
              $popup.addClass("show");
              this.PopupTld = true;
            } else {
              $popup.removeClass("show");
              this.PopupTld = false;
            }
          }, 10);
        });
        if (window.innerWidth <= 768) {
          document.body.style.overflow = "hidden";
        }
        this.checkSelectedCheckboxes();
      },

      hidePopupTld(e) {
        e.preventDefault();
        $(".vnx_wrapper_list_tld").removeClass("show");
        setTimeout(() => {
          this.PopupTld = false;
        }, 250);
        if (window.innerWidth <= 768) {
          document.body.style.overflow = "auto";
        }
      },

      showMessage(message, status) {
        this.showMessStatus = true;
        this.messStatus = message;
        //thêm class error vào messStatus
        $(this.$el).find(".vnx-message-display").addClass(status);
        setTimeout(() => {
          this.showMessStatus = false;
          $(this.$el).find(".vnx-message-display").removeClass(status);
        }, 3000);
      },

      clickCheckboxTld(e) {
        const checkbox = $(e.target);
        const value = checkbox.attr("value");
        //khi click vào checkbox thì thêm vào mảng ListDomainChecked nếu đã có trước đó thì xóa khỏi mảng còn không có thì thêm vào
        let index = this.ListDomainChecked.findIndex(
          (item) => item === value
        );
        if (index > -1) {
          this.ListDomainChecked.splice(index, 1);
          checkbox.removeClass("active");
          checkbox.prop("checked", false);
        } else {
          this.ListDomainChecked.push(value);
          checkbox.addClass("active");
          checkbox.prop("checked", true);
        }
        // Đảm bảo ListDomainChecked là một mảng trước khi lưu vào cookie
        const cookieValue = Array.isArray(this.ListDomainChecked)
          ? JSON.stringify(this.ListDomainChecked)
          : "[]";
        setCookieDomainCart("vnx_listpropose_tld", cookieValue, 30);
        // Cập nhật số lượng hiển thị
        $(".vnx-tag-filter").text(this.ListDomainChecked.length);
      },

      removeAllCheckbox(e) {
        e.preventDefault();
        this.ListDomainChecked = [];
        setCookieDomainCart("vnx_listpropose_tld", "[]", 30);
        $(".vnx-tag-filter").text(this.ListDomainChecked.length);
        // Bỏ check tất cả checkbox
        $(".vnx_checkbox_tld").removeClass("active").prop("checked", false);
        $(".vnx_checkbox_all").removeClass("active").prop("checked", false);
      },

      selectAllCheckbox(e) {
        const isChecked = $(e.target).prop("checked");
        if (isChecked) {
          // Lấy tất cả giá trị từ các checkbox
          this.ListDomainChecked = $(".vnx_checkbox_tld")
            .map(function () {
              return $(this).attr("value");
            })
            .get();
          // Check tất cả checkbox
          $(".vnx_checkbox_tld").addClass("active").prop("checked", true);
        } else {
          // Nếu bỏ check all thì xóa hết
          this.removeAllCheckbox(e);
          setTimeout(() => {
            $(".vnx_checkbox_all").prop("checked", false);
          }, 100);
        }
        const cookieValue = Array.isArray(this.ListDomainChecked)
          ? JSON.stringify(this.ListDomainChecked)
          : "[]";
        setCookieDomainCart("vnx_listpropose_tld", cookieValue, 30);
        $(".vnx-tag-filter").text(this.ListDomainChecked.length);
      },

      async searchDomains() {
        const idBoxResult = $(this.$el)
          .find(".form-search-multi-domain-mini")
          .data("result");
        if (this.enteredDomains.length === 0) {
          this.showMessage(
            "Vui lòng nhập ít nhất 1 tên miền để tìm kiếm"
            , "error");
          return;
        }
        this.isLoading = true;

        // Validate domains trước khi xử lý
        if (!this.checkDomain(this.enteredDomains)) {
          return;
        }

        // Check validation rules với backend
        try {
          // Check each domain in enteredDomains
          for (let domain of this.enteredDomains) {
            const checkDomain = domain.includes('.') ? domain : `${domain}.com`;
            const validationResponse = await $.ajax({
              url: admin_ajax_url,
              type: 'POST',
              contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
              dataType: 'json',
              data: {
                action: 'validate_single_domain_center',
                domain: checkDomain
              }
            });

            if (validationResponse.success === false) {
              this.showMessage(`Tên miền "${domain}": ${validationResponse.data.message}`, "error");
              this.isLoading = false;
              return;
            }
          }
        } catch (error) {
          console.error('Error checking validation rules:', error);
        }

        // Chuẩn hóa danh sách TLD hợp lệ và helper tìm TLD dài nhất khớp với domain
        const validTlds = (window.vnxAllTlds || []).map(tld =>
          tld.startsWith('.') ? tld.substring(1) : tld
        );
        const getLongestMatchingTld = (domain) => {
          const parts = domain.split('.');
          if (parts.length < 2) return null;
          for (let i = 1; i < parts.length; i += 1) {
            const candidate = parts.slice(i).join('.');
            if (validTlds.includes(candidate)) return candidate;
          }
          return null;
        };

        // Thu thập tất cả TLD từ input domains
        const tldsFromInputs = new Set();
        this.enteredDomains.forEach((domain) => {
          if (domain.includes(".")) {
            const matchedTld = getLongestMatchingTld(domain);
            if (matchedTld) tldsFromInputs.add(matchedTld);
          }
        });

        // Thu thập TLD từ filter (ListDomainChecked)
        const tldsFromFilter = new Set();
        this.ListDomainChecked.forEach((tld) => {
          const cleanTld = tld.startsWith(".") ? tld.substring(1) : tld;
          tldsFromFilter.add(cleanTld);
        });

        // Hợp nhất tất cả TLD cần thiết
        const allRequiredTlds = new Set([...tldsFromInputs, ...tldsFromFilter]);
        // Nếu không có TLD nào từ filter và có domain không có TLD, sử dụng TLD mặc định
        let tabValue = 6;
        if (this.ListDomainChecked.length === 0 && this.enteredDomains.some(domain => !domain.includes("."))) {
          const defaultTlds = window.vnxSuggestTld.split(",");
          defaultTlds.forEach(tld => {
            const cleanTld = tld.startsWith(".") ? tld.substring(1) : tld;
            allRequiredTlds.add(cleanTld);
          });
          tabValue = 0;
        }

        // Tạo mảng domain hoàn chỉnh với tất cả TLD cần thiết
        const arrayDomain = [];
        const finalTlds = Array.from(allRequiredTlds);

        this.enteredDomains.forEach((domain) => {
          if (domain.includes(".")) {
            // Domain đã có TLD - thêm domain gốc
            arrayDomain.push(domain);
            // Tách SLD và tạo domain với các TLD khác từ filter
            const domainParts = domain.split('.');
            if (domainParts.length >= 2) {
              const matchedTld = getLongestMatchingTld(domain);
              const tldLabels = matchedTld ? matchedTld.split('.') : [];
              const sld = matchedTld
                ? domainParts.slice(0, domainParts.length - tldLabels.length).join('.')
                : domainParts[0];
              const originalTld = matchedTld || domainParts[domainParts.length - 1];

              // Thêm domain với các TLD khác từ filter (trừ TLD gốc)
              finalTlds.forEach((tld) => {
                if (tld !== originalTld && sld) {
                  arrayDomain.push(`${sld}.${tld}`);
                }
              });
            }
          } else {
            // Domain chưa có TLD - tạo với tất cả TLD cần thiết
            finalTlds.forEach((tld) => {
              arrayDomain.push(`${domain}.${tld}`);
            });
          }
        });
        // Cập nhật ListDomainChecked với tất cả TLD cần thiết (để gửi lên API)
        this.ListDomainChecked = finalTlds.map(tld => `.${tld}`);

        const formElement = this.$el.querySelector(".vnx-content-form");
        if (formElement && formElement.classList.contains("redirect")) {
          this.handleFormSubmit();
        } else {
          // Emit event để trigger search
          this.$eventBus.$emit("triggerResult", {
            box_result: idBoxResult,
            domainArray: arrayDomain,
            tlds: this.ListDomainChecked,
            tab: tabValue,
          });
        }
      },

      //Kiểm tra tên miền
      checkDomain(arrayDomain) {
        // console.log(window.vnxAllTlds, "window.vnxAllTlds");
        const pattern = /^[a-zA-Z0-9-\.]+$/;
        const validTlds = (window.vnxAllTlds || []).map(tld =>
          tld.startsWith('.') ? tld.substring(1) : tld
        );
        const getLongestMatchingTld = (domain) => {
          const parts = domain.split('.');
          if (parts.length < 2) return null;
          for (let i = 1; i < parts.length; i += 1) {
            const candidate = parts.slice(i).join('.');
            if (validTlds.includes(candidate)) return candidate;
          }
          return null;
        };
        return arrayDomain.every((domain) => {
          const domainParts = domain.split('.');
          if (!pattern.test(domain)) {
            this.showMessage(
              "Tên miền \"" + domain + "\" không hợp lệ."
              , "error");
            this.isLoading = false;
            return false;
          } else {
            if (domainParts.length > 1) {
              const matchedTld = getLongestMatchingTld(domain);
              if (!matchedTld) {
                const tld = domainParts.slice(1).join('.');
                this.showMessage(
                  "TLD \"" + tld + "\" không hợp lệ."
                  , "error");
                this.isLoading = false;
                return false;
              }
            }
          }
          return true;
        });
      },

      handleFormSubmit() {
        // Tạo biến global
        localStorage.setItem('vnxDomaininput', JSON.stringify(this.enteredDomains));
        localStorage.setItem('vnxTldinput', JSON.stringify(this.ListDomainChecked));
        // Redirect sang trang khác
        window.location.href = this.redirectUrl;
      },


    },
  });
});


