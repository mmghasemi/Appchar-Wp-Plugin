<?php

class class_Appchar_Notifiation
{
    public function __construct(){
    }
    public function get_notifications_count($user_id=0){
        global $wpdb;
        $query = "SELECT COUNT(*) FROM `{$wpdb->prefix}appchar_notification_log` where `user_id`={$user_id}";
        $notifications_count = $wpdb->get_var( $query );
        return (int) $notifications_count;
    }
    public function add_block_to_admin_lottery($lottery_id){

        echo '<div style="width: 48%; min-width: 330px; float: right;">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <div class="postbox">
                            <h3 class="title">ارسال نوتیفیکیشن</h3>
                            <div class="inside">
                                <div id="settings">';
        if(isset($_POST['send_notification'])){
            $args = array();
            $result = new WPLT_Lottery($lottery_id);
            $result->leader_board = $result->get_leader_board();
            $result->winners = $result->get_winners();
            $args['data'] = array(
                'type' => 'lottery',
                'lottery' => $result,
            );

            if(isset($_POST["msg-title"])){
                $title = $_POST["msg-title"];
            }else{
                $title = __('manual massage','appchar');
            }

            $message = $_POST["message"];
            print_r($this->appchar_sendNOT($title , $message, $args));
        }
        echo'<form method="post" action="#">
                 <p>عنوان نوتیفیکیشن</p>
                 <input id="message" name="msg-title" type="text" cols="50" rows="5">
                 <p>پیام خود را وارد کنید:</p>
                 <textarea id="message" name="message" type="text" cols="50" rows="5"></textarea>
                 <p>* لطفا از کدهای html استفاده نکنید.</p>
                 <p class="submit"><input type="submit" name="send_notification" id="submit" class="button button-primary" value="ارسال"></p>
             </form>';
        echo '</div></div></div></div></div></div>';

    }
    public function set_device_by_id($device_id,$user_id){
        global $wpdb;
        if ($device_id!='') {
            $device = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}appchar_user_devices` WHERE `user_device_id`='$device_id'" );
            if($device->user_id){
                return $device->id;
            }
            $wpdb->insert($wpdb->prefix . 'appchar_user_devices', array('user_device_id' => $device_id, 'user_id' => $user_id));
        }else{
            return false;
        }
    }


    public function get_devices_by_user_id($user_id){
        global $wpdb;
        $devices = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}appchar_user_devices` WHERE `user_id`='$user_id'" );
        $device_ids = array();
        foreach ($devices as $values){
            $device_ids[]=$values->user_device_id;
        }
        if($device_ids){
            return $device_ids;
        }
        return false;
    }

    public function get_username_by_device_id($device_id){
        global $wpdb;
        $device = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}appchar_user_devices` WHERE `user_device_id`='$device_id'" );
        $user_id=$device->user_id;
        $user = get_user_by( 'id', $user_id );
        $user_name=$user->user_login;
        return $user_name;
    }
    public function get_userid_by_device_id($device_id){
        global $wpdb;
        $device = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}appchar_user_devices` WHERE `user_device_id`='$device_id'" );
        $user_id=$device->user_id;
        if($user_id){
            return $user_id;
        }
        return false;
    }
    public function get_id_by_device_id($device_id){
        global $wpdb;
        $device = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}appchar_user_devices` WHERE `user_device_id`='$device_id'" );
        $id=$device->id;
        if($id){
            return $id;
        }
        return false;
    }


    public function save_message($args){
        global $wpdb;
        $current_date = date('Y-m-d H:i:s');
        if(isset($args['include_player_ids'])){
            foreach ($args['include_player_ids'] as $player_id){
                $userid = $this->get_userid_by_device_id($player_id);
                if($userid){
                    $wpdb->insert($wpdb->prefix . 'appchar_notification_log', array('headings' => $args['headings']['en'],'contents' => $args['contents']['en'], 'user_id' => $userid, 'notification_time'=>$current_date));
                }
            }
        }else{
            $wpdb->insert($wpdb->prefix . 'appchar_notification_log', array('headings' => $args['headings']['en'],'contents' => $args['contents']['en'], 'user_id' => 0, 'notification_time'=>$current_date));
        }
    }
    public function get_message_by_username($username){
        $user = get_user_by( 'user_login', $username );
        global $wpdb;
        $notifications = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}appchar_notification_log` WHERE `user_id`='$user->ID' ORDER BY id DESC" );
        return $notifications;
    }
    public function get_message_by_userid($userid=0,$paged=1,$count=15){
        global $wpdb;
        $offset = ($paged-1)*$count;
        $notifications = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}appchar_notification_log` WHERE `user_id`='$userid'  ORDER BY id DESC limit $offset, $count;" );
        return $notifications;
    }






    public function appchar_notification(){
        if(isset($_GET['tab'])){$current_tab=$_GET['tab'];}else{$current_tab='settings';}
        $tabs=array('manual'=>__('Send notifications manually','appchar'), 'display-notifications'=>__('All notifications', 'appchar'), 'display-devices'=>__('All Devices', 'appchar'), 'settings'=>__('Notification Settings','appchar'));
        ?>
        <div class="wrap">

            <h2><?php _e('notification', 'appchar'); ?></h2>
            <h2 class="nav-tab-wrapper">

                <?php
                foreach ($tabs as $tab => $title):

                    $class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';

                    echo "<a class='nav-tab $class' href='?page=appchar-notification&tab=$tab'>$title</a>";

                endforeach;
                ?>
            </h2>
            <?php
            switch($current_tab) {
                case 'manual':
                    $this->appchar_send_new_msg();
                    break;
                case 'display-notifications':
                    $this->appchar_display_notifications();
                    break;
                case 'display-devices':
                    $this->appchar_display_devices();
                    break;
                case 'settings':
                    $this->appchar_display_setting_page();
                    break;
            }
            ?>
        </div>
        <?php
    }
// //-------------------------------     Delete Notification     ---------------------------------------
//   public function deleteNotification() {
//
//   }

//-------------------------------     Send Notification       ---------------------------------------
    public function appchar_send_new_msg(){
        global $appcharEndpoint;
        global $isSingle;

        $args = array();
        $userid = array();
        if (isset($_GET['id'])) {
            if (is_array($_GET['id'])){
                foreach ($_GET['id'] as $ids) {
                    $userid[] = @trim($ids);
                }
            }else{
                $userid[] = @trim($_GET['id']);
                $isSingle = true;
            }
            $args['include_player_ids'] = $userid;

        }
        require APPCHAR_TMP_DIR.'admin-notification-new-notification.php';
        if(isset($_POST['message'])) {
            if(isset($_POST["msg-title"])){
                $title = $_POST["msg-title"];
            }else{
                $title = __('manual massage','appchar');
            }
            $args['big_picture'] = isset($_POST['big_picture']) ? $_POST['big_picture'] : "";
            if ($_POST['url_options'] != 'none') {
              $urlOptions = $_POST['url_options'];
              switch ($urlOptions) {
                // case 'url':
                //   $args['url'] = isset($_POST['url-text']) ? $_POST['url-text'] : "";
                //   break;
                case 'post':
                  $postId = isset($_POST['url-text']) ? $_POST['url-text'] : "";
                  $post = $appcharEndpoint->appchar_get_post_by_id($postId);
                  $args['data'] = array(
                    'type' => 'post',
                    'post' => array(
                      'id' => $post->id,
                      'title' => $post->title,
                      'date' => $post->date,
                      'thumbnail' => $post->thumbnail,
                    ),
                  );
                  break;
                case 'product':
                  $productId = isset($_POST['url-text']) ? $_POST['url-text'] : "";
                  $product = $appcharEndpoint->appchar_get_product_by_id($productId);
                  $args['data'] = array(
                    'type' => 'product',
                    'product' => array(
                      'id' => $product['id'],
                      'title' => $product['title'],
                      'featured_src' => $product['featured_src'],
                    ),
                  );
                  break;

                default:
                  // code...
                  break;
              }
            } else {
              // code...
            }
            $desiredHour = "";
            if(isset($_POST['sendAfterState'])) {
              $desiredHour = $_POST['sendAfterStateDay'];
              $desiredDay = isset($_POST['days']) ? $_POST['days'] : "";
              $shortendDays = array("Sat", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri");
              $days = array("Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday");
              $desiredDayIndex = array_search($desiredDay, $shortendDays);
              $strForTimeStamp = $days[$desiredDayIndex];

              if (($timestamp = strtotime($strForTimeStamp)) === false) {
                  //echo "The string ($str) is bogus";
              } else {
                //$finalDate = date('F dS Y g:i:s A eP', $timestamp) . "";
                $timestamp = strtotime($strForTimeStamp);
                if ($desiredHour != "") {
                  $finalDate = date('F dS Y ', $timestamp) . $desiredHour . date(' eP', $timestamp); //$finalDate = date('F dS Y', $timestamp) . $desiredHour . date('eP', $timestamp)
                  $args['send_after'] = $finalDate;
                } else {
                  $finalDate = date('F dS Y g:i:s A eP', $timestamp) . "";
                  $args['send_after'] = $finalDate;
                }
              }
            }




            $message = $_POST["message"];
            print_r($this->appchar_sendNOT($title , $message, $args));
        }
    }
    public function sendMessage($args){
        global $isSingle;
        $options = get_option('appchar_notification_setting');
        $delayed_option = "immediate";
        $delivery_time_of_day = "";
        if($options['delayed_option'] != 'none') {
          $delayed_option = $options['delayed_option'];
        }
        if($options['delivery_time_of_day'] != 'none') {
          $delivery_time_of_day = $options['delivery_time_of_day'];
        }
        $default_args = array(
            'app_id' => $options['app-id'],
            'delayed_option' => $isSingle ? "immediate" : $delayed_option,
            'delivery_time_of_day' => $isSingle ? "" : $delivery_time_of_day . ':00',
            'ttl' => $isSingle ? "" : $options['ttl'],
        );


        $fields = wp_parse_args($args,$default_args);

        $this->save_message($fields);
        $fields = json_encode($fields);
        $url = "http://onesignal.appchar.com/api/v1/notifications";
        $args = array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers' => array(
                'Authorization' => 'Basic ' . $options['api-key'],
                'Content-Type'  => 'application/json'
            ),
            'body'        => $fields,
        );
        $response = wp_remote_get( $url, $args );
        if(is_wp_error($response)){
            $error_message = $response->get_error_message();
            return "خطایی رخ داده است لطفا مجددا تلاش کنید";//"Something went wrong: $error_message";
        }

        return "درخواست ارسال نوتیفیکیشن با موفقیت ارسال شد لطفا برای بررسی وضعیت نوتیفیکشن به قسمت نمایش نوتیفیکیشن ها مراجعه نمایید.";
    }
    public function appchar_sendNOT($title, $message, $other_args=array()) {
        global $wpdb;
        $content = array(
            "en" => $message
        );
        $head = array(
            "en" => $title
        );
        $args = array(
//            'data' => array("foo"=>"bar"),
//            'android_background_layout' =>array(
//                'image' => 'http://dreamicus.com/data/baby/baby-01.jpg',
//                "headings_color"=> "FFFF0000",
//                "contents_color"=> "FF00FF00"
//            ),
//            'small_icon'=>'http://dreamicus.com/data/baby/baby-01.jpg',
//            'large_icon'=>'http://dreamicus.com/data/baby/baby-01.jpg',
//            'big_picture'=>'http://dreamicus.com/data/baby/baby-01.jpg',
            'contents' => $content,
            'headings' => $head,
			'delayed_option' => 'immediate'
        );
        //        if($other_args){
//            $args['include_player_ids']=$other_args;
//        }else{
//            $args['included_segments'] = array('All');
//        }
        $args = wp_parse_args($other_args,$args);
        if(!isset($args['include_player_ids'])){
            $args['included_segments'] = array('All');
        }
        $response = $this->sendMessage($args);
        $options = get_option('appchar_notification_setting');
        if($options['show_result'] != false){
            $inf= "<div id='message' class='updated'><p><b>".__('Message sent.','appchar')."</b><i>&nbsp;&nbsp;($response)</i></p><p>$title. ' : ' .$message</p></div>";
            global $gcm_result;
            $gcm_result = $inf;
            return $inf;
        }

    }
//----------------      get information from one signal      -------------------------------
    public function get_from_onesignal($get_what){
        $options = get_option('appchar_notification_setting');
        $app_id = $options['app-id'];
        $url = "http://onesignal.appchar.com/api/v1/$get_what?app_id=$app_id";
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . $options['api-key'],
                'Content-Type'  => 'application/json'
            )
        );
        $response = wp_remote_get( $url, $args );
        if(is_wp_error( $response )){
            return '';
        }
        return $response['body'];
    }
    public function get_data_for_pagination($limit, $offset) {
      $options = get_option('appchar_notification_setting');
      $app_id = $options['app-id'];
      $url = "http://onesignal.appchar.com/api/v1/players?app_id=" . $app_id . "&limit=" . $limit . "&offset=" . $offset;
      $args = array(
        'headers' => array(
          'Authorization' => 'Basic ' . $options['api-key'],
          'Content-Type' => 'appliaction/json'
        )
      );
      $response = wp_remote_get($url, $args);
      if(is_wp_error($response)) {
        return '';
      }
      return $response['body'];
    }
    public function appchar_display_devices(){
        $device = $this->get_from_onesignal('players');
        $data_single = json_decode($device, true);
		$data = json_decode($device, true);
        require APPCHAR_TMP_DIR.'admin-notification-device-list.php';
    }
    public function appchar_display_notifications(){
        $device = $this->get_from_onesignal('notifications');
        $data = json_decode($device, true);
        require APPCHAR_TMP_DIR.'admin-notification-notif-list.php';

    }
//---------------------      settings       ---------------------------------
    public function appchar_display_setting_page() {
        require APPCHAR_TMP_DIR.'admin-notification-settings.php';
    }
    public function send_notification_when_order_status_changed( $order_id, $old_status, $new_status ){

        global $wpdb;
        global $isSingle;

        $options = get_option('appchar_notification_setting');
        $orno = $options['orno'];
        if($orno==1){
            $isSingle = true;
            $order = new WC_Order( $order_id );
            $user_id = $order->get_user_id();

            $product_list	= $this->get_product_list_appchar( $order );
            $all_items	= $product_list['names'] . '__vsh__' . $product_list['names_qty'];

            $msg=$this->appchar_str_replace_tags_order($options[$new_status], $new_status,$order_id,$order,$all_items,'');
            $user_devices = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}appchar_user_devices WHERE user_id=$user_id" );

            foreach ($user_devices as $key=>$values){
                $user_device_id[]=$values->user_device_id;
            }
            if($user_device_id){
                $args['include_player_ids']= $user_device_id;
                $this->appchar_sendNOT(__('change status','appchar'),$msg,$args);
            }
        }
    }
    public function appchar_on_all_status_transitions( $new_status, $old_status, $post ) {

        global $gcm_result;
        global $isSingle;

        $options = get_option('appchar_notification_setting');
        $post_id = $post->ID;
        $blog_title = get_bloginfo( 'name' );
        $post_title = get_the_title($post);
        $post_url = get_permalink($post);
        $post_image = get_the_post_thumbnail_url($post);
        $post_id = get_the_ID($post);
        $post_author = get_the_author_meta('display_name', $post->post_author);
        if ( $options['pdin'] &&  $post->post_type == 'product') {
            global $appcharEndpoint;
            if(  $new_status != $old_status && $new_status == 'publish')
            {
                $isSingle = false;
                $find=array('{product name}','{blog title}');
                $replace=array($post_title,$blog_title);
                $message = str_replace( array( '<br>' , '<br/>' , '<br />', '&nbsp;' ), array( '' , '' , '', ' ' ), str_replace( $find, $replace, $options['newproduct'] ) );
                //$message = sprintf($options['newproduct'],$post_title,$blog_title);
                $gcm_result = $message;
                $np = __('new product','appchar');
                $args = array();
                if(AppcharExtension::extensionIsActive('advance_notification')) {
                    $product = $appcharEndpoint->appchar_get_product_by_id($post_id);
                    $args['data'] = array(
                        'type' => 'product',
                        'product' => array(
                            'id'    => $product['id'],
                            'title' => $product['title'],
                            'featured_src' => $product['featured_src'],
                        ),
                    );
                    if ($post_image) {
                        switch ($options['notificationImageType']) {

                            case 'none':
                                break;
                            case 'background':
                                $args['android_background_layout'] = array(
                                    'image' => $post_image,
                                    "headings_color" => "FFFF0000",
                                    "contents_color" => "FF00FF00"
                                );
                                break;
                            case 'large':
                                $args['large_icon'] = $post_image;
                                $args['big_picture'] = $post_image;
                                break;
                        }
                    }
                }
                $this->appchar_sendNOT($np, $message,$args);
            }

        }
        if ( $options['poin'] &&  $post->post_type == 'post') {
            global $appcharEndpoint;
            if(  $new_status != $old_status && $new_status == 'publish')
            {
                $find=array('{product name}','{blog title}');
                $replace=array($post_title,$blog_title);
                $message = str_replace( array( '<br>' , '<br/>' , '<br />', '&nbsp;' ), array( '' , '' , '', ' ' ), str_replace( $find, $replace, $options['newpost'] ) );
                //$message = sprintf($options['newproduct'],$post_title,$blog_title);
                $gcm_result = $message;
                $np = __('new post','appchar');
                $args = array();
                if(AppcharExtension::extensionIsActive('advance_notification')) {
                    $post = $appcharEndpoint->appchar_get_post_by_id($post_id);
                    $args['data'] = array(
                        'type' => 'post',
                        'post' => array(
                            'id'        => $post->id,
                            'title'     => $post->title,
                            'date'      => $post->date,
                            'thumbnail' => $post->thumbnail,
                        ),
                    );
                    if ($post_image) {
                        switch ($options['notificationImageType']) {

                            case 'none':
                                break;
                            case 'background':
                                $args['android_background_layout'] = array(
                                    'image' => $post_image,
                                    "headings_color" => "FFFF0000",
                                    "contents_color" => "FF00FF00"
                                );
                                break;
                            case 'large':
                                $args['large_icon'] = $post_image;
                                $args['big_picture'] = $post_image;
                                break;
                        }
                    }
                }
                $this->appchar_sendNOT($np, $message,$args);
            }
        }
    }
    public function product_sales_price_changed($post_id){
        global $gcm_result,$appcharEndpoint;
        $options = get_option('appchar_notification_setting');
        if ( $options['pdpc'] == false )
            return ;
        $blog_title = get_bloginfo( 'name' );
        $WC_Product = wc_get_product( $post_id );
        $regular_price = $WC_Product->get_regular_price();
        $sale_price = $WC_Product->get_sale_price();
        if( is_numeric($sale_price) && is_numeric($regular_price) && $regular_price-$sale_price>0 && $sale_price>0 )
        {
            $percent_price = 100 - ( ($sale_price * 100.0) / $regular_price );
            $percent_price = round($percent_price, 1);
            $sale_price_dates_from 	= ( $date = get_post_meta( $post_id, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
            $sale_price_dates_to 	= ( $date = get_post_meta( $post_id, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';

            $find=array('{product name}','{percent price}','{blog title}');
            $replace=array(get_the_title($post_id),$percent_price,$blog_title);
            $message = str_replace( array( '<br>' , '<br/>' , '<br />', '&nbsp;' ), array( '' , '' , '', ' ' ), str_replace( $find, $replace, $options['spproduct'] ) );
            $np=__('special product','appchar');
            $args = array();
            if(AppcharExtension::extensionIsActive('advance_notification')) {
                $product = $appcharEndpoint->appchar_get_product_by_id($post_id);
                $args['data'] = array(
                    'type' => 'product',
                    'product' => array(
                        'id'    => $product['id'],
                        'title' => $product['title'],
                        'featured_src' => $product['featured_src'],
                    ),
                );
                $post_image = get_the_post_thumbnail_url($post_id);
                if ($post_image) {
                    switch ($options['notificationImageType']) {

                        case 'none':
                            break;
                        case 'background':
                            $args['android_background_layout'] = array(
                                'image' => $post_image,
                                "headings_color" => "FFFF0000",
                                "contents_color" => "FF00FF00"
                            );
                            break;
                        case 'large':
                            $args['large_icon'] = $post_image;
                            $args['big_picture'] = $post_image;
                            break;
                    }
                }
            }
            $this->appchar_sendNOT($np, $message,$args);
        }
    }
    public function get_product_list_appchar( $order ) {
        $product_list = '';
        $order_item = $order->get_items();
        $prodct_name = $prodct_id = array();
        foreach( (array) $order_item as $product ) {
            $prodct_id[] = $product['product_id'];
            $prodct_name[] = $product['name'];
            $prodct_name_qty[] = $product['name'] . '(' . $product['qty'] . ')';
        }
        $product_names = implode( '-', $prodct_name );
        $prodct_name_qtys = implode( '-', $prodct_name_qty );
        $prodct_ids = implode( ',', $prodct_id );
        return array (
            'names_qty' => $prodct_name_qtys,
            'names' => $product_names ,
            'ids' => $prodct_ids
        );
    }
    public function appchar_str_replace_tags_order( $content, $order_status, $order_id, $order , $all_items, $vendor_items ) {


        $price = intval($order->get_total()). ' '. sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order->get_order_currency() ), '' );
        $count_items = count(explode( '-' , $all_items));
        list($all_items, $all_items_qty) = explode ( '__vsh__' , $all_items );

        $payment_gateways = array();
        if ( WC()->payment_gateways() )
            $payment_gateways = WC()->payment_gateways->payment_gateways();
        $payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
        $payment_method = ( isset( $payment_gateways[ $payment_method ] ) ? esc_html( $payment_gateways[ $payment_method ]->get_title() ) : esc_html( $payment_method ) );
        $shipping_method = esc_html( $order->get_shipping_method() );

        $country = WC()->countries;

        $bill_country = ( isset( $country->countries[ $order->get_billing_country() ] ) ) ?$country->countries[ $order->get_billing_country() ] : $order->get_billing_country();
        $bill_state = ( $order->get_billing_country() && $order->get_billing_state() && isset( $country->states[ $order->get_billing_country() ][ $order->get_billing_state() ] ) ) ? $country->states[ $order->get_billing_country() ][ $order->get_billing_state() ] : $order->get_billing_state();

        $shipp_country = ( isset( $country->countries[ $order->get_shipping_country() ] ) ) ?$country->countries[ $order->get_shipping_country() ] : $order->get_shipping_country();
        $shipp_state = ( $order->get_shipping_country() && $order->get_shipping_state() && isset( $country->states[ $order->get_shipping_country() ][ $order->get_shipping_state() ] ) ) ? $country->states[ $order->get_shipping_country() ][ $order->get_shipping_state() ] : $order->get_shipping_state();

        $post = get_post( $order_id );

        $find = array(
            '{b_first_name}',
            '{b_last_name}',
            '{b_company}',
            '{b_address_1}',
            '{b_address_2}',
            '{b_state}',
            '{b_city}',
            '{b_postcode}',
            '{b_country}',
            '{sh_first_name}',
            '{sh_last_name}',
            '{sh_company}',
            '{sh_address_1}',
            '{sh_address_2}',
            '{sh_state}',
            '{sh_city}',
            '{sh_postcode}',
            '{sh_country}',
            '{phone}',
            '{email}',
            '{order_id}',
            '{status}',
            '{price}',
            '{all_items}',
            '{all_items_qty}',
            '{count_items}',
            '{vendor_items}',
            '{transaction_id}',
            '{payment_method}',
            '{shipping_method}',
            '{description}',
        );
        $replace = array(
            $order->get_billing_first_name(),
            $order->get_billing_last_name(),
            $order->get_billing_company(),
            $order->get_billing_address_1(),
            $order->get_billing_address_2(),
            $bill_state,
            $order->get_billing_city(),
            $order->get_billing_postcode(),
            $bill_country,
            $order->get_shipping_first_name(),
            $order->get_shipping_last_name(),
            $order->get_shipping_company(),
            $order->get_shipping_address_1(),
            $order->get_shipping_address_2(),
            $shipp_state,
            $order->get_shipping_city(),
            $order->get_shipping_postcode(),
            $shipp_country,
            $order->get_billing_phone(),
            $order->get_billing_email(),
            $order_id,
            wc_get_order_status_name($order_status),
            $price,
            $all_items,
            $all_items_qty,
            $count_items,
            $vendor_items,
            $order->get_transaction_id(),
            $payment_method,
            $shipping_method,
            nl2br( esc_html( $post->post_excerpt ) ),
        );

        return str_replace( array( '<br>' , '<br/>' , '<br />', '&nbsp;' ), array( '' , '' , '', ' ' ), str_replace( $find, $replace, $content ) );

    }
    public function notification_text_order_shortcode () {

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
}
