<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/4/17
 * Time: 10:26 AM
 */
class PB_Grid2_Element extends PB_Element_Class implements PB_Element_Interface{

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
        $this->type = 'grid2';
        $this->dashicon = 'dashicons-format-gallery';
        $this->title = __('two part grid element','appchar');
        $this->values = '';

        add_filter('pb_element_list_array',array($this,'add_default_object'));
    }

    public function add_default_object($default_element)
    {
        $default_element['grid2'] = $this;
        return $default_element;
    }

    public function save_element($index)
    {
        $items = array();
        foreach ($_POST['grid2_'.$index.'_image'] as $key=>$image){
            $items[]=array(
                'image'=> $image,
                'link_type'=>$_POST['grid2_'.$index.'_link_type'][$key],
                'link'=>$_POST['grid2_'.$index.'_link'][$key],
            );
        }
        $arr = array(
            'type'  => 'grid2',
            'grid_column_type'  => 'half',
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
            array(
                'image'=> '',
                'link_type'=>'',
                'link'=>'',
            ),
        );
        $index_field = '<input type="hidden" name="post_builder_index[]" value="'.$row_num.'">';
        $type_field = '<input type="hidden" name="post_builder_type[]" value="grid2">';
        $grids = (isset($value['items']))?$value['items']:$default_array;
        $grid_fields = '<div><table><thead><tr><th>image</th><th>نوع لینک</th><th>لینک</th></tr></thead><tbody>';
        foreach ($grids as $grid){
            $grid_fields .='<tr>';

            if($grid['image']!=''){
                $src_image = $grid['image'];
            }else{
                $src_image = APPCHAR_IMG_URL.'add_image.png';
            }
            $grid_fields .= '<td><input class="input-hidden-image" type="hidden" name="grid2_'.$row_num.'_image[]" value="'.$grid['image'].'">';
            $grid_fields .= '<img class="add-image-img" onclick="open_library_window(this)" src="'.$src_image.'" width="80px" style="margin:20px" /></td>';
            $options = array(
                'categories_page'   => 'صفحه دسته بندی ها',
                'single_category'   => 'دسته بندی تکی',
                'product'           => 'آیدی محصول',
                'telegram'          => 'آیدی تلگرام',
                'instagram'         => 'آیدی اینستاگرام',
                'link'              => __('url address', 'appchar'),
            );
            $grid_fields .= '<td><select name="grid2_'.$row_num.'_link_type[]"><option value="-1">هیچ کدام</option>';
            foreach ($options as $key=>$option){
                if($grid['link_type']==$key){
                    $grid_fields .= '<option value="'.$key.'" selected>'.$option.'</option>';
                }else{
                    $grid_fields .= '<option value="'.$key.'">'.$option.'</option>';
                }
            }
            $grid_fields .= '</select></td>';
            $grid_fields .= '<td><input name="grid2_'.$row_num.'_link[]" value="'.$grid['link'].'"></td>';
            $grid_fields .= '<tr>';
        }
        $grid_fields .= '</tbody></table></div>';

        echo $index_field.$type_field.$grid_fields;
    }
}