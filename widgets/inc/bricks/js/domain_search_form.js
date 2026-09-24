window.jQuery = window.$ = jQuery;
class VNX_DOMAIN_ONPAGE {
    // Make SingleTon to get data form it VNX_WHOIS
    static Ins() {
        if (!VNX_DOMAIN_ONPAGE.instance) {
            VNX_DOMAIN_ONPAGE.instance = new VNX_DOMAIN_ONPAGE();
        }
        return VNX_DOMAIN_ONPAGE.instance;
    }
    constructor() {
        this.form;
        this.domain;
    }
    seachFormInit(form) {
        const self = this;
        self.form = form;
        const thisInput = $(form).find("input");
        const inputVal = $(thisInput).val();
        if (inputVal == "") {
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
            return;
        }
        const domain = VNX_DOMAIN_FUNCTIONS.Ins().domainBeautified(inputVal);
        self.domain = domain;
        // VNX_DOMAIN_FUNCTIONS.Ins().modifyHistory(domain);
        domain_check(domain);
    }
}

$(document).ready(function () {
    vnxDomainSearchRedirect();
    vnxDomainSearchOnpage();
    vnxDomainSearchPageWhois();
});
function vnxDomainSearchRedirect() {
    if ($(".brxe-vnx-search-domain-form.redirect").length == 0) return;
    const loading_small =
        '<div class="loading_small flex flex-col items-center justify-center absolute w-full left-0 top-0 z-1 h-full"><div class="loading_wrapper"><div class="loading_icon"></div></div></div>';
    bricksQuerySelectorAll(
        document,
        ".brxe-vnx-search-domain-form.redirect"
    ).forEach(function (scope) {
        $(scope).on("submit", "form", function (e) {
            const notice_wrap = $(this).find(".vnx_notice");
            e.preventDefault();
            const thisWrapper = $(this).find(".vnx_wrapper");
            const thisInput = $(this).find("input");
            inputVal = $(thisInput).val();
            const domain =
                VNX_DOMAIN_FUNCTIONS.Ins().domainBeautified(inputVal);
            const domain_format =
                VNX_DOMAIN_FUNCTIONS.Ins().checkDomainFormat(domain);
            if (!domain_format.is_domain) {
                $(notice_wrap)
                    .find(".vnx_notice_text")
                    .html("Tên miền không hợp lệ!");
                $(this).find(notice_wrap).removeClass("hidden");
                setTimeout(function () {
                    $(notice_wrap).addClass("hidden");
                }, 2000);
            } else {
                $(thisWrapper).append(loading_small);
                $(thisWrapper).trigger("click");
                $(thisInput).val(domain);
                e.currentTarget.submit();
            }
        });
    });
}
function vnxDomainSearchOnpage() {
    if ($(".brxe-vnx-search-domain-form.onpage").length == 0) return;
    const loading_small =
        '<div class="loading_small flex flex-col items-center justify-center absolute w-full left-0 top-0 z-1 h-full"><div class="loading_wrapper"><div class="loading_icon"></div></div></div>';
    var index = 0;
    bricksQuerySelectorAll(
        document,
        ".brxe-vnx-search-domain-form.onpage"
    ).forEach(function (scope) {
        if (
            index == 0 &&
            VNX_DOMAIN_FUNCTIONS.Ins().getUrlParameter("domain")
        ) {
            // console.log("Has domain parameter");
            VNX_DOMAIN_ONPAGE.Ins().seachFormInit(jQuery(scope).find("form"));
        } else {
            // console.log("Has no domain parameter");
        }
        $(scope).on("click", ".clear_icon", function () {
            $(".brxe-vnx-search-domain-form.onpage form input").val("");
        });
        $(scope).on("submit", "form", function (e) {
            e.preventDefault();
            VNX_DOMAIN_ONPAGE.Ins().seachFormInit(this);
        });
        index++;
    });
}
function vnxDomainSearchPageWhois() {
    if ($(".brxe-vnx-search-domain-form.page-whois").length == 0) return;
    const loading_small =
        '<div class="loading_small flex flex-col items-center justify-center absolute w-full left-0 top-0 z-1 h-full"><div class="loading_wrapper"><div class="loading_icon"></div></div></div>';
    var index = 0;
    bricksQuerySelectorAll(
        document,
        ".brxe-vnx-search-domain-form.page-whois"
    ).forEach(function (scope) {
        if (
            index == 0 &&
            VNX_DOMAIN_FUNCTIONS.Ins().getUrlParameter("domain")
        ) {
            // console.log("Has domain parameter");
            VNX_DOMAIN_ONPAGE.Ins().seachFormInit(jQuery(scope).find("form"));
        } else {
            // console.log("Has no domain parameter");
        }
        $(scope).on("click", ".clear_icon", function () {
            $(".brxe-vnx-search-domain-form.onpage form input").val("");
        });
        $(scope).on("submit", "form", function (e) {
            e.preventDefault();
            VNX_DOMAIN_ONPAGE.Ins().seachFormInit(this);
        });
        index++;
    });
}
