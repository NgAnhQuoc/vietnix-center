window.jQuery = window.$ = jQuery;
$(document).ready(function () {
    try {
        vnxBonusServiceTimeScript();
    } catch (e) {
        console.error("Error initializing vnxBonusServiceTimeScript: ", e);
    }
});
function vnxBonusServiceTimeScript() {
    bricksQuerySelectorAll(document, ".brxe-vnx-bonus-service-time").forEach(
        function (element) {
            if (
                $(element).hasClass("is-active-element") ||
                !$(element).hasClass("is-initialized")
            ) {
                view = new vnxBonusServiceTimeScriptFn(element);
            }
        }
    );
}
class vnxBonusServiceTimeScriptFn {
    constructor(element) {
        this.element = element;
        this.tabTitleBar = $(element).find(".vnx_bst_tab_title");
        this.vnxSplide = $(element).find(".vnx_splide");
        this.vnxSpliderChild = this.vnxSplide.children();
        this.slide = null;
        this.timeOut;
        this.windowWidth;
        this.onClickTabTitle();
        setTimeout(() => {
            this.scrollTabTitle();
        });
        this.onResizeWindow();
        $(element).addClass("is-initialized");
    }

    onClickTabTitle() {
        const self = this;
        self.tabTitleBar.on("click", ".vnx_bst_tab_title_item", function (e) {
            e.preventDefault();
            const target = $(this).data("target");

            setTimeout(function () {
                self.tabTitleBar.find(".active").removeClass("active");
                const thisTarget = e.currentTarget;
                $(thisTarget).addClass("active");
            });

            setTimeout(function () {
                $(self.element)
                    .find(".vnx_bst_tab_content.active")
                    .removeClass("active");
                $(self.element)
                    .find(".vnx_bst_tab_content." + target)
                    .addClass("active");
            });
        });
    }

    scrollTabTitle() {
        const self = this;
        const vnxSpliderWidth = self.vnxSplide.width();
        let vnxSpliderChildWidth = 0;
        self.vnxSpliderChild.each(function (index, item) {
            vnxSpliderChildWidth += $(item).outerWidth(true);
        });
        self.windowWidth = window.innerWidth;
        let gap = self.windowWidth < 768 ? 16 : 24;
        vnxSpliderChildWidth += (self.vnxSpliderChild.length - 1) * gap;
        if (vnxSpliderWidth < vnxSpliderChildWidth) {
            self.vnxSplide.removeClass("justify-center");
            // case mobile
            if (self.windowWidth < 1024) {
                self.destroySlide();
            }
            // case desktop
            else {
                self.initSlide();
            }
        } else {
            self.destroySlide();
            self.vnxSplide.addClass("justify-center");
        }
    }

    initSlide() {
        const self = this;
        if (self.slide) return;
        if (!self.vnxSplide) return;
        if (jQuery(self.vnxSplide).hasClass("splide_active")) return;
        var splide__slide;
        var splide__list;
        self.vnxSplide.removeClass("flex");
        jQuery(self.vnxSplide).addClass("splide_active");
        jQuery(self.vnxSplide)
            .children()
            .wrap('<div class="splide__slide"></div>');
        splide__slide = jQuery(self.vnxSplide).find(".splide__slide");
        jQuery(splide__slide).wrapAll('<div class="splide__list" />');
        splide__list = jQuery(self.vnxSplide).find(".splide__list");
        jQuery(splide__list).wrap('<div class="splide"></div>');
        jQuery(splide__list).wrap('<div class="splide__track"></div>');

        self.slide = new Splide(".brxe-vnx-bonus-service-time .splide", {
            type: "slide",
            autoplay: false,
            autoWidth: true,
            perMove: 1,
            focus: 0,
            gap: "24px",
            pagination: false,
            arrows: false,
        });
        self.slide.mount();
        console.log("initSlide");
    }

    destroySlide() {
        const self = this;
        if (!self.tabTitleBar) return;
        if (!self.slide) return;
        self.slide.destroy();

        // unwrap splide__list by splide__track
        jQuery(self.tabTitleBar).find(".splide__list").unwrap();

        // unwrap splide__track by vnx_coupon_tab_title_slide
        jQuery(self.tabTitleBar).find(".splide__list").unwrap();

        // unwrap splide__slide by splide__list
        jQuery(self.tabTitleBar).find(".splide__slide").unwrap();

        // unwrap vnx_bst_tab_title_item by splide__slide
        jQuery(self.tabTitleBar).find(".vnx_bst_tab_title_item").unwrap();

        jQuery(self.tabTitleBar)
            .find(".vnx_splide")
            .removeClass("splide_active");

        self.slide = null;

        self.vnxSplide.addClass("flex");
        console.log("destroySlide");
    }

    onResizeWindow() {
        const self = this;
        window.addEventListener("resize", () => {
            clearTimeout(self.timeOut);
            self.timeOut = setTimeout(() => {
                self.scrollTabTitle();
            }, 100);
        });
    }
}
