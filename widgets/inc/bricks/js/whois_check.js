window.jQuery = window.$ = jQuery;

class VNX_WHOIS {
    // Make SingleTon to get data form it VNX_WHOIS
    static Ins() {
        if (!VNX_WHOIS.instance) {
            VNX_WHOIS.instance = new VNX_WHOIS();
        }
        return VNX_WHOIS.instance;
    }
    constructor() {
        this.checkTimeoutError;
        this.runSuggestQueue = false;
        this.suggestQueue = [];
        this.ajaxSuggestList = [];
        this.ajaxSuggestInterval;
        this.tld_price_data = "";
        this.error_text =
            '<h2 class="text-center">Đã có lỗi xảy ra, vui lòng thử lại sau</h2>';
        this.loading_html =
            '<div class="w-full whois_loading"><div class="flex justify-center vnx_wrapper w-full"><div class="vnx_loader-square vnx_square vnx_reg vnx_positioning"><div class="vnx_loading_block"><div class="vnx_loading_box"></div></div><div class="vnx_text_animate_loading">Loading...</div></div></div></div>';
        this.loading_small =
            '<div class="loading_suggest flex flex-col items-center justify-between my-5"><div class="loading_wrapper"><div class="loading_icon"></div></div><p class="text-brand text-center text-lg"><b>Đang tải</b></p></div>';
        this.ajaxGetSuggestChunk = 0;
    }

    readyGetSuggestData = () => {
        this.removeLoading();
        this.clearAjaxSuggest();
        this.ajaxGetSuggestChunk = 0;
        this.suggestQueue = [];
        this.ajaxSuggestList = [];
    };

    clearAjaxSuggest = () => {
        this.runSuggestQueue = false;
        clearInterval(this.ajaxSuggestInterval);
        $.each(this.ajaxSuggestList, (index, thisAjax) => {
            thisAjax.abort();
        });
    };

    processSuggestQueue = () => {
        // if (!this.runSuggestQueue) return;
        // console.log("processSuggestQueue");
        // suggestQueue sẽ bị trừ đi mỗi khi 1 ajax chạy xong
        if (this.suggestQueue.length > 0) {
            const params = this.suggestQueue.shift();
            this.GetSuggestChunk(...params);
        }
    };

    setTLDPriceData(data) {
        this.tld_price_data = data;
    }

    getTLDPriceData() {
        return this.tld_price_data;
    }

    setTLDPriceData(data) {
        this.tld_price_data = data;
    }

    setAjaxGetSuggestChunk(val) {
        this.ajaxGetSuggestChunk = val;
        // console.log("setAjaxGetSuggestChunk: " + val);
    }

    getAjaxGetSuggestChunk() {
        return this.ajaxGetSuggestChunk;
    }

    reduceAjaxGetSuggestChunk = () => {
        this.ajaxGetSuggestChunk += -1;
        // console.log("ajaxGetSuggestChunk: " + this.ajaxGetSuggestChunk);
        if (this.ajaxGetSuggestChunk <= 0) {
            this.removeLoading();
            this.disableCheck(false);
            clearInterval(this.ajaxSuggestInterval);
        }
    };

    disableCheck(val) {
        $(".whois_check_form_landingpage button").prop("disabled", val);
        $(".whois_check_form_landingpage input").prop("disabled", val);
    }

    disableResultCheck(val) {
        $(".whois_check_form button").prop("disabled", val);
        $(".whois_check_form input").prop("disabled", val);
    }
    removeLoading() {
        $("#vnx-whois-suggest.suggest-landingpage")
            .parent()
            .find(".loading_suggest")
            .remove();
    }

    showElement = (element, is_show = true) => {
        if (!is_show) {
            $(element).addClass("hidden");
        } else {
            $(element).removeClass("hidden");
        }
    };

    showProgressBar = (isShow = true) => {
        this.disableCheck(true);
        if (isShow) {
            $("#vnx-whois-result-landingpage .result_wrapper").html(""); // show data to front end
            $("#loading_animate").removeClass("hidden");
            this.showElement("#whois_suggest_result", false);
        } else {
            $("#loading_animate").addClass("hidden");
        }
    };

    scrollToResult = () => {
        $("html, body").animate(
            {
                scrollTop:
                    $("#vnx-whois-result-landingpage").offset().top - 200,
            },
            500
        );
    };

    modifyHistory = (thisDomain) => {
        if (this.getUrlParameter("domain") == thisDomain) return;
        if (!$.isFunction(replaceUrlParam) || !$.isFunction(addUrlParam))
            return false;
        var newUrl;
        if (window.location.search.indexOf("domain=") !== -1) {
            newUrl = replaceUrlParam("domain", encodeURIComponent(thisDomain));
        } else {
            newUrl = addUrlParam("domain", encodeURIComponent(thisDomain));
        } // replace domain parameter, if dont have domain parameter -> add it
        window.history.pushState({ path: newUrl }, "", newUrl); // add history to use back button on browser
        return true;
    };

    onPopstate = () => {
        $(window).on("popstate", () => {
            this.checkUrlParameter("domain");
        });
    };

    showWhoisCheckFormNotice = () => {
        $(".vnx-check-whois-form .nvx_notice").removeClass("hidden");
        setTimeout(() => {
            $(".vnx-check-whois-form .nvx_notice").addClass("hidden");
        }, 2000);
    };

    beforeGetWhoisData = (thisDomain) => {
        this.disableResultCheck(true);
        clearTimeout(this.checkTimeoutError);
        this.showWhoisLoading(thisDomain);
    };

    showWhoisLoading = (thisDomain) => {
        $("#vnx-whois-result").html(this.loading_html);
        $("#vnx-whois-suggest").html(this.loading_html);

        // check error ajax, remove loading and show error text
        this.checkTimeoutError = setTimeout(() => {
            if (thisDomain != $(".whois_check_form input").val()) return;
            if ($("#vnx-whois-result").find("#suggestion_loading").length > 0)
                $("#vnx-whois-result").html(this.error_text);
            if ($("#vnx-whois-suggest").find("#suggestion_loading").length > 0)
                $("#vnx-whois-suggest").html(this.error_text);
        }, 60000);
    };

    ajaxGetWhoisData = (thisDomain) => {
        // Run ajax get whois data -> return the HTML Data and show in selector #vnx-whois-result
        return $.ajax({
            method: "GET",
            url: whois_array.ajax_url,
            data: {
                action: "get_whois_data_center", //action
                domain: thisDomain, //data
                // _wpnonce: whois_array.nonce,
            },
            dataType: "json",
        });
    };

    onAjaxGetWhoisDataDone = (response) => {
        if (response.success) {
            if (response.data == "1") {
                // no domain
                $("#vnx-whois-result").html(this.error_text); // show error text to front end
                return;
            }
            $("#vnx-whois-result").html(response.data); // show data to front end
        } else {
            console.log(response.data);
            $("#vnx-whois-result").html(this.error_text); // show error text to front end
        }
    };

    onAjaxGetWhoisDataFail = (jqXHR, textStatus, errorThrown) => {
        console.log("The following error occured: " + textStatus, errorThrown);
        console.log(jqXHR);
        $("#vnx-whois-result").html(this.error_text); // show error text to front end
    };
    ajaxShowSuggest = (thisDomain) => {
        var tld_price_data = "";
        if (
            $.isFunction(get_sessionStorage) &&
            get_sessionStorage("tld_Price_data")
        ) {
            tld_price_data = get_sessionStorage("tld_Price_data");
        }
        $.ajax({
            type: "POST",
            url: whois_array.ajax_url,
            data: {
                action: "whois_get_suggest_item_center", //action
                domain: thisDomain,
                tld_price_data: tld_price_data,
                // _wpnonce: whois_array.nonce,
            },
            dataType: "json",
            beforeSend: () => {
                console.log("ajaxShowSuggest: " + thisDomain);
            },
            success: (response) => {
                if (response.success) {
                    $("#vnx-whois-suggest").html(response.data.html);
                    if (response.data.tld_data != "")
                        sessionStorage.setItem(
                            "tld_Price_data",
                            response.data.tld_data
                        );
                    this.disableResultCheck(false); // bật lại form check
                } else {
                    console.log(response.data);
                    $("#vnx-whois-suggest").html(this.error_text); // show error text to front end
                }
            },
            error: (jqXHR, textStatus, errorThrown) => {
                console.log(
                    "The following error occured: " + textStatus,
                    errorThrown
                );
                $("#vnx-whois-suggest").html(this.error_text); // show error text to front end
            },
        });
    };
    checkUrlParameter = (vnxParam) => {
        if (!this.getUrlParameter(vnxParam)) return;
        var thisDomain = this.getUrlParameter(vnxParam);
        $(".vnx-check-whois-form input").val(thisDomain);
        thisDomain = VNX_WHOIS.Ins().domainBeautified(thisDomain);
        const domain_format = this.WhoisCheckDomainFormat(thisDomain);
        if (domain_format.is_domain) {
            this.beforeGetWhoisData(thisDomain);
            if (!domain_format.tld) {
                // Lưu trữ giá trị trong Local Storage
                sessionStorage.setItem("WHOIS_domain", thisDomain);
                var action_url = $(".whois_check_form").attr("action");
                // Chuyển hướng đến trang action_url
                window.location.href = action_url;
                return;
            }
            const getWhoisData = this.ajaxGetWhoisData(thisDomain);
            this.ajaxShowSuggest(thisDomain);
            getWhoisData.done((response) => {
                if (response.data == "") {
                    // Lưu trữ giá trị trong Local Storage
                    sessionStorage.setItem("WHOIS_domain", thisDomain);
                    var action_url = $(".whois_check_form").attr("action");
                    // Chuyển hướng đến trang action_url
                    window.location.href = action_url;
                } else {
                    this.onAjaxGetWhoisDataDone(response);
                }
            });
            getWhoisData.fail((jqXHR, textStatus, errorThrown) => {
                this.onAjaxGetWhoisDataFail(jqXHR, textStatus, errorThrown);
            });
        } else {
            this.showWhoisCheckFormNotice();
        }
    };

    getUrlParameter = (vnxParam) => {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split("&"),
            vnxParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            vnxParameterName = sURLVariables[i].split("=");

            if (vnxParameterName[0] === vnxParam) {
                return vnxParameterName[1] === undefined
                    ? true
                    : decodeURIComponent(vnxParameterName[1]);
            }
        }
        return false;
    };

    WhoisCheckDomainFormat = (inputVal) => {
        var domainWithoutTLDRegex = /^[a-zA-Z0-9-]+$/;
        var domainWithTLDRegex = /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        var result = {
            is_domain: false,
            tld: false,
        };

        if (domainWithTLDRegex.test(inputVal)) {
            result.is_domain = true;
            result.tld = true;
        } else if (domainWithoutTLDRegex.test(inputVal)) {
            result.is_domain = true;
        }

        return result;
    };
    GetWhoisDataLPage = (thisDomain) => {
        // ajax show data
        this.ajaxGetDomainData(thisDomain);
        this.getSuggestData(thisDomain);
    };

    getSuggestData = (thisDomain) => {
        var tld_price_data = "";
        this.readyGetSuggestData(); // reset biến, clear ajax, ẩn loading, chuẩn bị kiểm tra mới
        if (
            $.isFunction(get_sessionStorage) &&
            get_sessionStorage("tld_Price_data")
        ) {
            tld_price_data = get_sessionStorage("tld_Price_data"); // lấy trong session storage để khỏi request lấy từ API
            this.setTLDPriceData(tld_price_data);
        }
        if (!this.getTLDPriceData()) return;
        const parts = thisDomain.split(".");
        var currentTld;
        if (parts.length == 2) {
            currentTld = parts[1];
        }
        if (parts.length == 3) {
            currentTld = parts[1] + "." + parts[2];
        }
        if (parts.length == 4) {
            currentTld = parts[2] + "." + parts[3];
        }
        const tld_chunk = this.tld_data_chunk(tld_price_data, currentTld); // chia tất cả domain cần kiểm tra thành từng nhóm (chunk)
        this.setAjaxGetSuggestChunk(Object.keys(tld_chunk).length); // set số lượng chunk vừa chia được, để trừ dần mỗi khi 1 chunk ajax chạy xong
        $("#vnx-whois-suggest.suggest-landingpage").empty();
        $("#vnx-whois-suggest.suggest-landingpage").after(this.loading_small);
        $.each(tld_chunk, (index, chunk) => {
            this.suggestQueue.push([index, chunk, thisDomain]); // push chunk vào suggestQueue để chạy ajax theo suggestQueue đó
        });
        // Gọi ajax mỗi 1.2s, nếu ko chạy cái này thì ajax gọi 1 lần rất nhiều
        this.ajaxSuggestInterval = setInterval(this.processSuggestQueue, 1200); // Gọi hàm processSuggestQueue mỗi 1200
    };

    tld_data_chunk = (tld_price_data, currentTld) => {
        const chunkSize = 4;

        const domains = Object.keys(tld_price_data);
        var dataChunks = [];

        for (let i = 0; i < domains.length; i += chunkSize) {
            const chunk = domains.slice(i, i + chunkSize);
            dataChunks.push(chunk);
        }

        // Tạo object mới từ các chuỗi con đã chia sử dụng jQuery
        const newData = {};

        $.each(dataChunks, (chunkNumber, chunk) => {
            const temp = {};
            $.each(chunk, (index, domain) => {
                if (domain == currentTld) return true;
                temp[domain] = tld_price_data[domain];
            });
            newData[chunkNumber] = temp;
        });
        return newData;
    };

    GetSuggestChunk = (index, chunk, thisDomain, firstChunkError = 0) => {
        if (!chunk) return;
        var appended;
        // console.log(chunk);
        const thisAjax = $.ajax({
            type: "GET",
            url: whois_array.ajax_url,
            data: {
                action: "get_suggest_item_chunk_center", //action
                domain: thisDomain, //data
                index: index,
                chunk: chunk, //data
                // _wpnonce: whois_array.nonce,
            },
            dataType: "json",
            beforeSend: () => {},
            success: (response) => {
                if (response.success) {
                    if (response.data.php_response == "1") {
                        // no domain
                        $(".suggest-landingpage.vnx-suggest-result").html(
                            this.error_text
                        ); // show error text to front end
                        return;
                    }
                    if (response.data.index == 0) {
                        // đưa mấy tên miền ưu tiên lên đầu
                        appended = $(
                            ".suggest-landingpage.vnx-suggest-result"
                        ).prepend(response.data.php_response);
                    } else {
                        appended = $(
                            ".suggest-landingpage.vnx-suggest-result"
                        ).append(response.data.php_response);
                    }
                    $(appended)
                        .find(".add_to_cart_action")
                        .each(function() {
                            VNX_WHOIS_CART.Ins().checkButtonSuggest(this); // kiểm tra tên miền này có trong cart chưa để hiện đúng trạng thái nút
                        });
                } else {
                    console.log(response.data);
                    if (index == 0 && firstChunkError == 0) {
                        // Only fix vietnix.vn error at first ajax
                        this.GetSuggestChunk(index, chunk, thisDomain, 1);
                        return;
                    }
                }
                this.reduceAjaxGetSuggestChunk(); // ajax xong thì trừ số lượng ajax đi 1, để khi tới 0 thì ko chạỵ nữa
            },
            error: (jqXHR, textStatus, errorThrown) => {
                console.log(
                    "The following error occured: " + textStatus,
                    errorThrown
                );
                if (textStatus != "abort") {
                    if (index == 0 && firstChunkError == 0) {
                        // Only fix vietnix.vn error at first ajax
                        this.GetSuggestChunk(index, chunk, thisDomain, 1);
                    } else {
                        this.reduceAjaxGetSuggestChunk();
                    }
                }
            },
        });
        this.ajaxSuggestList.push(thisAjax); // thêm ajax này vào ajaxSuggestList để dùng abort tất cả ajax
    };

    ajaxGetDomainData = (thisDomain) => {
        var progressBar;
        console.log("thisDomain: " + thisDomain);
        var tld_price_data = "";
        if (
            $.isFunction(get_sessionStorage) &&
            get_sessionStorage("tld_Price_data")
        ) {
            tld_price_data = get_sessionStorage("tld_Price_data");
        }
        $.ajax({
            type: "POST",
            url: whois_array.ajax_url,
            data: {
                action: "get_domain_data_whois_ldpage_center", //action
                domain: thisDomain, //data
                tld_price_data: tld_price_data,
                // _wpnonce: whois_array.nonce,
            },
            dataType: "json",
            beforeSend: () => {
                progressBar = runProgressBar();
            },
            success: (response) => {
                if (response.success) {
                    if (response.data == "1") {
                        // no domain
                        $("#vnx-whois-result-landingpage").html(
                            this.error_text
                        ); // show error text to front end
                        return;
                    }
                    if (response.data.tld_data != "")
                        sessionStorage.setItem(
                            "tld_Price_data",
                            response.data.tld_data
                        );
                    progressBar.endProgressBar(response.data.html); // ẩn thanh progress + hiện kết quả response data html
                    if (this.ajaxGetSuggestChunk > 0) return;
                    this.getSuggestData(thisDomain); // nếu chưa chạy chunk gợi ý thì chạy
                } else {
                    console.log(response.data);
                    $("#vnx-whois-result-landingpage").html(this.error_text); // show error text to front end
                }
            },
            error: (jqXHR, textStatus, errorThrown) => {
                console.log(
                    "The following error occured: " + textStatus,
                    errorThrown
                );
                $("#vnx-whois-result-landingpage").html(this.error_text); // show error text to front end
            },
        });
    };
    domainBeautified = (domain) => {
        domain = domain.toLowerCase();
        domain = domain.trim();
        return domain;
    };
}
class VNX_WHOIS_CART {
    // Make SingleTon to get data form it VNX_WHOIS_CART
    static Ins() {
        if (!VNX_WHOIS_CART.instance) {
            VNX_WHOIS_CART.instance = new VNX_WHOIS_CART();
        }
        return VNX_WHOIS_CART.instance;
    }
    constructor() {
        this.domain = "";
        this.cart_cookie = "vnx_domain_carts";
    }

    startAddToCart = (thisDomain) => {
        if ($.isFunction(add_domain_to_cart)) add_domain_to_cart(thisDomain);
    };

    switchButtonStatus = (thisButton, is_activated = false) => {
        if (is_activated == true) {
            $(thisButton).addClass("activated");
            $(thisButton)
                .find(".add_text")
                .addClass("hidden");
            $(thisButton)
                .find(".added_text")
                .removeClass("hidden");
            return;
        }
        $(thisButton).toggleClass("activated");
        $(thisButton)
            .find(".add_text")
            .toggleClass("hidden");
        $(thisButton)
            .find(".added_text")
            .toggleClass("hidden");
    };

    isDomainInCart = (thisDomain) => {
        const data = getDataFromCookie(this.cart_cookie);
        let isFound = false;
        for (const item of data) {
            if (item.domain === thisDomain) {
                isFound = true;
                break;
            }
        }
        // console.log(thisDomain + " in cart: " + isFound);
        return isFound;
    };

    checkButtonSuggest = (thisButton) => {
        const thisDomain = $(thisButton).attr("data-domain");
        if (this.isDomainInCart(thisDomain))
            this.switchButtonStatus(thisButton, true);
    };

    whois_register_suggested_domain = (domain, cart_url) => {
        if (!domain) {
            console.log("No data-domain getted from a tag");
            return;
        }
        if (
            !$.isFunction(checkObjectInCookie) ||
            !$.isFunction(addObjectToCookie)
        ) {
            console.log(
                "no checkObjectInCookie or addObjectToCookie function. Please check the cookie_func.js"
            );
            return;
        }
        if (!cart_cookie) {
            console.log(
                "no cart_cookie. Check in domain_cart.js Suggest: vnx_domain_carts"
            );
            cart_cookie = "vnx_domain_carts";
        }
        if (!cart_url) {
            console.log("no cart_url getted from a tag");
            cart_url = "https://portal.vietnix.vn/cart.php?a=view";
        }
        const cookie_age = 60;
        var obj = { domain: domain, register: "1", authen_code: "" };
        if (!checkObjectInCookie(cart_cookie, obj)) {
            addObjectToCookie(cart_cookie, obj, cookie_age, cookie_age);
            console.log("vnx_domain_carts cookie modified");
        }
        window.location.href = cart_url;
    };
}
$(document).ready(function() {
    // -------------- WhoisResult - Open ---------------
    VNX_WHOIS.Ins().checkUrlParameter("domain"); // kiểm tra url có param domain ko? nếu có thì check
    VNX_WHOIS.Ins().onPopstate(); // đăng ký sự kiện bấm nút back, next lịch sử trên trình duyệt, để check lại
    $(".whois_check_form").on("submit", function(e) {
        sessionStorage.removeItem("WHOIS_domain");
        var thisDomain = $(this)
            .find("input")
            .val();
        $(".whois_check_form input").val(thisDomain); // thêm giá trị vào tất cả input check để nhìn cho đồng bộ
        thisDomain = VNX_WHOIS.Ins().domainBeautified(thisDomain); // lowercase, xoá white space 2 đầu tên miền
        const domain_format = VNX_WHOIS.Ins().WhoisCheckDomainFormat(
            thisDomain
        ); // check xem domain đúng format không
        if (domain_format.is_domain) {
            if (!domain_format.tld) {
                // domain ko có tld
                return true;
            }
            e.preventDefault();
            VNX_WHOIS.Ins().beforeGetWhoisData(thisDomain); // disable form check, xoá timeout check lâu quá báo lỗi, show loading
            getWhoisData = VNX_WHOIS.Ins().ajaxGetWhoisData(thisDomain);
            VNX_WHOIS.Ins().ajaxShowSuggest(thisDomain);
            getWhoisData.done(function(response) {
                if (response.data == "") {
                    // trả về rỗng nghĩa là tên miền đang kiểm tra available -> redirect qua trang kia để check
                    VNX_WHOIS.Ins().disableResultCheck(false); // bỏ disable form thì khi submit redirect qua trang kia mới check được
                    e.currentTarget.submit();
                } else {
                    VNX_WHOIS.Ins().onAjaxGetWhoisDataDone(response);
                    VNX_WHOIS.Ins().modifyHistory(thisDomain);
                }
            });
            getWhoisData.fail(function(jqXHR, textStatus, errorThrown) {
                VNX_WHOIS.Ins().onAjaxGetWhoisDataFail(
                    jqXHR,
                    textStatus,
                    errorThrown
                );
            });
        } else {
            // nếu domain format sai -> hiện thông báo lỗi
            e.preventDefault();
            VNX_WHOIS.Ins().showWhoisCheckFormNotice();
        }
    });
    $(".vnx-suggest-result").on("click", ".register_domain_action", function(
        e
    ) {
        e.preventDefault();
        const domain = $(this).attr("data-domain");
        const cart_url = $(this).attr("href");
        VNX_WHOIS_CART.Ins().whois_register_suggested_domain(domain, cart_url);
    });
    // -------------- WhoisResult - Close ---------------
    // **************************************************************************************************************** //
    // -------------- WhoisLanding - Open ---------------
    $(".whois_check_form_landingpage")
        .first()
        .addClass("first"); //Thêm class first để scroll lên trên trong trường hợp trang có nhiều ô search
    $(".whois_check_form_landingpage").submit(function(e) {
        var thisDomain = $(this)
            .find("input")
            .val();
        $(".whois_check_form_landingpage input").val(thisDomain); // trường hợp nhiều ô search, gắn giá trị từng ô cho đồng bộ
        thisDomain = VNX_WHOIS.Ins().domainBeautified(thisDomain); // lowercase, xoá space 2 đầu
        var check_domain = VNX_WHOIS.Ins().WhoisCheckDomainFormat(thisDomain); // kiểm tra định dạng tên miền có đúng ko
        if (!check_domain.is_domain) {
            VNX_WHOIS.Ins().showWhoisCheckFormNotice(); // nếu tên miền ko đúng định dạng -> hiện lỗi -> ko làm gì cả
            return false;
        }
        if (!check_domain.tld) thisDomain = thisDomain + ".vn"; // nếu ko nhập tld -> thêm tld mặc định
        VNX_WHOIS.Ins().GetWhoisDataLPage(thisDomain);
        if (!$(this).hasClass("first")) VNX_WHOIS.Ins().scrollToResult(); // nếu ko phải ô search đầu -> đang ở dưới -> scroll lên ô kết quả
        return false;
    });
    if ($(".whois_check_form_landingpage").length > 0) {
        // Phần này: lúc mới load trang, kiểm tra có WHOIS_domain ko, trường hợp search bên trang whois result tên miền ko có tld hoặc chưa được đăng ký thì redirect qua đây kiểm tra
        domain_checking = sessionStorage.getItem("WHOIS_domain");
        if (domain_checking) {
            $(".whois_check_form_landingpage input").val(domain_checking);
        }
        if ($(".whois_check_form_landingpage input").val() != "") {
            let thisDomain = $(".whois_check_form_landingpage input").val();
            thisDomain = VNX_WHOIS.Ins().domainBeautified(thisDomain);
            let check_domain = VNX_WHOIS.Ins().WhoisCheckDomainFormat(
                thisDomain
            );
            if (!check_domain.is_domain) {
                VNX_WHOIS.Ins().showWhoisCheckFormNotice();
                return false;
            }
            if (!check_domain.tld) thisDomain = thisDomain + ".vn";
            VNX_WHOIS.Ins().GetWhoisDataLPage(thisDomain);
        }
    }
    // -------------- WhoisLanding - Close ---------------
    // **************************************************************************************************************** //
    // -------------- WhoisCart - Open ---------------
    $(document).on("click", ".add_to_cart_action", function(e) {
        e.preventDefault();
        const thisDomain = $(this).attr("data-domain");
        if (VNX_WHOIS_CART.Ins().isDomainInCart(thisDomain)) return;
        VNX_WHOIS_CART.Ins().startAddToCart(thisDomain);
        VNX_WHOIS_CART.Ins().switchButtonStatus(this); // thêm xong thì đổi trạng thái nút thêm vào giỏ
    });
    $(document).on("click", ".remove_domain_cart", function(e) {
        const thisDomain = $(this).attr("data-domain");
        $(`.add_to_cart_action[data-domain='${thisDomain}']`).each(function(
            index,
            thisButton
        ) {
            VNX_WHOIS_CART.Ins().switchButtonStatus(thisButton);
        });
    });
    // -------------- WhoisCart - Close ---------------
});
// **************************************************************************************************************** //
// -------------- WhoisLanding - Open ---------------
function runProgressBar() {
    var $progressBar = $(".progress-bar");
    var progress = 0;
    var interval = 33; // milliseconds
    var step = 0.2;
    var progressInterval;

    function updateProgressBar() {
        $progressBar.animate({ width: progress + "%" }, interval);
    }

    function finishProgressBar(html) {
        $progressBar.stop().animate({ width: "100%" }, 500);
        setTimeout(function() {
            VNX_WHOIS.Ins().showProgressBar(false);
            appended = $("#vnx-whois-result-landingpage .result_wrapper").html(
                html
            ); // show data to front end
            $(appended)
                .find(".add_to_cart_action")
                .each(function() {
                    VNX_WHOIS_CART.Ins().checkButtonSuggest(this);
                });
            VNX_WHOIS.Ins().showElement("#whois_suggest_result");
            // VNX_WHOIS.Ins().disableCheck(false);
        }, 1000);
    }

    function endProgressBar(html) {
        if (progress < 100) {
            clearInterval(progressInterval);
            finishProgressBar(html);
        } else {
            VNX_WHOIS.Ins().showProgressBar(false);
            appended = $("#vnx-whois-result-landingpage .result_wrapper").html(
                html
            ); // show data to front end
            $(appended)
                .find(".add_to_cart_action")
                .each(function() {
                    VNX_WHOIS_CART.Ins().checkButtonSuggest(this);
                });
            // VNX_WHOIS.Ins().disableCheck(false);
        }
    }

    function startProgress() {
        VNX_WHOIS.Ins().showElement("#whois_page_content", false);
        VNX_WHOIS.Ins().showProgressBar();
        progressInterval = setInterval(function() {
            progress += step; // Increase by 2% in every interval
            updateProgressBar();

            if (progress >= 98) {
                clearInterval(progressInterval);
                // Attach your logic for e_event completion here
            }
        }, interval);
    }

    startProgress();

    return {
        endProgressBar: endProgressBar,
    };
}
// -------------- WhoisLanding - Close ---------------
