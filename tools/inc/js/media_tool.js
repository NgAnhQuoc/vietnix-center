window.jQuery = window.$ = jQuery;

class VNX_MediaTool {
  static Ins(){
    if (!VNX_MediaTool.instance) {
      VNX_MediaTool.instance = new VNX_MediaTool();
    }
    return VNX_MediaTool.instance;
  }
  constructor(total_media, ajax_url, ajax_nonce) {
    this.total_media = total_media;
    this.ajax_url = ajax_url;
    this.ajax_nonce = ajax_nonce;
  }

  setTotalMedia(data) {
    this.total_media = data;
  }

  getTotalMedia() {
    return this.total_media;
  }

  setAjaxUrl(data) {
    this.ajax_url = data;
  }

  getAjaxUrl() {
    return this.ajax_url;
  }

  setAjaxNonce(data) {
    this.ajax_nonce = data;
  }

  getAjaxNonce() {
    return this.ajax_nonce;
  }

  ajaxMediaTool = (action, async = true) => {
    return $.ajax({
      method: "GET",
      url: this.ajax_url,
      async: async,
      data: {
        action: action,
        security: this.ajax_nonce,
      },
    });
  };

}

$(document).ready(function () {
  $('.tool_media_btn').on('click', function () {
    $('.tool_media_btn').addClass("disable");
    $('.vnx-media-tool-message .vnx-spinner').removeClass("hidden");
    let count = 0;
    let action = $(this).val();
    let total_media = media_tool_array.total_media;
    $('.vnx-media-tool-message p').html('✓ Processing  change <b>' + count + '</b> media slug in ' + media_tool_array.total_media + ' media!');
    const mediaTool = new VNX_MediaTool(total_media, media_tool_array.ajax_url, media_tool_array.ajax_nonce);
    mediaTool.ajaxMediaTool('count_attachment_paged_center', false).done(function(res){
      if(res.success == true){
        if (res.data != undefined) {
          for (var i = 1; i <= res.data; i++) {
            media_tool_action(action, i).then(function (data){
              count += parseInt(data.data)
              $('.vnx-media-tool-message p').html('✓ Processing  change <b>' + count + '</b> media slug in ' + total_media + ' media!')
              if (count >= total_media) {
                $('.tool_media_btn').removeClass("disable");
                $('.vnx-media-tool-message .vnx-spinner').addClass("hidden");
                $('.vnx-media-tool-message p').html('✓ Complete  change <b>' + count + '</b> media slug in ' + total_media + ' media!')
              }
            })
          }
        }
      }
    })
  })
})
function media_tool_action(action, paged) {
  return $.ajax({
     type: 'GET',
     url: media_tool_array.ajax_url,
     async: true,
     data: {
         action: action,
         security: media_tool_array.ajax_nonce,
         paged: paged
     },
   });
 }