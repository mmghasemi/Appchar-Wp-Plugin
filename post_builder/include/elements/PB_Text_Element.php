<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/4/17
 * Time: 10:26 AM
 */
class PB_Text_Element extends PB_Element_Class implements PB_Element_Interface{

    public $type;
    public $dashicon;
    public $title;
    public $values;

    protected static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        $this->type = 'text';
        $this->dashicon = 'dashicons-welcome-write-blog';
        $this->title = __('text element','appchar');
        $this->values = '';

        add_filter('pb_element_list_array',array($this,'add_default_object'));
    }

    public function add_default_object($default_element)
    {
        $default_element['text'] = $this;
        return $default_element;
    }

    public function save_element($index)
    {
        $arr = array(
            'type'  => 'text',
            'text_area' => $_POST['text_'.$index.'_element'],
        );
        return $arr;
    }

    public function generate_html_field($row_num,$value)
    {
        $index_field = '<input type="hidden" name="post_builder_index[]" value="'.$row_num.'">';
        $type_field = '<input type="hidden" name="post_builder_type[]" value="text">';
        $settings = array( 'buttons' => 'strong,em,del,ul,ol,li,close','media_buttons' => false );
        $text_area = (isset($value['text_area']))?$value['text_area']:'';
//        $pb_editor = wp_editor($text_area,'text_'.$row_num.'_element',$settings);
        $pb_editor = '<textarea name="text_'.$row_num.'_element" id="text_'.$row_num.'_element">'.$text_area.'</textarea>';
        $html = $index_field.$type_field.$pb_editor;
        return $html;
    }
}