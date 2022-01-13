<?php

class appchar_Card
{
    private $options;
    public function __construct($args=array())
    {
        $default_card_args=array(
            'name'      => 'default',
            'card_can_remove'  => false,
            'id'        => -1,
            'up'        => array(),
            'image'     => array(
                'image_can_remove'    => false,
                'url'     => '',
            ),
            'down'      => array(),
        );
        $args = wp_parse_args( $args, $default_card_args );
        $this->options = $args;
    }
    public function view_card($args = array()){
        $args = wp_parse_args( $args, $this->options );
        $string  = '<div class="card2 ui-sortable-handle">';
        if($args['card_can_remove']){
            $string .= '<a onclick="appchar_discard_slide(this)" class="discard_slide"><img src="'.APPCHAR_IMG_URL.'home_config/close.png"></a>';
        }
        $string .= '<input type="hidden" class="hidden" name="'.$args['name'].'_id[]" value="'.$args['id'].'">';
        if($args['up'])
            $string .= $this->view_up($args['up'],$args['name']);
        $string .= $this->view_image($args['image'],$args['name']);
        if($args['down'])
            $string .= $this->view_down($args['down'],$args['name']);
        $string .= '</div>';
        return $string;
    }
    public function view_image($image,$name){
        if($image['url']==''){
            $display = 'style="display: none;"';
            $nodisplay = '';
        }else{
            $nodisplay = 'style="display: none;"';
            $display = '';
        }
        $image_tags = '<div class="banners-img">';
        if($image['image_can_remove']){
            $image_tags .= '<a '.$display.' class="clean_image"><img src="'.APPCHAR_IMG_URL.'close.png"></a>';
        }
        $image_tags .= '<a id="slide_upload" onclick="appchar_upload_win(this)" class="slide_upload um-cover-add um-manual-trigger atag"
                style="width:100%; height: 370px;" data-parent=".um-cover" data-child=".um-btn-auto-width">
                <img class="thumbnail2 imgtag" '.$display.' src="'.$image['url'].'">
                <span class="dashicons dashicons-plus-alt banner-icon" '.$nodisplay.'></span></a>';
        $image_tags .='<input type="text" class="slide" name="'.$name.'_image[]" style="display: none;" id="slide" value="'.$image['url'].'" readonly/>';
        $image_tags .= '</div>';
        return $image_tags;
    }
    public function view_down($down_elements,$name){
        $down  = '<div class="container center down">';
        $down .= $this->replace_shortcodes($down_elements,$name);
        $down .= '</div>';
        return $down;
    }
    public function view_up($up_elements,$name){
        $up  = '<div class="container center up"><p>';
        $up .= $this->replace_shortcodes($up_elements,$name);
        $up .= '</p></div>';
        return $up;
    }
    public function replace_shortcodes($args=array(),$name){
        $str = '';
        if (is_array($args) || is_object($args)) {
            foreach ($args as $arg) {
                if(isset($arg['tag'])) {
                    switch ($arg['tag']) {
                        case 'taxonomy':
                            $str .= $this->generate_taxonomy_select($arg, $name);
                            break;
                        case 'input':
                            $str .= $this->generate_input($arg, $name);
                            break;
                        case 'select':
                            $str .= $this->generate_select($arg, $name);
                            break;
                        case 'text':
                            $str .= $arg['text'];
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return $str;

    }
    public function generate_taxonomy_select($args,$name){
        $default_args          = array(
            'name'              => $name,
            'type'              => "product_cat",
            'class'             => '',
            'custom_attributes'=> '',
            'selected'          => -1,
            'show_option_none'  =>  __('None'),
        );
        $args = wp_parse_args( $args, $default_args );
//        $arg=array('taxonomy'=>$args['type']);
//        $categories = get_categories($arg);
        $categories = get_terms( $args['type'], array( 'hide_empty' => false ) );
        $select = '<select name="'.$args['name'].'_category_id[]'.'" class="'.$args['class'].'" '.$args['custom_attributes'].'>';
        if($args['show_option_none']!=''){
            $select .= '<option value="-1" selected>'.$args['show_option_none'].'</option>';
        }
        foreach ($categories as $category){
            if(is_array($args['selected'])) {
                if (in_array($category->term_id,$args['selected'])) {
                    $select .= '<option value="' . $category->term_id . '" selected>' . $category->name . '</option>';
                } else {
                    $select .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                }
            }else{
                if ($args['selected'] == $category->term_id) {
                    $select .= '<option value="' . $category->term_id . '" selected>' . $category->name . '</option>';
                } else {
                    $select .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                }
            }
        }
        $select .= '</select>';
        return $select;
    }
    public function generate_has_child_taxonomy_select($args, $name)
    {
        $taxonomy = 'product_cat';
        $orderby = 'name';
        $show_count = 0;      // 1 for yes, 0 for no
        $pad_counts = 0;      // 1 for yes, 0 for no
        $hierarchical = 1;      // 1 for yes, 0 for no
        $title = '';
        $empty = 1;
        $categories = [];
        $args1 = array(
            'taxonomy' => $taxonomy,
            'orderby' => $orderby,
            'show_count' => $show_count,
            'pad_counts' => $pad_counts,
            'hierarchical' => $hierarchical,
            'hide_empty' => $empty
        );
        $all_categories = get_categories($args1);
        foreach ($all_categories as $cat) {
            $category_id = $cat->term_id;

            $args2 = array(
                'taxonomy' => $taxonomy,
                'parent' => $category_id,
                'orderby' => $orderby,
                'show_count' => $show_count,
                'pad_counts' => $pad_counts,
                'hierarchical' => $hierarchical,
                'hide_empty' => $empty
            );
            $sub_cats = get_categories($args2);
            if ($sub_cats) {
                $categories[] = $cat;
            }
        }
        $select = '<select name="' . $args['name'] . '_category_id[]' . '" class="' . $args['class'] . '" ' . $args['custom_attributes'] . '>';
        if ($args['show_option_none'] != '') {
            if (is_array($args['selected'])) {
                if (in_array(0, $args['selected'])) {
                    $select .= '<option value="0" selected>' . $args['show_option_none'] . '</option>';
                } else {
                    $select .= '<option value="0">' . $args['show_option_none'] . '</option>';
                }
            } else {
                if ($args['selected'] == 0) {
                    $select .= '<option value="0" selected>' . $args['show_option_none'] . '</option>';
                } else {
                    $select .= '<option value="0">' . $args['show_option_none'] . '</option>';
                }
            }
        }
        foreach ($categories as $category) {
            if (is_array($args['selected'])) {
                if (in_array($category->term_id, $args['selected'])) {
                    $select .= '<option value="' . $category->term_id . '" selected>' . $category->name . '</option>';
                } else {
                    $select .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                }
            } else {
                if ($args['selected'] == $category->term_id) {
                    $select .= '<option value="' . $category->term_id . '" selected>' . $category->name . '</option>';
                } else {
                    $select .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                }
            }
        }
        $select .= '</select>';
        return $select;
    }
    public function generate_input($args,$name){
        $default_args = array(
            'type'              => "text",
            'name'              => "",
            'value'             => "",
            'class'             => "txt_id",
            'custom_attributes' => "",
        );
        $args = wp_parse_args( $args, $default_args );
        $input = '<input type="'.$args['type'].'" name="'.$name.$args['name'].'" value="'.$args['value'].'" class="'.$args['class'].'" '.$args['custom_attributes'].'>';
        return $input;
    }
    public function generate_select($args,$name){
        $default_args = array(
            'name'      => "",
            'class'     => "",
            'style'     => "",
            'custom_attributes'=> '',
            'options'     => array(
            ),
            'selected'  => "",
            'show_option_none' => ''

        );
        $args = wp_parse_args( $args, $default_args );
        $select  = '<select name="'.$name.$args['name'].'" '.$args['custom_attributes'].' class="'.$args['class'].'">';
        if($args['show_option_none']!=''){
            $select .= '<option value="-1">'.$args['show_option_none'].'</option>';
        }
        foreach ($args['options'] as $key=>$values){
            if($key == $args['selected']){
                $select .= '<option value="'.$key.'" selected>'.$values.'</option>';
            }else{
                $select .= '<option value="'.$key.'">'.$values.'</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }

    public function validate_image(){

    }
    public function validate_post(){
        $this->validate_image();
    }
}
