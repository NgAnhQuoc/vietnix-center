<?php
if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$settings = $data->settings;
$placeholder = isset( $settings[ 'placeholder' ] ) ? $settings[ 'placeholder' ] : '';
$button_text = isset( $settings[ 'button_text' ] ) ? $settings[ 'button_text' ] : '';
$button_icon = isset( $settings[ 'button_icon' ] ) ? $settings[ 'button_icon' ] : array();
$clear_icon = isset( $settings[ 'clear_icon' ] ) ? $settings[ 'clear_icon' ] : array();
$susggest_tld = isset( $settings[ 'susggest_tld' ] ) ? $settings[ 'susggest_tld' ] : 'com';
$prioritize_tld = isset( $settings[ 'prioritize_tld' ] ) ? $settings[ 'prioritize_tld' ] : '';
$prioritize_arr = explode( ',', $prioritize_tld );
foreach ( $prioritize_arr as $key => $value ) {
    $prioritize_arr[ $key ] = trim( $value );
}
$prioritize_json = json_encode( $prioritize_arr );
$domain = '';
if ( isset( $_POST[ 'domain' ] ) ) {
    $domain = $_POST[ 'domain' ];
} elseif ( isset( $_GET[ 'domain' ] ) ) {
    $domain = $_GET[ 'domain' ];
} elseif ( isset( $_COOKIE[ 'domain_check' ] ) ) {
    $domain = base64_decode( urldecode( $_COOKIE[ 'domain_check' ] ) );
}
$domain_value = $domain ? ' value="' . esc_attr( $domain ) . '"' : '';

$csvdata = [];
$get_csv = $data->get_tld_file_data();

if ( isset( $get_csv[ 'status' ] ) && $get_csv[ 'status' ] == 'success' )
    $csvdata = isset( $get_csv[ 'data' ] ) ? $get_csv[ 'data' ] : [];
?>
<script>
    var tld_data = <?php echo json_encode( $csvdata, JSON_PRETTY_PRINT ) ?>;
    sessionStorage.setItem('TLD_Data', JSON.stringify(tld_data));
</script>

<?php wp_nonce_field( 'domain_checking', 'vnx_domain_security' ); ?>
<input type="hidden" name="template" id="vnx_suggest_tld" value="<?php echo esc_attr( $susggest_tld ); ?>">
<input type="hidden" name="template" id="vnx_template_domain" value="view-search-domain">
<form class="relative rounded-lg" data-tld='<?php echo esc_attr( $susggest_tld ); ?>'
    data-priority='<?php echo esc_attr( $prioritize_json ); ?>'>
    <div class="vnx_wrapper overflow-hidden relative rounded-lg">
        <input type="text" class="relative z-0 py-1.5 border outline-none" name="domain"
            placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo $domain_value; ?>>
        <?php
        if ( !empty( $clear_icon ) )
            echo Bricks\Element::render_icon( $clear_icon, [ 'vnx_icon', 'clear_icon' ] );
        ?>
        <button type="submit" id="vnx_search_domain_btn" class="absolute z-[1] rounded right-0 top-0 flex items-center">
            <?php
            if ( !empty( $button_icon ) )
                echo Bricks\Element::render_icon( $button_icon, [ 'vnx_icon' ] );
            echo '<span class="button_text">' . esc_html( $button_text ) . '</span>';
            ?>
        </button>
    </div>
    <div class="vnx_notice absolute hidden">
        <div class="vnx_wrapper relative rounded bg-white">
            <span class="vnx_notice_text block px-4 py-2 relative z-[2]"></span>
        </div>
    </div>
    <div class="loading_domain text-center py-3">
        <svg aria-hidden="true" role="status" class="inline w-4 h-4 text-white animate-spin " viewBox="0 0 100 101"
            fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                fill="#E5E7EB" />
            <path
                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                fill="currentColor" />
        </svg>
    </div>
    <div class="domain_availble w-full bg-[#F7FAFC] rounded-b-lg" id="vail_domain">
</form>