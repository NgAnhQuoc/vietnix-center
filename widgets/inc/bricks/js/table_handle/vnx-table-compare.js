document.querySelectorAll('.brxe-vnx-table-compare-service.table-compare-service').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      listService: [],
      containerId: containerId,
      referenceGridValue: '',
      currentIndex: 1,
      totalItems: 0,
    },
    mounted() {
      this.saveReferenceGridValue();
      this.initNavigation();
      this.showService();
      window.addEventListener('resize', this.showService);
      window.addEventListener('load', this.showService);
    },
    beforeDestroy() {
      window.removeEventListener('resize', this.showService);
      window.removeEventListener('load', this.showService);
    },
    methods: {
      saveReferenceGridValue() {
        const firstDataRow = this.$el.querySelector('.vnx-table-row .vnx-table-cell-list');
        if (firstDataRow) {
          const inlineStyle = firstDataRow.getAttribute('style');
          let gridValue = '';

          if (inlineStyle && inlineStyle.includes('grid-template-columns')) {
            const match = inlineStyle.match(/grid-template-columns:\s*([^;]+)/);
            if (match) {
              gridValue = match[1].trim();
            }
          }

          if (!gridValue) {
            gridValue = window.getComputedStyle(firstDataRow).gridTemplateColumns;
          }

          this.referenceGridValue = gridValue;
        }
      },

      getReferenceGridValue() {
        if (this.referenceGridValue) {
          return this.referenceGridValue;
        }

        const firstDataRow = this.$el.querySelector('.vnx-table-row .vnx-table-cell-list');
        if (firstDataRow) {
          const inlineStyle = firstDataRow.getAttribute('style');
          if (inlineStyle && inlineStyle.includes('grid-template-columns')) {
            const match = inlineStyle.match(/grid-template-columns:\s*([^;]+)/);
            if (match) {
              return match[1].trim();
            }
          }
        }

        return '';
      },

      initNavigation() {
        const cellItems = this.$el.querySelectorAll('.vnx-table-cell-item');
        const maxIndex = Math.max(...Array.from(cellItems).map(item =>
          parseInt(item.getAttribute('data-col-index'), 10)
        ));
        this.totalItems = maxIndex;

        const prevButton = this.$el.querySelector('.vnx-nav-button.prev');
        const nextButton = this.$el.querySelector('.vnx-nav-button.next');

        if (prevButton) {
          prevButton.addEventListener('click', this.handlePrev);
        }
        if (nextButton) {
          nextButton.addEventListener('click', this.handleNext);
        }
      },

      handleNext() {
        const breakpoint = 768;
        const isMobile = window.innerWidth <= breakpoint;

        if (!isMobile) return;

        const maxIndex = this.totalItems - 2;
        if (this.currentIndex < maxIndex) {
          this.currentIndex++;
          this.updateNavigation();
        }
      },

      handlePrev() {
        const breakpoint = 768;
        const isMobile = window.innerWidth <= breakpoint;

        if (!isMobile) return;

        if (this.currentIndex > 1) {
          this.currentIndex--;
          this.updateNavigation();
        }
      },

      updateNavigation() {
        const breakpoint = 768;
        const isMobile = window.innerWidth <= breakpoint;

        if (!isMobile) return;

        const cellItems = this.$el.querySelectorAll('.vnx-table-cell-item');
        const prevButton = this.$el.querySelector('.vnx-nav-button.prev');
        const nextButton = this.$el.querySelector('.vnx-nav-button.next');

        cellItems.forEach(item => {
          const colIndex = parseInt(item.getAttribute('data-col-index'), 10);
          const visibleStart = this.currentIndex;
          const visibleEnd = this.currentIndex + 2;

          if (colIndex >= visibleStart && colIndex <= visibleEnd) {
            item.classList.remove('hidden');
          } else {
            item.classList.add('hidden');
          }
        });

        if (prevButton) {
          prevButton.disabled = this.currentIndex === 1;
        }
        if (nextButton) {
          const maxIndex = this.totalItems - 2;
          nextButton.disabled = this.currentIndex >= maxIndex;
        }
      },

      showService() {
        const breakpoint = 768;
        const isMobile = window.innerWidth <= breakpoint;
        const cellItems = this.$el.querySelectorAll('.vnx-table-cell-item');
        const cellLists = this.$el.querySelectorAll('.vnx-table-cell-list');

        if (isMobile) {
          this.currentIndex = 1;
          cellItems.forEach(item => {
            const colIndex = parseInt(item.getAttribute('data-col-index'), 10);

            if (colIndex >= 1 && colIndex <= 3) {
              item.classList.remove('hidden');
            } else {
              item.classList.add('hidden');
            }
          });

          cellLists.forEach((list, index) => {
            list.style.gridTemplateColumns = 'repeat(3, 1fr)';
          });

          this.updateNavigation();
        } else {
          cellItems.forEach(item => {
            item.classList.remove('hidden');
          });

          const gridValue = this.getReferenceGridValue();
          if (gridValue) {
            cellLists.forEach((list) => {
              list.style.gridTemplateColumns = gridValue;
            });
          }

          const prevButton = this.$el.querySelector('.vnx-nav-button.prev');
          const nextButton = this.$el.querySelector('.vnx-nav-button.next');
          if (prevButton) prevButton.disabled = false;
          if (nextButton) nextButton.disabled = false;
        }
      }
    },
  });
});

document.querySelectorAll('.brxe-vnx-table-compare-service.table-compare-service-hosting').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      listService: [],
      containerId: containerId,
      referenceGridValue: '',
      currentIndex: 1,
      totalItems: 0,
      countItems: 0,
    },
    mounted() {
      const wrapper = this.$el.querySelector('.vnx-table-compare-wrapper');
      if (wrapper) {
        this.countItems = parseInt(wrapper.getAttribute('data-count-items')) || 0;
      }

      this.saveReferenceGridValue();
      this.initNavigation();
      this.showService();
      window.addEventListener('resize', this.showService);
      window.addEventListener('load', this.showService);
    },
    beforeDestroy() {
      window.removeEventListener('resize', this.showService);
      window.removeEventListener('load', this.showService);
    },
    methods: {
      saveReferenceGridValue() {
        const firstDataRow = this.$el.querySelector('.vnx-table-row .vnx-table-cell-list');
        if (firstDataRow) {
          const inlineStyle = firstDataRow.getAttribute('style');
          let gridValue = '';

          if (inlineStyle && inlineStyle.includes('grid-template-columns')) {
            const match = inlineStyle.match(/grid-template-columns:\s*([^;]+)/);
            if (match) {
              gridValue = match[1].trim();
            }
          }

          if (!gridValue) {
            gridValue = window.getComputedStyle(firstDataRow).gridTemplateColumns;
          }

          this.referenceGridValue = gridValue;
        }
      },

      getReferenceGridValue() {
        if (this.referenceGridValue) {
          return this.referenceGridValue;
        }

        const firstDataRow = this.$el.querySelector('.vnx-table-row .vnx-table-cell-list');
        if (firstDataRow) {
          const inlineStyle = firstDataRow.getAttribute('style');
          if (inlineStyle && inlineStyle.includes('grid-template-columns')) {
            const match = inlineStyle.match(/grid-template-columns:\s*([^;]+)/);
            if (match) {
              return match[1].trim();
            }
          }
        }

        return '';
      },

      initNavigation() {
        const cellItems = this.$el.querySelectorAll('.vnx-table-cell-item');
        const maxIndex = Math.max(...Array.from(cellItems).map(item =>
          parseInt(item.getAttribute('data-col-index'), 10)
        ));
        this.totalItems = maxIndex;

        const prevButtons = this.$el.querySelectorAll('.vnx-nav-button.prev');
        const nextButtons = this.$el.querySelectorAll('.vnx-nav-button.next');

        prevButtons.forEach(button => {
          button.addEventListener('click', this.handlePrev);
        });

        nextButtons.forEach(button => {
          button.addEventListener('click', this.handleNext);
        });
      },

      handleNext() {
        const breakpoint = 768;
        const isMobile = window.innerWidth <= breakpoint;
        const maxVisibleDesktop = 6;
        const totalColumns = this.countItems || this.totalItems;
        const visibleCount = isMobile ? 3 : Math.min(totalColumns, maxVisibleDesktop);
        const maxIndex = this.totalItems - visibleCount + 1;

        if (this.currentIndex < maxIndex) {
          this.currentIndex++;
          this.updateNavigation();
        }
      },

      handlePrev() {
        if (this.currentIndex > 1) {
          this.currentIndex--;
          this.updateNavigation();
        }
      },

      updateNavigation() {
        const breakpoint = 768;
        const isMobile = window.innerWidth <= breakpoint;
        const maxVisibleDesktop = 6;
        const totalColumns = this.countItems || this.totalItems;
        const visibleCount = isMobile ? 3 : Math.min(totalColumns, maxVisibleDesktop);
        const cellItems = this.$el.querySelectorAll('.vnx-table-cell-item');
        const prevButtons = this.$el.querySelectorAll('.vnx-nav-button.prev');
        const nextButtons = this.$el.querySelectorAll('.vnx-nav-button.next');

        cellItems.forEach(item => {
          const colIndex = parseInt(item.getAttribute('data-col-index'), 10);
          const visibleStart = this.currentIndex;
          const visibleEnd = this.currentIndex + visibleCount - 1;

          if (colIndex >= visibleStart && colIndex <= visibleEnd) {
            item.classList.remove('hidden');
          } else {
            item.classList.add('hidden');
          }
        });

        prevButtons.forEach(button => {
          button.disabled = this.currentIndex === 1;
        });

        nextButtons.forEach(button => {
          const maxIndex = this.totalItems - visibleCount + 1;
          button.disabled = this.currentIndex >= maxIndex;
        });
      },

      showService() {
        const breakpoint = 768;
        const isMobile = window.innerWidth <= breakpoint;
        const cellItems = this.$el.querySelectorAll('.vnx-table-cell-item');
        const cellLists = this.$el.querySelectorAll('.vnx-table-cell-list');
        const maxVisibleDesktop = 6;
        const totalColumns = this.countItems || this.totalItems;
        const visibleCount = isMobile ? 3 : Math.min(totalColumns, maxVisibleDesktop);

        this.currentIndex = 1;

        cellItems.forEach(item => {
          const colIndex = parseInt(item.getAttribute('data-col-index'), 10);

          if (colIndex >= 1 && colIndex <= visibleCount) {
            item.classList.remove('hidden');
          } else {
            item.classList.add('hidden');
          }
        });

        cellLists.forEach((list) => {
          list.style.gridTemplateColumns = `repeat(${visibleCount}, 1fr)`;
        });

        this.updateNavigation();
      }
    },
  });
});