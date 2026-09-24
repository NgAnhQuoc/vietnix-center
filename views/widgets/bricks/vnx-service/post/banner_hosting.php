<?php
$data = isset($data) ? $data : new stdClass();
if (!isset($data->settings)) {
  if (current_user_can('update_core')) {
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
  }
  return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();
$table_style = isset($settings['table_style']) ? $settings['table_style'] : '';
$item_show = isset($settings['item_show']) ? $settings['item_show'] : '3';
if ($get_csv['status'] == 'error') {
  if (current_user_can('update_core')) {
    echo '<div class="vnx_error no_data"><b>' . $get_csv['message'] . '</div>';
  }
  return;
}
if ($get_csv['status'] == 'success' && !empty($get_csv['data'])) {
  $csv_data = isset($get_csv['data']) ? $get_csv['data'] : array();
}

if (empty($csv_data)) {
  if (current_user_can('update_core')) {
    echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html(__FILE__) . '</div>';
  }
  return;
}
?>
<div class="<?php echo $table_style; ?>  vnx_service_list ">
  <?php
  if (is_array($csv_data)) {
    foreach ($csv_data as $index => $item) {
      if ($index != 0 && $index <= $item_show) {
        $url = count($csv_data) - 1;
        $price = count($csv_data) - 2;
        $tag = count($csv_data) - 3;
        $i = 1;
  ?>
        <div class="vnx_package">
          <?php if (!empty($item[$tag])) {
            $img_tag = explode(" | ", $csv_data[0][$tag]);
          ?>
            <div class="vnx_tag_price ">
              <?php if (!empty($img_tag[1])) { ?>
                <img src="<?php echo $img_tag[1]; ?>" alt="CPU" class="vnx_package_img">
              <?php } else { ?>
                <img src="https://vietnix.vn/wp-content/uploads/2024/04/fire.svg" alt="CPU" class="vnx_package_img">
              <?php } ?>
              <span class="vnx_tag_title"><?php echo $item[$tag]; ?></span>
            </div>
          <?php } ?>
          <p class="vnx_package_title"><?php echo $item[0]; ?></p>
          <div class="vnx_package_list">
            <?php for ($i; $i < $tag; $i++) {
              $titles = explode(" | ", $csv_data[0][$i]);
              $data_infor = explode(" + ", $item[$i]);
            ?>
              <div class="vnx_package_infor">
                <img src="<?php echo $titles[1]; ?>" alt="CPU" class="vnx_package_img">
                <span class="title "><?php echo $titles[0]; ?>:</span>
                <div class="title-sub">
                  <?php if (count($data_infor) == 2) { ?>
                    <p class="title "><?php echo $data_infor[0]; ?><span class="title-plus"> + <?php echo $data_infor[1]; ?></span></p>
                  <?php } else { ?>
                    <p class="title"><?php echo $item[$i]; ?></p>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>
          </div>
          <div class="vnx_package_price ">
            <div class="vnx_price_infor">
              <span>Từ</span>
              <span class="vnx_price_number "><?php echo $item[$price]; ?></span>
              <span>/ Tháng</span>
            </div>
            <div class="vnx_button_price">
              <a id="vnx_button_price" class="brxe-button bricks-button bricks-background-primary " data-price="<?php echo $item[$price]; ?>" data-product-name="<?php echo $item[0]; ?>" href="<?php echo $item[$url]; ?>"> <?php echo $settings['button_register']; ?><i class="fa-light fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
  <?php
      }
    }
  } ?>
</div>
<style>
  .banner_hosting.vnx_service_list {
    display: flex;
    width: 100%;
  }

  .banner_hosting.hidden.vnx_service_list {
    flex-direction: column;
    flex-wrap: nowrap;
    gap: 16px;
  }

  .banner_hosting .vnx_package {
    border: 1px solid #e4e6ea;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
  }

  .banner_hosting .vnx_package_list {
    display: flex;
    flex-direction: column;
    padding: 4px 0px;
    border-top: 1px dashed #E0E0E0;
    border-bottom: 1px dashed #E0E0E0;
    gap: 4px;
  }

  .banner_hosting .vnx_package {
    padding: 24px 8px 8px 8px;
    position: relative;
  }

  .banner_hosting .vnx_tag_price {
    position: absolute;
    right: 0;
    top: 0;
    padding: 4px;
    background-color: #F14C2E;
    border-radius: 0px 8px 0px 8px;
    color: #FFF;
  }

  .banner_hosting .vnx_tag_price img {
    fill: #FFF;
  }

  .banner_hosting a#vnx_button_price {
    border-radius: 8px;
    background: var(--Gradient-Orangle-01, linear-gradient(90deg, #F3B847 0%, #F49846 100%));
    color: #FFF;
    font-weight: 500;
    width: 100%;
  }

  .banner_hosting a#vnx_button_price:hover {
    background: var(--Gradient-Orange-02, linear-gradient(88deg, #FFA800 4.36%, #CB6000 100.54%));
  }

  .banner_hosting a#vnx_button_price i {
    font-size: 18px;
  }

  .banner_hosting .vnx_price_infor {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 4px;
    padding: 8px 0px 12px 0px;
  }

  .banner_hosting .vnx_price_infor span {
    color: var(--Gray-Warm-600, #B3B3B3);
    font-family: Roboto;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 26px;
  }

  .banner_hosting span.vnx_price_number {
    font-family: Roboto;
    font-size: 20px;
    font-style: normal;
    font-weight: 900;
    line-height: 30px;
    background: var(--Gradient-Orangle-01, linear-gradient(90deg, #F3B847 0%, #F49846 100%));
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 900;
  }

  .banner_hosting p.vnx_package_title {
    color: var(--Primary-color-800, #164366);
    font-family: Roboto;
    font-size: 16px;
    font-style: normal;
    font-weight: 700;
    line-height: 24px;
  }

  .banner_hosting .vnx_package_infor {
    display: flex;
    gap: 4px;
    flex-direction: row;
    flex-wrap: nowrap;
    align-content: center;
    align-items: center;
  }

  .banner_hosting .vnx_package_infor img {
    width: 16px;
    height: 16px;
  }

  .banner_hosting .vnx_package_infor span.title {
    color: var(--Gray-Cold-500, #525666);
    font-family: Roboto;
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    line-height: 18px;
  }

  .banner_hosting .title-sub {
    color: var(--Gray-Cold-500, #525666);
    text-align: center;
    font-family: Roboto;
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    line-height: 18px;
  }

  .banner_hosting span.vnx_tag_title {
    color: var(--Base-Color-White, #FFF);
    font-family: Roboto;
    font-size: 10px;
    font-style: normal;
    font-weight: 600;
    line-height: 18px;
  }

  .banner_hosting .vnx_tag_price {
    display: flex;
    flex-direction: row;
    gap: 4px;
  }
  .banner_hosting span.title-plus {
    color: #FF9038;
}
</style>