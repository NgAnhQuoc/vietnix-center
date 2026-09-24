<?php

use HelperCenter\View;
use Bricks\Breakpoints;

$data = isset($data) ? $data : new stdClass();
$settings = $data->settings;
// print_r($settings);
if (isset($settings['repeater_layout_carousel_price'])) {

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
  if (!function_exists('move_variable_and_following_elements_to_first_index_Center')) {
    function move_variable_and_following_elements_to_first_index_Center($array, $key)
    {
      $keys = array_keys($array);
      $index = array_search($key, $keys);

      if ($index !== false) {
        $before = array_slice($array, 0, $index);
        $after = array_slice($array, $index);
        $array = array_merge($after, $before);
      }

      return $array;
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
  $tab_active = 1;
  if (isset($settings['card_show_tab'])) {
    $tab_active = $settings['tab_active'] ?? 1;
    if ($tab_active >= count($settings['repeater_layout_carousel_price']))
      $tab_active = count($settings['repeater_layout_carousel_price']);
    if ($tab_active < 1)
      $tab_active = 1;
  }
?>

  <!-- Desktop -->
  <div class="w-full lg:block el-custom-table-price vnx-table-price-carousel-<?= $random_string ?>" data-tab-active="<?= $tab_active ?>">
    <!-- Tabs wrapper -->
    <?php if (isset($settings['card_show_tab']) && $settings['card_show_tab'] != '') : ?>
      <div class="w-full flex justify-center mb-10 table_price_carousel_tab">
        <div class="vnx-carousel-tab-wrapper el-custom-tab-wrapper el-custom-tab-hosting flex flex-row bg-white py-2 px-2 rounded-md text-[#38A7FF] font-bold">
          <?php foreach ($settings['repeater_layout_carousel_price'] as $key => $name) :
          ?>
            <div class="flex-1 text-center cursor-pointer hover:bg-gray-200 py-1 mx-2 rounded-t-md leading-8 vnx-tab relative <?php echo ($key + 1 == $tab_active) ? 'tab-active' : '' ?>" data-target="vnx-tab-<?= $random_string . $key ?>" data-id="vnx-tab-<?= $random_string . $key ?>">
              <?php
              echo esc_html($name['list_title'])
              ?>
            </div>
          <?php
          endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <!-- /Tabs wrapper -->

    <!-- Tabs content -->
    <div class="vnx-tabs-content bg-white vnx_table_price_v6">
      <?php foreach ($settings['repeater_layout_carousel_price'] as $slug => $name) :
        $active = $slug + 1 == $tab_active ? 'is_active ' : '';
      ?>
        <div class="cards vnx-carousel-tab-content vnx-tab-content relative vnx-tab-<?= $random_string . $slug . ' ' . $active ?>">

          <?php
          View::render('widgets/bricks/vnx-table/template/price-table-carousel-layout-v4', ['setting' => $settings, 'info' => $name, 'button_icon' => $settings['list_button_icon'], 'button_text' => $settings['list_button_text'], 'order_by' => ($settings["order_by"]) ?? '', 'random_string' => $random_string, 'slug' => $slug]);
          ?>
          <div id="vnx-tab-gift-<?= $random_string . $slug ?>" class="card__gift_data absolute w-fit bg-white z-10 py-2 px-4 rounded-lg hidden">
            <div class="gift_box_description relative">
              <?php echo $name['list_gift_description']; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <!-- /Tabs content -->
  </div>

  <style>
    .card,
    .owl-item {
      transition: all 1s ease-out;
      -webkit-backface-visibility: hidden;
      -webkit-transform: translateZ(0) scale(1, 1);
    }

    .owl-wrapper {
      position: relative;
      width: 100%;
      margin: 0;
      padding: 0;
    }
    .owl-stage-outer::before {
          content: "";
          position: absolute;
          top: 0;
          left: 0;
          width: 5%;
          height: 100%;
          background: linear-gradient(90deg, #FFF 0%, rgba(255, 255, 255, 0.00) 100%);
          pointer-events: none; 
          z-index: 1;
      }
      .owl-stage-outer::after {
          content: "";
          position: absolute;
          top: 0;
          right: 0;
          width: 5%;
          height: 100%;
          background: linear-gradient(90deg,rgba(255, 255, 255, 0.00) 0%,  #FFF 100%);
          pointer-events: none;
      }
    .cards {
      position: relative;
      width: 100%;
      background-color: #fefefe;
    }

    .vnx-table-price-carousel-<?= $random_string ?> article.card {
      height: fit-content;
    }

    .card {
      width: 100%;
      justify-content: center;
      height: 100%;
      padding: 24px;
      border-radius: 8px;
      transform: scale(1);
      transition: transform 0.2s 0.1s ease-out, opacity 1s ease;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
      overflow: hidden;
      border: 1px solid #E0E0E0;
    }

    .card.has_header_bandage {
      padding: 54px 24px 24px 24px;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .active.center .card {
      box-shadow: -1px 2px 6px 0px rgba(0, 0, 0, 0.35);
      transform: box-shadow 0.3s ease, transform 0.1s 0.4s ease-in, opacity 0.4s ease;
    }

    .card__header {
      text-align: center;
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
      position: relative;
      margin-top: -1px;
    }

    .card__header .card__header_title {
      font-size: 20px !important;
      line-height: 30px;
      color: #525666;
      font-weight: 700 !important;
      margin-bottom: 16px;
      line-height: unset;
    }

    .vnx-table-price-carousel-<?= $random_string ?>.vnx-table-price-carousel-<?= $random_string ?> article p.card__header_title {
      padding-top: unset;
      margin-bottom: unset;
      line-height: unset;
    }

    .vnx-table-price-carousel-<?= $random_string ?>.vnx-table-price-carousel-<?= $random_string ?> article .card__content .card__price__period p {
      line-height: unset;
      padding-top: unset;
      margin-bottom: unset;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .vnx-carousel-tab-wrapper .vnx-tab {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card__header span.header_bandage {
      position: absolute;
      top: 0px;
      right: -1px;
      background: #FC5844;
      padding-block: 4px;
      padding-inline: 16px;
      color: #fff;
      border-top-right-radius: 8px;
      border-bottom-left-radius: 8px;
    }

    .card__content {
      align-items: center;
      text-align: center;
      color: #fff;
      opacity: 1;
      position: relative;
    }

    .active .card__content {
      opacity: 1;
      transition: opacity 0.4s ease;
    }

    .card__price {
      /* padding-block: 10px; */
      border-bottom: 1px solid  #E0E0E0;
    }



    .card__price .card__price__value {
      position: relative;
      display: inline-flex;
      width: 100%;
      padding-top: 12px;
      padding-bottom: 24px;
      align-items: center;
      justify-content: center;
      width: 100%;
    }

    .card__price .card__price__value span {
      font-size: 25px;
      font-weight: bold;
      color: #38A7FF;
    }

    .card__price .card__price_bandage {
      background: #FFFDE5;
      width: 36px;
      border-radius: 16px;
      text-align: center;
      font-size: 12px;
      padding-block: 2px;
      line-height: 18px;
      border: 1px solid #FFB800;
      color: #FFB800;
      margin-left: 10px;
    }

    .card__price .card__price__period {
      display: flex;
      justify-content: center;
      font-size: 16px;
      color: #818A91;
    }

    .card__price .card__price__period .card__line-through {
      color: #757885;
      text-align: center;
      font-family: Roboto;
      font-size: 16px;
      font-style: normal;
      font-weight: 400;
      line-height: 24px;
      text-decoration: line-through;
    }

    .card__title {
      font-size: 2em;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 24px;
      padding-block: 16px;
      overflow: visible;
    }

    .card__title .card__system_info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      column-gap: 20px;
    }

    .card__title .card__system_info .image_box,
    .card__title .card__system_info .text_box {
      width: 50%;
      align-items: center;
      display: inline-flex;
    }

    .card__title .card__system_info .image_box {
      justify-content: flex-start;
    }

    .card__title .card__system_info .text_box {
      justify-content: flex-start;
    }

    .card__title .card__system_info img {
      max-width: 24px;
    }

    .card__title .card__system_info .system_info_title {
      width: fit-content;
      color: #525666;
      font-family: Roboto;
      font-size: 16px;
      font-style: normal;
      font-weight: 400;
      line-height: 24px;
    }

    .center.active .card__title .card__system_info span,
    .center.active.cloned:last-child .card__title .card__system_info span {
      opacity: 1;
    }

    .center .card {
      opacity: 1;
      transform: scale(1);
    }

    .card__gift .card__gift_title {
      display: flex;
      justify-content: flex-start;
      align-items: center;
    }

    .card__gift .card__gift_title img {
      max-width: 24px;
      max-height: 24px;
      margin-right: 8px;
    }

    .card__gift .card__gift_title span.card__gift_title_info {
      color: #525666;
      font-style: italic;
      font-weight: 400;
      font-size: 14px;
      line-height: 20px;
      text-decoration-line: underline;
      cursor: pointer;
      text-align: left;
    }



    .owl-item.active.center {
      min-width: 266px !important;
    }


    #gift_box {
      visibility: hidden;
      opacity: 0;
      transition: visibility 0s, opacity 0.1s linear;
    }

    .card__gift .card__gift_box {
      padding: 20px;
      background: #ffffff;
      position: absolute;
      bottom: 25%;
      z-index: 1;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
      transition: all 0.3s ease-in;
    }

    .gift_box_description {
      font-size: 12px;
      color: #333;
    }



    .card__gift_box .card__gift_box_header {
      font-style: normal;
      font-weight: 600;
      font-size: 12px;
      line-height: 14px;
      text-transform: uppercase;
      margin-bottom: 10px;
      color: #333333;
    }

    .card__gift_box .gift_box_description ul {
      list-style: inside;
    }
   .gift_box_description p {
      margin: 0px !important;
    }
    .card__gift_box .gift_box_description ul>li {
      font-style: normal;
      font-weight: 400;
      font-size: 12px;
      color: #333333;
      text-align: initial;
    }

    .card__button_btn:active {
      box-shadow: inset 0px 4px 4px rgba(0, 0, 0, 0.25);
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-dots {
      display: flex;
      flex-direction: row;
      justify-content: center;
      position: absolute;
      width: 100%;
      bottom: -40px;
      gap: 16px;
      flex-wrap: wrap;
    }
    .vnx-table-price-carousel-<?= $random_string ?> .owl-stage {
    display: flex;
    align-items: flex-end;
    }
    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-dots .owl-dot span {
      width: 8px;
      height: 8px;
      border: 1px solid #38A7FF;
      border-radius: 50px;
      background: #fff;
      display: block;
      -webkit-backface-visibility: visible;
      transition: opacity 0.2s ease;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-dots span {
      background: #38A7FF;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-nav {
      color: #FFF;
      top: 50%;
      width: 100%;
    }
    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-nav  i{
      color: #525666;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-nav button.owl-prev {
      position: absolute;
      top: 47%;
      left: -1%;
      /* padding: 7px 12px !important; */
      border-radius: 50px;
      border: 2px solid;
      width: 36px;
      height: 36px;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-nav button.owl-prev {
      left: 0%;
      background-color: #FFF;
      box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.10);
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-nav button.owl-next {
      position: absolute;
      top: 47%;
      right: -1%;
      /* padding: 7px 12px !important; */
      border-radius: 50px;
      border: 2px solid;
      width: 36px;
      height: 36px;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-theme .owl-nav button.owl-next {
      right: 0%;
      background-color: #FFF;
      box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.10);
    }

    .vnx-carousel-tab-wrapper .tab-active {
      background-color: #38A7FF !important;
      color: #ffffff
    }

    .el-custom-tab-hosting .vnx-tab {
      border: 1px solid #38A7FF;
    }

    .table_price_carousel_tab {
      position: relative;
    }

    .table_price_carousel_tab:after {
      position: absolute;
      content: '';
      height: 2px;
      width: 90%;
      bottom: 8px;
      background-color: #38A7FF;
    }

    .single.single-post .vnx-table-price-carousel-<?= $random_string ?> .owl-carousel.owl-loaded {
      display: inline-grid;
    }

    .single.single-post .vnx-table-price-carousel-<?= $random_string ?> .el-custom-tab-wrapper.el-custom-tab-hosting,
    .single.single-post .vnx-table-price-carousel-<?= $random_string ?> .el-custom-tab-wrapper.el-custom-tab-vps {
      width: 100%;
    }

    @media only screen and (max-width: 1024px) {
      .table_price_carousel_tab:after {
        display: none;
      }

      .vnx-tab {
        border-radius: 0.375rem;
      }
    }

    .card__gift_data {
      bottom: 13%;
      right: 0;
      left: 0;
      margin-inline: auto;
      box-shadow: 0px 0px 16px 0px rgba(0, 0, 0, 0.10);
      border: 1px solid #E5E8EF;
    }

    .card__gift_data:after {
      content: "";
      position: absolute;
      bottom: -20px;
      left: 74%;
      margin-left: -10px;
      border-width: 10px;
      border-style: solid;
      border-color: #FFF transparent transparent transparent;
    }

    .btn_coversion_post button {
      border-radius: 8px;
      background: #FFF;
      width: 100%;
      height: 44px;
      transition: background-color 0.3s;
      font-size: 18px;
      font-style: normal;
      font-weight: 500;
      line-height: 28px;
      border: 1px solid #38A7FF;
      margin-bottom: 24px;
    }

    .btn_coversion_post button:hover {
      background: #EBF6FF;
    }
    article.card.has_header_bandage{
      background: linear-gradient(282deg, #00B0FF 5.54%, #3E98EB 100%);
      color: #FFF !important;
    }
    .has_header_bandage .btn_coversion_post button {
      font-size: 18px;
      font-style: normal;
      font-weight: 500;
      line-height: 28px;
      border-radius: 8px;
      color: #FFF;
      border: none;
      background: var(--Gradient-Orangle-01, linear-gradient(90deg, #F3B847 0%, #F49846 100%));
    }

    .has_header_bandage .btn_coversion_post button:hover {
      background: var(--Gradient-Orange-02, linear-gradient(88deg, #FFA800 4.36%, #CB6000 100.54%));
    }

    .infor-title {
      color: var(--Gray-Cold-500, #525666);
      font-family: Roboto;
      font-size: 18px;
      font-style: normal;
      font-weight: 500;
      line-height: 28px;
    }

    .infor-tooltip {
      height: 20px;
      width: 20px;
      line-height: normal !important;
      font-size: 16px;
      position: relative;
    }

    .infor-tooltip .infor-icon .vnx_tooltip_icon {
      font-size: 16px;
      font-style: normal;
      font-weight: 300;
      line-height: normal !important;
      cursor: pointer;
    }

    p#text-tooltip {
      position: absolute;
      width: 162px;
      z-index: 1;
      right: 5px;
      border-radius: 4px;
      background: #FFF;
      box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.10);
      font-family: Roboto;
      font-size: 12px;
      font-style: normal;
      font-weight: 400;
      line-height: 18px;
      text-align: left;
      color: #FFF;
      background-color: #38A7FF;
      padding: 4px 8px;
      margin: 0px;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .card__gift_data ul {
      margin-top: 0px;
      margin-bottom: 0px;
      padding-left: 16px !important;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .card__gift_data ul li {
      padding-bottom: 0px;
      margin-bottom: 0px;
    }
    .has_header_bandage .card__header_title {
    color:#FFF;
    }

    .has_header_bandage .card__line-through{
        color:#FFFFFFCC !important;
    }

    .has_header_bandage .box-content-infor{
        color:#FFF !important;
    }

    .has_header_bandage span.card__price_bandage {
        background: #FFF5F3;
        border: 1px solid #F14C2E;
        color: #F14C2E;
    }
    .has_header_bandage .card__price .card__price__value span {
        color: #FFF000;
    }

    .has_header_bandage .card__price__period {
        color: #FFFFFFCC;
    }

    .has_header_bandage .box-content-infor .infor-title{
    color:#FFF !important;
    }

    .has_header_bandage .card__system_info .system_info_title{
        color: #FFF !important;
    }


    .has_header_bandage .card__system_info .image_box img {
        filter: invert(100%) brightness(200%);
    }
    .has_header_bandage .card__gift_title_info{
        color:#FFF !important;
    }

    .has_header_bandage p#text-tooltip {
        color: #525666;
        background-color: #FFF;
    }
    .vnx-table-price-carousel-<?= $random_string ?> .owl-dots button.owl-dot.active span{
        background: #38A7FF !important;
        width: 40px !important;
        height: 4px !important;
        border:none !important;
        border-radius:0 !important;
    }

    .vnx-table-price-carousel-<?= $random_string ?> .owl-dots button.owl-dot span{
        width: 20px !important;
        height: 4px !important;
        background: #D7EDFF !important;
        border:none !important;
        border-radius:0 !important;
    }
    .has_header_bandage .card__price {
          /* padding-block: 10px; */
          border-bottom: 1px solid  #FFFFFF80;
        }
  </style>

  <script>
    jQuery(document).ready(function($) {
      $(document).ready(function() {
        let carousel = $('.loop<?= $random_string ?>');
        carousel.owlCarousel({
          autowidth: true,
          autoplay: false,
          autoplayHoverPause: true,
          autoplayTimeout: 3000,
          autoplaySpeed: 800,
          center: true,
          items: 3,
          autoHeight: false,
          merge: true,
          mergeFit: true,
          autoPosition: true,
          stagePadding: 15,
          loop: true,
          dots: false,
          dotsEach: true,
          margin: 15,
          nav: true,
          navText: ['<i class="fa-solid fa-angle-left"></i>', '<i class="fa-solid fa-angle-right"></i>'],
          responsive: {
            <?php
            foreach (Breakpoints::$breakpoints as $bp) {
              $breakpoint_key = $bp['key'];
              if ($bp['key'] == 'desktop') {
            ?>
                <?= $bp['width'] ?>: {
                  items: <?= (isset($settings['item_show'])) ? $settings['item_show'] : '1' ?>,
                  nav: <?= (isset($settings['show_arrow'])) ? 'true' : 'false' ?>,
                  loop: <?= (isset($settings['loop_slide'])) ? 'true' : 'false' ?>,
                  dots: <?= (isset($settings['show_dot'])) ? 'true' : 'false' ?>,
                  minWidth: <?= $bp['width'] ?>
                },
              <?php
              } else {
              ?>
                <?= $bp['width'] ?>: {
                  items: <?= (isset($settings['item_show:' . $bp['key']])) ? $settings['item_show:' . $bp['key']] : '1' ?>,
                  nav: <?= (isset($settings['show_arrow:' . $bp['key']])) ? 'true' : 'false' ?>,
                  loop: <?= (isset($settings['loop_slide:' . $bp['key']])) ? 'true' : 'false' ?>,
                  dots: <?= (isset($settings['show_dot:' . $bp['key']])) ? 'true' : 'false' ?>,
                  minWidth: <?= $bp['width'] ?>
                },
            <?php
              }
            }
            ?>
          },
        });
      });
      $('.card__gift_title_info').hover(
        function() {
          var gift_id = $(this).data('gift-id');
          $('#' + gift_id).removeClass('hidden');
        },
        function() {
          var gift_id = $(this).data('gift-id');
          $('#' + gift_id).addClass('hidden');
          $('#' + gift_id).hover(
            function() {
              $(this).removeClass('hidden');
            },
            function() {
              $(this).addClass('hidden');

            }
          );
        }
      );

      $('.infor-icon').hover(
        function() {
          $(this).closest('.infor-tooltip').find('#text-tooltip').removeClass('hidden');
        },
        function() {
          $(this).closest('.infor-tooltip').find('.text-tooltip').addClass('hidden');
        }
      );

    });
  </script>
<?php
  wp_enqueue_style('owl-carousel_stylesheet-center');
  wp_enqueue_script('owl-carousel-library-center');
} else {
  echo $data->render_element_placeholder(['title' => esc_html__('No Data added.', 'vietnix')]);
}
