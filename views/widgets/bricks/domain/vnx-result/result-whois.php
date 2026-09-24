<?php wp_nonce_field('domain_suggest_checking', 'vnx_domain_suggest_security');
$settings = $data->settings;
?>
<script>
  window.whoisUrl = <?php echo json_encode($settings["whois_url"]); ?>;
</script>


<!-- Loading  -->
<div class="vnc_loading_result w-full items-center justify-center gap-2" v-show="IsLoading">
  <div class="vnx-loading_animate flex items-center">
    <img src="<?php echo VNX_PLUGIN_URL_CENTER; ?>assets/images/icons/search-icon-brand.svg" alt="search icon"
      class="vnx_icon mb-5 md:mb-0 mr-0 md:mr-5">
    <div class="col_right">
      <p class="loading_text text-brand text-xl font-medium mb-6">Đang tra cứu tên miền, vui lòng chờ giây lát..</p>
      <div class="progress_outline relative w-full">
        <div class="progress-bar absolute top-0 left-0 h-full"></div>
      </div>
    </div>
  </div>
</div>

<!-- Không tìm thấy kết quả phù hợp -->
<div v-show="IsEmpty && IsLoading == false"
  class="w-full h-full bg-white border border-gray-200 rounded-lg shadow  items-center justify-center">
  <div class="flex flex-col items-center pb-10">
    <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg" alt="none domain" />
    <h5 class="mb-1 text-lg text-center font-medium text-[#000000]">Không tìm thấy kết quả phù hợp</h5>
    <span class="text-sm text-center font-normal text-[#828282]">Bạn vui lòng nhập tên miền để kiểm tra vào
      ô tìm
      kiếm</span>
  </div>
</div>


<!-- Thông tin whois  -->
<div class=" gap-8 w-full bg-white rounded-lg border block vnx_tablet:hidden" v-if="!IsEmpty && !IsLoading">
  <div class="flex items-center justify-start gap-5 border-b p-5">
    <img height="100px"
      src="https://vietnix.vn/wp-content/plugins/vietnix-plugin/assets/images/illustrations/congratulations.svg">
    <div>
      <!-- Tên domain  -->
      <div class="flex vnx-domain-sld">
        {{Domain.sld}}
        <span class="vnx-domain-tld">
          .{{Domain.tld}}
        </span>
      </div>

      <!-- Trạng thái  -->
      <div class="flex items-center gap-1 vnx-status-domain">
        <img class="status_icon"
          src="https://vietnix.vn/wp-content/plugins/vietnix-plugin/assets/images/icons/x-icon.svg" alt="status icon">
        <span> Tên miền đã được đăng ký</span>
      </div>
    </div>
  </div>

  <!-- Chi tiết Thông tin whois -->
  <div class="p-5 pb-10 vnx_tablet:pb-5">
    <div>
      <div class="vnx-item-whois w-full flex justify-between py-4 px-5">
        <div class="vnx-item-whois-title">
          <i class="fa-light fa-globe mr-1"></i>

          <span>Tên miền</span>
        </div>
        <div class="vnx-item-whois-value">{{Domain.sld}}.{{Domain.tld}}</div>
      </div>

      <div class="vnx-item-whois w-full flex justify-between py-4 px-5 vnx-hightlight">
        <div class="vnx-item-whois-title">
          <i class="fa-regular fa-calendar mr-1"></i>
          <span>Ngày đăng ký</span>
        </div>
        <div class="vnx-item-whois-value">{{formatDate(Whois.creationDate)}}</div>
      </div>


      <div class="vnx-item-whois w-full flex justify-between py-4 px-5">
        <div class="vnx-item-whois-title">
          <i class="fa-regular fa-calendar mr-1"></i>
          <span>Ngày hết hạn</span>
        </div>
        <div class="vnx-item-whois-value">{{formatDate(Whois.registrarExpirationDate)}}</div>
      </div>

      <div class="vnx-item-whois w-full flex justify-between py-4 px-5 vnx-hightlight">
        <div class="vnx-item-whois-title">
          <i class="fa-solid fa-user-secret mr-1"></i>
          <span>Chủ sở hữu</span>
        </div>
        <div class="vnx-item-whois-value">{{Whois.domainName}}</div>
      </div>

      <div class="vnx-item-whois w-full flex justify-between py-4 px-5 ">
        <div class="vnx-item-whois-title">
          <i class="fa-regular fa-flag mr-1"></i>
          <span>Cờ trạng thái</span>
        </div>
        <div class="vnx-item-whois-value">{{Whois.status}}</div>
      </div>

      <div class="vnx-item-whois w-full flex justify-between py-4 px-5 vnx-hightlight">
        <div class="vnx-item-whois-title">
          <i class="fa-light fa-buildings mr-1"></i>
          <span>Quản lý tại</span>
        </div>
        <div class="vnx-item-whois-value">{{Whois.domainName}}</div>
      </div>

      <div class="vnx-item-whois w-full flex justify-between py-4 px-5 ">
        <div class="vnx-item-whois-title">
          <i class="fa-regular fa-server mr-1"></i>
          <span>Nameserver</span>
        </div>
        <div class="vnx-item-whois-value">{{Whois.nameServers}}</div>
      </div>

      <div class="vnx-item-whois w-full flex justify-between py-4 px-5 vnx-hightlight">
        <div class="vnx-item-whois-title">
          <i class="fa-regular fa-router mr-1"></i>
          <span>DNSSEC</span>
        </div>
        <div class="vnx-item-whois-value">{{Whois.dnssec}}</div>
      </div>
    </div>
  </div>
</div>

<!-- Thông tin whois mobile -->
<div class="vnx-whois-mobile py-5 px-4 hidden vnx_tablet:block " v-if="!IsEmpty && !IsLoading">
  <div class="vnx-title-whois-mobile mb-8">
    Thông tin domain
  </div>

  <!-- Chi tiết whois -->
  <div class="vnx-whois-detail-mobile">
    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        Tên miền
      </span>
      <span class="vnx-item-value">
        {{Domain.sld}}{{Domain.tld}}
      </span>
    </div>


    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        Chủ sở hữu
      </span>
      <span class="vnx-item-value">
        {{Whois.domainName}}
      </span>
    </div>


    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        Ngày đăng ký
      </span>
      <span class="vnx-item-value">
        {{Whois.creationDate}}
      </span>
    </div>


    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        Ngày hết hạn
      </span>
      <span class="vnx-item-value">
        {{formatDate(Whois.registrarExpirationDate)}}
      </span>
    </div>


    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        Trạng thái
      </span>
      <span class="vnx-item-value">
        {{Whois.status}}
      </span>
    </div>


    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        Nameservers
      </span>
      <span class="vnx-item-value">
        {{Whois.nameServers}}
      </span>
    </div>


    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        DNSSEC
      </span>
      <span class="vnx-item-value">
        {{Whois.dnssec}}
      </span>
    </div>

    <div class="vnx-item-whois-mobile py-3 gap-2 border-b">
      <span class="vnx-item-label flex flex-col">
        Quản lý tại
      </span>
      <span class="vnx-item-value">
        {{Whois.domainName}}
      </span>
    </div>



  </div>
</div>