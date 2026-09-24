<?php

/**
 * Layout trang Vietnix › Tool: sidebar dọc gom theo nhóm + panel nội dung.
 * Dữ liệu ($tool_groups, $tools_total) được chuẩn bị trong tool_page.php.
 */

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

// Tool chỉ có 'link' (không có view) khong co panel de kich hoat, nen khong tinh
// vao danh sach slug dung lam tab active mac dinh.
$all_tool_slugs = array();
foreach ($tool_groups as $group) {
  foreach ($group['items'] as $tool) {
    if ($tool['view'] !== '') {
      $all_tool_slugs[] = $tool['slug'];
    }
  }
}
$requested_tool_slug = isset($_GET['tool']) ? sanitize_title(wp_unslash($_GET['tool'])) : '';
$active_tool_slug = in_array($requested_tool_slug, $all_tool_slugs, true)
  ? $requested_tool_slug
  : ($all_tool_slugs[0] ?? '');

// Breakpoint xl (1280px) chu khong phai lg: menu admin WordPress da chiem ~160px,
// duoi 1280px ma con chia doi sidebar + panel thi panel qua hep.

// Luon hien tieu de nhom de danh sach nhat quan voi cach gom nhom o trang
// Settings (general.php luon nhom tu toan bo registry nen luon > 1 nhom).
// Khong the dung count($tool_groups) > 1 lam dieu kien: $tool_groups o day
// chi gom tool DANG BAT, nen khi ca site chi bat 1 tool thi no con dung 1
// nhom, an nham tieu de dung mot tool dang co that trong "SEO & Sitemap".
$show_group_labels = true;
?>
<!-- overflow-clip (khong phai overflow-hidden): van cat gon noi dung theo bo goc
     nhung khong tao scroll container nen sidebar ben trong con dinh (sticky) duoc. -->
<div id="vnx-tools" class="w-full mt-5 mr-5 bg-white border border-gray-200 rounded shadow overflow-clip">

  <?php View::render('tools/partials/tool_page_header', ['count' => $tools_total]); ?>

  <div class="flex flex-col xl:flex-row">

    <aside class="w-full min-w-0 border-b border-gray-200 shrink-0 bg-gray-50 xl:w-72 xl:rounded-bl xl:border-b-0 xl:border-r">
      <!-- Man hinh thap: khoi nay tu dinh lai va tu cuon, khong keo dai qua khoi tam nhin. -->
      <div class="p-3 xl:sticky xl:top-8 xl:flex xl:max-h-[calc(100vh-4rem)] xl:flex-col">
        <div class="relative mb-3">
          <svg class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 pointer-events-none left-3 top-1/2"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-width="1.8" d="m17 17-3.6-3.6M15 9a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
          </svg>
          <input type="search" id="vnx-tools-search" autocomplete="off" placeholder="Tìm tool..."
            aria-label="Tìm tool"
            class="!m-0 !block !w-full !rounded !border !border-gray-200 !bg-white !py-2 !pl-9 !pr-3 !text-[13px] !text-gray-900 !shadow-sm focus:!border-[#38A7FF] focus:!ring-2 focus:!ring-[#38A7FF]/20">
        </div>

        <!-- Preline open() chỉ bỏ .active ở các phần tử cùng cha, nên nav phải phẳng:
           tiêu đề nhóm và nút tool đều là con trực tiếp của <nav>. -->
        <nav class="-mx-3 flex snap-x snap-mandatory gap-2 overflow-x-auto px-3 pb-1 xl:mx-0 xl:min-h-0 xl:flex-1 xl:flex-col xl:gap-0.5 xl:overflow-x-visible xl:overflow-y-auto xl:px-0 xl:pb-0 xl:pr-1"
          aria-label="Danh sách tool" role="tablist" data-hs-tabs-vertical="true">
          <?php foreach ($tool_groups as $group):
            $group_id = sanitize_title($group['label']); ?>
            <?php if ($show_group_labels): ?>
              <p data-tool-group="<?= esc_attr($group_id) ?>"
                class="m-0 mb-0.5 mt-4 px-3 text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400 first:mt-0 max-xl:hidden">
                <?= esc_html($group['label']) ?>
              </p>
            <?php endif; ?>
            <?php foreach ($group['items'] as $tool):
              $is_link_only = $tool['view'] === '' && $tool['link'] !== '';
              $is_active_tool = $tool['slug'] === $active_tool_slug;
              $nav_item_classes = 'group relative flex w-auto shrink-0 snap-start items-center gap-2.5 rounded border border-gray-200 bg-white px-3 py-2 xl:w-full xl:shrink xl:border-transparent xl:bg-transparent xl:py-1.5 text-left text-[13px] font-medium leading-5 text-gray-600 transition-all duration-150 hover:border-gray-200 hover:bg-white hover:text-gray-900 hover:shadow-sm hs-tab-active:border-[#38A7FF]/30 hs-tab-active:bg-white hs-tab-active:text-[#1170BE] hs-tab-active:shadow-sm' . ($is_active_tool ? ' active' : ''); ?>
              <?php if ($is_link_only): ?>
                <!-- Tool quan ly o trang khac (vd CPT rieng): mo thang trang do, khong
                     phai tab noi bo nen khong gan data-hs-tab/aria-controls panel. -->
                <a href="<?= esc_url(admin_url($tool['link'])) ?>" data-tool-slug="<?= esc_attr($tool['slug']) ?>"
                  data-tool-group-item="<?= esc_attr($group_id) ?>"
                  data-tool-keyword="<?= esc_attr(mb_strtolower($tool['label'] . ' ' . $tool['desc'] . ' ' . $tool['slug'])) ?>"
                  <?php if ($tool['desc'] !== ''): ?>title="<?= esc_attr($tool['desc']) ?>" <?php endif; ?>
                  class="<?= esc_attr($nav_item_classes) ?>">
                  <span
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-white text-[11px] leading-none text-gray-400 ring-1 ring-gray-200 transition-colors duration-150 group-hover:text-gray-600">
                    <i class="fa-solid <?= esc_attr($tool['icon']) ?>" aria-hidden="true"></i>
                  </span>
                  <span class="truncate"><?= esc_html($tool['label']) ?></span>
                  <i class="fa-solid fa-arrow-up-right-from-square ml-auto shrink-0 text-[10px] text-gray-300"
                    aria-hidden="true"></i>
                </a>
              <?php else: ?>
                <button type="button" data-tool-tab data-tool-slug="<?= esc_attr($tool['slug']) ?>"
                  data-tool-group-item="<?= esc_attr($group_id) ?>"
                  data-tool-keyword="<?= esc_attr(mb_strtolower($tool['label'] . ' ' . $tool['desc'] . ' ' . $tool['slug'])) ?>"
                  data-hs-tab="#vnx-tool-<?= esc_attr($tool['slug']) ?>"
                  <?php if ($tool['desc'] !== ''): ?>title="<?= esc_attr($tool['desc']) ?>" <?php endif; ?>
                  aria-controls="vnx-tool-<?= esc_attr($tool['slug']) ?>" role="tab"
                  aria-selected="<?= $is_active_tool ? 'true' : 'false' ?>"
                  id="vnx-tool-tab-<?= esc_attr($tool['slug']) ?>"
                  class="<?= esc_attr($nav_item_classes) ?>">
                  <!-- thanh accent trái: chỉ hiện ở tab đang mở -->
                  <span aria-hidden="true"
                    class="absolute inset-y-1.5 left-0 w-[3px] scale-y-0 rounded-r-full max-xl:hidden bg-[#38A7FF] opacity-0 transition-all duration-150 hs-tab-active:scale-y-100 hs-tab-active:opacity-100"></span>
                  <span
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-white text-[11px] leading-none text-gray-400 ring-1 ring-gray-200 transition-colors duration-150 group-hover:text-gray-600 hs-tab-active:bg-[#38A7FF] hs-tab-active:text-white hs-tab-active:ring-[#38A7FF]">
                    <i class="fa-solid <?= esc_attr($tool['icon']) ?>" aria-hidden="true"></i>
                  </span>
                  <span class="truncate"><?= esc_html($tool['label']) ?></span>
                </button>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </nav>

        <p id="vnx-tools-no-result" class="hidden px-3 py-3 text-[13px] text-gray-500">Không có tool nào khớp.</p>
      </div>
    </aside>

    <main class="flex-1 min-w-0 p-4 bg-white vnx-tool-panels xl:p-6">
      <?php foreach ($tool_groups as $group): ?>
        <?php foreach ($group['items'] as $tool):
          if ($tool['view'] === '') {
            // Tool chi co 'link': khong co panel noi bo, da mo thang trang ngoai o nav ben tren.
            continue;
          }
          $is_active_tool = $tool['slug'] === $active_tool_slug; ?>
          <div id="vnx-tool-<?= esc_attr($tool['slug']) ?>" role="tabpanel"
            aria-labelledby="vnx-tool-tab-<?= esc_attr($tool['slug']) ?>" <?= $is_active_tool ? '' : 'class="hidden"' ?>>
            <!-- Tieu de dung chung: lay tu registry nen moi tool co dung mot kieu dau trang,
                 view chi con phan than. -->
            <header class="vnx-tool-head">
              <span class="vnx-tool-head__icon">
                <i class="fa-solid <?= esc_attr($tool['icon']) ?>" aria-hidden="true"></i>
              </span>
              <div class="min-w-0">
                <h2 class="vnx-tool-head__title"><?= esc_html($tool['label']) ?></h2>
                <?php if ($tool['desc'] !== ''): ?>
                  <p class="vnx-tool-head__desc"><?= esc_html($tool['desc']) ?></p>
                <?php endif; ?>
              </div>
            </header>
            <?php View::render('tools/' . $tool['view']); ?>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </main>

  </div>
</div>