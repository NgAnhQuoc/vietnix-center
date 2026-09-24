<?php

use HelperCenter\View;

try {
    $version_style = $data->settings['version'];

    $file_csv = $data->settings['import-csv'];

    // yêu cầu chọn version style
    if (!isset($version_style)) {
        echo '<div class="vnx_error no_data"><b>Yêu cầu chọn Version stylew</div>';
        return;
    }

    // yêu cầu import file csv
    if (!isset($file_csv)) {
        echo '<div class="vnx_error no_data"><b>Không có data tryền vàow</div>';
        return;
    }
    $my_class = [$version_style ,'w-full'];
    $data->set_attribute('_root', 'class', $my_class);

} catch (Exception $e) {
    throw new Exception($e->getMessage());
   
}

echo "<div {$data->render_attributes('_root')}>";
?>
<?php
   



   try {
    // Lấy dữ liệu từ các phương thức
    $boundaries = $data->getRangeIndexTypeDataCSV();

 
    $dataCSV = $data->geFileDataUpload();

    $rangeConfigProd = [
        'name' => 'Thông tin cấu hình',
        'start' => $boundaries[4][0],
        'end' => $boundaries[4][1] -1
    ];



    if (isset($dataCSV['data']) && is_array($dataCSV['data'])) {
        // Duyệt qua từng phần tử trong mảng data
        echo "<div class='w-full vnx-price_scroll_warp vnx-sidebar-xscroll'>";
        $listProd = array_slice($dataCSV['data'], 1);
        foreach ($listProd as $index => $item) {
            $nameProd = $item[0];
            $priceProd = $item[1];
            $specialUrl = $item[2];
            $code = $item[3];
            $listConfigProd = array_slice($item, $rangeConfigProd['start'], $rangeConfigProd['end'] - $rangeConfigProd['start'] + 1);
    
            echo "<div class='vnx-product-card vnx-warp-item-price'>";
           
            echo "<div class= 'vnx-product-info'>";
            echo "    <div class='vnx-product-name'>$nameProd</div>";
            echo "    <div class='vnx-product-price'>
                        <span class='vnx-product-price-label'>Chỉ từ</span>
                        <span class='vnx-product-price-value'>$priceProd</span>
                        <span class='vnx-product-price-label'>/th</span>

                    </div>";
            
            if (!empty($specialUrl)) {
                echo "<div class='vnx-product-special-url'>
                            <img src='$specialUrl' alt='Special Image' class='img-responsive'>
                    </div>";
            }

            if (!empty($code)) {
                $explodeCode = explode('|', $code);
                $discountCode = str_replace('/', '|', trim($explodeCode[0]));
                $textCode = $explodeCode[1];
            
                echo "<div class='vnx-product-code'>
                        <span class='vnx-product-code-label'>$discountCode</span>";
            
            
            
                echo "<span class='vnx-warp-icon-copy cursor-pointer' data-code='$textCode'>";
                if (!empty($textCode)) {
                    echo "<span class='vnx-product-code-text'>$textCode </span>";
                    echo "<span class='vnx-copy'>";
                    $data->showIcon_Center('copy_icon','vnx-icon-copy');
                    $data->showIcon_Center('paste_icon','vnx-icon-paste hidden');
                    echo "<span class='vnx-tooltip-copy'>Sao chép mã</span>";
                    echo" </span>";
                }
          
                echo "</span>";
            
                echo "</div>";
            }

            echo "</div>";
           
            echo "    <div class='vnx-product-config'>";
            foreach ($listConfigProd as $key => $value) {
                $itemConfig = explode('|', $value);
                $nameConfig = $itemConfig[0];
                $extraConfig = $itemConfig[1];
                $iconConfig = trim(end($itemConfig));

                echo "<div class='vnx-product-config-item'>";

                if ($iconConfig == 'highlight') {
                    $data->showIcon_Center($settings['highlight_icon'], 'highlight_icon');
                } elseif ($iconConfig == 'yes') {
                    $data->showIcon_Center('yes_icon', 'vnx-yes-icon');
                } elseif ($iconConfig == 'no') {
                    $data->showIcon_Center('no_icon', 'vnx-no-icon');
                } elseif (trim($iconConfig) == '') {
                    
                } else {
                    echo '<i aria-hidden="true" class="vnx-custom-icon ' . $iconConfig . '"></i>';
                }

                echo "<span class='vnx-config-detail " . ($iconConfig == 'no' ? 'vnx-config-detail-no' : '') . "'>$nameConfig</span>";
                echo "<span class='vnx-config-detail-extra'> $extraConfig</span>";
                echo "</div>";
            }
            echo "    </div>";
            echo "</div>";
        }

        echo "</div>";
    } else {
        echo "Không có dữ liệu để hiển thị.\n";
    }
}
    catch (Exception $e) {
        throw new Exception('Error in vnx-price_scroll widget: ' . $e->getMessage());
        return;
    }
    ?>
<?php
echo '</div>';