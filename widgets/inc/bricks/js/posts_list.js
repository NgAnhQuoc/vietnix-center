window.jQuery = window.$ = jQuery;
var Post
var admin_ajax_url = $(location).attr("origin") + '/wp-admin/admin-ajax.php';

$(document).ready(function () {
  //post_jobs
  var string = $('input[name="string_search_query"]').val()
  posts_string_search(string, 'posts_1');
  //post_case_studies
  posts_string_search(' ', 'case_studies_1');


  var loadMoreButton = $('.vnx_btn_loadmore');
  loadMoreButton.on('click', function (e) {
    e.preventDefault();
    var widget_id = $(this).data('widget_id');
    var posts_type = $('#vnx_posts_list_data_' + widget_id + ' input[name="posts_type"]').val();
    var posts_per_page = $('#vnx_posts_list_data_' + widget_id + ' input[name="posts_per_page"]').val();
    var loop_item_template = $('#vnx_posts_list_data_' + widget_id + ' input[name="loop_item_template"]').val();
    var search_string = $('#vnx_posts_list_data_' + widget_id + ' input[name="string_search_query"]').val();

    if ($('#vnx_posts_filter_' + widget_id).length > 0) {
      var next_page = $(this).data('next-page');
      var data = {};
      if (search_string == '') {
        var checked = $('.container_posts_filter');
        $.each(checked, function () {
          var data_name = $(this).data('filter');
          var items = $(this).find(".item.checked")
          data[data_name] = [];
          $.each(items, function () {
            data[data_name].push($(this).data('filter_value'))
          })
        })
        get_data_by_posts_meta_query(data, widget_id, next_page);
      } else {
        var query_data = []
        data = {
          "data": {
            "post_status": "publish",
            "post_type": posts_type,
            'order': 'DESC',
            'posts_per_page': posts_per_page,
            'paged': next_page,
            'meta_query': query_data,
            's': search_string,
          }
        }
        get_posts_list_ajax('vnx_search_custom_posts_list_center', data)
          .then(async function (data) {
            if (data.data.length > 0) {
              $('#vnx_posts_list_' + widget_id).empty();
              data.data.forEach(async function (item) {
                var item = {
                  "data": {
                    "post_status": "publish",
                    "p": item,
                    "post_type": posts_type,
                  }
                };
                await show_post_ajax_by_template('vnx_get_custom_posts_template_center', item, loop_item_template).then(function (data) {
                  $('#vnx_posts_list_' + widget_id).append(data);
                });
              })
              $('.vnx_pages_pagination span.current_page_' + widget_id).text(next_page)
              $('.vnx_pages_pagination span.max_num_pages_' + widget_id).text(data.max_numpage)
              if (data.max_numpage == next_page) {
                $('#vnx_btn_loadmore_' + widget_id).attr('style', 'display:none !important');
                $('#vnx_btn_prv_' + widget_id).data('prv-page', next_page - 1)
              }
              else {
                $('#vnx_btn_loadmore_' + widget_id).data('next-page', next_page + 1)
                $('#vnx_btn_prv_' + widget_id).data('prv-page', next_page - 1)
              }
            }
          })
      }
      $('#vnx_btn_prv_' + widget_id).removeAttr('style')
    }
    else {
      var paged = $(this).data('next-page');
      var data = {
        "data": {
          "post_status": "publish",
          "post_type": posts_type,
          "meta_query": [],
          'posts_per_page': posts_per_page,
          'paged': paged,
        }
      }
      var action = 'vnx_get_custom_posts_list_center'
      if (search_string != '') {
        action = 'vnx_search_custom_posts_list_center'
        data.data.s = search_string
      }
      get_posts_list_ajax(action, data)
        .then(async function (data) {
          if (data.data.length > 0) {
            $('#vnx_posts_list_' + widget_id).empty();
            data.data.forEach(async function (item) {
              var item = {
                "data": {
                  "post_status": "publish",
                  "p": item,
                  "post_type": posts_type,
                }
              };
              await show_post_ajax_by_template('vnx_get_custom_posts_template_center', item, loop_item_template).then(function (data) {
                $('#vnx_posts_list_' + widget_id).append(data);
              });
            })
            $('.vnx_pages_pagination span.current_page_' + widget_id).text(paged)
            $('.vnx_pages_pagination span.max_num_pages_' + widget_id).text(data.max_numpage)
            if (data.max_numpage == paged) {
              $('#vnx_btn_loadmore_' + widget_id).attr('style', 'display:none !important');
              if (data.max_numpage == 1) {
                $('#vnx_btn_prv_' + widget_id).attr('style', 'display:none !important')
                $('#vnx_btn_prv_' + widget_id).data('prv-page', 1)
              } else {
                $('#vnx_btn_prv_' + widget_id).removeAttr('style')
                $('#vnx_btn_prv_' + widget_id).data('prv-page', paged - 1)
              }
            }
            else {
              $('#vnx_btn_loadmore_' + widget_id).data('next-page', paged + 1)
              $('#vnx_btn_prv_' + widget_id).data('prv-page', paged - 1)
            }
            $('#vnx_btn_prv_' + widget_id).removeAttr('style')
          }

        })

    }
  })

  var prevButton = $('.vnx_btn_prv');
  prevButton.on('click', function (e) {
    e.preventDefault();
    var widget_id = $(this).data('widget_id');
    var search_string = $('#vnx_posts_list_data_' + widget_id + ' input[name="string_search_query"]').val()
    var prv_page = $(this).data('prv-page');
    var posts_type = $('#vnx_posts_list_data_' + widget_id + ' input[name="posts_type"]').val();
    var posts_per_page = $('#vnx_posts_list_data_' + widget_id + ' input[name="posts_per_page"]').val();
    var loop_item_template = $('#vnx_posts_list_data_' + widget_id + ' input[name="loop_item_template"]').val();
    if ($('#vnx_posts_filter_' + widget_id).length > 0) {
      var data = {};
      if (search_string == '') {
        var checked = $('.container_posts_filter');
        $.each(checked, function () {
          var data_name = $(this).data('filter');
          var items = $(this).find(".item.checked")
          data[data_name] = [];
          $.each(items, function () {
            data[data_name].push($(this).data('filter_value'))
          })
        })
        get_data_by_posts_meta_query(data, widget_id, prv_page)
      }
      else {
        var query_data = []
        data = {
          "data": {
            "post_status": "publish",
            "post_type": posts_type,
            'order': 'DESC',
            'posts_per_page': posts_per_page,
            'paged': prv_page,
            'meta_query': query_data,
            's': search_string
          }
        }

        get_posts_list_ajax('vnx_search_custom_posts_list_center', data)
          .then(async function (data) {
            if (data.data.length > 0) {
              $('#vnx_posts_list_' + widget_id).empty();
              data.data.forEach(async function (item) {
                var item = {
                  "data": {
                    "post_status": "publish",
                    "p": item,
                    "post_type": posts_type,
                  }
                };
                await show_post_ajax_by_template('vnx_get_custom_posts_template_center', item, loop_item_template).then(function (data) {
                  $('#vnx_posts_list_' + widget_id).append(data);
                });
              })

              $('.vnx_pages_pagination span.current_page_' + widget_id).text(prv_page)
              $('.vnx_pages_pagination span.max_num_pages_' + widget_id).text(data.max_numpage)

              if (prv_page <= 1) {
                $('#vnx_btn_prv_' + widget_id).data('prv-page', 1)
              } else {
                $('#vnx_btn_prv_' + widget_id).data('prv-page', prv_page - 1)
              }
            }
          })
        $('#vnx_btn_loadmore_' + widget_id).data('next-page', prv_page + 1)
      }
      $('#vnx_btn_loadmore_' + widget_id).removeAttr('style')
      if ($('#vnx_btn_prv_' + widget_id).data('prv-page') <= 1) {
        $('#vnx_btn_prv_' + widget_id).attr('style', 'display:none !important')
      }
    }
    else {
      var paged = $(this).data('prv-page')
      var data = {
        "data": {
          "post_status": "publish",
          "post_type": posts_type,
          "meta_query": [],
          'posts_per_page': posts_per_page,
          'paged': paged,
        }
      }
      var action = 'vnx_get_custom_posts_list_center'
      if (search_string != '') {
        action = 'vnx_search_custom_posts_list_center'
        data.data.s = search_string
      }
      get_posts_list_ajax(action, data)
        .then(async function (data) {
          if (data.data.length > 0) {
            $('#vnx_posts_list_' + widget_id).empty();
            data.data.forEach(async function (item) {
              var item = {
                "data": {
                  "post_status": "publish",
                  "p": item,
                  "post_type": posts_type,
                }
              };
              await show_post_ajax_by_template('vnx_get_custom_posts_template_center', item, loop_item_template).then(function (data) {
                $('#vnx_posts_list_' + widget_id).append(data);
              });
            })
            $('#vnx_btn_loadmore_' + widget_id).removeAttr('style')
            $('.vnx_pages_pagination span.current_page_' + widget_id).text(paged)
            $('.vnx_pages_pagination span.max_num_pages_' + widget_id).text(data.max_numpage)
            if (data.max_numpage == paged) {
              $('#vnx_btn_loadmore_' + widget_id).attr('style', 'display:none !important');
              if (data.max_numpage == 1) {
                $('#vnx_btn_prv_' + widget_id).attr('style', 'display:none !important')
                $('#vnx_btn_prv_' + widget_id).data('prv-page', 1)
              } else {
                $('#vnx_btn_prv_' + widget_id).removeAttr('style')
                $('#vnx_btn_prv_' + widget_id).data('prv-page', paged - 1)
              }
            }
            else {
              $('#vnx_btn_loadmore_' + widget_id).data('next-page', paged + 1)
              $('#vnx_btn_prv_' + widget_id).data('prv-page', paged - 1)
            }
            if ($('#vnx_btn_prv_' + widget_id).data('prv-page') < 1) {
              $('#vnx_btn_prv_' + widget_id).attr('style', 'display:none !important')
            }
          }
        })
    }
  })

});

function posts_string_search(string, widget_id) {
  $('#vnx_posts_list_' + widget_id).append('<div class="loading_posts"><div role="status"><img src="https://vietnix.vn/wp-content/uploads/2023/06/Rolling-1.2s-50px.svg" alt=""></div></div>')
  var posts_type = $('#vnx_posts_list_data_' + widget_id + ' input[name="posts_type"]').val();
  var posts_per_page = $('#vnx_posts_list_data_' + widget_id + ' input[name="posts_per_page"]').val();
  var loop_item_template = $('#vnx_posts_list_data_' + widget_id + ' input[name="loop_item_template"]').val();
  var query_data = []
  $('#vnx_posts_list_data_' + widget_id + ' input[name="string_search_query"]').val(string)

  var data = {
    "data": {
      "post_status": "publish",
      "post_type": posts_type,
      'order': 'DESC',
      'posts_per_page': posts_per_page,
      'paged': 1,
      // 'meta_query': query_data,
      's': string,
    }
  }
  get_posts_list_ajax('vnx_search_custom_posts_list_center', data)
    .then(async function (data) {
      if (data.data.length > 0) {
        $('#vnx_posts_list_' + widget_id).empty();
        for (let item of data.data) {
          let postItem = {
            "data": {
              "post_status": "publish",
              "p": item,
              "post_type": posts_type,
              "no_data_template": '',
            }
          };
          await show_post_ajax_by_template('vnx_get_custom_posts_template_center', postItem, loop_item_template).then(function (response) {
            $('#vnx_posts_list_' + widget_id).append(response);
          });
        }
        $('#vnx_btn_loadmore_' + widget_id).removeAttr('style');
        $('.vnx_pages_pagination span.current_page_' + widget_id).text(1);
        $('.vnx_pages_pagination span.max_num_pages_' + widget_id).text(data.max_numpage);
        $('#vnx_btn_prv_' + widget_id).attr('style', 'display:none !important');
        $('.vnx_pages_pagination').removeAttr('style');

        if (data.max_numpage <= 1) {
          $('#vnx_btn_loadmore_' + widget_id).attr('style', 'display:none !important');
          $('#vnx_btn_prv_' + widget_id).attr('style', 'display:none !important');
          $('.vnx_pages_pagination').attr('style', 'display:none !important');
        } else {
          $('#vnx_btn_loadmore_' + widget_id).data('next-page', 2);
          $('#vnx_btn_prv_' + widget_id).data('prv-page', 1);
        }
      } else {
        var nodata_template = $('#vnx_posts_list_data_' + widget_id + ' input[name="template_nodata_id"]').val();
        $('#vnx_btn_loadmore_' + widget_id).attr('style', 'display:none !important');
        $('#vnx_btn_prv_' + widget_id).attr('style', 'display:none !important');
        $('.vnx_pages_pagination').attr('style', 'display:none !important');
        if (nodata_template && nodata_template.length > 0) {
          let item = {
            "data": {
              "post_status": "publish",
              "no_data_template": nodata_template,
            }
          };
          show_post_ajax_by_template('vnx_get_custom_posts_template_center', item, nodata_template).then(function (response) {
            $('#vnx_posts_list_' + widget_id).html('<div class="col-span-full">' + response + '</div>');
          });
        } else {
          $('#vnx_posts_list_' + widget_id).html('<div class="no-data">No data</div>');
        }
      }
    });

}


function get_posts_list_ajax(action, data) {
  return $.ajax({
    url: admin_ajax_url,
    type: 'POST',
    dataType: "json",
    async: true,
    data: {
      action: action,
      data: data,
    },
  })
}
function show_post_ajax_by_template(action, data, loop_item_template) {
  return $.ajax({
    url: admin_ajax_url,
    type: 'POST',
    dataType: "html",
    async: true,
    data: {
      action: action,
      post_data: data,
      loop_item_template: loop_item_template,
    },
  })
}