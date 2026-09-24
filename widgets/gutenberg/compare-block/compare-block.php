<?php
$heading_left = get_field( 'heading_left' ) ?? '';
$heading_right = get_field( 'heading_right' ) ?? '';
$heading_tag = get_field( 'heading_html_tag' ) ?? 'div';
$cnt_left = get_field( 'content_left' ) ?? null;
$bgr_left = $cnt_left && $cnt_left[ 'bgr' ] ? $cnt_left[ 'bgr' ] : '#fff';
$cnt_right = get_field( 'content_right' ) ?? null;
$bgr_right = $cnt_right && $cnt_right[ 'bgr' ] ? $cnt_right[ 'bgr' ] : '#fff';
if ( !function_exists( 'vnx_compare_block_get_content_Center' ) ) {
    function vnx_compare_block_get_content_Center( $content, string $pos = 'left' )
    {
        try {
            if ( !$content )
                return;
            $items = $content && $content[ 'content' ] ? $content[ 'content' ] : [];
            if ( empty( $items ) )
                return;
            $icon = $content && $content[ 'icon' ] ? $content[ 'icon' ] : '';
            echo '<ul class="list_wrapper">';
            foreach ( $items as $key => $item ) {
                if ( !$item || !$item[ 'cnt' ] )
                    continue;
                echo '<li>';
                if ( $icon ) {
                    echo wp_get_attachment_image( $icon, 'thumbnail', true, [ "class" => 'list-bullet' ] );
                } else {
                    $icon_file = $pos == 'left' ? 'tick.svg' : 'close.svg';
                    echo "<img src ='" . VNX_PLUGIN_URL_CENTER . "assets/images/icons/$icon_file' alt='default icon' class='list-bullet'>";
                }
                echo '<div class="item_content">';
                echo apply_filters( 'acf_the_content', $item[ 'cnt' ] );
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        }
        catch ( Exception $e ) {
            echo '<p><b>Something wrong:</b>' . $e->getMessage() . '</p>';
        }
    }
}
?>
<div class="vnx-compare-block">
    <div class="vnx-row">
        <div class="vnx-col-6 left vnx-col" style="background-color:<?php echo esc_attr( $bgr_left ); ?>">
            <?php
            if ( $heading_left )
                echo "<$heading_tag class='col_title'>" . esc_html( $heading_left ) . "</$heading_tag>";
            vnx_compare_block_get_content_Center( $cnt_left );
            ?>
        </div>
        <div class="vnx-col-6 right vnx-col" style="background-color:<?php echo esc_attr( $bgr_right ); ?>">
            <?php
            if ( $heading_right )
                echo "<$heading_tag class='col_title'>" . esc_html( $heading_right ) . "</$heading_tag>";
            vnx_compare_block_get_content_Center( $cnt_right, "right" );
            ?>
        </div>
    </div>
</div>