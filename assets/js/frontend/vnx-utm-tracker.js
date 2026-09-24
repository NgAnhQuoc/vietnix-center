var UtmCookie;

UtmCookie = class UtmCookie {
  constructor(options = {}) {
    this._cookieNamePrefix = '_vnx_';
    this._domain = utm_array.cookie_domain || '.vietnix.vn';
    this._secure = utm_array.secure || false;
    this._initialUtmParams = utm_array.initialUtmParams || false;
    this._sessionLength = utm_array.sessionLength || 1;
    this._cookieExpiryDays = utm_array.cookies_age || 7;
    this._additionalParams = utm_array.additionalParams || [];
    this._additionalInitialParams = utm_array.additionalInitialParams || [];
    this._utmParamsAppend = ['utm_term', 'utm_source', 'utm_medium', 'utm_campaign'];
    this._utmParams = ['utm_term', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid'];
    this.writeInitialReferrer();
    this.writeInitialTimeCreation();
    this.writeLastReferrer();
    this.writeInitialLandingPageUrl();
    this.writeLastPageToAction();
    this.writeAdditionalInitialParams();
    this.setCurrentSession();
    if (this._initialUtmParams) {
      this.writeInitialUtmCookieFromParams();
    }
    if (this.additionalParamsPresentInUrl()) {
      this.writeAdditionalParams();
    }
    if (this.utmPresentInUrl()) {
      this.writeUtmCookieFromParams();
    }
    return;
  }

  createCookie(name, value, days, path, domain, secure) {
    var cookieDomain, cookieExpire, cookiePath, cookieSecure, date, expireDate;
    expireDate = null;
    if (days) {
      date = new Date;
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expireDate = date;
    }
    cookieExpire = expireDate != null ? '; expires=' + expireDate.toGMTString() : '';
    cookiePath = '; path=/';
    cookieDomain = domain != null ? '; domain=' + domain : '';
    cookieSecure = secure ? '; secure' : '';
    document.cookie = this._cookieNamePrefix + name + '=' + value + cookieExpire + cookiePath + cookieDomain + cookieSecure;
  }
  
  updateCookie(name, value, days, path, domain, secure) {
    this.eraseCookie(name);
    this.createCookie(name, value, days, path, domain, secure);
  }

  appendCookie(name, value) {
    document.cookie = this._cookieNamePrefix + name + '=' + value;
  }

  readCookie(name) {
    var c, ca, i, nameEQ;
    nameEQ = this._cookieNamePrefix + name + '=';
    ca = document.cookie.split(';');
    i = 0;
    while (i < ca.length) {
      c = ca[i];
      while (c.charAt(0) === ' ') {
        c = c.substring(1, c.length);
      }
      if (c.indexOf(nameEQ) === 0) {
        return c.substring(nameEQ.length, c.length);
      }
      i++;
    }
    return null;
  }

  eraseCookie(name) {
    this.createCookie(name, '', -1, null, this._domain, this._secure);
  }

  getParameterByName(name) {
    var regex, regexS, results;
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    regexS = '[\\?&]' + name + '=([^&#]*)';
    regex = new RegExp(regexS);
    results = regex.exec(window.location.search);
    if (results) {
      return decodeURIComponent(results[1].replace(/\+/g, ' '));
    } else {
      return '';
    }
  }

  additionalParamsPresentInUrl() {
    var j, len, param, ref;
    ref = this._additionalParams;
    for (j = 0, len = ref.length; j < len; j++) {
      param = ref[j];
      if (this.getParameterByName(param)) {
        return true;
      }
    }
    return false;
  }

  utmPresentInUrl() {
    var j, len, param, ref;
    ref = this._utmParams;
    for (j = 0, len = ref.length; j < len; j++) {
      param = ref[j];
      if (this.getParameterByName(param)) {
        return true;
      }
    }
    return false;
  }

  wrireCookieWithCounter(name, value) {
    var existValue = decodeURIComponent(this.readCookie(name));
    const param = value;
    const pattern = new RegExp(`${param}\\((.*?)\\)`);
    const match = existValue.match(pattern);
    var num = parseInt(match ? match[1] : 0);
    if (num) {
      const newValue = (num + 1);
      const cvalue = existValue.replace(pattern, param + `(${newValue})`);
      this.createCookie(name, cvalue, this._cookieExpiryDays, null, this._domain, this._secure);
    }
    else {
      if (existValue && existValue != 'null') {
        const cvalue = existValue + ',' + param + '(1)';
        this.createCookie(name, cvalue, this._cookieExpiryDays, null, this._domain, this._secure);
      } else {
        const value = param + '(1)';
        this.createCookie(name, value, this._cookieExpiryDays, null, this._domain, this._secure);
      }
    }
  }

  writeCookie(name, value) {
    if (this._utmParamsAppend.includes(name)) {
      var existValue = decodeURIComponent(this.readCookie(name));
      if (value == 'direct' || value == '-') {
        this.wrireCookieWithCounter(name, value);
      }
      else {
        if (existValue && existValue != 'null') {
          var cvalue = existValue + ',' + value;
          this.createCookie(name, cvalue, this._cookieExpiryDays, null, this._domain, this._secure);
        } else {
          this.createCookie(name, value, this._cookieExpiryDays, null, this._domain, this._secure);
        }
      }
    }
    else {
      this.createCookie(name, value, this._cookieExpiryDays, null, this._domain, this._secure);
    }
  }

  writeCookieOnce(name, value) {
    var existingValue;
    existingValue = this.readCookie(name);
    if (!existingValue) {
      this.writeCookie(name, value);
    }
  }

  writeAdditionalParams() {
    var j, len, param, ref, value;
    ref = this._additionalParams;
    for (j = 0, len = ref.length; j < len; j++) {
      param = ref[j];
      value = this.getParameterByName(param);
      this.writeCookie(param, value);
    }
  }

  writeAdditionalInitialParams() {
    var j, len, name, param, ref, value;
    ref = this._additionalInitialParams;
    for (j = 0, len = ref.length; j < len; j++) {
      param = ref[j];
      name = 'initial_' + param;
      value = this.getParameterByName(param) || null;
      this.writeCookieOnce(name, value);
    }
  }

  writeUtmCookieFromParams() {
    var j, len, param, ref, value;
    ref = this._utmParams;
    for (j = 0, len = ref.length; j < len; j++) {
      param = ref[j];
      value = this.getParameterByName(param);
      if (value) {
        this.writeCookie(param, value);
      }
      // else if (value == '' && param != 'gclid') {
      //   this.writeCookie(param, '-');
      // }
    }
  }

  writeInitialUtmCookieFromParams() {
    var j, len, name, param, ref, value;
    ref = this._utmParams;
    for (j = 0, len = ref.length; j < len; j++) {
      param = ref[j];
      name = 'initial_' + param;
      value = this.getParameterByName(param) || null;
      this.writeCookieOnce(name, value);
    }
  }

  _sameDomainReferrer(referrer) {
    var hostname;
    hostname = document.location.hostname;
    return referrer.indexOf(this._domain) > -1 || referrer.indexOf(hostname) > -1;
  }

  _isInvalidReferrer(referrer) {
    return referrer === '' || referrer === void 0;
  }

  writeInitialReferrer() {
    var value;
    value = document.referrer;
    if (this._isInvalidReferrer(value)) {
      value = 'direct';
    }
    this.writeCookieOnce('initial_referrer', value);
  }

  writeInitialTimeCreation() {
    const date = new Date;
    var value = new Date(date.getTime());
    this.writeCookieOnce('initial_time_creation', value.toISOString());
  }

  writeLastReferrer() {
    var value;
    value = document.referrer;
    if (!this._sameDomainReferrer(value)) {
      if (this._isInvalidReferrer(value)) {
        value = 'direct';
      }
      this.writeCookie('last_referrer', value);
    }
  }

  writeInitialLandingPageUrl() {
    var value;
    value = this.cleanUrl();
    if (value) {
      this.writeCookieOnce('initial_landing_page', value);
      this.writeCookieOnce('page', value);
    }
  }

  writeLastPageToAction() {
    var currentURL = window.location;
    var pages = [];
    var currentPages = this.getLastPageToAction();
    if (!currentURL) return;
    if (currentPages) {
      pages = currentPages.split(',');
    }
    var pagesCount = pages.length;
    if (pages.includes(currentURL, pagesCount - 1)) return;
    pages.push(currentURL);
    if (pages.length > 5) pages = pages.slice(-5); // keep only 5 last pages
    var saveString = pages.join(',');
    if (saveString) {
      this.writeCookie('last_page_to_action', saveString);
    }
  }

  getLastPageToAction() {
    return this.readCookie('last_page_to_action');
  }

  initialReferrer() {
    return this.readCookie('referrer');
  }

  lastReferrer() {
    return this.readCookie('last_referrer');
  }

  initialLandingPageUrl() {
    return this.readCookie('initial_landing_page');
  }

  incrementVisitCount() {
    var cookieName, existingValue, newValue;
    cookieName = 'visits';
    existingValue = parseInt(this.readCookie(cookieName), 10);
    if (isNaN(existingValue)) {
      newValue = 1;
    } else {
      newValue = existingValue + 1;
    }
    this.writeCookie(cookieName, newValue);
  }

  visits() {
    return this.readCookie('visits');
  }

  setCurrentSession() {
    var cookieName, existingValue;
    cookieName = 'current_session';
    existingValue = this.readCookie(cookieName);
    if (!existingValue) {
      this.createCookie(cookieName, 'true', this._sessionLength / 24, null, this._domain, this._secure);
      this.incrementVisitCount();
    }
  }

  cleanUrl() {
    var cleanSearch;
    cleanSearch = window.location.search.replace(/utm_[^&]+&?/g, '').replace(/&$/, '').replace(/^\?$/, '');
    return window.location.origin + window.location.pathname + cleanSearch + window.location.hash;
  }

  extractDomain(url) {
    var domain = url.replace(/^(https?:\/\/)?(www\.)?/i, '');
    domain = domain.split('/')[0];
    return domain;
  }

  hasUTMParameter(url, parameter) {
    var searchParams = new URLSearchParams(url.search);
    return searchParams.has(parameter);
  }
};

var _vnx;

_vnx = window._vnx || {};
if (typeof utm_array.cookie_domain !== "undefined") {
  _vnx.domain = utm_array.cookie_domain;
}
window.UtmCookie = new UtmCookie(_vnx);