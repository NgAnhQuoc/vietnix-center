<?php
namespace VNXCenter\Gutenberge\Block;
if ( !defined( 'ABSPATH' ) )
    die( 'Direct access forbidden.' );
class Blockquote
{
    private $class;
    private $icon_class = 'vnx-blockquote-icon';
    public function render()
    {
        try {
            if ( !function_exists( 'get_field' ) )
                throw new \Exception( 'ACF plugin is required' );
            $block_type = get_field( 'block_type' ) ?? '';
            if ( !$block_type )
                throw new \Exception( 'Block type is required' );
            if ( $block_type == 'basic' ) {
                $content = $this->basic_block();
            } elseif ( $block_type == 'urls' ) {
                $content = $this->url_list_block();
                $this->class = 'list_block';
            } else {
                throw new \Exception( 'Block type is invalid' );
            }
            ?>
            <div class="vnx-blockquote-block <?= $this->class; ?>">
                <div class="vnx_block_wrapper">
                    <?php
                    echo $content;
                    ?>
                </div>
            </div>
            <?php
        }
        catch ( \Exception $e ) {
            echo '<p><b>Something wrong: </b>' . $e->getMessage() . '</p>';
        }
    }

    protected function url_list_block()
    {
        try {
            $group = get_field( 'url_list_block' ) ?? [];
            if ( !$group || empty( $group ) )
                return 'No url_list_block found';
            $html = '';
            $links = isset( $group[ 'links' ] ) ? $group[ 'links' ] : [];
            if ( empty( $links ) )
                return 'No links found';
            $title = isset( $group[ 'title' ] ) ? $group[ 'title' ] : '';
            $icon = isset( $group[ 'icon' ] ) ? $group[ 'icon' ] : '';
            $html .= $title ? '<p class="blockquote_title">' . $title . '</p>' : '';
            $html .= '<ul class="p-0 m-0">';
            foreach ( $links as $link ) {
                $html .= $this->link_item( $link, $icon );
            }
            $html .= '</ul>';

            return $html;
        }
        catch ( \Exception $e ) {
            return '<p><b>Something wrong: </b>' . $e->getMessage() . '</p>';
        }
    }

    protected function link_item( $link, $icon )
    {
        try {
            $html = '';
            $html .= '<li class="item">';
            if ( !isset( $link[ 'link' ][ 'url' ] ) || !$link[ 'link' ][ 'url' ] )
                return 'URL is required';
            $url = $link[ 'link' ][ 'url' ];
            $title = isset( $link[ 'link' ][ 'title' ] ) && $link[ 'link' ][ 'title' ] ? $link[ 'link' ][ 'title' ] : 'Xem thêm';
            $title_attr = '';
            if ( $title )
                $title_attr = ' title="' . esc_html( $title ) . '"';
            $target = isset( $link[ 'link' ][ 'target' ] ) ? $link[ 'link' ][ 'target' ] : '';
            if ( $target )
                $target = ' target="_blank"';
            $rels = isset( $link[ 'rel' ] ) ? $link[ 'rel' ] : [];
            $rel_text = '';
            if ( !empty( $rels ) ) {
                $rel_text .= ' rel="';
                $rel_text .= implode( ' ', $rels );
                $rel_text .= '"';
            }
            $html .= $this->get_icon_html( $icon, VNX_PLUGIN_URL_CENTER . 'assets/images/icons/link-icon-vnx-blockquote.png' );
            $html .= '<a href="' . $url . '"' . $title_attr . $target . $rel_text . '>';
            $html .= '<span>' . esc_html( $title ) . '</span>';
            $html .= '</a>';
            $html .= '</li>';
            return $html;
        }
        catch ( \Exception $e ) {
            return '<li class="item"><p><b>Something wrong: </b>' . $e->getMessage() . '</p></li>';
        }
    }

    protected function basic_block()
    {
        try {
            $group = get_field( 'basic_block' ) ?? [];
            if ( !$group || empty( $group ) )
                return 'No basic_block found';
            $html = '';
            if ( !isset( $group[ 'basic_block_type' ] ) || !$group[ 'basic_block_type' ] )
                return 'Block type is required';
            $this->class = 'basic_block';
            $type = $group[ 'basic_block_type' ];
            if ( $type == 'info' && isset( $group[ 'info_group' ] ) ) {
                $this->class .= ' info_block';
                $html .= $this->basic_block_type_1( $group[ 'info_group' ] );
            }
            if ( $type == 'quote' && isset( $group[ 'quote_group' ] ) ) {
                $this->class .= ' quote_block';
                $html .= $this->basic_block_type_1( $group[ 'quote_group' ] );
            }
            if ( $type == 'example' && isset( $group[ 'example_group' ] ) ) {
                $this->class .= ' example_block';
                $html .= $this->basic_block_type_1( $group[ 'example_group' ] );
            }
            if ( $type == 'error' && isset( $group[ 'error_group' ] ) ) {
                $this->class .= ' error_block';
                $html .= $this->basic_block_type_1( $group[ 'error_group' ] );
            }
            if ( $type == 'price' && isset( $group[ 'price_group' ] ) ) {
                $this->class .= ' price_block';
                $html .= $this->basic_block_type_1( $group[ 'price_group' ] );
            }
            if ( $type == 'warning' && isset( $group[ 'warning_group' ] ) ) {
                $this->class .= ' warning_block';
                $html .= $this->warning_block_type( $group[ 'warning_group' ] );
            }
            return $html;
        }
        catch ( \Exception $e ) {
            return '<p><b>Something wrong: </b>' . $e->getMessage() . '</p>';
        }
    }

    /**
     * Use for info, quote, example, error, price blocks
     * @param array $data block group acf data
     * @return string html of block
     */
    protected function basic_block_type_1( $data )
    {
        $html = '';
        $icon = isset( $data[ 'icon' ] ) ? $data[ 'icon' ] : '';
        $content = isset( $data[ 'content' ] ) ? $data[ 'content' ] : '';
        $html .= '<div class="vnx_flex">';

        if ( $icon )
            $html .= $this->get_icon_html( $icon );

        if ( $content )
            $html .= $this->get_content_html( $content );

        $html .= '</div>';
        return $html;
    }

    /**
     * Use for warning block
     * @param array $data block group acf data
     * @return string html of block
     */
    protected function warning_block_type( $data )
    {
        $html = '';
        $content = isset( $data[ 'content' ] ) ? $data[ 'content' ] : '';
        $html .= '<p class="blockquote_title vnx_flex">';
        $html .= $this->get_icon_html( '', VNX_PLUGIN_URL_CENTER . 'assets/images/icons/warning-icon-vnx-blockquote.png' );
        $html .= '<span>Lưu ý</span>';
        $html . '</p>';

        if ( $content )
            $html .= $this->get_content_html( $content );

        return $html;
    }

    protected function get_icon_html( $icon, $default = '' )
    {
        if ( $icon ) {
            $icon_url = wp_get_attachment_image_url( $icon, 'thumbnail' );
        } elseif ( $default ) {
            $icon_url = $default;
        } else {
            $icon_url = '';
        }

        $html = '';
        if ( $icon_url && $icon_url != '' )
            $html = '<img src="' . $icon_url . '" alt="icon" class="' . $this->icon_class . '">';

        return $html;
    }

    protected function get_content_html( $content )
    {
        return '<div class="vnx-blockquote-content">' . $content . '</div>';
    }
}
?>