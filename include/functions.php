<?php
require 'plugin-update-checker/plugin-update-checker.php';
require 'general-setting.php';
require 'appchar_meta_product.php';



/*
 * add function from appchar.php
 */
function generate_image_for_app(){
    add_image_size( 'appcahr-1080', 1080, 1080 , false );
    add_image_size( 'appcahr-960', 960, 960 , false );
    add_image_size( 'appcahr-512', 512, 512 , false );
    add_image_size( 'appcahr-256', 256, 256 , false );
    add_image_size( 'appcahr-60', 300, 60 , false );
}
function auto_update_specific_plugins($update, $item)
{
    // Array of plugin slugs to always auto-update
    $plugins = array(
        'appchar-woocommerce'
    );
    if (in_array($item->slug, $plugins)) {
        return true; // Always update plugins in this array
    } else {
        return $update; // Else, use the normal API response to decide whether to update or not
    }
}
function add_enable_time($product_category){
    $categories_status = get_option('appchar_categories_status',false);
    $product_category['is_enabled'] = true;
    if(isset($categories_status[$product_category['id']])){
        $category_enable_from = $categories_status[$product_category['id']]['from'];
        $category_enable_to = $categories_status[$product_category['id']]['to'];
        $current_time = current_time('H',false);
        if($category_enable_from > $current_time || $current_time >= $category_enable_to){
            $product_category['is_enabled'] = false;
        }
    }
    return $product_category;
}
function appchar_validate_add_cart_item2($passed, $product_id, $quantity, $variation_id = '', $variations = '')
{
    $passed = true;
    $terms = get_the_terms( $product_id, 'product_cat' );
    $categories_status = get_option('appchar_categories_status',false);
    $product = wc_get_product($product_id);
    foreach ($terms as $term) {
        if(isset($categories_status[$term->term_id])){
            $category_enable_from = $categories_status[$term->term_id]['from'];
            $category_enable_to = $categories_status[$term->term_id]['to'];
            $current_time = current_time('H',false);

            if($category_enable_from > $current_time || $current_time >= $category_enable_to){
                $passed = false;
                if(isset($categories_status['error_message'])) {
                    $error_message = $categories_status['error_message'];
                }else {
                    $error_message = __('متاسفانه محصول {product_name} به سبد اضافه نشد.','appchar');
                }
                wc_add_notice(str_replace('{product_name}',$product->get_title(),$error_message), 'error');
            }
        }
    }
    return $passed;
}
function appchar_alert_bar_message()
{
    echo '<div id="appchar_alert_bar_message" style="background: #DE5145; padding: 15px; color: #fff; font-size: 18px; position: fixed; top: 0; left: 0; width: 100%; z-index: 999999;"><center>'
        . get_option('appchar_schedule_error_msg', '') .
        '</center></div>';
}
function appchar_validate_add_cart_item($passed, $product_id, $quantity, $variation_id = '', $variations = '')
{
    // do your validation, if not met switch $passed to false
    $passed = false;
    wc_add_notice(get_option('appchar_schedule_error_msg', ''), 'error');
    return $passed;

}

function save_time_to_receive_order($order_id,$entity_body){
    if(isset($entity_body->checkout->time_to_receive_order)) {
        add_post_meta($order_id, 'time_to_receive_order', $entity_body->checkout->time_to_receive_order);
    }
}
function appchar_add_time_to_receive_order_json($order){
    ob_clean();
    $order_id = $order['order']['id'];
    $order['order']['custom_fields'][] = array(
        'title' => 'زمان  تحویل سفارش به فروشگاه: ',
        'type' => 'text',
        'content' => get_post_meta($order_id, 'time_to_receive_order',true)
    );
    return $order;

}
function appchar_add_time_to_receive_order_json2($order){
    ob_clean();
    $order_id = $order['id'];
    $order['custom_fields'][] = array(
        'title' => 'زمان  تحویل سفارش به فروشگاه: ',
        'type' => 'text',
        'content' => get_post_meta($order_id, 'time_to_receive_order',true)
    );
    return $order;
}
function save_order_lat_lng($order_id,$entity_body){
    if(isset($entity_body->checkout->lat) && isset($entity_body->checkout->lng)) {
        $lat = $entity_body->checkout->lat;
        $lng = $entity_body->checkout->lng;
        add_post_meta($order_id, 'appchar_order_lat', $lat);
        add_post_meta($order_id, 'appchar_order_lng', $lng);
        $map_address = "https://www.google.com/maps?q=loc:$lat,$lng&z=16";
        add_post_meta($order_id, 'appchar_order_map_address', $map_address);
    }
}
function load_appchar_iconpicker_style($hook)
{
    if (strpos($hook, 'appchar') === false) {
        return;
    }
    wp_register_style('appchar_iconpicker_style1', APPCHAR_URL . 'assets/css/appchar.css', false, '1.0.0');
    wp_enqueue_style('appchar_iconpicker_style1');
    if(strpos($hook,'appchar-pages') !== false){
        wp_register_style('appchar-jquery-emojipicker-style', "http://cdn.appchar.com/emoji/css/jquery.emojipicker.css", false, '1.0.0');
        wp_enqueue_style('appchar-jquery-emojipicker-style');
        wp_register_script('appchar-jquery-emojipicker-tw-script', "http://cdn.appchar.com/emoji/js/jquery.emojipicker.js", array(), '1.0.0',true);
        wp_enqueue_script('appchar-jquery-emojipicker-tw-script');
        wp_register_style('appchar-jquery-emojipicker-tw-style', "http://cdn.appchar.com/emoji/css/jquery.emojipicker.tw.css", false, '1.0.0');
        wp_enqueue_style('appchar-jquery-emojipicker-tw-style');
        wp_register_script('appchar-jquery-emoji-tw-script', "http://cdn.appchar.com/emoji/js/jquery.emojis.js", array(), '1.0.0');
        wp_enqueue_script('appchar-jquery-emoji-tw-script');
        wp_enqueue_script('appchar-appchar-emoji-js',APPCHAR_JS_URL.'appchar.emoji.js');
    }
    if(strpos($hook,'appchar_schedule') !== false){
        wp_register_script( 'jquery1.11.3', 'https://code.jquery.com/jquery-1.11.3.min.js' );
        wp_add_inline_script( 'jquery1.11.3', 'var jquery1_11_3 = $.noConflict(true);' );
        wp_enqueue_script( 'appchar-schedule-javascript', APPCHAR_JS_URL .'schedule.js', array( 'jquery1.11.3' ) );
    }
}
function appchar_cards_load_wp_admin_script($hook)
{
    // Load only on ?page=appchar-blog
    //if ($hook != 'toplevel_appchar') {
    if(strpos($hook,'page_appchar-pages')=== true){
        return;
    }
    if (strpos($hook, 'appchar') === false) {
        return;
    }
    wp_enqueue_script(array('jquery','jquery-ui-core'));
    wp_register_script('appchar_cards_load_wp_admin_script', APPCHAR_JS_URL . 'home_banner.js');
    wp_enqueue_script('appchar_cards_load_wp_admin_script');
    wp_register_style('appchar_jquery_ui_style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('appchar_jquery_ui_style');
}
function my_custom_js() {
    echo '<script>function dpicker(dpid) { jQuery("#"+dpid).datepicker({ minDate: 0 });}</script>';
}
function add_special_offer_time_meta_to_endpoint($get_product){
    if(get_post_meta($get_product['product']['id'],'special_offer_date',true)) {
        $get_product['product']['special_offer_date'] = get_post_meta($get_product['product']['id'], 'special_offer_date',true);
    }
    return $get_product;
}
function appchar_add_credits_to_user_account($order_id, $old_status, $new_status)
{

    if ($new_status == 'processing') {
        $order = new WC_Order($order_id);
        $customer = get_userdata($order->customer_user);
        foreach ($order->get_items() as $item) {
            $product_id = $item['product_id'];
            $is_virtual = get_post_meta($product_id, '_virtual', false);
            if ($is_virtual[0] == 'yes') {
                $credit_amount = floatval(get_post_meta($product_id, "_credits_amount", true));
                $current_users_wallet_ballance = floatval(get_user_meta($order->customer_user, "_uw_balance", true));
                change_balance($credit_amount,$customer->ID,'خرید اعتبار',$customer->ID,$order_id,'add');

//                update_user_meta($customer->ID, "_uw_balance", ($credit_amount + $current_users_wallet_ballance));
                $order_status = $order->status;
                if($order_status != 'completed') {
                    $order->update_status('completed', 'virtual prooduct');
                }
            }
        }
    }
}
function add_reward_when_order_status_changed($order_id, $old_status, $new_status)
{
    $rewards_per_order = get_option('appchar_rewards_per_order', array());
    if (isset($rewards_per_order['status']) && $rewards_per_order['status'] == 'enable') {
        global $woocommerce, $post;
        $order = new WC_Order($order_id);
        $items = $order->get_items();
        foreach ($items as $item) {
            $product_id = $item[product_id];
        }
        $payment_method = get_post_meta($order_id, '_payment_method', true);
        $order_total = get_post_meta($order_id, '_order_total', true);
        if($rewards_per_order['minimum_amount_order']!=0 || $rewards_per_order['minimum_amount_order']!='') {
            if ($rewards_per_order['minimum_amount_order'] > $order_total) {
                return;
            }
        }
        if ($new_status == 'completed') {
            if ($payment_method != 'wpuw') {
                if ($rewards_per_order['amount_type'] == 'percent') {
                    $order_total = get_post_meta($order_id, '_order_total', true);
                    $reward_amount = $order_total * $rewards_per_order['amount'] / 100;
                } else {
                    $reward_amount = $rewards_per_order['amount'];
                }
                $current_users_wallet_ballance = floatval(get_user_meta($order->customer_user, "_uw_balance", true));
                change_balance($reward_amount,$order->customer_user,'امتیاز به ازای ثبت سفارش',$order->customer_user,$order_id,'add');
//                update_user_meta($order->customer_user, "_uw_balance", ($reward_amount + $current_users_wallet_ballance));
            }
        }
    }
}


/*
 *
 */
function ad_menu()
{
    global $extension, $notification,$osTicket;
    add_menu_page(__('Appchar', 'appchar'), __('Appchar', 'appchar'), 'manage_woocommerce', 'appchar', 'appchar_homepage');
//    add_submenu_page('appchar', __('build application', 'appchar'), __('build application', 'appchar'), 'manage_woocommerce', 'appchar', 'appchar_build_app');
    add_submenu_page('appchar', __('home page', 'appchar'), __('home page', 'appchar'), 'manage_woocommerce', 'appchar', 'appchar_homepage');
    add_submenu_page('appchar', __('pages', 'appchar'), __('pages', 'appchar'), 'manage_woocommerce', 'appchar-pages', 'appchar_pages');
    add_submenu_page('appchar', __('settings', 'appchar'), __('settings', 'appchar'), 'manage_woocommerce', 'appchar-setting', 'appchar_setting');
    add_submenu_page('appchar', __('Notification', 'appchar'), __('Notifications', 'appchar'), 'manage_woocommerce', 'appchar-notification', array($notification,'appchar_notification'));
    add_submenu_page('appchar', __('appchar extension', 'appchar'), __('appchar extension', 'appchar'), 'manage_woocommerce', 'appchar-extension', 'appchar_extension');
    if (get_option('redirect_to_appchar_about_page_check') != 'yes') {
        add_submenu_page('appchar', __('about appchar','appchar'), __('about appchar','appchar'), 'manage_woocommerce', 'about-appchar', 'about_page');
    }

    if($extension::has_settings()){
        $activeExtensions = $extension::getActiveExtensions();
        $first_plugin = $activeExtensions[0];
        unset($activeExtensions[0]);
        add_menu_page(__('Appchar Extensions','appchar'), __('Appchar Extensions','appchar'), 'manage_woocommerce', 'appchar_'.$first_plugin['plugincode'], $first_plugin['plugincode'].'_callback');
        add_submenu_page('appchar_'.$first_plugin['plugincode'], $first_plugin['title'], $first_plugin['title'], 'manage_woocommerce', 'appchar_'.$first_plugin['plugincode'] , $first_plugin['plugincode'].'_callback');

        foreach ($activeExtensions as $activeExtension){
            add_submenu_page( 'appchar_'.$first_plugin['plugincode'] , $activeExtension['title'], $activeExtension['title'], 'manage_woocommerce', 'appchar_'.$activeExtension['plugincode'], $activeExtension['plugincode'].'_callback');
        }
    }

    add_submenu_page( 'appchar', 'لیست درخواست های پشتیبانی', 'درخواست های پشتیبانی',
        'manage_options','osticket-ticket-list', array($osTicket,'showListOfTicketsPage'));
    add_submenu_page( 'appchar', 'درخواست پشتیبانی جدید', 'درخواست پشتیبانی جدید',
        'manage_options','osticket-new-ticket', array($osTicket,'addNewTicketPage'));
//    add_submenu_page( 'appchar', 'تنظیمات پشتیبانی', 'تنظیمات پشتیبانی',
//        'manage_options','osticket-settings',array($osTicket,'SettingsPage'));

}
function wallet_callback(){
    require_tmp('extensions/admin-wallet-setting');
}
function schedule_callback(){
    if(isset($_GET['tab'])){$current_tab=$_GET['tab'];}else{$current_tab='schedule_setting';}
    $tabs=array('schedule_setting'=>__('Schedule Setting','appchar'), 'categories_time'=>__('Categories Time','appchar'));
    ?>
    <div class="wrap">
        <h2><?php _e('Schedule Setting', 'appchar'); ?></h2>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ($tabs as $tab => $title2):
                $class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab $class' href='?page=appchar_schedule&tab=$tab'>$title2</a>";
            endforeach;
            ?>
        </h2>
        <?php
        if($current_tab=='schedule_setting'){
            require_tmp('extensions/admin-schedule-setting');
        }else{
            require_tmp('extensions/admin-set-categories-time');
        }
        ?>
    </div>
    <?php
}
function edit_checkout_fields_callback(){
    if(isset($_GET['tab'])){$current_tab=$_GET['tab'];}else{$current_tab='checkout_fields_setting';}
    $tabs=array('checkout_fields_setting'=>__('Checkout Fields Setting','appchar'), 'edit-state'=>__('Edit states','appchar'));
    ?>
    <div class="wrap">
        <h2><?php _e('Checkout Fields Setting', 'appchar'); ?></h2>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ($tabs as $tab => $title2):
                $class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab $class' href='?page=appchar_edit_checkout_fields&tab=$tab'>$title2</a>";
            endforeach;
            ?>
        </h2>
        
        <?php
        if($current_tab=='checkout_fields_setting'){
            require_tmp('extensions/admin-custom-checkout-fields');
        }else{
            require_tmp('extensions/admin-custom-states-field');
        }
        ?>
    </div>
    <?php
}
function custom_register_fields_callback(){
    require_tmp('extensions/admin-custom-register-fields');
}

function time_to_receive_order_callback(){
    require_tmp('extensions/admin-receive-order');
}

function appchar_toolbar() {
    $options = get_option('appchar_general_setting');
    if($options['abd'] == 1){
        global $wp_admin_bar;
        $page = get_site_url().'/wp-admin/admin.php?page=appchar-notification';
        $args = array(
            'id'     => 'appchar1',
            'title'  => '<span class="dashicons dashicons-megaphone"></span>'.__('notifications','appchar'),
            'href'   =>  "$page"
        );

        $wp_admin_bar->add_menu($args);
    }
}

function appchar_extension_setting(){
    if(isset($_GET['tab'])){$current_tab=$_GET['tab'];}else{$current_tab='schedule_setting';}
    $tabs=array('schedule_setting'=>__('schedule setting','appchar'), 'custom_register_fields'=>__('Custom Register Fields','appchar'), 'wallet'=>__('wallet setting','appchar'), 'custom_checkout_field'=>__('custom checkout field','appchar'), 'edit_states_field'=>__('edit states field','appchar'),'send_receive_order'=>__('send & receive order','appchar'));
    ?>
    <div class="wrap">
        <h2><?php _e('extension setting', 'appchar'); ?></h2>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ($tabs as $tab => $title2):
                $class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab $class' href='?page=appchar-extension-setting&tab=$tab'>$title2</a>";
            endforeach;
            ?>
        </h2>
        <?php
        switch($current_tab) {
            case 'wallet':
                if(AppcharExtension::extensionIsActive('wallet')){
                    require_tmp('extensions/admin-wallet-setting');
                }else{
                    require_tmp('extensions/eadmin-deactive-plugin');
                }
                break;
            case 'schedule_setting':
                if(AppcharExtension::extensionIsActive('schedule')){
                    require_tmp('extensions/admin-schedule-setting');
                }else{
                    require_tmp('extensions/admin-deactive-plugin');
                }
                break;
            case 'custom_register_fields':
                if(AppcharExtension::extensionIsActive('custom_register_fields')){
                    require_tmp('extensions/admin-custom-register-fields');
                }else{
                    require_tmp('extensions/admin-deactive-plugin');
                }
                break;

            case 'custom_checkout_field':
                if(AppcharExtension::extensionIsActive('edit_checkout_fields')){
                    require_tmp('extensions/admin-custom-checkout-fields');
                }else{
                    require_tmp('extensions/admin-deactive-plugin');
                }
                break;
            case 'edit_states_field':
                if(AppcharExtension::extensionIsActive('edit_checkout_fields')){
                    require_tmp('extensions/admin-custom-states-field');
                }else{
                    require_tmp('extensions/admin-deactive-plugin');
                }
                break;
            case 'send_receive_order':
                if(AppcharExtension::extensionIsActive('time_to_receive_order')){
                    require_tmp('extensions/admin-send-receive-order');
                }else{
                    require_tmp('extensions/admin-deactive-plugin');
                }
                break;
        }
        ?>
    </div>
    <?php
}

function appchar_homepage(){
    require_tmp('admin-homepage');
}
function appchar_build_app(){
    require_tmp('admin-build-application');
}
function appchar_pages(){
    if(isset($_GET['tab'])){$current_tab=$_GET['tab'];}else{$current_tab='select';}
    $tabs=array('select'=>__('select pages','appchar'),'show'=>__('show pages','appchar'));
    ?>
    <div class="wrap">

        <h2><?php _e('pages', 'appchar'); ?></h2>
        <p><?php _e('These pages will be displayed in the application menu. You can use these pages to introduce themselves (About Us) or links (Contact us) and ...','appchar') ?></p>
        <p style="font-weight: bold;"><?php _e("Please create your page with default editor and don't use page builder. We can't show shortcode to your app",'appchar') ?></p>
        <h2 class="nav-tab-wrapper">

            <?php
            foreach ($tabs as $tab => $title2):

                $class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';

                echo "<a class='nav-tab $class' href='?page=appchar-pages&tab=$tab'>$title2</a>";

            endforeach;
            ?>
        </h2>
        <?php
        switch($current_tab) {
            case 'select':
                require_tmp('appchar-post/admin-select-appchar-post');
                break;
//            case 'add':
//                require_tmp('appchar-post/admin-add-appchar-post');
//                break;
            case 'show':
                require_tmp('appchar-post/admin-show-appchar-posts');
                break;

        }
        ?>
    </div>
    <?php
}

function appchar_extension(){
    require_tmp('admin-add-extension');
}
function appchar_restrict_cart_setting(){
    require_tmp('admin-restrict-cart-setting');
}
function require_tmp($filename) {
    require APPCHAR_TMP_DIR.$filename.'.php';
}

function about_page() {
    require_tmp('about');
}
function appchar_text_order_shortcode () {

    return "
		<strong>جزییات سفارش : </strong><br/>
		<code>{phone}</code> = شماره موبایل خریدار   ،
		<code>{email}</code> = ایمیل خریدار   ،
		<code>{order_id}</code> = شماره سفارش  ،
		<code>{status}</code> = وضعیت سفارش<br/>
		<code>{price}</code> = مبلغ سفارش   ،
		<code>{all_items}</code> = آیتم های سفارش  ،
		<code>{all_items_qty}</code> = آیتم های سفارش همراه تعداد ،
		<code>{count_items}</code> = تعداد آیتم های سفارش  <br/>
		<code>{payment_method}</code> = روش پرداخت  ،
		<code>{shipping_method}</code> = روش ارسال  ،
		<code>{description}</code> = توضیحات خریدار  ،
		<code>{transaction_id}</code> = شماره تراکنش<br/><br/>

		<strong>جزییات صورت حساب : </strong><br/>
		<code>{b_first_name}</code> = نام خریدار   ،
		<code>{b_last_name}</code> = نام خانوادگی خریدار   ،
		<code>{b_company}</code> = نام شرکت   <br/>
		<code>{b_country}</code> = کشور   ،
		<code>{b_state}</code> = ایالت/استان   ،
		<code>{b_city}</code> = شهر   ،
		<code>{b_address_1}</code> = آدرس 1   ،
		<code>{b_address_2}</code> = آدرس 2   ،
		<code>{b_postcode}</code> = کد پستی<br/><br/>


		<strong>جزییات حمل و نقل : </strong><br/>
		<code>{sh_first_name}</code> = نام خریدار   ،
		<code>{sh_last_name}</code> = نام خانوادگی خریدار   ،
		<code>{sh_company}</code> = نام شرکت   <br/>
		<code>{sh_country}</code> = کشور   ،
		<code>{sh_state}</code> = ایالت/استان   ،
		<code>{sh_city}</code> = شهر   ،
		<code>{sh_address_1}</code> = آدرس 1   ،
		<code>{sh_address_2}</code> = آدرس 2   ،
		<code>{sh_postcode}</code> = کد پستی<br/><br/>

	";
}
function get_all_woo_status() {
    if ( !function_exists('wc_get_order_statuses') )
        return;
    $statuses = wc_get_order_statuses() ? wc_get_order_statuses() : array();
    $opt_statuses = array();
    foreach ( (array) $statuses as $status_val => $status_name ) {
        $opt_statuses[substr( $status_val, 3 )] = $status_name;
    }
    return $opt_statuses;
}

function login_with_email_address( &$username ) {
    if(is_email($username)){
        $user = get_user_by( 'email', $username );
        if (!empty( $user->user_login ))
            $username = $user->user_login;
    }
    return $username;
}

function appchar_is_plugin_there($plugin_dir) {
    $plugins = get_plugins($plugin_dir);
    if ($plugins) return true;
    return false;
}

function appchar_insert_csv_to_db($fileName){
    global $wpdb;
    $tablename = $wpdb->prefix . "appchar_device_list";

    if (($fp = fopen("$fileName", "r")) !== FALSE) {

        while (!feof($fp)) {
            if (!$line = fgetcsv($fp, 2000, ';', '"')) {
                continue;
            }
            $importSQL = "INSERT INTO `$tablename` (`id`, `device_id`, `os`, `device_model`, `app_version`, `last_activity`, `playtime`, `create_at`) VALUES(''," . $line[0] . "','" . $line[6] . "','" . $line[8] . "','" . $line[3] . "','" . $line[11] . "','" . $line[12] . "','" . $line[14] . "')";
            $wpdb->get_results($importSQL);
        }

        fclose($fp);
        return true;
    }else{
        return false;
    }
}
function addFieldToCat(){
    $banner_upload = get_term_meta($_GET['tag_ID'], 'banner', true);
    ?>
    <tr>
        <td style="width:190px">
            <label for="woosec"><?php echo __('select banner image (The standard size for photos should be 640x360 pixel with padding 10px )', 'appchar') ?></label>
        </td>
        <td>
            <div id="product_cat_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo $banner_upload; ?>" width="60px" height="60px"></div>

            <input type="button" name="banner_upload" id="slide_upload" value='انتخاب'
                   class="button"/>
            <input type="text" name="slide" id="slide"
                   style="width:300px;background-color: transparent; border: none; box-shadow: none; "
                   readonly/>
        </td>
    </tr>
    <?php
}
add_action ( 'product_cat_edit_form_fields', 'addFieldToCat');
function saveCategoryFields() {
    if ( isset( $_POST['slide'] ) ) {
        add_term_meta($_POST['tag_ID'], 'banner', $_POST['slide']);
    }
}
add_action ( 'edited_product_cat', 'saveCategoryFields');

function objToArray($obj, &$arr=array()){
    if(!is_object($obj) && !is_array($obj)){
        $arr = $obj;
        return $arr;
    }

    foreach ($obj as $key => $value)
    {
        if (!empty($value))
        {
            $arr[$key] = array();
            objToArray($value, $arr[$key]);
        }
        else
        {
            $arr[$key] = $value;
        }
    }
    return $arr;
}
function for_js_file($string){
    $string = str_replace("\n", "", $string);
    $string = str_replace("\t", "", $string);
    $string = str_replace("\r", "", $string);
    $string = str_replace("\"", "'", $string);
    return $string;
}

//---------------------- appchar custom tab - metabox block start--------------------------------

function appchar_add_custom_tab_meta_boxes_to_product()
{
    $options = get_option('appchar_general_setting');
    $custom_tab_count = (isset($options['custom_tab_count']))?$options['custom_tab_count']:get_option('appchar_custom_tab_count',0);
    if(intval($custom_tab_count)!=0){
        add_meta_box('appchar_custom_tab', __('appchar custom tab','appchar'), 'appchar_custom_tab_output_function');
    }
    add_meta_box('appchar_seller_address', __('appchar seller address','appchar'), 'appchar_seller_address_function');
}
function appchar_custom_tab_output_function( $post ){
    //so, dont ned to use esc_attr in front of get_post_meta
    $tab_value=  get_post_meta($post->ID, 'APPCHAR_CUSTOM_TAB' , true ) ;
    $options = get_option('appchar_general_setting');
    $custom_tab_count = (isset($options['custom_tab_count']))?$options['custom_tab_count']:get_option('appchar_custom_tab_count',0);
    $tab_count = intval($custom_tab_count);
    for ($i=0;$i<$tab_count;$i++){
        echo '<div class="metabox"><h3>عنوان</h3><div class="metainner"><div class="box-option">
<input type="text" id="" name="AppcharCustomTabTitle'.$i.'" value="'.htmlspecialchars_decode($tab_value[$i]['title']).'" size="50%">
          </div><div class="box-info"><label for="custom_tab_title1"></label></div></div></div>';
        echo '<div class="metabox"><h3>توضیحات</h3><div class="metainner"><div class="box-option">';
        wp_editor( htmlspecialchars_decode($tab_value[$i]['desc']), 'mettaabox_ID_stylee'.$i, $settings = array('textarea_name'=>'AppcharCustomTabDesc'.$i) );
        echo '</div><div class="box-info"><label for="custom_tab_title1"></label></div></div></div>';
    }
}
function save_custom_tab_data( $post_id ){
    $options = get_option('appchar_general_setting');
    $custom_tab_count = (isset($options['custom_tab_count']))?$options['custom_tab_count']:get_option('appchar_custom_tab_count',0);
    $tab_count = intval($custom_tab_count);
    $data = array();
    for ($i=0;$i<$tab_count;$i++){
        if (!empty($_POST['AppcharCustomTabTitle'.$i]))
        {
            $datta['title']=htmlspecialchars($_POST['AppcharCustomTabTitle'.$i]);
            $datta['desc']=htmlspecialchars($_POST['AppcharCustomTabDesc'.$i]);
            $data[]= $datta;
        }
    }
    update_post_meta($post_id, 'APPCHAR_CUSTOM_TAB', $data );
}
/*
 * add meta box for seller address
 */
function appchar_seller_address_function(){
    echo '<div class="metabox"><h3>عنوان</h3><div class="metainner"><div class="box-option">
<input type="text" id="" name="Appcharselleraddress'.$i.'" value="'.htmlspecialchars_decode($tab_value[$i]['title']).'" size="50%">
          </div><div class="box-info"><label for="custom_tab_title1"></label></div></div></div>';
    echo '<div class="metabox"><h3>توضیحات</h3><div class="metainner"><div class="box-option">';
    wp_editor( htmlspecialchars_decode($tab_value[$i]['desc']), 'mettaabox_ID_stylee'.$i, $settings = array('textarea_name'=>'AppcharCustomTabDesc'.$i) );
    echo '</div><div class="box-info"><label for="custom_tab_title1"></label></div></div></div>';
}

//---------------------- appchar custom tab - metabox block end--------------------------------

function remaining_time(){
    $current_date = strtotime(date('H:i:s'));
    $date = '24:00:00';
    $remaining = strtotime($date)-$current_date;
    $remaining = ($remaining>0)?$remaining:0;
    return $remaining;
}

function appchar_migrate_db(){
    if(!get_option('appchar_notification_setting',false) && get_option('appchar_setting',false)){
        add_option('appchar_notification_setting',get_option('appchar_setting',false));
    }
}

/*
 * get locale without country
 * it is not a clean solution because some locales may be more than 2 charachters and may be we need to have locale and language code together to seperate languages 
 */
function appchar_get_locale(){
    $locale = get_locale();
    return substr($locale, 0, 2);
}

function appchar_delete_woocommerce_token(){
    global $wpdb;
    $table_name = $wpdb->prefix.'woocommerce_api_keys';
    $sql = 'DELETE FROM `'.$table_name.'` WHERE `description` LIKE \'%appchar%\';';

    try {
        $wpdb->query($sql);

        return true;
    } catch (Exception $e) {
        return 'Error! '. $wpdb->last_error;
    }
}
$general_setting = get_option('appchar_general_setting');
if(get_option('woocommerce_default_catalog_orderby') == 'menu_order' && $general_setting['out_stock_products_end']) {
    add_action('woocommerce_updated_product_sales', 'woocommerce_updated_product_sales_database');
    function woocommerce_updated_product_sales_database($product_id) {
        global $wpdb;
        $product_meta = wc_get_product($product_id);
        $total_sales = $product_meta->get_total_sales();
        $menu_order = 0;
        if($product_meta->get_stock_status() != 'outofstock') {
            $menu_order = -1 * absint($total_sales);
        }
        $wpdb->update($wpdb->prefix.'posts', array('menu_order' => $menu_order), array('Id' => $product_id, 'post_type' => 'product'));
    }
    add_action('woocommerce_product_set_stock_status', 'woocommerce_product_set_stock_status_custom');
    function woocommerce_product_set_stock_status_custom($product_id) {
        global $wpdb;
        $product_meta = wc_get_product($product_id);
        $total_sales = $product_meta->get_total_sales();
        $menu_order = 0;
        if($product_meta->get_stock_status() != 'outofstock') {
            $menu_order = -1 * absint($total_sales);
        }
        $wpdb->update($wpdb->prefix.'posts', array('menu_order' => $menu_order), array('Id' => $product_id, 'post_type' => 'product'));
    }  
}
?>
