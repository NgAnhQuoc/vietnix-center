window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + "/wp-admin/admin-ajax.php";
Vue.prototype.$eventBus = new Vue();
document.querySelectorAll(".brxe-vnx-posts-fillter-v2.case_studies").forEach(el => {
  new Vue({
    el: `#${el.id}`,
    data: {
      tagify: null,
      selectedTags: {},
    },
    mounted() {
      this.initTagify();
    },
    methods: {
      initTagify() {
        document.querySelectorAll(".vnx_content_item").forEach(select => {
          const input = select.querySelector("input");
          const hiddenInput = select.querySelector("input[type='hidden']");
          if (!input || !hiddenInput) return;

          let whitelist, values, data;
          try {
            data = JSON.parse(input.dataset.list);
            values = data.map(item => item.value);
            const labels = data.map(item => item.label);
            whitelist = labels;

          } catch (e) {
            console.error("Lỗi khi parse JSON:", e);
            return;
          }
          const tagify = new Tagify(input, {
            enforceWhitelist: true,
            mode: "select",
            whitelist: whitelist,
            tagTextProp: 'label',
            userInput: false,
            dropdown: { enabled: 0, searchKeys: ["label"] }
          });

          tagify.on("dropdown:show", e => {
            if (e.detail.items) {
              e.detail.items.forEach(item => {
                item.value = item.data.value;
              });
            }
          });

          tagify.on("add", e => {
            const index = whitelist.indexOf(e.detail.data.label);
            if (index !== -1) {
              input.value = values[index];
              hiddenInput.value = values[index];
            }
          });

          tagify.on("remove", () => {
            if (tagify.value.length === 0) {
              input.value = "";
              hiddenInput.value = "";
            }
          });

          tagify.on("change", () => {
            const selectedLabels = tagify.value.map(tag => tag.value);
            const selectedValues = selectedLabels.map(label => {
              const item = data.find(d => d.label === label);
              return item ? item.value : null;
            }).filter(value => value !== null);
            hiddenInput.value = selectedValues.join(',');
          });

        });
      },

      clicksubmit(event) {
        event.preventDefault();
        let vnx_perpage = $(this.$el).find('input[name="vnx_perpage"]').val();
        let vnx_post_type = $(this.$el).find('input[name="vnx_post_type"]').val();
        let current_page = $(this.$el).find('input[name="vnx_current_page"]').val();
        let meta_keys = [];
        document.querySelectorAll(".vnx_content_item").forEach(item => {
          const hiddenInput = item.querySelector("input[type='hidden']");
          if (hiddenInput && hiddenInput.name && hiddenInput.value) {
            meta_keys.push({
              key: hiddenInput.name,
              value: hiddenInput.value,
              compare: '='
            });
          }
        });
        var data = {
          perpage: vnx_perpage,
          post_type: vnx_post_type,
          meta_keys: meta_keys,
          current_page: current_page
        }
        this.$eventBus.$emit("clickfillterPost", data);
      },

      //execute ajax
      vnx_post_ajax(type, action, data) {
        return $.ajax({
          url: admin_ajax_url,
          type: type,
          dataType: "json",
          async: true,
          timeout: 20000,
          data: {
            action: action,
            data: data,
          },
        });
      },

    },
  });
});

document.querySelectorAll(".brxe-vnx-posts-fillter-v2.jobs").forEach(el => {
  new Vue({
    el: `#${el.id}`,
    data: {
    },
    mounted() {
      this.handleClickOutside = (event) => {
        $(".container_posts_filter").each(function () {
          if (!$(this).is(event.target) && $(this).has(event.target).length === 0) {
            $(this).find(".select-btn").removeClass("open");
          }
        });
      };

      $(document).on("click", this.handleClickOutside);
    },
    methods: {

      clickSelect(event) {
        let selectBtn = $(event.currentTarget);
        let container = selectBtn.closest(".container_posts_filter");
        let listItems = container.find(".list-items");
        let btnText = selectBtn.find(".btn-text");
        let btnCount = selectBtn.find(".btn-text-count");
        let hiddenInput = container.find(".vnx_filter_value");
        if (!selectBtn.data("default-text")) {
          selectBtn.data("default-text", btnText.text().trim());
        }
        selectBtn.toggleClass("open");
        listItems.find(".item").off("click").on("click", function () {
          $(this).toggleClass("checked");
          let selectedValues = [];
          listItems.find(".checked").each(function () {
            selectedValues.push($(this).data("filter_value"));
          });
          hiddenInput.val(selectedValues.join(","));
          if (selectedValues.length > 0) {
            btnCount.css("display", "block");
            btnCount.text(selectedValues.length);
            btnText.text(`${selectBtn.data("default-text")}`);
          } else {
            btnCount.css("display", "none");
            btnText.text(selectBtn.data("default-text"));
          }
        });
      },

      clickSubmit(event) {
        event.preventDefault();
        let vnx_perpage = $(this.$el).find('input[name="vnx_perpage"]').val();
        let vnx_post_type = $(this.$el).find('input[name="vnx_post_type"]').val();
        let current_page = $(this.$el).find('input[name="vnx_current_page"]').val();
        let meta_keys = [];
        document.querySelectorAll(".container_posts_filter").forEach(item => {
          const hiddenInput = item.querySelector("input[type='hidden']");
          if (hiddenInput && hiddenInput.name && hiddenInput.value) {
            const values = hiddenInput.value.split(',');
            values.forEach(value => {
              meta_keys.push({
                key: hiddenInput.name,
                value: value,
                compare: '='
              });
            });
          }
        });
        var data = {
          perpage: vnx_perpage,
          post_type: vnx_post_type,
          meta_keys: meta_keys,
          current_page: current_page
        }
        this.$eventBus.$emit("clickfillterPost", data);
      },

      clickClearAll(event) {
        event.preventDefault();
        document.querySelectorAll(".container_posts_filter").forEach(item => {
          const boxfilter_list = item.querySelector(".list-items");
          const boxfilter_select = item.querySelector(".select-btn");
          const hiddenInput = item.querySelector("input[type='hidden']");
          if (hiddenInput && hiddenInput.name && hiddenInput.value) {
            hiddenInput.value = "";
          }
          if (boxfilter_list) {
            boxfilter_list.querySelectorAll(".item").forEach(item => {
              item.classList.remove("checked");
            });
          }
          if (boxfilter_select) {
            boxfilter_select.classList.remove("open");
            const btnCount = boxfilter_select.querySelector(".btn-text-count");
            btnCount.style.display = "none";
          }
        });
      },

      //execute ajax
      vnx_post_ajax(type, action, data) {
        return $.ajax({
          url: admin_ajax_url,
          type: type,
          dataType: "json",
          async: true,
          timeout: 20000,
          data: {
            action: action,
            data: data,
          },
        });
      },

    },
  });
});