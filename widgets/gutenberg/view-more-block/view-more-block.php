<?php
try {
    if ( !function_exists( 'vnx_get_view_more_link_items_Center' ) ) {
        function vnx_get_view_more_link_items_Center( $list, $icon_all = '', $before_url_all = '' )
        {
            if ( !$list )
                return;
            foreach ( $list as $key => $item ) {
                if ( !$item[ 'link' ] )
                    continue;
                $link_title = $item[ 'link' ][ 'title' ] ?? '';
                $url = $item[ 'link' ][ 'url' ] ?? 'javascript:void(0)';
                if ( !$link_title || !$url )
                    continue;
                $icon = $item[ 'icon' ] ?? '';
                if ( !$icon )
                    $icon = $icon_all;
                $before_url = $item[ 'before_url' ] ?? '';
                if ( !$before_url )
                    $before_url = $before_url_all;
                $target = $item[ 'link' ][ 'target' ] ? ' target="_blank"' : '';
                $rel = !$item[ 'link' ][ 'url' ] ? ' rel="nofollow"' : '';
                echo '<li class="item">';
                if ( $icon ) {
                    echo wp_get_attachment_image( $icon, 'medium', true, [ 'class' => 'list-bullet' ] );
                } else {
                    echo "<img src ='" . VNX_PLUGIN_URL_CENTER . "assets/images/icons/link.svg' alt='default icon' class='list-bullet'>";
                }
                echo $before_url ? "<b class='before_url'>" . esc_html( $before_url ) . "</b>" : '';
                echo "<a href='$url'$rel$target>$link_title</a>";
                echo '</li>';
            }
        }
    }
    $icon = get_field( 'icon' ) ?? '';
    $before_url = get_field( 'before_url' ) ?? '';
    $background_color = get_field( 'background_color' ) ?? 'transparent';
    $border_left_color = get_field( 'border_left_color' ) ?? 'transparent';
    $list = get_field( 'list' ) ?? null;
    ?>
    <div class="vnx-view-more-block">
        <ul class="col_wrapper list"
            style="background-color:<?php echo esc_attr( $background_color ); ?>; border-color:<?php echo esc_attr( $border_left_color ); ?>">
            <?php
            vnx_get_view_more_link_items_Center( $list, $icon, $before_url );
            ?>
        </ul>
    </div>
    <?php
}
catch ( Exception $e ) {
    echo '<p><b>Something wrong:</b>' . $e->getMessage() . '</p>';
}
?>