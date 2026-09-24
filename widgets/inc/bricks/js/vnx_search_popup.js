window.jQuery = window.$ = jQuery;
var admin_ajax_url_post = $(location).attr("origin") + '/wp-admin/admin-ajax.php';
class VNX_SEARCH_TO_POPUP {
  constructor(form, popup, returnWrapper) {
    this.form = form;
    this.popup = popup;
    this.returnWrapper = returnWrapper;
    this.popupStyle;
    this.queryData;
    this.keyword;
    this.currentURL = window.location.href.split("?")[0];
    // this.ajaxUrl = vnx_search_popup_array.ajax_url;
    this.ajaxUrl = admin_ajax_url_post;
    this.loadingIcon =
      '<div class="vnx_loading flex flex-col items-center justify-between my-5"><div class="loading_wrapper"><div class="loading_icon"></div></div><p class="text-brand text-center text-lg"><b>Đang tải</b></p></div>';
    this.errorNotice =
      '<div class="vnx_error">Có lỗi xảy ra, vui lòng thử lại sau</div>';
    this.errorTimeout;
  }
  Init() {
    const self = this;
    self.GetFormQueryData();
    self.SetPopupStyle();
    self.OnSubmit();
    self.OnClosePopup();
  }
  SubmitAction(e) {
    e.preventDefault();
    const self = this;
    $(self.form)
      .parent()
      .find(".form_search_to_popup input")
      .val(
        $(e.currentTarget)
          .find("input")
          .val()
      );
    self.SetKeyword();
  }
  OnSubmit() {
    const self = this;
    $(self.form).submit(function (e) {
      self.SubmitAction(e);
    });
  }
  GetFormQueryData = () => {
    this.queryData = JSON.parse($(this.form).attr("data-query"));
  };
  SetPopupStyle = () => {
    this.popupStyle = $(this.form).attr("data-style");
  };
  SetKeyword = () => {
    this.keyword = $(this.form)
      .find("input")
      .val();
  };
  ShowPopup = (isShow = false) => {
    if (isShow) {
      $(this.popup).addClass("show");
    } else {
      $(this.popup).removeClass("show");
    }
    this.StopScroll(isShow);
  };
  OnClosePopup() {
    const self = this;
    $(document).on("keydown", function (evt) {
      var isEscape = false;
      if ("key" in evt) {
        isEscape = evt.key === "Escape" || evt.key === "Esc";
      } else {
        isEscape = evt.isEscape;
      }
      if (isEscape) self.ShowPopup(false);
    });
    $(self.popup)
      .find(".vnx_popup_bgr")
      .click(function (e) {
        self.ShowPopup(false);
      });
  }
  StopScroll = (isStop = false) => {
    if (isStop) {
      $("body").css("overflow", "hidden");
    } else {
      $("body").css("overflow", "");
    }
  };
  getUrlParameter(myURL, paramName) {
    const url = new URL(myURL);
    const searchParams = new URLSearchParams(url.search);
    const paramValue = searchParams.get(paramName);
    return paramValue;
  }
  ShowLoading(val = true) {
    const self = this;
    if (val) {
      $(self.returnWrapper)
        .parent()
        .append(
          '<div class="append_loading">' + self.loadingIcon + "</div>"
        );
    } else {
      $(self.returnWrapper)
        .parent()
        .find(".append_loading")
        .remove();
    }
  }
  DisableSubmitBtn(isDisable = true) {
    const self = this;
    if (!isDisable) {
      setTimeout(function () {
        $(self.form)
          .find("button")
          .prop("disabled", isDisable);
        $(self.form)
          .find("input")
          .prop("disabled", isDisable);
      }, 500);
    } else {
      $(self.form)
        .find("button")
        .prop("disabled", isDisable);
      $(self.form)
        .find("input")
        .prop("disabled", isDisable);
    }
  }

  ShowAjaxError() {
    const self = this;
    self.returnWrapper.html(self.errorNotice);
    self.AfterAjaxReturn();
  }
  BeforeAjax() {
    const self = this;
    self.ShowPopup(true);
    self.DisableSubmitBtn();
    self.ShowLoading();
    self.errorTimeout = setTimeout(() => {
      self.ShowAjaxError();
    }, 20000);
  }
  AfterAjaxReturn() {
    const self = this;
    self.ShowPopup(true);
    self.ShowLoading(false);
    self.DisableSubmitBtn(false);
    clearTimeout(self.errorTimeout);
  }
}
class VNX_SEARCH_TO_POPUP_STYLE_1 extends VNX_SEARCH_TO_POPUP {
  constructor(form, popup, returnWrapper) {
    super(form, popup, returnWrapper);
    this.filter = $(this.popup).find(".vnx_select_orderby");
    this.paged = 1;
    this.paginate;
    this.paginateQuery;
    this.ajaxData;
  }
  Init() {
    const self = this;
    super.Init();
    self.InitFilter();
  }
  SubmitAction(e) {
    const self = this;
    super.SubmitAction(e);
    self.paged = 1;
    self.CheckFilterOrderBy();
    self.ajaxData = {
      action: "vnx_get_custom_post_type_center", //action
      _wpnonce: vnx_search_popup_array.nonce,
      current_url: self.currentURL,
      popup_style: self.popupStyle,
      query_data: self.queryData, //data
      keyword: self.keyword,
      paged: self.paged,
    };
    self.AjaxGetPost();
  }
  AjaxGetPost() {
    const self = this;
    self.BeforeAjax();
    // console.log(self.ajaxData);
    const postList = $.ajax({
      method: "POST",
      url: self.ajaxUrl,
      data: self.ajaxData,
      dataType: "json",
    });
    postList.done((response) => {
      if (response.success) {
        $(self.returnWrapper).html(response.data.html);
        self.ShowCountPost(response.data.count);
        self.InitPaginate();
      } else {
        $(self.returnWrapper).html(
          '<p class="text-center"><b>Có lỗi xảy ra, vui lòng thử lại!</b></p>'
        );
      }
      self.AfterAjaxReturn();
    });
    postList.fail((jqXHR, textStatus) => {
      self.ShowAjaxError();
      console.log(textStatus);
      console.log(jqXHR);
    });
  }

  CheckFilterOrderBy() {
    const self = this;
    const filter = self.GetFilterValue();
    switch (filter) {
      // case "popularity":
      //     self.queryData.orderby = "meta_value_num";
      //     self.queryData.meta_key = "reading_time";
      //     self.queryData.order = "DESC";
      //     break;

      case "latest":
        self.queryData.orderby = "date";
        self.queryData.order = "DESC";
        // self.queryData.meta_key = "";
        break;

      case "oldest":
        self.queryData.orderby = "date";
        self.queryData.order = "ASC";
        // self.queryData.meta_key = "";
        break;

      case "name":
        self.queryData.orderby = "name";
        self.queryData.order = "ASC";
        // self.queryData.meta_key = "";
        break;

      default:
        self.queryData.orderby = filter;
        self.queryData.order = "DESC";
        break;
    }
  }
  ShowCountPost(val = 0) {
    const self = this;
    $(self.returnWrapper)
      .parent()
      .find(".vnx_loop_count")
      .html(val);
  }
  SetPaginate = () => {
    if ($(this.returnWrapper).find(".vnx_ajax_paginate_popup").length == 0)
      return;
    this.paginate = $(this.returnWrapper).find(".vnx_ajax_paginate_popup");
    this.paginateQuery = JSON.parse($(this.paginate).attr("data-query"));
  };
  InitPaginate() {
    const self = this;
    self.SetPaginate();
    if (!self.paginate) return;
    self.ReSetAjaxData();
    $(self.paginate).on("click", "a.page-numbers", function (e) {
      e.preventDefault();
      const url = $(this).attr("href");
      self.paged = self.getUrlParameter(url, "paged");
      self.ajaxData.paged = self.paged;
      self.AjaxGetPost();
    });
  }
  GetFilterValue() {
    const self = this;
    return $(self.filter).val();
  }
  InitFilter() {
    const self = this;
    $(self.filter).on("change", function () {
      $(self.form)
        .first()
        .trigger("submit");
    });
  }
  ReSetAjaxData() {
    const self = this;
    self.queryData.post_type = self.paginateQuery.post_type;
    self.queryData.orderby = self.paginateQuery.orderby;
    self.CheckFilterOrderBy();
    self.queryData.order = self.paginateQuery.order;
    self.queryData.meta_key = self.paginateQuery.meta_key;
    self.currentURL = self.paginateQuery.current_url;
    self.keyword = self.paginateQuery.s;
    self.ajaxData = {
      action: "vnx_get_custom_post_type_center", //action
      _wpnonce: vnx_search_popup_array.nonce,
      current_url: self.currentURL,
      popup_style: self.popupStyle,
      query_data: self.queryData, //data
      keyword: self.keyword,
    };
  }
}
$(document).ready(function () {
  const form = $("form.form_search_to_popup");
  if (form.length === 0) return;
  const popup = $(".vnx_popup.style_1");
  const returnWrapper = $(".vnx_popup_content");
  var search = new VNX_SEARCH_TO_POPUP_STYLE_1(
    form,
    popup,
    returnWrapper
  );
  search.Init();
});