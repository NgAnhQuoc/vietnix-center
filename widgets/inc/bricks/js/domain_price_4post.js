window.jQuery = window.$ = jQuery;
$(document).ready(function () {
    vnxDomainPrice4post();
});
function vnxDomainPrice4post() {
    bricksQuerySelectorAll(document, ".brxe-vnx-domain-price-table").forEach(
        function (element) {
            if (
                $(element).hasClass("is-active-element") ||
                !$(element).hasClass("is-initialized")
            ) {
                view = new vnxDomainPrice4postFn(element);
            }
        }
    );
}
class vnxDomainPrice4postFn {
    constructor(element) {
        this.element = element;
        this.hoverTooltip();
        this.changeTab();
        this.mobileSelectTab();
        this.onExpandClick();
        this.onCollapseClick();
        $(element).addClass("is-initialized");
        console.log("vnxDomainPrice4post Script Initialized");
    }

    hoverTooltip = function () {
        const self = this;
        const wrapper = $(self.element).find(".vnx_wrapper");
        if (!wrapper.length) return;

        $(self.element).on("mouseover", ".vnx_tooltip", function () {
            const viewWidth = $(window).width();
            const wrapperWidth = wrapper.width();
            const wrapperOffset = wrapper.offset();
            const wrapperOffsetRight = wrapperOffset.left + wrapperWidth;
            const thisOffsetLeft = $(this).offset().left;
            const tooltipContent = $(this).find(".vnx_tooltip_content");
            $(tooltipContent).addClass("active");
            const tooltipContentWidth = tooltipContent.width();
            const tooltipContentOffset = tooltipContent.offset();
            const tooltipContentOffsetLeft = tooltipContentOffset.left;
            const tooltipContentOffsetRight =
                tooltipContentOffsetLeft + tooltipContentWidth;

            // if tooltip_content is out of the bottom of wrapper => set tooltip_content bottom is 20px
            if (
                tooltipContentOffset.top + tooltipContent.height() >
                wrapperOffset.top + wrapper.height()
            )
                tooltipContent.css({ top: "unset", bottom: "20px" });

            if (viewWidth >= 1024) return;

            if (tooltipContent.hasClass("domain_tooltip")) {
                if (tooltipContentOffsetRight > wrapperOffsetRight)
                    tooltipContent.css({ left: "unset", right: "0" });
            }
            if (tooltipContent.hasClass("price_tooltip"))
                if (tooltipContentOffsetLeft < 0) {
                    const leftValue =
                        viewWidth - thisOffsetLeft - tooltipContentWidth;
                    tooltipContent.css({
                        left: leftValue,
                        right: "unset",
                    });
                }
        });

        $(self.element).on("mouseout", ".vnx_tooltip", function (e) {
            const tooltipContent = $(this).find(".vnx_tooltip_content");
            $(tooltipContent).removeClass("active");
            tooltipContent.css({
                top: "",
                bottom: "",
                left: "",
                right: "",
                transform: "",
            });
        });
    };

    setMobileTabTille = function (thisEventTarget) {
        const tabHeaderWrap = $(thisEventTarget).closest(
            ".vnx_tab_header_wrap"
        );
        const mobileTabText = $(tabHeaderWrap).find(
            ".vnx_mobile_tab_header span"
        );
        const thisTableHeader = $(tabHeaderWrap).find(".vnx_table_header");
        $(mobileTabText).text($(thisEventTarget).text());
        $(thisTableHeader).addClass("hidden");
    };

    changeTab = function () {
        const self = this;
        $(self.element).on("click", ".vnx_tab_title", function (e) {
            e.preventDefault();
            const thisTarget = $(this).attr("data-target");
            self.switchActiveTabTitle(thisTarget);
            self.switchActiveTabContent(thisTarget);
            self.setMobileTabTille(this);
        });
    };

    switchActiveTabTitle = function (thisTarget) {
        const self = this;
        $(self.element).find(".vnx_tab_title.active").removeClass("active");
        $(self.element)
            .find(".vnx_tab_title[data-target='" + thisTarget + "']")
            .addClass("active");
    };

    switchActiveTabContent = function (thisTarget) {
        const self = this;
        $(self.element).find(".vnx_table_content.active").removeClass("active");
        $(self.element)
            .find(".vnx_table_content." + thisTarget)
            .addClass("active");
    };

    mobileSelectTab = function () {
        const self = this;
        $(self.element).on("click", ".vnx_mobile_tab_header", function (e) {
            e.preventDefault();
            const thisParent = $(this).parent();
            $(thisParent).find(".vnx_table_header").removeClass("hidden");
        });

        // on click outside of mobile tab header
        $(document).on("click", function (e) {
            const thisEventTarget = e.target;
            const thisWrap = $(thisEventTarget).closest(".vnx_tab_header_wrap");
            if ($(thisEventTarget).hasClass("vnx_tab_header_wrap")) return;
            if (!thisWrap.length) $(".vnx_table_header").addClass("hidden");
        });
    };

    onExpandClick = function () {
        const self = this;
        $(self.element).on(
            "click",
            ".vnx_table_expand .vnx_toggle_table",
            function (e) {
                e.preventDefault();
                const thisTable = $(this).closest(".vnx_table_content");
                const thisCollapseBtn = $(thisTable).find(
                    ".vnx_table_collapse"
                );
                $(this).closest(".vnx_table_expand").addClass("hidden");
                $(thisTable).find(".vnx_mobile_hidden").removeClass("hidden");
                $(thisCollapseBtn).removeClass("hidden");
            }
        );
    };

    onCollapseClick = function () {
        const self = this;
        $(self.element).on(
            "click",
            ".vnx_table_collapse .vnx_toggle_table",
            function (e) {
                e.preventDefault();
                const thisTable = $(this).closest(".vnx_table_content");
                const thisExpandBtn = $(thisTable).find(".vnx_table_expand");
                $(this).closest(".vnx_table_collapse").addClass("hidden");
                $(thisTable).find(".vnx_mobile_hidden").addClass("hidden");
                $(thisExpandBtn).removeClass("hidden");
            }
        );
    };
}
