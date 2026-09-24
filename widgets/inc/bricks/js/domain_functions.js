window.jQuery = window.$ = jQuery;
class VNX_DOMAIN_FUNCTIONS {
    // Make SingleTon to get data form it VNX_WHOIS
    static Ins() {
        if (!VNX_DOMAIN_FUNCTIONS.instance) {
            VNX_DOMAIN_FUNCTIONS.instance = new VNX_DOMAIN_FUNCTIONS();
        }
        return VNX_DOMAIN_FUNCTIONS.instance;
    }
    domainBeautified(domain) {
        domain = domain.toLowerCase();
        domain = domain.trim();
        return domain;
    }
    checkDomainFormat(domain) {
        var domainWithoutTLDRegex = /^[a-zA-Z0-9-]+$/;
        var domainWithTLDRegex = /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        var result = {
            is_domain: false,
            tld: false,
        };
        if (domainWithTLDRegex.test(domain)) {
            result.is_domain = true;
            result.tld = true;
        } else if (domainWithoutTLDRegex.test(domain)) {
            result.is_domain = true;
        }

        return result;
    }

    getUrlParameter = (vnxParam) => {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split("&"),
            vnxParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            vnxParameterName = sURLVariables[i].split("=");

            if (vnxParameterName[0] === vnxParam) {
                return vnxParameterName[1] === undefined
                    ? true
                    : decodeURIComponent(vnxParameterName[1]);
            }
        }
        return false;
    };

    modifyHistory = (domain) => {
        const self = this;
        if (self.getUrlParameter("domain") == domain) return;
        if (!$.isFunction(replaceUrlParam) || !$.isFunction(addUrlParam)) {
            return false;
        }
        var newUrl;
        if (window.location.search.indexOf("domain=") !== -1) {
            newUrl = replaceUrlParam("domain", encodeURIComponent(domain));
        } else {
            newUrl = addUrlParam("domain", encodeURIComponent(domain));
        } // replace domain parameter, if dont have domain parameter -> add it
        window.history.pushState({ path: newUrl }, "", newUrl); // add history to use back button on browser
        return true;
    };

    
}
