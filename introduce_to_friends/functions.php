<?php
/**
 *
 * Get the page template link
 * @param string $pages : login or register
 * @param array $params : array of query var
 * @return $link
 * @author alishojaei
 * @version 1.0
 * @copyright appchar.com team
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function itf_increase_credit_to_both_user($referrer,$introducing,$order_id){
    $itf_setting = get_option('introduce_to_friends_setting',array());
    $allow_add = get_user_meta($introducing,'itf_allow_to_add_credit',true);
    $point = (isset($itf_setting['referrer_point'])) ? $itf_setting['referrer_point'] : 0;
    if($point!=0 && ($itf_setting['referrer_count']=='always' || $allow_add)) {

        if(!isset($itf_setting['referrer_amount_type']) || $itf_setting['referrer_amount_type']=='constant') {
            $referrer_amount = $point;
        }else{
            $referrer_amount = get_point($order_id,$point);
        }
        change_balance($referrer_amount, $referrer, 'امتیاز به معرفی کننده', $introducing, $order_id, 'add');
    }
    $point = (isset($itf_setting['referred_user_point'])) ? $itf_setting['referred_user_point'] : 0;
    if($point!=0 && ($itf_setting['referred_user_count']=='always' || $allow_add)) {
        if(!isset($itf_setting['referred_user_amount_type']) || $itf_setting['referred_user_amount_type']=='constant') {
            $introducing_amount = $point;
        }else{
            $introducing_amount = get_point($order_id,$point);
        }
        change_balance($introducing_amount, $introducing, 'امتیاز به معرفی شونده', $referrer, $order_id, 'add');
    }

}

function get_point($order_id="",$percentage=0){
    if($order_id=="")
        return;
    $order = wc_get_order($order_id);
    return $order->get_total()*$percentage/100;
}

function appchar_itf_add_credit_to_introducer($order_id){
    $user_id = get_post_meta($order_id, '_customer_user', true);
    $referrer = get_user_meta($user_id,'itf_referrer_user',true);
    if(!$referrer) {
        return;
    }
    $referrer_user = get_users(
        array(
            'meta_query' => array(
                array(
                    'key' => 'refer_code',
                    'value' => $referrer,
                    'compare' => '=='
                ),
            )
        )
    );
    if(!$referrer_user){
        return;
    }
    $referrer_user_id = $referrer_user[0]->ID;

    itf_increase_credit_to_both_user($referrer_user_id,$user_id,$order_id);
    update_user_meta($user_id,'itf_allow_to_add_credit',false);
}
function appchar_itf_registration_save( $user_id,$refer_code ) {
    add_user_meta($user_id, 'itf_referrer_user', $refer_code, true);
    add_user_meta($user_id,'itf_allow_to_add_credit',true,true);
}
function add_referer_to_user($user){
    $referer_id = get_user_meta($user->ID,'itf_referrer_user',true);
    if($referer_id){
        $user->referer = $referer_id;
    }else{
        $user->referer = '';
    }
    return $user;
}