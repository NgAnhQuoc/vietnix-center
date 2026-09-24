<?php
namespace VNXCenter\Widgets\Bricks\Coupon;

if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$settings = $data->settings;
if ( !class_exists( 'VNXCenter\Widgets\Bricks\Coupon\Version_1' ) ) :

    class Version_1
    {
        private static $settings;
        public function __construct( $settings )
        {
            self::$settings = $settings;
        }

        public function render()
        {
            $title_tag = isset( self::$settings[ 'title_tag' ] ) ? self::$settings[ 'title_tag' ] : 'div';

            $btn_class = isset( self::$settings[ 'button_class' ] ) ? ' ' . self::$settings[ 'button_class' ] : '';
            $btn_text = isset( self::$settings[ 'button_text' ] ) && self::$settings[ 'button_text' ] ? self::$settings[ 'button_text' ] : 'Copy Code';
            $btn_text_copied = isset( self::$settings[ 'button_clicked_text' ] ) && self::$settings[ 'button_clicked_text' ] ? self::$settings[ 'button_clicked_text' ] : 'Copied Code';

            $image_url = isset( self::$settings[ 'image' ][ 'url' ] ) ? self::$settings[ 'image' ][ 'url' ] : '';

            echo '<div class="vnx_wrapper relative rounded-2xl text-white p-3 pb-0 md:p-4 overflow-y-visible">';

            echo '<div class="vnx_bg md:p-4 md:rounded-lg md:bg-[#FFFFFF33]">';

            echo '<div class="vnx_col_left">';
            if ( isset( self::$settings[ 'title' ] ) && self::$settings[ 'title' ] ) {
                echo '<' . $title_tag . ' class="font-black text-2xl md:text-3xl mb-4 md:mb-2">' . self::$settings[ 'title' ] . '</' . $title_tag . '>';
            }
            if ( isset( self::$settings[ 'description' ] ) && self::$settings[ 'description' ] ) {
                echo '<div class="vnx_description text-base md:text-lg">' . self::$settings[ 'description' ] . '</div>';
            }
            if ( isset( self::$settings[ 'coupon_code' ] ) && self::$settings[ 'coupon_code' ] ) {
                echo '<div class="vnx_coupon_code_wrap bg-white p-1 rounded-xl shadow-md flex md:inline-flex flex-col md:flex-row items-stretch gap-3 mt-4 md:mt-8">';
                echo '<div class="vnx_coupon_code text-brand border border-dashed border-brand text-center px-3 rounded-lg min-h-12 min-w-52 flex items-center justify-center text-2xl font-semibold">' . self::$settings[ 'coupon_code' ] . '</div>';
                echo '<a href="#" rel="nofollow" class="vnx_copy_coupon' . esc_attr( $btn_class ) . ' font-medium min-h-12 min-w-36 flex text-center items-center justify-center text-sm md:text-lg rounded-lg" data-text="' . $btn_text . '" data-clicked="' . $btn_text_copied . '">' . $btn_text . '</a>';
                echo '</div>'; // end .vnx_coupon_code_wrap
                if ( isset( self::$settings[ 'notice' ] ) && self::$settings[ 'notice' ] )
                    echo '<p class="vnx_notice text-xs italic mt-2 mb-0">' . self::$settings[ 'notice' ] . '</p>';
            }
            echo '</div>'; // end .vnx_col_left

            if ( $image_url ) {
                echo '<div class="vnx_img_wrap relative md:absolute mr-auto ml-auto md:mr-4 md:ml-0 md:z-10 md:bottom-[-12px] md:right-0 lg:right-28 w-[305px] md:w-auto max-w-[305px] md:flex md:items-end h-[275px] md:h-[110%] mb-[-10px] md:mb-0 mt-3 md:mt-0">';
                echo '<img src="' . $image_url . '" alt="gift image" class="absolute md:static max-h-full w-auto bottom-[-12px] md:bottom-0 left-1/2 md:left-0 translate-x-[-50%] md:translate-x-0 object-cover object-bottom">';
                echo '</div>';
            }

            echo '</div>'; // end .vnx_bg

            echo '</div>'; // end .vnx_wrapper
        }
    }

endif;
$my_class = [ 'vnx_element', 'w-full' ];
$data->set_attribute( '_root', 'class', $my_class );
echo "<div {$data->render_attributes( '_root' )}>";
if ( class_exists( 'VNXCenter\Widgets\Bricks\Coupon\Version_1' ) ) {
    $element = new Version_1( $settings );
    $element->render();
}
echo '</div>';
?>