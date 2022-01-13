<?php

class appchar_homepage_config
{
    public $appchar_card;
    public function __construct(){
        $appchar_card = new appchar_Card();
    }
    public function get_row_type($selected=''){
        $main_type_args = array(
            'custom_attributes'=> 'onchange="appchar_set_row_options(this)"',
            'options'=> array(
                'none' => __('none', 'appchar'),
                'slider' => __('slider', 'appchar'),
                'button' => __('button', 'appchar'),
                'banner' => __('full width image', 'appchar'),
                'list' => __('list', 'appchar'),
                'grid' => __('grid view', 'appchar'),
                'filter' => __('filter','appchar'),
            ),
            'selected'  => $selected,
        );
        return $this->appchar_card->generate_select($main_type_args,'row-type[]');
    }
    public function get_taxonomy($selected=''){
        $taxonomy_args = array(
            'name' => '',
            'class' => 'category',
            'custom_attributes'=> 'style="display:none"',
            'selected' => $selected,
            'show_option_none'  => '',
        );
        return $this->appchar_card->generate_taxonomy_select($taxonomy_args,'');
    }
    public function get_link_type($selected=''){
        $link_options = array(
            'categories_page' => __('categories page', 'appchar'),
            'single_category' => __('single category', 'appchar'),
            'product' => __('product id', 'appchar'),
            'telegram' => __('telegram id', 'appchar'),
            'instagram' => __('instagram id', 'appchar'),
            'phone_number' => __('Phone number','appchar'),
        );
        $link_args = array(
            'name' => '',
            'class' => '',
            'options' => $link_options,
            'custom_attributes' => 'onchange="link_type_selector(this)"',
            'selected' => '',
        );
        return $this->appchar_card->generate_select($link_args,'_link_type');

    }
    public function get_link_block($link_type_select='',$taxonomy_selected='',$input_value=''){
        $link_type = $this->get_link_type($link_type_select);
        $input_args = array(
            'value' => $input_value,
            'custom_attributes' => 'placeholder="'.__("insert your text",'appchar_wp').'" style="display:none"'
        );
        return $this->get_link_type().$this->get_taxonomy($taxonomy_selected).$this->appchar_card->generate_input($input_args,'_link_input');
    }
    public function get_grid_type($selected=''){
        $grid_options = array(
            'name' => '',
            'class' => '',
            'options' => array(
                'full' => __('full width', 'appchar'),
                'half' => __('two grid in width', 'appchar'),
                'third' => __('three grid in width', 'appchar'),
            ),
            'custom_attributes'=> 'onchange="appchar_append_gridcard(this)"',
            'selected' => $selected,
            'show_option_none' => __('none')
        );
        return $this->appchar_card->generate_select($grid_options,'grid_type');
    }
    public function get_grid_card($name='grid',$image_url='',$tax_selected=-1){
        $arg = array(
            'name' => $name,
            'up' => array(
                'tag'   => 'text',
                'text' => __('select your image', 'appchar'),
            ),
            'image' => array(
                'image_can_remove' => false,
                'url' => $image_url,
            ),
            'down' => array(
                array(
                    'tag'   => 'taxonomy',
                    'class' => 'category',
                    'custom_attributes'=> '',
                    'selected' => $tax_selected,
                    'show_option_none' => __('None'),
                ),
            )
        );
        return $this->appchar_card->view_card($arg);

    }



    public function slider_set_values($row_index){
        $slider_values = array();
        foreach ($_POST['slider_'.$row_index.'_id'] as $key=>$unused){
            if($_POST['slider_'.$row_index.'_image'][$key]!='') {
                $slider_values[] = array(
                    'image' => $_POST['slider_' . $row_index . '_image'][$key],//slider_2_image[]
                    'type' => $_POST['slider_' . $row_index . '_type'][$key],//slider_2_type[]
                    'category_id' => $_POST['slider_' . $row_index . '_category_id'][$key],//slider_2_category_id[]
                    'link' => $_POST['slider_' . $row_index . '_link'][$key],//slider_2_link[]
                );
            }
        };
        $slider = array(
            'type'  => 'slider',
//        "is_enable"=> true,
//        "weight"=> 1,
            'items' => $slider_values,
        );
        return $slider;
    }
    public function button_set_values($row_index){
        $button = array(
            'type'          => 'button',
            'text'          => $_POST['button_'.$row_index.'_title'],
            'button_type'   => $_POST['button_'.$row_index.'_link_type'],
            'category_id'   => $_POST['button_'.$row_index.'_category_id'][0],
            'link'          => $_POST['button_'.$row_index.'__link_input'],
        );
        return $button;
    }
    public function banner_set_values($row_index){
        if($_POST['banner_'.$row_index.'_image'][0]==''){
            return null;
        }
        $banner = array(
            'type'          => 'banner',
            'image'         => $_POST['banner_'.$row_index.'_image'][0],
            'banner_type'   => $_POST['banner_'.$row_index.'_type'][0],
            'category_id'   => $_POST['banner_'.$row_index.'_category_id'][0],
            'link'          => $_POST['banner_'.$row_index.'_link'][0],
        );
        return $banner;
    }
    public function list_set_values($row_index){
        $list = array(
            'type'          => 'list',
            'list_type'   => $_POST['list_'.$row_index.'_list_type'],
            'category_id'   => $_POST['list_'.$row_index.'_category_id'][0],
        );
        return $list;
    }
    public function grid_set_values($row_index){
        $grid_items = array();
        if($_POST['grid_'.$row_index.'_type']==-1){
            return null;
        }
        foreach ($_POST['grid_'.$row_index.'_id'] as $key=>$unused){
            $img = $_POST['grid_'.$row_index.'_image'][$key];
            $cat_id= $_POST['grid_'.$row_index.'_category_id'][$key];
            if($img == '' || $cat_id=='-1'){
                return null;
            }
            $grid_items[] = array(
                'image'         => $img,
                'category_id'   => $cat_id,
            );
        }
        $grid = array(
            'type'          => 'grid',
            'grid_type'   => $_POST['grid_'.$row_index.'_type'],
            'items'   => $grid_items,
        );
        return $grid;
    }
    public function filter_set_values($row_index){
        if (!AppcharExtension::extensionIsActive('hierarchical_filter')) {
            return;
        }
        $list = array(
            'type' => 'filter',
            'filter_type' => 'hierarchical',
            'title' => (isset($_POST['filter_' . $row_index . '_title'])) ? trim($_POST['filter_' . $row_index . '_title']) : __('filter', 'appchar'),
        );
        return $list;
    }


    public function slider_get_values($row,$row_index){
        $link_options = array(
            'categories_page' => __('categories page', 'appchar'),
            'single_category' => __('single category', 'appchar'),
            'product' => __('product id', 'appchar'),
            'telegram' => __('telegram id', 'appchar'),
            'instagram' => __('instagram id', 'appchar'),
            'phone_number' => __('Phone number','appchar'),
        );
        $arg = array(
            'name' => 'slider_'.$row_index,
            'up' => array(
                'tag'   => 'text',
                'text'  => __('select your image', 'appchar_wp'),
            ),
            'image' => array(
                'image_can_remove' => false,
                'url' => '',
            ),
            'down' => array(
                array(
                    'tag'   => 'select',
                    'name'  => "_type[]",
                    'class' => "banner_type_select",
                    'options' => $link_options,
                    'custom_attributes'=>'onchange="link_type_selector(this)"',
                    'selected' => '',
                    'show_option_none'=> __('none'),
                ),
                array(
                    'tag'   => 'input',
                    'type' => "text",
                    'name' => "_link[]",
                    'class' => "txt_id",
                    'custom_attributes'=> 'placeholder="'.__('insert your id','appchar').'" style="display:none"',
                    'value' => '',

                ),
                array(
                    'tag'   => 'taxonomy',
                    'class' => 'category',
                    'custom_attributes'=> 'style="display:none"',
                    'selected' => '',
                    'show_option_none' => __('None'),
                ),
            )
        );
        $seted_slider_card = new appchar_Card($arg);
        foreach ($row['items'] as $option){
            $args = array(
                'image' => array(
                    'image_can_remove' => false,
                    'url' => $option['image'],
                ),
                'down' => array(
                    array(
                        'tag'=>'select',
                        'name' => "_type[]",
                        'class' => "banner_type_select",
                        'options' => $link_options,
                        'custom_attributes'=>'onchange="link_type_selector(this)"',
                        'selected' => $option['type'],
                        'show_option_none'=> __('none'),
                    ),
                    array(
                        'tag'=>'input',
                        'type' => "text",
                        'name' => "_link[]",
                        'class' => "txt_id",
                        'custom_attributes'=> 'placeholder="'.__('insert your id','appchar').'" style="display:none"',
                        'value' => $option['link'],

                    ),
                    array(
                        'tag'=>'taxonomy',
                        'class' => 'category',
                        'custom_attributes'=> 'style="display:none"',
                        'selected' => $option['category_id'],
                        'show_option_none' => __('None'),
                    ),
                )
            );
            echo $seted_slider_card->view_card($args);
        }
        echo $seted_slider_card->view_card();
    }
    public function button_get_values($row,$row_index){

        return $row_index['text'];
    }
    public function banner_get_values($row,$row_index){
        return $row;
    }
    public function list_get_values($row,$row_index){
        return $row;
    }
    public function grid_get_values($row,$row_index){
        return $row;
    }
    public function filter_get_values($row,$row_index){
        if (!AppcharExtension::extensionIsActive('hierarchical_filter')) {
            echo '<p>'.__('this feature is not active','appchar').'</p>';
            return;
        }
        global $appchar_card;
        $input_args = array(
            'custom_attributes' => 'placeholder="'.__("insert your title",'appchar').'"',
            'value'             => $row['title'],
        );
        $post_list_type = $appchar_card->generate_input($input_args,'filter_'.$row_index.'_title');
        echo $post_list_type;
    }




}
