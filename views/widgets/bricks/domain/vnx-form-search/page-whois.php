<?php
$settings = $data->settings;
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$box_result = isset($settings['link_result_id']) ? $settings['link_result_id'] : '';
$button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
$button_icon = isset($settings['button_icon']) ? $settings['button_icon'] : array();
$clear_icon = isset($settings['clear_icon']) ? $settings['clear_icon'] : array();
$susggest_tld = isset($settings['susggest_tld']) ? $settings['susggest_tld'] : 'com';
$prioritize_tld = isset($settings['prioritize_tld']) ? $settings['prioritize_tld'] : '';
$prioritize_arr = explode(',', $prioritize_tld);
$get_csv = $data->get_data_tld_search();

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
  var tld_data = <?php echo json_encode($csvdata, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD', JSON.stringify(tld_data));
  var data_sussgest = <?php echo json_encode($data_sussgest, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD_Sussgest', JSON.stringify(data_sussgest));
</script>


<?php wp_nonce_field('domain_checking', 'vnx_domain_security'); ?>
<input type="hidden" name="template" id="vnx_template_domain" value="view-search-domain">
<input type="hidden" name="vnx_box_result" id="vnx_box_result" value="<?php echo $box_result; ?>">
<form class="relative rounded-lg" data-tld='<?php echo esc_attr($susggest_tld); ?>' data-priority='<?php echo esc_attr($prioritize_arr); ?>'>
  <div class="vnx_wrapper overflow-hidden relative rounded-lg">
    <input type="text" id="vnx_search_domain_input" :disabled="DisableButton" class="relative z-0 border outline-none px-5 py-3" name="domain" placeholder="<?php echo esc_attr($placeholder); ?>">
    <?php
    if (!empty($clear_icon))
      echo '<i class="vnx_icon clear_icon  ' . $clear_icon['icon'] . '" @click="clickButtonClear"></i>';
    ?>

    <button type="submit"  id="vnx_search_domain_btn" @click="clickSearchButton" class="absolute z-[1] rounded right-0 top-0 flex items-center justify-center" :disabled="DisableButton">
      <?php
      if (!empty($button_icon))
      echo '<span class="button_text">' . esc_html($button_text) . '</span>';
      ?>
    </button>
  </div>
</form>