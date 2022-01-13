<?php
/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/5/18
 * Time: 2:10 PM
 */


//global $woocommerce;
//$order = wc_create_order();
//$order->update_status("Completed", 'Imported order', TRUE);

class wallet_log
{
    private static $table_name = "appchar_user_wallet_log";

    public $id;
    public $price;
    public $user_id;
    public $transaction_type;
    public $how;
    public $by_who;
    public $order_id;
    public $old_credit;
    public $new_credit;
    public $created_at;

    public function user()
    {
        return get_user_by('ID', $this->user_id);
    }

    public function oprator()
    {
        return get_user_by('ID', $this->by_who);
    }

    public function order()
    {
        return wc_get_order($this->order_id);
    }

    public function save()
    {
        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . self::$table_name,
            array(
                'id' => $this->id,
                'price' => $this->price,
                'user_id' => $this->user_id,
                'transaction_type' => $this->transaction_type,
                'how' => $this->how,
                'by_who' => $this->by_who,
                'order_id' => $this->order_id,
                'old_credit' => $this->old_credit,
                'new_credit' => $this->new_credit,
                'created_at' => current_time('mysql'),
            )
        );
        if ($result) {
            $this->created_at = current_time('mysql');
            return $this;
        }
        return false;
    }

    public static function get($args = array())
    {

        $default_args = array(
            'where' => array(),//array('key'=>'','operator'=>'=','value'=>''),
            'order_by' => '',
            'sort' => 'DESC',
            'limit' => '20',
            'current_page' => 1,
        );

        $args = wp_parse_args($args, $default_args);

        $fields = isset($args['fields'])?$args['fields']:array();

        global $wpdb;
        $q = "SELECT * FROM  " . $wpdb->prefix . self::$table_name;

        if (count($args['where'])) {
            $args['where']['operator'] = (isset($args['where']['operator'])) ? $args['where']['operator'] : "=";
            $q .= " WHERE " . $args['where']['key'] . ' ' . $args['where']['operator'] . ' ' . $args['where']['value'];
        }
        if ($args['order_by'] != '') {
            $q .= " ORDER BY " . $args['order_by'] . ' ' . $args['sort'];
        }else{
            $q .= " ORDER BY id " . $args['sort'];
        }
        if ($args['limit'] != '') {
            $q .= " LIMIT " . $args['limit'];
        }
        if($args['current_page'] != 1){
            $q .= " OFFSET " .($args['current_page']-1)*$args['limit'];
        }
        $results = $wpdb->get_results($q);
        $wallet_logs = array();
        foreach ($results as $result) {
            $wallet_logs[] = self::convert_object($result,$fields);
        }
        return $wallet_logs;
    }

    private static function convert_object($result_query,$fields=array())
    {
        $wallet_log = new wallet_log();
        foreach ($wallet_log as $key=> $value){
            if(empty($fields) || in_array($key,$fields)){
                $wallet_log->$key = $result_query->$key;
            }else{
                unset($wallet_log->$key);
            }
        }
        return $wallet_log;

    }
}

function change_balance($price, $user_id, $how, $by_who, $order_id, $trancaction_type = 'subtract')
{

    //TODO validate user_id , order_id , by_how
    //TODO check balance with price

    $old_credit = get_user_meta($user_id, '_uw_balance', true);

    switch ($trancaction_type){
        case 'add':
            $new_credit = $old_credit + $price;
            break;
        case 'update':
            $new_credit = $price;
            break;
        default:
            $new_credit = $old_credit - $price;
    }

    $wallet_log = new wallet_log();
    $wallet_log->price = $price;
    $wallet_log->user_id = $user_id;
    $wallet_log->transaction_type = $trancaction_type;
    $wallet_log->how = $how;
    $wallet_log->by_who = $by_who;
    $wallet_log->order_id = $order_id;
    $wallet_log->new_credit = $new_credit;
    $wallet_log->old_credit = $old_credit;
    $wallet_log->save();

    update_user_meta($user_id,'_uw_balance',$new_credit);

}


?>