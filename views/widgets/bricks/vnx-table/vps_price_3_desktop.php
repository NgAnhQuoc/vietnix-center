<?php
$data = isset( $data ) ? $data : new stdClass();
if ( !isset( $data->row ) || empty( $data->row ) )
    return;
define_if_not_defined_Center( 'CSV_FILE_COLUMN_CHU_KY', 0 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_GOI_DICH_VU', 1 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_NHAN', 2 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_TT_TOI_THIEU', 3 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_CPU', 4 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_RAM', 5 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_O_CUNG', 6 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_TANG_DIRECT_ADMIN', 7 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_GIA_GOC', 8 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_GIAM', 9 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_GIA_SAU_GIAM', 10 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_DON_VI', 11 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_TAM_TINH', 12 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_URL_DANG_KY', 13 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_LOAI_DICH_VU', 14 );
define_if_not_defined_Center( 'CSV_FILE_COLUMN_KM', 15 );

$hide_col_list = isset( $data->hide_col_list ) ? $data->hide_col_list : array();
$chu_ky = isset( $data->header[ CSV_FILE_COLUMN_CHU_KY ] ) ? $data->header[ CSV_FILE_COLUMN_CHU_KY ] : '';
$goi_dich_vu = isset( $data->header[ CSV_FILE_COLUMN_GOI_DICH_VU ] ) ? $data->header[ CSV_FILE_COLUMN_GOI_DICH_VU ] : '';
$file_col_CPU = isset( $data->header[ CSV_FILE_COLUMN_CPU ] ) ? $data->header[ CSV_FILE_COLUMN_CPU ] : '';
$file_col_RAM = isset( $data->header[ CSV_FILE_COLUMN_RAM ] ) ? $data->header[ CSV_FILE_COLUMN_RAM ] : '';
$file_col_O_Cung = isset( $data->header[ CSV_FILE_COLUMN_O_CUNG ] ) ? $data->header[ CSV_FILE_COLUMN_O_CUNG ] : '';
$file_col_Direct = isset( $data->header[ CSV_FILE_COLUMN_TANG_DIRECT_ADMIN ] ) ? $data->header[ CSV_FILE_COLUMN_TANG_DIRECT_ADMIN ] : '';
$gia_goc = isset( $data->header[ CSV_FILE_COLUMN_GIA_GOC ] ) ? $data->header[ CSV_FILE_COLUMN_GIA_GOC ] : '';
?>
<table class="w-full text-sm hidden lg:table">
    <colgroup>
        <col width="17%" />
        <?php
        echo ( in_array( '4', $hide_col_list ) ) ? '' : '<col width="9%" />';
        echo ( in_array( '5', $hide_col_list ) ) ? '' : '<col width="11%" />';
        echo ( in_array( '6', $hide_col_list ) ) ? '' : '<col width="12%" />';
        echo ( in_array( '7', $hide_col_list ) ) ? '' : '<col width="14%" />';
        ?>
        <col width="16%" />
        <col width="6%" />
        <col width="16%" />
    </colgroup>
    <thread>
        <tr class="border border-b-2 text-base">
            <th class="pl-8 pr-1 text-left">
                <?php echo $goi_dich_vu; ?>
            </th>
            <?php
            echo ( in_array( '4', $hide_col_list ) ) ? '' : '<th class="p-3">' . $file_col_CPU . '</th>';
            echo ( in_array( '5', $hide_col_list ) ) ? '' : '<th class="p-3">' . $file_col_RAM . '</th>';
            echo ( in_array( '6', $hide_col_list ) ) ? '' : '<th class="p-3">' . $file_col_O_Cung . '</th>';
            echo ( in_array( '7', $hide_col_list ) ) ? '' : '<th class="p-3">' . $file_col_Direct . '</th>';
            ?>
            <th class="p-3 whitespace-nowrap">
                <?php echo $gia_goc; ?>
            </th>
            <th class="p-3"></th>
            <th class="py-3 px-8"></th>
        </tr>
    </thread>
    <tbody>
        <?php
        foreach ( $data->row as $key => $item ) :
            $CSV_FILE_COLUMN_NHAN = isset( $item[ CSV_FILE_COLUMN_NHAN ] ) ? $item[ CSV_FILE_COLUMN_NHAN ] : '';
            $CSV_FILE_COLUMN_GOI_DICH_VU = isset( $item[ CSV_FILE_COLUMN_GOI_DICH_VU ] ) ? $item[ CSV_FILE_COLUMN_GOI_DICH_VU ] : '';
            $CSV_FILE_COLUMN_LOAI_DICH_VU = isset( $item[ CSV_FILE_COLUMN_LOAI_DICH_VU ] ) ? $item[ CSV_FILE_COLUMN_LOAI_DICH_VU ] : '';
            if ( !$CSV_FILE_COLUMN_LOAI_DICH_VU )
                $CSV_FILE_COLUMN_LOAI_DICH_VU = 'nvme';
            $CSV_FILE_COLUMN_CPU = isset( $item[ CSV_FILE_COLUMN_CPU ] ) ? $item[ CSV_FILE_COLUMN_CPU ] : '';
            $CSV_FILE_COLUMN_RAM = isset( $item[ CSV_FILE_COLUMN_RAM ] ) ? $item[ CSV_FILE_COLUMN_RAM ] : '';
            $CSV_FILE_COLUMN_O_CUNG = isset( $item[ CSV_FILE_COLUMN_O_CUNG ] ) ? $item[ CSV_FILE_COLUMN_O_CUNG ] : '';
            $CSV_FILE_COLUMN_TANG_DIRECT_ADMIN = isset( $item[ CSV_FILE_COLUMN_TANG_DIRECT_ADMIN ] ) ? $item[ CSV_FILE_COLUMN_TANG_DIRECT_ADMIN ] : '';
            $CSV_FILE_COLUMN_GIAM = isset( $item[ CSV_FILE_COLUMN_GIAM ] ) ? $item[ CSV_FILE_COLUMN_GIAM ] : '';
            $CSV_FILE_COLUMN_KM = isset( $item[ CSV_FILE_COLUMN_KM ] ) ? $item[ CSV_FILE_COLUMN_KM ] : '';
            $CSV_FILE_COLUMN_GIA_SAU_GIAM = isset( $item[ CSV_FILE_COLUMN_GIA_SAU_GIAM ] ) ? $item[ CSV_FILE_COLUMN_GIA_SAU_GIAM ] : '';
            $CSV_FILE_COLUMN_GIAM = isset( $item[ CSV_FILE_COLUMN_GIAM ] ) ? $item[ CSV_FILE_COLUMN_GIAM ] : '';
            $CSV_FILE_COLUMN_GIA_GOC = isset( $item[ CSV_FILE_COLUMN_GIA_GOC ] ) ? $item[ CSV_FILE_COLUMN_GIA_GOC ] : '';
            $CSV_FILE_COLUMN_DON_VI = isset( $item[ CSV_FILE_COLUMN_DON_VI ] ) ? $item[ CSV_FILE_COLUMN_DON_VI ] : '';
            $CSV_FILE_COLUMN_TAM_TINH = isset( $item[ CSV_FILE_COLUMN_TAM_TINH ] ) ? $item[ CSV_FILE_COLUMN_TAM_TINH ] : '';
            $CSV_FILE_COLUMN_URL_DANG_KY = isset( $item[ CSV_FILE_COLUMN_URL_DANG_KY ] ) ? $item[ CSV_FILE_COLUMN_URL_DANG_KY ] : '#';
            $CSV_FILE_COLUMN_TT_TOI_THIEU = isset( $item[ CSV_FILE_COLUMN_TT_TOI_THIEU ] ) ? $item[ CSV_FILE_COLUMN_TT_TOI_THIEU ] : '';
            $opacity = $CSV_FILE_COLUMN_TT_TOI_THIEU ? ' opacity-50' : '';
            $bgr = ( $key % 2 === 0 ) ? ' bg-gray-100' : '';
            ?>
            <tr class="border<?php echo $opacity . $bgr; ?>">
                <td class="pl-8 pr-1 py-2.5 font-bold text-base">
                    <div class="flex flex-col justify-center">
                        <?php
                        if ( $CSV_FILE_COLUMN_NHAN ) :
                            $label = explode( ", ", trim( $CSV_FILE_COLUMN_NHAN ) );
                            ?>
                            <div class="text-white text-xs font-medium leading-[14px] p-1 mb-2 rounded-[3px] inline w-fit"
                                style="background-color: <?php echo isset( $label[ 1 ] ) ? $label[ 1 ] : ''; ?>;">
                                <?php echo isset( $label[ 0 ] ) ? $label[ 0 ] : ''; ?>
                            </div>
                            <?php
                        endif;
                        echo $CSV_FILE_COLUMN_GOI_DICH_VU;
                        ?>
                    </div>
                </td>
                <?php
                if ( !in_array( '4', $hide_col_list ) ) :
                    ?>
                    <td class="px-3 py-2.5 text-center">
                        <div class="flex flex-col items-center justify-center leading-8">
                            <?php
                            $alt = '';
                            $src = '';
                            switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                                case 'cloud_server':
                                    $alt = 'icon CPU bảng giá Cloud Server';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-cloud-server.svg';
                                    break;

                                case 'vps_server':
                                    $alt = 'icon CPU bảng giá VPS Server';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-cpu-bang-gia-vps-server.svg';
                                    break;

                                case 'cloud_vps':
                                    $alt = 'icon cpu bảng giá VPS Cheap';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2023/10/icon-cpu-bang-gia-vps-cheap.svg';
                                    break;

                                case 'vps_gpu':
                                    $alt = 'icon CPU bảng giá VPS GPU';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/09/microchip-1-1-1.svg';
                                    break;

                                default:
                                    $alt = 'icon CPU bảng giá VPS NVMe';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/09/microchip-1-1-1.svg';
                                    break;
                            }
                            echo '<img class="h-4" alt="' . esc_attr( $alt ) . '" src="' . esc_attr( $src ) . '" />';
                            echo $CSV_FILE_COLUMN_CPU;
                            ?>
                        </div>
                    </td>
                    <?php
                endif;
                if ( !in_array( '5', $hide_col_list ) ) :
                    ?>
                    <td class="px-3 py-2.5 text-center">
                        <div class="flex flex-col items-center justify-center leading-8">
                            <?php
                            $alt = '';
                            $src = '';
                            switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                                case 'cloud_server':
                                    $alt = 'icon RAM bảng giá Cloud Server';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-cloud-server.svg';
                                    break;

                                case 'vps_server':
                                    $alt = 'icon RAM bảng giá VPS Server';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-ram-bang-gia-vps-server.svg';
                                    break;

                                case 'cloud_vps':
                                    $alt = 'icon RAM bảng giá VPS Cheap';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2023/10/icon-ram-bang-gia-vps-cheap.svg';
                                    break;

                                case 'vps_gpu':
                                    $alt = 'icon RAM bảng giá VPS GPU';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/09/memory-1-1.svg';
                                    break;

                                default:
                                    $alt = 'icon RAM bảng giá VPS NVMe';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/09/memory-1-1.svg';
                                    break;
                            }
                            echo '<img class="h-4" alt="' . esc_attr( $alt ) . '" src="' . esc_attr( $src ) . '" />';
                            echo $CSV_FILE_COLUMN_RAM;
                            ?>
                        </div>
                    </td>
                    <?php
                endif;
                if ( !in_array( '6', $hide_col_list ) ) :
                    ?>
                    <td class="px-3 py-2.5 text-center">
                        <div class="flex flex-col items-center justify-center leading-8">
                            <?php
                            $alt = '';
                            $src = '';
                            switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                                case 'cloud_server':
                                    $alt = 'icon ổ cứng SSD bảng giá Cloud Server';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-o-cung-ssd-bang-gia-cloud-server.svg';
                                    break;

                                case 'vps_server':
                                    $alt = 'icon ổ cứng SSD bảng giá VPS Server';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/12/icon-o-cung-ssd-bang-gia-vps-server.svg';
                                    break;

                                case 'cloud_vps':
                                    $alt = 'icon ổ cứng SSD bảng giá VPS Cheap';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2023/10/icon-o-cung-ssd-bang-gia-vps-cheap.svg';
                                    break;

                                case 'vps_gpu':
                                    $alt = 'icon ổ cứng SSD bảng giá VPS GPU';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/09/hdd-1-1.svg';
                                    break;

                                default:
                                    $alt = 'icon ổ cứng SSD bảng giá VPS NVMe';
                                    $src = 'https://vietnix.vn/wp-content/uploads/2021/09/hdd-1-1.svg';
                                    break;
                            }
                            echo '<img class="h-4" alt="' . esc_attr( $alt ) . '" src="' . esc_attr( $src ) . '" />';
                            echo $CSV_FILE_COLUMN_O_CUNG;
                            ?>
                        </div>
                    </td>
                    <?php
                endif;
                if ( !in_array( '7', $hide_col_list ) ) :
                    ?>
                    <td class="px-3 py-2.5 text-center">
                        <div class="flex flex-col items-center justify-center leading-8">
                            <?php
                            if ( $CSV_FILE_COLUMN_TANG_DIRECT_ADMIN == '1' ) {

                                switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                                    case 'vps_gpu':
                                        echo $CSV_FILE_COLUMN_TANG_DIRECT_ADMIN;
                                        break;

                                    case 'cloud_server':
                                        echo '<img class="h-4" alt="Tặng DirectAdmin khi đăng ký Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tang-directadmin-khi-dang-ky-cloud-server.svg" />';
                                        break;

                                    case 'vps_server':
                                        echo '<img class="h-4" alt="Tặng DirectAdmin khi đăng ký VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tang-directadmin-khi-dang-ky-vps-server.svg" />';
                                        break;

                                    case 'cloud_vps':
                                        echo '<img class="h-4" alt="Tặng DirectAdmin khi đăng ký VPS Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2021/08/times2-1-1.svg" />';
                                        break;

                                    default:
                                        echo '<img class="h-4" alt="Tặng DirectAdmin khi đăng ký VPS NVMe" src="https://vietnix.vn/wp-content/uploads/2021/08/times2-1-1.svg" />';
                                        break;
                                }

                            } else {

                                switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {
                                    case 'vps_gpu':
                                        echo '<img class="h-[18px]" src="https://vietnix.vn/wp-content/uploads/2022/05/microchip.svg" alt="icon GPU" />';
                                        echo $CSV_FILE_COLUMN_TANG_DIRECT_ADMIN;
                                        break;

                                    case 'cloud_server':
                                        echo '<img class="h-[18px]" alt="Không tặng DirectAdmin Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/khong-tang-DirectAdmin-Cloud-Server.svg" />';
                                        break;

                                    case 'vps_server':
                                        echo '<img class="h-[18px]" alt="Không tặng DirectAdmin VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/khong-tang-DirectAdmin-Cloud-Server.svg" />';
                                        break;

                                    case 'cloud_vps':
                                        echo '<img class="h-[18px]" alt="Không tặng DirectAdmin VPS Giá Rẻ" src="https://vietnix.vn/wp-content/uploads/2021/08/times-1.svg" />';
                                        break;

                                    default:
                                        echo '<img class="h-[18px]" src="https://vietnix.vn/wp-content/uploads/2021/08/times-1.svg" alt="icon không tặng DirectAdmin" />';
                                        break;
                                }

                            }
                            ?>
                        </div>
                    </td>
                <?php endif; ?>
                <td class="px-3 py-2.5 text-right text-base">
                    <?php
                    if ( $CSV_FILE_COLUMN_GIAM && !$CSV_FILE_COLUMN_KM ) {
                        ?>
                        <span class="el-custom-text-price leading-5 font-bold">
                            <?php echo $CSV_FILE_COLUMN_GIA_SAU_GIAM; ?>
                        </span>
                        <span class="text-xs p-1 el-custom-text-discount">
                            <?php echo $CSV_FILE_COLUMN_GIAM; ?>
                        </span><br>
                        <span class="el-custom-text-price-base leading-5 text-base">
                            <?php echo $CSV_FILE_COLUMN_GIA_GOC; ?>
                        </span>
                        <?php
                    } else {
                        ?>
                        <span class="el-custom-text-price leading-5 font-bold">
                            <?php echo $CSV_FILE_COLUMN_GIA_GOC ?>
                        </span>
                        <?php
                    }
                    ?>
                    <span>
                        <?php echo '/' . $CSV_FILE_COLUMN_DON_VI; ?>
                    </span>
                    <?php
                    if ( $CSV_FILE_COLUMN_KM ) :
                        ?>
                        <div
                            class="flex flex-row bg-[#F8E2E2] rounded-md border border-[#FF0000] justify-center justify-center text-xs font-bold px-2 py-1 mt-1">
                            <img src="https://vietnix.vn/wp-content/uploads/2023/05/price-table-gif.png" />
                            <span>+
                                <?php echo $CSV_FILE_COLUMN_KM; ?>
                            </span>
                        </div>
                        <?php
                    endif;
                    ?>
                </td>
                <td class="p-3">
                    <div class="relative icon-estimating inline-block">
                        <?php
                        if ( !$CSV_FILE_COLUMN_TT_TOI_THIEU ) {
                            switch ($CSV_FILE_COLUMN_LOAI_DICH_VU) {

                                case 'cloud_server':
                                    echo '<img class="h-8 cursor-pointer" alt="Tag Price Cloud Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-cloud-server.svg" />';
                                    break;

                                case 'vps_server':
                                    echo '<img class="h-8 cursor-pointer" alt="Tag Price VPS Server" src="https://vietnix.vn/wp-content/uploads/2021/12/tag-price-vps-server.svg" />';
                                    break;

                                default:
                                    echo '<img class="h-8 cursor-pointer" src="https://vietnix.vn/wp-content/uploads/2021/09/tag-type1.svg" alt="icon tam tinh" />';
                                    break;
                            }
                            ?>
                            <div class="absolute bg-white w-48 text-left estimating-cost p-4 rounded-lg">
                                <span class="font-bold py-2">Tạm tính</span>
                                <div class="py-2">
                                    <span class="mr-6">Chu kỳ :</span>
                                    <span>
                                        <?php echo $item[ 0 ] ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="mr-6">Tổng :</span>
                                    <span class="el-custom-text-price leading-5 font-bold">
                                        <?php echo $CSV_FILE_COLUMN_TAM_TINH; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                </td>
                <td class="py-2.5 text-right pr-8">
                    <?php
                    if ( $CSV_FILE_COLUMN_TT_TOI_THIEU ) {
                        ?>
                        <span class="avalible-text text-base font-bold">Áp dụng từ
                            <?php echo $CSV_FILE_COLUMN_TT_TOI_THIEU; ?> tháng
                        </span>
                        <?php
                    } else {
                        ?>
                        <a class="px-5 py-2 el-custom-btn-register font-bold vnx-btn-conversion" rel="nofollow"
                            data-price="<?php echo $CSV_FILE_COLUMN_TAM_TINH; ?>"
                            data-period="<?php echo $item[CSV_FILE_COLUMN_CHU_KY] ?>" data-product-name="<?php echo $item[CSV_FILE_COLUMN_GOI_DICH_VU] ?>" data-product-category="<?php echo trim(preg_replace('/\d+/', '', $item[CSV_FILE_COLUMN_GOI_DICH_VU])); ?>"
                            href="<?php echo $CSV_FILE_COLUMN_URL_DANG_KY; ?>">
                            Đăng ký
                        </a>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>