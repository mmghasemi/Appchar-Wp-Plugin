<?php

class Apphcar_Endpoint
{

    // private $entity_body = json_decode('{"line_items":[{"product_id":24,"variation_id":0,"variation":{},"quantity":2,"line_total":1100,"line_tax":0,"line_subtotal":500,"line_subtotal_tax":0,"line_tax_data":{"total":[],"subtotal":[]}}],"user_id":4,"destination":{"country":"IR","state":"IS","postcode":"00731","city":"isfahan","address":"address","address_2":""}}');
    private $customer;
    private $order;
    private $products;

    public function __construct()
    {

        require_once APPCHAR_INC_DIR . 'api/class-appchar-category-object.php';
        require_once APPCHAR_INC_DIR . 'api/class-wc-api-server.php';
        require_once APPCHAR_INC_DIR . 'api/class-wc-api-resource.php';
        require_once APPCHAR_INC_DIR . 'api/class-wc-api-exception.php';
        require_once APPCHAR_INC_DIR . 'api/class-wc-api-customers.php';
        require_once APPCHAR_INC_DIR . 'api/class-wc-api-orders.php';
        require_once APPCHAR_INC_DIR . 'api/class-wp-api-products.php';
        require_once APPCHAR_INC_DIR . 'api/class-appchar-cart-handler.php';
        require_once APPCHAR_INC_DIR . 'api/class-appchar-checkout.php';
        $this->customer = new APPCHAR_WC_API_Customers(new APPCHAR_WC_API_Server("/customers"));
        $this->order = new APPCHAR_WC_API_Orders(new APPCHAR_WC_API_Server("/orders"));
        $this->products = new APPCHAR_WC_API_Products(new APPCHAR_WC_API_Server("/products"));
        add_action('parse_request', array(&$this, 'sniff_requests'));
        add_filter('query_vars', array(&$this, 'add_query_vars'));
        add_action('init', array(&$this, 'add_endpoint'));
        add_action('admin_init', array(&$this, 'appchar_flush_rewrites'));
    }

    public function sniff_requests()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        global $wp;
        global $wpdb;
        global $token;
        global $notification;
        date_default_timezone_set(get_option('timezone_string'));

        if (isset($wp->query_vars['appchar_display_notifications'])) {
            $this->authorizeAppcharToken();
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $count = (isset($_GET['count'])) ? trim($_GET['count']) : 15;
                $paged = (isset($_GET['page'])) ? $_GET['page'] : 1;
                $userid = (isset($_GET['userid'])) ? $_GET['userid'] : 0;
                $messages = $notification->get_message_by_userid($userid, $paged, $count);
                $messages2 = array();
                foreach ($messages as $message) {
                    $message->notification_time = strtotime($message->notification_time);
                    $messages2[] = $message;
                }
                if (is_array($messages2) || is_object($messages2)) {
                    $result = array(
                        'success' => true,
                        'data' => $messages2,
                        'count' => $notification->get_notifications_count($userid)
                    );
                    wp_send_json($result);
                } else {
                    wp_send_json_error(array('error' => 'No message found'), 404);
                }
            }
        }
        if (isset($wp->query_vars['appchar_special_offer'])) {
            if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                if (isset($_GET['locale'])) {
                    $lang = $_GET['locale'];
                } else {
                    if (defined('ICL_LANGUAGE_CODE')) {
                        $lang = ICL_LANGUAGE_CODE;
                    } else {
                        $lang = 'fa';
                    }
                }
                $hpc = get_option('appchar_homepage_config2_' . $lang, array());
            } else {
                $hpc = get_option('appchar_homepage_config2', array());
            }
            foreach ($hpc as $element) {
                if ($element['type'] == 'special_offer') {
                    $ids = $element['items'];
                }
            }
            $products = array();
            if (isset($ids)) {
                foreach ($ids as $id) {
                    $product2 = wc_get_product($id);
                    $product = $this->appchar_get_product_by_id($id);
                    if ($product2->is_on_sale()) {
                        $product['remaining'] = remaining_time();
                    }
                    $products[] = $product;
                }
            }
            wp_send_json_success($products);
        }
        if (isset($wp->query_vars['appchar_get_states'])) {
            if (get_option('appchar_states', false)) {
                $states = $appchar_states = get_option('appchar_states', false);
            } else {
                $countries_obj = new WC_Countries();
                $default_country = $countries_obj->get_base_country();
                $default_county_states = $countries_obj->get_states($default_country);
                $states = array();
                if (is_array($default_county_states) || is_object($default_county_states)) {
                    foreach ($default_county_states as $key => $state) {
                        $states[] = array(
                            'id' => $key,
                            'name' => $state,
                            'city' => array(),
                        );
                    }
                }
            }
            wp_send_json_success($states);
        }
        if (isset($wp->query_vars['appchar_get_options'])) {
            global $woocommerce;
            //--------------plugin version--------------
            $plugins = get_plugins();
            $appchar_data = $plugins['appchar-woocommerce/appchar-woocommerce.php'];
            $json['appchar-woocommerce_version'] = $appchar_data['Version'];
            $woocommerce_data = $plugins['woocommerce/woocommerce.php'];
            $json['woocommerce_version'] = $woocommerce_data['Version'];
            //--------------photo size------------------
            $json['shop_catalog_image_size'] = get_option('shop_catalog_image_size', false);
            $json['shop_single_image_size'] = get_option('shop_single_image_size', false);
            $json['shop_thumbnail_image_size'] = get_option('shop_thumbnail_image_size', false);
            //--------------category---------------------
            $cat_type = get_option('appchar_display_categories', 'random');
            if ($cat_type == 'cat_button') {
                $json['homepage_category_type'] = 'button';
                $json['homepage_category_button_text'] = $this->cat_list();
            } else {
                $json['homepage_category_type'] = 'list';
                $json['homepage_category_list'] = $this->cat_list();
            }
            $json['homepage_banners'] = $this->get_banners();
            $json['menu_pages'] = $this->get_pages();
            //-------------other-------------------------
            $json['schedule_time'] = $this->get_schedule_time();
            $json['schedule_alert_msg'] = get_option('appchar_schedule_error_msg', '');
            $hpc = get_option('appchar_homepage_config', array());
            foreach ($hpc['special_categories'] as $key => $value) {
                $hpc['special_categories'][$key]['id'] = intval($value['id']);
            }
            $json['homepage_config'] = $hpc;
            $json['home_static_banner_image_1'] = 'http://1x1px.me/FFFFFF-0.png';
            $json['home_static_banner_image_2'] = 'http://1x1px.me/FFFFFF-0.png';
            global $woocommerce;
            $countries_obj = new WC_Countries();
            $countries = $countries_obj->__get('countries');
            $default_country = $countries_obj->get_base_country();
            $default_county_states = $countries_obj->get_states($default_country);
            $states = array();
            if (is_array($default_county_states) || is_object($default_county_states)) {
                foreach ($default_county_states as $key => $state) {
                    $states[] = array(
                        'id' => $key,
                        'name' => $state
                    );
                    //echo $key.'+'.$state.'<br>';
                }
            }
            $json['states'] = $states;
            $json['coupon'] = get_option('woocommerce_enable_coupons', false);
            if (get_option('appchar_in_app_payment', false)) {
                $json['in_app_payment'] = true;
            } else {
                $json['in_app_payment'] = false;
            }

            if (get_option('appchar_product_display_short_description', false)) {
                $json['product_display_short_description'] = true;
            } else {
                $json['product_display_short_description'] = false;
            }
            $pinned ['message'] = get_option('appchar_update_message', '');
            $pinned ['backgroundcolor'] = '#c60f13';
            $pinned ['color'] = '#ffffff';
            $pinned ['download_link'] = get_option('appchar_update_link', '');
            //Due to the fact that I can't add extra field to pinned message I have to put the input in the pinned message section But not showing it pinned message sextion in api(Specifically in appchar/get_options_v2)
            if (get_option('appchar_force_update', false)) {
                $json['force_update'] = true;
            } else {
                $json['force_update'] = false;
            }
            $json['pinned_message'] = $pinned;
            $json['woocommerce_cart_redirect_after_add'] = get_option('woocommerce_cart_redirect_after_add', false);
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
            $json['custom_checkout_fields'] = array('shipping' => $shipping, 'billing' => $billing);
            if (get_option('appchar_custom_register_fields', false)) {
                $json['custom_register_fields'] = get_option('appchar_custom_register_fields', false);
            } else {
                $json['custom_register_fields'] = array();
            }

            if (get_option('appchar_custom_login_fields', false)) {
                $json['custom_login_fields'] = get_option('appchar_custom_login_fields', false);
            } else {
                $json['custom_login_fields'] = array();
            }

            $json['user_approve'] = get_option('appchar_user_approve', false);
            $json['force_login'] = get_option('appchar_force_login', false);
            $json['catalog_mode'] = get_option('appchar_catalog_mode', false);
            $json['categories_page_display_type'] = get_option('categories_page_display_type', 'v1');
            $json['currency'] = get_woocommerce_currency();
            $json['timezone'] = date_default_timezone_get();
            $serverdate = getdate();
            $json['date'] = $serverdate['year'] . '/' . $serverdate['month'] . '/' . $serverdate['mday'] . ' | ' . $serverdate['hours'] . ':' . $serverdate['minutes'] . ':' . $serverdate['seconds'];
            wp_send_json_success($json);
        }
        if (isset($wp->query_vars['appchar_get_options_v2'])) {
            $general_setting = get_option('appchar_general_setting', array());

            global $woocommerce;
            //--------------plugin version--------------
            $plugins = get_plugins();
            $appchar_data = $plugins['appchar-woocommerce/appchar-woocommerce.php'];
            $json['appchar-woocommerce_version'] = $appchar_data['Version'];
            $woocommerce_data = $plugins['woocommerce/woocommerce.php'];
            $json['woocommerce_version'] = $woocommerce_data['Version'];
            $json['woocommerce_options'] = array(
                'woocommerce_hide_out_of_stock_items' => (get_option('woocommerce_hide_out_of_stock_items', 'no') == 'yes') ? true : false,
            );


            if (AppcharExtension::extensionIsActive('appstore_distribution')) {
                $json['user_country_code'] = ClientLocation::getCountry();
                $json['alternative_base_url'] = ($general_setting['appstore_distribution_alternative_url'])?$general_setting['appstore_distribution_alternative_url']:'';
                $json['allow_countries_to_open'] = ($general_setting['appstore_distribution_allowed_countries'])?$general_setting['appstore_distribution_allowed_countries']:[];
            }

            $json['coupon_is_enable'] = (get_option('woocommerce_enable_coupons', false) == 'yes') ? true : false;
            $json['custom_product_addon'] = (isset($general_setting['custom_product_addon'])) ? $general_setting['custom_product_addon'] : false;


            global $woocommerce_wpml;

            if(AppcharExtension::extensionIsActive('multi_language') && isset($_GET['locale']) && $_GET['locale']=='en' && $woocommerce_wpml->settings[ 'enable_multi_currency' ] == WCML_MULTI_CURRENCIES_INDEPENDENT){
                $json['currency'] = 'USD';
            }else {
                $json['currency'] = get_woocommerce_currency();
            }
            $json['default_language'] = appchar_get_locale();
            $custom_fields = get_option('inspire_checkout_fields_settings', array());
            $shipping = array();
            $billing = array();
            if (isset($custom_fields['shipping']) && (is_array($custom_fields['shipping']) || is_object($custom_fields['shipping']))) {
                foreach ($custom_fields['shipping'] as $key => $custom_field) {
                    $custom_field['visible'] = ($custom_field['visible'] == 0) ? true : false;
                    $custom_field['required'] = ($custom_field['required'] == 1) ? true : false;
                    $shipping[] = $custom_field;
                }
            }

            if (isset($custom_fields['billing']) && (is_array($custom_fields['billing']) || is_object($custom_fields['billing']))) {
                foreach ($custom_fields['billing'] as $key => $custom_field) {
                    $custom_field['visible'] = ($custom_field['visible'] == 0) ? true : false;
                    $custom_field['required'] = ($custom_field['required'] == 1) ? true : false;
                    $billing[] = $custom_field;
                }
            }


            $json['custom_checkout_fields2'] = array('shipping' => $shipping, 'billing' => $billing);
            $json['custom_checkout_fields'] = get_option('appchar_checkout_fields_settings', array('shipping' => array(), 'billing' => array()));
            if (get_option('appchar_custom_register_fields', false)) {
                $json['custom_register_fields'] = get_option('appchar_custom_register_fields', false);
            } else {
                $json['custom_register_fields'] = array();
            }
            if (get_option('appchar_checkout_receive_setting', false)) {
                $json['order_receive_setting'] = get_option('appchar_checkout_receive_setting', false);
            } else {
                $json['order_receive_setting'] = array("status" => "disable");;
            }
            if (get_option('appchar_checkout_send_setting', false)) {
                $json['order_send_setting'] = get_option('appchar_checkout_send_setting', false);
            } else {
                $json['order_send_setting'] = array("status" => "disable");;
            }

            if (get_option('introduce_to_friends_setting', false)) {
                $json['introduce_to_friends'] = get_option('introduce_to_friends_setting', false);
                if (isset($json['introduce_to_friends']['page'])) {
                    $json['introduce_to_friends']['link'] = get_permalink($json['introduce_to_friends']['page']);
                } else {
                    $register_page = get_page_by_title('My Account');
                    $json['introduce_to_friends']['link'] = get_permalink($register_page->ID);
                }
            } else {
                $json['introduce_to_friends'] = null;
            }

            if (get_option('appchar_custom_login_fields', false)) {
                $json['custom_login_fields'] = get_option('appchar_custom_login_fields', false);
            } else {
                $json['custom_login_fields'] = array();
            }

            // NOTE Adding Custom Weight option to general settings
            // NOTE By Iman Mokhtari Aski on 11/12/2019
            $unit_price_calculator = (get_option('appchar_unit_price_calculator', false)) ? true : false;
            $toggle_product_to_cart = (get_option('appchar_toggle_product_to_cart', false)) ? true : false;
            $sync_cart_to_site = (get_option('appchar_sync_cart_to_site', false)) ? true : false;
            $allowed_countries = array();
            foreach(WC()->countries->get_allowed_countries() as $key => $value) {
                $allowed_countries[] = array(
                    'id' => $key,
                    'name' => $value,
                );
            }
            $json['allowed_countries'] = $allowed_countries;
            $product_rate_is_visible = (get_option('appchar_product_rate_is_visible', true)) ? true : false;
            $bestseller_product_is_visible = (get_option('appchar_bestseller_product_is_visible', true)) ? true : false;
            $recent_product_is_visible = (get_option('appchar_recent_product_is_visible', true)) ? true : false;


            $json['cart_button_type'] = (isset($general_setting['cart_button_type'])) ? $general_setting['cart_button_type'] : 'simple_button';
            $json['price_ranger'] = (isset($general_setting['price_ranger'])) ? $general_setting['price_ranger'] : 'variation';
            $json['material_theming_shape_options'] = array(
              'material_shape_type' => (isset($general_setting['material_shape_type'])) ? $general_setting['material_shape_type'] : 'round_shaped',
              'material_shape_value' => (isset($general_setting['material_shape_value'])) ? $general_setting['material_shape_value'] : '0dp',
              'material_shape_position' => (isset($general_setting['material_shape_position'])) ? $general_setting['material_shape_position'] : array(),
            );
            if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                if (isset($_GET['locale'])) {
                    $lang = $_GET['locale'];
                } else {
                    if (defined('ICL_LANGUAGE_CODE')) {
                        $lang = ICL_LANGUAGE_CODE;
                    } else {
                        $lang = 'fa';
                    }
                }
                $json['call_to_price_button'] = (isset($general_setting['call_to_price_button_' . $lang])) ? $general_setting['call_to_price_button_' . $lang] : __('Call To Price', 'appchar');

            } else {
                $json['call_to_price_button'] = (isset($general_setting['call_to_price_button'])) ? $general_setting['call_to_price_button'] : __('Call To Price', 'appchar');
            }
            $json['appchar_call_to_price_status'] = (isset($general_setting['call_to_price_status'])) ? $general_setting['call_to_price_status'] : false;
            // NOTE By Iman Mokhtari Aski on 11/12/2019
            $json['appchar_unit_price_calculator'] = (isset($general_setting['unit_price_calculator'])) ? $general_setting['unit_price_calculator'] : $unit_price_calculator;
            $json['appchar_currency'] = get_woocommerce_currency();
            $json['appchar_currency_symbol'] = get_woocommerce_currency_symbol();
            $json['appchar_currency_pos'] = get_option( 'woocommerce_currency_pos' );
            $json['appchar_price_thousand_sep'] =  get_option( 'woocommerce_price_thousand_sep' );
            $json['appchar_price_decimal_sep'] = get_option( 'woocommerce_price_decimal_sep' );
            $json['appchar_price_decimals'] = wc_get_price_decimals();
            $json['appchar_toggle_product_to_cart'] = (isset($general_setting['toggle_product_to_cart'])) ? $general_setting['toggle_product_to_cart'] : $toggle_product_to_cart;
            $json['appchar_sync_cart_to_site'] = (isset($general_setting['sync_cart_to_site'])) ? $general_setting['sync_cart_to_site'] : $sync_cart_to_site;
            $json['appchar_product_rate_is_visible'] = (isset($general_setting['product_rate_is_visible'])) ? $general_setting['product_rate_is_visible'] : $product_rate_is_visible;
            $json['appchar_bestseller_product_is_visible'] = (isset($general_setting['bestseller_product_is_visible'])) ? $general_setting['bestseller_product_is_visible'] : $bestseller_product_is_visible;
            $json['appchar_recent_product_is_visible'] = (isset($general_setting['recent_product_is_visible'])) ? $general_setting['recent_product_is_visible'] : $recent_product_is_visible;
            $json['appchar_search_is_visible'] = (isset($general_setting['search_is_visible'])) ? $general_setting['search_is_visible'] : true;
            $json['appchar_sort_is_visible'] = (isset($general_setting['sort_is_visible'])) ? $general_setting['sort_is_visible'] : true;
            $json['appchar_categories_is_visible'] = (isset($general_setting['categories_is_visible'])) ? $general_setting['categories_is_visible'] : true;

            $json['appchar_products_is_visible'] = (isset($general_setting['products_is_visible'])) ? $general_setting['products_is_visible'] : false;
            $json['appchar_hamburger_menu_is_visible'] = (isset($general_setting['hamburger_menu_is_visible'])) ? $general_setting['hamburger_menu_is_visible'] : false;
            $json['appchar_profile_is_visible'] = (isset($general_setting['profile_is_visible'])) ? $general_setting['profile_is_visible'] : false;
            $json['appchar_blog_categories_is_visible'] = (isset($general_setting['blog_categories_is_visible'])) ? $general_setting['blog_categories_is_visible'] : false;
            $json['appchar_hide_product_images_in_shopping_cart'] = (isset($general_setting['hide_product_images_in_shopping_cart'])) ? $general_setting['hide_product_images_in_shopping_cart'] : false;

            $json['appchar_sidebar_profile_is_visible'] = (isset($general_setting['sidebar_profile_is_visible'])) ? $general_setting['sidebar_profile_is_visible'] : true;
            $json['appchar_sidebar_blog_is_visible'] = (isset($general_setting['sidebar_blog_is_visible'])) ? $general_setting['sidebar_blog_is_visible'] : true;
            $json['appchar_sidebar_favorite_is_visible'] = (isset($general_setting['sidebar_favorite_is_visible'])) ? $general_setting['sidebar_favorite_is_visible'] : true;
            $json['appchar_related_products_is_visible'] = (isset($general_setting['related_products_is_visible'])) ? $general_setting['related_products_is_visible'] : true;

            $json["cedarmaps_access_token"] = (isset($general_setting['cedarmaps_access_token'])) ? $general_setting['cedarmaps_access_token'] : null;
            $appchar_filter_custom_label = (isset($general_setting['appchar_filter_custom_label'])) ? $general_setting['appchar_filter_custom_label'] : null;
            $json['appchar_filter_custom_label'] = explode('|',$appchar_filter_custom_label);
            $json['appchar_hide_signup_page'] = (isset($general_setting['hide_signup_page'])) ? $general_setting['hide_signup_page'] : false;
            $json['appchar_hide_user_info_in_drawer'] = (isset($general_setting['hide_user_info_in_drawer'])) ? $general_setting['hide_user_info_in_drawer'] : false;
            $json['appchar_open_child_page'] = (isset($general_setting['open_child_page'])) ? $general_setting['open_child_page'] : false;
            if(isset($general_setting['logo_image_id']) && $general_setting['logo_image_id']!='')
                $json['logo_image'] = wp_get_attachment_image_src( $general_setting['logo_image_id'], 'appcahr-60' )[0];
            else
                $json['logo_image'] = '';

            if(isset($general_setting['order_status_icon'])){
                foreach ($general_setting['order_status_icon'] as $key =>$order_status_icon){
                    if(wp_get_attachment_url($order_status_icon)){
                        $json['order_status_icon'][$key]  =  wp_get_attachment_url($order_status_icon);
                    }else {
                        $json['order_status_icon'][$key] = '';
                    }
                }
            }else{
                $json['order_status_icon'] = null;
            }
            date_default_timezone_set(get_option('timezone_string'));
            $date = new DateTime();
            $timeZone = $date->getTimezone();
            $json['timezone'] = $timeZone->getName();
            $serverdate = getdate();
            $json['date'] = $serverdate['year'] . '/' . $serverdate['month'] . '/' . $serverdate['mday'] . ' | ' . $serverdate['hours'] . ':' . $serverdate['minutes'] . ':' . $serverdate['seconds'];
            $pinned ['message'] = (isset($general_setting['update_message'])) ? $general_setting['update_message'] : get_option('appchar_update_message', '');
            $pinned ['backgroundcolor'] = '#c60f13';
            $pinned ['color'] = '#ffffff';
            $pinned ['download_link'] = (isset($general_setting['update_link'])) ? $general_setting['update_link'] : get_option('appchar_update_link', '');
            $json ['force_update'] = (isset($general_setting['force_update'])) ? $general_setting['force_update'] : get_option('appchar_force_update', '');
            $json['pinned_message'] = $pinned;
            $pinned ['color'] = '#555555';
            $json['update_message'] = $pinned;
            $pinned2 ['message'] = (isset($general_setting['custom_message'])) ? $general_setting['custom_message'] : get_option('appchar_custom_message', '');
            $pinned2 ['backgroundcolor'] = (isset($general_setting['pinned_bgcolor'])) ? $general_setting['pinned_bgcolor'] : get_option('appchar_custom_message_backgroundcolor', '#2ba6cb');
            $pinned2 ['color'] = '#ffffff';
            $json['pinned_message2'] = $pinned2;
            $json['woocommerce_cart_redirect_after_add'] = get_option('woocommerce_cart_redirect_after_add', false);
            $json['menu_pages'] = $this->get_pages();
            $json['schedule_time'] = $this->get_schedule_time();
            $json['woocommerce_cart_redirect_after_add'] = (get_option('woocommerce_cart_redirect_after_add', false) == 'yes') ? true : false;
            $countries_obj = new WC_Countries();
            $countries = $countries_obj->__get('countries');
            $default_country = $countries_obj->get_base_country();
            $default_county_states = $countries_obj->get_states($default_country);
            $states = array();
            if (is_array($default_county_states) || is_object($default_county_states)) {
                foreach ($default_county_states as $key => $state) {
                    $states[] = array(
                        'id' => $key,
                        'name' => $state
                    );
                    //echo $key.'+'.$state.'<br>';
                }
            }
            if (get_option('appchar_states', false)) {
                $states = $appchar_states = get_option('appchar_states', false);
            }
            $json['city_type'] = (isset($general_setting['city_type'])) ? $general_setting['city_type'] : 'text';
            $json['states'] = $states;

            $json['in_app_payment'] = (isset($general_setting['in_app_payment'])) ? $general_setting['in_app_payment'] : get_option('appchar_in_app_payment', false);
            $json['product_display_short_description'] = (isset($general_setting['product_display_short_description'])) ? $general_setting['product_display_short_description'] : get_option('appchar_product_display_short_description', false);

            $json['force_login'] = (isset($general_setting['force_login'])) ? $general_setting['force_login'] : get_option('appchar_force_login', false);
            $json['user_approve'] = (isset($general_setting['user_approve'])) ? $general_setting['user_approve'] : get_option('appchar_user_approve', false);
            $json['catalog_mode'] = (isset($general_setting['catalog_mode'])) ? $general_setting['catalog_mode'] : get_option('appchar_catalog_mode', false);;
            if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                if (isset($_GET['locale'])) {
                    $lang = $_GET['locale'];
                } else {
                    if (defined('ICL_LANGUAGE_CODE')) {
                        $lang = ICL_LANGUAGE_CODE;
                    } else {
                        $lang = 'fa';
                    }
                }
                $json['add_to_cart_button'] = (isset($general_setting['add_to_cart_button_' . $lang])) ? $general_setting['add_to_cart_button_' . $lang] : get_option('appchar_add_to_cart_button_' . $lang, __('add to cart', 'appchar'));

            } else {
                $json['add_to_cart_button'] = (isset($general_setting['add_to_cart_button'])) ? $general_setting['add_to_cart_button'] : get_option('appchar_add_to_cart_button', __('add to cart', 'appchar'));
            }
            if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                if (isset($_GET['locale'])) {
                    $lang = $_GET['locale'];
                } else {
                    if (defined('ICL_LANGUAGE_CODE')) {
                        $lang = ICL_LANGUAGE_CODE;
                    } else {
                        $lang = 'fa';
                    }
                }
                $json['featured_label_text'] = (isset($general_setting['featured_label_text_' . $lang])) ? $general_setting['featured_label_text_' . $lang] : __('featured', 'appchar');

            } else {
                $json['featured_label_text'] = (isset($general_setting['featured_label_text'])) ? $general_setting['featured_label_text'] : __('featured', 'appchar');
            }
            $json['blog_title'] = (isset($general_setting['blog_title'])) ? $general_setting['blog_title'] : get_option('appchar_blog_title', __('تازه ها', 'appchar'));
            $json['blog_type'] = (isset($general_setting['blog_type'])) ? $general_setting['blog_type'] : "posts";

            $json['lottery_title'] = (isset($general_setting['lottery_title'])) ? $general_setting['lottery_title'] : get_option('appchar_lottery_title', __('قرعه کشی', 'appchar'));
            $json['google_analytics_tracking_id'] = (isset($general_setting['google_analytics_tracking_id'])) ? $general_setting['google_analytics_tracking_id'] : get_option('appchar_google_analytics_tracking_id', '');


            $json['categories_page_display_type'] = (isset($general_setting['categories_display_type'])) ? $general_setting['categories_display_type'] : get_option('categories_page_display_type', 'v1');
            $json['percentage_discount_view_type'] = (isset($general_setting['percentage_discount_view_type'])) ? $general_setting['percentage_discount_view_type'] : "hide";
            $json['product_list_display_type'] = (isset($general_setting['product_list_display_type'])) ? $general_setting['product_list_display_type'] : 'two-col';
            $json['review_page_display_type'] = 'v2';


            if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                if (isset($_GET['locale'])) {
                    $lang = $_GET['locale'];
                } else {
                    if (defined('ICL_LANGUAGE_CODE')) {
                        $lang = ICL_LANGUAGE_CODE;
                    } else {
                        $lang = 'fa';
                    }
                }
                $hpc = get_option('appchar_homepage_config2_' . $lang, array());
            } else {
                $hpc = get_option('appchar_homepage_config2', array());
            }
            $hpc2 = array();
            foreach ($hpc as $element) {
                if ($element['type'] == 'slider' || $element['type'] == 'grid') {
                    $items = array();
                    foreach ($element['items'] as $item) {
                        $object = $this->add_object_to($item);//TODO
                        if (!$object) {
                            continue;
                        }
                        $items[] = $object;
                    }
                    $element['items'] = $items;
                } elseif ($element['type'] == 'product_list') {
                    if ($element['product_list_type'] == "special_category") {
                        $term = get_term($element['category_id'], 'product_cat');
                        if (is_wp_error($term)) {
                            continue;
                        }
                        $term->id = $term->term_id;
                        $element['category'] = $term;
                    }
                } elseif ($element['type'] == 'special_offer') {
                    unset($element['items']);
                    $element['remaining'] = remaining_time();
                } elseif ($element['type'] == 'lottery_leader_board') {
                    $result = new WPLT_Lottery($element['lottery_id']);
                    $element['title'] = $result->name;
                    if ($result->active) {
                        $element['leader_board'] = $result->get_leader_board();
                    } else {
                        $element['leader_board'] = $result->get_winners();
                    }
                } else {
                    $element = $this->add_object_to($element);
                    if (!$element) {
                        continue;
                    }
                }
                $hpc2[] = $element;
            }
            $json['homepage_config']['homepage_type'] = (get_option('appchar_homepage_type', false)) ? get_option('appchar_homepage_type', false) : 'normal';
            $json['homepage_config']['elements'] = $hpc2;
            $json['bottom_navigation_config']['enable'] = (isset($general_setting['bottom_navigation'])) ? $general_setting['bottom_navigation'] : false;

            if(isset($general_setting['bottom_navigation']) && $general_setting['bottom_navigation']) {
              $bottom_navigation_items = array(
                array(
                  'name' => 'home',
                  'icon' => '',
                  'title' => 'خانه',
                  'link' => ''
                ),
                array(
                  'name' => 'cat',
                  'icon' => '',
                  'title' => 'دسته بندی',
                  'link' => ''
                ),
                array(
                  'name' => 'cart',
                  'icon' => '',
                  'title' => 'سبد خرید',
                  'link' => ''
                ),
                array(
                  'name' => 'search',
                  'icon' => '',
                  'title' => 'جستجو',
                  'link' => ''
                ),
                array(
                  'name' => 'my_acc',
                  'icon' => '',
                  'title' => 'حساب من',
                  'link' => ''
                )
              );
              $json['bottom_navigation_config']['elements'] = $bottom_navigation_items;
              $json['wide_searchbar']['enable'] = true;
              $json['wide_searchbar']['config'] = array(
                'title' => 'جستجو',
                'icon' => '',
                'background_color' => 'gray'
              );
            }else {
              $json['wide_searchbar']['enable'] = false;
            }
            $json['woocommerce_checkout_show_terms'] = (wc_get_page_id('terms') > 0 && apply_filters('woocommerce_checkout_show_terms', true)) ? true : false;
            $json['woocommerce_checkout_show_terms_page_id'] = wc_get_page_id('terms');
            wp_send_json_success(apply_filters('appchar_get_options_v2', $json));
        }
//        wpmu_create_blog();

        if (isset($wp->query_vars['appchar_banners'])) {
            if (!isset($_GET['category_id'])) {
                wp_send_json($this->get_banners());
            } else {
                if (!get_term_meta($_GET['category_id'], 'banner', false)) {
                    wp_send_json_error(array('error' => 'No banner found'), 404);
                }
                $cat_banner[] = get_term_meta($_GET['category_id'], 'banner', false);
                wp_send_json_success($cat_banner);
            }
        }

        if (isset($wp->query_vars['appchar_pages'])) {
            wp_send_json($this->get_pages());
        }

        if (isset($wp->query_vars['appchar_get_page'])) {
            $page_id = $wp->query_vars['appchar_page_id'];
            $page = get_post($page_id, OBJECT, 'display');
            if (!$page) {
                wp_send_json_error(array('error' => 'there is no page'), 404);
            }
            $page->post_content = apply_filters("the_content", $page->post_content);
            wp_send_json($page);
        }


        if ( is_plugin_active( 'login-with-sms/login-with-sms.php' ) ) {
            if (isset($wp->query_vars['appchar_auth_login'])) {
                $this->authorizeAppcharToken();
                switch ($_SERVER['REQUEST_METHOD']) {
                    case "POST":
                        $entity_body = json_decode(file_get_contents('php://input'));
                        $arr = array();
                        $arr = objToArray($entity_body, $arr);
                        if(!isset($arr['mobile'])){
                            wp_send_json_error(array('error' => 'mobile Required'), 400);
                        }
                        try {
                            $mobile = validate_mobile(trim($arr['mobile']));
//                            if (($mobile)) {
//                                manage_user($mobile);
//                            }
                        } catch (Exception $e) {
                            wp_send_json_error(array('error' => $e->getMessage()), 400);
                        }
                        try{
                            $user = manage_user($mobile);
                            $json = array(
                                'mobile' => $mobile,
//                                'verify' => $verify,
                            );
                            if (AppcharExtension::extensionIsActive('introduce_to_friends')) {

                                if (isset($user->is_new) && $user->is_new) {
                                    $json['show_referer_code_field'] = true;
                                }else{
                                    $json['show_referer_code_field'] = false;
                                }

                            }
                            wp_send_json_success($json);
                        }catch (Exception $e){
                            wp_send_json_error(array('error' => $e->getMessage()), 400);
                        }

                        break;
                    default:
                        wp_send_json_error(array('error' => 'method not allowed'), 405);
                }
            }
            if (isset($wp->query_vars['appchar_auth_verify'])) {
                $this->authorizeAppcharToken();
                switch ($_SERVER['REQUEST_METHOD']) {
                    case "POST":
                        $entity_body = json_decode(file_get_contents('php://input'));
                        $arr = array();
                        $arr = objToArray($entity_body, $arr);
                        if (!isset($arr['mobile'])) {
                            wp_send_json_error(array('error' => 'mobile Required'), 400);
                        }
                        try {
                            $mobile = validate_mobile($arr['mobile']);
                        } catch (Exception $e) {
                            wp_send_json_error(array('error' => $e->getMessage()), 400);
                        }
                        if (!isset($arr['verification_code'])) {
                            wp_send_json_error(array('error' => 'verification_code Required'), 400);
                        }
                        try {
                            $user = verify_user($arr['mobile'], $arr['verification_code']);
                            if (isset($arr['device_id'])) {
                                $device_id = $arr['device_id'];
//                                $useracc = get_user_by('user_login', $username);
                                $wpdb->insert($wpdb->prefix . 'appchar_user_devices', array('user_device_id' => $device_id, 'user_id' => $user->ID));
                            }
                            //introduce to friends
                            $refer_code = substr(md5($user->user_login), 0, 7);
                            add_user_meta($user->ID, 'refer_code', $refer_code);
                            if (AppcharExtension::extensionIsActive('introduce_to_friends')) {
                                if (isset($arr['refer_code'])) {
                                    appchar_itf_registration_save($user->ID, $arr['refer_code']);
                                }
                            }
                            //introduce to friends
                            if (AppcharExtension::extensionIsActive('wallet')) {
                                $hasBalance = get_user_meta($user->ID, '_uw_balance', true);
                                if (!empty($hasBalance)) {
                                    $json = $hasBalance;
                                } else {
                                    change_balance("0.00",$user->ID,'اعتبار اولیه کیف پول',$user->ID,0,'update');
//                                    update_user_meta($user->ID, "_uw_balance", "0.00");
                                    $json = get_user_meta($user->ID, '_uw_balance', true);
                                }
                                $user->credit = $json;

                            }
                            wp_send_json_success($user);
                        } catch (Exception $e) {
                            wp_send_json_error(array('error' => $e->getMessage()), 400);
                        }
                        break;
                    default:
                        wp_send_json_error(array('error' => 'method not allowed'), 405);
                }


            }
        }


        if (isset($wp->query_vars['appchar_login'])) {

            //header('Content-Type: application/json');
            if (!isset($_POST['username'])) {
                wp_send_json_error(array('error' => 'Username Required'), 400);
            }

            if (!isset($_POST['password'])) {
                wp_send_json_error(array('error' => 'Password Required'), 400);
            }
            //if(!isset($_POST['device_id'])) {
            //    wp_send_json_error(array('errors' => array('device_id.required')));
            //}

            $username = $_POST['username'];
            $password = $_POST['password'];

            $user = wp_authenticate($username, $password);

            if (is_wp_error($user)) {
//                print_r($user->get_error_code());
//                exit();
                switch ($user->get_error_code()){
                    case 'incorrect_password':
                        wp_send_json_error(array('error' => __('Your password is incorrect','appchar')), 400);
                        break;
                    case 'invalid_username':
                        wp_send_json_error(array('error' => __('Your username is incorrect','appchar')), 400);
                        break;
                    case 'wpau_confirmation_error':
                        wp_send_json_error(array('error' => __('Your account has to be confirmed by an administrator before you can login','appchar')), 400);
                        break;
                    default:
                        wp_send_json_error(array('error' => __('Sorry, there is a problem','appchar')), 400);
                        break;

                }
            }

            if (isset($_POST['device_id'])) {
                $device_id = $_POST['device_id'];
//                $useracc = get_user_by('user_login', $username);

                $wpdb->insert($wpdb->prefix . 'appchar_user_devices', array('user_device_id' => $device_id, 'user_id' => $user->ID));
            }

            if (AppcharExtension::extensionIsActive('wallet')) {
                $hasBalance = get_user_meta($user->ID, '_uw_balance', true);
                if (!empty($hasBalance)) {
                    $json = $hasBalance;
                } else {
                    change_balance("0.00",$user->ID,'اعتبار اولیه کیف پول',$user->ID,0,'update');

//                    update_user_meta($user->ID, "_uw_balance", "0.00");
                    $json = get_user_meta($user->ID, '_uw_balance', true);
                }
                $user->credit = $json;
            }
            //introduce to friends
            $refer_code = substr(md5($user->user_login), 0, 7);
            add_user_meta($user->ID, 'refer_code', $refer_code);
            //introduce to friends
            wp_send_json_success($user);
        }

        if (isset($wp->query_vars['appchar_logout'])) {

            if (!isset($_POST['device_id'])) {
                wp_send_json_error(array('error' => 'Device Id Required'), 400);
            }
            $app_id = $_POST['device_id'];
            $msgdb = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}appchar_user_devices WHERE user_device_id = %s", $app_id));

            wp_send_json_success($msgdb);
        }

        if (isset($wp->query_vars['appchar_reset_password'])) {

            //deactive recaptcha for application
            add_filter('wp_recaptcha_required', array($this, 'appchar_recaptcha_required'), 10);

            if (!isset($_POST['user_login'])) {
                wp_send_json_error(array('error' => 'Username or Email required'), 400);
            }
            $result = $this->appchar_retrieve_password();
            if (is_wp_error($result)) {
                wp_send_json_error(array('error' => 'Username or Email not found'), 400);
            }
            wp_send_json_success($result);
        }

        if (isset($wp->query_vars['appchar_change_password'])) {
            if (!isset($_POST['user_id'])) {
                wp_send_json_error(array('error' => 'User Id Required'), 400);
            }
            if (!isset($_POST['current_pass'])) {
                wp_send_json_error(array('error' => 'Current Password Required'), 400);
            }
            if (!isset($_POST['new_pass'])) {
                wp_send_json_error(array('error' => 'New Password Required'), 400);
            }
            $user_id = trim($_POST['user_id']);
            $currentpass = trim($_POST['current_pass']);
            $newpass = trim($_POST['new_pass']);
            $user_info = get_userdata($user_id);
            $username = $user_info->user_login;
            $user = wp_authenticate($username, $currentpass);

            if (is_wp_error($user)) {
                wp_send_json_error(array('error' => 'Bad Credentials'), 400);
            }
            wp_set_password($newpass, $user_id);
            wp_send_json_success('success');
        }

        if (isset($wp->query_vars['appchar_get_woocommerce_api_key'])) {
            if (!isset($_GET['secret'])) {
                wp_send_json_error(array('error' => 'secret.required'), 400);
            }

            if (md5($_GET['secret']) != 'fca29c63fb9c1535a6c04aee05711ecd') {
                wp_send_json_error(array('error' => 'secret.invalid'), 400);
            }
            wp_send_json(array('consumer_key' => ":D", 'consumer_secret' => ":D", 'token' => $token->get_token()));
        }

        if (isset($wp->query_vars['appchar_get_woocommerce_data'])) {
            global $woocommerce;
            $currency = get_option('woocommerce_currency');
            $payment_methods = $woocommerce->payment_gateways->get_available_payment_gateways();

            $woocommerce->shipping()->load_shipping_methods();
            $shipping_methods = $woocommerce->shipping()->get_shipping_methods();

            foreach ($shipping_methods as $key => $shipping_method) {
                if ($shipping_method->enabled != 'yes') {
                    unset($shipping_methods[$key]);
                }
            }

            $shipping_methods = array_values($shipping_methods);
            $payment_methods = array_values($payment_methods);

            wp_send_json(array('currency' => $currency, 'payment_methods' => $payment_methods, 'shipping_methods' => $shipping_methods));
        }

        if (isset($wp->query_vars['appchar_get_shipping_methods'])) {

            global $woocommerce;
            try {
                $package = array();
                $package['contents'] = array();
                $entity_body = json_decode(file_get_contents('php://input'));
                $line_items = $entity_body->line_items;

                $contents_cost = 0;
                if (is_array($line_items) || is_object($line_items))
                    foreach ($line_items as $line_item) {
                        $_content_data = array();
                        $_content_data['product_id'] = $line_item->product_id;
                        $_content_data['variation_id'] = $line_item->variation_id;
                        $_content_data['variation'] = $line_item->variation;
                        $_content_data['quantity'] = $line_item->quantity;
                        $_content_data['line_total'] = $line_item->line_total;
                        $_content_data['line_tax'] = 0;
                        $_content_data['line_subtotal'] = $line_item->quantity * $line_item->line_total;
                        $_content_data['line_subtotal_tax'] = 0;
                        $_content_data['line_tax_data'] = array('total' => array(), 'subtotal' => array());
                        $_content_data['data'] = wc_get_product($line_item->product_id);
                        $contents_cost += $_content_data['line_subtotal'];
                        $package['contents'][] = $_content_data;
                    }

                $package['contents_cost'] = $contents_cost;
                $package['applied_coupons'] = array();
                $package['user']['ID'] = $entity_body->user_id;
                $package['destination'] = array(
                    'country' => $entity_body->destination->country,
                    'state' => $entity_body->destination->state,
                    'postcode' => $entity_body->destination->postcode,
                    'city' => $entity_body->destination->city,
                    'address' => $entity_body->destination->address,
                    'address_2' => $entity_body->destination->address_2
                );
                $woocommerce->shipping()->calculate_shipping_for_package($package);
                $shipping_methods = $woocommerce->shipping()->get_shipping_methods();

                $plugins = get_plugins();
                $woocommerce_data = $plugins['woocommerce/woocommerce.php'];
                $i = true;
                foreach ($shipping_methods as $key => $shipping_method) {
                    if ($shipping_method->enabled != 'yes') {
                        unset($shipping_methods[$key]);
                        continue;
                    } else {
                        if (floatval($woocommerce_data['Version']) < 2.6) {
                            if ($shipping_method->id == 'free_shipping') {
                                if ($shipping_method->min_amount > $contents_cost) {
                                    unset($shipping_methods[$key]);
                                } else {
                                    $shipping_method->rates[] = array(
                                        "id" => $shipping_method->id,
                                        "label" => $shipping_method->title,
                                        "cost" => 0,
                                        "taxes" => array(),
                                        "method_id" => $shipping_method->id
                                    );
                                }
                            }
                        } else {
                            if (count($shipping_method->rates) == 0) {
                                unset($shipping_methods[$key]);
                                continue;
                            } else {
                                if ($i) {
                                    if ($shipping_method->id == 'free_shipping') {
                                        $i = false;
                                        if ($shipping_method->min_amount > $contents_cost) {
                                            unset($shipping_methods[$key]);
                                            continue;
                                        }
                                    }
                                    $rate = $shipping_method->rates;
                                    $shipping_method->rates = '';
                                    foreach ($rate as $key => $value) {
                                        $shipping_method->rates[] = $value;
                                    }
                                }
                            }

                        }
                    }
                }
                if ($entity_body->version == 2) {
                    $method = array();
                    foreach ($shipping_methods as $methods) {
                        $method[] = $methods;
                    }
                    $shipping_methods = $method;
                }
                wp_send_json_success(array('shipping_methods' => $shipping_methods, 'woocommerce_checkout_pay_endpoint' => get_option('woocommerce_checkout_pay_endpoint')));

            } catch (\Exception $e) {
                wp_send_json_error(array('message' => $e->getMessage()), 400);
            }
        }

        if (isset($wp->query_vars['appchar_get_shipping_methods_v2'])) {
            try {
                $entity_body = json_decode(file_get_contents('php://input'));
                $line_items = $entity_body->line_items;
                if (is_array($line_items) || is_object($line_items)) {
                    foreach ($line_items as $line_item) {
                        WC()->cart->add_to_cart($line_item->product_id, $line_item->quantity);
                    }
                }
                self::update_order_review($entity_body, 'shipping');
            } catch (\Exception $e) {
                wp_send_json_error(array('message' => $e->getMessage()), 400);
            }
        }

        if (isset($wp->query_vars['appchar_get_payment_methods'])) {
            global $woocommerce;
            $currency = get_option('woocommerce_currency');
            $payment_methods = $woocommerce->payment_gateways->get_available_payment_gateways();
            if ($_POST['version'] == 2) {
                foreach ($payment_methods as $methods) {
                    $method[] = $methods;
                }
                $payment_methods = $method;
            }
            wp_send_json(array('currency' => $currency, 'payment_methods' => $payment_methods));
        }
        if (isset($wp->query_vars['appchar_get_payment_methods_v2'])) {
            $entity_body = json_decode(file_get_contents('php://input'));
            $line_items = $entity_body->line_items;
            if (is_array($line_items) || is_object($line_items)) {
                foreach ($line_items as $line_item) {
                    WC()->cart->add_to_cart($line_item->product_id, $line_item->quantity);
                }
            }
            self::update_order_review($entity_body, 'payment');
            if (WC()->cart->needs_payment()) {
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            }
            wp_send_json(array('currency' => $currency, 'payment_methods' => $available_gateways));
        }

        if (isset($wp->query_vars['appchar_apply_coupon'])) {
            $entity_body = json_decode(file_get_contents('php://input'));

//        if(!isset($_POST['coupon_code'])){
//            wp_send_json_error('coupon_code .required');
//        }

            WC()->cart->empty_cart();
            if (!isset($entity_body->line_items)) {
                wp_send_json_error(array('error' => 'order required'), 400);
            }
            $line_items = $entity_body->line_items;
            $total_price = 0;
            $qtt = 0;
            foreach ($line_items as $product) {
                $qtt += $product->quantity;
                $total_price += $product->line_subtotal;
                WC()->cart->add_to_cart($product->product_id, $product->quantity);
            }
            if (!isset($entity_body->coupon_code)) {
                wp_send_json_error(array('error' => 'coupon code required'), 400);
            }
            $coupon_code = $entity_body->coupon_code;
            $the_coupon = new WC_Coupon($coupon_code);
//-------------------------------------------------
            if (!$the_coupon->is_valid()) {
                if (!$the_coupon->exists) {
                    wp_send_json_error(array('error' => sprintf(__('Coupon "%s" does not exist!', 'woocommerce'), $coupon_code)), 400);
                }
                if ($the_coupon->usage_limit > 0 && $the_coupon->usage_count >= $the_coupon->usage_limit) {
                    wp_send_json_error(array('error' => __('Coupon usage limit has been reached.', 'woocommerce')), 400);
                }
                if ($the_coupon->expiry_date && current_time('timestamp') > $the_coupon->expiry_date) {
                    wp_send_json_error(array('error' => __('This coupon has expired.', 'woocommerce')), 400);
                }
                if ($the_coupon->minimum_amount > 0 && apply_filters('woocommerce_coupon_validate_minimum_amount', wc_format_decimal($the_coupon->minimum_amount) > $line_items[0]->line_subtotal, $this)) {
                    wp_send_json_error(array('error' => sprintf(__('The minimum spend for this coupon is %s.', 'woocommerce'), $the_coupon->minimum_amount)), 400);
                }
                if ($the_coupon->maximum_amount > 0 && apply_filters('woocommerce_coupon_validate_maximum_amount', wc_format_decimal($the_coupon->maximum_amount) < $line_items[0]->line_subtotal, $this)) {
                    wp_send_json_error(array('error' => sprintf(__('The maximum spend for this coupon is %s.', 'woocommerce'), $the_coupon->minimum_amount)), 400);
                }
                if (sizeof($the_coupon->product_ids) > 0) {
                    $valid_for_cart = false;
                    if (!$line_items) {
                        foreach ($line_items as $cart_item_key => $cart_item) {
                            if (in_array($cart_item->product_id, $the_coupon->product_ids) || in_array($cart_item->variation_id, $the_coupon->product_ids) || in_array($cart_item->data->get_parent(), $the_coupon->product_ids)) {
                                $valid_for_cart = true;
                            }
                        }
                    }
                    if (!$valid_for_cart) {
                        wp_send_json_error(array('error' => __('Sorry, this coupon is not applicable to your cart contents.', 'woocommerce')), 400);
                    }
                }
                if (sizeof($the_coupon->product_categories) > 0) {
                    $valid_for_cart = false;
                    if (!$line_items) {
                        foreach ($line_items as $cart_item_key => $cart_item) {
                            $product_cats = wc_get_product_cat_ids($cart_item->product_id);

                            // If we find an item with a cat in our allowed cat list, the coupon is valid
                            if (sizeof(array_intersect($product_cats, $the_coupon->product_categories)) > 0) {
                                $valid_for_cart = true;
                            }
                        }
                    }
                    if (!$valid_for_cart) {
                        wp_send_json_error(array('error' => __('Sorry, this coupon is not applicable to your cart contents.', 'woocommerce')), 400);
                    }
                }
                if ('yes' === $the_coupon->exclude_sale_items && $the_coupon->is_type(wc_get_product_coupon_types())) {
                    $valid_for_cart = false;
                    $product_ids_on_sale = wc_get_product_ids_on_sale();

                    if (!$line_items) {
                        foreach ($line_items as $cart_item_key => $cart_item) {
                            if (!empty($cart_item->variation_id)) {
                                if (!in_array($cart_item->variation_id, $product_ids_on_sale, true)) {
                                    $valid_for_cart = true;
                                }
                            } elseif (!in_array($cart_item->product_id, $product_ids_on_sale, true)) {
                                $valid_for_cart = true;
                            }
                        }
                    }
                    if (!$valid_for_cart) {
                        wp_send_json_error(array('error' => __('Sorry, this coupon is not valid for sale items.', 'woocommerce')), 400);
                    }
                }
                if (!$line_items && $the_coupon->is_type(wc_get_product_coupon_types())) {
                    $valid = false;

                    foreach ($line_items as $cart_item_key => $cart_item) {
                        if ($the_coupon->is_valid_for_product($cart_item->data, $cart_item)) {
                            $valid = true;
                            break;
                        }
                    }

                    if (!$valid) {
                        wp_send_json_error(array('error' => __('Sorry, this coupon is not applicable to your cart contents.', 'woocommerce')), 400);
                    }
                }
                if (!$the_coupon->is_type(wc_get_product_coupon_types())) {
                    if (sizeof($the_coupon->exclude_product_ids) > 0) {
                        $valid_for_cart = true;
                        if (!$line_items) {
                            foreach ($line_items as $cart_item_key => $cart_item) {
                                if (in_array($cart_item->product_id, $the_coupon->exclude_product_ids) || in_array($cart_item->variation_id, $the_coupon->exclude_product_ids) || in_array($cart_item->data->get_parent(), $the_coupon->exclude_product_ids)) {
                                    $valid_for_cart = false;
                                }
                            }
                        }
                        if (!$valid_for_cart) {
                            $products = array();
                            if (!$line_items) {
                                foreach ($line_items as $cart_item_key => $cart_item) {
                                    if (in_array($cart_item->product_id, $the_coupon->exclude_product_ids) || in_array($cart_item->variation_id, $the_coupon->exclude_product_ids) || in_array($cart_item->data->get_parent(), $the_coupon->exclude_product_ids)) {
                                        $product = wc_get_product($cart_item->product_id);
                                        $products[] = $product->get_title();
                                    }
                                }
                            }

                            wp_send_json_error(array('error' => sprintf(__('Sorry, this coupon is not applicable to the products: %s.', 'woocommerce'), implode(', ', $products))), 400);
                        }
                    }
                    if (sizeof($the_coupon->exclude_product_categories) > 0) {
                        $valid_for_cart = true;
                        if ($line_items) {
                            foreach ($line_items as $cart_item_key => $cart_item) {

                                $product_cats = wc_get_product_cat_ids($cart_item->product_id);

                                if (sizeof(array_intersect($product_cats, $the_coupon->exclude_product_categories)) > 0) {
                                    $valid_for_cart = false;
                                }
                            }
                        }
                        if (!$valid_for_cart) {
                            $categories = array();
                            if ($line_items) {
                                foreach ($line_items as $cart_item_key => $cart_item) {
                                    $product_cats = wc_get_product_cat_ids($cart_item->product_id);

                                    if (sizeof($intersect = array_intersect($product_cats, $the_coupon->exclude_product_categories)) > 0) {

                                        foreach ($intersect as $cat_id) {
                                            $cat = get_term($cat_id, 'product_cat');
                                            $categories[] = $cat->name;
                                        }
                                    }
                                }
                            }

                            wp_send_json_error(array('error' => sprintf(__('Sorry, this coupon is not applicable to the categories: %s.', 'woocommerce'), implode(', ', array_unique($categories)))), 400);
                        }
                    }
                    if ($the_coupon->exclude_sale_items == 'yes') {
                        $valid_for_cart = true;
                        $product_ids_on_sale = wc_get_product_ids_on_sale();
                        if ($line_items) {
                            foreach ($line_items as $cart_item_key => $cart_item) {
                                if (!empty($cart_item->variation_id)) {
                                    if (in_array($cart_item->variation_id, $product_ids_on_sale, true)) {
                                        $valid_for_cart = false;
                                    }
                                } elseif (in_array($cart_item->product_id, $product_ids_on_sale, true)) {
                                    $valid_for_cart = false;
                                }
                            }
                        }
                        if (!$valid_for_cart) {
                            wp_send_json_error(array('error' => __('Sorry, this coupon is not valid for sale items.', 'woocommerce')), 400);
                        }
                    }
                }
            } else {
                switch ($the_coupon->discount_type) {
                    case 'fixed_cart':
                        $price = $the_coupon->coupon_amount;
                        break;
                    case 'percent':
                        $price = $total_price * ($the_coupon->coupon_amount / 100);
                        break;
                    case 'fixed_product':
                        $price = $qtt * $the_coupon->coupon_amount;
                        break;
                    case 'percent_product':
                        $price = $total_price * ($the_coupon->coupon_amount / 100);
                        break;
                }
                wp_send_json_success(array('code' => $the_coupon->code, 'id' => $the_coupon->get_id(), 'amount' => $price));
            }
            wp_send_json_error(array('error' => __('coupon not valid!', 'appchar')), 400);
        }

        if (isset($wp->query_vars['appchar_get_order_payment_url'])) {
            //wp_send_json(['order_id' => $_POST['order_id']]);
            if (!isset($_POST['order_id'])) {
                wp_send_json_error(array('error' => 'order_id Required'), 400);
            }

            try {
                $order = wc_get_order($_POST['order_id']);
                if (!$order) {
                    wp_send_json_error(array('error' => 'there no order'), 400);
                }
                $url = $order->get_checkout_payment_url();
                $url = str_replace('pay_for_order=true&', '', $url);

                wp_send_json_success(array('pay_url' => $url));
            } catch (\Exception $e) {
                wp_send_json_error(array('error' => $e->getMessage()), 400);
            }
        }

        if (isset($wp->query_vars['appchar_post_comment'])) {


            if (!isset($_POST['comment_content'])) {
                wp_send_json_error(array('error' => array('comment.required')), 400);
            }
            if (!isset($_POST['post_id'])) {
                wp_send_json_error(array('error' => array('post id.required')), 400);
            }

            if (FALSE === get_post_status($_POST['post_id'])) {
                wp_send_json_error(array('error' => array('post id is not valid!')), 400);
            }
            $time = current_time('mysql');
            $data = array(
                'comment_post_ID' => trim($_POST['post_id']),
                'comment_author_url' => '',
                'comment_content' => trim($_POST['comment_content']),
                'comment_type' => '',
                'comment_parent' => 0,
                'comment_author_IP' => '',
                'comment_agent' => 'appchar',
                'comment_date' => $time,
                'comment_approved' => 0,
            );
            if (isset($_POST['user_id'])) {
                $author_id = trim($_POST['user_id']);
                $author = get_user_by('id', $author_id);
                if (!$author) {
                    wp_send_json_error(array('error' => 'user_id is not valid!'), 400);
                }
                $data['comment_author'] = $author->user_login;
                $data['comment_author_email'] = $author->user_email;
                $data['user_id'] = $author_id;
            } elseif (isset($_POST['comment_author']) && isset($_POST['comment_author_email'])) {
                $data['comment_author'] = trim($_POST['comment_author']);
                $data['comment_author_email'] = trim($_POST['comment_author_email']);
                $data['user_id'] = '0';
            } else {
                wp_send_json_error(array('error' => 'author and author email required'), 400);
            }
            wp_insert_comment($data);
            wp_send_json(json_encode($data));
        }

        if (AppcharExtension::extensionIsActive('wallet')) {
            if (isset($wp->query_vars['appchar_pay_with_credit'])) {
                if (!isset($_POST['user_id']) && !isset($_POST['order_id'])) {
                    wp_send_json_error(array('error_code' => -7, 'error' => 'user_id and order_id required'), 400);
                }
                $uid = trim($_POST['user_id']);
                $oid = trim($_POST['order_id']);
                $order = new WC_Order($oid);
                if (!$order->status) {
                    wp_send_json_error(array('error_code' => -6, 'error' => 'order_id is not valid'), 400);
                }
                $user = get_user_by('id', $uid);
                if (!$user) {
                    wp_send_json_error(array('error_code' => -4, 'error' => 'user_id is not valid'), 400);
                }
                if ($order->customer_user != $uid) {
                    wp_send_json_error(array('error_code' => -3, 'error' => 'This order is not owned by the user'), 400);
                }
                if ($order->status != 'pending') {
                    wp_send_json_error(array('error_code' => -2, 'error' => 'This order already paid'), 400);
                }
                $total_price = $order->get_total();
                $balance = get_user_meta($uid, '_uw_balance', true);
                if (!$balance) {
                    change_balance("0.00",$user->ID,'اعتبار اولیه کیف پول',$user->ID,0,'update');
//                    update_user_meta($user->ID, "_uw_balance", "0.00");
                    $balance = 0;
                }
                if ($balance < $total_price) {
                    wp_send_json_error(array('error_code' => -1, 'error' => 'Your credit is not enough'), 400);
                }
                $credit = $balance - $total_price;
                change_balance($total_price,$uid,'پرداخت از طریق کیف پول',$uid,$oid);

//                update_user_meta($uid, "_uw_balance", $credit);
                $order->update_status('processing', 'order_note');
                wp_send_json_success(array('result_code' => 1, 'result' => array('Order was paid')));
            }
            if (isset($wp->query_vars['get_wallet_credit'])) {
                if ($wp->query_vars['get_wallet_credit'] == '') {
                    wp_send_json_error(array('error' => 'username required'), 400);
                }
                $username = $wp->query_vars['get_wallet_credit'];
                $useracc = get_userdatabylogin($username);
                if (!$useracc) {
                    wp_send_json_error(array('error' => 'this user not exist!'), 400);
                }
                $hasBalance = get_user_meta($useracc->ID, '_uw_balance', true);
                if (!empty($hasBalance)) {
                    $json = $hasBalance;
                } else {
                    change_balance("0.00",$useracc->ID,'اعتبار اولیه کیف پول',$useracc->ID,0,'update');
//                    update_user_meta($useracc->ID, "_uw_balance", "0.00");
                    $json = get_user_meta($useracc->ID, '_uw_balance', true);
                }
                wp_send_json_success($json);
            }
            if (isset($wp->query_vars['increase_wallet_credit'])) {
                $json = array();
                $args = array(
                    'post_type' => 'product',
                    'product_cat' => 'credit'
                );
                $loop = new WP_Query($args);
                while ($loop->have_posts()) :
                    $loop->the_post();
                    $json[] = $this->appchar_get_product_by_id(get_the_ID());
                endwhile;
                wp_reset_query();
                wp_send_json($json);
            }
        }

        if (isset($wp->query_vars['appchar_get_product_id_by_sku'])) {
            if (!isset($_POST['sku'])) {
                wp_send_json_error(array('error' => 'sku.required'), 400);
            }
            $sku = trim($_POST['sku']);
            $productid = $this->appchar_get_productid_by_sku($sku, false);
            if (!$productid) {
                wp_send_json_error(array('error' => 'product id not found'), 400);
            }
            wp_send_json_success($productid);
        }

        if (isset($wp->query_vars['appchar_get_product_by_ids'])) {
            if (!isset($_POST['ids'])) {
                wp_send_json_error(array('IDs.required'), 400);
            }
            $IDs = explode(",", $_POST['ids']);
            foreach ($IDs as $id) {
                $json[] = $this->appchar_get_product_by_id($id);
            }
            wp_send_json_success($json);
        }

        if (isset($wp->query_vars['appchar_get_product_variation'])) {
            $id = $wp->query_vars['appchar_get_product_variation'];
            $_product = wc_get_product($id);
            $available_variations = array();
            foreach ($_product->get_children() as $child_id) {
                $variation = $_product->get_child($child_id);

                // Hide out of stock variations if 'Hide out of stock items from the catalog' is checked
                if (empty($variation->variation_id) || ('yes' === get_option('woocommerce_hide_out_of_stock_items') && !$variation->is_in_stock())) {
                    continue;
                }

                // Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price)
                if (apply_filters('woocommerce_hide_invisible_variations', false, $_product->get_id(), $variation) && !$variation->variation_is_visible()) {
                    continue;
                }

                $available_variations[] = $_product->get_available_variation($variation);
            }
            $available_variations2 = array();
            foreach ($available_variations as $available_variation) {
                $atts = array();
                foreach ($available_variation['attributes'] as $key => $att) {
                    $key = str_replace('attribute_', '', $key);
                    $atts[] = array(
                        'name' => $key,
                        'value' => $att
                    );
                }
                $available_variation['attributes_array'] = $atts;

                $product_for_download = $this->appchar_get_product_by_id($id);
                if ($product_for_download) {
                    foreach ($product_for_download['variations'] as $variations_download) {
                        if ($variations_download['id'] == $available_variation['variation_id']) {
                            $available_variation['downloads'] = $variations_download['downloads'];
                            $available_variation['price'] = $variations_download['price'];
                            $available_variation['regular_price'] = $variations_download['regular_price'];
                            $available_variation['sale_price'] = $variations_download['sale_price'];
                            $available_variation['on_sale'] = $variations_download['on_sale'];
                        }
                    }
                }
                $available_variation['managing_stock'] = (get_post_meta($available_variation['variation_id'],'_manage_stock',true)=='yes')?true:false;
                $available_variation['stock_quantity'] = get_post_meta($available_variation['variation_id'],'_stock',true) == "" ? null : intval(get_post_meta($available_variation['variation_id'],'_stock',true));
                $available_variation['backorder'] = get_post_meta($available_variation['variation_id'],'_backorders',true);

                $available_variation['_sale_price_dates_from'] = (get_post_meta($available_variation['variation_id'], '_sale_price_dates_from', true) != "") ? doubleval(get_post_meta($available_variation['variation_id'], '_sale_price_dates_from', true)) : null;
                $available_variation['_sale_price_dates_to'] = (get_post_meta($available_variation['variation_id'], '_sale_price_dates_to', true) != "") ? doubleval(get_post_meta($available_variation['variation_id'], '_sale_price_dates_to', true)) : null;

                $available_variations2[] = $available_variation;
            }

            $json['variations'] = $available_variations2;
            //-------------------------------------------------
            if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                global $sitepress;
                if (isset($_GET['locale'])) {
                    $lang = $_GET['locale'];
                } else {
                    if (defined('ICL_LANGUAGE_CODE')) {
                        $lang = ICL_LANGUAGE_CODE;
                    } else {
                        $lang = 'fa';
                    }
                }
                $sitepress->switch_lang($lang);
            }

            $product = new WC_Product($id);
            $attributes = $product->get_attributes();
            $_attr = array();
            foreach ($attributes as $key => $attribute) {
                if ($attribute['is_variation'] == 1) {
                    $key = urldecode($key);
                    $terms = wc_get_product_terms($id, $key, array('fields' => 'all'));
                    if (!$terms) {
                        $_terms = explode(" | ", $attribute['value']);
                        foreach ($_terms as $term) {
                            $terms[] = array(
                                'name' => $term,
                                'slug' => $term
                            );
                        }
                    }
                    $attribute['value'] = $product->get_attribute($key);
                    $attribute['value'] = str_replace(', ', ' | ', $attribute['value']);
                    $_attr[] = array(
                        'slug' => $key,
                        'name' => wc_attribute_label($attribute['name']),//$attribute->get_title()
                        'options' => $terms
                    );
                }
            }
            $json['attributes'] = $_attr;
            wp_send_json_success($json);
        }


        if (isset($wp->query_vars['appchar_get_customer'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $customer_id = $wp->query_vars['appchar_customer_id'];
                    $result = $this->customer->get_customer($customer_id);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 404);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_update_customer'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    $customer_id = $wp->query_vars['appchar_customer_id'];
                    $entity_body = json_decode(file_get_contents('php://input'));
                    $arr = array();
                    $arr = objToArray($entity_body, $arr);
                    $result = $this->customer->edit_customer($customer_id, $arr);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_customer_orders'])) {
            $this->authorizeAppcharToken();
            $customer_id = $wp->query_vars['appchar_customer_id'];
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $result = $this->customer->get_customer_orders($customer_id, null, array(), isset($_GET['page']) ? $_GET['page'] : 1);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 404);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_balance_log'])) {
            $this->authorizeAppcharToken();
            $customer_id = $wp->query_vars['appchar_customer_id'];
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $results = $this->customer->get_customer_balance_logs($customer_id, array('id','price','user_id','transaction_type','by_who','order_id','how','created_at'),array(), isset($_GET['page']) ? $_GET['page'] : 1, isset($_GET['limit'])?$_GET['limit']:20);
                    foreach ($results as &$result){
                        if($result->order_id!=0){
                            $order = wc_get_order($result->order_id);
                            $result->total_order = $order->get_total();
                        }else{
                            $result->total_order = "0";
                        }
                        $user = get_user_by('id',$result->by_who);
                        $result -> by_user = $user->user_login;
                    }
                    $json['logs'] = $results;
                    if (is_wp_error($json)) {
                        wp_send_json_error(array('error' => $json->get_error_message()), 404);
                    } else {
                        wp_send_json($json);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_create_customer'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    add_filter('wp_recaptcha_required', array($this, 'appchar_recaptcha_required'), 10);
                    $entity_body = json_decode(file_get_contents('php://input'));
                    $_POST['woocommerce-register-nonce']= wp_create_nonce( "woocommerce-register" );
                    $_POST['_wp_http_referer'] = esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) );
                    $arr = array();
                    $arr = objToArray($entity_body, $arr);
                    $result = $this->customer->create_customer($arr);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        if (isset($arr['device_id'])) {
                            $device_id = $arr['device_id'];
//                            $useracc = get_user_by('user_login', $result['customer']['id']);

                            $wpdb->insert($wpdb->prefix . 'appchar_user_devices', array('user_device_id' => $device_id, 'user_id' => $result['customer']['id']));
                        }
                        //introduce to friends
                        $refer_code = substr(md5($result['customer']['username']), 0, 7);
                        add_user_meta($result['customer']['id'], 'refer_code', $refer_code);
                        if (AppcharExtension::extensionIsActive('introduce_to_friends')) {
                            if (isset($arr['customer']['refer_code'])) {
                                appchar_itf_registration_save($result['customer']['id'], $arr['customer']['refer_code']);
                            }
                        }
                        //introduce to friends
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_product_terms'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":

                    $args = array(
                        'taxonomy'      => 'appchar_filter',
                        'hide_empty'    => true,
                        'parent'        => 0,
                    );
                    if(isset($_GET['taxonomy'])){
                        $args['taxonomy'] = trim($_GET['taxonomy']);
                    }
                    if(isset($_GET['parent'])){
                        $args['parent'] = trim($_GET['parent']);
                    }
                    $results = get_terms($args);
                    foreach($results as &$result){
                        $children = get_terms( array(
                            'taxonomy'      => 'appchar_filter',
                            'parent'    => $result->term_id,
                            'hide_empty' => true
                        ) );
                        $result->has_child = ($children)?true:false;
                    }
                    $result2 = apply_filters('appchar_get_product_terms', $results);
                    if ($result2) {
                        $results = $result2;
                    }
                    if (is_wp_error($results)) {
                        wp_send_json_error(array('error' => $results->get_error_message()), 400);
                    } else {
                        wp_send_json_success($results);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_product_categories'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $result = $this->products->get_product_categories();
                    $result2 = apply_filters('appchar_get_product_categories', $result);
                    if ($result2) {
                        $result = $result2;
                    }
                    foreach ($result as $k => $value) {
                      foreach ($value as $key => $v) {
                        if ($v['image'] == '') {
                          $result[$k][$key]['image'] = APPCHAR_IMG_URL . 'image-placeholder-icon-6.jpg';
                        }
                      }
                    }
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        if (isset($wp->query_vars['appchar_get_product_categories_v2'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $parent = (isset($_GET['parent'])) ? intval($_GET['parent']) : 0;
                    $result = $this->get_product_categories_v2($parent);
                    $result2 = apply_filters('appchar_get_product_categories_v2', $result);
                    if ($result2) {
                        $result = $result2;
                    }
                    foreach ($result as $k => $value) {
                      foreach ($value as $key => $v) {
                        if ($v['image'] == '') {
                          $result[$k][$key]['image'] = APPCHAR_IMG_URL . 'image-placeholder-icon-6.jpg';
                        }
                      }
                    }
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        if (isset($wp->query_vars['appchar_get_product_categories_sticky'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $result = $this->get_product_categories_sticky();
                    $result2 = apply_filters('appchar_get_product_categories_sticky', $result);
                    if ($result2) {
                        $result = $result2;
                    }
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }


        if (isset($wp->query_vars['appchar_get_products_attributes'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $result = $this->products->get_product_attributes();
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_product'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $product_id = $wp->query_vars['appchar_get_product'];
                    $result = $this->appchar_get_product_by_id($product_id);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json(array('product' => $result));
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_product_reviews'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $product_id = $wp->query_vars['appchar_get_product_reviews'];
                    $result = $this->products->get_product_reviews($product_id);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_product_reviews_v2'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $product_id = $wp->query_vars['appchar_get_product_reviews_v2'];
                    $result = $this->products->get_product_reviews_with_child($product_id);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_product_attributes'])) {
            $this->authorizeAppcharToken();
            $id = $wp->query_vars['appchar_get_product_attributes'];
            $product = new WC_Product($id);
            $attributes = $product->get_attributes();
            foreach ($attributes as $attr => $attr_deets) {
                $attributes[$attr]['value'] = $product->get_attribute($attr);
                $attributes[$attr]['value'] = str_replace("|", ',', $attributes[$attr]['value']);
            }
            wp_send_json_success($attributes);
        }

        if (isset($wp->query_vars['appchar_get_products'])) {

            $json = $this->getProducts();
            wp_send_json_success($json);
        }

        if (isset($wp->query_vars['appchar_get_products_new'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    try {
                        $result = $this->getProducts();
                        $obj = new stdClass();
                        $obj->products = $result;
                        wp_send_json($obj);
                    } catch (\Exception $e) {
                        wp_send_json_error(array('error' => $e->getMessage()), 400);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_order'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $order_id = $wp->query_vars['appchar_get_order'];
                    $result = $this->order->get_order($order_id);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        $order = new WC_Order($result['order']['id']);
                        $pay_url = $this->getOrderPayUrl($order);
                        $result['order']['needs_payment'] = $order->needs_payment();
                        $result['order']['pay_url'] = $pay_url;
                        $result['order']['received_url'] = $order->get_checkout_order_received_url();
                        //wp_send_json(apply_filters( 'appchar_get_single_order', $result )); //changed because of duplicate meta in order history
                        wp_send_json( $result );
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_order_statuses'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $result = $this->order->get_order_statuses();
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_order'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    $entity_body = json_decode(file_get_contents('php://input'));
                    $arr = array();
                    $arr = objToArray($entity_body, $arr);
                    $result = $this->order->create_order($arr);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        $order = new WC_Order($result['order']['id']);
                        $pay_with_credit = false;
                        if ($result['order']['payment_details']['method_id'] == 'wpuw' && AppcharExtension::extensionIsActive('wallet')) {
                            $pay_with_credit = true;
                            $userId = $result['order']['customer_id'];
                            $user = get_user_by('id', $userId);
                            if (!$order->status) {
                                wp_send_json_error(array('error_code' => -6, 'error' => 'order_id is not valid'), 400);
                            }
                            if (!$user) {
                                wp_send_json_error(array('error_code' => -4, 'error' => 'user_id is not valid'), 400);
                            }
                            if ($order->status != 'pending') {
                                wp_send_json_error(array('error_code' => -2, 'error' => 'This order already paid'), 400);
                            }
                            $total_price = $order->get_total();
                            $balance = get_user_meta($userId, '_uw_balance', true);
                            if (!$balance) {
                                change_balance("0.00",$userId,'اعتبار اولیه کیف پول',$userId,0,'update');
//                                update_user_meta($userId, "_uw_balance", "0.00");
                                $balance = 0;
                            }
                            if ($balance < $total_price) {
                                wp_send_json_error(array('error_code' => -1, 'error' => 'Your credit is not enough'), 400);
                            }
                            $credit = $balance - $total_price;
                            change_balance($total_price,$userId,'پرداخت از طریق کیف پول',$userId,$result['order']['id']);

//                            update_user_meta($userId, "_uw_balance", $credit);
                            $order->update_status('processing', 'order_note');
                        } else {
                            wc_get_payment_gateway_by_order($order)->process_payment($order->get_id());
                        }
                        $result['order']['needs_payment'] = $order->needs_payment();
                        $result['order']['pay_with_credit'] = $pay_with_credit;

                        $pay_url = $this->getOrderPayUrl($order);
                        $result['order']['pay_url'] = $pay_url;
                        $result['order']['received_url'] = $order->get_checkout_order_received_url();
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_product_can_add_to_cart'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    $entity_body = json_decode(file_get_contents('php://input'));
                    if (!$entity_body) {
                        wp_send_json_error(array('error' => __('لطفا ایدی محصولات را تعیین کنید', 'appchar')), 400);
                    }
                    $product_1 = $entity_body->product_1_id;
                    $product_2 = $entity_body->product_2_id;
                    if (get_post_meta($product_1, '_virtual', true) == get_post_meta($product_2, '_virtual', true)) {
                        wp_send_json_success(true);
                    } else {
                        wp_send_json_error(array('error' => __('محصول دانلودی با محصول فیزیکی همزمان نمی توانند اضافه شوند', 'appchar')), 400);
                    }
                    break;
            }
        }

        //New Pooya

        //appchar_cart_create
        if (isset($wp->query_vars['appchar_cart_create'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    define('WOOCOMMERCE_CART', true);
                    wc_clear_notices();
                    $entity_body = json_decode(file_get_contents('php://input'));

                    if (isset($entity_body->customer_id) && $entity_body->customer_id) {
                        wp_set_current_user($entity_body->customer_id);
                    }

                    WC()->cart->empty_cart(true);

                    if (isset($entity_body->products) && is_array($entity_body->products)) {
                        foreach ($entity_body->products as $key => $product) {
                            $cart_result = array();
                            $cart_result = Appchar_Cart_Handler::add_to_cart_action(false, (array)$product);
                            if($cart_result['status']) {
                                $entity_body->products[$key]->cart_item_key = $cart_result['cart_item_key'];
                                continue;
                            }else {
                                if(wc_notice_count('error') > 0) {
                                    unset($entity_body->products[$key]);
                                    $entity_body->products = array_values($entity_body->products);
                                    wc_clear_notices();
                                }
                            }
                        }
                    }

                    if (isset($entity_body->coupon_code) && $entity_body->coupon_code) {
                        WC()->cart->remove_coupons();
                        WC()->cart->add_discount(sanitize_text_field($entity_body->coupon_code));
                    } else {
                        WC()->cart->remove_coupons();
                    }

                    WC()->cart->set_session();
                    WC()->cart->calculate_totals();

//                    if(wc_notice_count('error') > 0) {
//                        $error = wc_notice_count('error') ? reset(wc_get_notices('error')) : 'سبد خرید نا معتبر است';
//                        wp_send_json_error(array('error' => $error['notice']), 400);
//                    }

                    wp_send_json_success(array(
                        'coupon_code' => (WC()->cart->applied_coupons && is_array(WC()->cart->applied_coupons)) ? reset(WC()->cart->applied_coupons) : null,
                        'discount_cart' => WC()->cart->discount_cart ? WC()->cart->discount_cart : 0,
                        'shipping_total' => WC()->cart->shipping_total ? WC()->cart->shipping_total : 0,
                        'subtotal' => WC()->cart->subtotal ? WC()->cart->subtotal : 0,
                        'total' => WC()->cart->total ? WC()->cart->total : 0,
                        'cart_content_total' => WC()->cart->cart_contents_total ? WC()->cart->cart_contents_total : 0,
                        'line_items' => $this->getCartLineItems($entity_body),
                        'needs_shipping_address' => WC()->cart->needs_shipping_address(),
                        'needs_payment' => WC()->cart->needs_payment(),
                        'needs_shipping' => WC()->cart->needs_shipping(),
                        'show_shipping' => WC()->cart->show_shipping(),
                        'error' => (wc_notice_count('error') > 0) ? reset(wc_get_notices('error')) : '',
                    ));
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_cart_add_to_cart'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    define('WOOCOMMERCE_CART', true);

                    wc_clear_notices();

                    $entity_body = json_decode(file_get_contents('php://input'));


                    if (isset($entity_body->customer_id) && $entity_body->customer_id) {
                        wp_set_current_user($entity_body->customer_id);
                    }

                    WC()->cart->empty_cart(true);

                    if (isset($entity_body->products) && is_array($entity_body->products)) {
                        foreach ($entity_body->products as $key => $product) {
                            $cart_result = array();
                            $cart_result = Appchar_Cart_Handler::add_to_cart_action(false, (array)$product);
                            if($cart_result['status']) {
                                $entity_body->products[$key]->cart_item_key = $cart_result['cart_item_key'];
                                continue;
                            }else {
                                if(wc_notice_count('error') > 0) {
                                    unset($entity_body->products[$key]);
                                    $entity_body->products = array_values($entity_body->products);
                                    wc_clear_notices();
                                }
                            }
                        }
                    }

                    if (!isset($entity_body->product_to_add) || !$entity_body->product_to_add) {
                        wp_send_json_error(array('error' => 'محصولی برای اضافه شدن به سبد خرید انتخاب نشده است'), 400);
                    }
                    // foreach($entity_body->product_to_add as $pKey => $pValue) {
                    //     if(strpos($pKey, "attribute_pa_") !== false) {
                    //         $entity_body->product_to_add[$pKey] = urldecode($pValue);
                    //     }
                    // }
                    $res = Appchar_Cart_Handler::add_to_cart_action(false, (array)$entity_body->product_to_add);
                    $general_setting = get_option('appchar_general_setting', array());
                    $cart_button_type = (isset($general_setting['cart_button_type'])) ? $general_setting['cart_button_type'] : 'simple_button';
                    if (!$res || wc_notice_count('error') > 0) {
                        $error = wc_notice_count('error') ? reset(wc_get_notices('error')) : 'محصول به سبد خرید اضافه نشد';
                        if(strpos($error['notice'], "شما نمی توانید این مقدار را به سبد خرید خود بیافزایید") !== true && $cart_button_type == "keyboard") {

                        }else {
                            wp_send_json_error(array('error' => str_replace("مشاهده سبد خرید", "", $error['notice']), 400));
                        }
                    }
                    $entity_body->product_to_add->cart_item_key = $res['cart_item_key'];
                    if($cart_button_type == "keyboard") {
                        WC()->cart->set_quantity($entity_body->product_to_add->cart_item_key, $entity_body->product_to_add->quantity);
                    }
                    if (isset($entity_body->coupon_code) && $entity_body->coupon_code) {
                        WC()->cart->add_discount(sanitize_text_field($entity_body->coupon_code));
                    } else {
                        WC()->cart->remove_coupons();
                    }

                    WC()->cart->set_session();
                    WC()->cart->calculate_totals();
                    wp_send_json_success(array(
                        'coupon_code' => (WC()->cart->applied_coupons && is_array(WC()->cart->applied_coupons)) ? reset(WC()->cart->applied_coupons) : null,
                        'discount_cart' => WC()->cart->discount_cart,
                        'shipping_total' => WC()->cart->shipping_total,
                        'subtotal' => WC()->cart->subtotal,
                        'total' => WC()->cart->total,
                        'cart_content_total' => WC()->cart->cart_contents_total,
                        'line_items' => $this->getCartLineItems($entity_body), //array_values(WC()->cart->cart_contents),
                        'needs_shipping_address' => WC()->cart->needs_shipping_address(),
                        'needs_payment' => WC()->cart->needs_payment(),
                        'needs_shipping' => WC()->cart->needs_shipping(),
                        'show_shipping' => WC()->cart->show_shipping(),
                    ));
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_cart_apply_coupon'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    define('WOOCOMMERCE_CART', true);
                    wc_clear_notices();
                    $entity_body = json_decode(file_get_contents('php://input'));

                    if (isset($entity_body->customer_id) && $entity_body->customer_id) {
                        wp_set_current_user($entity_body->customer_id);
                    }

                    WC()->cart->empty_cart(true);

                    if (isset($entity_body->products) && is_array($entity_body->products)) {
                        foreach ($entity_body->products as $key => $product) {
                            $cart_result = array();
                            $cart_result = Appchar_Cart_Handler::add_to_cart_action(false, (array)$product);
                            if($cart_result['status']) {
                                $entity_body->products[$key]->cart_item_key = $cart_result['cart_item_key'];
                                continue;
                            }else {
                                if(wc_notice_count('error') > 0) {
                                    unset($entity_body->products[$key]);
                                    $entity_body->products = array_values($entity_body->products);
                                    wc_clear_notices();
                                }
                            }
                        }
                    }



                    $res = WC()->cart->add_discount(sanitize_text_field($entity_body->coupon_code));

                    if (!$res || wc_notice_count('error') > 0) {
                        $error = wc_notice_count('error') ? reset(wc_get_notices('error')) : 'کوپن اعمال نشد';
                        wp_send_json_error(array('error' => $error['notice']), 400);
                    }

                    WC()->cart->set_session();
                    WC()->cart->calculate_totals();

                    wp_send_json_success(array(
                        'coupon_code' => (WC()->cart->applied_coupons && is_array(WC()->cart->applied_coupons)) ? reset(WC()->cart->applied_coupons) : null,
                        'discount_cart' => WC()->cart->discount_cart,
                        'shipping_total' => WC()->cart->shipping_total,
                        'subtotal' => WC()->cart->subtotal,
                        'total' => WC()->cart->total,
                        'cart_content_total' => WC()->cart->cart_contents_total,
                        'line_items' => $this->getCartLineItems($entity_body), //array_values(WC()->cart->cart_contents),
                        'needs_shipping_address' => WC()->cart->needs_shipping_address(),
                        'needs_payment' => WC()->cart->needs_payment(),
                        'needs_shipping' => WC()->cart->needs_shipping(),
                        'show_shipping' => WC()->cart->show_shipping(),
                    ));
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_checkout_get_shipping_methods'])) {
            //$this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    define('WOOCOMMERCE_CHECKOUT', true);
                    $entity_body = json_decode(file_get_contents('php://input'));
                    $this->setupCartSession($entity_body);
                    $rates = array();
                    $packages = WC()->shipping()->get_packages();
                    if (WC()->cart->needs_shipping()) {
                        $rates = ($packages && count($packages) && isset($packages[0]['rates']) && $packages[0]['rates']) ? array_values($packages[0]['rates']) : array();
//                        $rates = ($packages && count($packages)) ? array_values($packages[0]['rates']) : array();
//                        $rates = ($packages && count($packages)) ? array_values(WC()->shipping()->get_packages()[0]['rates']) : array();
                        global $woocommerce;
                        if( $woocommerce->version >= '3.2.0') {
                            $rates = array();
                            foreach ($packages[0]['rates'] as $item_key => $package) {
                                $rates[] = array(
                                    'id' => $package->__get('id'),
                                    'label' => $package->__get('label'),
                                    'cost' => $package->__get('cost'),
                                    'taxes' => $package->__get('taxes'),
                                    'method_id' => $package->__get('method_id'),
                                );
                            }
                        }

                    }
                    wp_send_json_success(array(
                        //'cart'  => WC()->cart,
                        'needs_shipping' => WC()->cart->needs_shipping(),
                        'show_shipping' => WC()->cart->show_shipping(),
                        'needs_payment' => WC()->cart->needs_payment(),
                        'rates' => $rates ? $rates : array()
                    ));
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_checkout_get_payment_methods'])) {
            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    define('WOOCOMMERCE_CHECKOUT', true);
                    $entity_body = json_decode(file_get_contents('php://input'));
                    $this->setupCartSession($entity_body);

                    if (WC()->cart->needs_payment()) {
                        $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
                        WC()->payment_gateways()->set_current_gateway($available_gateways);
                    } else {
                        $available_gateways = array();
                    }

                    wp_send_json_success(array(
                        'coupon_code' => (WC()->cart->applied_coupons && is_array(WC()->cart->applied_coupons)) ? reset(WC()->cart->applied_coupons) : null,
                        'discount_cart' => WC()->cart->discount_cart ? WC()->cart->discount_cart : 0,
                        'shipping_total' => WC()->cart->shipping_total ? WC()->cart->shipping_total : 0,
                        'subtotal' => WC()->cart->subtotal ? WC()->cart->subtotal : 0,
                        'total' => WC()->cart->total ? WC()->cart->total : 0,
                        'cart_content_total' => WC()->cart->cart_contents_total ? WC()->cart->cart_contents_total : 0,
                        'line_items' => $this->getCartLineItems($entity_body),
                        'needs_shipping_address' => WC()->cart->needs_shipping_address(),
                        'needs_payment' => WC()->cart->needs_payment(),
                        'needs_shipping' => WC()->cart->needs_shipping(),
                        'show_shipping' => WC()->cart->show_shipping(),
                        'available_gateways' => array_values($available_gateways)
                    ));
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        //
        if (isset($wp->query_vars['appchar_checkout'])) {
            //$this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "POST":
                    define('WOOCOMMERCE_CHECKOUT', true);

                    $entity_body = json_decode(file_get_contents('php://input'));
                    $this->setupCartSession($entity_body);
                    $_POST['_wpnonce'] = wp_create_nonce('woocommerce-process_checkout');

                    wc_clear_notices();

                    $_POST['billing_first_name'] = $entity_body->checkout->first_name;
                    $_POST['billing_last_name'] = $entity_body->checkout->last_name;
                    $_POST['billing_company'] = $entity_body->checkout->company;
                    $_POST['billing_email'] = $entity_body->checkout->email;
                    $_POST['billing_phone'] = $entity_body->checkout->phone;
                    $_POST['billing_country'] = $entity_body->checkout->country;
                    $_POST['billing_address_1'] = $entity_body->checkout->address_1;
                    $_POST['billing_address_2'] = $entity_body->checkout->address_2;
                    $_POST['billing_city'] = $entity_body->checkout->city;
                    $_POST['billing_state'] = $entity_body->checkout->state;
                    $_POST['billing_postcode'] = $entity_body->checkout->postcode;

                    $_POST['shipping_first_name'] = $entity_body->checkout->first_name;
                    $_POST['shipping_last_name'] = $entity_body->checkout->last_name;
                    $_POST['shipping_company'] = $entity_body->checkout->company;
                    $_POST['shipping_country'] = $entity_body->checkout->country;
                    $_POST['shipping_address_1'] = $entity_body->checkout->address_1;
                    $_POST['shipping_address_2'] = $entity_body->checkout->address_2;
                    $_POST['shipping_city'] = $entity_body->checkout->city;
                    $_POST['shipping_state'] = $entity_body->checkout->state;
                    $_POST['shipping_postcode'] = $entity_body->checkout->postcode;


                    $_POST['order_comments'] = '';

                    $_POST['payment_method'] = $entity_body->checkout->payment_method;
                    $_POST['shipping_method'] = $entity_body->checkout->shipping_method;

                    if (isset($entity_body->checkout->terms) && $entity_body->checkout->terms) {
                        $_POST['terms'] = 'on';
                        $_POST['terms-field'] = 1;
                    }

                    if (isset($entity_body->checkout->order_comments) && $entity_body->checkout->order_comments && strlen($entity_body->checkout->order_comments)) {
                        $_POST['order_comments'] = $entity_body->checkout->order_comments;
                    }

                    $appchar_checkout = new Appchar_Checkout();
                    $order_id = $appchar_checkout->process_checkout();
                    if ($order_id) {
                        do_action('appchar_save_extra_meta_to_order', $order_id, $entity_body);
                        $order = wc_get_order($order_id);
                        $result['order']['id'] = $order->get_id();
                        $result['order']['order_number'] = $order->get_order_number();
                        $result['order']['needs_payment'] = $order->needs_payment();

                        $pay_url = $this->getOrderPayUrl($order);
                        $result['order']['pay_url'] = $pay_url;
                        $result['order']['received_url'] = $order->get_checkout_order_received_url();
                        wp_send_json($result);
                    } else {
                        $error = wc_notice_count('error') ? reset(wc_get_notices('error')) : 'خطا در ثبت سفارش';
                        wp_send_json_error(array('error' => $error['notice']), 400);
                    }

                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        //send and receive extention
        if (isset($wp->query_vars['appchar_checkout_get_receive_time'])) {
            //$this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":

                    date('l', strtotime(' +3 day'));
                    if (get_option('appchar_checkout_receive_setting', false)) {
                        $order_receive_setting = get_option('appchar_checkout_receive_setting', false);
                    } else {
                        $order_receive_setting = null;
                    }
                    $day_time = (isset($order_receive_setting['day_time'])) ? $order_receive_setting['day_time'] : array();
                    unset($order_receive_setting['day_time']);
                    $week_days = array(
                        'Sunday' => __('Sunday', 'appchar'),
                        'Monday' => __('Monday', 'appchar'),
                        'Tuesday' => __('Tuesday', 'appchar'),
                        'Wednesday' => __('Wednesday', 'appchar'),
                        'Thursday' => __('Thursday', 'appchar'),
                        'Friday' => __('Friday', 'appchar'),
                        'Saturday' => __('Saturday', 'appchar'),
                    );
                    $current_time = current_time('H', false);
                    $service_time = (isset($order_receive_setting['service_time'])) ? $order_receive_setting['service_time'] : 1;
                    $current_time = $current_time + $service_time;
                    $day_count = (isset($order_receive_setting['day_count']) && $order_receive_setting['day_count']<=7 && $order_receive_setting['day_count']>0) ? $order_receive_setting['day_count'] : 1;
                    $days_time = array();
                    for ($i = 0; $i < 7; $i++) {
                        if (count($days_time) == $day_count)
                            break;
                        if (count($day_time[date('l', strtotime(' +' . $i . ' day'))]) != 0) {
                            $times = array();
                            foreach ($day_time[date('l', strtotime(' +' . $i . ' day'))] as $time) {
                                if ($i == 0) {
                                    if ($current_time >= $time['from']) {
                                        continue;
                                    }
                                }
                                $times[] = array(
                                    'name' => __("from","appchar") . $time['from'] . __("to","appchar") . $time['to'],
                                    'from' => $time['from'],
                                    'to' => $time['to'],
                                    'day_of_week' => array_search(date('l', strtotime(' +' . $i . ' day')), array_keys($week_days)),
                                );
                            }
                            if (count($times) != 0) {
                                $days_time[] = array(
                                    'name' => $week_days[date('l', strtotime(' +' . $i . ' day'))],
                                    'day_of_week' => array_search(date('l', strtotime(' +' . $i . ' day')), array_keys($week_days)),
                                    'times' => $times,
                                );
                            }
                        }

                    }
                    $order_receive_setting['options'] = $days_time;
                    wp_send_json_success($order_receive_setting);

                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        //lottery
        if (isset($wp->query_vars['appchar_get_lottery'])) {
            $this->authorizeAppcharToken();
            if (!defined('WPLT_PLUGIN_BASENAME')) {
                wp_send_json_error(array('error' => __('پلاگین قرعه کشی فعال نمی باشد', 'appchar')), 400);
            }
            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $json = array();
                $lottery_id = $wp->query_vars['appchar_get_lottery'];
                $result = new WPLT_Lottery($lottery_id);
                $sd = strtotime($result->start_date);
                $sd_jalali = jgetdate($sd);
                $result->start_date = $sd_jalali['year'] . '-' . $sd_jalali['month'] . '-' . $sd_jalali['mday'];
                $ed = strtotime($result->end_date);
                $ed_jalali = jgetdate($ed);
                $result->end_date = $ed_jalali['year'] . '-' . $ed_jalali['month'] . '-' . $ed_jalali['mday'];
                $result->leader_board = $result->get_leader_board();
                $result->winners = $result->get_winners();
                $json['lottery'] = $result;
                if (!isset($result->id)) {
                    wp_send_json_error(array('error' => __('قرعه کشی با این شماره یافت نشد', 'appchar')), 400);
                } else {
                    wp_send_json($json);
                }
            } else {
                wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        if (isset($wp->query_vars['appchar_get_lotteries'])) {
            $this->authorizeAppcharToken();
            if (!defined('WPLT_PLUGIN_BASENAME')) {
                wp_send_json_error(array('error' => __('پلاگین قرعه کشی فعال نمی باشد', 'appchar')), 400);
            }
            global $wpltdb;
            $lotteries_id = $wpltdb->get_lotteries_id();
            $lotteries = array();
            foreach ($lotteries_id as $lottery_id) {
                $result = new WPLT_Lottery($lottery_id->id);
                $sd = strtotime($result->start_date);
                $sd_jalali = jgetdate($sd);
                $result->start_date = $sd_jalali['year'] . '-' . $sd_jalali['month'] . '-' . $sd_jalali['mday'];
                $ed = strtotime($result->end_date);
                $ed_jalali = jgetdate($ed);
                $result->end_date = $ed_jalali['year'] . '-' . $ed_jalali['month'] . '-' . $ed_jalali['mday'];
                $lotteries[] = $result;
            }
            $json['lotteries'] = $lotteries;
            if (isset($_GET['user_id'])) {
                $user_id = $_GET['user_id'];
                $fields = get_option('wplottery_fields', array());
                if (class_exists('WPLT_Prepare_Fields')) {
                    $user_fields = WPLT_Prepare_Fields::set_value_to_fields($fields, $user_id);
                } else {
                    $user_fields = array();
                }
                $json['user_fields'] = $user_fields;
            }
            wp_send_json_success($json);
        }
        if (isset($wp->query_vars['lottery_register_code'])) {
            $this->authorizeAppcharToken();
            if (!defined('WPLT_PLUGIN_BASENAME')) {
                wp_send_json_error(array('error' => __('پلاگین قرعه کشی فعال نمی باشد', 'appchar')), 400);
            }
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                if (!isset($_POST['user_id'])) {
                    wp_send_json_error(array('error' => 'user_id required!'), 400);
                } elseif (!isset($_POST['code'])) {
                    wp_send_json_error(array('error' => 'code required!'), 400);
                }
                $user_id = trim($_POST['user_id']);
                do_action('lottery_shortcode_submit_form');
                $code = trim($_POST['code']);
                $result = WPLT_Code::register_code($code, $user_id);
                if (isset($result['error_id'])) {
                    wp_send_json_error(array('error' => $result['message']), 400);
                }
                wp_send_json_success($result);
            } else {
                wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        if (isset($wp->query_vars['get_lotteries_user_action'])) {
            $this->authorizeAppcharToken();
            if (!defined('WPLT_PLUGIN_BASENAME')) {
                wp_send_json_error(array('error' => __('پلاگین قرعه کشی فعال نمی باشد', 'appchar')), 400);
            }
            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $user_id = $wp->query_vars['get_lotteries_user_action'];
                $user_activities = WPLT_User_Action::get_user_action($user_id);
                wp_send_json_success($user_activities);
            } else {
                wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        global $wp;
        if (isset($wp->query_vars['appchar_get_post'])) {
//            $this->authorizeAppcharToken();
            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $post_id = $wp->query_vars['appchar_get_post'];
                $result = $this->appchar_get_post_by_id($post_id);
                $get_post = apply_filters('add_meta_to_single_post_endpoint', $result);
                if ($get_post) {
                    $result = $get_post;
                }

                if (isset($get_post->post_builder)) {
                    $hpc2 = array();
                    foreach ($get_post->post_builder['elements'] as $element) {
                        if ($element['type'] == 'slider' || $element['type'] == 'grid') {
                            $items = array();
                            foreach ($element['items'] as $item) {
                                if ($item['link_type'] == 'single_category') {
                                    $item['category_id'] = $item['link'];
                                }
                                $items[] = $this->add_object_to($item);//TODO
                            }
                            $element['items'] = $items;
                        } elseif ($element['type'] == 'product_list') {
                            if ($element['product_list_type'] == "special_category") {
                                $term = get_term($element['category_id'], 'product_cat');
                                $term->id = $term->term_id;
                                $element['category'] = $term;
                            }
                        } elseif ($element['type'] == 'special_offer') {
                            unset($element['items']);
                            $element['remaining'] = remaining_time();
                        } elseif ($element['type'] == 'lottery_leader_board') {
                            $result = new WPLT_Lottery($element['lottery_id']);
                            $element['title'] = $result->name;
                            if ($result->active) {
                                $element['leader_board'] = $result->get_leader_board();
                            } else {
                                $element['leader_board'] = $result->get_winners();
                            }
                        } else {
                            $element = $this->add_object_to($element);
                        }
                        $hpc2[] = $element;
                    }
                    unset($get_post->post_builder);
                    $get_post->post_builder['elements'] = $hpc2;
                }
                if (is_wp_error($result)) {
                    wp_send_json_error(array('error' => $result->get_error_message()), 400);
                } else {
                    wp_send_json(array('post' => $result));
                }
            } else {
                wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        if (isset($wp->query_vars['appchar_get_posts'])) {
            $json = $this->getPosts();
            wp_send_json_success($json);
        }
        if (isset($wp->query_vars['appchar_get_post_categories'])) {
            $categories = get_categories();
            foreach ($categories as &$category){
                $children = get_terms( $term->taxonomy, array(
                    'parent'    => $category->term_id,
                    'hide_empty' => false
                ) );
                $category->has_child = ($children)?true:false;
            }
            $categories = array_values($categories);
            $json['categories_count']= count($categories);
            $json['categories'] = $categories;
            wp_send_json_success($json);
        }
        if (isset($wp->query_vars['appchar_get_post_reviews_v2'])) {
//            $this->authorizeAppcharToken();
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $post_id = $wp->query_vars['appchar_get_post_reviews_v2'];
                    $result = $this->get_post_reviews_with_child($post_id);
                    if (is_wp_error($result)) {
                        wp_send_json_error(array('error' => $result->get_error_message()), 400);
                    } else {
                        wp_send_json($result);
                    }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }

        if (isset($wp->query_vars['appchar_get_country_states'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                    $options = get_option('appchar_general_setting');
                    $region_cities_source_option = $options['region_cities_source_option'] ? $options['region_cities_source_option'] : 'woocommerce';
                    $country_code = strtoupper($wp->query_vars['appchar_get_country_states']);
                    $in_allowed_countries = false;
                    $appchar_states = get_option('appchar_states', false);
                    foreach(WC()->countries->get_allowed_countries() as $key => $value) {
                        if(strtolower($country_code) == strtolower($key)) {
                            $in_allowed_countries = true;
                        }
                    }
                    if(AppcharExtension::extensionIsActive('edit_checkout_fields') && $in_allowed_countries && $appchar_states && $country_code == 'IR') {
                        $appchar_states_json = array();
                        foreach($appchar_states as $value) {
                            $appchar_states_json[] = array(
                                "id" => $value['id'],
                                "name" => $value['name']
                            );
                        }
                        wp_send_json_success($appchar_states_json);
                    }else {
                        $states = WC()->countries->get_states($country_code);
                        if(is_wp_error($states)) {
                            wp_send_json_error(array('error' => $states->get_error_message()), 400);
                        }else {
                            $states_copy = array();
                            foreach($states as $key => $value) {
                                $states_copy[] = array(
                                    "id" => $key,
                                    "name" => $value
                                );
                            }
                            wp_send_json_success($states_copy);
                        }
                    }
                    break;
                default:
                wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }
        if(isset($wp->query_vars['appchar_get_country_states_cities'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
                case "GET":
                  $options = get_option('appchar_general_setting');
                  $region_cities_source_option = $options['region_cities_source_option'] ? $options['region_cities_source_option'] : 'woocommerce';
                  $country_code = explode(',', $wp->query_vars['appchar_get_country_states_cities'])[0];
                  $states_code = explode(',', $wp->query_vars['appchar_get_country_states_cities'])[1];
                  if(AppcharExtension::extensionIsActive('edit_checkout_fields') && $appchar_states_cities = get_option('appchar_states', false)) {
                    $appchar_cities_json = array();
                    foreach($appchar_states_cities as $value) {
                        foreach($value['cities'] as $key => $v) {
                            if(strtolower($value['id']) == strtolower($states_code)) {
                            $appchar_cities_json[] = array(
                                "id" => $key,
                                "name" => $v,
                            );
                        }
                        }
                    }
                    wp_send_json_success($appchar_cities_json);
                  }else {
                    switch ($region_cities_source_option) {
                      case 'woocommerce':
                        wp_send_json_success(array());
                        break;
                      case 'persian-woocommerce':
                        wp_send_json_success(array());
                        break;
                      case 'persian-woocommerce-shipping':
                          if(is_plugin_active('persian-woocommerce-shipping/woocommerce-shipping.php')) {
                            $persian_woocommerce_shipping_states_cities = PWS()::cities( $states_code );
                            $final_cities = array();
                            foreach ($persian_woocommerce_shipping_states_cities as $key => $value) {
                              $final_cities[] = array(
                                'id' => $key,
                                'name' => $value,
                              );
                            }
                            wp_send_json_success($final_cities);
                          }else {
                            wp_send_json_success(array());
                          }
                        break;

                      default:
                        wp_send_json_success(array());
                        break;
                    }
                  }
                    break;
                default:
                    wp_send_json_error(array('error' => 'method not allowed'), 405);
            }
        }


    }

    public function add_query_vars($vars)
    {
        $vars[] = 'appchar_special_offer';
        $vars[] = 'appchar_display_notifications';
        $vars[] = 'appchar_banners';
        $vars[] = 'appchar_pages';
        $vars[] = 'appchar_get_page';
        $vars[] = 'appchar_page_id';

        $vars[] = 'appchar_auth_login';
        $vars[] = 'appchar_auth_verify';

        $vars[] = 'appchar_login';
        $vars[] = 'appchar_logout';
        $vars[] = 'appchar_reset_password';
        $vars[] = 'appchar_change_password';
        $vars[] = 'appchar_get_woocommerce_api_key';
        $vars[] = 'appchar_get_woocommerce_data';
        $vars[] = 'appchar_get_payment_methods';
        $vars[] = 'appchar_get_payment_methods_v2';
        $vars[] = 'appchar_get_customer';
        $vars[] = 'appchar_customer_id';
        $vars[] = 'appchar_create_customer';
        $vars[] = 'appchar_order';
        $vars[] = 'appchar_apply_coupon';
        $vars[] = 'appchar_get_shipping_methods';
        $vars[] = 'appchar_get_order_payment_url';
        $vars[] = 'appchar_pay_with_credit';
        $vars[] = 'get_wallet_credit';
        $vars[] = 'increase_wallet_credit';
        $vars[] = 'appchar_post_comment';
        $vars[] = 'appchar_get_product_attributes';
        $vars[] = 'appchar_get_product_variation';
        $vars[] = 'appchar_get_product_by_ids';
        $vars[] = 'appchar_get_products';
        $vars[] = 'appchar_get_product';
        $vars[] = 'appchar_get_product_reviews';
        $vars[] = 'appchar_get_product_reviews_v2';
        $vars[] = 'appchar_get_product_id_by_sku';
        $vars[] = 'appchar_get_options';

        $vars[] = 'appchar_get_product_terms';
        $vars[] = 'appchar_get_product_categories';
        $vars[] = 'appchar_get_product_categories_v2';
        $vars[] = 'appchar_get_product_categories_sticky';
        $vars[] = 'appchar_get_order';
        $vars[] = 'appchar_get_order_statuses';
        $vars[] = 'appchar_get_customer_orders';

        $vars[] = 'appchar_get_balance_log';


        $vars[] = 'appchar_get_products_new';
        $vars[] = 'appchar_get_products_attributes';
        $vars[] = 'appchar_update_customer';
        $vars[] = 'appchar_product_can_add_to_cart';

        $vars[] = 'appchar_get_options_v2';
        $vars[] = 'appchar_get_shipping_methods_v2';

        //New Pooya
        $vars[] = 'appchar_cart_create';
        $vars[] = 'appchar_cart_add_to_cart';
        $vars[] = 'appchar_cart_apply_coupon';
        $vars[] = 'appchar_checkout';
        $vars[] = 'appchar_checkout_get_shipping_methods';
        $vars[] = 'appchar_checkout_get_payment_methods';
        $vars[] = 'appchar_get_states';
        //send and receive extensions
        $vars[] = 'appchar_checkout_get_receive_time';

        //lottery
        $vars[] = 'appchar_get_lotteries';
        $vars[] = 'appchar_get_lottery';
        $vars[] = 'get_lotteries_user_action';
        $vars[] = 'lottery_register_code';

        //blog
        $vars[] = 'appchar_get_posts';
        $vars[] = 'appchar_get_post';
        $vars[] = 'appchar_get_post_categories';
        $vars[] = 'appchar_get_post_reviews_v2';

        //states
        $vars[] = 'appchar_get_country_states';
        $vars[] = 'appchar_get_country_states_cities';



        return $vars;
    }

    public function add_endpoint()
    {

        add_rewrite_rule('^appchar/special_offer$', 'index.php?appchar_special_offer=$matches[1]', 'top');
        add_rewrite_rule('^appchar/banners$', 'index.php?appchar_banners=$matches[1]', 'top');
        add_rewrite_rule('^appchar/pages$', 'index.php?appchar_pages=$matches[1]', 'top');

        add_rewrite_rule('^appchar/auth/login$', 'index.php?appchar_auth_login=$matches[1]', 'top');
        add_rewrite_rule('^appchar/auth/verify$', 'index.php?appchar_auth_verify=$matches[1]', 'top');

        add_rewrite_rule('^appchar/login$', 'index.php?appchar_login=$matches[1]', 'top');
        add_rewrite_rule('^appchar/logout$', 'index.php?appchar_logout=$matches[1]', 'top');

        add_rewrite_rule('^appchar/reset_password$', 'index.php?appchar_reset_password=$matches[1]', 'top');
        add_rewrite_rule('^appchar/change_password$', 'index.php?appchar_change_password=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_woocommerce_api_key$', 'index.php?appchar_get_woocommerce_api_key=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_woocommerce_data$', 'index.php?appchar_get_woocommerce_data=$matches[1]', 'top');
        add_rewrite_rule('^appchar/pages/([0-9]+)$', 'index.php?appchar_get_page=1&appchar_page_id=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_payment_methods$', 'index.php?appchar_get_payment_methods=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_payment_methods_v2$', 'index.php?appchar_get_payment_methods_v2=$matches[1]', 'top');
        add_rewrite_rule('^appchar/apply_coupon$', 'index.php?appchar_apply_coupon=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_shipping_methods$', 'index.php?appchar_get_shipping_methods=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_shipping_methods_v2$', 'index.php?appchar_get_shipping_methods_v2=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_order_payment_url$', 'index.php?appchar_get_order_payment_url=$matches[1]', 'top');
        add_rewrite_rule('^appchar/pay_with_credit$', 'index.php?appchar_pay_with_credit=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_wallet_credit/([^/]*)/?$', 'index.php?get_wallet_credit=$matches[1]', 'top');
        add_rewrite_rule('^appchar/increase_wallet_credit$', 'index.php?increase_wallet_credit=$matches[1]', 'top');
        add_rewrite_rule('^appchar/post_comment$', 'index.php?appchar_post_comment=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_product_attributes/([0-9]+)$', 'index.php?appchar_get_product_attributes=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_product_variation/([0-9]+)$', 'index.php?appchar_get_product_variation=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_product_by_ids$', 'index.php?appchar_get_product_by_ids=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_products$', 'index.php?appchar_get_products=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_product_id_by_sku$', 'index.php?appchar_get_product_id_by_sku=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_options$', 'index.php?appchar_get_options=$matches[1]', 'top');
        add_rewrite_rule('^appchar/get_states$', 'index.php?appchar_get_states=$matches[1]', 'top');
        add_rewrite_rule('^appchar/display_notifications$', 'index.php?appchar_display_notifications=$matches[1]', 'top');


        //New
        add_rewrite_rule('^appchar/products/terms$', 'index.php?appchar_get_product_terms=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products/categories$', 'index.php?appchar_get_product_categories=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products/categories/v2$', 'index.php?appchar_get_product_categories_v2=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products/categories/sticky$', 'index.php?appchar_get_product_categories_sticky=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products/attributes', 'index.php?appchar_get_products_attributes=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products$', 'index.php?appchar_get_products_new=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products/([0-9]+)$', 'index.php?appchar_get_product=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products/([0-9]+)/reviews$', 'index.php?appchar_get_product_reviews=$matches[1]', 'top');
        add_rewrite_rule('^appchar/products/([0-9]+)/reviews/v2$', 'index.php?appchar_get_product_reviews_v2=$matches[1]', 'top');

        add_rewrite_rule('^appchar/customers$', 'index.php?appchar_create_customer=$matches[1]', 'top');
        add_rewrite_rule('^appchar/customers/([0-9]+)$', 'index.php?appchar_get_customer=1&appchar_customer_id=$matches[1]', 'top');
        add_rewrite_rule('^appchar/customers/([0-9]+)/update', 'index.php?appchar_update_customer=1&appchar_customer_id=$matches[1]', 'top');
        add_rewrite_rule('^appchar/customers/([0-9]+)/orders$', 'index.php?appchar_get_customer_orders=1&appchar_customer_id=$matches[1]', 'top');
        add_rewrite_rule('^appchar/customers/([0-9]+)/wallet_logs$', 'index.php?appchar_get_balance_log=1&appchar_customer_id=$matches[1]', 'top');

        add_rewrite_rule('^appchar/orders$', 'index.php?appchar_order=$matches[1]', 'top');
        add_rewrite_rule('^appchar/orders/([0-9]+)$', 'index.php?appchar_get_order=$matches[1]', 'top');
        add_rewrite_rule('^appchar/orders/statuses', 'index.php?appchar_get_order_statuses=$matches[1]', 'top');


        add_rewrite_rule('^appchar/get_options_v2$', 'index.php?appchar_get_options_v2=$matches[1]', 'top');
        add_rewrite_rule('^appchar/product_can_add_to_cart$', 'index.php?appchar_product_can_add_to_cart=$matches[1]', 'top');


        //New Pooya
        //appchar_cart_create
        add_rewrite_rule('^appchar/cart$', 'index.php?appchar_cart_create=$matches[1]', 'top');
        add_rewrite_rule('^appchar/cart/add_to_cart$', 'index.php?appchar_cart_add_to_cart=$matches[1]', 'top');
        add_rewrite_rule('^appchar/cart/apply_coupon$', 'index.php?appchar_cart_apply_coupon=$matches[1]', 'top');
        add_rewrite_rule('^appchar/checkout$', 'index.php?appchar_checkout=$matches[1]', 'top');
        add_rewrite_rule('^appchar/checkout/get_shipping_methods$', 'index.php?appchar_checkout_get_shipping_methods=$matches[1]', 'top');
        add_rewrite_rule('^appchar/checkout/get_payment_methods$', 'index.php?appchar_checkout_get_payment_methods=$matches[1]', 'top');

        //send and receive order extensions
        add_rewrite_rule('^appchar/checkout/receive_time$', 'index.php?appchar_checkout_get_receive_time=$matches[1]', 'top');


        //lottery
        add_rewrite_rule('^appchar/lotteries$', 'index.php?appchar_get_lotteries=$matches[1]', 'top');
        add_rewrite_rule('^appchar/lotteries/([0-9]+)$', 'index.php?appchar_get_lottery=$matches[1]', 'top');
        add_rewrite_rule('^appchar/lotteries/users/([0-9]+)/actions$', 'index.php?get_lotteries_user_action=$matches[1]', 'top');
        add_rewrite_rule('^appchar/lotteries/register_code$', 'index.php?lottery_register_code=$matches[1]', 'top');

        //blog
        add_rewrite_rule('^appchar/posts$', 'index.php?appchar_get_posts=$matches[1]', 'top');
        add_rewrite_rule('^appchar/posts/([0-9]+)$', 'index.php?appchar_get_post=$matches[1]', 'top');
        add_rewrite_rule('^appchar/posts/categories$', 'index.php?appchar_get_post_categories=$matches[1]', 'top');
        add_rewrite_rule('^appchar/posts/([0-9]+)/reviews/v2$', 'index.php?appchar_get_post_reviews_v2=$matches[1]', 'top');

        //get states
        add_rewrite_rule('^appchar/countries/([^/]*)/states$', 'index.php?appchar_get_country_states=$matches[1]', 'top');
        add_rewrite_rule('^appchar/countries/([^/]*)/states/([^/]*)/cities$', 'index.php?appchar_get_country_states_cities=$matches[1],$matches[2]', 'top');

    }
    //NOTE addig custom updates triggers
    public function appchar_flush_rewrites()
    {
        $plugin_data = get_plugin_data(plugin_dir_path( __FILE__ ) . "../appchar-woocommerce.php", false, false);
        if($plugin_data['Version'] != get_option('appchar_flush_rewrites_flag') || !get_option('appchar_flush_rewrites_flag')) {
            update_option('appchar_flush_rewrites_flag', $plugin_data['Version']);
            $this->add_endpoint();
            flush_rewrite_rules();

        }

    }
    public function appchar_flush_rewrites_init() {
        $plugin_data = get_plugin_data(plugin_dir_path( __FILE__ ) . "../appchar-woocommerce.php", false, false);
        if(!get_option('appchar_flush_rewrites_init_flag')) {
            add_option('appchar_flush_rewrites_init_flag', true);
            add_option('appchar_flush_rewrites_flag', $plugin_data['Version']);
            $this->add_endpoint();
            flush_rewrite_rules();
        }
    }


//----------------------------------------------------------------------------------------------

    public function get_schedule_time()
    {
        $get_schedule = array();
        if (AppcharExtension::extensionIsActive('schedule')) {
            $get_schedule['status'] = 'enable';
            $schedule_times = get_option('appchar_schedule_time', array());
            $schedule = array();
            foreach ($schedule_times as $day => $schedule_time) {
                $times = array();
                foreach ($schedule_time as $time) {
                    $setime = explode('-', $time);
                    $times[] = array(
                        'start' => $setime[0],
                        'end' => $setime[1]
                    );
                }
                $schedule[] = array(
                    'day' => $day,
                    'hours' => $times
                );
            }
            $get_schedule['schedule_time'] = $schedule;
            $get_schedule['message'] = get_option('appchar_schedule_error_msg', '');
        } else {
            $get_schedule['status'] = 'disable';
        }
        return $get_schedule;
    }

    public function appchar_retrieve_password()
    {
        global $wpdb, $wp_hasher;

        $errors = new WP_Error();

        $login = trim($_POST['user_login']);

        if (empty($login)) {

            $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.'));
            return $errors;

        } else {
            // Check on username first, as customers can use emails as usernames.
            $user_data = get_user_by('login', $login);
        }

        // If no user found, check if it login is email and lookup user based on email.
        if (!$user_data && is_email($login) && apply_filters('woocommerce_get_username_from_email', true)) {
            $user_data = get_user_by('email', $login);
        }

        do_action('lostpassword_post');

        if (!$user_data) {
            $errors->add('invalid_email', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
            return $errors;
        }

        if (is_multisite() && !is_user_member_of_blog($user_data->ID, get_current_blog_id())) {
            wc_add_notice(__('Invalid username or e-mail.', 'woocommerce'), 'error');
            return false;
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;

        do_action('retrieve_password', $user_login);

        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if (!$allow) {

            wc_add_notice(__('Password reset is not allowed for this user', 'woocommerce'), 'error');
            return false;

        } elseif (is_wp_error($allow)) {

            wc_add_notice($allow->get_error_message(), 'error');
            return false;
        }

        $key = wp_generate_password(20, false);

        do_action('retrieve_password_key', $user_login, $key);

        // Now insert the key, hashed, into the DB.
        if (empty($wp_hasher)) {
            require_once ABSPATH . 'wp-includes/class-phpass.php';
            $wp_hasher = new PasswordHash(8, true);
        }

        $hashed = $wp_hasher->HashPassword($key);

        $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));

        // Send email notification
        WC()->mailer(); // load email classes
        do_action('woocommerce_reset_password_notification', $user_login, $key);

        wc_add_notice(__('Check your e-mail for the confirmation link.', 'woocommerce'));
        return true;
    }

    public function appchar_get_product_by_id($product_id)
    {
        /*$product = wc_get_product($product_id);
        $product_data = get_product_data($product);
        if ($product->is_type('variable') && $product->has_child()) {
            $product_data['variations'] = appchar_get_product_variation_data($product);
        }
        if ($product->is_type('variation') && $product->parent) {
            $product_data['parent'] = get_product_data($product->parent);
        }
        if ($product->is_type('grouped') && $product->has_child()) {
            $product_data['grouped_products'] = get_grouped_products_data($product);
        }
        if ($product->is_type('simple') && !empty($product->post->post_parent)) {
            $_product = wc_get_product($product->post->post_parent);
            $product_data['parent'] = get_product_data($_product);
        }
        return apply_filters('woocommerce_api_product_response', $product_data, $product, array(), null);
        */

        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            global $sitepress;
            if (isset($_GET['locale'])) {
                $lang = $_GET['locale'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            if($sitepress!=null)
                $sitepress->switch_lang($lang);
        }


        $get_product = $this->products->get_product($product_id);
        if (is_wp_error($get_product)) {
            return '';
        }
        if ($get_product['product']['featured_src'] == '' && isset($get_product['product']['images'])) {
            $get_product['product']['featured_src'] = $get_product['product']['images'][0]['src'];
        }
//        if(get_post_meta($product_id,'APPCHAR_CUSTOM_TAB',true)!=''){
//            $custom_tabs = get_post_meta($product_id,'APPCHAR_CUSTOM_TAB',true);
//            $custom_tabs2 = array();
//            foreach ($custom_tabs as $custom_tab){
//                $custom_tab['desc'] = html_entity_decode($custom_tab['desc']);
//                $custom_tabs2[] = $custom_tab;
//            }
//            $get_product['product']['custom_tabs']= $custom_tabs2;
//        }
        if (AppcharExtension::extensionIsActive('special_offer')) {
            if(get_post_meta($product_id,'_appchar_is_special_offer',true)=='yes') {
                if (wc_get_product($product_id)->is_on_sale()) {
                    $remaining_time = remaining_time(get_post_meta($get_product['product']['id'], 'special_offer_date', true));
                    $get_product['product']['special_offer_remaining_time'] = $remaining_time;
                }
            }
        }

        $pvariations2 = array();

        if ($get_product['product']['variations']) {
            foreach ($get_product['product']['variations'] as $pvariation) {
                $product_files = $pvariation['downloads'];
                if (!empty($product_files)) {
                    $product_files2 = array();
                    foreach ($product_files as $product_file) {
                        //checks if the file is full configured
                        if (!isset($product_file['file']) or empty($product_file['file'])) {
                            continue;
                        }
                        $link = $product_file['file'];

                        //gets the filename
                        if (isset($product_file['name']) and !empty($product_file['name'])) {
                            $filename = $product_file['name'];
                        } else {
                            $path_parts = pathinfo($link);
                            $filename = $path_parts['basename'];
                        }

                        $link_download = $pvariation['id'] . '|' . $link;
//                        $link_download = get_bloginfo('url') . "/direct-download/" . base64_encode($link_download) . "/";
                        $product_file['file'] = get_bloginfo('url') . "/direct-download/" . base64_encode($link_download) . "/";
//                        $links[] = $link_download;
//                        $name_files_array[] = $filename;
                        $product_file['name'] = $filename;
                        $product_files2[] = $product_file;
                    }
                    $pvariation['downloads'] = $product_files2;
                }
                $pvariation['_sale_price_dates_from'] = (get_post_meta($pvariation['id'], '_sale_price_dates_from', true) != "") ? doubleval(get_post_meta($pvariation['id'], '_sale_price_dates_from', true)) : null;
                $pvariation['_sale_price_dates_to'] = (get_post_meta($pvariation['id'], '_sale_price_dates_to', true) != "") ? doubleval(get_post_meta($pvariation['id'], '_sale_price_dates_to', true)) : null;
                $pvariations2[] = $pvariation;
            }
            $get_product['product']['variations'] = $pvariations2;
        } else {
            $product_files = $get_product['product']['downloads'];
            if (!empty($product_files)) {
                $product_files2 = array();
                foreach ($product_files as $product_file) {
                    //checks if the file is full configured
                    if (!isset($product_file['file']) or empty($product_file['file'])) {
                        continue;
                    }
                    $link = $product_file['file'];

                    //gets the filename
                    if (isset($product_file['name']) and !empty($product_file['name'])) {
                        $filename = $product_file['name'];
                    } else {
                        $path_parts = pathinfo($link);
                        $filename = $path_parts['basename'];
                    }

                    $link_download = $get_product['product']['id'] . '|' . $link;
//                        $link_download = get_bloginfo('url') . "/direct-download/" . base64_encode($link_download) . "/";
                    $product_file['file'] = get_bloginfo('url') . "/direct-download/" . base64_encode($link_download) . "/";
//                        $links[] = $link_download;
//                        $name_files_array[] = $filename;
                    $product_file['name'] = $filename;
                    $product_files2[] = $product_file;
                }
                $get_product['product']['downloads'] = $product_files2;
            }
        }
        $get_product['product']['_sale_price_dates_from'] = (get_post_meta($get_product['product']['id'], '_sale_price_dates_from', true) != "") ? doubleval(get_post_meta($get_product['product']['id'], '_sale_price_dates_from', true)) : null;
        $get_product['product']['_sale_price_dates_to'] = (get_post_meta($get_product['product']['id'], '_sale_price_dates_to', true) != "") ? doubleval(get_post_meta($get_product['product']['id'], '_sale_price_dates_to', true)) : null;

        $get_product2 = apply_filters('add_meta_to_product_endpoint', $get_product);
        if ($get_product2) {
            $get_product = $get_product2;
        }
        return $get_product['product'];
    }


    public function appchar_get_productid_by_sku($sku)
    {
        global $wpdb;
        $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku));
        if ($product_id) {
            return $product_id;
        } else {
            return false;
        }
    }

    public function cat_list()
    {
        $cat_display = get_option('appchar_display_categories', 'random');
        $args = array(
            'number' => '',
            'orderby' => '',
            'order' => '',
            'hide_empty' => '',
            'include' => ''
        );
        $product_categories = get_terms('product_cat', $args);
        if (!$product_categories) {
            return array();
        }
        $sorted_category_id = get_option('appchar_product_categores_by', false);
        $enable = get_option('appchar_category_enable', false);
        switch ($cat_display) {
            case 'hide':
                return array();
            case 'cat_button':
                return get_option('appchar_category_button_text', __('View All Categories', 'appchar'));
            case 'random':
                $args = array(
                    'hide_empty' => 0,
                );
                $product_categories = get_terms('product_cat', $args);
                if (count($product_categories) < 4) {
                    $count = count($product_categories);
                } else {
                    $count = 4;
                }
                $count = get_option('appchar_category_count', $count);
                $random_keys = array_rand($product_categories, $count);
                if (is_array($random_keys) || is_object($random_keys)) {
                    foreach ($random_keys as $random) {
                        $prod_cat = $product_categories[$random];
                        $cat_thumb_id = get_woocommerce_term_meta($prod_cat->term_id, 'thumbnail_id', true);
                        $cat_thumb_url = wp_get_attachment_thumb_url($cat_thumb_id);
                        if ($cat_thumb_url) {
                            $prod_cat->image = $cat_thumb_url;
                        } else {
                            $prod_cat->image = "";
                        }
                        $prod_cat->id = $prod_cat->term_id;
                        $json[] = $prod_cat;
                    }
                }
                break;
            case 'select_categories':
                if ($enable) {
                    foreach ($sorted_category_id as $category_id) {
                        foreach ($product_categories as $key => $category) {
                            if ($category_id == $category->term_id) {
                                if ($enable && $enable[$category->term_id] == 'on') {
                                    $cat_thumb_id = get_woocommerce_term_meta($category->term_id, 'thumbnail_id', true);
                                    $cat_thumb_url = wp_get_attachment_thumb_url($cat_thumb_id);
                                    if ($cat_thumb_url) {
                                        $category->image = $cat_thumb_url;
                                    } else {
                                        $category->image = "";
                                    }
                                    $category->id = $category->term_id;
                                    $json[] = $category;
                                }
                            }

                        }
                    }
                } else {
                    $json = array();
                }
                break;
        }
        return $json;
    }

    private function get_banners()
    {
        global $wpdb;
        $json = array();
        foreach ($wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "appchar_banners ORDER BY sequence,banner_id ") as $key => $row) {
            if ($row->category_id) {
                //product_cat
                $term = get_term($row->category_id, 'product_cat');
                $row->category = $term;
            }
            if (isset($row->banner_type) && $row->banner_type == 'product') {
                $pdt = $this->appchar_get_product_by_id($row->link);
                $row->product = $pdt;
            }
            $json[] = $row;
        }
        return $json;
    }

    private function get_pages()
    {
        global $wpdb;
        $json = array();
        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            if (isset($_GET['locale'])) {
                $lang = $_GET['locale'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            $query = "SELECT * FROM " . $wpdb->prefix . "appchar_pages where language = \"$lang\"";
        } else {
            $query = "SELECT * FROM " . $wpdb->prefix . "appchar_pages";
        }
        foreach ($wpdb->get_results($query) as $key => $row) {
//            if (get_post_meta($row->page_id, 'post_material_icon', false)) {
//                $icon = get_post_meta($row->page_id, 'post_material_icon');
//                $row->icon = $icon[0];
//            }
            $json[] = $row;
        }
        return $json;
    }

    public function getProducts()
    {
        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            global $sitepress;
            if (isset($_GET['locale'])) {
                $lang = $_GET['locale'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            $sitepress->switch_lang($lang);
        }



        $catid = (isset($_GET['category_id'])) ? trim($_GET['category_id']) : null;
        $order = 'asc';
        if(!isset($_GET['sortby']) || trim($_GET['sortby'])=='default'){
            switch (get_option('woocommerce_default_catalog_orderby')){
                case 'popularity':
                    $sortby = 'bestseller';
                    break;
                case 'date':
                    $sortby = 'recent';
                    break;
                case 'price':
                    $sortby = 'price';
                    break;
                case 'price-desc':
                    $sortby = 'price';
                    $order = 'desc';
                    break;
                default:
                    $sortby = 'default';
                    break;
            }
        }else{
            $sortby = trim($_GET['sortby']); //-- recent,bestseller,price
        }
        switch ($sortby){
            case 'recent':
            case 'bestseller':
                $order = 'desc';
                break;

        }
        $post_count = (isset($_GET['count'])) ? trim($_GET['count']) : 10;
        $order = (isset($_GET['order'])) ? trim($_GET['order']) : $order;
        $paged = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $search_query = (isset($_GET['q'])) ? $_GET['q'] : null;
        $json = array();
        // setup query
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => $post_count,
            'paged' => $paged,
            'order' => $order,
            'orderby' => 'meta_value_num',
            'meta_query' => array(
//                array(
//                    'key' => '_visibility',
//                    'value' => array('catalog', 'visible'),
//                    'compare' => 'IN'
//                )
            ),
            'tax_query' => array(),
        );
        if (get_option('woocommerce_hide_out_of_stock_items', 'no') == 'yes') {
            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => 'outofstock',
                'compare' => 'NOT IN'
            );
        }
        $category = get_term_by('slug', 'credit', 'product_cat', 'ARRAY_A');
        ('credit');
        if ($category && isset($category['term_id']) && $category['term_id']) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category['term_id'],
                'operator' => 'NOT IN'
            );
        }
        switch ($sortby) {
            case 'bestseller':
                $args['meta_key'] = 'total_sales';
                break;
            case 'price':
                $args['meta_key'] = '_price';
                break;
            case 'recent':
                $args['orderby'] = 'date';
                break;
            default:
                $args['orderby'] = 'menu_order title';
                break;
        }

        if ($search_query != null) {
            global $wp_the_query;
            $args['s'] = $search_query;
            $wp_the_query->query_vars['s'] = $search_query;
            $wp_the_query->query_vars['post_type'] = 'product';
            $wp_the_query->query_vars['posts_per_page'] = $post_count;
            $wp_the_query->query_vars['wc_query'] = 'product_query';
            unset($args['orderby']);

        }
        if ($catid != null) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $catid
            );
        }


        $termid = (isset($_GET['term_id'])) ? trim($_GET['term_id']) : null;
        $termtype = (isset($_GET['term_type'])) ? trim($_GET['term_type']) : null;

        if ($termtype != null && $termid != null) {
            $args['tax_query'][] = array(
                'taxonomy' => $termtype,
                'field' => 'id',
                'terms' => $termid
            );
        }

        $products = new WP_Query($args);
        while ($products->have_posts()) :
            try {
                $products->the_post();
                $product_id = get_the_ID();
                if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                    if (isset($_GET['locale']) && function_exists('icl_object_id')) {
                        $product_id = icl_object_id($product_id, 'product', false, $_GET['locale']);
                        if ($product_id == '') {
                            continue;
                        }
                    }
                }
                $json[] = $this->appchar_get_product_by_id($product_id);
            } catch (Exception $e) {
                //continue;
            }
        endwhile;

        return $json;
    }

    public function getOrderPayUrl($order)
    {
        $pay_url = $order->get_checkout_payment_url();
        $pay_url = str_replace('pay_for_order=true&', '', $pay_url);
        return $pay_url;
    }

    private function authorizeAppcharToken()
    {
        global $token;
        $appchar_token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;

        if (!$appchar_token) {
            $appchar_token = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : null;
        }

        if (!$appchar_token) {
            $appchar_token = isset($_SERVER['HTTP_APPCHARTOKEN']) ? $_SERVER['HTTP_APPCHARTOKEN'] : null;
        }

        if (!$appchar_token) {
            wp_send_json_error(array('error' => 'token not set'), 401);
        }

        if (strpos(strtolower($appchar_token), 'basic') === 0) {
            list($username, $password) = explode(':', base64_decode(substr($appchar_token, 6)));
            if (!$token->token_validator($username)) {
                wp_send_json_error(array('error' => 'token is not valid'), 401);
            }

        } else {
            wp_send_json_error(array('error' => 'token is not valid'), 401);

        }

    }

    public static function update_order_review($entity_body, $method)
    {
        ob_start();

//        check_ajax_referer( 'update-order-review', 'security' );

        if (!defined('WOOCOMMERCE_CHECKOUT')) {
            define('WOOCOMMERCE_CHECKOUT', true);
        }

        if (WC()->cart->is_empty()) {
            $data = array(
                'fragments' => apply_filters('woocommerce_update_order_review_fragments', array(
                    'form.woocommerce-checkout' => '<div class="woocommerce-error">' . __('Sorry, your session has expired.', 'woocommerce') . ' <a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="wc-backward">' . __('Return to shop', 'woocommerce') . '</a></div>'
                ))
            );

            wp_send_json($data);

            die();
        }

        do_action('woocommerce_checkout_update_order_review', $entity_body->checkout->post_data);

        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

        if (isset($entity_body->destination->shipping_method) && is_array($entity_body->destination->shipping_method)) {
            foreach ($entity_body->destination->shipping_method as $i => $value) {
                $chosen_shipping_methods[$i] = wc_clean($value);
            }
        }

        WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
        WC()->session->set('chosen_payment_method', empty($entity_body->destination->payment_method) ? '' : $entity_body->destination->payment_method);

        if (isset($entity_body->destination->country)) {
            WC()->customer->set_country($entity_body->destination->country);
        }

        if (isset($entity_body->destination->state)) {
            WC()->customer->set_state($entity_body->destination->state);
        }

        if (isset($entity_body->destination->postcode)) {
            WC()->customer->set_postcode($entity_body->destination->postcode);
        }

        if (isset($entity_body->destination->city)) {
            WC()->customer->set_city($entity_body->destination->city);
        }

        if (isset($entity_body->destination->address)) {
            WC()->customer->set_address($entity_body->destination->address);
        }

        if (isset($entity_body->destination->address_2)) {
            WC()->customer->set_address_2($entity_body->destination->address_2);
        }

        if (!empty($entity_body->destination->country)) {
            WC()->customer->set_shipping_country($entity_body->destination->country);
            WC()->customer->calculated_shipping(true);
        }

        if (isset($entity_body->destination->state)) {
            WC()->customer->set_shipping_state($entity_body->destination->state);
        }

        if (isset($entity_body->destination->postcode)) {
            WC()->customer->set_shipping_postcode($entity_body->destination->postcode);
        }

        if (isset($entity_body->destination->city)) {
            WC()->customer->set_shipping_city($entity_body->destination->city);
        }

        if (isset($entity_body->destination->address)) {
            WC()->customer->set_shipping_address($entity_body->destination->address);
        }

        if (isset($entity_body->destination->address_2)) {
            WC()->customer->set_shipping_address_2($entity_body->destination->address_2);
        }
        WC()->cart->calculate_totals();
        // Get order review fragment
        ob_start();
        //“coupon_lines”: [{“id”: 2321, “code”: “321312”, “amount”: 321321}] //TODO add coupon to cart
//        woocommerce_order_review();
//         if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :
        $packages = WC()->shipping->get_packages();
        foreach ($packages as $i => $package) {
            $chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
        }
        $product_names = array();

        if (sizeof($packages) > 1) {
            foreach ($package['contents'] as $item_id => $values) {
                $product_names[] = $values['data']->get_title() . ' &times;' . $values['quantity'];
            }
        }
        if ($method == "shipping") {
            if (!$package['rates'])
                $package['rates'] = array();
            $shipping_met['shipping_methods'][] = array('rates' => array_values($package['rates']));
            if ($entity_body->version == 2) {
                wp_send_json_success(array_values($package['rates']));
            } else {
                wp_send_json_success($shipping_met);
            }
        } elseif ($method == "payment") {

        }
//         endif;
    }

    public function add_object_to($element)
    {
        if (isset($element['link_type']) && $element['link_type'] == 'single_category') {
//            $term = get_term($element['category_id'], 'product_cat');
//            $term->id=$term->term_id;
            $term = $this->products->get_product_category($element['category_id']);
            if (is_wp_error($term)) {
                return false;
            }
            $element['category'] = $term['product_category'];
            $element['category']['term_id'] = $element['category']['id'];
            $children = get_terms( 'product_cat', array(
                'parent'    => $element['category']['id'],
                'hide_empty' => false
            ) );
            $element['category']['has_child'] = ($children)?true:false;
        } elseif (isset($element['link_type']) && $element['link_type'] == 'product') {
            $element['product'] = $this->appchar_get_product_by_id($element['link']);
            if (is_wp_error($element['product'])) {
                return false;
            }
        } elseif (isset($element['link_type']) && $element['link_type'] == 'post') {
            $element['post'] = $this->appchar_get_post_by_id($element['link']);
            if (is_wp_error($element['post'])) {
                return false;
            }
        }  elseif (isset($element['link_type']) && $element['link_type'] == 'blog_single_category') {
            $link = (int) $element['link'];
            $element['category'] = get_term( $link, 'category' );
            $element['category']->id = $element['category']->term_id;
            if (is_wp_error($element['category'])) {
                return false;
            }
            if ( !$element['category'] ){
                return false;
            }
        } elseif (isset($element['link_type']) && $element['link_type'] == 'lottery') {
            $result = new WPLT_Lottery($element['link']);
            $result->leader_board = $result->get_leader_board();
            $result->winners = $result->get_winners();
            $element['lottery'] = $result;
        }
        return $element;
    }

    private function setupCartSession($entity_body)
    {

        if (isset($entity_body->customer_id) && $entity_body->customer_id) {
            wp_set_current_user($entity_body->customer_id);
        }

        WC()->cart->empty_cart(true);

        if (isset($entity_body->products) && is_array($entity_body->products)) {
            foreach ($entity_body->products as $product) {
                Appchar_Cart_Handler::add_to_cart_action(false, (array)$product);
            }
        }

        if (isset($entity_body->coupon_code) && $entity_body->coupon_code) {
            WC()->cart->add_discount(sanitize_text_field($entity_body->coupon_code));
        } else {
            WC()->cart->remove_coupons();
        }

        WC()->cart->set_session();

        WC()->cart->calculate_totals();

        if (isset($entity_body->checkout) && $entity_body->checkout) {
            $checkout = $entity_body->checkout;

            do_action('woocommerce_checkout_update_order_review', $checkout->post_data);

            $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
            if (isset($checkout->shipping_method) && is_array($checkout->shipping_method)) {
                foreach ($checkout->shipping_method as $i => $value) {
                    $chosen_shipping_methods[$i] = wc_clean($value);
                }
            }

            WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
            WC()->session->set('chosen_payment_method', (isset($checkout->payment_method) && $checkout->payment_method) ? $checkout->payment_method : '');

            if (isset($checkout->country)) {
                WC()->customer->set_country($checkout->country);
                WC()->customer->set_shipping_country($checkout->country);
                WC()->customer->calculated_shipping(true);
            }

            if (isset($checkout->state)) {
                WC()->customer->set_state($checkout->state);
                WC()->customer->set_shipping_state($checkout->state);
            }

            if (isset($checkout->postcode)) {
                WC()->customer->set_postcode($checkout->postcode);
                WC()->customer->set_shipping_postcode($checkout->postcode);
            }

            if (isset($checkout->city)) {
                WC()->customer->set_city($checkout->city);
                WC()->customer->set_shipping_city($checkout->city);
            }

            if (isset($checkout->address)) {
                WC()->customer->set_address($checkout->address);
                WC()->customer->set_shipping_address($checkout->address);
            }

            if (isset($checkout->address_2)) {
                WC()->customer->set_address_2($checkout->address_2);
                WC()->customer->set_shipping_address_2($checkout->address_2);
            }

            WC()->cart->calculate_totals();
        }
    }

    /**
     * getCartLineItems
     *
     * @param  mixed $entity_body
     * @return mixed $line_items
     *
     */
    private function getCartLineItems($entity_body)
    {
        /**
         * Here I explain some variables you may encounter with in this function, So you can have them at a glance
         * $line_items : An array of our whole products we calculated and returned to our client
         * $cart_products : the products that are in our cart(specifically they pointed as products in client requests)
         * $cart_products_to_add : the product that is in going to be added to our cart(Specifically they pointed as products to add in client requests)
         * $cart_item_key_array : an temporary array for transfering cart_item_keys fro WC_Cart to cart_products for later comparison
         * $cart_button_type : a variable that takes the cart button type as a value
         * WC_cart : this conatains a big array of woocommerce cart that we use for information
         * $cart_item_key : when we iterate through  WC_Cart this variable is the key for each product
         * $cart_item : when we iterate through  WC_Cart this variable is a mixed array of the whole information about product and cart
         */

        //Created an empty array for adding the products and returning it
        $line_items = array();
        //language-related conditions
        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            global $sitepress;
            if (isset($_GET['locale'])) {
                $lang = $_GET['locale'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            $sitepress->switch_lang($lang);
        }
        //getting cart_products and cart_product_to_add fro $entity_body and turning them into array from json
        $cart_products = json_decode(json_encode($entity_body->products), true);

        $cart_product_to_add = json_decode(json_encode($entity_body->product_to_add), true);

        $cart_products[] = $cart_product_to_add;


        /**
         * the main foeach-loop that iterate though WC_Cart, also we some information from WC_Cart from WC_Cart like
         * item_data, variation, customfields and subtotal, So it is neccessary for us to iterate though this cart.
        */
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                //Initiating item_data array for inserting information
                $item_data = array();
                $product = $this->products->get_product($cart_item['product_id']);
                if ($product['product']['featured_src'] == '' && isset($product['product']['images'])) {
                    $product['product']['featured_src'] = $product['product']['images'][0]['src'];
                }

                if (!empty($cart_item['data']->variation_id) && is_array($cart_item['variation'])) {
                    foreach ($cart_item['variation'] as $name => $value) {
                        if ('' === $value)
                            continue;

                        $taxonomy = wc_attribute_taxonomy_name(str_replace('attribute_pa_', '', urldecode($name)));

                        // If this is a term slug, get the term's nice name
                        if (taxonomy_exists($taxonomy)) {
                            $term = get_term_by('slug', $value, $taxonomy);
                            if (!is_wp_error($term) && $term && $term->name) {
                                $value = $term->name;
                            }
                            $label = wc_attribute_label($taxonomy);

                            // If this is a custom option slug, get the options name
                        } else {
                            $value = apply_filters('woocommerce_variation_option_name', $value);
                            $product_attributes = $cart_item['data']->get_attributes();
                            if (isset($product_attributes[str_replace('attribute_', '', $name)])) {
                                $label = urldecode(wc_attribute_label($product_attributes[str_replace('attribute_', '', $name)]['name']));
                            } else {
                                $label = urldecode($name);
                            }
                        }

                        $item_data[] = array(
                            'key' => urldecode($label),
                            'value' => urldecode($value)
                        );
                    }

                }
                if(isset($cart_item['wcpa_data'])){
                    foreach ($cart_item['wcpa_data'] as $wcpa_datum){
                        $item_data[] = array(
                            'key' => urldecode($wcpa_datum['label']),
                            'value'  => urldecode($wcpa_datum['value'])
                        );
                    }
                }

                //Initiating variation array for inserting information
                $variation = new stdClass();
                if ($cart_item['variation'] && count($cart_item['variation'])) {
                    foreach ($cart_item['variation'] as $key => $value) {
                        $variation->{urldecode($key)} = urldecode($value);
                    }
                }
                //Initiating custom_fields array for inserting information
                $custom_fields = new stdClass();
                if ($cart_item['wcpa_data'] && count($cart_item['wcpa_data'])) {
                    foreach ($cart_item['wcpa_data'] as $wcpa_datum) {
                        $custom_fields->{$wcpa_datum['name']} = urldecode($wcpa_datum['value']);
                    }
                }
                /**
                 * this is the line item(better to say the sum of whole information that we got till this line of code)
                 * that we return for each product individually
                */
                $line_item = array();
                /**
                 * here we iterate though the cart_products and make a comparison between each cart_products value and WC_Cart
                 * value. for comparison we use cart_item_key that inserted to cart_products earlier. We chose cart_item_keys because
                 * they are very unique and they are created for each variation individually.
                */
                foreach($cart_products as $res => $resValue) {
                    if($resValue['cart_item_key'] === $cart_item_key) {
                        if (isset($resValue['_measurement_needed']) && isset($resValue['weight_needed']) && isset($resValue['_measurement_needed_unit'])) {
                            $custom_fields->_measurement_needed = !isset($cart_item['pricing_item_meta_data']['weight']) ? $cart_item['weight'] : $cart_item['pricing_item_meta_data']['weight'];
                            $custom_fields->weight_needed = !isset($cart_item['pricing_item_meta_data']['weight']) ? $cart_item['weight'] : $cart_item['pricing_item_meta_data']['weight'];
                            $custom_fields->_measurement_needed_unit = $resValue['_measurement_needed_unit'];
                        }

                        //به خاطر نمایش وزنی که در حمل و نقل تایین میشد این شرطی که مخالف "number" باشد اضافه شد.
                        if (isset($resValue['_measurement_needed'])) {
                            if($product['product']['measurement_unit'] != "number") {
                                $item_data[] = array(
                                    'key' => $product ? $product['product']['unit_title'] : null,
                                    'value' => (!isset($cart_item['pricing_item_meta_data']['weight']) ? $cart_item['weight'] : $cart_item['pricing_item_meta_data']['weight']) . " " . $product['product']['measurement_unit'],
                                );
                            }
                        }
                        //If there a was zero-quantity item in cart we shall not return it to the client.
                        if($resValue['quantity'] == 0) {
                            break;
                        }
                        $line_item = array(
                            'product_id' => $cart_item['product_id'],
                            'product' => $product ? $product['product'] : null,
                            'variation_id' => $cart_item['variation_id'] ? $cart_item['variation_id'] : 0,
                            'quantity' => $cart_item['quantity'],
                            'weight' => !isset($cart_item['pricing_item_meta_data']['weight']) ? $cart_item['weight'] : $cart_item['pricing_item_meta_data']['weight'],
                            'line_total' => $cart_item['line_total'] ? $cart_item['line_total'] : 0,
                            'line_subtotal' => $cart_item['line_subtotal'] ? $cart_item['line_subtotal'] : 0,
                            'item_data' => $item_data,
                            'variation' => ($variation && count((array)$variation)) ? $variation : null,
                            'custom_fields' => ($custom_fields && count((array)$custom_fields)) ? $custom_fields : null,
                            'cart_item_key' => $cart_item_key,
                        );
                        //Here we add the line_item to our initiated line_items array and then break the for-loop to go for checking another products
                        $line_items[] = $line_item;
                        break;
                    }
                }
            }
        //returning the whole line_items to our client
        return $line_items;
    }

    public function appchar_get_post_by_id($post_id)
    {
//        $get_post = get_post($post_id);
        $get_post = new AppcharPostType($post_id);
        if (is_wp_error($get_post)) {
            return '';
        }

        $get_post2 = apply_filters('add_meta_to_post_endpoint', $get_post);
        if ($get_post2) {
            $get_product = $get_post2;
        }

        return $get_post;
    }

    public function get_individual_states($country_code) {
        return $WC()->countries->get_states($cc);
    }

    public function getPosts()
    {
        $catid = (isset($_GET['category_id'])) ? trim($_GET['category_id']) : null;
        $post_count = (isset($_GET['count'])) ? trim($_GET['count']) : 10;
        $order = (isset($_GET['order'])) ? trim($_GET['order']) : 'desc';
        $paged = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $search_query = (isset($_GET['q'])) ? $_GET['q'] : null;
        $json = array();
        // setup query
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => $post_count,
            'orderby' => 'date',
            'paged' => $paged,
            'order' => $order,
            'orderby' => 'meta_value_num',
//            'meta_query' => array(
//                array(
//                    'key' => '_visibility',
//                    'value' => array('catalog', 'visible'),
//                    'compare' => 'IN'
//                )
//            ),
            'tax_query' => array(),
        );
        $category = get_term_by('slug', 'credit', 'product_cat', 'ARRAY_A');
        ('credit');
        if ($category && isset($category['term_id']) && $category['term_id']) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category['term_id'],
                'operator' => 'NOT IN'
            );
        }

        if ($search_query != null) {
            $args['s'] = $search_query;
        }

        if ($catid != null) {
            $args['tax_query'][] = array(
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $catid
            );
        }
        $posts = new WP_Query($args);
        while ($posts->have_posts()) :
            try {
                $posts->the_post();
                $post_id = get_the_ID();
                if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
                    if (isset($_GET['locale']) && function_exists('icl_object_id')) {
                        $post_id = icl_object_id($post_id, 'post', false, $_GET['locale']);
                        if ($post_id == '') {
                            continue;
                        }
                    }
                }
                $json[] = $this->appchar_get_post_by_id($post_id);
            } catch (Exception $e) {
                //continue;
            }
        endwhile;
        return $json;
    }

    public function get_product_categories_v2($parent = 0)
    {
        try {
            // Permissions check
            /*if ( ! current_user_can( 'manage_product_terms' ) ) {
                throw new APPCHAR_WC_API_Exception( 'woocommerce_api_user_cannot_read_product_categories', __( 'You do not have permission to read product categories', 'woocommerce' ), 401 );
            }*/

            $product_categories = $this->products->get_product_categories();
            $product_categories2 = array();
            $product_categories2['product_categories'] = array();
            foreach ($product_categories['product_categories'] as $product_category) {
                if ($product_category['parent'] == $parent) {
                    $children = get_terms('product_cat', array(
                        'parent' => $product_category['id'],
                        'hide_empty' => false
                    ));
                    $product_category['has_child'] = ($children) ? true : false;
                    $product_categories2['product_categories'][] = $product_category;
                }
            }
            return $product_categories2;
        } catch (APPCHAR_WC_API_Exception $e) {
            return new WP_Error($e->getErrorCode(), $e->getMessage(), array('status' => $e->getCode()));
        }
    }

    public function get_product_categories_sticky()
    {
        try {
            // Permissions check
            /*if ( ! current_user_can( 'manage_product_terms' ) ) {
                throw new APPCHAR_WC_API_Exception( 'woocommerce_api_user_cannot_read_product_categories', __( 'You do not have permission to read product categories', 'woocommerce' ), 401 );
            }*/

//            $product_categories = $this->products->get_product_categories();
            $product_categories2 = array();
            $product_categories2['product_categories'] = array();
            $product_categories = get_terms('product_cat', array(
                'parent' => 0,
                'hide_empty' => false,
                'fields' => 'ids'
            ));
            foreach ($product_categories as $term_id) {
                if(AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')){
                    if(isset($_GET['locale']) && function_exists('icl_object_id')){
                        $term_id = icl_object_id($term_id , 'product_cat', false, $_GET['locale']);
                        if(!$term_id){
                            continue;
                        }
                    }
                }
                $product_category =  $this->products->get_product_category( $term_id, null );
                $childs = get_terms('product_cat', array(
                    'parent' => $term_id,
                    'hide_empty' => false,
                    'fields' => 'ids'
                ));
                $children = array();
                foreach ($childs as $child_term_id) {
                    if(AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')){
                        if(isset($_GET['locale']) && function_exists('icl_object_id')){
                            $child_term_id = icl_object_id($child_term_id , 'product_cat', false, $_GET['locale']);
                            if(!$child_term_id){
                                continue;
                            }
                        }
                    }
                    $has_child = get_terms('product_cat', array(
                        'parent' => $child_term_id,
                        'hide_empty' => false
                    ));
                    $child = $this->products->get_product_category( $child_term_id, null );

                    $child2 = $child['product_category'];

                    if ($has_child) {
                        $child2['has_child'] = true;
                    } else {
                        $child2['has_child'] = false;
                    }
                    $children[] = $child2;
                }
                $product_category = $product_category['product_category'];
                $product_category['children'] = $children;
                $product_categories2['product_categories'][] = $product_category;
            }
            return $product_categories2;
        } catch (APPCHAR_WC_API_Exception $e) {
            return new WP_Error($e->getErrorCode(), $e->getMessage(), array('status' => $e->getCode()));
        }
    }

    public function appchar_recaptcha_required()
    {
        return false;
    }

    /**
     * Get the reviews for a post with child
     *
     * @since 2.1
     * @param int $id the post ID to get reviews for
     * @param string $fields fields to include in response
     * @return array
     */
    public function get_post_reviews_with_child($id, $fields = null)
    {

        // $id = $this->validate_request($id, 'post', 'read');

        // if (is_wp_error($id)) {
        //     return $id;
        // }

        $comments = get_approved_comments($id, array('parent' => '0'));

        $reviews = array();

        foreach ($comments as $comment) {
            $reviews[] = new appcharReviewObject($comment);
        }
        return array('product_reviews' => apply_filters('appchar_api_post_reviews_response', $reviews, $id, $fields, $comments, $this->server));
    }

}
new Apphcar_Endpoint();
//new Apphcar_Endpoint();
