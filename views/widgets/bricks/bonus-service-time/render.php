<?php
namespace VNXCenter\Widgets\Bricks\BonusServiceTime;

if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html( __FILE__ ) . '</b></div>';
    return;
}
$settings = $data->settings;
if ( !class_exists( 'VNXCenter\Widgets\Bricks\BonusServiceTime\Version_1' ) ) :

    class Version_1
    {
        private static $bricks;
        private static $settings;
        private static $content_data;
        protected static $item_bg;
        protected static $bonus_title_bg;
        protected static $circle_title;
        protected static $bonus_title;
        public function __construct( $data )
        {
            self::$bricks = $data;
            self::$settings = $data->settings;
        }

        public function render()
        {
            self::$content_data = $this->prepare_data();
            if ( empty( self::$content_data ) )
                return;
            echo $this->tab_title_html();
            echo $this->tab_content_html();
        }

        protected function register_button_html( $index )
        {
            if ( !isset( self::$content_data[ 'buttons' ] ) || empty( self::$content_data[ 'buttons' ] ) )
                return '';
            if ( !isset( self::$content_data[ 'buttons' ][ $index ] ) || empty( self::$content_data[ 'buttons' ][ $index ] ) )
                return '';
            $btn_text = isset( self::$settings[ 'register_text' ] ) ? self::$settings[ 'register_text' ] : 'Xem thêm';
            $button = self::$content_data[ 'buttons' ][ $index ];
            self::$bricks->set_link_attributes( 'button-' . $index, $button );
            $html = '<div class="vnx_bst_tab_content_button flex items-center justify-center text-center mt-6 lg:mt-8">';
            $html .= '<a class="flex items-center justify-center rounded-xl font-medium" ' . self::$bricks->render_attributes( 'button-' . $index ) . '>';
            $html .= '<div>' . esc_html( $btn_text ) . '</div>';
            $html .= '</a>';
            $html .= '</div>'; // .vnx_bst_tab_content_button
            return $html;
        }

        protected function content_item_html( $item )
        {
            $html = '';
            if ( empty( $item ) )
                return '';

            $circle = isset( $item[ 'circle' ] ) ? $item[ 'circle' ] : '';
            if ( !$circle )
                return '';

            $bonus_time = isset( $item[ 'bonus_time' ] ) ? $item[ 'bonus_time' ] : '';
            if ( !$bonus_time )
                return '';

            $html .= '<div class="vnx_bst_tab_content_item relative flex flex-col pt-6 pb-3 px-3 rounded-[4px] overflow-hidden gap-y-8 mx-2 lg:mx-3">';
            $html .= self::$item_bg;

            $html .= '<div class="vnx_bst_top rounded-[4px] text-center z-10 relative overflow-hidden">';
            $html .= '<div class="vnx_circle_top_title h-[30px] lg:h-10 text-white text-xs lg:text-base font-extrabold flex items-center justify-center"><div>' . esc_html( self::$circle_title ) . '</div></div>';
            $html .= '<div class="vnx_circle_top_content h-11 lg:h-14 pt-1 pb-2 lg:pb-3 text-xl leading-8 lg:text-[28px] lg:leading-10 font-bold border border-dashed border-brand border-t-0"><div>' . esc_html( $circle ) . '</div></div>';
            $html .= '</div>'; // .vnx_bst_top

            $html .= '<div class="vnx_bst_bottom relative z-10">';
            $html .= '<div class="vnx_circle_bottom_title h-[26px] lg:h-[30px] w-[161px] lg:w-[205px] justify-self-center text-white text-sm lg:text-base font-black flex items-center justify-center relative">';
            $html .= self::$bonus_title_bg;
            $html .= '<div class="relative z-10">' . esc_html( self::$bonus_title ) . '</div>';
            $html .= '</div>';
            $html .= '<div class="vnx_circle_bonus_time pt-5 lg:pt-8 pb-2 lg:pb-3 px-2 rounded-[4px] text-xl lg:text-[28px] leading-8 lg:leading-10 font-bold flex items-center justify-center text-center -mt-[14px] lg:-mt-4"><div>' . esc_html( $bonus_time ) . '</div></div>';
            $html .= '</div>'; // .vnx_bst_bottom

            $html .= '</div>'; // .vnx_bst_tab_content_item
            return $html;
        }

        protected function tab_content_html()
        {
            if ( !isset( self::$content_data[ 'items' ] ) || empty( self::$content_data[ 'items' ] ) )
                return '';

            $get_item_bg = isset( self::$settings[ 'item_bg' ] ) ? self::$settings[ 'item_bg' ] : [];
            self::$item_bg = '';
            if ( !empty( $get_item_bg ) && isset( $get_item_bg[ 'id' ] ) ) {
                $attachment_id = $get_item_bg[ 'id' ];
                $size = isset( $get_item_bg[ 'size' ] ) ? $get_item_bg[ 'size' ] : 'full';
                self::$item_bg = wp_get_attachment_image( $attachment_id, $size, false, [ 'class' => 'vnx_bst_item_bg absolute w-full h-full z-0 top-0 left-0 object-cover' ] );
            }

            $get_title_bg = isset( self::$settings[ 'title_bg' ] ) ? self::$settings[ 'title_bg' ] : [];
            self::$bonus_title_bg = '';
            if ( !empty( $get_title_bg ) && isset( $get_title_bg[ 'id' ] ) ) {
                $attachment_id = $get_title_bg[ 'id' ];
                $size = isset( $get_title_bg[ 'size' ] ) ? $get_title_bg[ 'size' ] : 'full';
                self::$bonus_title_bg = wp_get_attachment_image( $attachment_id, $size, false, [ 'class' => 'vnx_bst_title_bg absolute w-full h-full z-0 top-0 left-0 object-fill' ] );
            }

            self::$circle_title = isset( self::$settings[ 'circle_title' ] ) ? self::$settings[ 'circle_title' ] : '';
            self::$bonus_title = isset( self::$settings[ 'bonus_title' ] ) ? self::$settings[ 'bonus_title' ] : '';


            $html = '';
            foreach ( self::$content_data[ 'items' ] as $key => $service ) {
                if ( empty( $service ) )
                    continue;
                $active = ( $key == 0 ) ? ' active' : '';

                $html .= '<div class="vnx_bst_tab_content' . esc_attr( $active ) . ' vnx_bst_' . esc_attr( $key ) . ' flex flex-col items-stretch">';
                $html .= '<div class="vnx_bts_tab_content_wrapper flex gap-y-6 -mx-2 lg:-mx-3">';
                foreach ( $service as $item_key => $item ) {
                    $html .= $this->content_item_html( $item );
                }
                $html .= '</div>'; // .vnx_bst_tab_content_wrapper
                $html .= $this->register_button_html( $key );
                $html .= '</div>'; // .vnx_bst_tab_content
            }
            return $html;
        }

        protected function tab_title_html()
        {
            if ( !isset( self::$content_data[ 'titles' ] ) || empty( self::$content_data[ 'titles' ] ) )
                return '';

            if ( sizeof( self::$content_data[ 'titles' ] ) <= 1 )
                return '';

            $get_tab_title_bg = isset( self::$settings[ 'tab_title_bg' ] ) ? self::$settings[ 'tab_title_bg' ] : [];
            $tab_title_bg = '';
            if ( !empty( $get_tab_title_bg ) && isset( $get_tab_title_bg[ 'id' ] ) ) {
                $attachment_id = $get_tab_title_bg[ 'id' ];
                $size = isset( $get_tab_title_bg[ 'size' ] ) ? $get_tab_title_bg[ 'size' ] : 'full';
                $tab_title_bg = wp_get_attachment_image( $attachment_id, $size, false, [ 'class' => 'vnx_bst_item_bg absolute w-full h-full z-0 top-0 left-0 object-cover' ] );
            }

            $html = '<div class="vnx_bst_tab_title">';
            $html .= '<div class="vnx_splide overflow-x-auto gap-x-4 lg:gap-x-6 flex items-center justify-center">';
            foreach ( self::$content_data[ 'titles' ] as $key => $service ) {
                $active = ( $key == 0 ) ? ' active' : '';
                $html .= '<a class="vnx_bst_tab_title_item' . esc_attr( $active ) . ' px-4 lg:px-5 flex items-center justify-center text-center rounded-[4px] border font-bold text-sm lg:text-lg overflow-hidden relative" data-target="vnx_bst_' . esc_attr( $key ) . '">';
                $html .= $tab_title_bg;
                $html .= '<div class="relative z-1">' . esc_html( $service ) . '</div>';
                $html .= '</a>';
            }
            $html .= '</div>'; // .vnx_splide
            $html .= '</div>'; // .vnx_bst_tab_title
            return $html;
        }

        protected function prepare_data()
        {
            $content = isset( self::$settings[ 'content' ] ) ? self::$settings[ 'content' ] : [];
            if ( empty( $content ) )
                return [];
            $titles = [];
            $items = [];
            $buttons = [];
            foreach ( $content as $key => $value ) {
                if ( !isset( $value[ 'service' ] ) || empty( $value[ 'service' ] ) )
                    continue;

                if ( !isset( $value[ 'items' ] ) || empty( $value[ 'items' ] ) )
                    continue;

                $titles[ $key ] = $value[ 'service' ];
                $buttons[ $key ] = isset( $value[ 'link' ] ) ? $value[ 'link' ] : [];

                foreach ( $value[ 'items' ] as $item_key => $item_value ) {
                    $items[ $key ][ $item_key ][ 'circle' ] = isset( $item_value[ 'circle' ] ) ? $item_value[ 'circle' ] : '';

                    $items[ $key ][ $item_key ][ 'bonus_time' ] = isset( $item_value[ 'bonus_time' ] ) ? $item_value[ 'bonus_time' ] : '';
                }
            }
            return [ 
                'titles'  => $titles,
                'items'   => $items,
                'buttons' => $buttons,
            ];
        }
    }

endif;
$my_class = [ 'vnx_element', 'w-full' ];
$data->set_attribute( '_root', 'class', $my_class );
echo "<div {$data->render_attributes( '_root' )}>";
if ( class_exists( 'VNXCenter\Widgets\Bricks\BonusServiceTime\Version_1' ) ) {
    $element = new Version_1( $data );
    $element->render();
}
echo '</div>';
?>