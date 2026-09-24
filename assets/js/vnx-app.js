import CustomCodeBlock from "./components/CustomCodeBlock";
import PolicyTemplate from "./components/Policy";

import "alpinejs";

window.addEventListener("DOMContentLoaded", (event) => {
  PolicyTemplate.init();
  // Tawk_to.init();
});

jQuery.event.special.touchstart = {
  setup: function( _, ns, handle ){
    this.addEventListener("touchstart", handle, { passive: true });
  }
};