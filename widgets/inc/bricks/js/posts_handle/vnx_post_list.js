window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + "/wp-admin/admin-ajax.php";
document.querySelectorAll(".brxe-vnx-custom-posts-list-v2").forEach((el) => {
  new Vue({
    el: `#${el.id}`,
    data: {
      LoadingResult: false,
      MaxNumPages: 0,
      ShowButton: false,
    },
    mounted() {
      this.ShowButton = false;
      let input = this.$el.querySelector('input[name="id_widget"]');
      let id = input ? input.value : null;
      this.posts_string_search(" ", id, 1);
    },
    created() {
      this.$eventBus.$on("clickfillterPost", (data) => {
        this.LoadingResult = true;
        var meta_keys = data.meta_keys;
        let widget_id = $(this.$el).find('input[name="id_widget"]').val();
        var widget = $(this.$el).find("#vnx_posts_list_" + widget_id);
        $(this.$el).find('input[name="current_page"]').val(1);
        $(this.$el)
          .find('input[name="meta_query"]')
          .val(JSON.stringify(meta_keys));
        let input = this.$el.querySelector('input[name="id_widget"]');
        let id = input ? input.value : null;
        widget.children().not(".loading_posts").remove();
        this.posts_string_search(" ", id, 1, meta_keys);
      });
    },
    methods: {
      clickLoadMore() {
        var current_page = parseInt(
          $(this.$el).find('input[name="current_page"]').val()
        );
        let nextPage = current_page + 1;
        $(this.$el).find('input[name="current_page"]').val(nextPage);
        let widget_id = $(this.$el).find(".vnx_btn_loadmore").data("widget_id");
        this.posts_string_search("", widget_id, nextPage);
      },

      //excerpt
      posts_string_search(string, widget_id, current_page, args = "") {
        this.LoadingResult = true;
        var string = $(this.$el)
          .find('input[name="string_search_query"]')
          .val();
        var current_page = $(this.$el).find('input[name="current_page"]').val();
        var widget = $(this.$el).find("#vnx_posts_list_" + widget_id);
        var widgetData = $(this.$el).find("#vnx_posts_list_data_" + widget_id);
        if (args == "") {
          let metaQueryVal = $(this.$el).find('input[name="meta_query"]').val();
          if (metaQueryVal) {
            var args = JSON.parse(metaQueryVal);
          } else {
            var args = "";
          }
        }
        var posts_type = widgetData.find('input[name="posts_type"]').val();
        var posts_per_page = widgetData
          .find('input[name="posts_per_page"]')
          .val();
        var loop_item_template = widgetData
          .find('input[name="loop_item_template"]')
          .val();
        var data = {
          data: {
            post_status: "publish",
            post_type: posts_type,
            order: "DESC",
            posts_per_page: posts_per_page,
            paged: current_page,
            s: string,
            meta_query: args,
          },
        };
        var type_loop = "loadmore";
        this.getListsPost("vnx_onload_list_post_center", data).then(async (data) => {
          this.MaxNumPages = data.max_numpage;
          if (data.data.length > 0) {
            widget.addClass("grid");
            var item = data.data;
            let postItem = {
              data: {
                post_status: "publish",
                post__in: item,
                post_type: posts_type,
                no_data_template: "",
              },
            };
            this.showPostTemplate(
              "vnx_get_posts_template_center",
              postItem,
              loop_item_template
            ).then(async (response) => {
              if (type_loop == "loadmore") {
                widget.append(response);
                this.LoadingResult = false;
                if (this.MaxNumPages == current_page) {
                  this.ShowButton = false;
                } else {
                  this.ShowButton = true;
                }
              } else {
                widget.children().not(".loading_posts").remove();
                widget.append(response);
                this.LoadingResult = false;
              }
            });
          } else {
            widget.removeClass("grid");
            var nodata_template = widgetData
              .find('input[name="template_nodata_id"]')
              .val();
            let item = {
              data: {
                post_status: "publish",
                no_data_template: nodata_template,
              },
            };
            this.showPostTemplate("vnx_get_posts_template_center", item, nodata_template).then(async (response) => {
              this.ShowButton = false;
              widget.children().not(".loading_posts").remove();
              widget.append(response);
              this.LoadingResult = false;
            });


          }
        });
      },

      getListsPost(action, data) {
        return $.ajax({
          url: admin_ajax_url,
          type: "POST",
          dataType: "json",
          async: true,
          data: {
            action: action,
            data: data,
          },
        });
      },

      showPostTemplate(action, data, loop_item_template) {
        return $.ajax({
          url: admin_ajax_url,
          type: "POST",
          dataType: "html",
          async: true,
          data: {
            action: action,
            post_data: data,
            loop_item_template: loop_item_template,
          },
        });
      },
    },
  });
});
