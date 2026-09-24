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
$prioritize_tld = isset($settings['prioritize_tld']) ? $settings['prioritize_tld'] : '';
$popular_tlds = [];
$all_tlds = [];

if (isset($get_data_sussgest['status']) && $get_data_sussgest['status'] == 'success') {
  $csvdata = isset($get_data_sussgest['data']) ? $get_data_sussgest['data'] : [];
  $data_sussgest = [];
  foreach ($csvdata as $key => $value) {
    if (!empty($value[0])) {
      $data_sussgest[] = $value[0];
    }
  }
}

if ($get_csv && is_array($get_csv)) {
  $vn_tlds = [];
  $other_tlds = [];
  if (isset($get_csv['Phần mở rộng (TLD) Việt Nam']) && is_array($get_csv['Phần mở rộng (TLD) Việt Nam'])) {
    $vn_tlds = array_filter($get_csv['Phần mở rộng (TLD) Việt Nam']);
  }
  if (isset($get_csv['Phần mở rộng (TLD) khác']) && is_array($get_csv['Phần mở rộng (TLD) khác'])) {
    $other_tlds = array_filter($get_csv['Phần mở rộng (TLD) khác']);
  }
  $all_tlds = array_merge($vn_tlds, $other_tlds);
}
?>
<script>
  var tld_data = <?php echo json_encode($csvdata, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD', JSON.stringify(tld_data));
  var data_sussgest = <?php echo json_encode($data_sussgest, JSON_PRETTY_PRINT) ?>;
  sessionStorage.setItem('Data_TLD_Sussgest', JSON.stringify(data_sussgest));
  window.vnxPopularTlds = <?php echo json_encode($vn_tlds); ?>;
  window.vnxOtherTlds = <?php echo json_encode($other_tlds); ?>;
  window.vnxAllTlds = <?php echo json_encode($all_tlds); ?>;
  window.vnxSuggestTld = <?php echo json_encode($prioritize_tld); ?>;
</script>
<div class="vnx-wrapper-form">
  <div class="relative vnx-content-form w-full h-full <?php echo esc_attr($status_form); ?>" data-result="<?php echo esc_attr($id_box_result); ?>">
    <input type="hidden" name="redirect_url" value="<?php echo esc_attr($redirect_url); ?>"> 
    <div class="vnx_wrapper overflow-hidden">
      <div class="vnx-input-form">
        <?php
        if (!empty($button_icon)) {
          echo Bricks\Element::render_icon($button_icon, ['vnx_icon']);
        }
        ?>
        <div class="vnx-box-domain-tags">
          <div class="domain-tags" v-if="enteredDomains.length > 0">
            <div v-for="(domain, index) in enteredDomains" :key="index" class="domain-tag">
              <span>{{ domain }}</span>
              <button @click="removeDomain(index)" class="remove-domain" type="button">
                <i class="fa-regular fa-circle-minus "></i>
              </button>
            </div>
          </div>
          <input v-model="domain" @keydown.space.prevent="addDomain" @keydown.enter.prevent="addDomain" placeholder="<?php echo $placeholder; ?>" class="vnx_input border-none outline-none" :maxlength="20" id="vnx-input-domain"
            :disabled="enteredDomains.length >= 20"></input>
        </div>
        <div class="vnx-button-filter flex items-center justify-center relative cursor-pointer" :class="{ 'active': PopupTld == true }" @click="showPopupTld">
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
        <button type="submit" class="vnx-btn-search flex items-center justify-center relative <?php echo esc_attr($button_class); ?>" @click="searchDomains" :disabled="DomainLoading == true">
          <div class="vnx-btn-search-content items-center justify-center gap-2" v-show="DomainLoading == false">
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
          if (is_array($get_csv)) {
            foreach ($get_csv as $key => $value) { ?>
              <div class="vnx_list_tld_search_body flex flex-col">
                <div class="vnx_list_tld_search_title">
                  <span class="vnx_text_title">
                    <?php echo $key; ?>
                  </span>
                </div>
                <div class="vnx_list_tld_search_content">
                  <?php
                  if (is_array($value)) {
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
                    <?php }
                    }
                  } ?>
                </div>
              </div>
            <?php }
          } ?>
        </div>
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
    <!-- html show message -->
  <div class="vnx-message-display absolute" v-show="showMessStatus" v-cloak>
    <div class="vnx-inline-message transition-all duration-300 ease-in-out">
      <div class="flex items-center justify-between content" role="alert">
        <div class="flex items-center">
          <div class="content-message">{{ messStatus }}</div>
        </div>
      </div>
    </div>
  </div>
  </div>
</div>