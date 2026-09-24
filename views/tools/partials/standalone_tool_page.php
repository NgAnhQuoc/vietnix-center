<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

include_once(VNX_PLUGIN_PATH_CENTER . 'register.php');

$tool_key = $data->key ?? '';
$tool_view = $data->view ?? '';

$tools_registry = (new RegisterVariables_Center())->ToolsRegister();
$tool = $tools_registry[$tool_key] ?? array('label' => '', 'icon' => '', 'desc' => '');

$tool_slug = str_replace('_', '-', preg_replace('/^vietnix-/', '', $tool_key));
$tools_page_url = admin_url('admin.php?page=' . plugin_basename(VNX_PLUGIN_PATH_CENTER . VNX_PLUGIN_SLUG_CENTER . '.php'));
?>
<div class="wrap">
  <div class="w-full mt-3 bg-white border border-gray-200 rounded shadow overflow-clip">
    <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-gray-200">
      <img class="w-auto h-8" src="<?= esc_url(VNX_PLUGIN_URL_CENTER . 'assets/logo.png') ?>" alt="Vietnix">
      <div class="flex items-center gap-2 ml-auto">
        <a class="inline-flex items-center gap-2 rounded border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 no-underline hover:border-[#38A7FF] hover:text-[#38A7FF]"
          href="<?= esc_url($tools_page_url . '#tool=' . $tool_slug) ?>">
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
              d="M12.5 15 7.5 10l5-5" />
          </svg>
          Tất cả Tool
        </a>
        <?php View::render('tools/partials/help_drawer', ['context' => 'tools']); ?>
      </div>
    </div>

    <div class="p-4 xl:p-6 vnx-tool-panels">
      <?php if ($tool['label'] !== ''): ?>
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
      <?php endif; ?>

      <?php View::render($tool_view); ?>
    </div>
  </div>
</div>