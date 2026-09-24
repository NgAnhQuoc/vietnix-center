<?php
$settings = $data->settings;
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
$button_icon = isset($settings['button_icon']) ? $settings['button_icon'] : array();
$button_class = isset($settings['button_class']) ? ' ' . $settings['button_class'] : '';
$redirect_url = isset($settings['redirect_url']) ? $settings['redirect_url'] : '';
$button_icon_filter = isset($settings['button_icon_filter']) ? $settings['button_icon_filter'] : array();
$get_csv = $data->vnx_get_data_tld_search();
$get_data_sussgest = $data->get_data_tld_search();
$status_form = isset($settings['status_form']) ? $settings['status_form'] : 'redirect';
$id_box_result = isset($settings['id_box_result']) ? $settings['id_box_result'] : '';
if (isset($get_data_sussgest['status']) && $get_data_sussgest['status'] == 'success') {
  $csvdata = isset($get_data_sussgest['data']) ? $get_data_sussgest['data'] : [];
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
<div class="vnx-wrapper-form">
  <form method="GET" action="<?php echo esc_attr($redirect_url); ?>" class="relative vnx-content-form w-full h-full <?php echo esc_attr($status_form); ?>" data-result="<?php echo esc_attr($id_box_result); ?>">
    <div class="vnx_wrapper rounded overflow-hidden">
      <div class="vnx-input-form flex flex-row items-center">
        <?php
        if (!empty($button_icon)) {
          echo Bricks\Element::render_icon($button_icon, ['vnx_icon']);
        }
        ?>
        <input type="text" class="vnx_input border-none outline-none" name="domain" placeholder="<?php echo esc_attr($placeholder); ?>" v-model="domain">
        <div class="vnx-button-filter flex items-center justify-center relative cursor-pointer" @click="showPopupTld">
          <?php
          if (!empty($button_icon_filter)) {
            echo Bricks\Element::render_icon($button_icon_filter, ['vnx_icon_filter']);
          }
          ?>
          <span class="vnx-tag-filter absolute" v-show="ListDomainChecked.length > 0" style="display: none;">
            0
          </span>
        </div>
      </div>
      <div class="vnx-box-btn-search">
        <button type="submit" class="vnx-btn-search flex items-center justify-center relative <?php echo esc_attr($button_class); ?>" @click="clickSearchButtonRedirect($event)" :disabled="DomainLoading == true">
          <div class="vnx-btn-search-content items-center justify-center gap-2" v-show="DomainLoading == false" >
          <?php
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
    <div class="vnx_wrapper_list_tld" v-show="PopupTld == true" v-cloak style="display: none;">
      <div class="vnx_close_popup">
        <i class="vnx_icon_close fa fa-circle-xmark cursor-pointer text-white" @click="hidePopupTld"></i>
      </div>
      <div class="vnx_wrapper_list_tld_body">
        <div class="vnx_list_tld_search">
          <?php
          if(is_array($get_csv)){
          foreach ($get_csv as $key => $value) { ?>
          <div class="vnx_list_tld_search_body flex flex-col">
            <div class="vnx_list_tld_search_title">
              <span class="vnx_text_title">
                <?php echo $key; ?>
              </span>
            </div>
            <div class="vnx_list_tld_search_content">
              <?php
              if(is_array($value)){
                foreach ($value as $key => $item) {
                  if(!empty($item)){
                  $id_item = str_replace('.', '_', $item);
                  ?>
              <div class="vnx_list_tld_item flex items-center gap-2 px-3 py-1 cursor-pointer">
                <input type="checkbox" id="vnx_checkbox_tld<?php echo $id_item; ?>" value="<?php echo $item; ?>" class="vnx_checkbox_tld cursor-pointer" @click="clickCheckboxTld">
                <label for="vnx_checkbox_tld<?php echo $id_item; ?>">
                  <span class="vnx_text_tld cursor-pointer">
                    <?php echo $item; ?>
                  </span>
                </label>
              </div>
              <?php } } } ?>
            </div>
          </div>
          <?php } } ?>
        </div>
        <!-- phần action search  -->
        <div class="vnx_action_search">
          <div class="vnx_action_all flex items-center justify-center gap-2 px-3 py-1">
            <input type="checkbox" id="vnx_checkbox_all" value="check_all" class="vnx_checkbox_all cursor-pointer" @click="selectAllCheckbox">
            <label for="vnx_checkbox_all">
              <span class="vnx_text_all cursor-pointer">
                Chọn tất cả
              </span>
            </label>
          </div>
          <div class="vnx_action_button flex items-center justify-center gap-3">
            <button class="vnx_btn_clear_all px-6 py-2" @click="removeAllCheckbox"> <span class="vnx_btn_text">Xoá hết</span></button>
            <button class="vnx_btn_apply_tld px-6 py-2" @click="hidePopupTld"> <span class="vnx_btn_text">Áp Dụng</span></button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>