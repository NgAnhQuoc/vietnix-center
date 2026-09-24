document.querySelectorAll('.brxe-vnx-tabs-layout.tab-layout-content').forEach(function (element) {
  const wrapper = element.querySelector('.vnx-tabs-layout-wrapper');
  if (!wrapper || !wrapper.id) {
    return;
  }

  new Vue({
    el: element,
    data: {
      uniqueId: wrapper.id,
    },
    mounted() {
      this.initTabSwitching();
      this.initMobileDropdown();
    },
    methods: {
      switchTab(targetTab, tabLabel) {
        const tabNavItems = this.$el.querySelectorAll('.vnx-tab-nav-item');
        const tabPanels = this.$el.querySelectorAll('.vnx-tab-content-panel');
        const mobileDropdownItems = this.$el.querySelectorAll('.vnx-mobile-dropdown-item');
        const mobileDropdownLabel = this.$el.querySelector(`#${this.uniqueId}-dropdown-label`);

        tabNavItems.forEach((item) => {
          item.classList.remove('active');
        });

        tabPanels.forEach((panel) => {
          panel.classList.remove('active');
        });

        mobileDropdownItems.forEach((item) => {
          item.classList.remove('active');
        });

        const activeNavItem = this.$el.querySelector(`.vnx-tab-nav-item[data-tab="${targetTab}"]`);
        if (activeNavItem) {
          activeNavItem.classList.add('active');
        }

        const activePanel = this.$el.querySelector(`#${targetTab}`);
        if (activePanel) {
          activePanel.classList.add('active');
        }

        const activeMobileItem = this.$el.querySelector(`.vnx-mobile-dropdown-item[data-tab="${targetTab}"]`);
        if (activeMobileItem) {
          activeMobileItem.classList.add('active');
          if (mobileDropdownLabel && tabLabel) {
            mobileDropdownLabel.textContent = tabLabel;
          }
        }
      },

      initTabSwitching() {
        const tabNavItems = this.$el.querySelectorAll('.vnx-tab-nav-item');

        tabNavItems.forEach((navItem) => {
          navItem.addEventListener('click', () => {
            const targetTab = navItem.getAttribute('data-tab');
            const tabLabel = navItem.textContent.trim();
            this.switchTab(targetTab, tabLabel);
          });
        });
      },

      initMobileDropdown() {
        const mobileDropdownBtn = this.$el.querySelector(`#${this.uniqueId}-dropdown-btn`);
        const mobileDropdownMenu = this.$el.querySelector(`#${this.uniqueId}-dropdown-menu`);

        if (!mobileDropdownBtn || !mobileDropdownMenu) return;

        mobileDropdownBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          mobileDropdownBtn.classList.toggle('open');
          mobileDropdownMenu.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
          if (!mobileDropdownBtn.contains(e.target) && !mobileDropdownMenu.contains(e.target)) {
            mobileDropdownBtn.classList.remove('open');
            mobileDropdownMenu.classList.remove('open');
          }
        });

        const mobileDropdownItems = this.$el.querySelectorAll('.vnx-mobile-dropdown-item');
        mobileDropdownItems.forEach((item) => {
          item.addEventListener('click', () => {
            const targetTab = item.getAttribute('data-tab');
            const tabLabel = item.textContent.trim();
            this.switchTab(targetTab, tabLabel);
            mobileDropdownBtn.classList.remove('open');
            mobileDropdownMenu.classList.remove('open');
          });
        });
      },
    },
  });
});