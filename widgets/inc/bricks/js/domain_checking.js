window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + "/wp-admin/admin-ajax.php";
var domain_annual_price = "Giá đăng ký gốc";
var domain_tooltip_title = "Tooltip title";
var domain_tooltip_des = "Tooltip description";
var domain_label = "Label";

get_domain_price();
var ongoingRequests = [];

// Add a global AJAX event handler
$(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
    ongoingRequests.push(jqXHR);
});

// Add another global AJAX event handler
$(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {
    var index = ongoingRequests.indexOf(jqXHR);
    if (index !== -1) {
        ongoingRequests.splice(index, 1);
    }
});

// Function to abort all ongoing AJAX requests
function abortAllRequests() {
    $.each(ongoingRequests, function (index, jqXHR) {
        if (jqXHR != undefined) {
            jqXHR.abort();
        }
    });
    ongoingRequests = [];
}
//check form isset and run function when user hit enter button
if ($("#vnx_search_domain").length) {
    $("#vnx_search_domain").keypress(function (e) {
        if (e.which === 13) {
            e.preventDefault();
            abortAllRequests();
            var searchQuery = $(this).val().trim();
            searchQuery = searchQuery.toLowerCase();
            if (searchQuery != "") {
                $(this).val(searchQuery);
                domain_check(searchQuery);
            } else {
                var notification = $("<div>", {
                    class: "vnx_noti fixed top-40",
                });
                $("body").append(notification);
                $(".vnx_noti").append(
                    '<div id="toast-danger" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow" role="alert"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg"><svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg><span class="sr-only">Error icon</span></div><div class="ml-3 text-sm font-normal">Vui lòng nhập tên miền</div></div>'
                );

                $(".vnx_noti").animate({ right: "0" });
                setTimeout(function () {
                    notification.remove();
                }, 2000);
            }
        }
    });
}
if ($("#vnx_search_domain").length) {
    $("#vnx_search_domain_btn").on("click", function (e) {
        e.preventDefault();
        $("#vnx_search_domain").trigger(
            jQuery.Event("keypress", { which: 13 })
        );
    });
}

$(".clear_input img").on("click", function (e) {
    $("#vnx_search_domain").val("");
    $("#vnx_search_domain").focus();
});

if ($('form[name="formSearchDomain"]').length) {
    $('form[name="formSearchDomain"]').submit(function (e) {
        e.preventDefault();
        var searchQuery = $("#form-field-vnx_template_domain").val().trim();
        searchQuery = searchQuery.toLowerCase();
        $(".vnx-section-form-search-domain").css("display", "block");
        domain_check(searchQuery);
        $("#form-field-vnx_template_domain").val(searchQuery);
    });
}

//clear form and forcus when click button
// $(document).on("click", "#btn_another_domain", function () {
//   $("#vnx_search_domain").val('').focus();
//   $("#form-field-vnx_template_domain").val('').focus();
// });

//check form have variable on page load
$(window).on("load", function () {
    setTimeout(function () {
        if ($("input#vnx_search_domain").length) {
            if ($("input#vnx_search_domain").val() !== "") {
                domain_check($("input#vnx_search_domain").val());
            }
        }
        if ($("input#form-field-vnx_template_domain").length) {
            if ($("input#form-field-vnx_template_domain").val() !== "") {
                domain_check($("input#form-field-vnx_template_domain").val());
            }
        }
    });

});


//check domain validity
function isValidDomain(input) {
    var pattern = /^(?:[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*\.)+[a-zA-Z]{2,}$/g;
    return pattern.test(input);
}
function enable_disable_Form(value = true) {
    $("#vnx_search_domain").prop("disabled", value);
    $("#vnx_search_domain_btn").prop("disabled", value);
}
//check domain availability and add SLD
function domain_check(searchQuery) {
    // deleteCookie('domain_check')
    var white_space = /\s/g;
    let tool_tip_html = "";
    let data_tooltip_des = `Hiện tên miền không hợp lệ. Quý khách vui lòng tìm kiếm lại với một tên miền khác.`;
    tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/><span class="sr-only">Info</span>
  <div class="tool_tip_search absolute domain_notvalid">
    <div class="tooltiptext_search w-full relative">`;
    tool_tip_html += '<div class="text-xs text-left font-bold w-full"></div>';
    tool_tip_html +=
        '<div class="text-xs text-left font-normal mt-1 w-full">' +
        data_tooltip_des +
        "</div>";
    tool_tip_html += "</div></div></div>";
    if (!white_space.test(searchQuery)) {
        $("#vail_domain.domain_availble").empty();
        var afterDot = searchQuery.substr(searchQuery.indexOf(".") + 1);
        var default_search = searchQuery;
        if (searchQuery.includes(".") == false) {
            afterDot = "." + $("input#vnx_suggest_tld").val();
            searchQuery += afterDot;
        }
        // $('input#vnx_search_domain').val(searchQuery)
        if (isValidDomain(searchQuery) == true) {
            check_domain(searchQuery);
            var newUrl;
            if (window.location.search.indexOf("domain=") !== -1) {
                newUrl = replaceUrlParam(
                    "domain",
                    encodeURIComponent(default_search)
                );
            } else {
                newUrl = addUrlParam(
                    "domain",
                    encodeURIComponent(default_search)
                );
            }
            window.history.pushState({ path: newUrl }, "", newUrl);
            $(".loading_domain").show();
        } else {
            $("#vail_domain.domain_availble").html(
                '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res"><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban"/><span class="sr-only">Error icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                searchQuery +
                '</div></div><div class="inline-flex sm:mt-1 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền không hợp lệ</span></div>' +
                tool_tip_html +
                '</div></div><div class="lg:ml-auto inline-flex lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:ml-auto lg:mt-0 m-auto mt-4"></div></div></div>'
            );
        }
    } else {
        $("#vail_domain.domain_availble").html(
            '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res"><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban"/><span class="sr-only">Error icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
            searchQuery +
            '</div></div><div class="inline-flex sm:mt-1 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền không hợp lệ</span></div>' +
            tool_tip_html +
            '</div></div><div class="lg:ml-auto inline-flex lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:ml-auto lg:mt-0 m-auto mt-4"></div></div></div>'
        );
    }
}

function addUrlParam(param, value) {
    var baseUrl = [
        location.protocol,
        "//",
        location.host,
        location.pathname,
    ].join("");
    var urlQueryString = location.search;
    var newParam = param + "=" + value;

    if (urlQueryString) {
        var params = urlQueryString.split("&");
        var found = false;
        for (var i = 0; i < params.length; i++) {
            if (params[i].split("=")[0] === param) {
                params[i] = newParam;
                found = true;
            }
        }
        if (!found) {
            params.push(newParam);
        }
        return baseUrl + params.join("&");
    } else {
        return baseUrl + "?" + newParam;
    }
}

function replaceUrlParam(param, value) {
    // Helper function to replace the value of an existing parameter in the URL
    var baseUrl = [
        location.protocol,
        "//",
        location.host,
        location.pathname,
    ].join("");
    var urlQueryString = location.search;
    var newParam = param + "=" + value;

    // If the URL already has any parameters, replace the value of the parameter
    if (urlQueryString) {
        var firstParam = "";
        var params = urlQueryString.split("&");
        $.each(params, function (index, item) {
            if (index == 0) {
                if (item.split("=")[0] === "?" + param) {
                    firstParam = "?";
                    params[index] = newParam;
                    return false;
                }
            } else {
                if (item.split("=")[0] === param) {
                    firstParam = "";
                    params[index] = newParam;
                    return false;
                }
            }
        });
        return baseUrl + firstParam + params.join("&");
    } else {
        return baseUrl + "?" + newParam;
    }
}

async function suggest_domain(searchQuery, template) {
    data = [{ domain_search: searchQuery, template: template }];
    $.ajax({
        type: "POST",
        url: admin_ajax_url,
        data: {
            action: "suggest_domain_center",
            data: data,
        },
        success: function (data) {
            $("#suggestion_domain").html(data);
        },
        error: function (xhr, status, error) { },
    });
    return;
}

//get and save domain price to session storage
async function get_domain_price() {
    // data = [{ 'domain': 'vietnix.vn' }]
    if (
        !get_sessionStorage("domain_price_Data") ||
        !get_sessionStorage("domain_Data")
    ) {
        var data = {
            action: "GetTLDPricing",
            data: {
                security: $("#vnx_domain_security").val(),
                currencyid: "2",
            },
        };
        try {
            const response = await post_ajax_Data_search("POST", "get_api_whmcs_center", data);
            if (response != null && response.result === "success") {
                sessionStorage.setItem(
                    "domain_price_Data",
                    JSON.stringify(response.pricing)
                );
                sessionStorage.setItem("domain_Data", JSON.stringify(response));
                display_cart();
            } else if (response && response.result == "error") {
                // console.log(response);
            }
        } catch (error) {
            console.warn("Lấy giá domain thất bại:", error);
        }
    } else {
        display_cart();
    }

    if (typeof tld_data != "undefined") {
        sessionStorage.setItem("TLD_Data", JSON.stringify(tld_data));
    } else {
        sessionStorage.setItem("TLD_Data", JSON.stringify(""));
    }
    return;
}

//check domain and show results data
async function check_domain(searchQuery) {
    enable_disable_Form(true);
    var data = {
        data: {
            security: $("#vnx_domain_security").val(),
            domain: searchQuery,
        },
    };
    sessionStorage.setItem(
        "domain_random_Data",
        JSON.stringify(generateRandomString(5))
    );
    var domain_data_array = get_sessionStorage("domain_price_Data");
    var tld_data = get_sessionStorage("TLD_Data");
    let suggest = true;
    if (domain_data_array == null || tld_data == null) {
        await get_domain_price();
        domain_data_array = get_sessionStorage("domain_price_Data");
        tld_data = get_sessionStorage("TLD_Data");
    }
    var template = $("#vnx_template_domain").val();
    var afterDot = searchQuery.substr(searchQuery.indexOf(".") + 1);
    var beforeDot = searchQuery.split(".")[0];
    if (afterDot == "vn" && beforeDot.length <= 2) {
        setTimeout(function () {
            $(".loading_domain").hide();
            var tool_tip_html = "";
            var data_tooltip_des = `Theo quy định của Trung tâm Internet Việt Nam (VNNIC) tên miền cấp 2 có 1,2 kí tự hiện chưa thể đăng ký. Quý khách có thể tham khảo tại đây: <a href=""><strong>Xem chi tiết</strong></a>`;
            tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/>
      <div class="tool_tip_search absolute">
        <div class="tooltiptext_search w-full relative">`;
            tool_tip_html +=
                '<div class="text-xs text-left font-bold w-full"></div>';
            tool_tip_html +=
                '<div class="text-xs text-left font-normal mt-1 w-full">' +
                data_tooltip_des +
                "</div>";
            tool_tip_html += "</div></div></div>";
            $("#vail_domain.domain_availble").html(
                '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res"><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban"/><span class="sr-only">Error icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                searchQuery +
                '</div></div><div class="inline-flex sm:mt-1 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Chưa thể đăng ký tên miền .vn có 1,2 kí tự</span></div>' +
                tool_tip_html +
                '</div></div><div class="lg:ml-auto inline-flex lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:ml-auto lg:mt-0 m-auto mt-4"></div></div></div>'
            );
            $("#suggestion_domain").empty();
            $("#suggestion_domain").html(
                `<div class="flex justify-center w-full h-full"><div class="pt-5"><img class="mx-auto" src="https://vietnix.vn/wp-content/uploads/2023/08/timeout.svg"><p class="text-center">Đã xảy ra lỗi trong quá trình tìm kiếm. </br> Vui lòng thử lại</p></div></div></div>`
            );
        });
        enable_disable_Form(false);
    } else {
        $("#suggestion_domain").html(
            '<div class="w-full" id="suggestion_loading"><div class="flex justify-center w-full"><div class="vnx_loader-square vnx_square vnx_reg vnx_positioning"><div class="vnx_loading_block"><div class="vnx_loading_box"></div></div><div class="vnx_text_animate_loading">Loading...</div></div></div></div>'
        );
        try {
        await post_ajax_Data_search("POST", "domain_vailid_checking_center", data)
            .then(function (data) {
                if (
                    data !== null &&
                    data.status == "unavailable" &&
                    data.result == "success"
                ) {
                    var tool_tip_html = "";
                    var data_tooltip_title =
                        tld_data.length > 0
                            ? tld_data[0].indexOf(domain_tooltip_title)
                            : -1;
                    var data_tooltip_des =
                        tld_data.length > 0
                            ? tld_data[0].indexOf(domain_tooltip_des)
                            : -1;
                    if (
                        findObjectByTLD(afterDot, tld_data) != -1 &&
                        tld_data[findObjectByTLD(afterDot, tld_data)][
                        data_tooltip_title
                        ] !== ""
                    ) {
                        tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/><span class="sr-only">Info</span>
            <div class="tool_tip_search absolute">
              <div class="tooltiptext_search w-full relative">`;
                        tool_tip_html +=
                            '<div class="text-xs text-left font-bold w-full">' +
                            tld_data[findObjectByTLD(afterDot, tld_data)][
                            data_tooltip_title
                            ] +
                            "</div>";
                        tool_tip_html +=
                            '<div class="text-xs text-left font-normal mt-1 w-full">' +
                            tld_data[findObjectByTLD(afterDot, tld_data)][
                            data_tooltip_des
                            ] +
                            "</div>";
                        tool_tip_html += "</div></div></div>";
                    }
                    $("#vail_domain.domain_availble").html(
                        '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res"><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban"/><span class="sr-only">Error icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                        searchQuery +
                        '</div></div><div class="inline-flex sm:mt-1 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền đã được đăng ký</span></div>' +
                        tool_tip_html +
                        '</div></div><div class="lg:ml-auto inline-flex lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:ml-auto lg:mt-0 m-auto mt-4"></div></div></div>'
                    );
                } else if (
                    data !== null &&
                    data.status == "available" &&
                    data.result == "success"
                ) {
                    if (
                        data.premium != null &&
                        data.premium.status == "available" &&
                        data.premium.costHash != undefined
                    ) {
                        var money =
                            domain_data_array[afterDot].register[1].split(
                                "."
                            )[0];
                        var tld_data_price = "";
                        var tld_data_price_nodot = "";
                        var data_price_index =
                            tld_data.length > 0
                                ? tld_data[0].indexOf(domain_annual_price)
                                : -1;

                        if (findObjectByTLD(afterDot, tld_data) !== -1) {
                            tld_data_price = tld_data[
                                findObjectByTLD(afterDot, tld_data)
                            ][data_price_index].replace(/\,/g, "");
                            tld_data_price_nodot = tld_data_price.replace(
                                /\./g,
                                ""
                            );
                        }
                        var persent_html = "";
                        var tld_price_html = "";
                        if (tld_data_price !== "") {
                            if (!(money == tld_data_price_nodot)) {
                                var percent = persen_calculate(
                                    money,
                                    tld_data_price_nodot
                                );
                                persent_html =
                                    '<span class="bg-[#EB5757] text-white text-xs font-medium ml-2 px-1 py-0.5 rounded">-' +
                                    percent +
                                    "%</span>";
                                var annual_price_dot = tld_data_price
                                    .toString()
                                    .replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
                                tld_price_html =
                                    '<div class="text-xs line-through text-[#828282]">' +
                                    annual_price_dot +
                                    " đ</div>";
                            }
                        }
                        var moneyDots = money
                            .toString()
                            .replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
                        var tool_tip_html = "";
                        var data_tooltip_des = `Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký`;
                        tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/08/circle-info2.svg" alt="doamin info"/>
          <div class="tool_tip_search absolute">
            <div class="tooltiptext_search w-full relative">`;
                        tool_tip_html +=
                            '<div class="text-xs text-left font-bold w-full"></div>';
                        tool_tip_html +=
                            '<div class="text-xs text-left font-normal mt-1 w-full">' +
                            data_tooltip_des +
                            "</div>";
                        tool_tip_html += "</div></div></div>";
                        var html =
                            '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res" ><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/check-domain.svg" alt="doamin checked"/><span class="sr-only">Check icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                            searchQuery +
                            '</div></div><div class="inline-flex sm:mt-0 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#CE6A00] rounded-full bg-[#FFF0BA] relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền đặc biệt, vui lòng liên hệ Vietnix để đăng ký</span></div>' +
                            tool_tip_html +
                            '</div></div><div class="lg:ml-auto lg:inline-flex flex lg:flex-nowrap flex-wrap lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:w-fit w-full lg:ml-auto lg:mt-0 sm:ml-auto mt-4 ml-auto price_button"><button type="button" data-domain="' +
                            searchQuery +
                            '" class="text-white bg-[#38A7FF] hover:bg-[#38A7FF] font-medium rounded-lg text-sm px-3 py-2.5 focus:outline-none btn_tawk w-full" style="min-width: 9rem">Liên hệ</button></div></div></div>';
                        $("#vail_domain.domain_availble").html(html);
                    } else {
                        if (
                            typeof domain_data_array[afterDot] === "undefined"
                        ) {
                            suggest = false;
                            var tool_tip_html = "";
                            var data_tooltip_des = `Hiện tên miền không khả dụng. Quý khách vui lòng tìm kiếm lại với một tên miền khác.`;
                            tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/><span class="sr-only">Info</span>
            <div class="tool_tip_search absolute domain_notvalid">
              <div class="tooltiptext_search w-full relative">`;
                            tool_tip_html +=
                                '<div class="text-xs text-left font-bold w-full"></div>';
                            tool_tip_html +=
                                '<div class="text-xs text-left font-normal mt-1 w-full">' +
                                data_tooltip_des +
                                "</div>";
                            tool_tip_html += "</div></div></div>";
                            $("#vail_domain.domain_availble").html(
                                '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res"><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban"/><span class="sr-only">Error icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                                searchQuery +
                                '</div></div><div class="inline-flex sm:mt-1 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền không hợp lệ</span></div>' +
                                tool_tip_html +
                                '</div></div><div class="lg:ml-auto inline-flex lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:ml-auto lg:mt-0 m-auto mt-4"></div></div></div>'
                            );
                            $(".loading_domain").hide();
                            $("#suggestion_domain").empty();
                            $("#suggestion_domain").html(
                                `<div class="flex justify-center w-full h-full"><div class="pt-5"><img class="mx-auto" src="https://vietnix.vn/wp-content/uploads/2023/08/timeout.svg"><p class="text-center">Đã xảy ra lỗi trong quá trình tìm kiếm. </br> Vui lòng thử lại</p></div></div></div>`
                            );
                            abortAllRequests();
                            enable_disable_Form(false);
                        } else {
                            var money =
                                domain_data_array[afterDot].register[1].split(
                                    "."
                                )[0];
                            var tld_data_price = "";
                            var tld_data_price_nodot = "";
                            var data_price_index =
                                tld_data.length > 0
                                    ? tld_data[0].indexOf(domain_annual_price)
                                    : -1;

                            if (findObjectByTLD(afterDot, tld_data) !== -1) {
                                tld_data_price = tld_data[
                                    findObjectByTLD(afterDot, tld_data)
                                ][data_price_index].replace(/\,/g, "");
                                tld_data_price_nodot = tld_data_price.replace(
                                    /\./g,
                                    ""
                                );
                            }
                            var persent_html = "";
                            var tld_price_html = "";
                            if (tld_data_price !== "") {
                                if (!(money == tld_data_price_nodot)) {
                                    var percent = persen_calculate(
                                        money,
                                        tld_data_price_nodot
                                    );
                                    persent_html =
                                        '<span class="bg-[#EB5757] text-white text-xs font-medium ml-2 px-1 py-0.5 rounded">-' +
                                        percent +
                                        "%</span>";
                                    var annual_price_dot = tld_data_price
                                        .toString()
                                        .replace(
                                            /(\d)(?=(\d\d\d)+(?!\d))/g,
                                            "$1."
                                        );
                                    tld_price_html =
                                        '<div class="text-xs line-through text-[#828282]">' +
                                        annual_price_dot +
                                        " đ</div>";
                                }
                            }
                            var moneyDots = money
                                .toString()
                                .replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
                            var tool_tip_html = (label_html = "");
                            var data_tooltip_title =
                                tld_data.length > 0
                                    ? tld_data[0].indexOf(domain_tooltip_title)
                                    : -1;
                            var data_tooltip_des =
                                tld_data.length > 0
                                    ? tld_data[0].indexOf(domain_tooltip_des)
                                    : -1;
                            var data_label =
                                tld_data.length > 0
                                    ? tld_data[0].indexOf(domain_label)
                                    : -1;
                            if (
                                tld_data[findObjectByTLD(afterDot, tld_data)][
                                data_tooltip_title
                                ] !== ""
                            ) {
                                tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-green.svg" alt="doamin info green"/><span class="sr-only">Info</span>
                <div class="tool_tip_search absolute">
                  <div class="tooltiptext_search w-full relative">`;
                                tool_tip_html +=
                                    '<div class="text-xs text-left font-bold w-full">' +
                                    tld_data[
                                    findObjectByTLD(afterDot, tld_data)
                                    ][data_tooltip_title] +
                                    "</div>";
                                tool_tip_html +=
                                    '<div class="text-xs text-left font-normal mt-1 w-full">' +
                                    tld_data[
                                    findObjectByTLD(afterDot, tld_data)
                                    ][data_tooltip_des] +
                                    "</div>";
                                tool_tip_html += "</div></div></div>";
                            }
                            if (
                                data_label != -1 &&
                                tld_data[findObjectByTLD(afterDot, tld_data)][
                                data_label
                                ] !== ""
                            ) {
                                let str =
                                    tld_data[
                                    findObjectByTLD(afterDot, tld_data)
                                    ][data_label];
                                let firstCommaIndex = str.indexOf(",");
                                let beforeFirstComma = str.substring(
                                    0,
                                    firstCommaIndex
                                );
                                let afterFirstComma = str.substring(
                                    firstCommaIndex + 1
                                );
                                if (
                                    beforeFirstComma.toLowerCase() == "miễn phí"
                                ) {
                                    label_html =
                                        `<div class="free_tooltip relative float-right">
                <img class="free_tooltip_hover" src='https://vietnix.vn/wp-content/uploads/2023/11/free_golobal_domain.svg'>
                 <span class="free_tooltip_data absolute w-60 p-2 bg-white rounded-md text-sm">
                    ` +
                                        afterFirstComma +
                                        `
                  </span>
                 </div>`;
                                }
                            }
                            var domains = $("#cart_list").find(
                                ".vnx_trash_icon.remove_domain_cart"
                            );
                            var html =
                                '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res" ><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/check-domain.svg" alt="doamin checked"/><span class="sr-only">Check icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                                searchQuery +
                                label_html +
                                '</div></div><div class="inline-flex sm:mt-0 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-green-800 rounded-full bg-green-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền đang sẵn sàng cho bạn </span></div>' +
                                tool_tip_html +
                                '</div></div><div class="lg:ml-auto lg:inline-flex flex lg:flex-nowrap flex-wrap lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center lg:w-fit w-full lg:justify-center px-3 lg:ml-auto lg:mt-0 mt-4 price_text flex"><div class="text-left"><div class="font-sans font-semibold inline-flex text-[#828282] content-center items-center"><p class="text-[#F2994A] mr-1">' +
                                moneyDots +
                                " đ</p>/năm" +
                                persent_html +
                                "</div>" +
                                tld_price_html +
                                '</div></div><div class="items-center justify-center px-3 lg:w-fit w-full lg:ml-auto lg:mt-0 sm:ml-auto mt-4 ml-auto price_button"><button type="button" data-domain="' +
                                searchQuery +
                                '" class="text-white bg-[#38A7FF] hover:bg-[#38A7FF] font-medium rounded-lg text-sm px-3 py-2.5 focus:outline-none add_domain_cart w-full" style="min-width: 9rem">Thêm vào giỏ hàng</button></div></div></div>';
                            domains.each(function () {
                                var dataInfo = $(this).data("domain");
                                if (dataInfo === searchQuery) {
                                    html =
                                        '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res" ><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/check-domain.svg" alt="doamin checked"/><span class="sr-only">Check icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                                        searchQuery +
                                        label_html +
                                        '</div></div><div class="inline-flex sm:mt-0 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-green-800 rounded-full bg-green-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền đang sẵn sàng cho bạn </span></div>' +
                                        tool_tip_html +
                                        '</div></div><div class="lg:ml-auto lg:inline-flex flex lg:flex-nowrap flex-wrap lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center lg:w-fit w-full lg:justify-center px-3 lg:ml-auto lg:mt-0 mt-4 price_text flex"><div class="text-left"><div class="font-sans font-semibold inline-flex text-[#828282] content-center items-center"><p class="text-[#F2994A] mr-1">' +
                                        moneyDots +
                                        " đ</p>/năm" +
                                        persent_html +
                                        "</div>" +
                                        tld_price_html +
                                        '</div></div><div class="items-center justify-center px-3 lg:w-fit w-full lg:ml-auto lg:mt-0 sm:ml-auto mt-4 ml-auto price_button"><button type="button" data-domain="' +
                                        searchQuery +
                                        '" class="text-white font-medium rounded-lg text-sm px-3 py-2.5 focus:outline-none add_domain_cart cursor-not-allowed bg-[#81AFD3] w-full" style="min-width: 9rem"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Đã thêm</button></div></div></div>';
                                }
                            });
                            $("#vail_domain.domain_availble").html(html);
                        }
                    }
                } else if (
                    data !== null &&
                    data.message == "Domain not valid" &&
                    data.result == "error"
                ) {
                    var tool_tip_html = "";
                    var data_tooltip_title =
                        tld_data.length > 0
                            ? tld_data[0].indexOf(domain_tooltip_title)
                            : -1;
                    var data_tooltip_des =
                        tld_data.length > 0
                            ? tld_data[0].indexOf(domain_tooltip_des)
                            : -1;
                    if (
                        findObjectByTLD(afterDot, tld_data) != -1 &&
                        tld_data[findObjectByTLD(afterDot, tld_data)][
                        data_tooltip_title
                        ] !== ""
                    ) {
                        tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/><span class="sr-only">Info</span>
              <div class="tool_tip_search absolute">
                <div class="tooltiptext_search w-full relative">`;
                        tool_tip_html +=
                            '<div class="text-xs text-left font-bold w-full">' +
                            tld_data[findObjectByTLD(afterDot, tld_data)][
                            data_tooltip_title
                            ] +
                            "</div>";
                        tool_tip_html +=
                            '<div class="text-xs text-left font-normal mt-1 w-full">' +
                            tld_data[findObjectByTLD(afterDot, tld_data)][
                            data_tooltip_des
                            ] +
                            "</div>";
                        tool_tip_html += "</div></div></div>";
                    }
                    $("#vail_domain.domain_availble").html(
                        '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res"><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban"/><span class="sr-only">Error icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                        searchQuery +
                        '</div></div><div class="inline-flex sm:mt-1 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền đã được đăng ký</span></div>' +
                        tool_tip_html +
                        '</div></div><div class="lg:ml-auto inline-flex lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:ml-auto lg:mt-0 m-auto mt-4"></div></div></div>'
                    );
                } else {
                    var tool_tip_html = "";
                    var data_tooltip_des = `Hiện tên miền không khả dụng. Quý khách vui lòng tìm kiếm lại với một tên miền khác.`;
                    tool_tip_html = `<div class="search_tooltip"><img class="search_tooltip_icon" src = "https://vietnix.vn/wp-content/uploads/2023/06/circle-info-red.svg" alt="doamin info red"/><span class="sr-only">Info</span>
        <div class="tool_tip_search absolute domain_notvalid">
          <div class="tooltiptext_search w-full relative">`;
                    tool_tip_html +=
                        '<div class="text-xs text-left font-bold w-full"></div>';
                    tool_tip_html +=
                        '<div class="text-xs text-left font-normal mt-1 w-full">' +
                        data_tooltip_des +
                        "</div>";
                    tool_tip_html += "</div></div></div>";
                    $("#vail_domain.domain_availble").html(
                        '<div class="border-t border-[#DDE1E8] inline-flex w-full items-center py-3 flex-wrap"><div class="flex items-center query-domain-res"><div class="inline-block"><div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 rounded-full mx-2"><img src = "https://vietnix.vn/wp-content/uploads/2023/06/domain-ban.svg" alt="doamin ban"/><span class="sr-only">Error icon</span></div></div><div class="text-xl font-normal sm:mx-5 lg:block break-words domain_search_req inline-block">' +
                        searchQuery +
                        '</div></div><div class="inline-flex sm:mt-1 mt-3 ml-2"><div class="inline-flex px-3 py-1 items-center text-sm text-[#EB5757] rounded-full bg-red-50 relative" role="alert"><div class="mr-1"><span class="font-normal">Tên miền không hợp lệ</span></div>' +
                        tool_tip_html +
                        '</div></div><div class="lg:ml-auto inline-flex lg:mt-0 mt-3 price_domain_search lg:w-fit w-full ml-auto"><div class="items-center justify-center px-3 lg:ml-auto lg:mt-0 m-auto mt-4"></div></div></div>'
                    );
                }
                $(".loading_domain").hide();
            });
        } catch (error) {
            console.warn("Lỗi kiểm tra tên miền:", error);
        }
        enable_disable_Form(false);
    }
    if (suggest == true) {
        await suggest_domain(searchQuery, template);
    }
    return;
}

function post_ajax_Data(type, action, data) {
    return $.ajax({
        url: admin_ajax_url,
        type: type,
        dataType: "json",
        async: true,
        timeout: 40000,
        data: {
            action: action,
            data: data,
        },
    });
}
function post_ajax_Data_noAsync(type, action, data) {
    return $.ajax({
        url: admin_ajax_url,
        type: type,
        dataType: "json",
        async: false,
        timeout: 5000,
        data: {
            action: action,
            data: data,
        },
    });
}
function post_ajax_Data_search(type, action, data) {
    return $.ajax({
        url: admin_ajax_url,
        type: type,
        dataType: "json",
        async: true,
        timeout: 5000,
        data: {
            action: action,
            data: data,
        },
    });
}

function get_sessionStorage(name) {
    return JSON.parse(sessionStorage.getItem(name));
}

function show_suggestion(back, before_dot, tld) {
    var domains = $("#cart_list").find(".vnx_trash_icon.remove_domain_cart");
    var domain_data_array = get_sessionStorage("domain_Data");
    var tld_data = get_sessionStorage("TLD_Data");
    var money = domain_data_array["pricing"][tld].register[1].split(".")[0];
    var moneyDots = money.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
    var tld_data_price = "";
    var tld_data_price_nodot = "";
    var tool_tip_html = "";
    var data_price_index =
        tld_data.length > 0 ? tld_data[0].indexOf(domain_annual_price) : -1;
    var data_tooltip_title =
        tld_data.length > 0 ? tld_data[0].indexOf(domain_tooltip_title) : -1;
    var data_tooltip_des =
        tld_data.length > 0 ? tld_data[0].indexOf(domain_tooltip_des) : -1;

    if (findObjectByTLD(tld, tld_data) !== -1) {
        tld_data_price = tld_data[findObjectByTLD(tld, tld_data)][
            data_price_index
        ].replace(/\,/g, "");
        tld_data_price_nodot = tld_data_price.replace(/\./g, "");
        if (
            tld_data[findObjectByTLD(tld, tld_data)][data_tooltip_title] !== ""
        ) {
            tool_tip_html +=
                '<div class="tooltip relative"style="width: 10%"><i class="vnx_icon_info"></i><div class="tooltiptext bg-white text-black text-center py-1.5 px-2 rounded-md absolute w-full">';
            tool_tip_html +=
                '<div class="text-xs text-left font-bold w-full">' +
                tld_data[findObjectByTLD(tld, tld_data)][data_tooltip_title] +
                "</div>";
            tool_tip_html +=
                '<div class="text-xs text-left font-normal mt-1 w-full">' +
                tld_data[findObjectByTLD(tld, tld_data)][data_tooltip_des] +
                "</div>";
            tool_tip_html += "</div></div>";
        }
    }
    var persent_html = "";
    var persent_html_mobile = "";
    var tld_price_html = "";
    if (tld_data_price !== "") {
        if (!(money == tld_data_price_nodot)) {
            var percent = persen_calculate(money, tld_data_price_nodot);
            var annual_price_dot = tld_data_price
                .toString()
                .replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
            persent_html =
                '<span class="discount hidden lg:inline-flex">-' +
                percent +
                "%</span>";
            persent_html_mobile =
                '<div class="w-full lg:hidden"><span class="w-fit rounded bg-[#EB5757] py-1 px-5 text-xs font-medium text-white">-' +
                percent +
                "%</span></div>";
            tld_price_html =
                '<small class="line-through text-gray-400">' +
                annual_price_dot +
                " đ</small>";
        }
    }
    if (
        back !== null &&
        back.result === "success" &&
        back.status === "available"
    ) {
        if (
            back.premium != null &&
            back.premium.status == "available" &&
            back.premium.costHash != undefined
        ) {
            tool_tip_html = "";
            tool_tip_html +=
                '<div class="tooltip relative"style="width: 10%"><i class="vnx_icon_info special_domain"></i><div class="tooltiptext bg-white text-black text-center py-1.5 px-2 rounded-md absolute w-full">';
            tool_tip_html +=
                '<div class="text-xs text-left font-bold w-full"></div>';
            tool_tip_html +=
                '<div class="text-xs text-left font-normal mt-1 w-full">Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký</div>';
            tool_tip_html += "</div></div>";
            $("#show-domain-suggest").append(
                `<div class="box"> <div class="box_general flex flex-row items-center"> <div class="sub_box first flex items-center flex-wrap">
      <p class="domains inline-flex items-center break-all" style="max-width: 85%">` +
                before_dot +
                `<span class="dots" style="word-break: keep-all">.` +
                tld +
                `</span> </p>
    </div><div class="sub_box second special flex flex-row justify-between items-center gap-1"><div class="text_sale_price"><span class="info block items-center bg-[#FFF0BA] text-[#CE6A00] gap-1">Tên miền đặc biệt ` +
                tool_tip_html +
                `</span></div><div class="button bg-transparent"><button class="button_detail btn_tawk">
      <span class="hidden lg:block">Liên hệ</span>
      <img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/support_chat.svg" alt="add to cart icon">
      </button></div></div></div ></div >`
            );
        } else {
            var html =
                `<div class="box">
      <div class="box_general flex flex-row items-center">
        <div class="sub_box first flex items-center flex-wrap">
          <p class="domains inline-flex items-center break-all" style="max-width: 85%">` +
                before_dot +
                `<span class="dots" style="word-break: keep-all">.` +
                tld +
                `</span> </p>
            ` +
                tool_tip_html +
                `
            ` +
                persent_html_mobile +
                `
        </div>
        <div class="sub_box second flex flex-row justify-between items-center">
          <div class="text_sale_price"> ` +
                moneyDots +
                ` VND/<span
              class="year">Năm</span>` +
                persent_html +
                `</br>` +
                tld_price_html +
                `</div>
          <div class="button bg-transparent"><button class="button_detail add_domain_cart"
              data-domain="` +
                before_dot +
                `.` +
                tld +
                `">
              <span class="hidden lg:block">Thêm vào giỏ hàng</span>
              <img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/add_cart_mobile.svg" alt="add to cart icon">
              </button></div>
        </div>
      </div>
    </div>`;
            domains.each(function () {
                var dataInfo = $(this).data("domain");
                if (dataInfo === before_dot + "." + tld) {
                    html =
                        `<div class="box">
          <div class="box_general flex flex-row">
            <div class="sub_box first flex items-center flex-wrap">
              <p class="domains inline-flex items-center break-all" style="max-width: 85%">` +
                        before_dot +
                        `<span class="dots" style="word-break: keep-all">.` +
                        tld +
                        `</span> </p>
                ` +
                        tool_tip_html +
                        `
            </div>
            <div class="sub_box second flex flex-row justify-between items-center">
              <div class="text_sale_price"> ` +
                        moneyDots +
                        ` VND/<span class="year">Năm</span>` +
                        persent_html +
                        `</br>
                ` +
                        tld_price_html +
                        `</div>
              <div class="button bg-transparent"><button class="button_detail add_domain_cart cursor-not-allowed lg:bg-[#81AFD3] bg-[#38A7FF] lg:opacity-100 opacity-50"
                  data-domain="` +
                        before_dot +
                        `.` +
                        tld +
                        `" disabled>
                  <span class="hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                  </svg> Đã thêm</span>
                  <img class="m-auto lg:hidden block" src="https://vietnix.vn/wp-content/uploads/2023/08/added_cart_icon.svg" alt="added to cart icon">
                  </button></div>
            </div>
          </div>
        </div>`;
                }
            });
            $("#show-domain-suggest").append(html);
        }
    }
    // else if (back !== null && back.result === 'success' && back.status === 'unavailable') {
    //   $('#show-domain-suggest').append('<div class="box"> <div class="box_general flex flex-row items-center"> <div class="sub_box first"> <p class="domains inline-flex items-center" >' + before_dot + '<span class="dots">.' + tld + '</span></p></div><div class="sub_box second flex flex-row md:justify-between justify-start md:mt-0 mt-5"> <div class="text_sale_price"><span class="year"></span></br> </div><div class="button bg-transparent"><span class="info flex items-center">Tên miền đã được đăng ký</span></div></div></div ></div >');
    // }
    // else {
    //   $('#show-domain-suggest').append('<div class="box"> <div class="box_general flex flex-row items-center"> <div class="sub_box first"> <p class="domains inline-flex items-center" >' + before_dot + '<span class="dots">.' + tld + '</span></p></div><div class="sub_box second flex flex-row md:justify-between justify-start md:mt-0 mt-5"> <div class="text_sale_price"><span class="year"></span></br> </div><div class="button bg-transparent"><span class="info flex items-center">Tên miền đã được đăng ký</span></div></div></div ></div >');
    // }
}

function findObjectByTLD(tld, myArray) {
    foundIndex = myArray.findIndex((obj) => obj[0] === tld);
    if (foundIndex !== -1) {
        return foundIndex;
    } else {
        return -1;
    }
}

function persen_calculate(discount_price, price) {
    var percent = 100 - (parseInt(discount_price) / parseInt(price)) * 100;
    return parseFloat(percent).toFixed(0);
}

function generateRandomString(length) {
    const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let result = "";

    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        result += characters.charAt(randomIndex);
    }

    return result;
}
