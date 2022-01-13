<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/10/17
 * Time: 2:10 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
class Introduce_To_Friends{

    public $page_url;
    protected static $_instance = null;

    public static function instance()
    {
        if(get_option('itf_set_refer_code',false)==false ){
            $allusers = get_users();
            foreach ($allusers as $user){
                $refer_code = substr(md5($user->user_login), 0, 7);
                update_user_meta($user->ID,'refer_code',$refer_code);
            }
            update_option('itf_set_refer_code',true);
        }
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
//        add_action('init',array($this , 'do_output_buffer'));

        //add_action( 'user_register', 'appchar_itf_registration_save', 10, 1 );

        add_action( 'woocommerce_order_status_completed', 'appchar_itf_add_credit_to_introducer');
        add_filter('lwsms_user_after_send_sms','add_referer_to_user',10,1);

        $this->include_files();
    }

    public function include_files(){
        require_once APPCHAR_DIR.'introduce_to_friends/functions.php';
    }
//    public function do_output_buffer() {
//        ob_start();
//    }

}