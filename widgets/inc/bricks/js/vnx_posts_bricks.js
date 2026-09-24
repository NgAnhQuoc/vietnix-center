window.jQuery = window.$ = jQuery;
$(document).ready(function () {
    vnxPostsElement();
});
function vnxPostsElement() {
    bricksQuerySelectorAll(document, ".brxe-vnx-posts").forEach(function (
        element
    ) {
        if (
            $(element).hasClass("is-active-element") ||
            !$(element).hasClass("is-initialized")
        ) {
            view = vnxPostsElementFn(element);
        }
    });
}
const vnxPostsElementFn = function (element) {
  count_down(element);
  $(element).addClass("is-initialized");
};
function count_down(element) {
  const cs_date_time = $(element).find(".vnx_custom_sale");
  const vnx_custom_count_down = $(element).find(".vnx_custom_count_down");
  const has_expiration_date = $(element).find(".has_expiration_date");
  const countdownInterval = setInterval(function () {
    for (let i = 0; i < cs_date_time.length; i++) {
      const targetDate = new Date(cs_date_time[i].getAttribute("data-time"));
      var countdownElement = "";

      const now = new Date().getTime();
      const distance = targetDate - now;
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      countdownElement = `<div class="border-r border-gray-200 pr-4 pl-4">
    <span class="vnx_days_time_second">` + days + `</span> </br> 
      <span class="vnx_label_countdown">Days</span>
    </div>
    <div class="border-r border-gray-200 pr-4 pl-4 ">
    <span class="vnx_days_time_second">` + hours + `</span></br> 
      <span class="vnx_label_countdown">Hours</span></div>  
    <div class="border-r border-gray-200 pr-4 pl-4">
    <span class="vnx_days_time_second">` + minutes + `</span></br> 
      <span class="vnx_label_countdown">Minutes</span></div>
    <div class="pr-4 pl-4 ">
      <span class="vnx_days_time_second">` + seconds + `</span></br> 
      <span class="vnx_label_countdown">Seconds</span></div>
    </div>`;
      vnx_custom_count_down[i].innerHTML = countdownElement;
      if (distance < 0) {
        vnx_custom_count_down[i].innerHTML = `<div class="border-r border-gray-200 pr-4 pl-4 ">
    <span class="vnx_days_time_second">00</span> </br> 
      <span class="vnx_label_countdown">Days</span>
    </div>
    <div class="border-r border-gray-200 pr-4 pl-4 ">
    <span class="vnx_days_time_second">00</span></br> 
      <span class="vnx_label_countdown">Hours</span></div>  
    <div class="border-r border-gray-200 pr-4 pl-4">
    <span class="vnx_days_time_second">00</span></br> 
      <span class="vnx_label_countdown">Minutes</span></div>
    <div class="pr-4 pl-4 ">
      <span class="vnx_days_time_second">00</span></br> 
      <span class="vnx_label_countdown">Seconds</span></div>
    </div>`;
        has_expiration_date[i].innerText = "Hết hạn";

      }
    }
  }, 1000);
}