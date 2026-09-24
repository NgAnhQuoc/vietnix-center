window.jQuery = window.$ = jQuery;

$(document).ready(function () {
  var selectBtn = $(".select-btn")
  var items = $(".item");

  selectBtn.on("click", function () {
    $(this).toggleClass("open");
  });
  items.on("click", function () {
    $(this).toggleClass("checked");
    var checked = $(this).closest(".list-items").find(".item.checked");
    var btnText_count = $(this).parent().parent().find(".select-btn").find(".btn-text-count");

    if (checked && checked.length > 0) {
      btnText_count.show();
      btnText_count.text(`${checked.length}`);
    } else {
      btnText_count.hide();
    }
  });

  $('.filter_submit').on("click", function () {
    var target_id = $(this).data('target_id');
    selectBtn.removeClass("open");
    if ($('#vnx_posts_list_' + target_id).length > 0) {
      $('#vnx_posts_list_data_' + target_id + ' input[name="string_search_query"]').val('')
      var data = {};
      var checked = $('.container_posts_filter');
      $.each(checked, function () {
        var data_name = $(this).data('filter');
        var items = $(this).find(".item.checked")
        data[data_name] = [];
        $.each(items, function () {
          data[data_name].push($(this).data('filter_value'))
        })
      })
      $('#vnx_btn_loadmore_' + target_id).removeAttr('style')
      $('#vnx_btn_prv_' + target_id).attr('style', 'display:none !important')

      get_data_by_posts_meta_query(data, target_id, 1)
    }
    else {
      alert('Oops! Something went wrong')
    }
  });

  $('.filter_clear').on("click", function () {
    var target_id = $(this).data('target_id');
    selectBtn.removeClass("open");
    if ($('#vnx_posts_list_' + target_id).length > 0) {
      $('#vnx_posts_list_data_' + target_id + ' input[name="string_search_query"]').val('')
      var data = {};
      var checked = $('#vnx_posts_filter_' + target_id + ' .container_posts_filter .item.checked');
      checked.removeClass('checked');
      $('#vnx_btn_loadmore_' + target_id).removeAttr('style')
      $('#vnx_btn_prv_' + target_id).attr('style', 'display:none !important')
      get_data_by_posts_meta_query(data, target_id, 1)
      $('#vnx_posts_filter_' + target_id).find('.btn-text-count').hide();
    }
    else {
      alert('Oops! Something went wrong')
    }
  });
});
function get_data_by_posts_meta_query(data_query, target_id, paged = 1) {
  $('#vnx_posts_list_' + target_id).append('<div class="loading_posts"><div role="status"><img src="https://vietnix.vn/wp-content/uploads/2023/06/Rolling-1.2s-50px.svg" alt=""></div></div>')
  var posts_type = $('#vnx_posts_list_data_' + target_id + ' input[name="posts_type"]').val();
  var posts_per_page = $('#vnx_posts_list_data_' + target_id + ' input[name="posts_per_page"]').val();
  var loop_item_template = $('#vnx_posts_list_data_' + target_id + ' input[name="loop_item_template"]').val();
  var s = $('#jobs_posts_form input[name="posts_1"]').val();
  var meta_query = [];
  $.each(data_query, function (key, value) {
    var meta_query_name = key;
    $.each(value, function (key, value) {
      meta_query.push({
        key: meta_query_name,
        value: value,
        compare: '='
      })
    })
  })
  var data = {
    "data": {
      "post_status": "publish",
      "post_type": posts_type,
      "meta_query": meta_query,
      'posts_per_page': posts_per_page,
      'paged': paged,
      'meta_query_relation': '',
      's': s,
    }
  }
  get_posts_list_ajax('vnx_get_custom_posts_list_center', data)
    .then(function (data) {
      if (data.data.length > 0) {
        $('.vnx_pages_pagination').removeAttr('style')
        $('#vnx_posts_list_' + target_id).empty();

        data.data.forEach(async function (item) {
          var item = {
            "data": {
              "post_status": "publish",
              "p": item,
              "post_type": posts_type,
              "no_data_template": '',
            }
          }
          await show_post_ajax_by_template('vnx_get_custom_posts_template_center', item, loop_item_template).then(function (data) {
            $('#vnx_posts_list_' + target_id).append(data);
          })
        })
        $('.vnx_pages_pagination span.current_page_' + target_id).text(paged)
        $('.vnx_pages_pagination span.max_num_pages_' + target_id).text(data.max_numpage)
        if (data.max_numpage == paged) {
          $('#vnx_btn_loadmore_' + target_id).attr('style', 'display:none !important');
          if (data.max_numpage == 1) {
            $('#vnx_btn_prv_' + target_id).attr('style', 'display:none !important')
            $('#vnx_btn_prv_' + target_id).data('prv-page', 1)
          } else {
            $('#vnx_btn_prv_' + target_id).removeAttr('style')
            $('#vnx_btn_prv_' + target_id).data('prv-page', paged - 1)
          }
        }
        else {
          $('#vnx_btn_loadmore_' + target_id).data('next-page', paged + 1)
          $('#vnx_btn_prv_' + target_id).data('prv-page', paged - 1)
        }
      }
      else {
        var nodata_template = $('#vnx_posts_list_data_' + target_id + ' input[name="template_nodata_id"]').val();
        $('#vnx_btn_loadmore_' + target_id).attr('style', 'display:none !important');
        $('.vnx_pages_pagination').attr('style', 'display:none !important');
        if (nodata_template.length > 0) {
          var item = {
            "data": {
              "no_data_template": nodata_template,
            }
          }
          show_post_ajax_by_template('vnx_get_custom_posts_template_center', item, loop_item_template).then(function (data) {
            $('#vnx_posts_list_' + target_id).html('<div class="col-span-full">' + data + '</div>');
          })
        }
        else {
          $('#vnx_posts_list_' + target_id).html('<div class="no-data">No data</div>');
        }
      }
    })
}
