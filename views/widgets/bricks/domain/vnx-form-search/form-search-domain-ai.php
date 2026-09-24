<?php
$settings = $data->settings;
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
$button_icon = isset($settings['button_icon']) ? $settings['button_icon'] : array();
$button_class = isset($settings['button_class']) ? ' ' . $settings['button_class'] : '';
$redirect_url = isset($settings['redirect_url']) ? $settings['redirect_url'] : '';
$status_form = isset($settings['status_form']) ? $settings['status_form'] : 'redirect';
$id_box_result = isset($settings['id_box_result']) ? $settings['id_box_result'] : '';
$get_csv = $data->get_data_tld_search();
$list_datalist = !empty($_POST['get_domain_suggest_ai']) ? $_POST['get_domain_suggest_ai'] : '';
$domainquery = !empty($_POST['domainquery']) ? $_POST['domainquery'] : '';
if (isset($get_csv['status']) && $get_csv['status'] == 'success') {
  $csvdata = isset($get_csv['data']) ? $get_csv['data'] : [];
  $data_sussgest = [];
  foreach ($csvdata as $key => $value) {
    if (!empty($value[0])) {
      $data_sussgest[] = $value[0];
    }
  }
}
?>
<script>
  window.domainquery = <?php echo wp_json_encode($domainquery); ?>;
  var tld_data = <?php echo json_encode($csvdata, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD', JSON.stringify(tld_data));
  var data_sussgest = <?php echo json_encode($data_sussgest, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD_Sussgest', JSON.stringify(data_sussgest));
</script>
<div class="vnx-wrapper-form">
  <form method="POST" action="<?php echo esc_attr($redirect_url); ?>" class="relative vnx-content-form w-full h-full <?php echo esc_attr($status_form); ?>" data-result="<?php echo esc_attr($id_box_result); ?>">
    <div class="vnx_wrapper overflow-hidden">
      <div class="vnx-input-form w-full">
        <input type="hidden" id="vnx_domain_suggest_ai" name="get_domain_suggest_ai" value="<?php echo esc_attr($list_datalist); ?>">
        <textarea id="vnx_search_domain_input" cols="10" rows="3" class="vnx_input border-none outline-none" name="domainquery" placeholder="<?php echo esc_attr($placeholder); ?>" v-model="domainquery"> <?php echo esc_textarea($domainquery); ?></textarea>
      </div>
      <div class="vnx-box-btn-search">
        <button type="submit" class="vnx-btn-search flex items-center justify-center relative <?php echo esc_attr($button_class); ?>" @click="clickSearchButtonAi" :disabled="DomainLoading == true">
          <div class="vnx-btn-search-content items-center justify-center gap-2" v-show="DomainLoading == false">
            <?php
            if (!empty($button_icon)) {
              echo Bricks\Element::render_icon($button_icon, ['vnx_icon']);
            }
            echo '<span class="button_text">' . esc_html($button_text) . '</span>';
            ?>
          </div>
          <div class="loading_small" v-show="DomainLoading == true" style="display: none;">
            <div class="loading_wrapper">
              <div class="loading_icon"></div>
            </div>
          </div>
        </button>
      </div>
    </div>
  </form>
</div>