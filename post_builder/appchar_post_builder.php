<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/2/17
 * Time: 8:35 AM
 */
class Appchar_Post_Builder //APB
{
    public $pb_element;
    public $APB_VERSION = '1.0';

    protected static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        add_filter('add_meta_to_single_post_endpoint',array($this,'add_post_builder_to_single_post_endpoint'));
        add_filter('add_meta_to_post_endpoint',array($this,'add_post_builder_to_endpoint'));
        add_meta_box('appchar_post_builder', __('appchar post builder','appchar'), array($this ,'appchar_post_builder_callback'), 'post');
        add_action('save_post', array($this, 'save_post_builder_items'), 10, 1);
        $this->include_files();
    }


    public function add_post_builder_to_single_post_endpoint($get_post){
        $post_builder = get_post_meta($get_post->id,'post_builder_elements',true);
        if(isset($post_builder['elements'])) {
            $elements = array();
            foreach ($post_builder['elements'] as $element) {
                if ($element['type'] == 'grid1' || $element['type'] == 'grid2' || $element['type'] == 'grid3') {
                    $element['type'] = 'grid';
                }
                $elements[] = $element;
            }
            $post_builder_elements = array();
            $post_builder_elements['elements'] = $elements;
            $get_post->post_builder = $post_builder_elements;
        }
        return $get_post;
    }

    public function add_post_builder_to_endpoint($get_post){
        if(get_post_meta($get_post->id,'post_builder_elements',true)){
            $get_post->has_post_builder = true;
        }else{
            $get_post->has_post_builder = false;
        }
        return $get_post;
    }
    private function define_constants() {
        define('APPCHAR_PB_DIR'    , trailingslashit(APPCHAR_DIR . 'include'));
        define('APPCHAR_PB_DIR'    , trailingslashit(APPCHAR_INC_DIR . 'include'));
    }
    public function include_files(){
        require_once APPCHAR_DIR . '/post_builder/include/PB_Functions.php';
        require_once APPCHAR_DIR . '/post_builder/include/PB_Elements.php';
        $this->pb_element = PB_Elements::instance();
    }
    public function appchar_post_builder_callback( $post ){
        require_once APPCHAR_DIR . '/post_builder/template/meta_box_template.php';
    }
    public function save_post_builder_items($post_id){
        $post = get_post($post_id);
        if ($post->post_type == 'post') {
            $this->pb_element->save_post_builder_items($post_id);
        }
    }
    
}