<?php
$settings = $data->settings;
$id_box_result = isset($settings['id_box_result']) ? $settings['id_box_result'] : '';
$button_icon = isset($settings['button_icon']) ? $settings['button_icon'] : '';
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$prioritize_tld = isset($settings['prioritize_tld']) ? $settings['prioritize_tld'] : '.com,.vn,.net';
$instructions_url = isset($settings['instructions_url']) ? $settings['instructions_url'] : '#';
$data_tld = $data->vnx_get_data_tld_search();
$status_form = isset($settings['status_form']) ? $settings['status_form'] : 'onpage';
$redirect_url = isset($settings['redirect_url']) ? $settings['redirect_url'] : '#';
$popular_tlds = [];
$all_tlds = [];

if ($data_tld && is_array($data_tld)) {
  if (isset($data_tld['Đuôi tên miền phổ biến']) && is_array($data_tld['Đuôi tên miền phổ biến'])) {
    $popular_tlds = array_filter($data_tld['Đuôi tên miền phổ biến']);
  }
  if (isset($data_tld['Tất cả đuôi tên miền']) && is_array($data_tld['Tất cả đuôi tên miền'])) {
    $all_tlds = array_filter($data_tld['Tất cả đuôi tên miền']);
  }
}
?>
<script>
  window.vnxPopularTlds = <?php echo json_encode($popular_tlds); ?>;
  window.vnxAllTlds = <?php echo json_encode($all_tlds); ?>;
  window.vnxSuggestTld = <?php echo json_encode($prioritize_tld); ?>;
</script>
<div class="form-search-multi-domain w-full relative flex items-center justify-center <?php echo $status_form; ?>" data-result="<?php echo $id_box_result; ?>">
    <div class="form-container" v-cloak :class="{'is-disabled': isLoading}">
      <input type="hidden" name="redirect_url" value="<?php echo esc_attr($redirect_url); ?>"> 
    <div class="form-container-top">
      <!-- Left Section - Domain Input -->
      <div class="domain-input-section">
        <div class="body">
          <!-- Domain Input Area -->
          <div class="input-area">
            <!-- Domain Tags -->
            <div class="domain-tags" v-if="enteredDomains.length > 0">
              <div v-for="(domain, index) in enteredDomains" :key="index" class="domain-tag">
                <span>{{ domain }}</span>
                <button @click="removeDomain(index)" class="remove-domain" type="button">
                  <i class="fa-regular fa-circle-minus text-white"></i>
                </button>
              </div>
              <button @click="clearAllDomains" class="clear-all-link" type="button">
                Xóa tất cả
              </button>
            </div>
            <textarea v-model="domainInput" @keydown.space.prevent="addDomain" @keydown.enter.prevent="addDomain" @paste="handlePaste" placeholder="<?php echo $placeholder; ?>" class="domain-textarea" :maxlength="300" id="vnx-input-domain" :disabled="enteredDomains.length >= 300"></textarea>

          </div>
          <div class="input-actions">
            <div class="left-actions">
              <button @click="uploadFile" class="upload-btn" type="button">
                <?php
                if (!empty($button_icon)) {
                  echo Bricks\Element::render_icon($button_icon, ['vnx_icon']);
                }
                ?>
                Tải tệp lên
              </button>
              <a href="#" class="instructions-link" @click="clickShowInstructionsPopup()" v-cloak>Hướng dẫn</a>
            </div>
            <div class="counter">
              {{ enteredDomains.length }}/300
            </div>
          </div>
        </div>
      </div>
      <!-- Right Section - TLD Selection -->
      <div class="tld-selection-section">
        <!-- Popular TLDs -->
        <div class="popular-tlds">
          <h3>Đuôi tên miền phổ biến</h3>
          <div class="tld-grid">
            <label v-for="tld in popularTlds" :key="tld.name" class="tld-checkbox">
              <input type="checkbox" v-model="selectedTlds" :value="tld.name" @change="updateSelectedCount">
              <span class="checkmark"></span>
              <span class="tld-name">{{ tld.name }}</span>
            </label>
          </div>
        </div>

        <!-- All TLDs -->
        <div class="all-tlds">
          <h3>Tất cả đuôi tên miền</h3>
          <div class="search-tld">
            <div class="search-input-wrapper">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M11.5 11.5L14 14M13 7.5C13 10.5376 10.5376 13 7.5 13C4.46243 13 2 10.5376 2 7.5C2 4.46243 4.46243 2 7.5 2C10.5376 2 13 4.46243 13 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
              <input v-model="tldSearchQuery" type="text" placeholder="Tìm kiếm đuôi tên miền cần đăng ký" class="tld-search-input">
            </div>
          </div>

          <div class="tld-list">
            <label v-for="tld in filteredAllTlds" :key="tld.name" class="tld-checkbox">
              <input type="checkbox" v-model="selectedTlds" :value="tld.name" @change="updateSelectedCount">
              <span class="checkmark"></span>
              <span class="tld-name">{{ tld.name }}</span>
            </label>
          </div>

          <!-- Selected TLDs Summary -->
          <div class="selected-tlds-summary" v-if="selectedTlds.length > 0">
            <div class="summary-text">
              Đã chọn <b>{{ selectedTlds.length }}</b> đuôi tên miền
            </div>
            <div class="selected-tld-tags">
              <div v-for="(tld, idx) in selectedTlds.slice(0, 5)" :key="tld" class="tld-tag">
                {{ tld }}
              </div>
              <span v-if="selectedTlds.length > 5" class="tld-tag">
                +{{ selectedTlds.length - 5 }}
              </span>
              <button @click="clearAllTlds" class="clear-all-tlds" type="button">
                Xóa tất cả
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="form-container-bottom">
      <!-- Action Buttons -->
      <div class="action-buttons">
        <button @click="searchDomains" class="search-btn">
          <span class="text-btn" v-cloak :style="{ opacity: isLoading ? 0 : 1 }">Tìm kiếm tên miền</span>
          <div class="loading_small" style="display: none;" v-cloak v-show="isLoading"><div class="loading_wrapper"><div class="loading_icon"></div></div></div>
        </button>
        <button @click="resetForm" class="reset-btn" v-if="enteredDomains.length > 0">
          Đặt lại
        </button>
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
<!-- Instructions Popup -->
<div class="instructions-popup-overlay" v-show="showInstructionsPopup == true" @click="clickCloseInstructionsPopup" v-cloak style="display: none;">
  <div class="instructions-popup" @click.stop>
    <button @click="clickCloseInstructionsPopup" class="popup-close-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path
          d="M12 2.25C10.0716 2.25 8.18657 2.82183 6.58319 3.89317C4.97982 4.96451 3.73013 6.48726 2.99218 8.26884C2.25422 10.0504 2.06114 12.0108 2.43735 13.9021C2.81355 15.7934 3.74215 17.5307 5.10571 18.8943C6.46928 20.2579 8.20656 21.1865 10.0979 21.5627C11.9892 21.9389 13.9496 21.7458 15.7312 21.0078C17.5127 20.2699 19.0355 19.0202 20.1068 17.4168C21.1782 15.8134 21.75 13.9284 21.75 12C21.7473 9.41498 20.7192 6.93661 18.8913 5.10872C17.0634 3.28084 14.585 2.25273 12 2.25ZM15.5306 14.4694C15.6003 14.5391 15.6556 14.6218 15.6933 14.7128C15.731 14.8039 15.7504 14.9015 15.7504 15C15.7504 15.0985 15.731 15.1961 15.6933 15.2872C15.6556 15.3782 15.6003 15.4609 15.5306 15.5306C15.4609 15.6003 15.3782 15.6556 15.2872 15.6933C15.1961 15.731 15.0986 15.7504 15 15.7504C14.9015 15.7504 14.8039 15.731 14.7128 15.6933C14.6218 15.6556 14.5391 15.6003 14.4694 15.5306L12 13.0603L9.53063 15.5306C9.46095 15.6003 9.37822 15.6556 9.28718 15.6933C9.19613 15.731 9.09855 15.7504 9 15.7504C8.90146 15.7504 8.80388 15.731 8.71283 15.6933C8.62179 15.6556 8.53906 15.6003 8.46938 15.5306C8.3997 15.4609 8.34442 15.3782 8.30671 15.2872C8.269 15.1961 8.24959 15.0985 8.24959 15C8.24959 14.9015 8.269 14.8039 8.30671 14.7128C8.34442 14.6218 8.3997 14.5391 8.46938 14.4694L10.9397 12L8.46938 9.53063C8.32865 9.38989 8.24959 9.19902 8.24959 9C8.24959 8.80098 8.32865 8.61011 8.46938 8.46937C8.61011 8.32864 8.80098 8.24958 9 8.24958C9.19903 8.24958 9.3899 8.32864 9.53063 8.46937L12 10.9397L14.4694 8.46937C14.5391 8.39969 14.6218 8.34442 14.7128 8.3067C14.8039 8.26899 14.9015 8.24958 15 8.24958C15.0986 8.24958 15.1961 8.26899 15.2872 8.3067C15.3782 8.34442 15.4609 8.39969 15.5306 8.46937C15.6003 8.53906 15.6556 8.62178 15.6933 8.71283C15.731 8.80387 15.7504 8.90145 15.7504 9C15.7504 9.09855 15.731 9.19613 15.6933 9.28717C15.6556 9.37822 15.6003 9.46094 15.5306 9.53063L13.0603 12L15.5306 14.4694Z"
          fill="#D9D9DB" />
      </svg>
    </button>
    <div class="popup-header">
      <p class="popup-title">Hướng dẫn tải file lên</p>
    </div>
    <div class="popup-content">
       <ul class="instructions-list">
        <li>Định dạng file: Hỗ trợ file .csv hoặc .xlsx.</li>
        <li>Quy tắc nhập liệu: Tên miền hợp lệ chỉ chứa chữ cái không dấu (a-z), số (0-9) và dấu gạch ngang (-).</li>
        <li>Tải file mẫu chuẩn <a href="<?php echo $instructions_url; ?>" class="sample-file-link">tại đây</a></li>
        <li><span class="highlight">Giới hạn: Hệ thống sẽ tự động xử lý 300 tên miền đầu tiên trong danh sách.</span></li>
      </ul>
    </div>
  </div>
</div>