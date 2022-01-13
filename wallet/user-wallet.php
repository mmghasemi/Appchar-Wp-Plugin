<?php
if ( ! function_exists( 'add_filter' ) ){
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! defined( 'WPUW_FILE' ) ){
	define( 'WPUW_FILE', __FILE__ );
}
if ( !in_array( 'user-waller-credit-system/user-wallet.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once( dirname( __FILE__ ) . '/user-wallet-main.php' );
    require_once( dirname( __FILE__ ) . '/log/user-wallet-log.php' );

} else {
    add_action( 'admin_notices', 'appchar_admin_wallet_notice_error' );
}
function appchar_admin_wallet_notice_error() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'you already have "user wallet credit system" plugin in your wordpress, please deactive the plugin in order to use our wallet credit!', 'sample-text-domain' ); ?></p>
    </div>
    <?php
}
