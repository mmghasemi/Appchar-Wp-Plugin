<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/4/17
 * Time: 10:26 AM
 */
class PB_Button_Element extends PB_Element_Class implements PB_Element_Interface{

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
        $this->type = 'button';
        $this->dashicon = 'dashicons-editor-removeformatting';
        $this->title = __('button element','appchar');
        $this->values = '';

        add_filter('pb_element_list_array',array($this,'add_default_object'));
    }

    public function add_default_object($default_element)
    {
        $default_element['button'] = $this;
        return $default_element;
    }

    public function save_element($index)
    {
        $arr = array(
            'type'              => 'button',
            'text'              => $_POST['button_'.$index.'_text'],
            'link_type'         => $_POST['button_'.$index.'_link_type'],
            'link'              => $_POST['button_'.$index.'_link'],
            'background_color'  => 'default'
        );
        if ($arr['link_type'] == 'single_category'){
            $arr['category_id'] = $arr['link'];
        }
        return $arr;
    }
    public function generate_html_field($row_num,$value)
    {
        $index_field = '<input type="hidden" name="post_builder_index[]" value="'.$row_num.'">';
        $type_field = '<input type="hidden" name="post_builder_type[]" value="button">';
        $button_text = (isset($value['text']))?$value['text']:'';
        $button_link_type = (isset($value['link_type']))?$value['link_type']:'';
        $button_link = (isset($value['link']))?$value['link']:'';
        $button_fields = '<div><table><thead><tr><th>متن دکمه</th><th>نوع لینک</th><th>لینک</th></tr></thead><tbody><tr></tr>';
        $button_fields .= '<td><input name="button_'.$row_num.'_text" value="'.$button_text.'"></td>';
        $options = array(
            'categories_page'   => 'صفحه دسته بندی ها',
            'single_category'   => 'دسته بندی تکی',
            'product'           => 'آیدی محصول',
            'telegram'          => 'آیدی تلگرام',
            'instagram'         => 'آیدی اینستاگرام',
            'link'              => __('url address', 'appchar'),

        );
        $button_fields .= '<td><select name="button_'.$row_num.'_link_type"><option value="-1">هیچ کدام</option>';
        foreach ($options as $key=>$option){
            if($button_link_type==$key){
                $button_fields .= '<option value="'.$key.'" selected>'.$option.'</option>';
            }else{
                $button_fields .= '<option value="'.$key.'">'.$option.'</option>';
            }
        }
        $button_fields .= '</select></td>';
        $button_fields .= '<td><input name="button_'.$row_num.'_link" value="'.$button_link.'"></td>';
        $button_fields .= '</tr></tbody></table></div>';

        echo $index_field.$type_field.$button_fields;
    }
}