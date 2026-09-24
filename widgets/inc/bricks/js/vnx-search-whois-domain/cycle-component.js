if ($(".vnx-cycle-component-whois-domain").length != 0) {
  new Vue({
    el: ".vnx-cycle-component-whois-domain",
    data: {
      resultWhoisDomain: null,
      resultAvailableDomain: null,
      isLoading: false,
    },

    mounted() {
      const elements = document.querySelectorAll(
        ".vnx-cycle-component-whois-domain .vnx-sidebar-xscroll"
      );
      initPerfectScrollbar(elements);
      this.$eventBus.$on("vnx-emit-submit-whois", (domain) => {
        this.isLoading = true;
      });

      this.$eventBus.$on("vnx-emit-result-whois", (data) => {
        this.isLoading = false;
        this.resultWhoisDomain = data;
      });

      this.$eventBus.$on("vnx-emit-available-domain", (data) => {
        //     data={
        //     "domainName": "caole.vn",
        //     "isAvailable": true,
        //     "legacyStatus": "available",
        //     "isPremium": false,
        //     "sld": "caole",
        //     "tld": "vn",
        //     "currency": "VND",
        //     "pricing": {
        //         "categories": [
        //             "Other"
        //         ],
        //         "group": "",
        //         "register": {
        //             "1": "558000.00",
        //             "2": "1016000.00",
        //             "3": "1474000.00",
        //             "4": "1932000.00",
        //             "5": "2390000.00"
        //         },
        //         "renew": {
        //             "1": "558000.00",
        //             "2": "1016000.00",
        //             "3": "1474000.00",
        //             "4": "1932000.00",
        //             "5": "2390000.00"
        //         }
        //     }
        // }
        this.resultAvailableDomain = data;
      });
    },

    methods: {},
  });
}
