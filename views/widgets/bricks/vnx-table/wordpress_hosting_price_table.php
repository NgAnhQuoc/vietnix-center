<?php
use HelperCenter\View;

try {
  wp_enqueue_style('owl-carousel_stylesheet-center');
  wp_enqueue_script('owl-carousel-library-center');

  $data = isset($data) ? $data : new stdClass();
  if (!isset($data->settings)) {
    if (current_user_can('update_core'))
      echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
    return;
  }
  $settings = $data->settings;
  $get_csv = $data->get_upload_file_data();

  if ($get_csv['status'] == 'error') {
    if (current_user_can('update_core'))
      echo '<div class="vnx_error no_data"><b>' . $get_csv['message'] . '</div>';
    return;
  }
  if ($get_csv['status'] == 'success' && !empty($get_csv['data']))
    $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
  if (empty($csv_data)) {
    if (current_user_can('update_core'))
      echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html(__FILE__) . '</div>';
    return;
  }           

  $data_price = $csv_data;

  $header = $data_price[0];
  $cycle = array('Cơ bản', 'Tiêu chuẩn', 'Nâng cao');

  if (!function_exists('findValueIndex_Center')) {
    function findValueIndex_Center($arr, $value)
    {
      foreach ($arr as $index => $object) {
        if ($object[0] === $value) {
          return $index;
        }
      }
      return -1;
    }
  }
  if (!function_exists('findIndexInObject_Center')) {
    function findIndexInObject_Center($obj, $searchValue)
    {
      foreach ($obj as $key => $value) {
        if ($value === $searchValue) {
          return $key;
        }
      }
      return null;
    }
  }
  if (!function_exists('convertStringToArray_Center')) {
    function convertStringToArray_Center($string)
    {
      $pattern = '/\[(.*?)\]/'; // Regular expression to match data within square brackets
      preg_match_all($pattern, $string, $matches); // Extract data within brackets

      $result = $matches[1]; // Extracted data will be in the first capture group

      return $result;
    }
  }
  if (!function_exists('generate_random_string_Center')) {
    function generate_random_string_Center($length)
    {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $random_string = '';

      for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
      }

      return $random_string;
    }
  }
  $random_string = generate_random_string_Center(5);
  $price_list = [];
  $targetValue = ($settings['cycle_price']) ?? '1 Năm';

  foreach ($data_price as $object) {
    if ($object[0] === $targetValue && in_array($object[2], $cycle)) {
        $price_list[] = $object;
    }
  }
  $button_attributes = '';
  if (isset($settings['list_viewmore_url']['newTab']) && $settings['list_viewmore_url']['newTab'] !== '') {
    $button_attributes .= 'target="_blank" ';
  }
  if (isset($settings['list_viewmore_url']['rel']) && $settings['list_viewmore_url']['rel'] !== '') {
    $button_attributes .= 'rel="'.$settings['list_viewmore_url']['rel'].'" ';
  }
  ?>
  <!-- Desktop -->
  <div id="vnx-wordpress-hosting-table" class="w-full el-custom-table-price">

    <!-- Tabs content -->
    <div class="bg-white">
          <?php
          View::render('widgets/bricks/vnx-table/wordpress/wordpress_hosting_price_desktop', ['header' => $header,'settings' => $settings, 'data' =>$price_list, 'random_string' =>$random_string]);
          ?>
    </div>
    <!-- /Tabs content -->
  </div>
  <!-- /Desktop -->
  <div class="flex w-full self-center items-center justify-center">
      <a href="<?= $settings['list_viewmore_url']['url'] ?>" <?= $button_attributes ?> class="flex flex-row items-center justify-center gap-x-1 p-1 no-underline" rel="nofollow"><img src="https://vietnix.vn/wp-content/uploads/2023/08/icon_down.svg" alt=""><span class="text-base font-normal text-[#38A7FF]">Xem Thêm</span></a>
  </div>

  <script>
    jQuery(document).ready(function($){
      $(document).ready(function(){
        var carousel = $('.loop<?= $random_string ?>');
        carousel.owlCarousel({
        autowidth: true,
        autoplay: false,
        autoplayHoverPause: true,
        autoplayTimeout: 3000,
        autoplaySpeed: 800,
        center: false,
        items: 3,
        autoHeight:true,
        merge:true,
        mergeFit:true,
        autoPosition:true,
        stagePadding: 0,
        loop: false,
        dots: false,
        margin: 20,
        nav: true,
        navText: ['<i class="fas fa-arrow-left"></i>', '<i class="fas fa-arrow-right"></i>'],
        responsive: {
            0: {
              items: 1,
              nav: false,
              loop: true,
              center: true,
            },
            768: {
              items: 2,
              nav: false,
              loop: true,
              center: true,
            },
            1024: {
              items: 3
            }
          },
    });
    carousel.trigger("to.owl.carousel", [4, 1])
  });
  
  });
  </script>
  <?php
}catch (Exception $e) {
  echo "Hosing wordpress template have error. Please fix that first!";
}