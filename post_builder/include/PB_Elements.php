<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/4/17
 * Time: 11:54 PM
 */
class PB_Elements
{
    protected static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        $this->includes_files();
        add_action( 'wp_ajax_pb_get_fields', array($this,'pb_get_fields'));

    }

    public function pb_get_fields(){

        $default_element = get_element_list();

        $element_obj =$_POST['element_obj'] ;
        $element_type = $element_obj['type'];
        $row_number = intval($_POST['row_number']);
        $html_field = $default_element[$element_type]->generate_html_field($row_number,$element_obj);

        echo $html_field;

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    private function includes_files(){
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Element_Interface.php';
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Element_Class.php';
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Text_Element.php';
        PB_Text_Element::instance();
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Video_Element.php';
        PB_Video_Element::instance();
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Slider_Element.php';
        PB_Slider_Element::instance();
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Button_Element.php';
        PB_Button_Element::instance();
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Grid3_Element.php';
        PB_Grid3_Element::instance();
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Grid2_Element.php';
        PB_Grid2_Element::instance();
        require_once APPCHAR_DIR . '/post_builder/include/elements/PB_Grid1_Element.php';
        PB_Grid1_Element::instance();

    }

    public function save_post_builder_items($post_id){
        // pointless if $_POST is empty (this happens on bulk edit
        if ( empty( $_POST ) )
            return $post_id;

        // verify quick edit nonce
        if ( isset( $_POST[ '_inline_edit' ] ) && ! wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) )
            return $post_id;

        // don't save for autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;



        $default_element = get_element_list();

        $indexes = $_POST['post_builder_index'];
        $pb_my_options = array();
        foreach ($indexes as $key=>$index){
            $type = $_POST['post_builder_type'][$key];
            $pb_my_options['elements'][] = $default_element[$type]->save_element($index);
        }
        update_post_meta($post_id , 'post_builder_elements' ,$pb_my_options);
    }

}