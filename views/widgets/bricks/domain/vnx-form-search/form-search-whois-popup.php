<?php
$settings = $data->settings;
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
$button_icon = isset($settings['button_icon']) ? $settings['button_icon'] : array();
$button_class = isset($settings['button_class']) ? ' ' . $settings['button_class'] : '';
$redirect_url = isset($settings['redirect_url']) ? $settings['redirect_url'] : '';
$whois_text_link = isset($settings['whois_text_link']) ? $settings['whois_text_link'] : '';
$dns_tooltip = isset($settings['dns_tooltip']) ? $settings['dns_tooltip'] : '';
$registry_lock_tooltip = isset($settings['registry_lock_tooltip']) ? $settings['registry_lock_tooltip'] : '';
$hidden_information_tooltip = isset($settings['hidden_information_tooltip']) ? $settings['hidden_information_tooltip'] : '';
?>
<div class="vnx-wrapper-form">
  <form method="GET" action="<?php echo esc_attr($redirect_url); ?>" class="relative vnx-content-form w-full h-full">
    <div class="vnx_wrapper flex flex-row flex-no-wrap overflow-hidden">
      <div class="vnx-input-form w-full">
        <input type="text" class="vnx_input border-none outline-none" name="domain" placeholder="<?php echo esc_attr($placeholder); ?>" v-model="domain">
      </div>
      <div class="vnx-box-btn-search w-fit">
        <button type="submit" class="vnx-btn-search w-max flex items-center justify-center <?php echo esc_attr($button_class); ?>" @click="clickSearchButtonWhoisPopup">
          <?php
          if (!empty($button_icon)) {
            echo Bricks\Element::render_icon($button_icon, ['vnx_icon']);
          }
          echo '<span class="button_text">' . esc_html($button_text) . '</span>';
          ?>
        </button>
      </div>
    </div>
    <div class="vnx_wrapper_popup" style="display: none;" v-show="PopupWhois">
      <div class="vnx_wrapper_popup_content">
        <div class="vnx_close_popup">
          <i class="vnx_icon_close fa fa-circle-xmark cursor-pointer text-white" @click="clickHidePopupWhois"></i>
        </div>
        <div class="vnx_wrapper_popup_body w-full rounded-lg bg-white relative">
          <div class="vnx_popup_success" v-show="StatusResult == true">
            <div class="vnx_popup_success_header">
              <p class="vnx_popup_success_header_title">Kiểm tra tên miền chính chủ</p>
              <div class="vnx_popup_status">
                <div class="vnx_whois_protected">
                  <p v-show="ProtectName == true">Tên miền này sử dụng tính năng <strong>bảo mật danh tính</strong> nên chúng tôi không tìm được thông tin.</p>
                  <p v-show="ProtectName == false">Chủ sỡ hữu tên miền {{domain}} là <strong>{{Whois.domainName}}</strong>. Nếu đây <strong>đúng là bạn</strong> thì tên miền này chính chủ.</p>
                </div>
              </div>
            </div>
            <div class="vnx_popup_success_body">
              <div class="vnx_popup_success_body_item">
                <div class="vnx_popup_success_body_item_title">
                  <p class="title">Thông tin tên miền <span>{{domain}}</span>:</p>
                </div>
                <div class="vnx_popup_success_body_item_content flex flex-col flex-no-wrap gap-5">
                  <div class="vnx_popup_item_row" v-if="hasAnyValue(Whois.creationDate, Whois.registrarExpirationDate)">
                    <p class="vnx_item_title">Thời gian</p>
                    <div class="vnx_item_row_content flex flex-row flex-no-wrap gap-3" v-if="hasValue(Whois.creationDate)">
                      <p class="vnx_item_row_content_title">Ngày tạo</p>
                      <p class="vnx_item_row_content_value">{{formatDate(Whois.creationDate)}}</p>
                    </div>
                    <div class="vnx_item_row_content flex flex-row flex-no-wrap gap-3" v-if="hasValue(Whois.registrarExpirationDate)">
                      <p class="vnx_item_row_content_title">Ngày hết hạn</p>
                      <p class="vnx_item_row_content_value">{{formatDate(Whois.registrarExpirationDate)}}</p>
                    </div>
                  </div>
                  <div class="vnx_popup_item_row">
                    <p class="vnx_item_title">Thông tin nhà cung cấp tên miền</p>
                    <div class="vnx_item_row_content flex flex-row flex-no-wrap gap-3" v-if="hasAnyValue(Whois.registrars, Whois.domainName)">
                      <p class="vnx_item_row_content_title">Tên tổ chức</p>
                      <p class="vnx_item_row_content_value">{{Whois.registrars || Whois.domainName}}</p>
                    </div>
                    <div class="vnx_item_row_content flex flex-row flex-no-wrap gap-3" v-if="hasValue(Whois.status)">
                      <p class="vnx_item_row_content_title">Trạng thái tên miền</p>
                      <p class="vnx_item_row_content_value flex flex-col">
                        <span v-for="(item, idx) in Whois.status" :key="idx">{{ item }}</span>
                      </p>
                    </div>
                    <div class="vnx_item_row_content flex flex-row flex-no-wrap gap-3" v-if="hasValue(Whois.dnssec)">
                      <div class="flex flex-row flex-no-wrap gap-1 items-start tooltip_parent">
                        <div class="vnx_item_row_content_title flex flex-row flex-no-wrap gap-1 items-center">
                          <p>DNSSEC</p>
                          <?php if (!empty($dns_tooltip)) { ?>
                            <div class="vnx_item_row_icon flex items-center justify-center w-6 h-6 relative">
                              <i class="vnx_icon fa-regular fa-circle-question"></i>
                              <p class="vnx_item_row_icon_tooltip" style="display: none;">Bạn có thể tìm hiểu thêm thông tin <a href="<?php echo esc_attr($dns_tooltip); ?>" rel="nofollow" target="_blank">tại đây.</a></p>
                            </div>
                          <?php } ?>
                        </div>
                      </div>
                      <p class="vnx_item_row_content_value red">{{Whois.dnssec}}</p>
                    </div>
                    <div class="vnx_item_row_content flex flex-row flex-no-wrap gap-3">
                      <div class="flex flex-row flex-no-wrap gap-1 items-start tooltip_parent">
                        <div class="vnx_item_row_content_title flex flex-row flex-no-wrap gap-1 items-center">
                          <p>Registry Lock</p>
                          <?php if (!empty($registry_lock_tooltip)) { ?>
                            <div class="vnx_item_row_icon flex items-center justify-center w-6 h-6 relative">
                              <i class="vnx_icon fa-regular fa-circle-question"></i>
                              <p class="vnx_item_row_icon_tooltip" style="display: none;">Bạn có thể tìm hiểu thêm thông tin <a href="<?php echo esc_attr($registry_lock_tooltip); ?>" rel="nofollow" target="_blank">tại đây.</a></p>
                            </div>
                          <?php } ?>
                        </div>
                      </div>
                      <p class="vnx_item_row_content_value red" v-show="Whois.registryLock == false">Tên miền chưa được bảo vệ tuyệt đối</p>
                      <p class="vnx_item_row_content_value" v-show="Whois.registryLock == true">Tên miền được bảo vệ tuyệt đối</p>
                    </div>
                  </div>
                  <div class="vnx_popup_item_row" v-if="hasValue(Whois.nameServers)">
                    <p class="vnx_item_title">Name Server</p>
                    <div class="vnx_item_row_content flex flex-row flex-no-wrap gap-3">
                      <p class="vnx_item_row_content_title flex flex-col max-w-[200px]">
                        <span v-for="(item, idx) in Whois.nameServers" :key="idx">{{ item }}</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="vnx_popup_fail" v-show="StatusResult == false && WhoisError == false">
            <div class="vnx_popup_fail_header">
              <p class="vnx_popup_fail_header_title">Rất tiếc! Tên miền này chưa được đăng ký</p>
            </div>
            <div class="vnx_popup_fail_body">
              <p class="vnx_popup_fail_body_title">Đăng ký ngay tên miền để bảo vệ thương hiệu của bạn</p>
              <div class="vnx_popup_fail_body_button flex flex-row flex-no-wrap items-center justify-between">
                <p class="vnx_popup_fail_body_button_title">{{domain}}</p>
                <button type="submit" class="vnx_popup_fail_body_button_link">Đăng ký ngay</button>
              </div>
            </div>
          </div>
          <div class="vnx_popup_fail" v-show="StatusResult == false && WhoisError == true">
            <div class="vnx_popup_fail_header">
              <p class="vnx_popup_fail_header_title">Rất tiếc không thể lấy thông tin từ tên miền này. Hãy thử lại với tên miền khác.</p>
            </div>
          </div>
          <!-- loading result -->
          <div class="vnx_loading_result_wrapper " v-show="DomainLoading == true">
            <div class="vnc_loading_result w-full items-center justify-center gap-2 ">
              <div class="vnx-loading_animate flex flex-col flex-no-wrap items-center">
                <img src="<?php echo VNX_PLUGIN_URL_CENTER; ?>assets/images/icons/icon-loading-search-whois.png" alt="search icon" class="vnx_icon mb-5 md:mb-0 mr-0 md:mr-5">
                <div class="col_right">
                  <p class="loading_text">Đang tra cứu tên miền, vui lòng chờ giây lát..</p>
                  <div class="progress_outline relative w-full">
                    <div class="progress-bar absolute top-0 left-0 h-full"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
