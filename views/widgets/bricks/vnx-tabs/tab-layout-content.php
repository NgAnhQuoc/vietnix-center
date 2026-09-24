<?php
$data = isset($data) ? $data : new stdClass();
$settings = $data->settings;
$data_file = $data->read_csv_file($settings['import-csv']);
$list_tabs = $data->transform_csv_to_tabs($data_file['data']);

$unique_id = 'vnx-tabs-' . uniqid();
$icon_tooltip = $settings['icon_tooltip'];
$icon_dropdown = $settings['icon_dropdown'];

if ($data_file['status'] !== 'success') {
  echo '<div class="vnx_error no_data"><b>' . $data_file['message'] . '</div>';
  return;
}
try{
?>
<div class="vnx-tabs-layout-wrapper" id="<?php echo $unique_id; ?>">
  <div class="vnx-mobile-dropdown">
    <div class="vnx-mobile-dropdown-button" id="<?php echo $unique_id; ?>-dropdown-btn">
      <span id="<?php echo $unique_id; ?>-dropdown-label"><?php echo esc_html($list_tabs[0]['tab_name']); ?></span>
    <?php if (!empty($icon_dropdown['icon'])): ?>
     <i class="<?php echo esc_attr($icon_dropdown['icon']); ?>"></i>
    <?php endif; ?>
    </div>
    <div class="vnx-mobile-dropdown-menu" id="<?php echo $unique_id; ?>-dropdown-menu">
      <?php foreach ($list_tabs as $index => $tab): ?>
        <div class="vnx-mobile-dropdown-item <?php echo $index === 0 ? 'active' : ''; ?>"
             data-tab="<?php echo $unique_id; ?>-tab-<?php echo $index; ?>">
          <?php echo esc_html($tab['tab_name']); ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="vnx-tabs-sidebar">
    <?php foreach ($list_tabs as $index => $tab): ?>
      <div class="vnx-tab-nav-item <?php echo $index === 0 ? 'active' : ''; ?>"
           data-tab="<?php echo $unique_id; ?>-tab-<?php echo $index; ?>">
        <?php echo esc_html($tab['tab_name']); ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="vnx-tabs-content-wrapper">
    <?php foreach ($list_tabs as $index => $tab): ?>
      <div class="vnx-tab-content-panel <?php echo $index === 0 ? 'active' : ''; ?>"
           id="<?php echo $unique_id; ?>-tab-<?php echo $index; ?>"
           style="background-image: url('<?php echo esc_url($tab['background_image']); ?>');">
        <?php if (!empty($tab['items'])): ?>
          <div class="vnx-tab-items-grid">
            <?php foreach ($tab['items'] as $item): ?>
              <div class="vnx-tab-item">
                <div class="vnx-tab-item-icon">
                  <img src="<?php echo esc_url($item['icon']); ?>"
                       alt="<?php echo esc_attr($item['title']); ?>">
                </div>
                <div class="vnx-tab-item-content">
                  <div class="vnx-tab-item-title">
                    <?php echo esc_html($item['title']); ?>
                    <?php if (!empty($item['description'])): ?>
                      <span class="vnx-tab-item-info">
                        <?php if (!empty($icon_tooltip['icon'])): ?>
                          <i class="<?php echo esc_attr($icon_tooltip['icon']); ?>"></i>
                        <?php endif; ?>
                        <?php if (!empty($item['description'])): ?>
                          <span class="vnx-tooltip">
                            <?php echo esc_html($item['description']); ?>
                          </span>
                        <?php endif; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php } catch (Exception $e) {
  echo '<div class="vnx_error no_data"><b>' . $e->getMessage() . '</div>';
}
?>