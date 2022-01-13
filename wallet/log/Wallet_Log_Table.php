<?php
/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/8/18
 * Time: 10:23 AM
 */

class Wallet_Log_Table extends WP_List_Table {

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct() {
        parent::__construct( array(
            'singular'=> 'Wallet Log', //Singular label
            'plural' => 'Wallet Logs', //plural label, also this well be one of the table css class
            'ajax'   => false //We won't support Ajax for this table
        ) );
    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        _e( 'No wallet log avaliable.', 'sp' );
    }


    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            //The code that goes before the table is here
            echo '<div class="alignleft actions"><form method="get">';
            foreach ($_GET as $key=>$get){
                if($key!='username')
                    echo '<input type="hidden" name="'.$key.'" value="'.$get.'">';
            }
            $value = (isset($_GET['username']))?$_GET['username']:'';
            echo '<input type="text" placeholder="username" name="username" value="'.$value.'">
                <input type="submit" class="button" value="ارسال">
            </form></div>';
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there
//            echo"Hi, I'm after the table";
        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        return $columns= array(
            'col_wallet_log_id'=>__('آیدی'),
            'col_wallet_log_price'=>__('قیمت'),
            'col_wallet_log_user'=>__('کاربر'),
            'col_wallet_log_transaction_type'=>__('نوع تراکنش'),
            'col_wallet_log_how'=>__('چطور'),
            'col_wallet_log_by_who'=>__('توسط'),
            'col_wallet_log_order_id'=>__('شماره سفارش'),
            'col_wallet_log_old_credit'=>__('اعتبار پیشین'),
            'col_wallet_log_new_credit'=>__('اعتبار جدید'),
            'col_wallet_log_created_at'=>__('زمان تغییر اعتبار'),
        );
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
        return $sortable = array(
            'col_wallet_log_id'=>array( 'id', true ),
            'col_wallet_log_user'=>array( 'user_id', true ),
            'col_wallet_log_transaction_type'=>array( 'transaction_type', true ),
        );
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count($user_id = '') {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}appchar_user_wallet_log";
        if($user_id != ''){
            $sql .= ' WHERE user_id='.$user_id;
        }
        return $wpdb->get_var( $sql );
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
//        $this->_column_headers = $this->get_column_info();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $args = array();
        $user_id = '';
        if(!empty($_GET["username"])){
            $user = get_user_by('login' , $_GET['username']);
            if($user){
                $user_id = $user->ID;
                $args['where'] = array(
                    'key'=>'user_id',
                    'operator'=>'=',
                    'value'=> $user_id,
                );
            }
        }

        $per_page     = $this->get_items_per_page( 'customers_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items = self::record_count($user_id);

        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );

        $args['order_by'] = !empty($_GET["orderby"]) ? $_GET["orderby"]: 'id';
        $args['sort'] = !empty($_GET["order"]) ? $_GET["order"] : 'DESC';
        $args['limit']= $per_page;
        $args['current_page'] = $current_page;
        $this->items = wallet_log::get($args);
    }

    /**
     * Display the rows of records in the table
     * @return string, echo the markup of the rows
     */
    function display_rows() {
        //Get the records registered in the prepare_items method
        $records = $this->items;
        //Get the columns registered in the get_columns and get_sortable_columns methods
//        list( $columns, $hidden ) = $this->get_column_info();
        $columns = $this->get_columns();
        //Loop for each record
        if(!empty($records)){
            foreach($records as $rec){
            //Open the line
            echo '<tr id="record_'.$rec->id.'">';
            foreach ( $columns as $column_name => $column_display_name ) {
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
//                if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                $attributes = $class . $style;

                //edit link
//                $editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->link_id;

                //Display the cell
                switch ( $column_name ) {
                    case "col_wallet_log_id":  echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';   break;
                    case "col_wallet_log_price":  echo '<td '.$attributes.'>'.stripslashes($rec->price).'</td>';   break;
                    case "col_wallet_log_user":  echo '<td '.$attributes.'><a href="'.get_edit_user_link( $rec->user()->ID ).'">'.stripslashes($rec->user()->user_login).'</a></td>';   break;
                    case "col_wallet_log_transaction_type":  echo '<td '.$attributes.'>'.stripslashes($rec->transaction_type).'</td>';   break;
                    case "col_wallet_log_how":  echo '<td '.$attributes.'>'.stripslashes($rec->how).'</td>';   break;
                    case "col_wallet_log_by_who":  echo '<td '.$attributes.'><a href="'.get_edit_user_link( $rec->oprator()->ID ).'">'.stripslashes($rec->oprator()->user_login).'</a></td>';   break;
                    case "col_wallet_log_order_id": echo '<td '.$attributes.'><a href="'.get_edit_post_link( $rec->order_id ).'">'.stripslashes($rec->order_id).'</a></td>'; break;
                    case "col_wallet_log_old_credit": echo '<td '.$attributes.'>'.stripslashes($rec->old_credit).'</td>'; break;
                    case "col_wallet_log_new_credit": echo '<td '.$attributes.'>'.stripslashes($rec->new_credit).'</td>'; break;
                    case "col_wallet_log_created_at": echo '<td '.$attributes.'>'.stripslashes($rec->created_at).'</td>'; break;
                }
            }

            //Close the line
            echo'</tr>';
        }}
    }


}
