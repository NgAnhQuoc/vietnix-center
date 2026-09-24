if (typeof window.cookie_domain === 'undefined') {
  window.cookie_domain = ".vietnix.vn";
  if (typeof vnx_app_array !== "undefined" && typeof vnx_app_array.cookie_domain !== "undefined") {
    window.cookie_domain = vnx_app_array.cookie_domain;
  }
}
var cookie_domain = window.cookie_domain;

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
    class: "vnx_noti fixed top-10 right-[-300px] transition-all duration-300 ease-in-out",
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
      setTimeout(() => notification.remove(), 300);
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

// Lấy domain từ URL
function getDomainFromUrl() {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get("domain") || "";
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

function formatVND(amount) {
  if (!amount) return "0";
  return parseFloat(amount).toLocaleString("vi-VN");
}

// Parse ngày từ nhiều định dạng whois trả về: 
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

function getDay(inputDate = new Date(), day = 0, isDay = false) {
  const baseDate = parseFlexibleDate(inputDate);

  // Return null if invalid date
  if (!baseDate) return null;

  // Add days to the input date
  const resultDate = new Date(baseDate);
  resultDate.setUTCDate(resultDate.getUTCDate() + day);

  if (isDay) {
    const start = Date.UTC(resultDate.getUTCFullYear(), 0, 0);
    const diff = resultDate.getTime() - start;
    const oneDay = 1000 * 60 * 60 * 24;
    const dayOfYear = Math.floor(diff / oneDay);
    return dayOfYear;
  }

  const d = String(resultDate.getUTCDate()).padStart(2, "0");
  const m = String(resultDate.getUTCMonth() + 1).padStart(2, "0");
  const y = resultDate.getUTCFullYear();
  return `${d}/${m}/${y}`;

  // Usage examples:
  // getDay();  "10/10/2025" → today
  // getDay("25/12/2023", 30);  "24/01/2024" → 30 days after input date
  // getDay("25/12/2023", -7);  "18/12/2023" → 7 days before Christmas
  // getDay("25/12/2023", 0, true);  359 → day of year for input date
}

function initPerfectScrollbar(elements) {
  elements.forEach((element) => {
    // Destroy existing PerfectScrollbar instance (if any) before initializing a new one
    if (element._ps && typeof element._ps.destroy === "function") {
      try {
        element._ps.destroy();
      } catch (e) {
        // ignore
      }
      element._ps = null;
    }

    const ps = new PerfectScrollbar(element, {
      wheelSpeed: 0.5,
      minScrollbarLength: 20,
      swipeEasing: true,
    });
    // store instance for future destroy
    element._ps = ps;
    ps.update();

    // --- Kéo để cuộn ngang ---
    let isDown = false;
    let startX;
    let scrollLeft;

    element.addEventListener("mousedown", (e) => {
      isDown = true;
      element.classList.add("dragging");
      startX = e.pageX - element.offsetLeft;
      scrollLeft = element.scrollLeft;
    });

    element.addEventListener("mouseleave", () => {
      isDown = false;
      element.classList.remove("dragging");
    });

    element.addEventListener("mouseup", () => {
      isDown = false;
      element.classList.remove("dragging");
    });

    element.addEventListener("mousemove", (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - element.offsetLeft;
      const walk = (x - startX) * 1; // tốc độ kéo
      element.scrollLeft = scrollLeft - walk;
    });
  });
}

function formatDate(input) {
  // Trả về chuỗi rỗng nếu input không hợp lệ
  if (input === null || input === undefined || input === "") return "";

  const date = parseFlexibleDate(input);

  if (!date) return "";

  const day = String(date.getUTCDate()).padStart(2, "0");
  const month = String(date.getUTCMonth() + 1).padStart(2, "0");
  const year = date.getUTCFullYear();
  return `${day}/${month}/${year}`;
}

// tạo ham tinh khoảng thời gian giữa 2 ngày (có kiểm tra input)
function dateDiffInDays(date1, date2) {
  if (date1 == null || date2 == null) return null;

  const d1 = parseFlexibleDate(date1);
  const d2 = parseFlexibleDate(date2);

  if (!d1 || !d2) return null;

  const diffTime = Math.abs(d2 - d1);

  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  // Convert diffDays to string and add dots every 3 digits from right
  if (diffDays) {
    return diffDays.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }
  return diffDays;
}

// tạo hàm tính ra số tuổi, có kiểm tra input không hợp lệ
function calculateAge(birthDate, currentDate = new Date()) {
  if (birthDate == null) return null;

  const birth = parseFlexibleDate(birthDate);
  const current = parseFlexibleDate(currentDate);

  // Nếu bất kỳ ngày nào không hợp lệ thì trả về null
  if (!birth || !current) return null;

  // Nếu ngày sinh nằm sau ngày hiện tại => không hợp lệ
  if (birth > current) return null;

  let age = current.getUTCFullYear() - birth.getUTCFullYear();
  const monthDiff = current.getUTCMonth() - birth.getUTCMonth();

  // Kiểm tra nếu tháng hiện tại nhỏ hơn tháng sinh hoặc cùng tháng nhưng ngày hiện tại nhỏ hơn ngày sinh
  if (monthDiff < 0 || (monthDiff === 0 && current.getUTCDate() < birth.getUTCDate())) {
    age--;
  }

  return age;
}

// viết hàm js chuyển danh sách này thành object với key là tên miền và value là mảng các giá trị còn lại
function convertToObject(list) {
  const result = {};
  list.forEach((item) => {
    const [domain, ...values] = item; // tách domain và phần còn lại
    result[domain] = values;
  });
  return result;
}

//get infor domain in cookie
function getCookiesDomainCart(key) {
  var data = this.getCookieKey(key);
  return data ? JSON.parse(atob(data)) : [];
}

// Check if cookie size exceeds limit
function checkCookieSizeLimit(key, newData, domainName, expires = 30) {
  const maxCookieSize = 4000;
  const cookieValue = JSON.stringify(newData);
  const cookieSize = getCookieSize(key, cookieValue, expires);

  if (cookieSize >= maxCookieSize) {
    showNoticeMessage(
      "error",
      `Không thể thêm domain "${domainName}" vào giỏ hàng vì giỏ hàng đã đạt giới hạn tối đa. Vui lòng xóa một số domain khác trước.`,
      null,
      null
    );
    return false;
  }

  return true;
}

//2. set coookie domain cart
function setCookieDomainCart(key, value, expires = 30) {
  const now = new Date();
  const exp = new Date(now.getTime() + parseInt(expires) * 24 * 60 * 60 * 1000);
  var cookie =
    key +
    "=" +
    encodeURIComponent(btoa(value)) +
    "; domain=" +
    cookie_domain +
    " ; path=/; expires=" +
    exp.toUTCString() +
    ";";
  document.cookie = cookie;
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

// Hàm thêm domain vào cookie với kiểm tra giới hạn kích thước cookie
function addDomaininCookie(key, obj, expires = 30) {
  var data = this.getCookiesDomainCart(key);
  var newData = [...data, obj];

  // Check cookie size limit before adding
  if (!checkCookieSizeLimit(key, newData, obj.domain, expires)) {
    return false;
  }

  this.setCookieDomainCart(key, JSON.stringify(newData), expires);
  return true;
}
