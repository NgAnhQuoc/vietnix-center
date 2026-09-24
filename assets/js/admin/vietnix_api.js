import { createTagInput } from "./vnx_tag_input";

(function ($) {
    "use strict";
    window.addEventListener("DOMContentLoaded", (event) => {
        var allow_api = document.querySelector('input[name="allow_api"]');
        createTagInput(allow_api);
    });
})(jQuery);
