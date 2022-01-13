<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/4/17
 * Time: 10:26 AM
 */
class PB_Slider_Element extends PB_Element_Class implements PB_Element_Interface{

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
        $this->type = 'slider';
        $this->dashicon = 'dashicons-images-alt2';
        $this->title = __('slider element','appchar');
        $this->values = '';

        add_filter('pb_element_list_array',array($this,'add_default_object'));
        add_action('add_script_to_postbuilder_meta_box',array($this,'add_slider_script_to_meta'));
    }

    public function add_default_object($default_element)
    {
        $default_element['slider'] = $this;
        return $default_element;
    }

    public function save_element($index)
    {
        $items = array();
        foreach ($_POST['slide_'.$index.'_image'] as $key=>$image){
            $items[]=array(
                'image'=> $image,
                'link_type'=>$_POST['slide_'.$index.'_link_type'][$key],
                'link'=>$_POST['slide_'.$index.'_link'][$key],
            );
        }
        $arr = array(
            'type'  => 'slider',
            'items' => $items,
        );
        return $arr;
    }
    public function generate_html_field($row_num,$value)
    {
        $default_array = array(
            array(
                'image'=> '',
                'link_type'=>'',
                'link'=>'',
            ),
        );
        $index_field = '<input type="hidden" name="post_builder_index[]" value="'.$row_num.'">';
        $type_field = '<input type="hidden" name="post_builder_type[]" value="slider">';
        $slides = (isset($value['items']))?$value['items']:$default_array;
        $slide_fields = '<div><table><thead><tr><th>image</th><th>نوع لینک</th><th>لینک</th></tr></thead><tbody>';
        foreach ($slides as $slide){
            $slide_fields .='<tr>';
            if($slide['image']!=''){
                $src_image = $slide['image'];
            }else{
                $src_image = APPCHAR_IMG_URL.'add_image.png';
            }
            $slide_fields .= '<td><input class="input-hidden-image" type="hidden" name="slide_'.$row_num.'_image[]" value="'.$slide['image'].'">';
            $slide_fields .= '<img class="add-image-img" onclick="open_library_window(this)" src="'.$src_image.'" width="80px" /></td>';
            $options = array(
                'categories_page'   => 'صفحه دسته بندی ها',
                'single_category'   => 'دسته بندی تکی',
                'product'           => 'آیدی محصول',
                'telegram'          => 'آیدی تلگرام',
                'instagram'         => 'آیدی اینستاگرام',
                'link'              => __('url address', 'appchar'),

            );
            $slide_fields .= '<td><select name="slide_'.$row_num.'_link_type[]"><option value="-1">هیچ کدام</option>';
            foreach ($options as $key=>$option){
                if($slide['link_type']==$key){
                    $slide_fields .= '<option value="'.$key.'" selected>'.$option.'</option>';
                }else{
                    $slide_fields .= '<option value="'.$key.'">'.$option.'</option>';
                }
            }
            $slide_fields .= '</select></td>';
            //slide_fields .= '<input name="slide_'.$row_num.'_link_type[]" value="'.$slide['link_type'].'">';
            $slide_fields .= '<td><input name="slide_'.$row_num.'_link[]" value="'.$slide['link'].'"></td>';
            $slide_fields .= '<tr>';
        }
        $slide_fields .= '</tbody></table><a onclick="add_slider_item(this)">اضافه کردن</a></div>';

        echo $index_field.$type_field.$slide_fields;
    }

    public function add_slider_script_to_meta()
    {
        echo 'function add_slider_item(_this){jQuery(_this).parent().children(\'table\').children(\'tbody\').children(\'tr:last\').after(\'<tr><td><input class="input-hidden-image" type="hidden" name="slide_1_image[]" value=""><img onclick="open_library_window(this)" src="'.APPCHAR_IMG_URL.'/add_image.png" width="80px" style="margin:20px"></td><td><select name="slide_1_link_type[]"><option value="-1">هیچ کدام</option><option value="categories_page">صفحه دسته بندی ها</option><option value="single_category">دسته بندی تکی</option><option value="product">آیدی محصول</option><option value="telegram">آیدی تلگرام</option><option value="instagram">آیدی اینستاگرام</option><option value="link">'.__('url address','appchar').'</option></select></td><td><input name="slide_1_link[]" value=""></td></tr>\');}';
    }
}