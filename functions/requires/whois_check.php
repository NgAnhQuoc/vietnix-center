<?php
class VNX_WHOIS_CHECK_Center
{
    public static $domain;
    protected static $domain_name;
    protected static $tld_checking;
    protected static $whois_result;
    protected static $status;
    protected static $whois;
    protected static $data;
    protected static $tld_data;
    public static function setDomain( $domain )
    {
        try {
            self::$domain = $domain;
            $domain_explode = explode( '.', self::$domain, 2 );
            self::$domain_name = $domain_explode[ 0 ];
            if ( isset( $domain_explode[ 1 ] ) )
                self::$tld_checking = $domain_explode[ 1 ];
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }

    public static function checkAjaxNonce()
    {
        if ( !check_ajax_referer( 'whois_nonce', '_wpnonce', false ) ) {
            wp_send_json_error( 'Nonce is invalid' );
            wp_die(); // All ajax handlers should die when finished
        }
    }
    public static function setTldData( $data )
    {
        self::$tld_data = $data;
    }
    public static function getTldPrice()
    {
        try {
            $fail_log = '';
            $tld_prices = array();
            $return = array(
                'fail_log'   => $fail_log,
                'tld_prices' => $tld_prices,
            );
            $getdata = self::getCsvData(); // get regular price form CSV file uploaded
            $csvdata = $getdata[ 'csv_data' ] ?? '';
            $sale_prices = self::getTldSalePrice(); // get sale price from api
            if ( !$csvdata ) {
                $return[ 'fail_log' ] = $getdata[ 'fail_log' ] ?? '';
                return $return;
            }
            if ( !$sale_prices ) {
                $return[ 'fail_log' ] = 'no sale_prices';
                return $return;
            }
            $count = 0;
            foreach ( $csvdata as $key => $value ) {
                $tld = $value[ 0 ];
                if ( $count == 0 ) {
                    $count++;
                    continue; // skip the first line of CSV file ( the column title )
                }
                if ( !array_key_exists( $tld, $sale_prices ) )
                    continue;
                $other_tool_tiptitle = $value[ 2 ];
                $other_tool_tip = $value[ 3 ];
                $whois_tool_tip = $value[ 4 ];
                $price = $value[ 1 ];
                $tld_prices[ $tld ][ 'regular' ] = $price;
                $sale_register = (array) $sale_prices[ $tld ]->register;
                $sale = $sale_register[ 1 ];
                $tld_prices[ $tld ][ 'sale' ] = $sale;
                $tld_prices[ $tld ][ 'tool_tip_title' ] = $other_tool_tiptitle;
                $tld_prices[ $tld ][ 'other_tool_tip' ] = $other_tool_tip;
                $tld_prices[ $tld ][ 'tool_tip' ] = $whois_tool_tip;
            }
            $sorted_tld = array();
            $high_priority = array( 'vn', 'com', 'net', 'com.vn', 'net.vn', 'info' );
            foreach ( $high_priority as $key => $tld ) {
                $sorted_tld[ $tld ] = $tld_prices[ $tld ];
            }
            foreach ( $tld_prices as $tld => $data ) {
                if ( in_array( $tld, $high_priority ) )
                    continue;
                $sorted_tld[ $tld ] = $tld_prices[ $tld ];
            }
            $return[ 'tld_prices' ] = $sorted_tld;
            return $return;
        }
        catch ( Exception $e ) {
            return "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
        }
    }
    public static function whoisGetSuggest()
    {
        try {
            ob_start();
            $tld_return = '';
            $isRunGetTLD = false;
            if ( !self::$tld_data ) { // nếu ko có $tld_data -> chạy code lấy nó 
                $get_tld_data = self::getTldPrice();
                $tld_data = $get_tld_data[ 'tld_prices' ] ?? '';
                $isRunGetTLD = true;
            } else {
                $tld_data = self::$tld_data;
                $isRunGetTLD = false;
            }
            if ( !$tld_data ) {
                $tld_return = $get_tld_data[ 'fail_log' ] ?? 'whoisGetSuggest Failed!!!';
            } else {
                $tld_return = $isRunGetTLD ? json_encode( $tld_data ) : ''; // trả về trong ajax để lưu storage, mấy lần check sau khỏi lấy nữa
            }
            $i = 0;
            if ( $tld_data ) {
                foreach ( $tld_data as $tld => $data ) {
                    if ( $i >= 3 )
                        break; // hiển thị 3 kết quả thôi, nên đến 3 thì ngưng
                    if ( $tld == self::$tld_checking )
                        continue; // Nếu tld đang check trong ô suggest trùng với tld đang search thì next
                    if ( $tld == 'vn' && strlen( self::$domain_name ) <= 2 )
                        continue; // Không hiện trường hợp đặc biệt
                    // echo '<pre>';
                    // print_r( $data );
                    // echo '</pre>';
                    $regular = str_replace( ',', '', $data[ 'regular' ] );
                    $sale_price = $data[ 'sale' ];
                    $percent = round( 100 * ( $regular - $sale_price ) / $regular );
                    $domain_to_show = self::$domain_name . '.' . $tld;
                    $get_whois = whois_get_domain_status_Center( $domain_to_show );
                    $premium = true;
                    // echo 'domain: ' . $domain_to_show;
                    if ( $get_whois && $get_whois == 'available' )
                        $premium = self::isPremium( $domain_to_show );
                    if ( !$premium ) { // Không hiện trường hợp đặc biệt
                        ?>
                        <div class="suggest_item">
                            <div class="vnx_info">
                                <div class="suggest_domain_Center font-bold flex">
                                    <div class="domain_name">
                                        <?php echo self::$domain_name; ?>
                                    </div>
                                    <div class="domain_tld text-[#FE9842]">
                                        <?php echo '.' . $tld; ?>
                                    </div>
                                </div>
                                <div class="sale_price flex items-center">
                                    <div class="vnx_price align-bottom">
                                        <?php echo number_format( $sale_price ) . '<span class="vnx_currency">đ</span>'; ?>
                                    </div>
                                    <div class="vnx_time align-bottom text-[#828282] text-sm">/năm</div>
                                    <?php
                                    if ( $regular - $sale_price != 0 ) {
                                        echo '<div class="sale_icon text-white text-xs ml-2">' . esc_html( $percent ) . '<span class="vnx_symbol">%</span></div>';
                                    }
                                    ?>
                                </div>
                                <?php
                                if ( $regular - $sale_price != 0 ) {
                                    echo '<div class="regular_price text-[#828282] text-xs line-through">' . number_format( $regular ) . '<span class="vnx_currency">đ</span></div>';
                                }
                                ?>
                            </div>
                            <a href="https://portal.vietnix.vn/cart.php?a=view" data-domain="<?php echo esc_attr( $domain_to_show ); ?>"
                                class="register_domain_action whois_vnx_button bg-brand text-white px-3 py-2.5 rounded-md text-sm"
                                rel="nofollow">Đăng
                                ký ngay</a>
                        </div>
                        <?php
                        $i++;
                    }
                }
            }
            $items = ob_get_clean(); //cho hết bộ nhớ đệm vào biến $result
            $return = array(
                'html'        => $items,
                'isRunGetTLD' => $isRunGetTLD,
                'tld_data'    => $tld_return,
            );
            return $return;
        }
        catch ( Exception $e ) {
            return "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
        }
    }
    public static function getCsvData()
    {
        try {
            $fail_log = '';
            $csvdata = [];
            $return = array(
                'fail_log' => $fail_log,
                'csv_data' => $csvdata,
            );
            $file_url = get_field( 'tld_data', 'option' ) ?? '';
            if ( !$file_url ) {
                $return[ 'fail_log' ] = 'No file_url';
                return $return;
            }
            $cdn_url = get_field( 'cdn_url', 'option' ) ?? '';
            $url_replaced = $cdn_url != '' ? $cdn_url : get_site_url() . '/';
            $file = str_replace( $url_replaced, ABSPATH, $file_url );
            if ( !file_exists( $file ) ) {
                $return[ 'fail_log' ] = 'No file. $file_url: ' . $file_url;
                return $return;
            }
            if ( ( $handle = fopen( $file, "r" ) ) !== FALSE ) {
                while ( ( $data = fgetcsv( $handle ) ) !== FALSE ) {
                    $csvdata[] = $data;
                }
                $return[ 'csv_data' ] = $csvdata;
            } else {
                $return[ 'fail_log' ] = 'Can not handle $file:' . $file;
            }
            fclose( $handle );
            return $return;
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
    public static function getTldSalePrice()
    {
        try {
            $return = '';
            $get_tld_data = get_tld_pricing_from_api_Center();
            $return = $get_tld_data && isset( $get_tld_data->pricing ) ? (array) $get_tld_data->pricing : '';
            return $return;
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
    protected static function setWhoisData()
    {
        try {
            $data = get_whois_data_by_domain_Center( self::$domain );
            if ( !isset( $data ) || empty( $data ) || !$data || isset( $data->error ) )
                return;
            self::$whois = array(
                'domain'      => self::$domain,
                'create'      => $data[ 2 ]->value,
                'expiry'      => $data[ 3 ]->value,
                'registar'    => $data[ 1 ]->value,
                'flag'        => $data[ 4 ]->value,
                'manager'     => $data[ 7 ]->value,
                'name_server' => $data[ 5 ]->value,
                'dnssec'      => $data[ 6 ]->value,
            );
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
    public static function showResultTable()
    {
        try {
            if ( !self::$domain )
                return;
            $case = self::domainCase();
            $get_status = self::caseStatus( $case );
            if ( $get_status == 'available' || $get_status == 'special_available' )
                return; // trả về rỗng để redirect sang trang kia check
            $status = '';
            $icon = '';
            $hidden = '';
            self::setWhoisData(); // Lấy thông tin WHOIS đưa vào self::$whois
            if ( !empty( self::$whois ) ) {
                $status = 'Tên miền đã được đăng ký';
                $icon = 'x-icon.svg';
                $hidden = ' hidden';
                ?>
                <!-- success checking data -->
                <div class="whois-result success text-sm">
                    <div class="whois_status items-center<?php echo $hidden; ?> md:flex flex-row p-5 border-b">
                        <img class="icon_image h-[100px] mr-5"
                            src="<?php echo esc_attr( VNX_PLUGIN_URL_CENTER . 'assets/images/illustrations/search-image.svg' ); ?>"
                            alt="question">
                        <div class="col_right flex flex-col">
                            <div class="text-gray-700 text-[28px] font-bold domain_checking mb-2">
                                <?php
                                echo '<span class="domain_first">' . esc_html( self::$domain_name ) . '</span>';
                                echo '<span class="domain_tld text-orange-500">.' . esc_html( self::$tld_checking ) . '</span>';
                                ?>
                            </div>
                            <div class="flex flex-row items-center domain-info">
                                <img class="status_icon"
                                    src="<?php echo esc_attr( VNX_PLUGIN_URL_CENTER . 'assets/images/icons/' . $icon ); ?>"
                                    alt="status icon">
                                <div class="status ml-1 text-gray-700">
                                    <?php echo esc_html( $status ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="md:hidden font-bold px-4 pt-5 text-base">Thông tin domain</div>
                    <div class="whois_content px-4 py-5 md:px-5 md:py-2.5">
                        <?php
                        if ( self::$whois )
                            self::showWhoisData( self::$whois );
                        ?>
                    </div>
                </div>
                <?php
            } else {
                self::showNotFound();
            }
            // } else {
            //     self::showNotFound();
            // }
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
    protected static function showWhoisData( $data = [] )
    {
        try {
            if ( empty( $data ) )
                return;
            $index = 0;
            foreach ( $data as $key => $value ) {
                $icon = "";
                $label = "";
                switch ($key) {
                    case 'domain':
                        $icon = "globe.svg";
                        $label = "Tên miền";
                        break;
                    case 'create':
                        $icon = "calendar.svg";
                        $label = "Ngày đăng ký";
                        break;
                    case 'expiry':
                        $icon = "calendar.svg";
                        $label = "Ngày hết hạn";
                        break;
                    case 'registar':
                        $icon = "registar.svg";
                        $label = "Chủ sở hữu";
                        break;
                    case 'flag':
                        $icon = "flag.svg";
                        $label = "Cờ trạng thái";
                        break;
                    case 'manager':
                        $icon = "building.svg";
                        $label = "Quản lý tại";
                        break;
                    case 'name_server':
                        $icon = "server-black.svg";
                        $label = "Nameserver";
                        break;
                    case 'dnssec':
                        $icon = "router.svg";
                        $label = "DNSSEC";
                        break;
                    default:
                        # code...
                        break;
                }
                ?>
                <div class="whois-content-<?php echo esc_attr( $key ); ?> vnx_row">
                    <span class="whois-key flex items-center mb-1.5 md:mb-0"><img class="hidden mr-2 md:block"
                            src="<?php echo esc_attr( VNX_PLUGIN_URL_CENTER . 'assets/images/icons/' . $icon ); ?>"
                            alt="domain">
                        <?php echo esc_attr( $label ); ?>
                    </span>
                    <?php
                    // echo '<span class="whois-value">' . esc_html( $value ) . '</span>';
                    if ( $key != 'name_server' ) {
                        echo '<span class="whois-value">' . esc_html( $value ) . '</span>';
                    } else {
                        $name_sv = preg_split( '/\r\n|\r|\n/', $value );
                        if ( empty( $name_sv ) )
                            return;
                        echo '<div class="whois-value">';
                        for ( $i = 0; $i < sizeof( $name_sv ); $i++ ) {
                            echo '<div class="val">' . esc_html( $name_sv[ $i ] ) . '</div>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
                <?php
                $index++;
            }
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
    public static function showNotFound()
    {
        ?>
        <div class="whois-result not-found w-full h-ful flex items-center justify-center">
            <div class="flex flex-col items-center pb-10 px-2 text-center">
                <img class="w-20 h-20 m-4" src="https://vietnix.vn/wp-content/uploads/2023/06/none_domain.svg"
                    alt="none domain" />
                <div class="mb-1 text-lg font-medium text-[#000000]">Không tìm thấy kết quả WHOIS</div>
                <span class="text-sm font-normal text-[#828282]">Không thể lấy thông tin tên miền. Vui lòng nhập một tên miền
                    khác!</span>
            </div>
        </div>
        <?php
    }
    protected static function isCaseTwoCharVn( $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        $domain_explode = explode( '.', $domain, 2 );
        $domain_name = $domain_explode[ 0 ];
        if ( isset( $domain_explode[ 1 ] ) )
            $tld = $domain_explode[ 1 ];
        return ( strlen( $domain_name ) <= 2 && $tld == 'vn' );
    }
    protected static function isCaseTwoCharVnOther( $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        $domain_explode = explode( '.', $domain, 2 );
        $domain_name = $domain_explode[ 0 ];
        if ( isset( $domain_explode[ 1 ] ) )
            $tld = $domain_explode[ 1 ];
        return ( strlen( $domain_name ) <= 2 && $tld != 'vn' && strstr( $tld, 'vn' ) );

    }
    protected static function isCaseTwoCharGlobal( $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        $domain_explode = explode( '.', $domain, 2 );
        $domain_name = $domain_explode[ 0 ];
        if ( isset( $domain_explode[ 1 ] ) )
            $tld = $domain_explode[ 1 ];
        return ( strlen( $domain_name ) <= 2 && $tld != 'vn' && !strstr( $tld, 'vn' ) );
    }
    protected static function domainCase( $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        $case = '';
        if ( self::isCaseTwoCharVn( $domain ) ) {
            $case = 'twocharsvn';
        } else if ( self::isCaseTwoCharVnOther( $domain ) ) {
            $case = 'twocharsvn_other';
        } else if ( self::isCaseTwoCharGlobal( $domain ) ) {
            $case = 'twochars_global';
        } else {
            $case = 'normal';
        }
        return $case;
    }

    protected static function caseStatus( $case, $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        $status = '';
        if ( $case == 'twocharsvn' ) {
            $status = 'twocharsvn';
        } elseif ( $case == 'twocharsvn_other' ) {
            $status = whois_get_domain_status_Center( $domain );
        } elseif ( $case == 'twochars_global' ) {
            $status = whois_get_domain_status_Center( $domain ); // API này chỉ lỗi ở một số tld, nên check thử
            if ( $status == 'available' ) {
                if ( !function_exists( 'check_premium_domain_Center' ) )
                    return 'premium_error';
                if ( self::isPremium( $domain ) )
                    $status = 'special_available';
            } elseif ( $status == 'error' ) { // Cái nào lỗi check tiếp bằng premium
                $status = self::premiumStatus( $domain );
                switch ($status) {
                    case 'available':
                        $status = 'special_available'; // nếu là premium, có giá, available thì nó là trường hợp đặc biệt
                        break;

                    case 'regthroughothers': // trường hợp này là đăng ký bán ở bên khác ko phải ở API hiện tại, nên ko check được -> tạm hiện lỗi ( chờ API khác)
                        $status = 'error';
                        break;

                    case 'unknown': // check ra lỗi -> tạm hiện lỗi ( chờ API khác)
                        $status = 'error';
                        break;

                    default:
                        $status = 'premium_error';
                        break;
                }
            }
        } else {
            $status = whois_get_domain_status_Center( $domain );
            if ( $status == 'available' ) {
                if ( !function_exists( 'check_premium_domain_Center' ) )
                    return 'premium_error';
                if ( self::isPremium( $domain ) )
                    $status = 'special_available';
            }
        }
        return $status;
    }

    public static function isPremium( $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        if ( function_exists( 'check_premium_domain_Center' ) ) {
            $domain_explode = explode( '.', $domain, 2 );
            $domain_name = $domain_explode[ 0 ];
            if ( isset( $domain_explode[ 1 ] ) )
                $tld = $domain_explode[ 1 ];
            $premium = check_premium_domain_Center( $domain_name, $tld );
            if ( isset( $premium[ 'costHash' ] ) && !empty( $premium[ 'costHash' ] ) )
                return true;
        }
        return false;
    }
    public static function premiumStatus( $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        $return = 'premium_error';
        if ( function_exists( 'check_premium_domain_Center' ) ) {
            $domain_explode = explode( '.', $domain, 2 );
            $domain_name = $domain_explode[ 0 ];
            if ( isset( $domain_explode[ 1 ] ) )
                $tld = $domain_explode[ 1 ];
            $premium = check_premium_domain_Center( $domain_name, $tld );
            $return = isset( $premium[ 'status' ] ) ? $premium[ 'status' ] : 'error';
        }
        return $return;
    }

    public static function showResultLPage( $tld_price_data )
    {
        try {
            $tld_return = '';
            $args = array(
                'status' => 'available',
                'image'  => '',
                'notice' => '',
                'icon'   => '',
                'prices' => array()
            );
            ob_start();
            $isRunGetTLD = false;
            self::setTldData( $tld_price_data );
            if ( !self::$tld_data ) { // nếu ko có $tld_data -> chạy code lấy nó 
                $get_tld_data = self::getTldPrice();
                $tld_data = $get_tld_data[ 'tld_prices' ] ?? '';
                $isRunGetTLD = true;
            } else {
                $tld_data = self::$tld_data;
            }
            $tld_return = $isRunGetTLD ? json_encode( $tld_data ) : ''; // trả về trong ajax để lưu storage, mấy lần check sau khỏi lấy nữa

            $case = self::domainCase();
            $get_status = self::caseStatus( $case );
            if ( $get_status == 'error' || $get_status == 'premium_error' ) {
                self::setWhoisData(); // nếu lỗi -> thử chạy API lấy thông tin WHOIS -> nếu có là đã được ĐK, nếu ko có thì vẫn giữ lỗi (đợi API xịn)
                if ( !empty( self::$whois ) )
                    $get_status = 'unavailable';
            }
            $args[ 'status' ] = $get_status;
            if ( $get_status == 'available' ) {
                if ( !$tld_data ) {
                    $tld_return = $get_tld_data[ 'fail_log' ] ?? 'whoisGetSuggest Failed!!!';
                    $args[ 'status' ] = 'error';
                } else {
                    if ( !array_key_exists( self::$tld_checking, $tld_data ) ) {
                        $args[ 'status' ] = 'wrong_tld';
                    } else {
                        $args[ 'prices' ] = array(
                            'regular'  => $tld_data[ self::$tld_checking ][ 'regular' ],
                            'sale'     => $tld_data[ self::$tld_checking ][ 'sale' ],
                            'tool_tip' => $tld_data[ self::$tld_checking ][ 'tool_tip' ],
                        );
                    }
                }
            }
            self::showResultLPageHTML( $args );
            $html = ob_get_clean(); //cho hết bộ nhớ đệm vào biến $result
            $return = array(
                'html'        => $html,
                'isRunGetTLD' => $isRunGetTLD,
                'tld_data'    => $tld_return,
                'args'        => $args,
            );
            return $return;
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
    protected static function getWhoisItemVarByCase( $args )
    {
        $return = array(
            'status' => $args[ 'status' ],
        );
        switch ($args[ 'status' ]) {
            case 'available':
                $return[ 'image' ] = 'congratulations.svg';
                $return[ 'icon' ] = 'tick.svg';
                $return[ 'notice' ] = 'Tên miền có thể đăng ký';
                $return[ 'prices' ] = $args[ 'prices' ] ?? '';
                $return[ 'tooltip' ] = '';
                break;

            case 'unavailable':
                $return[ 'image' ] = 'search-image.svg';
                $return[ 'icon' ] = 'x-icon.svg';
                $return[ 'notice' ] = 'Tên miền đã được đăng ký';
                $return[ 'prices' ] = '';
                $return[ 'tooltip' ] = '';
                break;

            case 'wrong_tld':
                $return[ 'image' ] = 'search-image.svg';
                $return[ 'icon' ] = 'x-icon.svg';
                $return[ 'notice' ] = 'Tên miền với đuôi <b>' . self::$tld_checking . '</b> không hỗ trợ';
                $return[ 'prices' ] = '';
                $return[ 'tooltip' ] = '';
                break;

            case 'twocharsvn':
                $return[ 'image' ] = 'search-image.svg';
                $return[ 'icon' ] = 'x-icon.svg';
                $return[ 'notice' ] = 'Chưa thể đăng ký tên miền .vn có 1,2 kí tự';
                $return[ 'prices' ] = '';
                $return[ 'tooltip' ] = 'Theo quy định của Trung tâm Internet Việt Nam (VNNIC) tên miền cấp 2 có 1, 2 kí tự hiện chưa thể đăng ký.';
                break;

            case 'special_available':
                $return[ 'image' ] = 'search-image.svg';
                $return[ 'icon' ] = 'tick.svg';
                $return[ 'notice' ] = 'Tên miền đặc biệt';
                $return[ 'prices' ] = $args[ 'prices' ] ?? '';
                $return[ 'tooltip' ] = 'Một số tên miền đặc biệt vui lòng liên hệ Vietnix để được hỗ trợ đăng ký.';
                break;

            case 'premium_error':
                $return[ 'image' ] = 'search-image.svg';
                $return[ 'icon' ] = 'x-icon.svg';
                $return[ 'notice' ] = 'Đã có lỗi xảy ra';
                $return[ 'prices' ] = '';
                $return[ 'tooltip' ] = 'Có lỗi xảy ra trong quá trình kiểm tra tên miền PREMIUM, vui lòng thử lại!';
                break;

            default:
                $return[ 'image' ] = 'search-image.svg';
                $return[ 'icon' ] = 'x-icon.svg';
                $return[ 'notice' ] = 'Đã có lỗi xảy ra';
                $return[ 'prices' ] = '';
                $return[ 'tooltip' ] = 'Không thể lấy thông tin tên miền, vui lòng thử lại với tên miền khác!';
                break;
        }
        return $return;
    }
    protected static function showResultLPageHTML( $args )
    {
        if ( !$args ) {
            echo '<h1 class="text-red">Thiếu $args</h1>';
            return;
        }
        $status = isset( $args[ 'status' ] ) ? $args[ 'status' ] : 'error';
        $get_var = self::getWhoisItemVarByCase( $args ); // so sánh các case, lấy các biến cần thiết hiển thị phía dưới
        $image = $get_var[ 'image' ];
        $icon = $get_var[ 'icon' ];
        $notice = $get_var[ 'notice' ];
        $prices = $get_var[ 'prices' ];
        $tooltip = $get_var[ 'tooltip' ];
        ?>
        <div class="whois_status items-center md:flex flex-row px-5 sm:px-8 py-5 bg-white rounded-[10px]">
            <img class="icon_image h-[193px] w-[394px] mr-2.5 mb-2.5 sm:mb-0"
                src="<?php echo esc_attr( VNX_PLUGIN_URL_CENTER . 'assets/images/illustrations/' . $image ); ?>"
                alt="question">
            <div class="col_right flex sm:items-center flex-col sm:flex-row">
                <?php self::domainWhoisItem( $status, $icon, $notice, $tooltip, $prices ); ?>
            </div>
        </div>
        <?php
    }
    public static function getSuggestItemChunk( $chunk )
    {
        ob_start();
        if ( !$chunk || empty( $chunk ) ) {
            $result = ob_get_clean(); //cho hết bộ nhớ đệm vào biến $result
            return 'Error: No chunk';
        }
        foreach ( $chunk as $tld => $prices ) {
            self::$tld_checking = $tld;
            $domain_to_show = self::$domain_name . '.' . $tld;
            $case = self::domainCase( $domain_to_show );
            $status = self::caseStatus( $case, $domain_to_show );
            // if ( $status == 'error' )
            //     continue;
            $args = array(
                'status' => $status,
                'prices' => $prices,
            );
            $get_var = self::getWhoisItemVarByCase( $args ); // so sánh các case, lấy các biến cần thiết hiển thị phía dưới
            $icon = $get_var[ 'icon' ];
            $notice = $get_var[ 'notice' ];
            $prices = $get_var[ 'prices' ];
            $tooltip = $get_var[ 'tooltip' ];
            echo '<div class="suggest_item flex-col sm:flex-row bg-white sm:bg-transparent py-6 px-4 sm:px-7 mb-3.5 ms:mb-0 sm:rounded-none">';
            self::domainWhoisItem( $status, $icon, $notice, $tooltip, $prices, $domain_to_show );
            echo '</div>';
        }
        $result = ob_get_clean(); //cho hết bộ nhớ đệm vào biến $result
        return $result;
    }
    protected static function domainWhoisItem( $status, $icon, $notice, $tooltip = '', $prices='', $domain = '' )
    {
        if ( !$domain )
            $domain = self::$domain;
        ?>
        <div class="domain_<?php echo esc_attr( $status ); ?> domain_status flex flex-col text-sm mb-5">
            <div class="text-gray-700 text-[28px] font-bold domain_checking mb-2">
                <?php
                echo '<span class="domain_first">' . esc_html( self::$domain_name ) . '</span>';
                echo '<span class="domain_tld text-orange-500">.' . esc_html( self::$tld_checking ) . '</span>';
                ?>
            </div>
            <div class="flex flex-row items-center domain-info">
                <img class="status_icon"
                    src="<?php echo esc_attr( VNX_PLUGIN_URL_CENTER . 'assets/images/icons/' . $icon ); ?>"
                    alt="status icon">
                <div class="status ml-1">
                    <?php
                    echo $notice;
                    if ( $tooltip ) {
                        echo '<div class="tool_tip cursor-pointer relative inline-flex w-3.5 h-3.5 ml-1 border rounded-full items-center justify-center">';
                        echo '<img src="' . VNX_PLUGIN_URL_CENTER . 'assets/images/icons/exclamation-icon.svg" class="vnx_tooltip_icon h-2.5 opacity-70">';
                        echo '<div class="absolute hidden tool_tip_text cursor-default"><div class="tooltip_content relative z-[1]">' . $tooltip . '</div></div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        switch ($status) {
            case 'available':
                $regular = $regular = str_replace( ',', '', $prices[ 'regular' ] );
                $sale_price = $prices[ 'sale' ];
                $tool_tip = $prices[ 'tool_tip' ] ?? '';
                ?>
                <div class="vnx_wrap ms:mt-0">
                    <div class="price_info mb-2 sm:text-right">
                        <?php
                        if ( $regular - $sale_price != 0 ) {
                            echo '<del class="regular_price text-[#828282] text-sm line-through mr-1">';
                            echo number_format( $regular );
                            echo '<span class="vnx_currency">đ</span></del>';
                        }
                        ?>
                        <div class="sale_price text-2xl text-[#EB5757] font-bold">
                            <?php echo number_format( $sale_price ) . '<span class="vnx_currency">đ</span>'; ?>
                        </div>
                        <?php
                        if ( $tool_tip ) {
                            echo '<div class="tool_tip relative">';
                            echo '<span class="quest_icon relative text-xs">?</span>';
                            echo '<div class="absolute hidden tool_tip_text cursor-default"><div class="tooltip_content relative z-[1]">' . esc_html( $tool_tip ) . '</div></div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <a href="#" rel="nofollow" data-domain="<?php echo esc_attr( $domain ); ?>"
                        class="add_to_cart_action whois_vnx_button bg-brand text-white text-center sm:px-20 py-2.5 rounded font-semibold"
                        rel="nofollow"><span class="add_text">Đăng ký ngay</span><span class="added_text hidden">Đã thêm</span></a>
                </div>
                <?php
                break;

            case 'unavailable':
                $whois_page_id = get_field( 'select_whois_page', 'option' ) ?? '';
                $whois_result_url = $whois_page_id ? get_the_permalink( $whois_page_id ) : get_home_url() . '/whois/';
                ?>
                <a href="<?php echo untrailingslashit( $whois_result_url ) . '?domain=' . $domain; ?>"
                    class="whois_view_btn whois_vnx_button py-2.5 rounded border" rel="nofollow">Xem Whois</a>
                <?php
                break;

            case 'special_available':
                ?>
                <div class="vnx_wrap ms:mt-0">
                    <a href="#" rel="nofollow" data-domain="<?php echo esc_attr( $domain ); ?>"
                        class="btn_tawk whois_vnx_button bg-brand text-white text-center sm:px-20 py-2.5 rounded font-semibold"
                        rel="nofollow"><span class="add_text">Liên hệ</span></a>
                </div>
                <?php
                break;

            default:
                # code...
                break;
        }
    }
    protected static function resultLPageUnavailable()
    {
        $whois_page_id = get_field( 'select_whois_page', 'option' ) ?? '';
        $whois_result_url = $whois_page_id ? get_the_permalink( $whois_page_id ) : get_home_url() . '/whois/';
        ?>
        <div class="whois_status items-center md:flex flex-row p-5 border-b">
            <img class="icon_image h-[100px] mr-5"
                src="<?php echo esc_attr( VNX_PLUGIN_URL_CENTER . 'assets/images/illustrations/search-image.svg' ); ?>"
                alt="question">
            <div class="col_right flex flex-col">
                <div class="text-gray-700 text-[28px] font-bold domain_checking mb-2">
                    <?php
                    echo '<span class="domain_first">' . esc_html( self::$domain_name ) . '</span>';
                    echo '<span class="domain_tld text-[#FE9842]">.' . esc_html( self::$tld_checking ) . '</span>';
                    ?>
                </div>
                <div class="flex flex-row items-center domain-info">
                    <img class="status_icon"
                        src="<?php echo esc_attr( VNX_PLUGIN_URL_CENTER . 'assets/images/icons/x-icon.svg' ); ?>"
                        alt="status icon">
                    <div class="status ml-1 text-gray-700">Tên miền đã được đăng ký</div>
                </div>
            </div>
            <a href="<?php echo untrailingslashit( $whois_result_url ) . '?domain=' . self::$domain; ?>"
                class="whois_view_btn whois_vnx_button text-white px-3 py-2.5 rounded-md text-sm border" rel="nofollow">Xem
                Whois</a>
        </div>
        <?php
    }
    public static function showLoadingLPage()
    {
        ?>
        <div id="loading_animate" class="flex items-center hidden">
            <img src="<?php echo VNX_PLUGIN_URL_CENTER; ?>assets/images/icons/search-icon-brand.svg" alt="search icon"
                class="vnx_icon mb-5 md:mb-0 mr-0 md:mr-5">
            <div class="col_right">
                <p class="loading_text text-brand text-xl font-medium mb-6">Đang tra cứu tên miền, vui lòng chờ giây lát..</p>
                <div class="progress_outline relative w-full">
                    <div class="progress-bar absolute top-0 left-0 h-full"></div>
                </div>
            </div>
        </div>
        <?php
    }
}

if ( !function_exists( 'get_tld_pricing_from_api_Center' ) ) {
    function get_tld_pricing_from_api_Center()
    {
        try {
            $url = defined( 'VNX_WHMCS_API_LINK' ) ? VNX_WHMCS_API_LINK : 'https://portal.vietnix.vn/includes/api.php';
            $action = 'GetTLDPricing';
            $data = array( "currencyid" => "2" );
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                http_build_query(
                    array_merge(
                        array(
                            'action'       => $action,
                            'username'     => '51bYFLhdmtgx5E6AdIdPggIWWynCbRqe',
                            'password'     => 'zzEDyVQgciVvwbSAt4CL8P9l1LVn02vn',
                            'responsetype' => 'json',
                        ),
                        $data
                    )
                )
            );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            $response = curl_exec( $ch );
            $return = json_decode( $response );
            return $return;
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
}
if ( !function_exists( 'whois_get_domain_status_Center' ) ) {
    function whois_get_domain_status_Center( $domain )
    {
        try {
            if ( !$domain )
                return;
            $api_link = defined( 'VNX_DOMAIN_CHECKING' ) ? VNX_DOMAIN_CHECKING : 'https://portal.vietnix.vn/domainvn.php?domain=';
            $curl = curl_init();

            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL            => $api_link . $domain,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => '',
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => 'GET',
                )
            );

            $response = curl_exec( $curl );

            curl_close( $curl );
            $return = strtolower( $response );
            return $return;
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
}
if ( !function_exists( 'get_whois_data_by_domain_Center' ) ) {
    function get_whois_data_by_domain_Center( $domain )
    {
        try {
            $api_link = defined( 'VNX_WHOIS_LINK' ) ? VNX_WHOIS_LINK : 'https://guestapi.vietnix.vn/whois';
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL            => $api_link,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => '',
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 0,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => 'POST',
                    CURLOPT_POSTFIELDS     => "domain=$domain",
                    CURLOPT_HTTPHEADER     => array(
                        'Content-Type: application/x-www-form-urlencoded'
                    ),
                )
            );
            $response = curl_exec( $curl );
            curl_close( $curl );
            $return = json_decode( $response );
            return $return;
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
    }
}
if ( !function_exists( 'get_whois_data_Center' ) ) {
    add_action( 'wp_ajax_get_whois_data_center', 'get_whois_data_Center' );
    add_action( 'wp_ajax_nopriv_get_whois_data_center', 'get_whois_data_Center' );
    function get_whois_data_Center()
    {
        // VNX_WHOIS_CHECK_Center::checkAjaxNonce();
        ob_start(); //bắt đầu bộ nhớ đệm
        try {
            $domain = isset( $_GET[ 'domain' ] ) ? $_GET[ 'domain' ] : '';
            if ( !$domain ) {
                echo '1';
            } else {
                VNX_WHOIS_CHECK_Center::setDomain( $domain );
                VNX_WHOIS_CHECK_Center::showResultTable();
            }
        }
        catch ( Exception $e ) {
            echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
        $result = ob_get_clean(); //cho hết bộ nhớ đệm vào biến $result
        wp_send_json_success( $result ); // trả về giá trị dạng json
        wp_die(); // All ajax handlers should die when finished
    }
}
if ( !function_exists( 'whois_get_suggest_item_Center' ) ) {
    add_action( 'wp_ajax_whois_get_suggest_item_center', 'whois_get_suggest_item_Center' );
    add_action( 'wp_ajax_nopriv_whois_get_suggest_item_center', 'whois_get_suggest_item_Center' );
    function whois_get_suggest_item_Center()
    {
        // ob_start(); //bắt đầu bộ nhớ đệm
        try {
            // VNX_WHOIS_CHECK_Center::checkAjaxNonce();
            if ( !isset( $_POST[ 'domain' ] ) ) {
                $result = '1';
            } else {
                $domain = $_POST[ 'domain' ];
                $tld_price_data = isset( $_POST[ 'tld_price_data' ] ) ? $_POST[ 'tld_price_data' ] : '';
                VNX_WHOIS_CHECK_Center::setTldData( $tld_price_data );
                VNX_WHOIS_CHECK_Center::setDomain( $domain );
                $result = VNX_WHOIS_CHECK_Center::whoisGetSuggest();
            }
        }
        catch ( Exception $e ) {
            $result = "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
        // $result = ob_get_clean(); //cho hết bộ nhớ đệm vào biến $result
        wp_send_json_success( $result ); // trả về giá trị dạng json
        wp_die(); // All ajax handlers should die when finished
    }
}

if ( !function_exists( 'get_suggest_item_chunk_Center' ) ) {
    add_action( 'wp_ajax_get_suggest_item_chunk_center', 'get_suggest_item_chunk_Center' );
    add_action( 'wp_ajax_nopriv_get_suggest_item_chunk_center', 'get_suggest_item_chunk_Center' );
    function get_suggest_item_chunk_Center()
    {
        try {
            // VNX_WHOIS_CHECK_Center::checkAjaxNonce();
            if ( !isset( $_GET[ 'domain' ] ) ) {
                $php_response = '1';
            } else {
                $domain = $_GET[ 'domain' ];
                $index = isset( $_GET[ 'index' ] ) ? $_GET[ 'index' ] : 'No index found';
                $chunk = isset( $_GET[ 'chunk' ] ) ? $_GET[ 'chunk' ] : '';
                VNX_WHOIS_CHECK_Center::setDomain( $domain );
                $php_response = VNX_WHOIS_CHECK_Center::getSuggestItemChunk( $chunk );
            }
        }
        catch ( Exception $e ) {
            $php_response = "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
        $result = array(
            'index'        => $index,
            'php_response' => $php_response,
        );
        wp_send_json_success( $result ); // trả về giá trị dạng json
        wp_die(); // All ajax handlers should die when finished
    }
}

if ( !function_exists( 'get_domain_data_whois_ldpage_Center' ) ) {
    add_action( 'wp_ajax_get_domain_data_whois_ldpage_center', 'get_domain_data_whois_ldpage_Center' );
    add_action( 'wp_ajax_nopriv_get_domain_data_whois_ldpage_center', 'get_domain_data_whois_ldpage_Center' );
    function get_domain_data_whois_ldpage_Center()
    {
        try {
            // VNX_WHOIS_CHECK_Center::checkAjaxNonce();
            // $domain = isset( $_GET[ 'domain' ] ) ? $_GET[ 'domain' ] : '';
            if ( !isset( $_POST[ 'domain' ] ) ) {
                $result = '1';
            } else {
                $domain = $_POST[ 'domain' ];
                $tld_price_data = $_POST[ 'tld_price_data' ] ?? '';
                VNX_WHOIS_CHECK_Center::setDomain( $domain );
                $result = VNX_WHOIS_CHECK_Center::showResultLPage( $tld_price_data );
            }
        }
        catch ( Exception $e ) {
            $result = "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
            error_log( $e->getMessage() );
        }
        wp_send_json_success( $result ); // trả về giá trị dạng json
        wp_die(); // All ajax handlers should die when finished
    }
}

if ( !function_exists( 'whois_result_template_include_Center' ) ) {
    try {
        function whois_result_template_include_Center( $template )
        {
            $whois_page = get_field( 'select_whois_page', 'option' );
            if ( !$whois_page )
                $whois_page = 'whois';
            $whois_result_page = get_field( 'whois_result_page', 'option' );
            if ( $whois_result_page ) // Biến global này để bên template xác định được post_id để get content
                $GLOBALS[ 'whois_result_page_id' ] = $whois_result_page;
            if ( is_page( $whois_page ) && isset( $_GET[ 'domain' ] ) && $GLOBALS[ 'whois_result_page_id' ] ) {
                $post_url_rel = wp_make_link_relative( get_permalink( get_the_ID() ) );
                $post_url_rel = trim( $post_url_rel, '/' );
                // Nếu có tham số domain, sử dụng template của trang con
                $theme = wp_get_theme(); // gets the current theme
                if ( 'Bricks' == $theme->name || 'Bricks' == $theme->parent_theme ) {
                  $template = VNX_PLUGIN_PATH_CENTER . 'views/widgets/bricks/domain/template-whois-result.php';
                }
            }
            return $template;
        }
        // Chay song song thi vietnix-plugin render trang ket qua whois (xem vnx_center_companion_mode()).
        if ( !vnx_center_companion_mode() ) {
            add_filter( 'template_include', 'whois_result_template_include_Center', 100, 1 );
        }
    }
    catch ( Exception $e ) {
        echo '<p><b>Something Wrong: </b>' . $e->getMessage() . '</p>';
    }
}
if ( !function_exists( 'add_noindex_to_whois_result_pages_Center' ) ) {
    try {
        function add_noindex_to_whois_result_pages_Center()
        {
            $whois_page = get_field( 'select_whois_page', 'option' );
            if ( !$whois_page )
                $whois_page = 'whois';
            if ( is_page( $whois_page ) && isset( $_GET[ 'domain' ] ) && $GLOBALS[ 'whois_result_page_id' ] ) {
                echo '<meta name="robots" content="noindex, nofollow" />';
            }
        }
        if ( !vnx_center_companion_mode() ) {
            add_action( 'wp_head', 'add_noindex_to_whois_result_pages_Center' );
        }
    }
    catch ( Exception $e ) {
        echo '<p><b>Something Wrong: </b>' . $e->getMessage() . '</p>';
    }
}