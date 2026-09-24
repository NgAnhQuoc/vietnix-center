import { createTagInput } from "./vnx_tag_input";

(function ($) {
  "use strict";
  window.addEventListener("DOMContentLoaded", (event) => {
    var seo_channel = document.querySelector('input[name="seo_channel"]');
    createTagInput(seo_channel);

    var social_channel = document.querySelector('input[name="social_channel"]');
    createTagInput(social_channel);
  });
})(jQuery);
