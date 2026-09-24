window.jQuery = window.$ = jQuery;
var admin_ajax_url = $(location).attr("origin") + "/wp-admin/admin-ajax.php";
document.querySelectorAll(".brxe-vnx-theme-posts.theme_posts").forEach((el, index) => {  
  new Vue({
    el: `#${el.id}`,
    data: {
      LoadingResult: false,
      LoadingFillter: false,
      MaxNumPages: 0,
      CurrentPage: 1,
      originalPrevBtnHtml: null,
      originalNextBtnHtml: null,
      popup: null,
    },
    mounted() {
      const dataInput = this.$el.querySelector('.vnx-theme-posts-data');
      if (dataInput) {
        this.CurrentPage = parseInt(dataInput.dataset.currentPage) || 1;
        this.MaxNumPages = parseInt(dataInput.dataset.maxPages) || 0;
      }

      this.saveOriginalPaginationButtons();
      this.initPagination();
      this.initFilter();
      
      this.loadPosts(this.CurrentPage);
    },
    methods: {
      saveOriginalPaginationButtons() {
        const $pagination = $(this.$el).find('.vnx-theme-posts-pagination');
        if ($pagination.length) {
          const $prevBtn = $pagination.find('.vnx-theme-posts-pagination-button[data-page="prev"]');
          const $nextBtn = $pagination.find('.vnx-theme-posts-pagination-button[data-page="next"]');

          if ($prevBtn.length) {
            this.originalPrevBtnHtml = $prevBtn[0].outerHTML;
          }
          if ($nextBtn.length) {
            this.originalNextBtnHtml = $nextBtn[0].outerHTML;
          }
        }
      },

      initPagination() {
        const self = this;
        $(this.$el).on('click', '.vnx-theme-posts-pagination-button, .vnx-theme-posts-pagination-number', function(e) {
          e.preventDefault();
          const $btn = $(this);
          const pageData = $btn.data('page');
          
          if (pageData === 'prev') {
            if (self.CurrentPage > 1) {
              self.clickPagination(self.CurrentPage - 1);
            }
          } else if (pageData === 'next') {
            if (self.CurrentPage < self.MaxNumPages) {
              self.clickPagination(self.CurrentPage + 1);
            }
          } else if (typeof pageData === 'number') {
            self.clickPagination(pageData);
          }
          self.scrollToTop();
        });
      },

      initFilter() {
        const self = this;
        $(this.$el).on('change', '.vnx-theme-posts-sidebar .vnx-theme-posts-filter-item input[type="checkbox"]', function () {
          if (self.LoadingResult) {
            $(this).prop('checked', !$(this).prop('checked'));
            return;
          }

          const checkbox = this;
          const value = $(checkbox).val();

          self.syncCheckboxByValue(value, checkbox.checked, 'sidebar');
          self.CurrentPage = 1;
          self.loadPosts(1);
        });
      },

      disableAllCheckboxes() {
        const sidebarCheckboxes = this.$el.querySelectorAll('.vnx-theme-posts-sidebar input[type="checkbox"]');
        const popupTemplate = this.$el.querySelector('.vnx-theme-posts-popup-template');
        const popupCheckboxes = popupTemplate ? popupTemplate.querySelectorAll('input[type="checkbox"]') : [];

        sidebarCheckboxes.forEach((checkbox) => {
          checkbox.disabled = true;
        });

        popupCheckboxes.forEach((checkbox) => {
          checkbox.disabled = true;
        });
      },

      enableAllCheckboxes() {
        const sidebarCheckboxes = this.$el.querySelectorAll('.vnx-theme-posts-sidebar input[type="checkbox"]');
        const popupTemplate = this.$el.querySelector('.vnx-theme-posts-popup-template');
        const popupCheckboxes = popupTemplate ? popupTemplate.querySelectorAll('input[type="checkbox"]') : [];

        sidebarCheckboxes.forEach((checkbox) => {
          checkbox.disabled = false;
        });

        popupCheckboxes.forEach((checkbox) => {
          checkbox.disabled = false;
        });
      },

      setupPopupEvents() {
        const popupTemplate = this.$el.querySelector('.vnx-theme-posts-popup-template');
        if (!popupTemplate) return;

        const self = this;
        const openPopupBtn = this.$el.querySelector('.vnx-theme-posts-open-popup');
        const closePopupBtn = popupTemplate.querySelector('.vnx-theme-posts-popup-close');
        const overlay = popupTemplate.querySelector('.vnx-theme-posts-popup-overlay');
        const applyBtn = popupTemplate.querySelector('.vnx-theme-posts-popup-apply');
        const popupContent = popupTemplate.querySelector('.vnx-theme-posts-popup');
        const popupCheckboxes = popupTemplate.querySelectorAll('.vnx-theme-posts-filter-item input[type="checkbox"]');

        if (openPopupBtn) {
          $(openPopupBtn).on('click', () => {
            this.openPopup();
          });
        }

        if (closePopupBtn) {
          $(closePopupBtn).on('click', () => {
            this.closePopup();
          });
        }

        if (overlay) {
          $(overlay).on('click', () => {
            this.closePopup();
          });
        }

        if (applyBtn) {
          $(applyBtn).on('click', () => {
            this.applyFilters();
          });
        }

        if (popupContent) {
          $(popupContent).on('click', (e) => {
            e.stopPropagation();
          });
        }

        $(popupTemplate).on('click', (e) => {
          if (e.target === popupTemplate) {
            this.closePopup();
          }
        });

        popupCheckboxes.forEach((checkbox) => {
          $(checkbox).off('change.sync').on('change.sync', function () {
            if (self.LoadingResult) {
              $(this).prop('checked', !$(this).prop('checked'));
              return;
            }

            const value = $(this).val();
            self.syncCheckboxByValue(value, this.checked, 'popup');
          });
        });

        $(document).on('keydown', (e) => {
          if (e.key === 'Escape' && popupTemplate && popupTemplate.style.display === 'flex') {
            this.closePopup();
          }
        });
      },

      syncCheckboxes(from, to) {
        from.forEach((checkbox, index) => {
          if (to[index]) {
            to[index].checked = checkbox.checked;
          }
        });
      },

      syncCheckboxByValue(value, checked, source) {
        const sidebarCheckboxes = this.$el.querySelectorAll('.vnx-theme-posts-sidebar input[type="checkbox"]');
        const popupTemplate = this.$el.querySelector('.vnx-theme-posts-popup-template');
        const popupCheckboxes = popupTemplate ? popupTemplate.querySelectorAll('input[type="checkbox"]') : [];

        if (source === 'sidebar') {
          popupCheckboxes.forEach((checkbox) => {
            if (checkbox.value === value) {
              checkbox.checked = checked;
            }
          });
        } else if (source === 'popup') {
          sidebarCheckboxes.forEach((checkbox) => {
            if (checkbox.value === value) {
              checkbox.checked = checked;
            }
          });
        }
      },

      openPopup() {
        this.popup = this.$el.querySelector('.vnx-theme-posts-popup-template');
        if (!this.popup) return;
        const sidebarCheckboxes = this.$el.querySelectorAll('.vnx-theme-posts-sidebar input[type="checkbox"]');
        const popupCheckboxes = this.popup.querySelectorAll('input[type="checkbox"]');

        this.syncCheckboxes(sidebarCheckboxes, popupCheckboxes);
        this.setupPopupEvents();
        this.popup.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      },

      closePopup(event) {
        if (!this.popup) return;

        if (event && event.target !== this.popup) {
          const popupContent = this.popup.querySelector('.vnx-theme-posts-popup');
          if (popupContent && popupContent.contains(event.target)) {
            return;
          }
        }

        this.popup.style.display = 'none';
        document.body.style.overflow = '';
      },

      applyFilters() {
        if (this.LoadingResult) return;

        const popupTemplate = this.$el.querySelector('.vnx-theme-posts-popup-template');
        if (!popupTemplate) return;

        const sidebarCheckboxes = this.$el.querySelectorAll('.vnx-theme-posts-sidebar input[type="checkbox"]');
        const popupCheckboxes = popupTemplate.querySelectorAll('input[type="checkbox"]');

        popupCheckboxes.forEach((popupCheckbox) => {
          const value = popupCheckbox.value;
          sidebarCheckboxes.forEach((sidebarCheckbox) => {
            if (sidebarCheckbox.value === value) {
              sidebarCheckbox.checked = popupCheckbox.checked;
            }
          });
        });

        this.CurrentPage = 1;
        this.loadPosts(1);
        this.closePopup();
      },

      clickPagination(page) {
        if (this.LoadingResult) return;
        if (page < 1 || (this.MaxNumPages > 0 && page > this.MaxNumPages)) return;
        
        this.CurrentPage = page;
        this.loadPosts(page);
      },

      loadPosts(page) {
        if (this.LoadingResult) return;
        
        this.LoadingResult = true;
        this.disableAllCheckboxes();

        const dataInput = this.$el.querySelector('.vnx-theme-posts-data');
        if (!dataInput) {
          this.LoadingResult = false;
          this.enableAllCheckboxes();
          return;
        }

        const postType = dataInput.dataset.postType || 'post';
        const postsPerPage = dataInput.dataset.postsPerPage || 6;
        const loopTemplate = dataInput.dataset.loopTemplate || '';
        const loopNotTemplate = dataInput.dataset.loopNotTemplate || '';
        const rootDiv = dataInput.dataset.rootDiv || 'bricks';

        const selectedFilters = this.getSelectedFilters();
        const taxId = selectedFilters.length > 0 ? selectedFilters.join(',') : 'all';

        const ajaxData = {
          action: 'vnx_load_theme_posts_center',
          ajax_paged: page,
          tax_id: taxId,
          posts_type: postType,
          posts_per_page: postsPerPage,
          current_page: window.location.href,
        };

        if (loopTemplate) {
          ajaxData.loop_card = loopTemplate;
        }

        if (loopNotTemplate) {
          ajaxData.loop_not_card = loopNotTemplate;
        }

        if (rootDiv === 'bricks') {
          ajaxData.root_div = rootDiv;
        }

        const $grid = $(this.$el).find('.vnx-theme-posts-grid');
        const $pagination = $(this.$el).find('.vnx-theme-posts-pagination');
        const $loadingPosts = $(this.$el).find('.loading_posts');
        const gridHeight = $grid.outerHeight() || 200;

        $grid.css('min-height', gridHeight + 'px');
        $loadingPosts.removeClass('hidden');

        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: admin_ajax_url,
          data: ajaxData,
        })
        .done((response) => {
          if (response.success && response.data) {
            const responseHtml = response.data.replace(/\\"/g, '"');
            const $response = $('<div>').html(responseHtml);
            
            const $cards = $response.find('.vnx-theme-posts-card');
            const $noResults = $response.find('.vnx-theme-posts-no-results');
            const $paginationLinks = $response.find('.paginate_links');

            $grid.css('min-height', '');
            $(this.$el).find('.loading_posts').addClass('hidden');
            
            if ($cards.length) {
              $grid.html($cards);
            } else if ($noResults.length) {
              $grid.html($noResults);
            } else {
              $grid.html('');
            }

            if ($paginationLinks.length) {
              const paginationContent = $paginationLinks.html();
              if (paginationContent) {
                this.updatePaginationUI(paginationContent);
              }
            } else {
              const $pagination = $(this.$el).find('.vnx-theme-posts-pagination');
              $pagination.empty();
            }

            const totalPagesMatch = responseHtml.match(/page-numbers.*>(\d+)<\/a>/g);
            if (totalPagesMatch && totalPagesMatch.length > 0) {
              const lastPageMatch = totalPagesMatch[totalPagesMatch.length - 1].match(/>(\d+)</);
              if (lastPageMatch) {
                this.MaxNumPages = parseInt(lastPageMatch[1]);
              }
            }

            const dataInput = this.$el.querySelector('.vnx-theme-posts-data');
            if (dataInput) {
              dataInput.dataset.currentPage = page;
              dataInput.dataset.maxPages = this.MaxNumPages;
            }

            this.scrollToTop();
          } else {
            this.handleError();
          }
          this.LoadingResult = false;
          this.enableAllCheckboxes();
        })
        .fail((jqXHR, textStatus, error) => {
          console.error('Pagination error:', textStatus, error);
          this.handleError();
          this.LoadingResult = false;
          this.enableAllCheckboxes();
        });
      },

      getSelectedFilters() {
        const selected = [];
        const sidebarCheckboxes = $(this.$el).find('.vnx-theme-posts-sidebar .vnx-theme-posts-filter-item input[type="checkbox"]:checked');
        sidebarCheckboxes.each(function () {
          selected.push($(this).val());
        });
        return selected;
      },

      scrollToTop() {
        const container = this.$el.querySelector('.vnx-theme-posts-container');
        if (container) {
          const offsetTop = container.getBoundingClientRect().top + window.pageYOffset - 100;
          window.scrollTo({
            top: offsetTop,
            behavior: 'smooth'
          });
        }
      },

      updatePaginationUI(paginationHtml) {
        const $pagination = $(this.$el).find('.vnx-theme-posts-pagination');
        if (!$pagination.length) return;

        let $wrapper = $pagination.find('.vnx-theme-pagi-wrapper');
        if (!$wrapper.length) {
          $wrapper = $('<div>').addClass('vnx-theme-pagi-wrapper');
          $pagination.append($wrapper);
        }

        const $prevBtn = $wrapper.find('.vnx-theme-posts-pagination-button[data-page="prev"]');
        const $nextBtn = $wrapper.find('.vnx-theme-posts-pagination-button[data-page="next"]');

        const $temp = $('<div>').html(paginationHtml);
        const $links = $temp.find('a, span');
        
        const paginationItems = [];
        
        $links.each((index, el) => {
          const $el = $(el);
          const text = $el.text().trim();
          const isEllipsis = $el.hasClass('dots') || 
                            ($el.hasClass('page-numbers') && (text === '…' || text === '...' || text === '&hellip;'));
          
          if (isEllipsis) {
            paginationItems.push({ type: 'ellipsis' });
          } else if ($el.hasClass('current') || $el.hasClass('page-numbers.current')) {
            const pageNum = parseInt(text) || 1;
            paginationItems.push({ type: 'page', num: pageNum, active: true });
            this.CurrentPage = pageNum;
          } else if ($el.is('a') && !$el.hasClass('prev') && !$el.hasClass('next')) {
            const pageNum = parseInt(text);
            if (!isNaN(pageNum)) {
              paginationItems.push({ type: 'page', num: pageNum, active: false });
            }
          }
        });

        const $existingItems = $wrapper.find('.vnx-theme-posts-pagination-number, .vnx-theme-posts-pagination-ellipsis');
        $existingItems.remove();

        if (!$prevBtn.length) {
          if (this.originalPrevBtnHtml) {
            $wrapper.prepend(this.originalPrevBtnHtml);
          } else {
            const $newPrevBtn = $('<button>')
              .addClass('vnx-theme-posts-pagination-button')
              .attr('data-page', 'prev');
            $wrapper.prepend($newPrevBtn);
          }
        }

        if (!$nextBtn.length) {
          if (this.originalNextBtnHtml) {
            $wrapper.append(this.originalNextBtnHtml);
          } else {
            const $newNextBtn = $('<button>')
              .addClass('vnx-theme-posts-pagination-button')
              .attr('data-page', 'next');
            $wrapper.append($newNextBtn);
          }
        }

        const $finalNextBtn = $wrapper.find('.vnx-theme-posts-pagination-button[data-page="next"]');
        
        paginationItems.forEach((item) => {
          if (item.type === 'ellipsis') {
            const $ellipsis = $('<span>').addClass('vnx-theme-posts-pagination-ellipsis').text('...');
            $finalNextBtn.before($ellipsis);
          } else if (item.type === 'page') {
            const $newBtn = $('<button>')
              .addClass('vnx-theme-posts-pagination-number' + (item.active ? ' active' : ''))
              .attr('data-page', item.num)
              .text(item.num);
            $finalNextBtn.before($newBtn);
          }
        });
      },

      handleError() {
        const $grid = $(this.$el).find('.vnx-theme-posts-grid');
        $grid.css('min-height', '');
        $(this.$el).find('.loading_posts').addClass('hidden');
        $grid.prepend('<div class="vnx-error-message" style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #d32f2f;"><p>Đã có lỗi xảy ra. Vui lòng thử lại sau!</p></div>');
      }
    }
  });
});
