<?php
if ( !isset( $data->settings ) ) {
  if ( current_user_can( 'update_core' ) )
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
  return;
}
$settings = $data->settings;
$my_class = [ 'vnx_element' ];
$element_type = isset( $settings[ 'element_type' ] ) ? $settings[ 'element_type' ] : '';
array_push( $my_class, $element_type );
$data->set_attribute( '_root', 'class', $my_class );

echo "<div {$data->render_attributes( '_root' )}>";
if ( !$element_type ) {
  if ( current_user_can( 'update_core' ) )
    echo '<div class="vnx_error no_data text-center"><b>Chưa chọn style cho table</div>';
} else {
  $domain = ( isset( $_POST[ "input_text_search" ] ) && $_POST[ 'input_text_search' ] ) ? $_POST[ 'input_text_search' ] : "";
  $TLD = ( isset( $_POST[ "extension_TLD" ] ) && $_POST[ 'extension_TLD' ] ) ? json_encode( $_POST[ 'extension_TLD' ] ) : "";
  $csvdata = [];
  $get_csv = $data->get_tld_file_data();

  if ( isset( $get_csv[ 'status' ] ) && $get_csv[ 'status' ] == 'success' )
    $csvdata = isset( $get_csv[ 'data' ] ) ? $get_csv[ 'data' ] : [];
  ?>
  <script>
    var tld_data = <?php echo json_encode( $csvdata, JSON_PRETTY_PRINT ) ?>;
    sessionStorage.setItem('TLD_Data', JSON.stringify(tld_data));
  </script>

  <?php if ( isset( $domain ) && $domain != "" && $element_type == "search_domain" ) { ?>
    <style>
      #vnx_hidden_form_search {
        display: none;
      }
    </style>
  <?php } ?>
  <!---------------------- SEARCH DOMAIN ----------------------------->
<?php if ( $element_type == "search_domain" ) : ?>
<div class="vnx_domain_search w-full bg-white border border-gray-200 rounded-lg shadoresult"
  id="vnx_domain_search_result">
  <div class="vnx-grid-container ">
    <div class=" flex items-center">
      <span class="loadding_when_search_result">
        <img class="sm:m-auto" src="https://vietnix.vn/wp-content/uploads/2023/06/check-domain.svg">
      </span>
      <span class="ml-1"><span class="count-result-search">0</span> Kết quả tìm kiếm cho: </span>
    </div>
    <div class="flex items-center"><select class="select-domain sm:ml-2.5" id="select_domain" name="select-domain">
        <option>0 tên miền</option>
      </select></div>
    <div class="flex items-center md:justify-end lg:justify-end sm:justify-end justify-center"><button
        class="button_search_other_domain">Tìm kiếm tên miền khác</button></div>
  </div>
  <p class="result_keyword_false hidden">Từ khóa không hợp lệ: <span class="key_word_check_false"></span></p>
</div>

<?php endif; ?>
<!---------------------- END SEARCH DOMAIN ----------------------------->

<!---------------------- WHOIS DOMAIN ----------------------------->
<?php if($element_type == "whois_domain") : ?> 
  <div id="vnx_show_hide_domain_search_result" class="hidden">
      <?php 
        wp_enqueue_style( 'vnx_table_price-center' );
      ?>
      <input type="hidden" name="back_portal" id="back_portal" value="<?=(isset($settings["back_portal"])) ? 'Yes' : 'No'?>">
      <input type="hidden" id="cart_cookie_age" value="30">
      <input type="hidden" class="vnx_link_portal" name="vnx_link_portal" id="vnx_link_portal" value="<?=$settings["vnx_link_portal_redirect"]['url'] ?? '';?>" data-rel="<?=$settings["vnx_link_portal_redirect"]['rel'] ?? ''?>" data-target="<?=$settings["vnx_link_portal_redirect"]['newTab'] ?? ''?>">
      <input type="hidden" class="vnx_link_see_whois" name="vnx_link_see_whois" id="vnx_link_see_whois" value="<?=$settings["vnx_link_see_whois"]['url']?? '';?>" data-rel="<?=$settings["vnx_link_see_whois"]['rel'] ?? ''?>" data-target="<?=$settings["vnx_link_see_whois"]['newTab'] ?? ''?>">
      <div class="vnx_domain_search w-full bg-white   rounded-lg shadoresult flex  items-center md:px-4 md:py-2.5 px-px"
      id="vnx_whois_domain_search_result">
      <div class="flex items-center column-1">
          <div class="vnx_custom_icon_search"><img src="https://vietnix.vn/wp-content/uploads/2023/08/Search.svg" alt=""></div>
          <div class="flex flex-col pl-3.5 column-1 vnx_custom_processbar">
              <div><p><span class="text_result">Đang tra cứu </span>  <span class="count_check">0</span> / <span class="count_check_total">0</span> tên miền.<span class="animate_dot"></span></p></div>
              <div><progress class="progress progress1" max="100" min="0" value="0"></div>
          </div>
      </div>
      <div class=" column-2 vnx_button_export"><button class="flex justify-between items-center vnx_button_export_csv_download cursor-pointer"><img src="https://vietnix.vn/wp-content/uploads/2023/08/.svg" class="px-1"> <span>Tải file<span></button></div>
  </div>
  <p class="result_keyword_false md:px-4 md:py-2.5 px-px hidden">Tên miền không đúng định dạng: <span class="key_word_check_false"></span></p>
  </div>

<?php endif; ?> 
<!----------------------END WHOIS DOMAIN ----------------------------->

<input type="hidden" name="template" id="vnx_suggest_tld" value='<?= $TLD ?>'>
<textarea placeholder="Search" id="vnx_search_many_domain" name="search_domain"
  class="w-full py-3 pl-11 pr-5 rounded-lg hidden"><?= $domain ?></textarea>


<style>
  .column-1 {
    flex-basis: 90%;
  }

  .column-2 {
    flex-basis: 10%;
  }

  .vnx_button_download {
    color: #219653;
    text-align: center;
    font-family: Roboto;
    font-size: 16px;
    font-style: normal;
    font-weight: 600;
    line-height: normal;
  }

  .vnx_custom_processbar .progress {
    width: 60%;
  }

  @media (max-width: 767px) {
    .column-1 {
      flex-basis: 80%;
    }

    .column-2 {
      flex-basis: 20%;
    }

    .vnx_custom_processbar .progress {
      width: 80%;
    }
  }

  @media (max-width: 414px) {
    .vnx_custom_processbar .progress {
      width: 90%;
    }
  }

  .vnx_custom_processbar .progress::-webkit-progress-bar {
    border-radius: 24.649px;
    background: #F0F4F6;
    box-shadow: 0px 3.081181526184082px 9.243544578552246px 0px rgba(164, 164, 164, 0.30) inset;
  }

  .vnx_custom_processbar .progress::-webkit-progress-bar,
  .vnx_custom_processbar .progress::-webkit-progress-value {
    border-radius: 10px;
  }

  .vnx_custom_processbar .progress::-moz-progress-bar {
    border-radius: 10px;
  }

  .vnx_custom_processbar .progress::-webkit-progress-value {
    border-radius: 24.649px;
    background: #38A7FF;
    box-shadow: 3.081181526184082px 3.081181526184082px 9.243544578552246px 0px rgba(255, 255, 255, 0.30) inset;
  }

  .vnx_custom_icon_search img {
    width: 40px;
    height: 40px;
  }

  .custom_width_icon_search img {
    width: 37px;
    height: 37px;
  }

  .animate_dot::after {
    content: '';
    animation: dotsAnimation 2s infinite;
  }

  @keyframes dotsAnimation {

    0%,
    20% {
      content: '.';
    }

    40% {
      content: '..';
    }

    60% {
      content: '...';
    }

    80%,
    100% {
      content: '';
    }
  }
</style>
<?php
}
echo '</div>';