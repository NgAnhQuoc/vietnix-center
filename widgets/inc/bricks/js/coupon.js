window.jQuery = window.$ = jQuery;
$(document).ready(function () {
    vnxCouponScript();
});
function vnxCouponScript() {
    bricksQuerySelectorAll(document, ".brxe-vnx-coupon").forEach(function (
        element
    ) {
        if (
            $(element).hasClass("is-active-element") ||
            !$(element).hasClass("is-initialized")
        ) {
            view = new vnxCouponScriptFn(element);
        }
    });
}
class vnxCouponScriptFn {
    constructor(element) {
        this.element = element;
        this.copyCouponBtn = $(element).find(".vnx_copy_coupon");
        this.couponCode = $(element).find(".vnx_coupon_code");
        this.copyText = $(this.copyCouponBtn).data("text");
        this.copiedText = $(this.copyCouponBtn).data("clicked");
        this.timeOut;
        console.log(this.copyText, this.copiedText);

        this.onCopyCouponClicked();
        $(element).addClass("is-initialized");
        console.log("vnxCouponScript Script Initialized");
    }

    onCopyCouponClicked() {
        const self = this;
        $(self.copyCouponBtn).on("click", function (e) {
            e.preventDefault();
            if ($(this).hasClass("vnx_disabled")) return;
            // add the coupon code to the clipboard
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(self.couponCode).text()).select();
            document.execCommand("copy");
            $temp.remove();

            self.disableCopyButton();
            self.timeOut = setTimeout(function () {
                self.enableCopyButton();
            }, 5000);
        });
    }

    disableCopyButton() {
        $(this.copyCouponBtn).text(this.copiedText);
        $(this.copyCouponBtn).addClass("vnx_disabled");
    }

    enableCopyButton() {
        $(this.copyCouponBtn).text(this.copyText);
        $(this.copyCouponBtn).removeClass("vnx_disabled");
        clearTimeout(this.timeOut);
    }
}
