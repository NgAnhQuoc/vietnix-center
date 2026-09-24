<?php

use HelperCenter\View;

try {
    $version_style = $data->settings['version'];

    $file_csv = $data->settings['import-csv'];

    // yêu cầu chọn version style
    if (!isset($version_style)) {
        echo '<div class="vnx_error no_data"><b>Yêu cầu chọn Version style</div>';
        return;
    }

    // yêu cầu import file csv
    if (!isset($file_csv)) {
        echo '<div class="vnx_error no_data"><b>Không có data tryền vào</div>';
        return;
    }
    $my_class = [$version_style, 'w-full'];
    $data->set_attribute('_root', 'class', $my_class);
} catch (Exception $e) {
    throw new Exception($e->getMessage());
    return;
}

echo "<div {$data->render_attributes('_root')}>";
?>
<?php

if (!function_exists('showIcon_Center')) {
    function showIcon_Center($settingIcon, $class)
    {
        if (!empty($settingIcon)) {
            if ($settingIcon['library'] == 'svg') {
                echo '<img src="' . trim($settingIcon['svg']['url']) . '" alt="icon tooltip vnx-icon" class="' . $class . '"/>';
            } else {
                echo '<i aria-hidden="true" class="' . $class . ' ' . $settingIcon['icon'] . '"></i>';
            }
        }
    }
}

try {
    $data = isset($data) ? $data : new stdClass();
    $settings = $data->settings;
    $indexActiveCycle = (int) $settings['cycle_popular'];
    $indexActiveService = (int) $settings['service_popular'];

    $dataCSV = $data->geFileDataUpload();

    $boundaries = $data->getRangeIndexTypeDataCSV();
    $cycle = $data->getByTypeDataCSV('cycle', 0);
    $listDescription = $data->getByTypeDataCSV('description', 1);

    // Range for product information
    $rangeInfo = [
        'name' => 'Thông tin sản phẩm',
        'start' => $boundaries[1][0] + 1,
        'end' => $boundaries[1][1]
    ];

    // Range for URL buttons
    $rangeURLButton = [
        'name' => 'Url',
        'start' => $boundaries[2][0] + 2,
        'end' => $boundaries[2][1]
    ];

    // Display cycles
    echo "<div id='top-vnx-price-have-compare' class='vnx-container-cycle'>
    <div class='vnx-cycle grid grid-cols-2 tablet:flex'>";

    foreach ($cycle as $index => $item) {
        $parts = explode("|", $item);
        $main = trim($parts[0]);
        $extra = isset($parts[1]) ? trim($parts[1]) : "";
        $isPopular = ($index === $indexActiveCycle);

        $prefix = isset($settings['circle_class_prefix']) ? $settings['circle_class_prefix'] : '';
        $suffix = isset($settings['circle_class_suffix']) ? $settings['circle_class_suffix'] : '';
        $circle_class = $prefix . $data->circle_text_to_key($main) . $suffix;

        echo "<div class='vnx-container-item cursor-pointer " . esc_html($circle_class) . " " . ($isPopular ? "vnx-active" : "") . "'>
            <span class='vnx-time'>{$main}</span>";

        if (!empty($extra)) {
            echo "<span class='vnx-sale'>{$extra}</span>";
        }

        if ($isPopular) {
            echo "<img src='" . $settings['cycle_lable']['url'] . "' class='vnx-cycle-popular hidden tablet:block' />";
            echo "<img src='" . $settings['cycle_lable_mb']['url'] . "' class='vnx-cycle-popular_mobile block tablet:hidden' />";
        }

        echo "</div>";
    }

    echo "</div></div>";

    // Display prices
    echo "<div id='splide' class='splide splide_table' data-start='$indexActiveService'>";

    // Arrows splide
    echo '<div class="splide__arrows splide__arrows--ltr service-price-arrow">
              <button class="splide__arrow splide__arrow--prev" type="button" aria-label="Previous slide">
                <i class="fas fa-angle-left"></i>
              </button>
              <button class="splide__arrow splide__arrow--next" type="button" aria-label="Next slide">
                <i class="fas fa-angle-right"></i>
              </button>
            </div>';

    echo "<div class='splide__track overflow-x-clip overflow-y-visible'>";
    echo "<div class='splide__list vnx-container-price vnx-container-price-desktop'>";

    // Loop through product information
    $countProduct = 0;
    foreach (array_slice($dataCSV['data'], 2) as $index => $item) {
        $prices = $data->getByTypeDataCSV('prices', $index + 2);
        $price = explode('|', $item[3 + $indexActiveCycle]);
        $urlLabelDiscount = $price[3];
        $indexActiveService = $settings['show_service_popular'] != null ? (int) $settings['service_popular'] : -1;
        $isPopular = $index === $indexActiveService;
        $countProduct++;



        echo "<div class='vnx-warp-item-price  p-2 tablet:p-0 splide__slide '>";
        // Lasbel Popular
        if ($isPopular) {
            $color = !empty($settings['highlight_lable_bg']) ? $settings['highlight_lable_bg'] : 'linear-gradient(104deg, #FF8600 0%, #FFB300 100%)';
            $img_label = !empty($settings['highlight_lable']['url']) ? $settings['highlight_lable']['url'] : 'https://vietnix.vn/wp-content/uploads/2024/12/hot_label_hosting.svg';
            echo "<div class='vnx-label-popular' style='background:" . $color . ";'>";
            echo "<div class='vnx-label-image'>";
            echo "<img src='" . $img_label . "'/>";
            echo "</div>";
            echo "</div>";
        }

        echo "<div class='vnx-item " . ($isPopular ? 'vnx-popular' : '') . "'>";
        echo "<div class='vnx-name'>" . $item[0] . "</div>";

        // Discounted prices
        foreach ($prices as $index => $price) {
            $price = explode('|', $price);

            $discountPrice = trim($price[1]);
            $tempPrice = trim($price[2]);
            $discountCode = str_replace('/', '|', trim($price[4]));
            $code = trim($price[5]);
            $cycleTemp = explode('|', $cycle[$index])[0];
            $classActive = ($indexActiveCycle === $index) ? '' : 'hidden';
            $classActiveTooltip = $tempPrice == "" ? 'hidden' : '';
            $classActiveCode = ($discountCode == "" || $code == "") ? 'hidden' : '';

            echo "<div class='vnx-warp-discount-price {$classActive} flex flex-col'>";
            echo "<div class='w-full flex items-end'>";
            echo "<div class='vnx-sale'>";
            foreach ($prices as $index => $price_sale) {
                $price_sale = explode('|', $price_sale);
                $originalPrice = $price_sale[0];
                $originalgap = !empty($price_sale[0]) && $price_sale[0] != null && $price_sale[0] != " " ? 'mr-2' : '';
                $classActive_price = ($indexActiveCycle === $index) ? ' ' : 'hidden';
                $labelSpecial = trim($price_sale[6]);
                echo "<img src='" . $labelSpecial . "' class='vnx-label-special {$classActive_price}' />";
                echo "<span class='vnx-original-price {$originalgap} {$classActive_price}'>" . $originalPrice . "</span>";
            }
            echo "</div>";
            echo "<div class='vnx-discount-price'>";
            echo "<span class='vnx-price'>" . $discountPrice . "</span>";
            echo "<span class='vnx-time'>/th</span>";

            echo "<div class='vnx-icon cursor-pointer $classActiveTooltip'>";
            showIcon_Center($settings['vnx-tooltip-icon-price'], 'vnx-tooltip-icon-price');
            showIcon_Center($settings['vnx-tooltip-icon-price-hover'], 'vnx-tooltip-icon-price-hover hidden');

            echo "<div class='vnx-tooltip'>";
            echo "<div class='text-base font-bold leading-6 mb-2'>Tạm tính</div>";
            echo "<div class='grid grid-cols-2 gap-y-2'>";
            echo "<div>Chu kỳ:</div>";
            echo "<span class='font-semibold'>$cycleTemp</span>";
            echo "<div>Tổng:</div>";
            echo "<span class='font-semibold leading-6 bg-gradient-to-r from-[#F3B847] to-[#F49846] bg-clip-text text-transparent'>" . $tempPrice . "đ</span>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";

            echo "<div class='w-full'>";

            echo "<div class='vnx-label-code mt-2 w-full flex $classActiveCode'>";
            echo "<span class='vnx-discount-code whitespace-nowrap'>$discountCode</span>";
            echo "<span class='vnx-code break-all w-full'>$code</span>";

            echo "<span class='vnx-warp-icon-copy cursor-pointer' data-code='$code'>";
            echo showIcon_Center($settings['copy_icon'], 'icon-copy');
            echo showIcon_Center($settings['paste_icon'], 'icon-paste hidden');
            echo "<span class='vnx-tooltip-copy'>Sao chép mã</span>";

            echo "</span>";

            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        // Product information
        echo "<div class='vnx-info-product h-full'>";
        echo "<div class='flex flex-col gap-3 pt-5 h-full justify-end border-t border-gray-200'>";
        for ($info = $rangeInfo['start']; $info < $rangeInfo['end']; $info++) {
            $itemInfo = explode("|", $item[$info]);
            $infoIcon = trim(end($itemInfo));
            $description = trim($listDescription[$info - $rangeInfo['start']]);

            echo "<div class='flex justify-between items-center'>";
            echo "<div class='flex gap-2 tablet:gap-3 items-center'>";

            if ($infoIcon == 'highlight') {
                showIcon_Center($settings['highlight_icon'], 'highlight_icon');
            } elseif ($infoIcon == 'yes') {
                showIcon_Center($settings['yes_icon'], 'vnx-yes-icon');
            } elseif ($infoIcon == 'no') {
                showIcon_Center($settings['no_icon'], 'vnx-no-icon');
            } else {
                echo '<i aria-hidden="true" class="vnx-custom-icon ' . $infoIcon . '"></i>';
            }

            echo "<div class='flex gap-2'>";
            echo "<div class='" . ($infoIcon == 'no' ? 'opacity-50 ' : '') . "vnx-text-info'>" . $itemInfo[0] . "</div>";
            echo "<div class='vnx-text-info2'>" . $itemInfo[1] . "</div>";
            echo "</div>";
            echo "</div>";

            if (!empty($description)) {
                echo "<div class='vnx-icon-tooltip cursor-pointer'>";
                showIcon_Center($settings['tooltip_icon'], 'tooltip-icon');
                echo "<div class='vnx-tooltip'>" . $description . "</div>";
                echo "</div>";
            }

            echo "</div>";
        }

        for ($index = $rangeURLButton['start']; $index <= $rangeURLButton['end']; $index++) {
            $isCycleActive = $indexActiveCycle + $rangeURLButton['start'] === $index;

            $priceDetails = explode("|", $prices[$index - $rangeURLButton['start']]);
            $originalPrice = trim($priceDetails[0]);
            $discountedPrice = trim($priceDetails[1]);
            $temporaryPrice = !empty(trim($priceDetails[2])) ? trim($priceDetails[2]) : $discountedPrice;

            $productName = trim($item[0]);


            $productCategory = trim(preg_replace('/[^a-zA-Z\s]/', '', $productName));

            $mainCycle = trim(explode("|", $cycle[$index - $rangeURLButton['start']])[0]);

            echo "<a href='" . $item[$index - 1] . "' 
                      class='vnx-button vnx-btn-conversion vnx-button-register " .
                ($isCycleActive ? "" : "hidden") .
                ($isPopular ? " vnx-btn-popular" : "") . "'
                      rel='nofollow'
                      data-price='$temporaryPrice' 
                      data-period='$mainCycle' 
                      data-product-name='$productName'
                      data-product-category='$productCategory'>
                      Đăng ký ngay
                  </a>";
        }


        echo "<button  class='vnx-btn-compare vnx_tablet:hidden text-[#007CFC]' 
            :disabled=\"compare.includes($countProduct - 1)\" 
            v-on:click='addCompare($countProduct - 1 )'>
            <i v-if=\"compare.includes($countProduct - 1)\" class='fa-regular fa-circle-check'></i>
            <i  v-if=\"!compare.includes($countProduct - 1)\" class='fa-light fa-circle-plus'></i>
            <span>So sánh</span>
        </button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }

    echo "</div>";
    echo "</div>";
    echo "</div>";


    /// Hiển thị sticky so sánh

?>
    <div v-cloak class="sticky vnx_tablet:hidden mt-6 bottom-0 w-full bg-white border-t-0 border-[#c0c0c2]  p-0 m-0" v-if="compare.filter(i => i !== '').length > 0">
        <div class="absolute left-1/2 -translate-x-1/2 w-[calc(100vw-20px)] h-[1px] bg-[#C0C0C2]"></div>
        <div class="flex w-full p-0 m-0">
            <!-- Các item đã chọn -->
            <div
                v-for="(item, index) in compare"
                :key="index"
                class="flex-1 justify-center items-center p-4 border-r border-[#C0C0C2] relative flex gap-2.5 border-t-0">
                <div class="text-center text-[#282828] text-lg font-medium leading-[30px]">
                    <span v-if="titles[index]">{{ titles[item] }}</span>

                    <!-- Nút đóng -->
                    <div v-if="item !== ''" class="hover:cursor-pointer absolute flex justify-center items-center top-1 p-1 right-1 w-5 h-5 rounded-full bg-[#d8d9db]"
                        @click="removeCompare(index)">
                        <i class="fa-solid fa-xmark text-white text-xs"></i>
                    </div>

                    <!-- Nút thêm  -->
                    <a href="#top-vnx-price-have-compare" v-if="item === ''" class="hover:cursor-pointer flex w-8 h-8 justify-center items-center gap-2 border border-dashed border-black">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
            </div>



            <!-- Nút so sánh và xoá -->
            <div class="flex-1  p-4 flex flex-col items-center gap-2">
                <button
                    @click="emitCompareNow()"
                    :disabled="!hasTwoValues()"
                    class="px-4 py-1 h-8 flex justify-between items-center bg-[#007cfc] rounded text-white text-sm disabled:opacity-20 disabled:cursor-not-allowed">
                    So sánh ngay
                </button>
                <div @click="closeCompare" class="text-[#f73131] text-sm cursor-pointer">Xóa tất cả</div>
            </div>
        </div>
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100vw-20px)] h-[1px] bg-[#C0C0C2]"></div>

    </div>



<?php


} catch (Exception $e) {
    throw new Exception('Error in vnx-price_hosting_v2 widget: ' . $e->getMessage());
}

echo '</div>';
