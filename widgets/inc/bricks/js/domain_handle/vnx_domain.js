if (typeof window.cookie_domain === 'undefined') {
  window.cookie_domain = ".vietnix.vn";
  if (typeof vnx_app_array !== "undefined" && typeof vnx_app_array.cookie_domain !== "undefined") {
    window.cookie_domain = vnx_app_array.cookie_domain;
  }
}
var cookie_domain = window.cookie_domain;
//1. check domain in cookie
function checkCookieDomain(key, obj) {
  var data = this.getCookiesDomainCart(key);
  return data.some(function (item) {
    return item.domain === obj.domain;
  });
}

//2. set coookie domain cart
function setCookieDomainCart(key, value, expires = 30) {
  // If value is already a string (JSON), parse it first
  let domains = typeof value === 'string' ? JSON.parse(value) : value;

  // Normalize all domains before saving to cookie
  const normalizedDomains = domains.map(normalizeDomainForCookie);
  const normalizedValue = JSON.stringify(normalizedDomains);

  const now = new Date();
  const exp = new Date(now.getTime() + parseInt(expires) * 24 * 60 * 60 * 1000);
  var cookie =
    key +
    "=" +
    encodeURIComponent(btoa(normalizedValue)) +
    "; domain=" +
    cookie_domain +
    " ; path=/; expires=" +
    exp.toUTCString() +
    ";";
  document.cookie = cookie;
}

// Normalize domain data before saving to cookie - only keep essential fields
function normalizeDomainForCookie(domainObj) {
  const normalized = {
    domain: domainObj.domain,
    register: domainObj.register || "1",
    authen_code: domainObj.authen_code || "",
    sld: domainObj.sld,
    tld: domainObj.tld,
  };

  // Only include combo fields if domain is actually a combo
  if (domainObj.isCombo && domainObj.comboId) {
    normalized.isCombo = true;
    normalized.comboId = domainObj.comboId;
    // Only save comboStt to lookup comboData from sessionStorage later
    if (domainObj.comboData && domainObj.comboData.stt) {
      normalized.comboStt = domainObj.comboData.stt;
    }
  }

  return normalized;
}

// Enrich domain data when reading from cookie - restore computed fields
function enrichDomainFromStorage(domainObj) {
  const enriched = { ...domainObj };

  // Restore comboData from sessionStorage if comboStt exists
  if (enriched.isCombo && enriched.comboStt) {
    try {
      const comboData = sessionStorage.getItem("Data_Combo_TLD");
      if (comboData) {
        const combos = JSON.parse(comboData);
        const combo = combos.find((c) => c.stt === enriched.comboStt);
        if (combo) {
          enriched.comboData = {
            stt: combo.stt,
            tlds: combo.tlds || combo.Tlds,
            defaultpricing: combo.defaultpricing || combo["Default Pricing"],
            combopricing: combo.combopricing || combo["Combo Pricing"],
            titlecombo: combo.titlecombo || combo["Title Combo"],
            contentcombo: combo.contentcombo || combo["Content Combo"],
          };
        }
      }
    } catch (error) {
      console.error("Error restoring comboData from sessionStorage:", error);
    }
  }

  // Prices will be calculated by cart component when needed
  // Don't restore old_price and new_price here to save space

  return enriched;
}

//get infor domain in cookie
function getCookiesDomainCart(key) {
  var data = this.getCookieKey(key);
  if (!data) return [];

  const domains = JSON.parse(atob(data));
  // Enrich each domain with computed fields
  return domains.map(enrichDomainFromStorage);
}

//3. get cookie
function getCookieKey(key) {
  var name = key + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var cookieArray = decodedCookie.split(";");
  for (var i = 0; i < cookieArray.length; i++) {
    var cookie = cookieArray[i];
    while (cookie.charAt(0) === " ") {
      cookie = cookie.substring(1);
    }
    if (cookie.indexOf(name) === 0) {
      return decodeURIComponent(cookie.substring(name.length, cookie.length));
    }
  }
  return null;
}

// Calculate cookie size in bytes
function getCookieSize(key, value, expires = 30) {
  const now = new Date();
  const exp = new Date(now.getTime() + parseInt(expires) * 24 * 60 * 60 * 1000);
  const encodedValue = encodeURIComponent(btoa(value));
  const cookieString =
    key +
    "=" +
    encodedValue +
    "; domain=" +
    cookie_domain +
    " ; path=/; expires=" +
    exp.toUTCString() +
    ";";
  return new TextEncoder().encode(cookieString).length;
}

// Check if cookie size exceeds limit
function checkCookieSizeLimit(key, newData, domainName, expires = 30) {
  const maxCookieSize = 4000;
  // Normalize data before calculating size (same as what will be saved to cookie)
  const normalizedData = Array.isArray(newData)
    ? newData.map(normalizeDomainForCookie)
    : normalizeDomainForCookie(newData);
  const cookieValue = JSON.stringify(normalizedData);
  const cookieSize = getCookieSize(key, cookieValue, expires);

  if (cookieSize >= maxCookieSize) {
    showNoticeMessage(
      "error",
      `Không thể thêm domain "${domainName}" vào giỏ hàng vì giỏ hàng đã đạt giới hạn tối đa. Vui lòng liên hệ với chúng tôi để được hỗ trợ.`,
      null,
      null
    );
    return false;
  }

  return true;
}

// // add doamin in cookie
function addDomaininCookie(key, obj, expires = 30) {
  const maxDomainsInCart = 20;
  var data = this.getCookiesDomainCart(key);

  // Check domain count limit before adding
  if (data.length >= maxDomainsInCart) {
    showNoticeMessage(
      "error",
      `Giỏ hàng chỉ cho phép tối đa ${maxDomainsInCart} domain. Vui lòng liên hệ với chúng tôi để được hỗ trợ.`,
      null,
      null
    );
    return false;
  }

  // Normalize the new domain object before adding
  const normalizedObj = normalizeDomainForCookie(obj);
  var newData = [...data, normalizedObj];
  // Check cookie size limit before adding (use normalized data for size check)
  if (!checkCookieSizeLimit(key, newData, obj.domain, expires)) {
    return false;
  }

  this.setCookieDomainCart(key, newData, expires);
  return true;
}

//4. delete cookie domain cart
function deleteCookieDomainCart(key) {
  this.deleteCookie(key);
}

function deleteCookie(key) {
  document.cookie =
    key +
    "=; domain=" +
    cookie_domain +
    " ; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
}

// Set cookie for TLD list (array of strings, not domain objects)
function setCookieTldList(key, value, expires = 30) {
  // Ensure value is an array of strings
  const tldList = Array.isArray(value) ? value : [];
  const cookieValue = JSON.stringify(tldList);

  const now = new Date();
  const exp = new Date(now.getTime() + parseInt(expires) * 24 * 60 * 60 * 1000);
  var cookie =
    key +
    "=" +
    encodeURIComponent(btoa(cookieValue)) +
    "; domain=" +
    cookie_domain +
    " ; path=/; expires=" +
    exp.toUTCString() +
    ";";
  document.cookie = cookie;
}

// Get cookie for TLD list (array of strings, not domain objects)
function getCookieTldList(key) {
  var data = getCookieKey(key);
  if (!data) return [];

  try {
    const decoded = atob(data);
    const tldList = JSON.parse(decoded);
    // Ensure it's an array and filter out non-string items
    if (Array.isArray(tldList)) {
      return tldList.filter(item => typeof item === 'string');
    }
    return [];
  } catch (error) {
    return [];
  }
}

// //remove domain in cookie
function removeDomaininCookie(key, domain, expires = 30) {
  var data = this.getCookiesDomainCart(key);
  data = data.filter((obj) => obj.domain !== domain);
  if (data.length == 0) {
    this.deleteCookieDomainCart(key);
  } else {
    this.setCookieDomainCart(key, data, expires);
  }
}

//update domain in cookie
function updateDomaininCookie(key, identifier, updatedData, expires = 30) {
  var data = this.getCookiesDomainCart(key);
  var updated = false;
  for (var i = 0; i < data.length; i++) {
    if (data[i].domain === identifier) {
      // Merge updated data and normalize before saving
      const merged = { ...data[i], ...updatedData };
      data[i] = normalizeDomainForCookie(merged);
      updated = true;
      break;
    }
  }
  if (updated) {
    this.setCookieDomainCart(key, data, expires);
  }
}

// calculate percent % price domain
function calculatePriceDomain(reductionPrice, originalPrice) {
  // Chuyển đổi về số và kiểm tra giá trị hợp lệ
  let reduction = parseFloat(reductionPrice);
  let original = parseFloat(originalPrice);

  if (isNaN(reduction) || isNaN(original) || original <= 0) {
    console.warn("Dữ liệu không hợp lệ:", { reductionPrice, originalPrice });
    return null;
  }
  if (reduction === original) {
    return 0;
  } else {
    let percent = 100 - (reduction / original) * 100;
    return Math.round(percent);
  }
}

function calculateDiscountPercent(reductionPrice, originalPrice) {
  // Kiểm tra dữ liệu đầu vào
  if (!reductionPrice || !originalPrice) {
    console.warn("Dữ liệu không tồn tại hoặc null:", {
      reductionPrice,
      originalPrice,
    });
    return null;
  }

  // Xử lý bỏ dấu chấm ngăn cách hàng nghìn
  let reduction = parseFloat(reductionPrice.toString().replace(/\./g, ""));
  let original = parseFloat(originalPrice.toString().replace(/\./g, ""));

  if (isNaN(reduction) || isNaN(original) || original <= 0) {
    console.warn("Dữ liệu không hợp lệ:", { reductionPrice, originalPrice });
    return null;
  }

  if (reduction >= original) return 0;

  let percent = ((original - reduction) / original) * 100;
  return Math.round(percent);
}

// lấy giá domain từ sessionStorage
function getPriceDomainData(tld) {
  let storedData = sessionStorage.getItem("Data_TLD");
  if (!storedData) {
    console.warn("Không có dữ liệu trong sessionStorage.");
    return null;
  }
  let tldData = JSON.parse(storedData);
  let matchedItem = tldData.find((item) => item[0] === tld);
  if (!matchedItem) {
    console.warn(`Không tìm thấy dữ liệu cho: ${tld}`);
    return null;
  }
  let price = matchedItem[1];
  if (!price || isNaN(price)) {
    console.warn(`Giá trị không hợp lệ: ${price}`);
    return null;
  }
  return Number(price).toLocaleString("vi-VN");
}

function getTooltipTitleDomainData(tld) {
  let storedData = sessionStorage.getItem("Data_TLD");
  let title = "";
  if (!storedData) {
    console.warn("Không có dữ liệu trong sessionStorage.");
    return null;
  }
  let tldData = JSON.parse(storedData);
  let matchedItem = tldData.find((item) => item[0] === tld);
  if (!matchedItem) {
    console.warn(`Không tìm thấy dữ liệu cho: ${tld}`);
    return null;
  }
  title = matchedItem[2];
  return title;
}

function getTooltipContentDomainData(tld) {
  let storedData = sessionStorage.getItem("Data_TLD");
  let content = "";
  if (!storedData) {
    console.warn("Không có dữ liệu trong sessionStorage.");
    return null;
  }
  let tldData = JSON.parse(storedData);
  let matchedItem = tldData.find((item) => item[0] === tld);
  if (!matchedItem) {
    console.warn(`Không tìm thấy dữ liệu cho: ${tld}`);
    return null;
  }
  content = matchedItem[3];
  return content;
}

function getTitleDomainExp(tld) {
  let storedData = sessionStorage.getItem("Data_TLD");
  let title = "";
  if (!storedData) {
    console.warn("Không có dữ liệu trong sessionStorage.");
    return null;
  }
  let tldData = JSON.parse(storedData);
  let matchedItem = tldData.find((item) => item[0] === tld);
  if (!matchedItem) {
    console.warn(`Không tìm thấy dữ liệu cho: ${tld}`);
    return null;
  }
  title = matchedItem[5];
  return title;
}

function getCombinationDomain(tld) {
  let storedData = sessionStorage.getItem("Data_Combo_TLD");
  if (!storedData) {
    console.warn("Không có dữ liệu trong sessionStorage.");
    return null;
  }
  let comboData;
  try {
    comboData = JSON.parse(storedData);
  } catch (error) {
    console.error("Error parsing Data_Combo_TLD:", error);
    return null;
  }

  // Check if it's an error object or not an array
  if (!Array.isArray(comboData) || (comboData.status && comboData.status === "error")) {
    return null;
  }

  let matchedCombos = [];
  for (let i = 0; i < comboData.length; i++) {
    let combo = comboData[i];
    if (combo && combo.Tlds) {
      // console.log("combogetCombinationDomain", combo);
      let tldsString = combo.Tlds;
      let tldsArray = tldsString.split("+").map((tld) => tld.trim());
      if (tldsArray.includes(tld)) {
        // Chuyển đổi key thành lowercase và loại bỏ khoảng cách
        let normalizedCombo = {
          stt: combo.stt,
          tlds: combo.Tlds,
          defaultpricing: combo["Default Pricing"],
          combopricing: combo["Combo Pricing"],
          titlecombo: combo["Title Combo"],
          contentcombo: combo["Content Combo"],
        };
        matchedCombos.push(normalizedCombo);
      }
    }
  }

  if (matchedCombos.length === 0) {
    return null;
  }

  return matchedCombos;
}

function formatPrice(price) {
  return parseInt(price).toLocaleString("vi-VN");
}

function splitDomain(domain) {
  const index = domain.indexOf(".");
  if (index === -1) {
    return { sld: domain, tld: "" };
  }
  const sld = domain.substring(0, index);
  const tld = domain.substring(index + 1);
  return { sld, tld };
}

//function dùng cho search domain
function showNoticeMessage(status, text, buttonText, buttonCallback) {
  var message = text;
  var bgColor = status === "success" ? "bg-green-100" : "bg-red-100";
  var textColor = status === "success" ? "text-green-500" : "text-red-500";
  var iconPath =
    status === "success"
      ? "M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
      : "M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z";

  // Create button HTML if buttonText is provided
  var buttonHtml = "";
  if (buttonText && buttonCallback) {
    buttonHtml = `
      <div class="ml-3 flex-shrink-0">
        <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-1 px-2 rounded transition-colors duration-200">
          ${buttonText}
        </button>
      </div>
    `;
  }

  var notification = $("<div>", {
    class:
      "vnx_noti fixed top-10 right-[-300px] transition-all duration-300 ease-in-out",
    css: {
      position: "fixed",
      top: "40px",
      right: "-300px", // Ẩn ban đầu
      "z-index": 9999,
    },
  }).append(`
        <div class="flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow" role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 ${textColor} ${bgColor} rounded-lg">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="${iconPath}" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3 text-sm font-normal">${message}</div>
            ${buttonHtml}
        </div>
    `);

  $("body").append(notification);

  // Add click handler for button if provided
  if (buttonText && buttonCallback) {
    notification.find("button").on("click", function () {
      buttonCallback();
      notification.css("right", "-300px");
      setTimeout(() => notification.remove(), 700);
    });
  }

  setTimeout(() => {
    notification.css("right", "20px");
  }, 50);

  // Auto hide after 5 seconds if no button, or 10 seconds if has button
  var autoHideTime = buttonText ? 10000 : 2000;
  setTimeout(() => {
    notification.css("right", "-300px");
    setTimeout(() => notification.remove(), 300);
  }, autoHideTime);
}
function vnx_post_ajax_Data(type, action, data) {
  return $.ajax({
    url: admin_ajax_url,
    type: type,
    dataType: "json",
    async: true,
    timeout: 20000,
    data: {
      action: action,
      data: data,
      nonce: window.vnxDomainNonce.nonce,
    },
  });
}
function vnx_post_ajax_DataSugguest(type, action, domain, data) {
  return $.ajax({
    url: admin_ajax_url,
    type: type,
    dataType: "json",
    async: true,
    timeout: 20000,
    data: {
      action: action,
      domain: domain,
      data: data,
      nonce: window.vnxDomainNonce.nonce,
    },
  });
}

function vnx_post_ajax_whois(type, action, domain) {
  return $.ajax({
    url: admin_ajax_url,
    type: type,
    dataType: "json",
    async: true,
    timeout: 20000,
    data: {
      action: action,
      domain: domain,
    },
  });
}

function handlePopupImportFile() {
  const popup = $(".popup");
  const closePopup = $(".closePopup");

  //--------------------------- xử lý hiển thị popup import file -------------------------
  closePopup.click(function () {
    popup.addClass("hidden");

    // Xóa dữ liệu trong input file
    $("#csv-file").val("");
    $("#name-file").html("");
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      closePopup.click();
    }
  });
  //---------------------------------------------------------------------

  //---------------------------------- xử lý input file  csv----------------------------------
  const dropZone = $("#drop-zone");
  const csvFileInput = $("#csv-file");

  // Khi kéo thả file vào drop zone
  dropZone.on("dragover", function (e) {
    e.preventDefault();
    dropZone.addClass("dragging");
  });

  // Khi kết thúc kéo thả file
  dropZone.on("dragleave", function () {
    dropZone.removeClass("dragging");
  });

  dropZone.on("drop", function (e) {
    e.preventDefault();
    var files = e.originalEvent.dataTransfer.files;
    $("#csv-file").prop("files", files);
    // Trigger the "change" event on the input field
    $("#csv-file").trigger("change");
  });

  // Khi thả file vào drop zone hoặc chọn file từ dialog
  csvFileInput.on("change", function (e) {
    const file = e.target.files[0];
    $("#name-file").html("<strong>File:</strong> " + file["name"]);
    // Xử lý file CSV ở đây
  });
}

function handlePopupExtentionDomain() {
  const tld_ex_default = ["com", "com.vn", "net", "vn"];
  const openPopupExtension = $("#open-popup-extension");
  const extension = $(".extension");
  const closePopupExtension = $("#closePopupExtension");
  const searchInput = $("#search_TLD");
  const storedData = sessionStorage.getItem("Data_TLD_Sussgest");

  // --------------------------- xử lý hiển thị popup phần mở rộng -------------------------

  openPopupExtension.click(function () {
    extension.removeClass("hidden");
  });

  closePopupExtension.click(function () {
    extension.addClass("hidden").hide().fadeIn(300);
  });
  // ---------------------------------------------------------------------------------

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      closePopupExtension.click();
    }
  });

  //--------------------------- xử lý hiển thị danh sách phổ biến -------------------------
  const html = tld_ex_default
    .map((item) => {
      return `
     <label for="ex${item}" class="cursor-pointer">
              <div class="bg-gray-200 p-4 rounded-lg border-ex${item} domain-popular flex">
                <input type="checkbox" name="nameExtention['${item}']" id="ex${item}" value="${item}" width="15%"
                  class="vnx-float-left custom-checkbox ">
                <div> <span>.</span>${item}</div>
              </div>
            </label>
    `;
    })
    .join("");
  $("#result-popular-domain").html(html);
  //-------------------------------------------------------------------------

  //--------------------------- xử lý hiển thị danh sách phần mở rộng -------------------------
  if (!storedData) {
    console.log("Không có dữ liệu trong sessionStorage");
    return;
  }
  let parsedData;
  try {
    parsedData = JSON.parse(storedData);
  } catch (error) {
    console.error("Lỗi khi parse dữ liệu từ sessionStorage:", error);
    return;
  }
  showItemCheckboxExtensiton(parsedData, "#resultTldDomain");
  //-------------------------------------------------------------------------

  //-----------------xử lý tìm kiếm phần mở rộng --------------------------------
  searchInput.on("input", function () {
    const listTLD = JSON.parse(storedData);
    let searchValue = $(this).val().toLowerCase().replace(".", "");
    let filterDomain =
      searchValue !== ""
        ? listTLD.filter((item) => item.toLowerCase().includes(searchValue))
        : [];
    showItemCheckboxExtensiton(filterDomain, "#result-input-search");
  });
  //-------------------------------------------------------------------------

  //------------------------xử lý check all---------------------------------------------------
  $(document).on("click", "#checkAll", function () {
    if (!$(this).is(":checked")) {
      $(".custom-checkbox").prop("checked", false);
    } else {
      $(".custom-checkbox").prop("checked", true);
    }
  });

  $(document).on("click", ".custom-checkbox", function () {
    if (!$(this).is(":checked")) {
      $("#checkAll").prop("checked", false);
    }
  });

  //--------------close popup phần mở rộng---------------------------
  const uncheckAndClosePopup = $(".uncheckAndClosePopup");
  uncheckAndClosePopup.click(function () {
    $("input[name^='nameExtention'][name$=']']").map(function () {
      $(this).prop("checked", false);
      extension.addClass("hidden");
    });
  });
  //-------------------------------------------------------------------------------------

  //------------------------------- Show item checkbox extension và xử lý đồng bộ checked -------------------------
  function showItemCheckboxExtensiton(arrayDomain, idElement) {
    // Lấy danh sách tất cả checkbox đã chọn trong cả danh sách chính và danh sách tìm kiếm
    let checkedValues = new Set(
      $('input[type="checkbox"]:checked')
        .map(function () {
          return $(this).val();
        })
        .get()
    );

    const htmlContent = arrayDomain
      .map((item) => {
        if (!tld_ex_default.includes(item)) {
          return `
                    <label for="${item.replace(".", "_")}"
                        
                        class="flex items-center justify-start vn-cs-checkbox p-4 w-32 cursor-pointer bg-red-400">
                        <input type="checkbox" name="nameExtention['${item}']" 
                            id="${item.replace(".", "_")}" 
                            value="${item}" 
                            class="custom-checkbox mt-0" 
                            ${checkedValues.has(item) ? "checked" : ""}> 
                        <span class="mb-0"> .${item}</span>
                    </label>
                `;
        }
        return "";
      })
      .join("");

    $(idElement).html(htmlContent);
    // Thêm sự kiện đồng bộ checkbox
    $(`${idElement} input[type="checkbox"]`).on("change", function () {
      $(`input[value="${$(this).val()}"]`).prop(
        "checked",
        $(this).prop("checked")
      );
    });
  }
  //-------------------------------------------------------------------------------------
}

function classifyDomains(domains) {
  // phân loại về từng nhóm theo domain và tld
  // [
  //   {
  //     name: "vietnix",
  //     tld: ["com", "vn"],
  //   },
  //   {
  //     name: "nguyenhung",
  //     tld: ["com", "vn"],
  //   }
  // ]
  const result = {};

  domains.forEach((domain) => {
    const parts = domain.split(".");
    const name = parts[0];
    const tld = parts.slice(1).join(".");

    if (!result[name]) {
      result[name] = new Set();
    }

    result[name].add(tld);
  });

  return Object.entries(result).map(([name, tlds]) => ({
    name,
    tld: Array.from(tlds),
  }));
}

function isValidDomain(input) {
  var pattern = /^(?:[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*\.)+[a-zA-Z]{2,}$/g;
  return pattern.test(input);
}

// Parse ngày từ nhiều định dạng whois trả về: Date object, timestamp,
// ISO "yyyy-mm-dd"/"yyyy-mm-ddTHH:mm:ssZ" (whois quốc tế), hoặc
// "dd-mm-yyyy" / "dd/mm/yyyy" (whois.inet.vn cho tên miền .vn).
// Luôn trả về Date neo ở UTC-midnight của đúng ngày lịch: whois/registry
// hay trả ngày hết hạn dạng "...T23:59:59Z", nếu đọc theo giờ VN (+7) sẽ
// bị lố sang ngày hôm sau khi hiển thị.
function parseFlexibleDate(input) {
  if (input instanceof Date) {
    if (isNaN(input.getTime())) return null;
    return new Date(Date.UTC(input.getFullYear(), input.getMonth(), input.getDate()));
  }
  if (input === null || input === undefined || input === "") return null;

  if (typeof input === "number") {
    const d = new Date(input);
    return isNaN(d.getTime()) ? null : new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()));
  }

  const str = String(input).trim();
  const dmy = str.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
  if (dmy) {
    const [, d, m, y] = dmy;
    return new Date(Date.UTC(Number(y), Number(m) - 1, Number(d)));
  }

  const parsed = new Date(str);
  if (isNaN(parsed.getTime())) return null;
  return new Date(Date.UTC(parsed.getUTCFullYear(), parsed.getUTCMonth(), parsed.getUTCDate()));
}

// Chuẩn hóa ngày whois về dạng dd/mm/yyyy để hiển thị nhất quán giữa
// tên miền .vn và quốc tế
function formatDate(input) {
  if (input === null || input === undefined || input === "") return "";

  const date = parseFlexibleDate(input);
  if (!date) return String(input);

  const day = String(date.getUTCDate()).padStart(2, "0");
  const month = String(date.getUTCMonth() + 1).padStart(2, "0");
  const year = date.getUTCFullYear();
  return `${day}/${month}/${year}`;
}

// kiểm tra local storage by key
function getDataLocalStorage(key) {
  try {
    const data = localStorage.getItem(key) !== null;

    if (data) {
      return JSON.parse(localStorage.getItem(key));
    }
    return data;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu từ localStorage:", error);
    return null;
  }
}

// lấy dữ liệu từ local storage
function setLocalStorage(key, value) {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch (error) {
    console.error("Lỗi khi lưu dữ liệu vào localStorage:", error);
  }
}
