var $ = jQuery.noConflict();
var admin_ajax_url_post = $(location).attr("origin") + '/wp-admin/admin-ajax.php';
  //---------------------------- Ajax API --------------------------------
  function post_ajax_search_multipe(type, action, data) {
    return $.ajax({
      url: admin_ajax_url_post,
      type: type,
      async: true,
      dataType: "json",
      data: {
        action: action,
        data: data
      }
    })
  }

  //---------------------------- Ajax API GET TLD --------------------------------
  
  function post_ajax_gettld(type, action, data) {
    return $.ajax({
      url: admin_ajax_url_post,
      type: type,
      async: true,
      dataType: "json",
      data: {
        action: action,
        data: data
      }
    })
  }