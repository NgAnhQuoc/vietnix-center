<?php
$widget = isset($data) ? $data : new stdClass();
$settings = $widget->settings;

// geFileDataUpload() trả null khi chưa upload file hoặc file lỗi
$dataCSV = $widget->geFileDataUpload();

if (empty($dataCSV['data'])) {
    echo '<div class="vnx_error no_data"><b>Không có data truyền vào</b></div>';
    return;
}

$transformed = $widget->transform_columns_to_packages($dataCSV);

if (empty($transformed['packages'])) {
    echo '<div class="vnx_error no_data"><b>Không có data truyền vào</b></div>';
    return;
}

$allPackages = $transformed['packages'];

// packages[0] là hàng nhãn -> lấy tên chu kỳ
$labels = isset($allPackages[0]) ? $allPackages[0] : [];
$cycles = isset($labels['groups']['group_1']) ? array_values($labels['groups']['group_1']) : [];

// packages[1] là cột chú thích -> dùng làm tooltip cho từng dòng thông số
$notes = isset($allPackages[1]['groups']['group_2']) ? array_values($allPackages[1]['groups']['group_2']) : [];

// Từ packages[2] trở đi mới là gói dịch vụ
$packages = array_slice($allPackages, 2);

// Giới hạn trong khoảng chu kỳ hiện có, tránh trường hợp set sai làm ẩn hết giá
$indexActiveCycle = isset($settings['cycle_popular']) ? (int) $settings['cycle_popular'] : 0;
if ($indexActiveCycle < 0 || ($cycles && $indexActiveCycle >= count($cycles))) {
    $indexActiveCycle = 0;
}

// 2 banner: bảng cho desktop, mobile cho màn nhỏ. Thiếu bên nào thì dùng bên còn lại.
$bannerDesktop = isset($settings['banner_tabel']['url']) ? $settings['banner_tabel']['url'] : '';
$bannerMobile = isset($settings['banner_mobile']['url']) ? $settings['banner_mobile']['url'] : '';

if ($bannerDesktop === '') {
    $bannerDesktop = $bannerMobile;
}
if ($bannerMobile === '') {
    $bannerMobile = $bannerDesktop;
}

// SCSS đọc 2 biến này và đổi ảnh theo breakpoint
$bannerStyle = '';
if ($bannerDesktop !== '') {
    $bannerStyle = '--vnx-banner:url(' . esc_url($bannerDesktop) . ');'
        . '--vnx-banner-mb:url(' . esc_url($bannerMobile) . ');';
}

// Đơn vị giá cố định; bỏ trống thì lấy tên chu kỳ đang chọn
$priceUnit = isset($settings['price_unit']) ? trim($settings['price_unit']) : '';
?>

<!-- Tablist chu kỳ -->
<?php if (!empty($cycles)) { ?>
    <div class="vnx-container-cycle">
        <div class="vnx-cycle">
            <?php foreach ($cycles as $index => $cycle) {
                $parts = explode('|', $cycle['value']);
                $main = trim($parts[0]);
                $extra = isset($parts[1]) ? trim($parts[1]) : '';

                $prefix = isset($settings['circle_class_prefix']) ? $settings['circle_class_prefix'] : '';
                $suffix = isset($settings['circle_class_suffix']) ? $settings['circle_class_suffix'] : '';
                $circle_class = $prefix . $widget->circle_text_to_key($main) . $suffix;
                ?>
                <div class="vnx-container-item cursor-pointer <?php echo esc_attr($circle_class); ?> <?php echo $index === $indexActiveCycle ? 'vnx-active' : ''; ?>">
                    <span class="vnx-time"><?php echo esc_html($main); ?></span>
                    <?php if (!empty($extra)) { ?>
                        <span class="vnx-sale"><?php echo esc_html($extra); ?></span>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<!-- Danh sách card giá -->
<div class="vnx-container-price">
    <?php foreach ($packages as $package) {
        $prices = isset($package['groups']['group_1']) ? array_values($package['groups']['group_1']) : [];
        $specs = isset($package['groups']['group_2']) ? array_values($package['groups']['group_2']) : [];

        $productName = $package['name'];
        $productLink = isset($package['image']) ? $package['image'] : '';
        $productImage = isset($package['range']) ? $package['range'] : '';
        $productCategory = trim(preg_replace('/[^a-zA-Z\s]/', '', $productName));

        // Tách sẵn giá theo chu kỳ: "giá gốc | giá sau giảm | link đăng ký"
        $cycleRows = [];
        foreach ($prices as $index => $price) {
            $parts = explode('|', $price['value']);
            $original = isset($parts[0]) ? trim($parts[0]) : '';
            $sale = isset($parts[1]) ? trim($parts[1]) : '';
            $url = isset($parts[2]) ? trim($parts[2]) : '';

            $cycleRows[] = [
                'original' => $original,
                // Chưa có giá sau giảm thì hiển thị giá gốc
                'display' => $sale !== '' ? $sale : $original,
                'hasSale' => $sale !== '' && $original !== '',
                'url' => $url !== '' ? $url : $productLink,
                'name' => isset($cycles[$index]['value'])
                    ? trim(explode('|', $cycles[$index]['value'])[0])
                    : trim($price['label']),
                'active' => $index === $indexActiveCycle,
            ];
        }
        ?>
        <div class="vnx-warp-item-price">
            <div class="vnx-item">

                <!-- Header card -->
                <div class="vnx-item-header" <?php echo $bannerStyle ? 'style="' . esc_attr($bannerStyle) . '"' : ''; ?>>
                    <div class="vnx-name"><?php echo wp_kses($productName, ['br' => []]); ?></div>

                    <?php if (!empty($productImage)) { ?>
                        <div class="vnx-header-image">
                            <img src="<?php echo esc_url($productImage); ?>" alt="<?php echo esc_attr($productName); ?>" loading="lazy" />
                        </div>
                    <?php } ?>
                </div>

                <div class="vnx-item-body">

                    <!-- Cột giá + nút đăng ký -->
                    <div class="vnx-price-col">
                        <?php foreach ($cycleRows as $row) { ?>
                            <div class="vnx-warp-discount-price <?php echo $row['active'] ? '' : 'hidden'; ?>">
                                <?php if ($row['hasSale']) { ?>
                                    <span class="vnx-original-price"><?php echo esc_html($row['original']); ?></span>
                                <?php } ?>
                                <span class="vnx-price"><?php echo esc_html($row['display']); ?></span>
                                <span class="vnx-time">/ <?php echo esc_html($priceUnit !== '' ? $priceUnit : $row['name']); ?></span>
                            </div>
                        <?php } ?>

                        <?php foreach ($cycleRows as $row) { ?>
                            <a href="<?php echo esc_url($row['url']); ?>"
                                class="vnx-button vnx-btn-conversion vnx-button-register <?php echo $row['active'] ? '' : 'hidden'; ?>"
                                rel="nofollow"
                                data-price="<?php echo esc_attr($row['display']); ?>"
                                data-period="<?php echo esc_attr($row['name']); ?>"
                                data-product-name="<?php echo esc_attr($productName); ?>"
                                data-product-category="<?php echo esc_attr($productCategory); ?>">
                                <span>Đăng ký ngay</span>
                                <i aria-hidden="true" class="vnx-btn-arrow fas fa-arrow-right"></i>
                            </a>
                        <?php } ?>
                    </div>

                    <!-- Cột thông số kỹ thuật -->
                    <div class="vnx-info-col">
                        <?php foreach ($specs as $index => $spec) {
                            // "yes|no | Nhãn: Giá trị"
                            $specParts = explode('|', $spec['value']);
                            $icon = trim($specParts[0]);
                            $text = isset($specParts[1]) ? trim($specParts[1]) : '';

                            // Nếu ô chỉ có text, không có cờ icon thì mặc định là yes
                            if ($text === '') {
                                $text = $icon;
                                $icon = 'yes';
                            }

                            $note = isset($notes[$index]['value']) ? trim($notes[$index]['value']) : '';
                            ?>
                            <div class="vnx-info-item">
                                <div class="vnx-info-content">
                                    <?php if ($icon === 'no') {
                                        $widget->showIcon_Center('no_icon', 'vnx-no-icon');
                                    } elseif ($icon === 'yes') {
                                        $widget->showIcon_Center('yes_icon', 'vnx-yes-icon');
                                    } else { ?>
                                        <i aria-hidden="true" class="vnx-custom-icon <?php echo esc_attr($icon); ?>"></i>
                                    <?php } ?>

                                    <div class="vnx-text-info <?php echo $icon === 'no' ? 'opacity-50' : ''; ?>">
                                        <?php echo esc_html($text); ?>
                                    </div>
                                </div>

                                <?php if (!empty($note)) { ?>
                                    <div class="vnx-icon-tooltip cursor-pointer">
                                        <?php $widget->showIcon_Center('tooltip_icon', 'tooltip-icon'); ?>
                                        <div class="vnx-tooltip"><?php echo esc_html($note); ?></div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    <?php } ?>
</div>