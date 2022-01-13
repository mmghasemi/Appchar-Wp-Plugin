<?php

/*
Plugin Name: Appchar Woocommerce Mobile App Manager
Plugin URI: http://appchar.com
Description: This Plugin is used to manage Android and iOS mobile app created for your WooCommerce store
Version: 3.5.3
Author: appchar
Text Domain: appchar
Domain Path: /languages
Author URI: https://profiles.wordpress.org/appchar/
License: GPLv2 or later
*/


defined('ABSPATH') or exit(__('You do not have direct access to this page.', 'appchar'));

load_plugin_textdomain('appchar', false, basename(dirname(__FILE__)) . '/languages');



define('APPCHAR_DIR'        , plugin_dir_path(__FILE__));
define('APPCHAR_INC_DIR'    , trailingslashit(APPCHAR_DIR . 'include'));
define('APPCHAR_TMP_DIR'    , trailingslashit(APPCHAR_DIR . 'templates'));
define('APPCHAR_NOTIF_DIR'  , trailingslashit(APPCHAR_DIR . 'notifications'));
define('APPCHAR_URL'        , plugin_dir_url(__FILE__));
define('APPCHAR_ASSETS_URL' , trailingslashit(APPCHAR_URL . 'assets'));
define('APPCHAR_JS_URL'     , trailingslashit(APPCHAR_ASSETS_URL . 'js'));
define('APPCHAR_CSS_URL'    , trailingslashit(APPCHAR_ASSETS_URL . 'css'));
define('APPCHAR_IMG_URL'    , trailingslashit(APPCHAR_ASSETS_URL . 'images'));

//OSTicket
require 'mf_support.php';


require APPCHAR_INC_DIR . 'class/ClientLocation.php';
require APPCHAR_INC_DIR . 'endpoint.php';
require APPCHAR_INC_DIR . 'class/AppcharPostType.php';
require APPCHAR_INC_DIR . 'appchar-setting.php';
require APPCHAR_INC_DIR . 'functions.php';
//require APPCHAR_INC_DIR.'class_appchar_string_XML.php';
require APPCHAR_INC_DIR . 'class_appchar_extension.php';
require APPCHAR_INC_DIR . 'class_appchar_token.php';
require APPCHAR_INC_DIR . 'class_appchar_schedule.php';
require APPCHAR_INC_DIR . 'class/class_Appchar_Notification.php';
require APPCHAR_INC_DIR . 'class/classAppcharMultiLanguage.php';
require APPCHAR_INC_DIR . 'class/classAppcharAddMetaToProduct.php';
require APPCHAR_INC_DIR . 'class/classAppcharAddVideoToPosts.php';
require APPCHAR_INC_DIR . 'plugin-update-checker/plugin-update-checker.php';
include_once APPCHAR_NOTIF_DIR."options.php";
require_once APPCHAR_NOTIF_DIR.'woocommerce_appchar_tabs.php';
include_once APPCHAR_INC_DIR . 'appchar_Card.php';

global $appcharEndpoint;
$appcharEndpoint = new Apphcar_Endpoint();
$extension = new AppcharExtension();
$token = new Appchar_Token();
$osTicket = new MF_OSTicketSupport();
global $notification , $extension,  $token , $appchar_ml, $appchar_meta_product,$osTicket; //--->appchar_ml=appchar_multi_language
$notification = new class_Appchar_Notifiation();
$appchar_ml = new classAppcharMultiLanguage();
$appchar_meta_product = new classAppcharAddMetaTOProduct();

if(AppcharExtension::extensionIsActive('wallet')) {
    require_once APPCHAR_DIR . '/wallet/user-wallet.php';
}
if(AppcharExtension::extensionIsActive('lottery')){
    //require( ABSPATH . WPINC . '/pluggable.php' );
    add_action('wplt_admin_lottery_block',array($notification,'add_block_to_admin_lottery'));
}
if(AppcharExtension::extensionIsActive('post_builder')){//true){ //
    require_once APPCHAR_DIR . '/post_builder/appchar_post_builder.php';
    $post_builder = Appchar_Post_Builder::instance();
}

if(AppcharExtension::extensionIsActive('introduce_to_friends')){
    require_once APPCHAR_DIR . '/introduce_to_friends/introduce_to_friends.php';
    add_action('init', function() {
      $introduce_to_friends = Introduce_To_Friends::instance();
    });
}
if(AppcharExtension::extensionIsActive('hierarchical_filter')) {
    include_once APPCHAR_DIR . 'extensions/hierarchical_filter/hierarchical_filter.php';
}
require_once APPCHAR_DIR . '/extensions/product_custom_fields/productCustomFields.php';

appchar_migrate_db();

/*
 * save time_to_send_order to order meta
 * Pooya must checkout->time_to_send_order with json format to checkout endpoint
 *
 */


/*
 * add custom update server for plugin
 */
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'http://plugins.appchar.com/?action=get_metadata&slug=appchar-woocommerce', //Metadata URL.
	__FILE__, //Full path to the main plugin file or functions.php.
	'appchar-woocommerce'
);


add_filter('auto_update_plugin', 'auto_update_specific_plugins', 8, 2);
add_action( 'upgrader_process_complete', 'appchar_upgrade_function',10, 2);

function appchar_upgrade_function( $upgrader_object, $options ) {
    $current_plugin_path_name = plugin_basename( __FILE__ );

    if ($options['action'] == 'update' && $options['type'] == 'plugin' ){
        foreach($options['plugins'] as $each_plugin){
            if ($each_plugin==$current_plugin_path_name){
                appchar_delete_woocommerce_token();
            }
        }
    }
}





add_action( 'admin_menu', 'ad_menu');
add_action( 'woocommerce_order_status_changed', array($notification,'send_notification_when_order_status_changed'), 10, 3);
add_action( 'woocommerce_sales_price_changed' , array($notification,'product_sales_price_changed') ,10 );
add_action( 'transition_post_status',  array($notification,'appchar_on_all_status_transitions'), 10, 3 );
add_action( 'wp_authenticate','login_with_email_address' );
add_action('admin_enqueue_scripts', 'load_appchar_iconpicker_style');
add_action('admin_enqueue_scripts', 'appchar_cards_load_wp_admin_script');
add_action('admin_head', 'my_custom_js');

register_activation_hook( __FILE__, 'appchar_on_activate' );
register_deactivation_hook( __FILE__, 'appchar_on_deactivate' );

/*
 * check extensions and do sth.
 */
if(AppcharExtension::extensionIsActive('time_to_receive_order')){
    add_action('appchar_save_extra_meta_to_order','save_time_to_receive_order',10,2);
    add_filter('appchar_get_single_order','appchar_add_time_to_receive_order_json',10,1);
    add_filter("woocommerce_api_order_response","appchar_add_time_to_receive_order_json2",10,1);
}
if(AppcharExtension::extensionIsActive('get_shipping_on_map')){
    add_action('appchar_save_extra_meta_to_order','save_order_lat_lng',10,2);
}
if(AppcharExtension::extensionIsActive('schedule')) {
    if(get_option('appchar_schedule_error_msg','')==''){
        update_option('appchar_schedule_error_msg',__( 'The shop is closed at this time', 'appchar' ));
    }
    $schedule = new Appchar_schedule();
    if ($schedule->is_on_schedule()) {
        add_action('wp_footer', 'appchar_alert_bar_message');
        add_filter('woocommerce_add_to_cart_validation', 'appchar_validate_add_cart_item', 10, 5);
    }

    add_filter('woocommerce_api_product_category_response','add_enable_time',10,1);
    add_filter('woocommerce_add_to_cart_validation', 'appchar_validate_add_cart_item2', 15, 5);
}
if (AppcharExtension::extensionIsActive('wallet')) {
//    add_action('woocommerce_order_status_changed', 'appchar_add_credits_to_user_account', 10, 3);
    add_action('woocommerce_order_status_changed', 'add_reward_when_order_status_changed', 10, 3);
}
if(AppcharExtension::extensionIsActive('edit_checkout_fields')){
    if(get_option('appchar_checkout_fields_settings','')==''){
        $custom_fields = get_option('inspire_checkout_fields_settings', array());
        $shipping = array();
        $billing = array();
        if (is_array($custom_fields['shipping']) || is_object($custom_fields['shipping'])) {
            foreach ($custom_fields['shipping'] as $key => $custom_field) {
                $custom_field['visible'] = ($custom_field['visible'] == 0) ? true : false;
                $custom_field['required'] = ($custom_field['required'] == 1) ? true : false;
                $shipping[] = $custom_field;
            }
        }
        if (is_array($custom_fields['billing']) || is_object($custom_fields['billing'])) {
            foreach ($custom_fields['billing'] as $key => $custom_field) {
                $custom_field['visible'] = ($custom_field['visible'] == 0) ? true : false;
                $custom_field['required'] = ($custom_field['required'] == 1) ? true : false;
                $billing[] = $custom_field;
            }
        }
        $custom_checkout_fields = array('shipping'=>$shipping,'billing'=>$billing);
        update_option('appchar_checkout_fields_settings',$custom_checkout_fields);
    }
}


if (get_option('appchar_homepage_config', '') == '') {
    $homepage_setting = array(
        'banner_status' => 'enable',
        'category_status' => 'enable',
        'recent_product' => array(
            'status' => 'enable',
            'image' => '',
        ),
        'bestseller_product' => array(
            'status' => 'enable',
            'image' => '',
        ),
        'special_categories' => array()
    );
    update_option('appchar_homepage_config', $homepage_setting);
}

if (!get_option('categories_page_display_type', false)) {
    update_option('categories_page_display_type', 'v1');
}


// NOTE By Iman Mokhtari Aski on 11/12/2019
add_filter( 'plugin_action_links', 'disable_plugin_deactivation', 10, 4 );
function disable_plugin_deactivation( $actions, $plugin_file, $plugin_data, $context ) {
  $options = get_option('appchar_general_setting');
  $unit_price_calculator = (isset($options['unit_price_calculator'])) ? $options['unit_price_calculator'] : get_option('appchar_unit_price_calculator', true);
  $custom_product_addon = (isset($options['custom_product_addon'])) ? $options['custom_product_addon'] : get_option('custom_product_addon', true);

// Remove deactivate link for important plugins
if ( array_key_exists( 'deactivate', $actions ) && in_array( $plugin_file, array(
'woocommerce-measurement-price-calculator/woocommerce-measurement-price-calculator.php'
)) && $unit_price_calculator != false) {
unset( $actions['deactivate'] );
}
if ( array_key_exists( 'deactivate', $actions ) && in_array( $plugin_file, array(
    'woo-custom-product-addons/start.php'
    )) && $custom_product_addon != false) {
    unset( $actions['deactivate'] );
    }
return $actions;
}

generate_image_for_app();
//add_filters('add_meta_to_product_endpoint','add_special_offer_time_meta_to_endpoint',10,1);

$options = get_option('appchar_general_setting');
if ($options['call_to_price_status']) {
    add_filter('woocommerce_is_purchasable', '__return_TRUE');
    add_filter( 'woocommerce_product_single_add_to_cart_text', 'bryce_add_to_cart_text',10,1 );

}

function bryce_add_to_cart_text($var) {
    global $product;
    if($product->get_price()==null) {
        $options = get_option('appchar_general_setting');
        $call_to_price_button = (isset($options['call_to_price_button'])) ? $options['call_to_price_button'] : __('Call To Price!', 'appchar');
        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            if (isset($_GET['lang'])) {
                $lang = $_GET['lang'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            $call_to_price_button = (isset($options['call_to_price_button_' . $lang])) ? $options['call_to_price_button_' . $lang] : __('Call To Price!', 'appchar');
        }
        return $call_to_price_button;
    }
    return $var;
}
