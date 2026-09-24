window.jQuery = window.$ = jQuery;
class STYLE_BY_SCRIPT {
    // Make SingleTon to get data form it class STYLE_BY_SCRIPT
    static Ins() {
        if (!STYLE_BY_SCRIPT.instance) {
            STYLE_BY_SCRIPT.instance = new STYLE_BY_SCRIPT();
        }
        return STYLE_BY_SCRIPT.instance;
    }
    constructor() {
        this.headTag;
        this.styleData;
    }
    Init() {
        const self = this;
        self.setHeadTag();
        self.setStyleData();
        self.addStyles();
    }
    setHeadTag() {
        this.headTag = $("head").first();
    }
    setStyleData() {
        this.styleData = JSON.parse(add_stylesheet_arr.style_data);
    }
    addStyles() {
        const self = this;
        const datas = self.styleData;
        datas.forEach((data) => {
            const thisID = data.id;
            const thisHref = data.href;
            const addTag =
                '<link rel="stylesheet" id="' +
                thisID +
                '" href="' +
                thisHref +
                '" media="all" />';
            $(self.headTag).append(addTag);
        });
    }
}
$(document).ready(function () {
    STYLE_BY_SCRIPT.Ins().Init();
});
