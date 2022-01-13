<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if(!class_exists('MF_List_Table'))
{
    class MF_List_Table extends WP_List_Table {
    
        var $example_data = array();
        var $columns = array();
    
        function __construct($data,$columns){
            global $status, $page;   
            $columns = $this->array_unshift_assoc($columns,'cb','<input type="checkbox" />'); 
    
            $this->columns = $columns;
            //Set parent defaults
            parent::__construct( array(
                'singular'  => 'cl_selected',     //singular name of the listed records
                'plural'    => 'movies',    //plural name of the listed records
                'ajax'      => false        //does this table support ajax?
            ) );
            $this->example_data = $data;
        }
        function array_unshift_assoc($arr, $key, $val) 
        { 
            return array($key=>$val) + $arr; 
        } 
        function column_default($item, $column_name){
            if( isset($this->columns[$column_name]) )
                return $item[$column_name];
            return print_r($item,true);
            switch($column_name){
                case 'id':
                case 'blog_id':
                case 'amount_commission':
                case 'inserted_date':
                case 'state':
                case 'action':
                    return $item[$column_name];
                default:
                    return print_r($item,true); //Show the whole array for troubleshooting purposes
            }
        }

        function column_title($item){

            //Build row actions
            $actions = array(
                'edit'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
                'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
            );
            //Return the title contents
            return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
                /*$1%s*/ $item['blog_id'],
                /*$2%s*/ $item['ID'],
                /*$3%s*/ $this->row_actions($actions)
            );
        }

        function column_cb($item){
            return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
                /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
            );
        }

        function get_columns(){
            return $this->columns;
        }

        function get_sortable_columns() {
            $sortable_columns = array(
               /* 'id'     => array('id',false),     //true means it's already sorted
                'blog_id'    => array('blog_id',false),
                'amount_commission'  => array('amount_commission',false),
                'inserted_date'    => array('inserted_date',false),
                'state'    => array('state',false)*/
            );
            foreach($this->columns as $key=>$value)
            {
                if( $key == 'cb' || $key == 'action')
                    continue;
                $sortable_columns[$key] = array($key,false);
            }

            return $sortable_columns;
        }

        function get_bulk_actions() {
            $actions = array(
                'delete'    => 'Delete'
            );
            return $actions;
        }

        function process_bulk_action() {

            //Detect when a bulk action is being triggered...
            if( 'delete'===$this->current_action() ) {
                wp_die('Items deleted (or they would be if we had items to delete)!');
            }

        }


        function prepare_items() {
            global $wpdb; //This is used only if making any database queries

            $per_page = 5;

            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->process_bulk_action();
            $data = $this->example_data;
            if( !function_exists('usort_reorder') )
            {
                function usort_reorder($a,$b){
                    $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
                    $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
                    $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
                    return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
                }
            }
            if( count($data) > 0 )
                usort($data, 'usort_reorder');

            $current_page = $this->get_pagenum();
            $total_items = count($data);
            if( $total_items > 0 )
                $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
            $this->items = $data;
            $this->set_pagination_args( array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
                'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
            ) );
        }
    
    
    }
}