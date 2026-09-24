<?php
if ( !defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

/**
 * Display navigation to next/previous set of posts when applicable.
 */
if ( !function_exists( 'vnx_paging_nav_Center' ) ) {

  function vnx_paging_nav_Center( $wp_query = null )
  {
    if ( !$wp_query ) {
      $wp_query = $GLOBALS[ 'wp_query' ];
    }

    if ( $wp_query->max_num_pages < 2 ) {
      return;
    }

    $paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
    $pagenum_link = html_entity_decode( get_pagenum_link() );
    $query_args = array();
    $url_parts = explode( '?', $pagenum_link );

    if ( isset( $url_parts[ 1 ] ) ) {
      wp_parse_str( $url_parts[ 1 ], $query_args );
    }

    $pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
    $pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

    $format = $GLOBALS[ 'wp_rewrite' ]->using_index_permalinks() && !strpos(
      $pagenum_link,
      'index.php'
    ) ? 'index.php/' : '';
    $format .= $GLOBALS[ 'wp_rewrite' ]->using_permalinks() ? user_trailingslashit(
      'page/%#%',
      'paged'
    ) : '?paged=%#%';

    // Set up paginated links.
    $links = paginate_links( array(
      'base'      => $pagenum_link,
      'format'    => $format,
      'total'     => $wp_query->max_num_pages,
      'current'   => $paged,
      'mid_size'  => 1,
      'add_args'  => array_map( 'urlencode', $query_args ),
      'prev_text' => '',
      'next_text' => '',
    ) );

    if ( $links ) :
      ?>
      <nav class="pagination">
        <?php
        if ( $paged == 1 ) {
          echo '<a href="#" class="prev page-numbers disabled"><i class="fas fa-chevron-left"></i></a>';
        }

        echo $links;

        if ( $paged == $wp_query->max_num_pages ) {
          echo '<a href="#" class="next page-numbers disabled"></a>';
        }
        ?>
      </nav>
      <?php
    endif;
  }
}

if ( !function_exists( 'vnx_paging_nav_custom_icon_Center' ) ) {

  function vnx_paging_nav_custom_icon_Center( $query = null, $prev_icon = '', $next_icon = '' )
  {
    if ( !$query )
      $query = $GLOBALS[ 'wp_query' ];

    if ( $query->max_num_pages < 2 )
      return;

    if ( !$prev_icon )
      $prev_icon = '<i class="fas fa-chevron-left"></i>';
    if ( !$next_icon )
      $next_icon = '<i class="fas fa-chevron-right"></i>';


    $paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
    $pagenum_link = html_entity_decode( get_pagenum_link() );
    $query_args = array();
    $url_parts = explode( '?', $pagenum_link );

    if ( isset( $url_parts[ 1 ] ) ) {
      wp_parse_str( $url_parts[ 1 ], $query_args );
    }

    $pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
    $pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

    $format = $GLOBALS[ 'wp_rewrite' ]->using_index_permalinks() && !strpos(
      $pagenum_link,
      'index.php'
    ) ? 'index.php/' : '';
    $format .= $GLOBALS[ 'wp_rewrite' ]->using_permalinks() ? user_trailingslashit(
      'page/%#%',
      'paged'
    ) : '?paged=%#%';

    // Set up paginated links.
    $links = paginate_links( array(
      'base'      => $pagenum_link,
      'format'    => $format,
      'total'     => $query->max_num_pages,
      'current'   => $paged,
      'mid_size'  => 1,
      'add_args'  => array_map( 'urlencode', $query_args ),
      'prev_text' => $prev_icon,
      'next_text' => $next_icon,
    ) );
    echo $links;
  }
}