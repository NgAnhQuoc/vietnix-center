<?php

class RegisterVariables_Center
{
  const TOOL_GROUPS = [
    'content' => 'Content',
    'seo' => 'SEO & Sitemap',
    'domain' => 'Domain & Pricing',
    'integration' => 'Integrations',
    'system' => 'System',
  ];

  const RENAMED_KEYS = [
    'vietnix-sync-telegram-sheet-form' => 'vietnix-sync-telegram-sheet', // đã có
    'vietnix-sync-telegram-sheet'      => 'vietnix-sync-discord-sheet',  // mới
    'vnx_telegram_post_message'        => 'vnx_discord_post_notify',    // mới
  ];

  // Tool chi co o vietnix-center, vietnix-plugin khong co checkbox tuong ung
  // (xem vnx_keep_center_only_tools_Center trong functions/requires/vnx_plugin_general.php).
  const CENTER_ONLY_TOOLS = [
    'vietnix-cache-scheduler',
  ];

  public static function migrate_legacy_keys(array $enabled)
  {
    foreach (self::RENAMED_KEYS as $old_key => $new_key) {
      if (isset($enabled[$old_key])) {
        if (!isset($enabled[$new_key])) {
          $enabled[$new_key] = $enabled[$old_key];
        }
        unset($enabled[$old_key]);
      }
    }

    return $enabled;
  }

  public static function get_enabled($option_name, $suffix)
  {
    return self::migrate_legacy_keys((array) get_option($option_name . $suffix, array()));
  }

  /**
   * Một tool chỉ "đếm được" khi nó có trang cấu hình thật (views/tools/{view}.php
   * tồn tại). Vài tool có thể đang thiếu file view do chưa làm xong - không nên
   * tính vào tử số/mẫu số, để khớp với số tool hiện ra ở sidebar (tool_page.php).
   */
  public static function has_config_view(array $addon)
  {
    return $addon['view'] !== '' && file_exists(VNX_PLUGIN_PATH_CENTER . 'views/tools/' . $addon['view'] . '.php');
  }

  /**
   * Tool quản lý ở nơi khác (CPT, trang admin riêng...) khai báo 'link' thay vì
   * 'view': không có panel để render, nhưng vẫn có trang thật để mở ra, nên vẫn
   * nên hiện trong sidebar/đếm số lượng - vd vietnix-banner trỏ sang edit.php?post_type=...
   */
  public static function has_tool_page(array $addon)
  {
    return self::has_config_view($addon) || !empty($addon['link']);
  }

  public static function countable_tools(array $items)
  {
    return array_filter($items, array(__CLASS__, 'has_tool_page'));
  }

  public static function group_by_key(array $items)
  {
    $grouped = array();
    foreach (self::TOOL_GROUPS as $group_key => $group_label) {
      $group_items = array_filter($items, function ($item) use ($group_key) {
        return $item['group'] === $group_key;
      });
      if (count($group_items) > 0) {
        $grouped[$group_key] = array('label' => $group_label, 'items' => $group_items);
      }
    }

    return $grouped;
  }

  function BricksRegisterWidget()
  {
    $widget_list_bricks = [
      'vnx-star-ratting' => ['label' => 'VNX Star Rating'],
      'vnx-post-view-counter' => ['label' => 'VNX Post View Counter'],
      'vnx-search-domain-form' => ['label' => 'VNX Search Domain Form'],
      'vnx-breadcrumbs' => ['label' => 'VNX Breadcrumbs'],
      'vnx-hook-filter' => [
        'label' => 'VNX Hook Filter',
        'desc' => 'Thêm control tuỳ chỉnh vào element Post Content của Bricks',
      ],
      'vnx-table' => ['label' => 'VNX Table'],
      'vnx-form-search-many-domain' => ['label' => 'VNX Form Search Many Domain'],
      'domain_search_many' => ['label' => 'VNX Many Domain Search'],
      'domain_result' => ['label' => 'VNX Domain Result'],
      'domain_cart' => ['label' => 'VNX Domain Cart'],
      'vnx-layer-toggle' => ['label' => 'VNX Layer Toggle'],
      'vnx-posts' => ['label' => 'VNX Posts'],
      'vnx-custom-posts-list' => ['label' => 'VNX Custom Posts List'],
      'vnx-posts-filter' => ['label' => 'VNX Posts Filter'],
      'vnx-wp-theme-post' => ['label' => 'VNX WP Theme Posts'],
      'vnx-search-to-popup' => ['label' => 'VNX Search To Popup'],
      'vnx-service-price' => ['label' => 'VNX Service Price'],
      'vnx-service-price-v2' => ['label' => 'VNX Service Price V2'],
      'vnx-coupon' => ['label' => 'VNX Coupon'],
      'vnx-domain-price-table' => ['label' => 'VNX Domain Price Table'],
      'vnx-search-domain-form-v2' => ['label' => 'VNX Search Domain Form V2'],
      'vnx-domain-cart-v2' => ['label' => 'VNX Domain Cart V2'],
      'vnx-domain-result-v2' => ['label' => 'VNX Domain Result V2'],
      'vnx-slide-domain-v1' => ['label' => 'VNX Slide Domain V1'],
      'vnx-posts-fillter-v2' => ['label' => 'VNX Posts Filter V2'],
      'vnx-custom-posts-list-v2' => ['label' => 'VNX Custom Posts List V2'],
      'bonus-service-time' => ['label' => 'VNX Bonus Service Time'],
      'vnx-search-ai' => ['label' => 'VNX Search Posts AI'],
      'element-before-after-image' => ['label' => 'VNX Before/After Image'],
      'vnx-dynamic-price-table' => ['label' => 'VNX Dynamic Price Table'],
      'vnx-dynamic-price-obj-storage' => ['label' => 'VNX Dynamic Price Object Storage'],
      'vnx-tab-service-price' => ['label' => 'VNX Tab Service Price'],
      'vnx-search-whois-domain' => ['label' => 'VNX Search Whois Domain'],
      'vnx-table-compare-service' => ['label' => 'VNX Table Compare Service'],
      'vnx-tabs-layout' => ['label' => 'VNX Tabs Layout'],
      'vnx-theme-posts' => ['label' => 'VNX Theme Posts'],
    ];

    return $this->normalize_addons($widget_list_bricks, false);
  }

  function GutenbergRegisterWidget()
  {
    $gutenberg_list = [
      'widget-button-blocks' => ['label' => 'VNX Button'],
      'widget-note-blocks' => ['label' => 'VNX Note'],
      'widget-note-icon-block' => ['label' => 'VNX Icon Note'],
      'widget-featured-blocks' => ['label' => 'VNX Featured Snippet'],
      'widget-shortcode-blocks' => ['label' => 'VNX Shortcode'],
      'widget-blockquote-block' => ['label' => 'VNX Blockquote'],
      'widget-compare-block' => ['label' => 'VNX Compare Table'],
      'widget-view-more-block' => ['label' => 'VNX View More List'],
      'widget-block-table-coupon' => ['label' => 'VNX Coupon Table'],
    ];

    return $this->normalize_addons($gutenberg_list, false);
  }

  /**
   * Extension = code chạy ngầm, bật/tắt ở trang Setting.
   * Key = tên file trong extentions/. 'view' để trống vì không có trang cấu hình riêng.
   */
  function ExtentionsRegister()
  {
    $extentions_list = [
      'api' => [
        'label' => 'REST API',
        'desc' => 'Bật các endpoint REST API của plugin',
      ],
      'api_send_message_bot' => [
        'label' => 'Discord Form Notify',
        'desc' => 'Gửi nội dung form submit về Discord webhook',
      ],
      'vnx_discord_post_notify' => [
        'label' => 'Discord Post Notify',
        'desc' => 'Thông báo về Discord khi có bài viết mới',
      ],
      'add_seo_and_writer_post' => [
        'label' => 'Post SEO & Writer',
        'desc' => 'Cần ACF field "seo_author" và "writer" (type user) cho post',
      ],
      'add_bricks_archive_author_condition' => [
        'label' => 'Bricks Author Condition',
        'desc' => 'Thêm điều kiện tác giả cho Bricks ở trang archive',
      ],
      'add_seo_and_writer_dev' => [
        'label' => 'Dev SEO & Writer',
        'desc' => 'Cần ACF field "seo_author_dev" và "writer_dev" (type user) cho mục Lập Trình',
      ],
      'vnx_custom_page_sitemap' => [
        'label' => 'Custom Page Sitemap',
        'desc' => 'Cần ACF field "vnx_check_product_page" để tuỳ biến sitemap của page',
      ],
      'vnx_api_order_product' => [
        'label' => 'Portal Order API',
        'desc' => 'Kết nối portal Vietnix để đặt sản phẩm tuỳ chỉnh',
      ],
      'vnx_new_sitemap' => [
        'label' => 'New Sitemap',
        'desc' => 'Sitemap tuỳ chỉnh dành riêng cho Vietnix',
      ],
      'vnx_hidden_login' => [
        'label' => 'Hidden Login',
        'desc' => 'Ẩn form đăng nhập mặc định của WordPress',
      ],
    ];

    return $this->normalize_addons($extentions_list, false);
  }

  function ToolsRegister()
  {
    $tools_list = [
      
      // Content
      'vietnix-import-docs' => [
        'label' => 'Import Docs',
        'icon' => 'fa-file-import',
        'desc' => 'Nhập nội dung từ Google Docs thành bài viết WordPress',
        'group' => 'content',
        'view' => '',
        'link' => 'admin.php?page=vnx_import_docs_center',
      ],
      'vietnix-filter-posts' => [
        'label' => 'Filter Posts',
        'icon' => 'fa-filter',
        'desc' => 'Lọc bài viết theo từ khoá, chuyên mục, trạng thái và xuất CSV',
        'group' => 'content',
      ],
      'vietnix-report-posts' => [
        'label' => 'Report Posts',
        'icon' => 'fa-chart-simple',
        'desc' => 'Gửi báo cáo bài viết định kỳ qua webhook Discord',
        'group' => 'content',
        'view' => '',
        'link' => 'admin.php?page=vnx_report_post_center',
      ],
      'vietnix-sync-post-authors-by-category' => [
        'label' => 'Sync Authors',
        'icon' => 'fa-users',
        'desc' => 'Gán hàng loạt tác giả cho bài viết theo chuyên mục',
        'group' => 'content',
      ],
      'vietnix-search-ai' => [
        'label' => 'AI Search',
        'icon' => 'fa-robot',
        'desc' => 'Tạo và cập nhật embeddings phục vụ tìm kiếm bài viết bằng AI',
        'group' => 'content',
      ],
      'vietnix-internal-link-ldp' => [
        'label' => 'Internal Links',
        'icon' => 'fa-link',
        'desc' => 'Quét bài viết có link đến LDP và xuất ra Google Sheet',
        'group' => 'content',
      ],

      // SEO & Sitemap
      'vietnix-sitemap-settings' => [
        'label' => 'Sitemap Settings',
        'icon' => 'fa-sitemap',
        'desc' => 'Chọn loại nội dung và thiết lập priority cho sitemap',
        'group' => 'seo',
      ],
      'vietnix-export-sitemap' => [
        'label' => 'Sitemap Export',
        'icon' => 'fa-file-export',
        'desc' => 'Xuất danh sách URL trong sitemap ra file',
        'group' => 'seo',
      ],

      // Domain & Pricing
      'vietnix-validate-domain' => [
        'label' => 'Domain Checker',
        'icon' => 'fa-globe',
        'desc' => 'Kiểm tra tình trạng và thông tin WHOIS của domain',
        'group' => 'domain',
      ],
      'vietnix-api-price-table' => [
        'label' => 'Price Sources',
        'icon' => 'fa-tags',
        'desc' => 'Danh sách URL CSV cấp dữ liệu cho API bảng giá và khuyến mãi',
        'group' => 'domain',
      ],

      // Integrations
      'vietnix-api' => [
        'label' => 'Portal API',
        'icon' => 'fa-plug',
        'desc' => 'Kết nối API portal Vietnix (token, endpoint)',
        'group' => 'integration',
      ],
      'vietnix-api-ldp' => [
        'label' => 'Landing Page API',
        'icon' => 'fa-plug',
        'desc' => 'Endpoint /get-ldp trả nội dung và bảng giá của landing page',
        'group' => 'integration',
      ],
      'vietnix-utm-tracker' => [
        'label' => 'UTM Tracker',
        'icon' => 'fa-bullseye',
        'desc' => 'Ghi nhận và lưu tham số UTM vào cookie',
        'group' => 'integration',
        'view' => '',
        'link' => 'admin.php?page=vietnix_utm_tracking_center',
      ],
      'vietnix-sync-discord-sheet' => [
        'label' => 'Sync Discord & Sheets',
        'icon' => 'fa-paper-plane',
        'desc' => 'Đồng bộ dữ liệu form sang Discord và Google Sheet',
        'group' => 'integration',
      ],
      'vietnix-logger-search' => [
        'label' => 'Vietnix Logger Search',
        'icon' => 'fa-paper-plane',
        'desc' => 'Ghi log từ khoá người dùng tìm kiếm trên site, đọc lại qua API /vnx_api/v1/search-keywords',
        'group' => 'integration',
      ],

      // System
      'vietnix-cache-scheduler' => [
        'label' => 'Cache Scheduler',
        'icon' => 'fa-clock',
        'desc' => 'Hẹn giờ tự động xoá cache LiteSpeed theo URL cụ thể hoặc toàn bộ site',
        'group' => 'system',
      ],
      'media_tool' => [
        'label' => 'Media Tool',
        'icon' => 'fa-image',
        'desc' => 'Đổi slug file upload thành chuỗi ngẫu nhiên 32 ký tự',
        'group' => 'system',
      ],
      'vietnix-banner' => [
        'label' => 'Banner',
        'icon' => 'fa-rectangle-ad',
        'desc' => 'Quản lý banner bằng custom post type',
        'group' => 'system',
        'view' => '',
        'link' => 'edit.php?post_type=vietnix_banner',
      ],
    ];

    return $this->normalize_addons($tools_list, true);
  }

  private function normalize_addons(array $items, $derive_view)
  {
    foreach ($items as $key => $item) {
      $item['label'] = isset($item['label']) ? $item['label'] : $key;
      $item['desc'] = isset($item['desc']) ? $item['desc'] : '';
      $item['group'] = isset($item['group']) ? $item['group'] : 'system';
      $item['icon'] = isset($item['icon']) ? $item['icon'] : 'fa-puzzle-piece';
      $item['link'] = isset($item['link']) ? $item['link'] : '';
      if (!array_key_exists('view', $item)) {
        $item['view'] = $derive_view ? str_replace('-', '_', $key) : '';
      }

      $items[$key] = $item;
    }

    return $items;
  }
}
