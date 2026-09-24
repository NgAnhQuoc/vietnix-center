Vue.prototype.$eventBus = new Vue();

let splideTabServicePrice = {};

//version Tab Service Price
document.querySelectorAll('.brxe-vnx-tab-service-price.tab-service-price').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      listService: [],
      containerId: containerId,
    },
    mounted() {
      this.listService = this.$el.querySelectorAll('.vnx-tab-package-item');

      if (window.innerWidth <= 991) {
        this.handleSplideForMobile();
      }

      this.initTabSwitching();
      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          this.handleSplideForMobile();
        }, 150);
      });
    },
    methods: {
      getListService() {
        return this.listService;
      },
      initSplideForPackageItem(packageItem) {
        const itemId = packageItem.getAttribute('data-service') || packageItem.className;
        const uniqueId = `${this.containerId}-splide-${itemId.replace(/\s+/g, '-').toLowerCase()}`;

        if (splideTabServicePrice[uniqueId]) {
          return;
        }

        const packageCards = Array.from(packageItem.querySelectorAll('.vnx-package-card'));
        if (packageCards.length === 0) return;

        const wasHidden = packageItem.style.display === 'none';
        if (wasHidden) {
          packageItem.style.visibility = 'hidden';
          packageItem.style.display = '';
        }

        const splideContainer = document.createElement('div');
        splideContainer.className = 'splide vnx-tab-package-splide';
        splideContainer.setAttribute('id', uniqueId);

        const splideArrows = document.createElement('div');
        splideArrows.className = 'splide__arrows splide__arrows--ltr';
        splideArrows.innerHTML = `
          <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Previous slide"><i class="ion-ios-arrow-back brxe-icon"></i></button>
          <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide"><i class="ion-ios-arrow-forward brxe-icon"></i></button>
        `;

        const splideTrack = document.createElement('div');
        splideTrack.className = 'splide__track';

        const splideList = document.createElement('div');
        splideList.className = 'splide__list';

        packageCards.forEach((card) => {
          const slide = document.createElement('div');
          slide.className = 'splide__slide';
          slide.appendChild(card);
          splideList.appendChild(slide);
        });

        splideTrack.appendChild(splideList);
        splideContainer.appendChild(splideArrows);
        splideContainer.appendChild(splideTrack);
        packageItem.appendChild(splideContainer);

        const splide = new Splide('#' + uniqueId, {
          type: 'loop',
          perPage: 1,
          perMove: 1,
          autoplay: false,
          pagination: true,
          arrows: true,
          drag: true,
          gap: '16px',
          focus: 'center',
          start: 1,
        });

        splide.mount();
        splideTabServicePrice[uniqueId] = splide;

        if (wasHidden) {
          packageItem.style.display = 'none';
          packageItem.style.visibility = '';
        }
      },
      destroySplideForPackageItem(packageItem) {
        const splideContainer = packageItem.querySelector('.vnx-tab-package-splide');
        if (!splideContainer) return;

        const uniqueId = splideContainer.getAttribute('id');
        if (splideTabServicePrice[uniqueId]) {
          splideTabServicePrice[uniqueId].destroy();
          delete splideTabServicePrice[uniqueId];
        }

        const slides = splideContainer.querySelectorAll('.splide__slide');
        slides.forEach((slide) => {
          const card = slide.querySelector('.vnx-package-card');
          if (card) {
            packageItem.appendChild(card);
          }
        });

        splideContainer.remove();
      },
      handleSplideForMobile() {
        const isMobile = window.innerWidth <= 991;
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          if (isMobile) {
            const hasSplide = packageItem.querySelector('.vnx-tab-package-splide');
            if (!hasSplide) {
              this.initSplideForPackageItem(packageItem);
            }
          } else {
            this.destroySplideForPackageItem(packageItem);
          }
        });
      },
      initTabSwitching() {
        const tabItems = this.$el.querySelectorAll('.vnx-tab-cycle-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        if (tabItems.length === 0 || packageItems.length === 0) return;

        const switchTab = (selectedTab) => {
          const serviceName = selectedTab.getAttribute('data-service');

          tabItems.forEach((tab) => {
            tab.classList.remove('active');
          });
          selectedTab.classList.add('active');

          packageItems.forEach((packageItem) => {
            const packageServiceName = packageItem.getAttribute('data-service');
            if (packageServiceName === serviceName) {
              packageItem.style.display = '';
            } else {
              packageItem.style.display = 'none';
            }
          });
        };

        tabItems.forEach((tab) => {
          tab.addEventListener('click', () => {
            switchTab(tab);
          });
        });

        const firstTab = tabItems[0];
        if (firstTab) {
          switchTab(firstTab);
        }
      },
    },
  });
});

//version Tab Service Price VPS
document.querySelectorAll('.brxe-vnx-tab-service-price.tab-service-price-vps').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      listService: [],
      containerId: containerId,
    },
    mounted() {
      this.listService = this.$el.querySelectorAll('.vnx-tab-package-item');

      if (window.innerWidth <= 991) {
        this.handleSplideForMobile();
      }

      this.initTabSwitching();
      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          this.handleSplideForMobile();
        }, 150);
      });
    },
    methods: {
      getListService() {
        return this.listService;
      },
      initSplideForPackageItem(packageItem) {
        const itemId = packageItem.getAttribute('data-service') || packageItem.className;
        const uniqueId = `${this.containerId}-splide-${itemId.replace(/\s+/g, '-').toLowerCase()}`;

        if (splideTabServicePrice[uniqueId]) {
          return;
        }

        const packageCards = Array.from(packageItem.querySelectorAll('.vnx-package-card'));
        if (packageCards.length === 0) return;

        const wasHidden = packageItem.style.display === 'none';
        if (wasHidden) {
          packageItem.style.visibility = 'hidden';
          packageItem.style.display = '';
        }

        const splideContainer = document.createElement('div');
        splideContainer.className = 'splide vnx-tab-package-splide';
        splideContainer.setAttribute('id', uniqueId);

        const splideArrows = document.createElement('div');
        splideArrows.className = 'splide__arrows splide__arrows--ltr';
        splideArrows.innerHTML = `
          <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Previous slide"><i class="ion-ios-arrow-back brxe-icon"></i></button>
          <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide"><i class="ion-ios-arrow-forward brxe-icon"></i></button>
        `;

        const splideTrack = document.createElement('div');
        splideTrack.className = 'splide__track';

        const splideList = document.createElement('div');
        splideList.className = 'splide__list';

        packageCards.forEach((card) => {
          const slide = document.createElement('div');
          slide.className = 'splide__slide';
          slide.appendChild(card);
          splideList.appendChild(slide);
        });

        splideTrack.appendChild(splideList);
        splideContainer.appendChild(splideArrows);
        splideContainer.appendChild(splideTrack);
        packageItem.appendChild(splideContainer);

        const splide = new Splide('#' + uniqueId, {
          type: 'loop',
          perPage: 1,
          perMove: 1,
          autoplay: false,
          pagination: true,
          arrows: true,
          drag: true,
          gap: '16px',
          focus: 'center',
          start: 1,
        });

        splide.mount();
        splideTabServicePrice[uniqueId] = splide;

        if (wasHidden) {
          packageItem.style.display = 'none';
          packageItem.style.visibility = '';
        }
      },
      destroySplideForPackageItem(packageItem) {
        const splideContainer = packageItem.querySelector('.vnx-tab-package-splide');
        if (!splideContainer) return;

        const uniqueId = splideContainer.getAttribute('id');
        if (splideTabServicePrice[uniqueId]) {
          splideTabServicePrice[uniqueId].destroy();
          delete splideTabServicePrice[uniqueId];
        }

        const slides = splideContainer.querySelectorAll('.splide__slide');
        slides.forEach((slide) => {
          const card = slide.querySelector('.vnx-package-card');
          if (card) {
            packageItem.appendChild(card);
          }
        });

        splideContainer.remove();
      },
      handleSplideForMobile() {
        const isMobile = window.innerWidth <= 991;
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          if (isMobile) {
            const hasSplide = packageItem.querySelector('.vnx-tab-package-splide');
            if (!hasSplide) {
              this.initSplideForPackageItem(packageItem);
            }
          } else {
            this.destroySplideForPackageItem(packageItem);
          }
        });
      },
      initTabSwitching() {
        const tabItems = this.$el.querySelectorAll('.vnx-tab-cycle-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        if (tabItems.length === 0 || packageItems.length === 0) return;

        const switchTab = (selectedTab) => {
          const serviceName = selectedTab.getAttribute('data-service');

          tabItems.forEach((tab) => {
            tab.classList.remove('active');
          });
          selectedTab.classList.add('active');

          packageItems.forEach((packageItem) => {
            const packageServiceName = packageItem.getAttribute('data-service');
            if (packageServiceName === serviceName) {
              packageItem.style.display = '';
            } else {
              packageItem.style.display = 'none';
            }
          });
        };

        tabItems.forEach((tab) => {
          tab.addEventListener('click', () => {
            switchTab(tab);
          });
        });

        const firstTab = tabItems[0];
        if (firstTab) {
          switchTab(firstTab);
        }
      },
    },
  });
});

//version Tab Service Price group
document.querySelectorAll('.brxe-vnx-tab-service-price.tab-service-price-group').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      listService: [],
      containerId: containerId,
    },
    mounted() {
      this.listService = this.$el.querySelectorAll('.vnx-tab-package-item');
      this.initTabSwitching();
      this.initDurationSwitching();
      this.initMobileDropdowns();
      this.initDragToScroll();
    },
    methods: {
      getListService() {
        return this.listService;
      },
      //chuyển tab dịch vụ
      initTabSwitching() {
        const tabItems = this.$el.querySelectorAll('.vnx-service-nav-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        if (tabItems.length === 0 || packageItems.length === 0) return;

        const switchTab = (selectedTab) => {
          const serviceName = selectedTab.getAttribute('data-service');

          tabItems.forEach((tab) => {
            tab.classList.remove('active');
          });
          selectedTab.classList.add('active');

          packageItems.forEach((packageItem) => {
            const packageServiceName = packageItem.getAttribute('data-service');
            if (packageServiceName === serviceName) {
              packageItem.style.display = '';
            } else {
              packageItem.style.display = 'none';
            }
          });
        };

        tabItems.forEach((tab) => {
          tab.addEventListener('click', () => {
            switchTab(tab);
          });
        });

        const firstTab = tabItems[0];
        if (firstTab) {
          switchTab(firstTab);
        }
      },

      //chuyển tab gói dịch vụ
      initDurationSwitching() {
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          const durationItems = packageItem.querySelectorAll('.vnx-duration-item');
          const priceCards = packageItem.querySelectorAll('.vnx-price-card');

          if (durationItems.length === 0) return;

          const switchDuration = (selectedDurationItem) => {
            const durationIndex = Array.from(durationItems).indexOf(selectedDurationItem);

            durationItems.forEach((item) => {
              item.classList.remove('active', 'popular');
            });
            selectedDurationItem.classList.add('active');

            priceCards.forEach((priceCard) => {
              const priceSections = priceCard.querySelectorAll('.vnx-price-section');
              const registerButtons = priceCard.querySelectorAll('.vnx-register-list .vnx-button-register');
              const planTitleDiscounts = priceCard.querySelectorAll('.vnx-plan-title-discount');
              const promotionBanners = priceCard.querySelectorAll('.vnx-promotion-banner');

              priceSections.forEach((section, index) => {
                if (index === durationIndex) {
                  section.classList.remove('hidden');
                } else {
                  section.classList.add('hidden');
                }
              });

              registerButtons.forEach((button, index) => {
                if (index === durationIndex) {
                  button.classList.remove('hidden');
                } else {
                  button.classList.add('hidden');
                }
              });

              planTitleDiscounts.forEach((discount, index) => {
                if (index === durationIndex) {
                  discount.classList.remove('hidden');
                } else {
                  discount.classList.add('hidden');
                }
              });

              promotionBanners.forEach((banner, index) => {
                if (index === durationIndex) {
                  banner.classList.remove('hidden');
                } else {
                  banner.classList.add('hidden');
                }
              });
            });
          };

          durationItems.forEach((durationItem) => {
            durationItem.addEventListener('click', () => {
              switchDuration(durationItem);
            });
          });

          const activeDuration = packageItem.querySelector('.vnx-duration-item.active');
          if (activeDuration) {
            switchDuration(activeDuration);
          } else if (durationItems.length > 0) {
            switchDuration(durationItems[0]);
          }
        });
      },

      //copy mã khuyến mãi
      actionCopy(e) {
        const button = e.currentTarget;
        const promotionBanner = button.closest('.vnx-promotion-banner');
        if (!promotionBanner) return;

        const promotionDiscountValue = promotionBanner.querySelector('.vnx-promotion-discount-value');
        const iconCopy = button.querySelector('.vnx-icon-copy');
        const iconPaste = button.querySelector('.vnx-icon-paste');

        if (promotionDiscountValue) {
          const textToCopy = promotionDiscountValue.textContent.trim();
          navigator.clipboard.writeText(textToCopy).then(() => {
            if (iconCopy) iconCopy.classList.add('hidden');
            if (iconPaste) iconPaste.classList.remove('hidden');

            setTimeout(() => {
              if (iconCopy) iconCopy.classList.remove('hidden');
              if (iconPaste) iconPaste.classList.add('hidden');
            }, 2000);
          });
        }
      },

      //mobile dropdowns
      initMobileDropdowns() {
        this.initServiceDropdown();
        this.initDurationDropdowns();
        this.handleClickOutside();
      },

      initServiceDropdown() {
        const serviceDropdown = this.$el.querySelector('.vnx-service-nav-mobile');
        if (!serviceDropdown) return;

        const button = serviceDropdown.querySelector('.vnx-dropdown-button');
        const menu = serviceDropdown.querySelector('.vnx-dropdown-menu');
        const items = menu.querySelectorAll('.vnx-dropdown-item');
        const selectedText = button.querySelector('.vnx-dropdown-selected');

        button.addEventListener('click', (e) => {
          e.stopPropagation();
          const wrapper = button.closest('.vnx-dropdown-wrapper');
          wrapper.classList.toggle('active');
        });

        items.forEach((item) => {
          item.addEventListener('click', () => {
            const serviceName = item.getAttribute('data-service');
            selectedText.textContent = item.textContent.trim();

            items.forEach((i) => i.classList.remove('active'));
            item.classList.add('active');

            button.closest('.vnx-dropdown-wrapper').classList.remove('active');

            const desktopTab = this.$el.querySelector(`.vnx-service-nav-item[data-service="${serviceName}"]`);
            if (desktopTab) {
              this.switchTab(desktopTab);
            }
          });
        });
      },

      initDurationDropdowns() {
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          const durationDropdown = packageItem.querySelector('.vnx-duration-selector-mobile');
          if (!durationDropdown) return;

          const button = durationDropdown.querySelector('.vnx-dropdown-button');
          const menu = durationDropdown.querySelector('.vnx-dropdown-menu');
          const items = menu.querySelectorAll('.vnx-dropdown-item');
          const selectedText = button.querySelector('.vnx-dropdown-selected');
          const durationItems = packageItem.querySelectorAll('.vnx-duration-item');

          button.addEventListener('click', (e) => {
            e.stopPropagation();
            const wrapper = button.closest('.vnx-dropdown-wrapper');
            wrapper.classList.toggle('active');
          });

          items.forEach((item) => {
            item.addEventListener('click', () => {
              const cycleIndex = parseInt(item.getAttribute('data-cycle-index'));

              const titleElement = item.querySelector('.vnx-duration-title');
              const discountElement = item.querySelector('.vnx-duration-discount');

              const displayText = '<span class="vnx-duration-title">' + titleElement.textContent.trim() + '</span>' + (discountElement ? ' <span class="vnx-duration-discount">' + discountElement.textContent.trim() + '</span>' : '');

              selectedText.innerHTML = displayText;

              items.forEach((i) => i.classList.remove('active'));
              item.classList.add('active');

              button.closest('.vnx-dropdown-wrapper').classList.remove('active');

              if (durationItems[cycleIndex]) {
                this.switchDuration(durationItems[cycleIndex], packageItem);
              }
            });
          });
        });
      },

      switchTab(selectedTab) {
        const tabItems = this.$el.querySelectorAll('.vnx-service-nav-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');
        const serviceName = selectedTab.getAttribute('data-service');

        tabItems.forEach((tab) => {
          tab.classList.remove('active');
        });
        selectedTab.classList.add('active');

        packageItems.forEach((packageItem) => {
          const packageServiceName = packageItem.getAttribute('data-service');
          if (packageServiceName === serviceName) {
            packageItem.style.display = '';
          } else {
            packageItem.style.display = 'none';
          }
        });
      },

      switchDuration(selectedDurationItem, packageItem) {
        const durationItems = packageItem.querySelectorAll('.vnx-duration-item');
        const priceCards = packageItem.querySelectorAll('.vnx-price-card');
        const durationIndex = Array.from(durationItems).indexOf(selectedDurationItem);

        durationItems.forEach((item) => {
          item.classList.remove('active', 'popular');
        });
        selectedDurationItem.classList.add('active');

        priceCards.forEach((priceCard) => {
          const priceSections = priceCard.querySelectorAll('.vnx-price-section');
          const registerButtons = priceCard.querySelectorAll('.vnx-register-list .vnx-button-register');
          const planTitleDiscounts = priceCard.querySelectorAll('.vnx-plan-title-discount');
          const promotionBanners = priceCard.querySelectorAll('.vnx-promotion-banner');

          priceSections.forEach((section, index) => {
            if (index === durationIndex) {
              section.classList.remove('hidden');
            } else {
              section.classList.add('hidden');
            }
          });

          registerButtons.forEach((button, index) => {
            if (index === durationIndex) {
              button.classList.remove('hidden');
            } else {
              button.classList.add('hidden');
            }
          });

          planTitleDiscounts.forEach((discount, index) => {
            if (index === durationIndex) {
              discount.classList.remove('hidden');
            } else {
              discount.classList.add('hidden');
            }
          });

          promotionBanners.forEach((banner, index) => {
            if (index === durationIndex) {
              banner.classList.remove('hidden');
            } else {
              banner.classList.add('hidden');
            }
          });
        });
      },

      handleClickOutside() {
        document.addEventListener('click', (e) => {
          if (!e.target.closest('.vnx-dropdown-wrapper')) {
            this.$el.querySelectorAll('.vnx-dropdown-wrapper').forEach((wrapper) => {
              wrapper.classList.remove('active');
            });
          }
        });
      },

      initDragToScroll() {
        const priceCardsGrids = this.$el.querySelectorAll('.vnx-price-cards-grid');

        priceCardsGrids.forEach((grid) => {
          if (window.innerWidth <= 1024) {
            this.addDragToScroll(grid);
          }
        });

        let resizeTimeout;
        window.addEventListener('resize', () => {
          clearTimeout(resizeTimeout);
          resizeTimeout = setTimeout(() => {
            priceCardsGrids.forEach((grid) => {
              if (window.innerWidth <= 1024) {
                if (!grid.dataset.dragEnabled) {
                  this.addDragToScroll(grid);
                }
              } else {
                this.removeDragToScroll(grid);
              }
            });
          }, 150);
        });
      },

      addDragToScroll(grid) {
        if (grid.dataset.dragEnabled === 'true') {
          return;
        }

        grid.dataset.dragEnabled = 'true';
        let isDown = false;
        let startX;
        let scrollLeft;
        let isDragging = false;

        const handleMouseDown = (e) => {
          if (e.target.closest('a, button, input, select, textarea') || e.target.closest('.vnx-promotion-action-button')) {
            return;
          }
          isDown = true;
          grid.style.cursor = 'grabbing';
          grid.style.userSelect = 'none';
          startX = e.pageX - grid.offsetLeft;
          scrollLeft = grid.scrollLeft;
          isDragging = false;
        };

        const handleMouseLeave = () => {
          isDown = false;
          grid.style.cursor = 'grab';
          grid.style.userSelect = '';
        };

        const handleMouseUp = () => {
          isDown = false;
          grid.style.cursor = 'grab';
          grid.style.userSelect = '';

          if (!isDragging) {
            return;
          }

          isDragging = false;
        };

        const handleMouseMove = (e) => {
          if (!isDown) return;
          e.preventDefault();
          isDragging = true;
          const x = e.pageX - grid.offsetLeft;
          const walk = (x - startX) * 2;
          grid.scrollLeft = scrollLeft - walk;
        };

        const handleTouchStart = (e) => {
          if (e.target.closest('a, button, input, select, textarea') || e.target.closest('.vnx-promotion-action-button')) {
            return;
          }
          isDown = true;
          startX = e.touches[0].pageX - grid.offsetLeft;
          scrollLeft = grid.scrollLeft;
          isDragging = false;
        };

        const handleTouchEnd = () => {
          isDown = false;
          isDragging = false;
        };

        const handleTouchMove = (e) => {
          if (!isDown) return;
          isDragging = true;
          const x = e.touches[0].pageX - grid.offsetLeft;
          const walk = (x - startX) * 2;
          grid.scrollLeft = scrollLeft - walk;
        };

        grid.style.cursor = 'grab';
        grid.style.userSelect = 'none';

        grid.addEventListener('mousedown', handleMouseDown);
        grid.addEventListener('mouseleave', handleMouseLeave);
        grid.addEventListener('mouseup', handleMouseUp);
        grid.addEventListener('mousemove', handleMouseMove);
        grid.addEventListener('touchstart', handleTouchStart, { passive: false });
        grid.addEventListener('touchend', handleTouchEnd);
        grid.addEventListener('touchmove', handleTouchMove, { passive: false });

        grid._dragHandlers = {
          mousedown: handleMouseDown,
          mouseleave: handleMouseLeave,
          mouseup: handleMouseUp,
          mousemove: handleMouseMove,
          touchstart: handleTouchStart,
          touchend: handleTouchEnd,
          touchmove: handleTouchMove,
        };
      },

      removeDragToScroll(grid) {
        if (grid.dataset.dragEnabled !== 'true') {
          return;
        }

        grid.dataset.dragEnabled = 'false';
        grid.style.cursor = '';
        grid.style.userSelect = '';

        if (grid._dragHandlers) {
          grid.removeEventListener('mousedown', grid._dragHandlers.mousedown);
          grid.removeEventListener('mouseleave', grid._dragHandlers.mouseleave);
          grid.removeEventListener('mouseup', grid._dragHandlers.mouseup);
          grid.removeEventListener('mousemove', grid._dragHandlers.mousemove);
          grid.removeEventListener('touchstart', grid._dragHandlers.touchstart);
          grid.removeEventListener('touchend', grid._dragHandlers.touchend);
          grid.removeEventListener('touchmove', grid._dragHandlers.touchmove);
          delete grid._dragHandlers;
        }
      },
    },
  });
});

//version Tab Service Price All Group
document.querySelectorAll('.brxe-vnx-tab-service-price.tab-service-price-all-group').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      listService: [],
      containerId: containerId,
      priceSliders: {},
    },
    mounted() {
      this.listService = this.$el.querySelectorAll('.vnx-tab-package-item');
      this.initTabSwitching();
      this.initDurationSwitching();
      this.initMobileDropdowns();

      this.initPriceCardsSlider();

      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          if (window.innerWidth <= 991) {
            this.initPriceCardsSlider();
          } else {
            this.destroyPriceCardsSlider();
          }
        }, 150);
      });
    },
    methods: {
      getListService() {
        return this.listService;
      },
      //chuyển tab dịch vụ
      initTabSwitching() {
        const tabItems = this.$el.querySelectorAll('.vnx-service-nav-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        if (tabItems.length === 0 || packageItems.length === 0) return;

        const switchTab = (selectedTab) => {
          const serviceName = selectedTab.getAttribute('data-service');

          tabItems.forEach((tab) => {
            tab.classList.remove('active');
          });
          selectedTab.classList.add('active');

          packageItems.forEach((packageItem) => {
            const packageServiceName = packageItem.getAttribute('data-service');
            if (packageServiceName === serviceName) {
              packageItem.style.display = '';
            } else {
              packageItem.style.display = 'none';
            }
          });
        };

        tabItems.forEach((tab) => {
          tab.addEventListener('click', () => {
            switchTab(tab);
          });
        });

        const firstTab = tabItems[0];
        if (firstTab) {
          switchTab(firstTab);
        }
      },

      //chuyển tab gói dịch vụ
      initDurationSwitching() {
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          const durationItems = packageItem.querySelectorAll('.vnx-duration-item');
          const priceCards = packageItem.querySelectorAll('.vnx-price-card');

          if (durationItems.length === 0) return;

          const switchDuration = (selectedDurationItem) => {
            const durationIndex = Array.from(durationItems).indexOf(selectedDurationItem);

            durationItems.forEach((item) => {
              item.classList.remove('active', 'popular');
            });
            selectedDurationItem.classList.add('active');

            priceCards.forEach((priceCard) => {
              const priceSections = priceCard.querySelectorAll('.vnx-price-section');
              const registerButtons = priceCard.querySelectorAll('.vnx-register-list .vnx-button-register');
              const planTitleDiscounts = priceCard.querySelectorAll('.vnx-plan-title-discount');
              const promotionBanners = priceCard.querySelectorAll('.vnx-promotion-banner');

              priceSections.forEach((section, index) => {
                if (index === durationIndex) {
                  section.classList.remove('hidden');
                } else {
                  section.classList.add('hidden');
                }
              });

              registerButtons.forEach((button, index) => {
                if (index === durationIndex) {
                  button.classList.remove('hidden');
                } else {
                  button.classList.add('hidden');
                }
              });

              planTitleDiscounts.forEach((discount, index) => {
                if (index === durationIndex) {
                  discount.classList.remove('hidden');
                } else {
                  discount.classList.add('hidden');
                }
              });

              promotionBanners.forEach((banner, index) => {
                if (index === durationIndex) {
                  banner.classList.remove('hidden');
                } else {
                  banner.classList.add('hidden');
                }
              });
            });
          };

          durationItems.forEach((durationItem) => {
            durationItem.addEventListener('click', () => {
              switchDuration(durationItem);
            });
          });

          const activeDuration = packageItem.querySelector('.vnx-duration-item.active');
          if (activeDuration) {
            switchDuration(activeDuration);
          } else if (durationItems.length > 0) {
            switchDuration(durationItems[0]);
          }
        });
      },

      //copy mã khuyến mãi
      actionCopy(e) {
        const button = e.currentTarget;
        const promotionBanner = button.closest('.vnx-promotion-banner');
        if (!promotionBanner) return;

        const promotionDiscountValue = promotionBanner.querySelector('.vnx-promotion-discount-value');
        const iconCopy = button.querySelector('.vnx-icon-copy');
        const iconPaste = button.querySelector('.vnx-icon-paste');

        if (promotionDiscountValue) {
          const textToCopy = promotionDiscountValue.textContent.trim();
          navigator.clipboard.writeText(textToCopy).then(() => {
            if (iconCopy) iconCopy.classList.add('hidden');
            if (iconPaste) iconPaste.classList.remove('hidden');

            setTimeout(() => {
              if (iconCopy) iconCopy.classList.remove('hidden');
              if (iconPaste) iconPaste.classList.add('hidden');
            }, 2000);
          });
        }
      },

      attachCopyEventListeners(container) {
        const copyButtons = container.querySelectorAll('.vnx-promotion-action-button');
        copyButtons.forEach((button) => {
          button.addEventListener('click', this.actionCopy.bind(this));
        });
      },

      //mobile dropdowns
      initMobileDropdowns() {
        this.initServiceDropdown();
        this.initDurationDropdowns();
        this.handleClickOutside();
      },

      initServiceDropdown() {
        const serviceDropdown = this.$el.querySelector('.vnx-service-nav-mobile');
        if (!serviceDropdown) return;

        const button = serviceDropdown.querySelector('.vnx-dropdown-button');
        const menu = serviceDropdown.querySelector('.vnx-dropdown-menu');
        const items = menu.querySelectorAll('.vnx-dropdown-item');
        const selectedText = button.querySelector('.vnx-dropdown-selected');

        button.addEventListener('click', (e) => {
          e.stopPropagation();
          const wrapper = button.closest('.vnx-dropdown-wrapper');
          wrapper.classList.toggle('active');
        });

        items.forEach((item) => {
          item.addEventListener('click', () => {
            const serviceName = item.getAttribute('data-service');
            selectedText.textContent = item.textContent.trim();

            items.forEach((i) => i.classList.remove('active'));
            item.classList.add('active');

            button.closest('.vnx-dropdown-wrapper').classList.remove('active');

            const desktopTab = this.$el.querySelector(`.vnx-service-nav-item[data-service="${serviceName}"]`);
            if (desktopTab) {
              this.switchTab(desktopTab);
            }
          });
        });
      },

      initDurationDropdowns() {
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          const durationDropdown = packageItem.querySelector('.vnx-duration-selector-mobile');
          if (!durationDropdown) return;

          const button = durationDropdown.querySelector('.vnx-dropdown-button');
          const menu = durationDropdown.querySelector('.vnx-dropdown-menu');
          const items = menu.querySelectorAll('.vnx-dropdown-item');
          const selectedText = button.querySelector('.vnx-dropdown-selected');
          const durationItems = packageItem.querySelectorAll('.vnx-duration-item');

          button.addEventListener('click', (e) => {
            e.stopPropagation();
            const wrapper = button.closest('.vnx-dropdown-wrapper');
            wrapper.classList.toggle('active');
          });

          items.forEach((item) => {
            item.addEventListener('click', () => {
              const cycleIndex = parseInt(item.getAttribute('data-cycle-index'));

              const titleElement = item.querySelector('.vnx-duration-title');
              const discountElement = item.querySelector('.vnx-duration-discount');

              const displayText = '<span class="vnx-duration-title">' + titleElement.textContent.trim() + '</span>' + (discountElement ? ' <span class="vnx-duration-discount">' + discountElement.textContent.trim() + '</span>' : '');

              selectedText.innerHTML = displayText;

              items.forEach((i) => i.classList.remove('active'));
              item.classList.add('active');

              button.closest('.vnx-dropdown-wrapper').classList.remove('active');

              if (durationItems[cycleIndex]) {
                this.switchDuration(durationItems[cycleIndex], packageItem);
              }
            });
          });
        });
      },

      switchTab(selectedTab) {
        const tabItems = this.$el.querySelectorAll('.vnx-service-nav-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');
        const serviceName = selectedTab.getAttribute('data-service');

        tabItems.forEach((tab) => {
          tab.classList.remove('active');
        });
        selectedTab.classList.add('active');

        packageItems.forEach((packageItem) => {
          const packageServiceName = packageItem.getAttribute('data-service');
          if (packageServiceName === serviceName) {
            packageItem.style.display = '';
          } else {
            packageItem.style.display = 'none';
          }
        });
      },

      switchDuration(selectedDurationItem, packageItem) {
        const durationItems = packageItem.querySelectorAll('.vnx-duration-item');
        const priceCards = packageItem.querySelectorAll('.vnx-price-card');
        const durationIndex = Array.from(durationItems).indexOf(selectedDurationItem);

        durationItems.forEach((item) => {
          item.classList.remove('active', 'popular');
        });
        selectedDurationItem.classList.add('active');

        priceCards.forEach((priceCard) => {
          const priceSections = priceCard.querySelectorAll('.vnx-price-section');
          const registerButtons = priceCard.querySelectorAll('.vnx-register-list .vnx-button-register');
          const planTitleDiscounts = priceCard.querySelectorAll('.vnx-plan-title-discount');
          const promotionBanners = priceCard.querySelectorAll('.vnx-promotion-banner');

          priceSections.forEach((section, index) => {
            if (index === durationIndex) {
              section.classList.remove('hidden');
            } else {
              section.classList.add('hidden');
            }
          });

          registerButtons.forEach((button, index) => {
            if (index === durationIndex) {
              button.classList.remove('hidden');
            } else {
              button.classList.add('hidden');
            }
          });

          planTitleDiscounts.forEach((discount, index) => {
            if (index === durationIndex) {
              discount.classList.remove('hidden');
            } else {
              discount.classList.add('hidden');
            }
          });

          promotionBanners.forEach((banner, index) => {
            if (index === durationIndex) {
              banner.classList.remove('hidden');
            } else {
              banner.classList.add('hidden');
            }
          });
        });
      },

      handleClickOutside() {
        document.addEventListener('click', (e) => {
          if (!e.target.closest('.vnx-dropdown-wrapper')) {
            this.$el.querySelectorAll('.vnx-dropdown-wrapper').forEach((wrapper) => {
              wrapper.classList.remove('active');
            });
          }
        });
      },

      initPriceCardsSlider() {
        const isMobile = window.innerWidth <= 991;
        if (!isMobile) {
          this.destroyPriceCardsSlider();
          return;
        }

        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem, index) => {
          const gridWrapper = packageItem.querySelector('.vnx-price-cards-grid-wrapper');
          const grid = packageItem.querySelector('.vnx-price-cards-grid');

          if (!grid || !gridWrapper) return;

          const cards = Array.from(grid.querySelectorAll('.vnx-price-card'));
          if (cards.length === 0) return;

          const serviceId = packageItem.getAttribute('data-service') || `service-${index}`;
          const sliderId = `${this.containerId}-price-slider-${serviceId.replace(/\s+/g, '-').toLowerCase()}`;

          if (document.getElementById(sliderId)) return;

          const splideContainer = document.createElement('div');
          splideContainer.className = 'splide vnx-price-cards-slider';
          splideContainer.setAttribute('id', sliderId);
          const splideArrows = document.createElement('div');
          splideArrows.className = 'splide__arrows splide__arrows--ltr';
          splideArrows.innerHTML = `
          <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Previous slide"><i class="ion-ios-arrow-back"></i></button>
          <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide"><i class="ion-ios-arrow-forward "></i></button>
        `;

          const splideTrack = document.createElement('div');
          splideTrack.className = 'splide__track';

          const splideList = document.createElement('div');
          splideList.className = 'splide__list';

          cards.forEach((card) => {
            const slide = document.createElement('div');
            slide.className = 'splide__slide';
            slide.appendChild(card.cloneNode(true));
            splideList.appendChild(slide);
          });

          splideTrack.appendChild(splideList);
          splideContainer.appendChild(splideArrows);
          splideContainer.appendChild(splideTrack);

          grid.style.display = 'none';
          gridWrapper.appendChild(splideContainer);

          const splide = new Splide(`#${sliderId}`, {
            type: 'loop',
            perPage: 1,
            perMove: 1,
            gap: '16px',
            padding: '0',
            arrows: true,
            pagination: true,
            drag: true,
            autoplay: false,
            focus: 'center',
          });

          splide.mount();

          this.attachCopyEventListeners(splideContainer);

          if (!this.priceSliders) {
            this.priceSliders = {};
          }
          this.priceSliders[sliderId] = splide;
        });
      },

      destroyPriceCardsSlider() {
        if (!this.priceSliders) return;

        Object.keys(this.priceSliders).forEach((sliderId) => {
          const splide = this.priceSliders[sliderId];
          if (splide) {
            splide.destroy();
          }

          const sliderElement = document.getElementById(sliderId);
          if (sliderElement) {
            sliderElement.remove();
          }
        });

        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');
        packageItems.forEach((packageItem) => {
          const grid = packageItem.querySelector('.vnx-price-cards-grid');
          if (grid) {
            grid.style.display = '';
          }
        });

        this.priceSliders = {};
      },
    },
  });
});

// version Tab Service Price Object Storage
document.querySelectorAll('.brxe-vnx-tab-service-price.tab-service-price-object-storage').forEach(function (element, index) {
  const containerId = element.id;
  if (!element.id) {
    element.id = containerId;
  }

  new Vue({
    el: element,
    data: {
      listService: [],
      containerId: containerId,
      priceSliders: {},
    },
    mounted() {
      this.listService = this.$el.querySelectorAll('.vnx-tab-package-item');
      this.initTabSwitching();
      this.initDurationSwitching();
      this.initMobileDropdowns();

      this.initPriceCardsSlider();

      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          if (window.innerWidth <= 991) {
            this.initPriceCardsSlider();
          } else {
            this.destroyPriceCardsSlider();
          }
        }, 150);
      });
    },
    methods: {
      getListService() {
        return this.listService;
      },
      // chuyển tab dịch vụ
      initTabSwitching() {
        const tabItems = this.$el.querySelectorAll('.vnx-service-nav-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        if (tabItems.length === 0 || packageItems.length === 0) return;

        const switchTab = (selectedTab) => {
          const serviceName = selectedTab.getAttribute('data-service');

          tabItems.forEach((tab) => tab.classList.remove('active'));
          selectedTab.classList.add('active');

          packageItems.forEach((packageItem) => {
            const match = packageItem.getAttribute('data-service') === serviceName;
            packageItem.style.display = match ? '' : 'none';
          });
        };

        tabItems.forEach((tab) => {
          tab.addEventListener('click', () => switchTab(tab));
        });

        // Dùng tab active được set bởi PHP, fallback về tab đầu
        const activeTab = this.$el.querySelector('.vnx-service-nav-item.active') || tabItems[0];
        if (activeTab) switchTab(activeTab);
      },

      // chuyển chu kỳ thanh toán (dùng vnx-os-* selectors)
      initDurationSwitching() {
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          const durationItems = packageItem.querySelectorAll('.vnx-duration-item');
          const priceCards = packageItem.querySelectorAll('.vnx-price-card');

          if (durationItems.length === 0) return;

          const switchDuration = (selectedDurationItem) => {
            const durationIndex = Array.from(durationItems).indexOf(selectedDurationItem);

            durationItems.forEach((item) => item.classList.remove('active', 'popular'));
            selectedDurationItem.classList.add('active');

            priceCards.forEach((priceCard) => {
              // Price rows (vnx-os-price-row)
              priceCard.querySelectorAll('.vnx-os-price-row').forEach((row, i) => {
                row.classList.toggle('hidden', i !== durationIndex);
              });
              // Register buttons (vnx-os-btn-register)
              priceCard.querySelectorAll('.vnx-os-btn-register').forEach((btn, i) => {
                btn.classList.toggle('hidden', i !== durationIndex);
              });
            });
          };

          durationItems.forEach((durationItem) => {
            durationItem.addEventListener('click', () => switchDuration(durationItem));
          });

          // Dùng duration active được PHP set, fallback về item đầu
          const activeDuration = packageItem.querySelector('.vnx-duration-item.active') || durationItems[0];
          if (activeDuration) switchDuration(activeDuration);
        });
      },

      //copy mã khuyến mãi
      actionCopy(e) {
        const button = e.currentTarget;
        const promotionBanner = button.closest('.vnx-promotion-banner');
        if (!promotionBanner) return;

        const promotionDiscountValue = promotionBanner.querySelector('.vnx-promotion-discount-value');
        const iconCopy = button.querySelector('.vnx-icon-copy');
        const iconPaste = button.querySelector('.vnx-icon-paste');

        if (promotionDiscountValue) {
          const textToCopy = promotionDiscountValue.textContent.trim();
          navigator.clipboard.writeText(textToCopy).then(() => {
            if (iconCopy) iconCopy.classList.add('hidden');
            if (iconPaste) iconPaste.classList.remove('hidden');

            setTimeout(() => {
              if (iconCopy) iconCopy.classList.remove('hidden');
              if (iconPaste) iconPaste.classList.add('hidden');
            }, 2000);
          });
        }
      },

      attachCopyEventListeners(container) {
        const copyButtons = container.querySelectorAll('.vnx-promotion-action-button');
        copyButtons.forEach((button) => {
          button.addEventListener('click', this.actionCopy.bind(this));
        });
      },

      //mobile dropdowns
      initMobileDropdowns() {
        this.initServiceDropdown();
        this.initDurationDropdowns();
        this.handleClickOutside();
      },

      initServiceDropdown() {
        const serviceDropdown = this.$el.querySelector('.vnx-service-nav-mobile');
        if (!serviceDropdown) return;

        const button = serviceDropdown.querySelector('.vnx-dropdown-button');
        const menu = serviceDropdown.querySelector('.vnx-dropdown-menu');
        const items = menu.querySelectorAll('.vnx-dropdown-item');
        const selectedText = button.querySelector('.vnx-dropdown-selected');

        button.addEventListener('click', (e) => {
          e.stopPropagation();
          const wrapper = button.closest('.vnx-dropdown-wrapper');
          wrapper.classList.toggle('active');
        });

        items.forEach((item) => {
          item.addEventListener('click', () => {
            const serviceName = item.getAttribute('data-service');
            selectedText.textContent = item.textContent.trim();

            items.forEach((i) => i.classList.remove('active'));
            item.classList.add('active');

            button.closest('.vnx-dropdown-wrapper').classList.remove('active');

            const desktopTab = this.$el.querySelector(`.vnx-service-nav-item[data-service="${serviceName}"]`);
            if (desktopTab) {
              this.switchTab(desktopTab);
            }
          });
        });
      },

      initDurationDropdowns() {
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem) => {
          const durationDropdown = packageItem.querySelector('.vnx-duration-selector-mobile');
          if (!durationDropdown) return;

          const button = durationDropdown.querySelector('.vnx-dropdown-button');
          const menu = durationDropdown.querySelector('.vnx-dropdown-menu');
          const items = menu.querySelectorAll('.vnx-dropdown-item');
          const selectedText = button.querySelector('.vnx-dropdown-selected');
          const durationItems = packageItem.querySelectorAll('.vnx-duration-item');

          button.addEventListener('click', (e) => {
            e.stopPropagation();
            const wrapper = button.closest('.vnx-dropdown-wrapper');
            wrapper.classList.toggle('active');
          });

          items.forEach((item) => {
            item.addEventListener('click', () => {
              const cycleIndex = parseInt(item.getAttribute('data-cycle-index'));

              const titleElement = item.querySelector('.vnx-duration-title');
              const discountElement = item.querySelector('.vnx-duration-discount');

              const displayText = '<span class="vnx-duration-title">' + titleElement.textContent.trim() + '</span>' + (discountElement ? ' <span class="vnx-duration-discount">' + discountElement.textContent.trim() + '</span>' : '');

              selectedText.innerHTML = displayText;

              items.forEach((i) => i.classList.remove('active'));
              item.classList.add('active');

              button.closest('.vnx-dropdown-wrapper').classList.remove('active');

              if (durationItems[cycleIndex]) {
                this.switchDuration(durationItems[cycleIndex], packageItem);
              }
            });
          });
        });
      },

      switchTab(selectedTab) {
        const tabItems = this.$el.querySelectorAll('.vnx-service-nav-item');
        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');
        const serviceName = selectedTab.getAttribute('data-service');

        tabItems.forEach((tab) => {
          tab.classList.remove('active');
        });
        selectedTab.classList.add('active');

        packageItems.forEach((packageItem) => {
          const packageServiceName = packageItem.getAttribute('data-service');
          if (packageServiceName === serviceName) {
            packageItem.style.display = '';
          } else {
            packageItem.style.display = 'none';
          }
        });
      },

      // switchDuration dùng từ mobile dropdown (vnx-os-* selectors)
      switchDuration(selectedDurationItem, packageItem) {
        const durationItems = packageItem.querySelectorAll('.vnx-duration-item');
        const priceCards = packageItem.querySelectorAll('.vnx-price-card');
        const durationIndex = Array.from(durationItems).indexOf(selectedDurationItem);

        durationItems.forEach((item) => item.classList.remove('active', 'popular'));
        selectedDurationItem.classList.add('active');

        priceCards.forEach((priceCard) => {
          priceCard.querySelectorAll('.vnx-os-price-row').forEach((row, i) => {
            row.classList.toggle('hidden', i !== durationIndex);
          });
          priceCard.querySelectorAll('.vnx-os-btn-register').forEach((btn, i) => {
            btn.classList.toggle('hidden', i !== durationIndex);
          });
        });
      },

      handleClickOutside() {
        document.addEventListener('click', (e) => {
          if (!e.target.closest('.vnx-dropdown-wrapper')) {
            this.$el.querySelectorAll('.vnx-dropdown-wrapper').forEach((wrapper) => {
              wrapper.classList.remove('active');
            });
          }
        });
      },

      initPriceCardsSlider() {
        const isMobile = window.innerWidth <= 991;
        if (!isMobile) {
          this.destroyPriceCardsSlider();
          return;
        }

        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');

        packageItems.forEach((packageItem, index) => {
          const gridWrapper = packageItem.querySelector('.vnx-price-cards-grid-wrapper');
          const grid = packageItem.querySelector('.vnx-price-cards-grid');

          if (!grid || !gridWrapper) return;

          const cards = Array.from(grid.querySelectorAll('.vnx-price-card'));
          if (cards.length === 0) return;

          // Tìm index của gói phổ biến
          const activeIndex = cards.findIndex(card => card.classList.contains('is-popular'));

          const serviceId = packageItem.getAttribute('data-service') || `service-${index}`;
          const sliderId = `${this.containerId}-price-slider-${serviceId.replace(/\s+/g, '-').toLowerCase()}`;

          if (document.getElementById(sliderId)) return;

          const splideContainer = document.createElement('div');
          splideContainer.className = 'splide vnx-price-cards-slider';
          splideContainer.setAttribute('id', sliderId);
          const splideArrows = document.createElement('div');
          splideArrows.className = 'splide__arrows splide__arrows--ltr';
          splideArrows.innerHTML = `
          <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Previous slide"><i class="ion-ios-arrow-back"></i></button>
          <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide"><i class="ion-ios-arrow-forward "></i></button>
        `;

          const splideTrack = document.createElement('div');
          splideTrack.className = 'splide__track';

          const splideList = document.createElement('div');
          splideList.className = 'splide__list';

          cards.forEach((card) => {
            const slide = document.createElement('div');
            slide.className = 'splide__slide';
            slide.appendChild(card.cloneNode(true));
            splideList.appendChild(slide);
          });

          splideTrack.appendChild(splideList);
          splideContainer.appendChild(splideArrows);
          splideContainer.appendChild(splideTrack);

          grid.style.display = 'none';
          gridWrapper.appendChild(splideContainer);

          const splide = new Splide(`#${sliderId}`, {
            type: 'loop',
            perPage: 1,
            perMove: 1,
            start: activeIndex !== -1 ? activeIndex : 0,
            gap: '16px',
            padding: '0',
            arrows: true,
            pagination: false,
            drag: true,
            autoplay: false,
            focus: 'center',
          });

          splide.mount();

          this.attachCopyEventListeners(splideContainer);

          if (!this.priceSliders) {
            this.priceSliders = {};
          }
          this.priceSliders[sliderId] = splide;
        });
      },

      destroyPriceCardsSlider() {
        if (!this.priceSliders) return;

        Object.keys(this.priceSliders).forEach((sliderId) => {
          const splide = this.priceSliders[sliderId];
          if (splide) {
            splide.destroy();
          }

          const sliderElement = document.getElementById(sliderId);
          if (sliderElement) {
            sliderElement.remove();
          }
        });

        const packageItems = this.$el.querySelectorAll('.vnx-tab-package-item');
        packageItems.forEach((packageItem) => {
          const grid = packageItem.querySelector('.vnx-price-cards-grid');
          if (grid) {
            grid.style.display = '';
          }
        });

        this.priceSliders = {};
      },


      // Drag-to-scroll ngang tại breakpoint 981px – 1280px
    },
  });
});