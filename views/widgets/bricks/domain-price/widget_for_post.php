<?php
namespace VNXCenter\Widgets\Bricks\Domain_Price;
use \Bricks\Element;
if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();


if ( $get_csv[ 'status' ] == 'error' ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>' . $get_csv[ 'message' ] . '</div>';
    return;
}

if ( $get_csv[ 'status' ] == 'success' )
    $csv_data = isset( $get_csv[ 'data' ] ) ? $get_csv[ 'data' ] : array();
if ( empty( $csv_data ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html( __FILE__ ) . '</b></div>';
    return;
}
if ( !class_exists( 'VNXCenter\Widgets\Bricks\Domain_Price\Widget_For_Post' ) ) :
    class Widget_For_Post
    {
        protected static $settings;
        protected static $csv_data;
        protected static $domain_name;
        protected static $tooltip;
        protected static $status;
        protected static $gift;
        protected static $registration_price_sum;
        protected static $registration_price_sale;
        protected static $registration_fee;
        protected static $maintenance_fee;
        protected static $first_year_admin_service_fee;
        protected static $vat_fee;
        protected static $renewal_sum;
        protected static $maintenance_fee_renewal;
        protected static $next_year_admin_service_fee;
        protected static $vat_renewal_fee;
        protected static $transfer_fee;
        protected static $domain_type;

        private static $row_class = 'vnx_row md:flex md:gap-x-3 mb-2 md:mb-0';
        private static $col_class = 'md:w-[calc(50%-6px)] flex';
        private static $col_left_class = '';
        private static $col_right_class = '';
        private static $register_cell_class = 'vnx_register_cel flex pl-4 pr-3 py-2 w-1/2 md:w-48 shrink-0 gap-x-3 flex-wrap md:flex-nowrap rounded-bl-lg md:rounded-none items-start md:items-center';
        private static $renewal_cell_class = 'vnx_renew_cell flex flex-col justify-start md:justify-center pl-4 pr-3 py-2 grow rounded-br-lg md:rounded-none border-l border-solid md:border-0';
        private static $tooltip_row_class = 'flex pl-3 pr-6 justify-between items-center text-sm';

        public function __construct( $settings, $csv_data )
        {
            self::$settings = $settings;
            self::$csv_data = $csv_data;
            $this->set_columns_index();
            self::$col_left_class = 'vnx_col_left rounded-t-lg md:rounded-none items-center pl-4 md:pl-5 min-h-12 border-b border-solid md:border-0 ' . self::$col_class;
            self::$col_right_class = 'vnx_col_right min-h-12 md:gap-x-3 ' . self::$col_class;

        }

        protected function set_columns_index()
        {
            self::$domain_name = 0;
            self::$tooltip = 1;
            self::$status = 2;
            self::$gift = 3;
            self::$registration_price_sum = 4;
            self::$registration_price_sale = 5;
            self::$registration_fee = 6;
            self::$maintenance_fee = 7;
            self::$first_year_admin_service_fee = 8;
            self::$vat_fee = 9;
            self::$renewal_sum = 10;
            self::$maintenance_fee_renewal = 11;
            self::$next_year_admin_service_fee = 12;
            self::$vat_renewal_fee = 13;
            self::$transfer_fee = 14;
            self::$domain_type = 15;
        }

        protected function get_domain_name( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$domain_name ] ) )
                $result = $row_data[ self::$domain_name ];
            return $result;
        }

        protected function get_tooltip( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$tooltip ] ) )
                $result = $row_data[ self::$tooltip ];
            return $result;
        }

        protected function get_status( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$status ] ) )
                $result = $row_data[ self::$status ];
            return $result;
        }

        protected function get_gift( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$gift ] ) )
                $result = $row_data[ self::$gift ];
            return $result;
        }

        protected function get_registration_price_sum( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$registration_price_sum ] ) )
                $result = $row_data[ self::$registration_price_sum ];
            return $result;
        }

        protected function get_registration_price_sale( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$registration_price_sale ] ) )
                $result = $row_data[ self::$registration_price_sale ];
            return $result;
        }

        protected function get_registration_fee( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$registration_fee ] ) )
                $result = $row_data[ self::$registration_fee ];
            return $result;
        }

        protected function get_maintenance_fee( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$maintenance_fee ] ) )
                $result = $row_data[ self::$maintenance_fee ];
            return $result;
        }

        protected function get_first_year_admin_service_fee( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$first_year_admin_service_fee ] ) )
                $result = $row_data[ self::$first_year_admin_service_fee ];
            return $result;
        }

        protected function get_vat_fee( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$vat_fee ] ) )
                $result = $row_data[ self::$vat_fee ];
            return $result;
        }

        protected function get_renewal_sum( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$renewal_sum ] ) )
                $result = $row_data[ self::$renewal_sum ];
            return $result;
        }

        protected function get_maintenance_fee_renewal( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$maintenance_fee_renewal ] ) )
                $result = $row_data[ self::$maintenance_fee_renewal ];
            return $result;
        }

        protected function get_next_year_admin_service_fee( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$next_year_admin_service_fee ] ) )
                $result = $row_data[ self::$next_year_admin_service_fee ];
            return $result;
        }

        // get vat renewal fee
        protected function get_vat_renew_fee( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$vat_renewal_fee ] ) )
                $result = $row_data[ self::$vat_renewal_fee ];
            return $result;
        }

        protected function get_transfer_fee( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$transfer_fee ] ) )
                $result = $row_data[ self::$transfer_fee ];
            return $result;
        }

        protected function get_domain_type( $row_data )
        {
            $result = '';
            if ( isset( $row_data[ self::$domain_type ] ) )
                $result = $row_data[ self::$domain_type ];
            return $result;
        }

        protected function table_content( $domain_type )
        {
            $class = 'vnx_domain_' . $domain_type;
            $content_title = '';
            $icon = '';
            if ( $domain_type == 'vn' ) {
                $class .= ' active';
                $icon = VNX_PLUGIN_URL_CENTER . 'assets/images/icons/domain-vn-icon.png';
                $content_title = 'TÊN MIỀN VIỆT NAM';
            }
            if ( $domain_type == 'qt' ) {
                $icon = VNX_PLUGIN_URL_CENTER . 'assets/images/icons/domain-global-icon.png';
                $content_title = 'TÊN MIỀN QUỐC TẾ';
            }
            $content_header = '<img src="' . $icon . '" alt="' . $content_title . '">' . '<span class="font-bold pl-5">' . $content_title . '</span>';
            echo '<div class="vnx_table_content md:overflow-y-auto md:overflow-x-hidden md:max-h-[528px] ' . $class . '">';
            echo '<div class="vnx_content_header hidden md:flex ' . self::$row_class . '">';

            echo '<div class="' . self::$col_left_class . '">' . $content_header . '</div>'; // vnx_col_left

            echo '<div class="' . self::$col_right_class . '">'; // vnx_col_right
            echo '<div class="' . self::$register_cell_class . ' font-bold bg-brand text-white">Đăng ký mới</div>';
            echo '<div class="' . self::$renewal_cell_class . ' font-bold bg-brand text-white">Gia hạn</div>';
            echo '</div>'; // end vnx_col_right

            echo '</div>'; // end vnx_content_header

            $index = 0;
            foreach ( self::$csv_data as $row_data ) {
                $row_domain_type = $this->get_domain_type( $row_data );
                // lowercase $domain_type
                $row_domain_type = strtolower( $row_domain_type );
                if ( $row_domain_type == $domain_type ) {
                    $this->add_row( $row_data, $index );
                    $index++;
                }
            }
            echo '<div class="vnx_table_collapse flex md:hidden justify-center font-medium mt-2 hidden"><a href="#" class="vnx_toggle_table text-sm" rel="nofollow">Thu Gọn <img class="inline align-baseline" src="' . VNX_PLUGIN_URL_CENTER . 'assets/images/icons/arrow-up.svg" alt="arrow up icon"></a></div>';
            echo '</div>'; // end vnx_table_content
        }

        protected function add_row( $data, $index = 0 )
        {
            $is_odd = $index % 2 == 0 ? false : true;
            $odd_class = $is_odd ? ' bg-white md:bg-[#F9F9F9]' : ' bg-white';
            $mobile_hidden_class = $index > 5 ? ' vnx_mobile_hidden hidden' : '';
            echo '<div class="' . self::$row_class . $mobile_hidden_class . '">';

            echo '<div class="' . self::$col_left_class . $odd_class . ' gap-3">'; // vnx_col_left
            $this->col_left_content( $data );
            echo '</div>'; // end vnx_col_left

            echo '<div class="' . self::$col_right_class . '">'; // vnx_col_right
            echo '<div class="' . self::$register_cell_class . $odd_class . '">';
            $this->register_cell_content( $data );
            echo '</div>'; // end vnx_register_cell
            echo '<div class="' . self::$renewal_cell_class . $odd_class . '">';
            $this->renewal_cell_content( $data );
            echo '</div>'; // end vnx_renewal_cell
            echo '</div>'; // end vnx_col_right

            echo '</div>'; // end row
            if ( $index != 5 )
                return;
            echo '<div class="vnx_table_expand flex md:hidden justify-center font-medium mt-2"><a href="#" class="vnx_toggle_table text-sm" rel="nofollow">Xem thêm <img class="inline align-baseline" src="' . VNX_PLUGIN_URL_CENTER . 'assets/images/icons/arrow-down.svg" alt="arrow down icon"></a></div>';
        }

        protected function renewal_cell_content( $data )
        {
            $renewal_sum = $this->get_renewal_sum( $data );
            $maintenance_fee_renewal = $this->get_maintenance_fee_renewal( $data );
            $next_year_admin_service_fee = $this->get_next_year_admin_service_fee( $data );
            $transfer_fee = $this->get_transfer_fee( $data );
            $vat_renewal_fee = $this->get_vat_renew_fee( $data );

            echo '<p class="mb-0 text-xs w-full md:hidden font-bold pt-0">Gia hạn</p>';
            echo '<div class="flex items-center flex-wrap md:flex-nowrap">';
            echo '<span class="vnx_renewal_price text-[#525666] font-bold">' . $renewal_sum . '</span>';

            if ( $maintenance_fee_renewal || $next_year_admin_service_fee || $vat_renewal_fee ) {
                $tooltip_content = '<div class="flex flex-col gap-y-3 pt-6 pb-3 px-3">';
                $tooltip_content .= '<div class="' . self::$tooltip_row_class . '"><span>Phí duy trì/năm</span><span>' . $maintenance_fee_renewal . 'đ</span></div>';
                $tooltip_content .= '<div class="' . self::$tooltip_row_class . '"><span>Dịch vụ quản trị tên </br>miền năm tiếp theo</span><span>' . $next_year_admin_service_fee . 'đ</span></div>';
                $tooltip_content .= '<div class="' . self::$tooltip_row_class . '"><span>VAT(10%)<span class="text-sx block">(Thuế dịch vụ)</span></span><span>' . $vat_renewal_fee . 'đ</span></div>';
                $tooltip_content .= '<div class="' . self::$tooltip_row_class . ' bg-[#38A7FF1A] font-bold min-h-10 px-2 rounded-lg"><span class="text-base">Tổng</span><span class="text-brand text-base">' . $renewal_sum . 'đ</span></div>';
                $tooltip_content .= '</div>';
                $this->tooltip_content( $tooltip_content, 'bg-white w-[304px] price_tooltip', 'ml-auto' );
            }
            echo '</div>';
        }

        protected function register_cell_content( $data )
        {
            $registration_price_sum = $this->get_registration_price_sum( $data );
            $registration_price_sale = $this->get_registration_price_sale( $data );
            $registration_fee = $this->get_registration_fee( $data );
            $maintenance_fee = $this->get_maintenance_fee( $data );
            $first_year_admin_service_fee = $this->get_first_year_admin_service_fee( $data );
            $vat_fee = $this->get_vat_fee( $data );


            $regular_price = $registration_price_sale ? $registration_price_sale : $registration_price_sum;
            echo '<p class="mb-0 text-xs w-full md:hidden font-bold pt-0">Đăng ký mới</p>';
            echo '<div class="vnx_price_wrap flex items-start md:items-center gap-x-3 flex-wrap flex-col md:flex-row">';
            echo '<span class="vnx_regular_price text-[#FF9038] font-bold">' . $regular_price . '</span>';
            if ( $registration_price_sale )
                echo '<span class="vnx_sale_price text-[#BABBC2] line-through text-sm">' . $registration_price_sum . '</span>';
            echo '</div>'; // end vnx_price_wrap

            if ( !$registration_fee && !$maintenance_fee && !$first_year_admin_service_fee && !$vat_fee )
                return;
            $tooltip_content = '<div class="flex flex-col gap-y-3 pt-6 pb-3 px-3">';
            $tooltip_content .= '<div class="' . self::$tooltip_row_class . '"><span>Lệ phí đăng ký</span><span>' . $registration_fee . 'đ</span></div>';
            $tooltip_content .= '<div class="' . self::$tooltip_row_class . '"><span>Phí duy trì/năm</span><span>' . $maintenance_fee . 'đ</span></div>';
            $tooltip_content .= '<div class="' . self::$tooltip_row_class . '"><span>Dịch vụ quản trị tên </br>miền năm đầu tiên</span><span>' . $first_year_admin_service_fee . 'đ</span></div>';
            $tooltip_content .= '<div class="' . self::$tooltip_row_class . '"><span>VAT(10%)<span class="text-sx block">(Thuế dịch vụ)</span></span><span>' . $vat_fee . 'đ</span></div>';
            $tooltip_content .= '<div class="' . self::$tooltip_row_class . ' bg-[#38A7FF1A] font-bold min-h-10 px-2 rounded-lg"><span class="text-base">Tổng</span><span class="text-brand text-base">' . $registration_price_sum . 'đ</span></div>';
            $tooltip_content .= '</div>';
            $this->tooltip_content( $tooltip_content, 'bg-white w-[304px] price_tooltip', 'ml-auto mt-1 md:mt-0' );
        }

        protected function col_left_content( $data )
        {
            $domain_name = $this->get_domain_name( $data );
            $tooltip = $this->get_tooltip( $data );
            $status = $this->get_status( $data );
            $gift = $this->get_gift( $data );

            echo '<span class="vnx_domain_name">' . $domain_name . '</span>';
            $this->tooltip_content( $tooltip, 'w-40 py-1 px-2 bg-brand text-white text-xs domain_tooltip' );
            $this->status_label( $status );
            $this->gift_label( $gift );
        }

        protected function gift_label( $gift )
        {
            if ( !$gift || empty( $gift ) )
                return;
            $gift_icon_url = VNX_PLUGIN_URL_CENTER . 'assets/images/icons/domain-price-v3-gift-icon.png';
            $flash_icon_url = VNX_PLUGIN_URL_CENTER . 'assets/images/icons/domain-price-v3-gift-flash.png';
            $gift_icon = '<img class="absolute z-[1] top-[-1px] right-[calc(100%-15px)]" src="' . $gift_icon_url . '" alt="gift icon">';
            $flash_icon = '<img class="absolute z-[1] right-[-2px] top-[-2px]" src="' . $flash_icon_url . '" alt="flash icon">';
            ?>
            <div class="vnx_gif_icon relative ml-auto mr-[6px]">
                <div class="text-[9px] pr-2 pl-[18px] h-5 flex items-center text-white font-black italic relative z-[1] leading-none rounded-full border border-solid border-[#FEDF7A]"
                    style="background:linear-gradient(45deg, #F3B847, #F49846);">
                    <?= $gift ?>
                </div>
                <?php echo $gift_icon; ?>
                <?php echo $flash_icon; ?>
            </div>
            <?php
        }

        protected function status_label( $status )
        {
            if ( !$status || empty( $status ) )
                return;
            $data = explode( ',', $status );
            // remove space before and after string in array
            $data = array_map( 'trim', $data );
            if ( empty( $data ) )
                return;
            $text = isset( $data[ 0 ] ) ? $data[ 0 ] : '';
            $background = isset( $data[ 1 ] ) ? $data[ 1 ] : '';
            $color = isset( $data[ 2 ] ) ? $data[ 2 ] : '';
            // echo label use $text, $color, $background and span
            echo '<span class="vnx_label text-xs px-2 py-[2px] rounded-full border border-solid" style="color:' . $color . ';background:' . $background . ';border-color:' . $color . '">' . $text . '</span>';
        }

        protected function tooltip_content( $content, $content_class = '', $wrap_class = '' )
        {
            if ( !$content || empty( $content ) )
                return;
            if ( $content_class )
                $content_class = ' ' . $content_class;
            if ( $wrap_class )
                $wrap_class = ' ' . $wrap_class;
            $tooltip_icon = isset( self::$settings[ 'tooltip_icon' ] ) && self::$settings[ 'tooltip_icon' ][ 'library' ] ? self::$settings[ 'tooltip_icon' ] : '';
            echo '<div class="vnx_tooltip relative leading-none cursor-pointer' . $wrap_class . '">';
            echo $tooltip_icon ? Element::render_icon( $tooltip_icon, [ 'vnx_tooltip_icon static z-0 w-4 h-4 flex items-center justify-center text-base' ] ) : '<img src="' . VNX_PLUGIN_URL_CENTER . 'assets/images/icons/circle-quest.svg' . '" alt="tooltip icon" class="vnx_tooltip_icon static z-0 w-4 h-4 flex items-center justify-center">';
            echo '<div class="vnx_tooltip_content absolute z-10 shadow-2xl rounded-md' . $content_class . '">' . $content . '</div>';
            echo '</div>'; // end vnx_tooltip

        }

        public function render()
        {
            ?>
            <div class="vnx_wrapper bg-[#EFF4F8] rounded-lg p-4 md:p-3 md:max-w-[740px] mx-auto text-base md:text-lg">
                <div class="vnx_tab_header_wrap relative z-10">
                    <div
                        class="vnx_mobile_tab_header h-[60px] px-4 bg-white rounded-lg flex md:hidden flex-nowrap items-center justify-between mb-2">
                        <span>Bảng giá tên miền Việt Nam</span>
                        <img src="<?= VNX_PLUGIN_URL_CENTER; ?>assets/images/icons/arrow-down.svg" alt="arrow down icon">
                    </div>
                    <div
                        class="vnx_table_header absolute top-[100%] left-0 mt-[-8px] md:mt-0 shadow-lg md:shadow-none rounded-b-lg md:relative px-4 py-2 md:p-0 w-full bg-slate-50 md:bg-transparent hidden md:flex md:items-stretch md:mb-5">
                        <a href="#" class="vnx_tab_title flex items-center h-[42px] md:w-1/2 active" data-target="vnx_domain_vn"
                            rel="nofollow">Bảng giá tên miền
                            Việt Nam</a>
                        <a href="#" class="vnx_tab_title flex items-center h-[42px] md:w-1/2 md:pl-6 mt-1 md:mt-0"
                            data-target="vnx_domain_qt" rel="nofollow">Bảng giá tên miền
                            quốc tế</a>
                    </div>
                </div>
                <?php
                $this->table_content( 'vn' );
                $this->table_content( 'qt' );
                ?>
            </div>
            <?php
        }

    }
endif;

$widget = new Widget_For_Post( $settings, $csv_data );
$widget->render();

?>