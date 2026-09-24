window.jQuery = window.$ = jQuery;

$(document).ready(function (e) {
  var currentUrl = window.location.href;
  var posts_type = $(
    '#vnx_themes_posts_list_data input[name="posts_type"]'
  ).val();
  if (posts_type == 'lap-trinh') {
    var paged = 1;
    var tax_id = 'all';
    load_theme_ajax_post(paged, tax_id, currentUrl);
  }
  $(".themes-get-id").click(function (e) {
    tax_id = $(this).attr("data-id");
    $(this)
      .closest(".selector-category")
      .find(".active_category")
      .removeClass("active_category");
    $("#cat-theme-id-" + tax_id).addClass("active_category");
    $(".vnx-pagination-2").empty();
    paged = 1;
    load_theme_ajax_post(paged, tax_id, currentUrl);
  });
  $(document).on("click", ".paginate_links a", function (event) {
    event.preventDefault();
    var scroll_target = ScrollTarget(jQuery(this));
    scroll_top_section(scroll_target);
    var hrefThis = $(this).attr("href");
    var paged = String(hrefThis).split("/");
    var pageIndex = $.inArray('page', paged);
    if (pageIndex !== -1 && pageIndex + 1 < paged.length) {
      paged = paged[pageIndex + 1];
    } else {
      console.log(error);
    }
    tax_id = $(".active_category").attr("data-id");
    if (!tax_id) tax_id = "";
    if (!paged) paged = 1;
    load_theme_ajax_post(paged, tax_id, currentUrl);
  });
});
function load_theme_ajax_post(paged, tax_id, currentUrl) {
  var posts_type = $(
    '#vnx_themes_posts_list_data input[name="posts_type"]'
  ).val();
  var posts_per_page = $(
    '#vnx_themes_posts_list_data input[name="posts_per_page"]'
  ).val();
  var taxonomy = $(
    '#vnx_themes_posts_list_data input[name="taxonomy"]'
  ).val();
  var loop_card = $(
    '#vnx_themes_posts_list_data input[name="loop_card"]'
  ).val();
  var ajax_data = {
    action: "loadpost_center",
    ajax_paged: paged,
    tax_id: tax_id,
    posts_type: posts_type,
    posts_per_page: posts_per_page,
    current_page: currentUrl,
  };
  if (taxonomy != undefined) ajax_data.taxonomy = taxonomy;
  if (loop_card != undefined) ajax_data.loop_card = loop_card;
  if (typeof root_div !== "undefined") ajax_data.root_div = root_div;
  $.ajax({
    type: "post",
    dataType: "json",
    url: vietnix_themes_post_filter_js.url,
    data: ajax_data,
    beforeSend: function () {
      var cover_height = $(".cover-post").outerHeight();
      $(".cover-post").css("height", cover_height + "px");
      $(".cover-post").empty();
      $(".cover-post").append(
        '<div class="loading_posts w-full "><div role="status" class="w-full flex justify-center"><img src="https://vietnix.vn/wp-content/uploads/2023/06/Rolling-1.2s-50px.svg" alt=""></div></div>'
      );
    },
  })
    .done(function (data) {
      $(".cover-post").css("height", "");
      $(".theme-post-vnx-pagination").empty();
      $(".cover-post").html(data.data.replace(/\\"/g, '"'));
    })
    .fail(function (jqXHR, textStatus, error) {
      $(".cover-post").css("height", "");
      $(".theme-post-vnx-pagination").empty();
      $(".cover-post").html(
        '<p class="text-center font-lg"><i>Đã có lỗi xảy ra. Vui lòng thử lại sau!</i></p>'
      );
      console.log(textStatus + ": " + error);
      console.log(jqXHR);
    });
}
function ScrollTarget(click) {
  if (!click) return;
  let scroll_target;
  if (jQuery("body").find("#tangtheme-20000").length != 0) {
    scroll_target = jQuery("#tangtheme-20000");
  } else {
    var section = jQuery(click).closest(".cover-post");
    if (section.length != 0) scroll_target = section;
  }
  return scroll_target;
}
function scroll_top_section(scroll_target) {
  if (!scroll_target) return;
  var pos = jQuery(scroll_target).offset().top - 120;
  jQuery("html,body").animate({ scrollTop: pos }, 200);
}
