<?php

defined( 'ABSPATH' ) or exit;

// Check if WooCommerce is active and bail if it's not
if ( ! WooCmommerceAppcharProductTabs::is_woocommerce_active() ) {
	return;
}

class WooCmommerceAppcharProductTabs {

	private $tab_data = false;

	/** plugin version number */
	const VERSION = '1.5.0';

	/** @var WooCmommerceCommissionProductTabs single instance of this plugin */
	protected static $instance;

	/** plugin version name */
	const VERSION_OPTION_NAME = 'woocommerce_appchar_product_tabs_lite_db_version';


	/**
	 * Gets things started by adding an action to initialize this plugin once
	 * WooCommerce is known to be active and initialized
	 */
	public function __construct() {
		// Installation
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) $this->install();
		
		add_action( 'woocommerce_init', array( $this, 'init' ) );
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.5.0
	 */
	public function __clone() {

		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'commission-managers' ), 'WooCommerce Custom Product Tabs Lite' ), '1.5.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.5.0
	 */
	public function __wakeup() {

		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'commission-managers' ), 'WooCommerce Custom Product Tabs Lite' ), '1.5.0' );
	}


	public function init() {
		//add_action( 'woocommerce_process_product_meta',     array( $this, 'product_save_data' ), 10, 2 );
		add_action('save_post', array($this,'product_saved'));
	}
	public function product_saved($post_id)
	{
		$post = get_post($post_id);
		
		if( $post->post_type != 'product')
			return ;
		$WC_Product = wc_get_product( $post_id );
		$regular_price = $WC_Product->get_regular_price();
		$sale_price = $WC_Product->get_sale_price();
		$sale_price_dates_from 	= ( $date = get_post_meta( $post_id, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to 	= ( $date = get_post_meta( $post_id, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sales_hash = $regular_price."#".$sale_price."#".$sale_price_dates_from."#".$sale_price_dates_to;
		$sales_hash = md5($sales_hash);
		if( strcmp( $this->get_data($post_id) , $sales_hash ) != 0 && $post->post_status == 'publish' )
		{
			do_action('woocommerce_sales_price_changed',$post_id);
		}
		$tab_data = array('sales_product_hash'=>$sales_hash );
		update_post_meta( $post_id, 'frs_woo_product_appchar_tabs', $tab_data );
	}


	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public static function is_woocommerce_active() {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}

	static public function get_data($post_id)
	{
		$tab_data = maybe_unserialize( get_post_meta( $post_id, 'frs_woo_product_appchar_tabs', true ) );

		if ( empty( $tab_data ) ) {
			return '';
		}
		else return $tab_data['sales_product_hash'];
	}
	private function install() {

		global $wpdb;

		$installed_version = get_option( self::VERSION_OPTION_NAME );

		// installed version lower than plugin version?
		if ( -1 === version_compare( $installed_version, self::VERSION ) ) {
			// new version number
			update_option( self::VERSION_OPTION_NAME, self::VERSION );
		}
	}

}

function wc_custom_product_appchar_tabs_lite() {
	return WooCmommerceAppcharProductTabs::instance();
}

$GLOBALS['woocommerce_appchar_info'] = wc_custom_product_appchar_tabs_lite();



