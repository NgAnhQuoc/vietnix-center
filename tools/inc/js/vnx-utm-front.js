window.jQuery = window.$ = jQuery;
document.addEventListener("DOMContentLoaded", () => {
    var _vnx = _vnx || {};
    _vnx.domain = ".vietnix.vn";
    if (typeof utm_array.cookie_domain !== "undefined") {
        _vnx.domain = utm_array.cookie_domain;
    }
    _vnx.cookieExpiryDays = utm_array.cookies_age;
    _vnx.additional_params_map = {
        gclid: "IGCLID",
        msclkid: "IMSCLKID",
        fbclid: "IFBCLID",
        place: "IPLACE",
        net: "INET",
        match: "IMATCH",
    };
    var UtmCookie = window.UtmCookie;
    var tags = document.querySelectorAll(
        'a[href^="https://portal.vietnix.vn/"]'
    );
    var utm_param = [],
        data_detect = window.determinedChanel(),
        chanel = data_detect[0],
        medium = data_detect[1];

    var social_chanel_names = ["social", "facebooksc", "youtubesc", "zalosc"];

    if (chanel == "seo" || social_chanel_names.includes(chanel)) {
        if (!UtmCookie.getParameterByName("utm_source")) {
            UtmCookie.writeCookie("utm_source", chanel);
        }
        if (!UtmCookie.utmPresentInUrl()) {
            if (!UtmCookie.getParameterByName("utm_medium")) {
                UtmCookie.writeCookie("utm_medium", medium);
            }
            if (!UtmCookie.getParameterByName("utm_campaign")) {
                UtmCookie.writeCookie("utm_campaign", "-");
            }
        }
    } else if (chanel == "direct") {
        // if (!UtmCookie.utmPresentInUrl()) {
        if (!UtmCookie.getParameterByName("utm_source")) {
            UtmCookie.writeCookie("utm_source", "direct");
        }
        if (!UtmCookie.getParameterByName("utm_medium")) {
            UtmCookie.writeCookie("utm_medium", "-");
        }
        if (!UtmCookie.getParameterByName("utm_campaign")) {
            UtmCookie.writeCookie("utm_campaign", "-");
        }
        // }
    } else if (chanel == "ads") {
        if (!UtmCookie.getParameterByName("utm_source")) {
            UtmCookie.writeCookie("utm_source", "ads");
        }
        if (!UtmCookie.getParameterByName("utm_medium")) {
            UtmCookie.writeCookie("utm_medium", "-");
        }
        if (!UtmCookie.getParameterByName("utm_campaign")) {
            UtmCookie.writeCookie("utm_campaign", "-");
        }
    } else if (chanel == "referral") {
        if (!UtmCookie.utmPresentInUrl()) {
            if (!UtmCookie.getParameterByName("utm_source")) {
                UtmCookie.writeCookie("utm_source", "referral");
            }
            if (!UtmCookie.getParameterByName("utm_medium")) {
                UtmCookie.writeCookie("utm_medium", medium);
            }
            if (!UtmCookie.getParameterByName("utm_campaign")) {
                UtmCookie.writeCookie("utm_campaign", "-");
            }
        }
    } else if (chanel == "unknow") {
        if (!UtmCookie.utmPresentInUrl()) {
            if (!UtmCookie.getParameterByName("utm_source")) {
                UtmCookie.writeCookie("utm_source", "-");
            }
            if (!UtmCookie.getParameterByName("utm_medium")) {
                UtmCookie.writeCookie("utm_medium", "-");
            }
            if (!UtmCookie.getParameterByName("utm_campaign")) {
                UtmCookie.writeCookie("utm_campaign", "-");
            }
        }
    }

    if (UtmCookie.readCookie("utm_source")) {
        var utm_source = decodeURIComponent(UtmCookie.readCookie("utm_source"));
        utm_source = Array.from(new Set(utm_source.split(","))).toString();
        utm_param["utm_source"] = encodeURIComponent(utm_source);
    }
    if (UtmCookie.readCookie("utm_medium")) {
        var utm_medium = decodeURIComponent(UtmCookie.readCookie("utm_medium"));
        utm_medium = Array.from(new Set(utm_medium.split(","))).toString();
        utm_param["utm_medium"] = encodeURIComponent(utm_medium);
    }
    if (UtmCookie.readCookie("utm_campaign")) {
        var utm_campaign = decodeURIComponent(
            UtmCookie.readCookie("utm_campaign")
        );
        utm_campaign = Array.from(new Set(utm_campaign.split(","))).toString();
        utm_param["utm_campaign"] = encodeURIComponent(utm_campaign);
    }

    if (
        utm_param["utm_source"] ||
        utm_param["utm_medium"] ||
        utm_param["utm_campaign"]
    ) {
        for (i = 0; i < tags.length; i++) {
            if (utm_param["utm_source"]) {
                tags[i].href = updateQueryStringParameter(
                    tags[i].href,
                    "utm_source",
                    utm_param["utm_source"]
                );
            }
            if (utm_param["utm_medium"]) {
                tags[i].href = updateQueryStringParameter(
                    tags[i].href,
                    "utm_medium",
                    utm_param["utm_medium"]
                );
            }
            if (utm_param["utm_campaign"]) {
                tags[i].href = updateQueryStringParameter(
                    tags[i].href,
                    "utm_campaign",
                    utm_param["utm_campaign"]
                );
            }
        }
    }

		$('.vnx_button_price').on('click', 'a#vnx_button_price', function(event) {
			event.preventDefault();

			var last_button = jQuery(this);
			var last_button_text = last_button.text();
			UtmCookie.writeCookie("utm_last_button", last_button_text);

			console.log("utm_last_button added:", last_button_text);

			var href = last_button.attr('href');
			window.location.href = href;
		});
});

function determinedChanel() {
    var chanel = "unknow";
    var medium = "-";

    var seo_channel = utm_array.seo_channel;
    var social_channel = utm_array.social_channel;

    var arr_seo_channel = seo_channel.split(",");
    var arr_social_channel = social_channel.split(",");

    if (UtmCookie.getParameterByName("gclid")) {
        chanel = "ads";
    } else if (UtmCookie.getParameterByName("gidzl")) {
        chanel = "zalosc";
    } else if (
        document.referrer &&
        document.referrer.indexOf(document.location.hostname) == -1
    ) {
        chanel = "referral";
        jQuery.each(arr_seo_channel, function (key, value) {
            if (value) {
                if (value.split(".")[1] == "*") {
                    if (document.referrer.includes(value.split(".")[0] + ".")) {
                        chanel = "seo";
                    }
                } else {
                    if (document.referrer.includes(value)) {
                        chanel = "seo";
                    }
                }
            }
        });

        jQuery.each(arr_social_channel, function (key, value) {
            if (value) {
                if (value.split(".")[1] == "*") {
                    if (document.referrer.includes(value.split(".")[0] + ".")) {
                        var thisChanel = "social";
                        const referrer_domain = value.split(".")[0];
                        switch (referrer_domain) {
                            case "facebook":
                                thisChanel = "facebooksc";
                                break;

                            case "youtube":
                                thisChanel = "youtubesc";
                                break;

                            case "zalo":
                                thisChanel = "zalosc";
                                break;

                            default:
                                thisChanel = "social";
                                break;
                        }
                        chanel = thisChanel;
                    }
                } else {
                    if (document.referrer.includes(value)) {
                        chanel = "social";
                    }
                }
            }
        });
        medium = UtmCookie.extractDomain(document.referrer);
    } else if (document.referrer.indexOf(document.location.hostname) > 0) {
        chanel = "direct";
        medium = UtmCookie.extractDomain(document.referrer);
    } else if (document.referrer == "") {
        chanel = "direct";
    }

    return [chanel, medium];
}

function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf("?") !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, "$1" + key + "=" + value + "$2");
    } else {
        return uri + separator + key + "=" + value;
    }
}
