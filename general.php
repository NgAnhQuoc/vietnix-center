<?php

use HelperCenter\View;

vietnix_plugin_enqueue_admin_style_Center();
include_once('register.php');
$RegisterVariables_Center = new RegisterVariables_Center();
$widget_list_bricks = $RegisterVariables_Center->BricksRegisterWidget();
$widget_list_guntenberg = $RegisterVariables_Center->GutenbergRegisterWidget();
$list_tools = $RegisterVariables_Center->ToolsRegister();
$list_extentions = $RegisterVariables_Center->ExtentionsRegister();
$tool_groups = RegisterVariables_Center::group_by_key($list_tools);

$option_name = 'vnx_plugin_setting';
$tools_enabled_option = RegisterVariables_Center::get_enabled($option_name, '_tools');
$extentions_enabled_option = RegisterVariables_Center::get_enabled($option_name, '_extentions');
$widgets_enabled_option = (array) get_option($option_name . '_widgets', array());
$bricks_enabled_option = (array) ($widgets_enabled_option['bricks'] ?? array());
$gutenberg_enabled_option = (array) ($widgets_enabled_option['gutenberg'] ?? array());
$options_values = (array) get_option($option_name . '_options');

$bricks_enabled_count = count(array_intersect_key($bricks_enabled_option, $widget_list_bricks));
$gutenberg_enabled_count = count(array_intersect_key($gutenberg_enabled_option, $widget_list_guntenberg));
$widgets_enabled_count = $bricks_enabled_count + $gutenberg_enabled_count;
$widgets_total_count = count($widget_list_bricks) + count($widget_list_guntenberg);
$extentions_enabled_count = count(array_intersect_key($extentions_enabled_option, $list_extentions));
// Chỉ đếm tool có trang cấu hình thật, khớp với cách tool_page.php dựng sidebar
// (xem RegisterVariables_Center::has_config_view) - vietnix-banner và các tool chưa có
// view sẽ không tính vào tử số/mẫu số, dù toggle vẫn hiển thị bình thường bên dưới.
$countable_tools = RegisterVariables_Center::countable_tools($list_tools);
$tools_enabled_count = count(array_intersect_key($tools_enabled_option, $countable_tools));
$tools_total_count = count($countable_tools);

/**
 * Một dòng bật/tắt: dùng chung cho Widget (Bricks/Gutenberg), Extensions và Tool
 * để cả ba khối trong trang Settings nhìn như một hệ thống, không phải ba kiểu khác nhau.
 */
$vnx_render_toggle_row = function ($name, $index, $checked, $label, $desc) {
?>
  <label
    class="flex items-center justify-between gap-3 rounded border border-gray-100 px-3 py-2.5 cursor-pointer transition-colors duration-150 hover:border-gray-200 hover:bg-gray-50">
    <span class="min-w-0">
      <span class="block text-[13px] font-medium leading-5 text-gray-800"><?= esc_html($label) ?></span>
      <?php if ($desc !== ''): ?>
        <span class="block mt-0.5 text-xs leading-4 text-gray-500"><?= esc_html($desc) ?></span>
      <?php endif; ?>
    </span>
    <span class="relative inline-flex items-center shrink-0">
      <input type="checkbox" name="<?= esc_attr($name) ?>" class="sr-only peer" value="<?= esc_attr($index) ?>"
        <?php if ($checked)
          echo 'checked' ?>>
      <span
        class="h-5 w-9 rounded-full bg-gray-200 transition-colors duration-150 after:absolute after:left-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow after:transition-all after:duration-150 after:content-[''] peer-checked:bg-[#38A7FF] peer-checked:after:translate-x-4 peer-focus-visible:ring-2 peer-focus-visible:ring-[#38A7FF]/30"></span>
    </span>
  </label>
<?php
};

/**
 * Mở một <form> lưu riêng cho một khối trong trang Settings. $form_type là giá trị
 * đọc lại ở vnx_plugin_general_submit_Center() (functions/requires/vnx_plugin_general.php)
 * để biết chỉ cập nhật đúng phần này, không đụng tới các form khác trên cùng trang.
 * Gọi xong nhớ tự đóng </form> ở nơi gọi.
 */
$vnx_settings_form_open = function ($form_type, array $extra_fields = array()) {
?>
  <form action="<?php echo esc_attr('admin-post.php'); ?>" method="post">
    <?php wp_nonce_field('vnx_plugin_general_security', 'vnx-general-nonce'); ?>
    <input type="hidden" name="action" value="vnx_plugin_general_submit_Center" />
    <input type="hidden" name="vnx_settings_form" value="<?= esc_attr($form_type) ?>" />
    <?php foreach ($extra_fields as $field_name => $field_value): ?>
      <input type="hidden" name="<?= esc_attr($field_name) ?>" value="<?= esc_attr($field_value) ?>" />
    <?php endforeach; ?>
  <?php
};
  ?>
  <div id="vnx-settings" class="w-full mt-5 mr-5 bg-white border border-gray-200 rounded shadow overflow-clip">

    <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-gray-200">
      <img class="w-auto h-8" src="<?= esc_url(plugin_dir_url(__FILE__) . 'assets/logo.png') ?>" alt="Vietnix">
      <div class="ml-auto">
        <?php View::render('tools/partials/help_drawer', ['context' => 'settings']); ?>
      </div>
    </div>
    <?php // Danh diem cho WP biet dat admin notice ngay duoi header nay, thay vi tu
    // mo h1/h2 dau tien no vo tinh vo trong DOM. Xem wp-admin/js/common.js ~dong 1084. ?>
    <hr class="wp-header-end">

    <select id="tab-select"
      class="block w-full px-4 py-3 text-sm border-gray-200 rounded-md sm:hidden pr-9 focus:border-blue-500 focus:ring-blue-500"
      aria-label="Tabs" role="tablist">
      <option value="#hs-tab-to-select-1">Widget (<?= $widgets_enabled_count ?>/<?= $widgets_total_count ?>)</option>
      <option value="#hs-tab-to-select-2">Extensions (<?= $extentions_enabled_count ?>/<?= count($list_extentions) ?>)
      </option>
      <option value="#hs-tab-to-select-3">Tool (<?= $tools_enabled_count ?>/<?= $tools_total_count ?>)</option>
      <option value="#hs-tab-to-select-options">Options</option>
    </select>

    <div class="hidden px-5 pt-4 sm:block">
      <nav class="inline-flex flex-wrap items-center gap-1.5 p-1 border border-gray-200 rounded-lg bg-gray-50"
        aria-label="Tabs" role="tablist" hs-data-tab-select="#tab-select">
        <button type="button"
          class="hs-tab-active:bg-white hs-tab-active:text-[#1170BE] hs-tab-active:shadow-sm inline-flex items-center gap-2 rounded-md px-3.5 py-2 text-[13px] font-medium text-gray-600 transition-all duration-150 hover:bg-white/70 hover:text-gray-900 active"
          id="hs-tab-to-select-item-1" data-hs-tab="#hs-tab-to-select-1" aria-controls="hs-tab-to-select-1" role="tab">
          <svg class="w-4 h-4 text-gray-400 hs-tab-active:text-[#38A7FF]" aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
            <path
              d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z" />
          </svg>
          Widget
          <span
            class="vnx-badge vnx-badge--gray hs-tab-active:!bg-[#38A7FF] hs-tab-active:!text-white"><?= $widgets_enabled_count ?>/<?= $widgets_total_count ?></span>
        </button>
        <button type="button"
          class="hs-tab-active:bg-white hs-tab-active:text-[#1170BE] hs-tab-active:shadow-sm inline-flex items-center gap-2 rounded-md px-3.5 py-2 text-[13px] font-medium text-gray-600 transition-all duration-150 hover:bg-white/70 hover:text-gray-900"
          id="hs-tab-to-select-item-2" data-hs-tab="#hs-tab-to-select-2" aria-controls="hs-tab-to-select-2" role="tab">
          <svg class="w-4 h-4 text-gray-400 hs-tab-active:text-[#38A7FF]" aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M5 11.424V1a1 1 0 1 0-2 0v10.424a3.228 3.228 0 0 0 0 6.152V19a1 1 0 1 0 2 0v-1.424a3.228 3.228 0 0 0 0-6.152ZM19.25 14.5A3.243 3.243 0 0 0 17 11.424V1a1 1 0 0 0-2 0v10.424a3.227 3.227 0 0 0 0 6.152V19a1 1 0 1 0 2 0v-1.424a3.243 3.243 0 0 0 2.25-3.076Zm-6-9A3.243 3.243 0 0 0 11 2.424V1a1 1 0 0 0-2 0v1.424a3.228 3.228 0 0 0 0 6.152V19a1 1 0 1 0 2 0V8.576A3.243 3.243 0 0 0 13.25 5.5Z" />
          </svg>
          Extensions
          <span
            class="vnx-badge vnx-badge--gray hs-tab-active:!bg-[#38A7FF] hs-tab-active:!text-white"><?= $extentions_enabled_count ?>/<?= count($list_extentions) ?></span>
        </button>
        <button type="button"
          class="hs-tab-active:bg-white hs-tab-active:text-[#1170BE] hs-tab-active:shadow-sm inline-flex items-center gap-2 rounded-md px-3.5 py-2 text-[13px] font-medium text-gray-600 transition-all duration-150 hover:bg-white/70 hover:text-gray-900"
          id="hs-tab-to-select-item-3" data-hs-tab="#hs-tab-to-select-3" aria-controls="hs-tab-to-select-3" role="tab">
          <svg class="w-4 h-4 text-gray-400 hs-tab-active:text-[#38A7FF]" aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
            <path
              d="M19 0H1a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1ZM2 6v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6H2Zm11 3a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8a1 1 0 0 1 2 0h2a1 1 0 0 1 2 0v1Z">
            </path>
          </svg>
          Tool
          <span
            class="vnx-badge vnx-badge--gray hs-tab-active:!bg-[#38A7FF] hs-tab-active:!text-white"><?= $tools_enabled_count ?>/<?= $tools_total_count ?></span>
        </button>
        <button type="button"
          class="hs-tab-active:bg-white hs-tab-active:text-[#1170BE] hs-tab-active:shadow-sm inline-flex items-center gap-2 rounded-md px-3.5 py-2 text-[13px] font-medium text-gray-600 transition-all duration-150 hover:bg-white/70 hover:text-gray-900"
          id="hs-tab-to-select-item-options" data-hs-tab="#hs-tab-to-select-options"
          aria-controls="hs-tab-to-select-options" role="tab">
          <svg class="w-4 h-4 text-gray-400 hs-tab-active:text-[#38A7FF]" aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7.75 4H19M7.75 4a2.25 2.25 0 0 1-4.5 0m4.5 0a2.25 2.25 0 0 0-4.5 0M1 4h2.25m13.5 6H19m-2.25 0a2.25 2.25 0 0 1-4.5 0m4.5 0a2.25 2.25 0 0 0-4.5 0M1 10h11.25m-4.5 6H19M7.75 16a2.25 2.25 0 0 1-4.5 0m4.5 0a2.25 2.25 0 0 0-4.5 0M1 16h2.25" />
          </svg>
          Options
        </button>
      </nav>
    </div>

    <div class="p-5 vnx-tool-panels">
      <!-- Widget: tab cha nằm ngang -> subtab Bricks/Gutenberg đổi sang nằm dọc bên trái -->
      <div id="hs-tab-to-select-1" role="tabpanel" aria-labelledby="hs-tab-to-select-item-1">
        <div class="flex flex-col gap-5 lg:flex-row">
          <div class="vnx-subtabs vnx-subtabs--vertical">
            <button type="button" class="vnx-subtab active" role="tab" id="vnx-widget-subtab-bricks"
              data-hs-tab="#vnx-widget-panel-bricks" aria-controls="vnx-widget-panel-bricks">
              Bricks
              <span class="vnx-badge vnx-badge--blue"><?= $bricks_enabled_count ?>/<?= count($widget_list_bricks) ?></span>
            </button>
            <button type="button" class="vnx-subtab" role="tab" id="vnx-widget-subtab-gutenberg"
              data-hs-tab="#vnx-widget-panel-gutenberg" aria-controls="vnx-widget-panel-gutenberg">
              Gutenberg
              <span
                class="vnx-badge vnx-badge--blue"><?= $gutenberg_enabled_count ?>/<?= count($widget_list_guntenberg) ?></span>
            </button>
          </div>

          <div class="flex-1 min-w-0">
            <?php $vnx_settings_form_open('widgets'); ?>
            <!-- JS (assets/js/admin/general.js) cập nhật giá trị này ngay trước khi submit,
                 đọc từ subtab Bricks/Gutenberg đang active bên trái - để server biết lưu
                 xong thì đưa người dùng về lại đúng subtab đó. -->
            <input type="hidden" name="vnx_active_widget_group" class="vnx-active-widget-group" value="bricks">
            <!-- Bricks va Gutenberg phai la con truc tiep cua CHINH div nay (khong chung voi
               .vnx-subtabs o tren): HSTabs.open() an het cac phan tu anh em cua panel dang mo,
               gop chung cha se lam mat luon thanh subtab khi doi framework. -->
            <div>
              <div id="vnx-widget-panel-bricks" role="tabpanel" aria-labelledby="vnx-widget-subtab-bricks">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                  <?php foreach ($widget_list_bricks as $index => $widget) {
                    $vnx_render_toggle_row(
                      $option_name . '_widgets[bricks][' . $index . ']',
                      $index,
                      isset($bricks_enabled_option[$index]),
                      $widget['label'],
                      $widget['desc'] ?? ''
                    );
                  } ?>
                </div>
              </div>

              <div id="vnx-widget-panel-gutenberg" role="tabpanel" aria-labelledby="vnx-widget-subtab-gutenberg"
                class="hidden">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                  <?php foreach ($widget_list_guntenberg as $index => $widget) {
                    $vnx_render_toggle_row(
                      $option_name . '_widgets[gutenberg][' . $index . ']',
                      $index,
                      isset($gutenberg_enabled_option[$index]),
                      $widget['label'],
                      $widget['desc'] ?? ''
                    );
                  } ?>
                </div>
              </div>
            </div>
            <div class="flex items-center pt-4 mt-5 border-t border-gray-100">
              <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit" name="submit_vnx_plugin"
                value="save">Cập nhật</button>
            </div>
  </form>
  </div>
  </div>
  </div>
  <!-- /Widget -->

  <!-- Extensions -->
  <div id="hs-tab-to-select-2" class="hidden" role="tabpanel" aria-labelledby="hs-tab-to-select-item-2">
    <?php $vnx_settings_form_open('extensions'); ?>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <?php foreach ($list_extentions as $index => $extention) {
        $vnx_render_toggle_row(
          $option_name . '_extentions[' . $index . ']',
          $index,
          isset($extentions_enabled_option[$index]),
          $extention['label'],
          $extention['desc'] ?? ''
        );
      } ?>
    </div>
    <div class="flex items-center pt-4 mt-5 border-t border-gray-100">
      <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit" name="submit_vnx_plugin"
        value="save">Cập nhật</button>
    </div>
    </form>
  </div>
  <!-- /Extensions -->

  <!-- Tool: mỗi danh mục là một subtab dọc + một form lưu riêng, không lưu chung
         một lượt vì danh sách tool khá dài. -->
  <div id="hs-tab-to-select-3" class="hidden" role="tabpanel" aria-labelledby="hs-tab-to-select-item-3">
    <div class="flex flex-col gap-5 lg:flex-row">
      <div class="vnx-subtabs vnx-subtabs--vertical">
        <?php $tool_group_index = 0;
        foreach ($tool_groups as $group_key => $tool_group):
          $group_items = $tool_group['items'];
          $countable_group_items = array_intersect_key($countable_tools, $group_items);
          $group_enabled_count = count(array_intersect_key($tools_enabled_option, $countable_group_items)); ?>
          <button type="button" class="vnx-subtab <?= $tool_group_index === 0 ? 'active' : '' ?>" role="tab"
            id="vnx-tool-group-subtab-<?= esc_attr($group_key) ?>" data-hs-tab="#vnx-tool-group-panel-<?= esc_attr($group_key) ?>"
            aria-controls="vnx-tool-group-panel-<?= esc_attr($group_key) ?>">
            <?= esc_html($tool_group['label']) ?>
            <span class="vnx-badge vnx-badge--blue"><?= $group_enabled_count ?>/<?= count($countable_group_items) ?></span>
          </button>
        <?php $tool_group_index++;
        endforeach; ?>
      </div>

      <div class="flex-1 min-w-0">
        <?php $tool_group_index = 0;
        foreach ($tool_groups as $group_key => $tool_group):
          $group_items = $tool_group['items']; ?>
          <div id="vnx-tool-group-panel-<?= esc_attr($group_key) ?>" role="tabpanel"
            aria-labelledby="vnx-tool-group-subtab-<?= esc_attr($group_key) ?>"
            class="<?= $tool_group_index === 0 ? '' : 'hidden' ?>">
            <?php $vnx_settings_form_open('tools', array('vnx_tool_group' => $group_key)); ?>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
              <?php foreach ($group_items as $index => $tool) {
                $vnx_render_toggle_row(
                  $option_name . '_tools[' . $index . ']',
                  $index,
                  isset($tools_enabled_option[$index]),
                  $tool['label'],
                  $tool['desc'] ?? ''
                );
              } ?>
            </div>
            <div class="flex items-center pt-4 mt-5 border-t border-gray-100">
              <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit" name="submit_vnx_plugin"
                value="save">Cập nhật</button>
            </div>
            </form>
          </div>
        <?php $tool_group_index++;
        endforeach; ?>
      </div>
    </div>
  </div>
  <!-- /Tool -->

  <!-- Options -->
  <div id="hs-tab-to-select-options" class="hidden" role="tabpanel" aria-labelledby="hs-tab-to-select-item-options">
    <?php $vnx_settings_form_open('options'); ?>
    <div class="vnx-field vnx-field--md">
      <label for="cookie_domain" class="vnx-label">Cookie Domain</label>
      <input type="text" id="cookie_domain" name="<?= esc_attr($option_name . '_options') ?>[cookie_domain]"
        placeholder=".vietnix.vn" value="<?= esc_attr($options_values['cookie_domain'] ?? '') ?>" required>
    </div>

    <p class="mt-5 mb-2 text-[11px] font-semibold uppercase tracking-[0.04em] text-gray-400">Discord Webhook</p>
    <div class="vnx-fields">
      <?php foreach (array(
        'discord_webhook_trial' => array('Form đăng ký dùng thử', ''),
        'discord_webhook_callme' => array('Form liên hệ lại cho tôi', ''),
        'discord_webhook_post' => array('Thông báo bài viết mới', ''),
      ) as $webhook_key => $webhook_field): ?>
        <div class="vnx-field vnx-field--lg">
          <label for="<?= esc_attr($webhook_key) ?>" class="vnx-label"><?= esc_html($webhook_field[0]) ?></label>
          <input type="url" id="<?= esc_attr($webhook_key) ?>" name="<?= esc_attr($option_name . '_options') ?>[<?= esc_attr($webhook_key) ?>]"
            placeholder="https://discord.com/api/webhooks/..." value="<?= esc_attr($options_values[$webhook_key] ?? '') ?>">
          <?php if ($webhook_field[1]): ?>
            <p class="vnx-help"><?= esc_html($webhook_field[1]) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="flex items-center pt-4 mt-5 border-t border-gray-100">
      <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit" name="submit_vnx_plugin"
        value="save">Cập nhật</button>
    </div>
    </form>
  </div>
  <!-- /Options -->
  </div>
  </div>