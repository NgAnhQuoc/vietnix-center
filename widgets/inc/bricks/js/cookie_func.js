if (typeof window.cookie_domain === 'undefined') {
  window.cookie_domain = ".vietnix.vn";
  if (typeof vnx_app_array !== "undefined" && typeof vnx_app_array.cookie_domain !== "undefined") {
    window.cookie_domain = vnx_app_array.cookie_domain;
  }
}
var cookie_domain = window.cookie_domain;

function deleteCookie(name) {
  document.cookie = name + '=""; domain=' + cookie_domain + '; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function addObjectToCookie(key, obj, expires = 30) {
  var data = getDataFromCookie(key);
  data.push(obj);
  var jsonData = JSON.stringify(data);
  setCookie(key, jsonData, expires);
}

function getCookie(key) {
  var name = key + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var cookieArray = decodedCookie.split(';');

  for (var i = 0; i < cookieArray.length; i++) {
    var cookie = cookieArray[i];
    while (cookie.charAt(0) === ' ') {
      cookie = cookie.substring(1);
    }
    if (cookie.indexOf(name) === 0) {
      return decodeURIComponent(cookie.substring(name.length, cookie.length));
    }
  }
  return null;
}

function setCookie(key, value, expires = 30) {
  const now = new Date();
  const exp = new Date(now.getTime() + parseInt(expires) * 24 * 60 * 60 * 1000);
  var cookie = key + '=' + encodeURIComponent(btoa(value)) + '; domain=' + cookie_domain + ' ; path=/; expires=' + exp.toUTCString() + ';';
  document.cookie = cookie;
}

function checkObjectInCookie(key, obj) {
  var data = getDataFromCookie(key);
  return data.some(function (item) {
    // Perform the comparison based on your criteria
    return item.domain === obj.domain;
  });
}

function getDataFromCookie(key) {
  var data = getCookie(key);
  return data ? JSON.parse(atob(data)) : [];
}

function getObjectFromCookie(key, identifier) {
  var data = getDataFromCookie(key);
  return data.find(function (obj) {
    return obj.domain === identifier;
  });
}

function updateObjectInCookie(key, identifier, updatedData, expires = 30) {
  var data = getDataFromCookie(key);
  var updated = false;

  for (var i = 0; i < data.length; i++) {
    if (data[i].domain === identifier) {
      data[i] = { ...data[i], ...updatedData }; // Merge the updated data into the existing object
      updated = true;
      break;
    }
  }
  if (updated) {
    setCookie(key, JSON.stringify(data), expires);
  }
}

function removeObjectFromCookie(key, domain, expires = 30) {
  var data = getDataFromCookie(key);
  data = data.filter(obj => obj.domain !== domain);
  if (data.length == 0) {
    deleteCookie(key)
  }
  else {
    setCookie(key, JSON.stringify(data), expires);
  }
}