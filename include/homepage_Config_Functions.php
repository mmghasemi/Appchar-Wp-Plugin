<?php
/**
 * Created by PhpStorm.
 * User: parvazco
 * Date: 2/22/2017
 * Time: 3:15 AM
 */
function slider_get_values($row,$row_index){
    $rotate = (isset($row['rotate']) && $row['rotate'])?'checked':'';
    echo '<div class="slider-type" style="float: right;"><label for="slider_'.$row_index.'_rotate">قابلیت چرخش</label><input type="checkbox" name="slider_'.$row_index.'_rotate" value="1" '.$rotate.'><br>';
    $rotate_time = (isset($row['rotate_time']))?$row['rotate_time']:'';
    echo '<label for="slider_'.$row_index.'_rotate_time">زمان مکث چرخش</label><input name="slider_'.$row_index.'_rotate_time" value="'.$rotate_time.'" style="width:50px;">ms</div>';
    $link_options = array(
        'categories_page' => __('categories page', 'appchar'),
        'single_category' => __('single category', 'appchar'),
        'product' => __('product id', 'appchar'),
        'telegram' => __('telegram id', 'appchar'),
        'instagram' => __('instagram id', 'appchar'),
        'link' => __('url address', 'appchar'),
        'phone_number' => __('Phone number','appchar'),
    );
    if (AppcharExtension::extensionIsActive('blog')) {
        $link_options['post']=__('post id', 'appchar');
        $link_options['blog_categories_page'] = __('blog categories page','appchar');
        $link_options['blog_single_category'] = __('blog category page','appchar');
    }
    if (AppcharExtension::extensionIsActive('lottery')) {
        $link_options['lottery']=__('lottery id', 'appchar');
    }
    $arg = array(
        'name' => 'slider_'.$row_index,
        'up' => array(
            'tag'=>'text',
            'text' => __('select your image', 'appchar_wp'),
        ),
        'image' => array(
            'image_can_remove' => false,
            'url' => '',
        ),
        'down' => array(
            array(
                'tag'=>'select',
                'name' => "_type[]",
                'class' => "banner_type_select",
                'options' => $link_options,
                'custom_attributes'=>'onchange="link_type_selector(this)"',
                'selected' => '',
                'show_option_none'=> __('none'),
            ),
            array(
                'tag'=>'input',
                'type' => "text",
                'name' => "_link[]",
                'class' => "txt_id",
                'custom_attributes'=> 'placeholder="'.__('insert your id','appchar').'" style="display:none"',
                'value' => '',

            ),
            array(
                'tag'=>'taxonomy',
                'class' => 'category',
                'custom_attributes'=> 'style="display:none"',
                'selected' => '',
                'show_option_none' => __('None'),
            ),
        )
    );
    $seted_slider_card = new appchar_Card($arg);
    foreach ($row['items'] as $option){
        $display_input = '';
        $display_tax   = '';
        if($option['link_type']=='single_category'){
            $display_input = 'style="display:none"';
        }elseif($option['link_type']==-1 || $option['link_type']=='categories_page' || $option['link_type']=='blog_categories_page'){
            $display_input = 'style="display:none"';
            $display_tax   = 'style="display:none"';
        }else{
            $display_tax   = 'style="display:none"';
        }
        $args = array(
            'card_can_remove'  => true,
            'image' => array(
                'image_can_remove' => false,
                'url' => $option['image'],
            ),
            'down' => array(
                array(
                    'tag'   => 'select',
                    'name' => "_type[]",
                    'class' => "banner_type_select",
                    'options' => $link_options,
                    'custom_attributes'=>'onchange="link_type_selector(this)"',
                    'selected' => $option['link_type'],
                    'show_option_none'=> __('none'),
                ),
                array(
                    'tag'   => 'input',
                    'type'  => "text",
                    'name'  => "_link[]",
                    'class' => "txt_id",
                    'custom_attributes'=> 'placeholder="'.__('insert your id','appchar').'"'.$display_input,
                    'value' => $option['link'],

                ),
                array(
                    'tag'   => 'taxonomy',
                    'class' => 'category',
                    'custom_attributes'=> $display_tax,
                    'selected' => $option['category_id'],
                    'show_option_none' => __('None'),
                ),
            )
        );
        echo $seted_slider_card->view_card($args);
    }
    echo $seted_slider_card->view_card();
}
function slider_set_values($row_index){
    $slider_values = array();
    foreach ($_POST['slider_'.$row_index.'_id'] as $key=>$unused){
        if($_POST['slider_'.$row_index.'_image'][$key]!='') {
            if($_POST['slider_' . $row_index . '_type'][$key]=='product'){
                $pdt = wc_get_product($_POST['slider_' . $row_index . '_link'][$key]);
                if(!$pdt){
                    continue;
                }
            }elseif ($_POST['slider_' . $row_index . '_type'][$key]=='single_category') {
                if($_POST['slider_' . $row_index . '_category_id'][$key]=='-1'){
                    continue;
                }
            }elseif($_POST['slider_' . $row_index . '_type'][$key]=='post'){
                if(!AppcharExtension::extensionIsActive('blog')){
                    continue;
                }
                $pdt = get_post($_POST['slider_' . $row_index . '_link'][$key]);
                if(!$pdt){
                    continue;
                }
            }elseif($_POST['slider_' . $row_index . '_type'][$key]=='blog_single_category'){
                if(!AppcharExtension::extensionIsActive('blog')){
                    continue;
                }
                $link = (int) $_POST['slider_' . $row_index . '_link'][$key];
                $category = get_term( $link, 'category' );

                if(is_wp_error( $category )){
                    continue;
                }
                if(!$category){
                    continue;
                }
            }
            $slider_values[] = array(
                'image'         => $_POST['slider_' . $row_index . '_image'][$key],
                'link_type'     => $_POST['slider_' . $row_index . '_type'][$key],
                'category_id'   => $_POST['slider_' . $row_index . '_category_id'][$key],
                'link'          => $_POST['slider_' . $row_index . '_link'][$key],
            );
        }
    };
    $rotate_time = intval($_POST['slider_'.$row_index.'_rotate_time']);
    $rotate_time = ($rotate_time>500)?$rotate_time:500;
    if($slider_values) {
        $slider = array(
            'type' => 'slider',
//        "is_enable"=> true,
//        "weight"=> 1,
            'items' => $slider_values,
            'rotate'        => (isset($_POST['slider_'.$row_index.'_rotate']))?true:false,
            'rotate_time'   => $rotate_time
        );
    }else{
        return null;
    }
    return $slider;
}

function button_get_values($row,$row_index){
    global $appchar_card;
    $display_input = '';
    $display_tax   = '';
    if($row['link_type']=='single_category'){
        $display_input = 'style="display:none"';
    }elseif($row['link_type']==-1 || $row['link_type']=='categories_page' || $row['link_type']=='search' || $row['link_type']=='blog_categories_page'){
        $display_input = 'style="display:none"';
        $display_tax   = 'style="display:none"';
    }else{
        $display_tax   = 'style="display:none"';
    }
    $taxonomy_args = array(
        'name' => $row['type'].'_'.$row_index,
        'class' => 'category',
        'custom_attributes'=> $display_tax,
        'selected' => $row['category_id'],
        'show_option_none'  => '',
    );
    $taxonomy = $appchar_card->generate_taxonomy_select($taxonomy_args,'');
    $link_options = array(
        'categories_page' => __('categories page', 'appchar'),
        'search'  => __('search','appchar'),
        'single_category' => __('single category', 'appchar'),
        'product' => __('product id', 'appchar'),
        'telegram' => __('telegram id', 'appchar'),
        'instagram' => __('instagram id', 'appchar'),
        'link' => __('url address', 'appchar'),
        'phone_number' => __('Phone number','appchar'),
    );
    if (AppcharExtension::extensionIsActive('blog')) {
        $link_options['post']=__('post id', 'appchar');
        $link_options['blog_categories_page'] = __('blog categories page','appchar');
        $link_options['blog_single_category'] = __('blog category page','appchar');
    }
    if (AppcharExtension::extensionIsActive('lottery')) {
        $link_options['lottery']=__('lottery id', 'appchar');
    }
    $link_args = array(
        'name' => '_link_type',
        'class' => '',
        'options' => $link_options,
        'custom_attributes' => 'onchange="link_type_selector(this)"',
        'selected' => $row['link_type'],
    );

    $link_type = $appchar_card->generate_select($link_args,$row['type'].'_'.$row_index);
    $input_args = array(
        'value' => $row['link'],
        'custom_attributes' => 'placeholder="'.__("insert your text",'appchar_wp').'"'.$display_input,
    );
    $link_block = '<input name="'.$row['type'].'_'.$row_index.'_title" value="'.$row['text'].'" >'.$link_type.$taxonomy.$appchar_card->generate_input($input_args,$row['type'].'_'.$row_index.'_link_input');
    echo $link_block;
}
function button_set_values($row_index)
{
    if ($_POST['button_' . $row_index . '_link_type'] == 'product') {
        $pdt = wc_get_product($_POST['button_' . $row_index . '_link_input']);
        if (!$pdt) {
            return;
        }
    }elseif($_POST['button_' . $row_index . '_link_type'] == 'post'){
        if(!AppcharExtension::extensionIsActive('blog')){
            return;
        }
        $pdt = get_post($_POST['button_' . $row_index . '_link_input']);
        if (!$pdt) {
            return;
        }
    }elseif($_POST['button_' . $row_index . '_link_type']=='blog_single_category'){
        if(!AppcharExtension::extensionIsActive('blog')){
            return;
        }
        $link = (int) $_POST['button_' . $row_index . '_link_input'];
        $category = get_term( $link, 'category' );
        if(is_wp_error( $category )){
            return;
        }
        if(!$category){
            return;
        }
    }elseif($_POST['button_'.$row_index.'_title']==''){
        return;
    }
    $button = array(
        'type'          => 'button',
        'text'          => $_POST['button_'.$row_index.'_title'],
        'link_type'   => $_POST['button_'.$row_index.'_link_type'],
        'category_id'   => $_POST['button_'.$row_index.'_category_id'][0],
        'link'          => $_POST['button_'.$row_index.'_link_input'],
        'background_color' => 'default',//'#5da423'
    );

    return $button;
}

function banner_get_values($row,$row_index){
    $link_options = array(
        'categories_page' => __('categories page', 'appchar'),
        'single_category' => __('single category', 'appchar'),
        'product' => __('product id', 'appchar'),
        'telegram' => __('telegram id', 'appchar'),
        'instagram' => __('instagram id', 'appchar'),
        'link' => __('url address', 'appchar'),
        'phone_number' => __('Phone number','appchar'),

    );
    if (AppcharExtension::extensionIsActive('blog')) {
        $link_options['post']=__('post id', 'appchar');
        $link_options['blog_categories_page'] = __('blog categories page','appchar');
        $link_options['blog_single_category'] = __('blog category page','appchar');
    }
    if (AppcharExtension::extensionIsActive('lottery')) {
        $link_options['lottery']=__('lottery id', 'appchar');
    }
    $display_input = '';
    $display_tax   = '';
    if($row['link_type']=='single_category'){
        $display_input = 'style="display:none"';
    }elseif($row['link_type']==-1 || $row['link_type']=='categories_page' || $row['link_type']=='blog_categories_page' ){
        $display_input = 'style="display:none"';
        $display_tax   = 'style="display:none"';
    }else{
        $display_tax   = 'style="display:none"';
    }
    $arg = array(
        'name' => 'banner_'.$row_index,
        'up' => array(
            'tag'   =>'text',
            'text' => __('select your image', 'appchar_wp'),
        ),
        'image' => array(
            'image_can_remove' => false,
            'url' => $row['image'],
        ),
        'down' => array(
            array(
                'tag'=>'select',
                'name' => "_type[]",
                'class' => "banner_type_select",
                'options' => $link_options,
                'custom_attributes'=>'onchange="link_type_selector(this)"',
                'selected' => $row['link_type'],
                'show_option_none'=> __('none'),
            ),
            array(
                'tag'   => 'input',
                'type' => "text",
                'name' => "_link[]",
                'class' => "txt_id",
                'custom_attributes'=> 'placeholder="'.__('insert your id','appchar').'"'.$display_input,
                'value' => $row['link'],

            ),
            array(
                'tag'=> 'taxonomy',
                'class' => 'category',
                'custom_attributes'=> $display_tax,
                'selected' => $row['category_id'],
                'show_option_none' => __('None'),
            ),
        )
    );
    $banner_card = new appchar_Card($arg);
    echo $banner_card->view_card();
}
function banner_set_values($row_index){
    if($_POST['banner_'.$row_index.'_image'][0]==''){
        return;
    }elseif($_POST['banner_'.$row_index.'_type'][0]=='product'){
        $pdt = wc_get_product($_POST['banner_'.$row_index.'_link'][0]);
        if(!$pdt){
            return;
        }
    }elseif ($_POST['banner_'.$row_index.'_type'][0]=='post'){
        if(!AppcharExtension::extensionIsActive('blog')){
            return;
        }
        $pdt = get_post($_POST['banner_'.$row_index.'_link'][0]);
        if(!$pdt){
            return;
        }
    }elseif($_POST['banner_'.$row_index.'_type'][0]=='blog_single_category'){
        if(!AppcharExtension::extensionIsActive('blog')){
            return;
        }
        $link = (int) $_POST['banner_'.$row_index.'_link'][0];
        $category = get_term( $link, 'category' );
        if(is_wp_error( $category )){
            return;
        }
        if(!$category){
            return;
        }
    }
    $banner = array(
        'type'          => 'banner',
        'image'         => $_POST['banner_'.$row_index.'_image'][0],
        'link_type'   => $_POST['banner_'.$row_index.'_type'][0],
        'category_id'   => $_POST['banner_'.$row_index.'_category_id'][0],
        'link'          => $_POST['banner_'.$row_index.'_link'][0],
    );
    return $banner;
}

function category_list_get_values($row,$row_index){

    global $appchar_card;
    $selected = array();
    foreach ($row['items'] as $items){
        $selected[]=$items['id'];
    }
    $taxonomy_args = array(
        'name' => $row['type'].'_'.$row_index,
        'class' => 'category',
        'custom_attributes'=> 'multiple',
        'selected' => $selected,
        'show_option_none'  => '',
    );
    $category_list_block = $appchar_card->generate_taxonomy_select($taxonomy_args,'');

    echo '<p>'.__('By holding down the Control key and select the categories you can choose several options','appchar').'</p>'.$category_list_block;
}
function category_list_set_values($row_index){
    $category_list = array(
        'type'          => 'category_list',
    );
    foreach ($_POST['category_list_' . $row_index . '_category_id'] as $category){
        $term_obj= get_term($category);
        $category_list['items'][]=array(
            'id'=> $category,
            'name'=> $term_obj->name,
            'category'=>$term_obj,
        );
    }
    return $category_list;

}


function product_list_get_values($row,$row_index){
    global $appchar_card;
    if($row['product_list_type']!='special_category'){
        $tax_display = 'style="display:none"';
    }else{
        $tax_display = '';
    }
    $taxonomy_args = array(
        'name' => $row['type'].'_'.$row_index,
        'class' => 'category',
        'custom_attributes'=> $tax_display,
        'selected' => $row['category_id'],
        'show_option_none'  => '',
    );
    $taxonomy = $appchar_card->generate_taxonomy_select($taxonomy_args,'');
    $list_args = array(
        'name' => '',
        'class' => '',
        'options' => array(
            'recent' => __('recent product', 'appchar'),
            'bestseller' => __('bestseller product', 'appchar'),
            'special_category' => __('special category', 'appchar'),
        ),
        'custom_attributes' => 'onchange="list_type_selector(this)"',
        'selected' => $row['product_list_type'],
    );
    $list_type = $appchar_card->generate_select($list_args,'product_list_'.$row_index.'_list_type');
    $title_field = '<input name="product_list_'.$row_index.'_title" value="'.$row['title'].'">';
    $list_block = $title_field.$list_type.$taxonomy;
    echo $list_block;
}
function product_list_set_values($row_index){
    $list = array(
        'type'          => 'product_list',
        'product_list_type'     => $_POST['product_list_'.$row_index.'_list_type'],
    );
    if($_POST['product_list_'.$row_index.'_list_type']=='special_category' && $_POST['product_list_'.$row_index.'_category_id'][0]==-1){
        return;
    }
    if($_POST['product_list_'.$row_index.'_category_id'][0]!=-1){
        if($_POST['product_list_'.$row_index.'_list_type']=='special_category') {
            $list['category_id'] = $_POST['product_list_' . $row_index . '_category_id'][0];
        }else{
            $list['category_id']=null;
        }
    }else{
        $list['category_id']=null;
    }
    if($_POST['product_list_'.$row_index.'_list_type']=='bestseller'){
        $list['sortby']= 'bestseller';
        $list['order']= 'desc';
	}elseif ($_POST['product_list_'.$row_index.'_list_type']=='recent'){
        $list['sortby']= 'recent';	
        $list['order']= 'desc';
    }else{
        $list['sortby']= 'default';
        if(get_option( 'woocommerce_default_catalog_orderby') == 'menu_order') {
            $list['order']= 'asc';
        }else {
            $list['order']= 'desc';
        }
        
    }
    $list['count']= 15;
    if($_POST['product_list_'.$row_index.'_list_type']=='bestseller') {
        $list['title'] = (isset($_POST['product_list_'.$row_index.'_title']))?$_POST['product_list_'.$row_index.'_title']:__('bestseller product','appchar');
    }elseif($_POST['product_list_'.$row_index.'_list_type']=='special_category'){
        $list['title'] = (isset($_POST['product_list_'.$row_index.'_title']))?$_POST['product_list_'.$row_index.'_title']:get_the_category_by_ID($_POST['product_list_'.$row_index.'_category_id'][0]);
    }else{
        $list['title'] = (isset($_POST['product_list_'.$row_index.'_title']))?$_POST['product_list_'.$row_index.'_title']:__('recent product','appchar');
    }
    return $list;
}

function cat_slider_get_values($row,$row_index){
    global $appchar_card;
    if($row['color_type']!='custom'){
        $tax_display = 'display:none;';
    }else{
        $tax_display = '';
    }
    $show_image_checked = ($row['show_image'])?'checked':'';
    $taxonomy_args = array(
        'name' => $row['type'].'_'.$row_index,
        'class' => 'category',
        'selected' => $row['category_id'],
        'show_option_none'  => 'دسته های اصلی',
    );

    $cat_block_taxonomies = $appchar_card->generate_has_child_taxonomy_select($taxonomy_args,'');
    $list_args = array(
        'name' => '_color_type',
        'class' => '',
        'options' => array(
            'app_color' => __('App color', 'appchar'),
            'green' => __('Green', 'appchar'),
            'custom' => __('custom', 'appchar'),
        ),
        'custom_attributes' => 'onchange="show_custom_color_field(this)"',
        'selected' => $row['color_type'],
    );
    $color_type = $appchar_card->generate_select($list_args,$row['type'].'_'.$row_index);
    $custom_color_field = '<input value="'.$row['custom_text_color'].'" name="'.$row['type'].'_'.$row_index.'_custom_text_color" placeholder="'.__('text color example:','appchar').'#000000" class="custom-text-color" style="'.$tax_display.'min-width: 230px;">
<input value="'.$row['custom_bg_color'].'" name="'.$row['type'].'_'.$row_index.'_custom_bg_color" placeholder="'.__('background color example:','appchar').'#ffffff" class="custom-bg-color" style="'.$tax_display.'min-width: 230px;">';
    $show_image =  '<input type="checkbox" name="'.$row['type'].'_'.$row_index.'_show_image" value="1" '.$show_image_checked.'><label for="'.$row['type'].'_'.$row_index.'_show_image">نمایش تصویر دسته بندی</label>';

    $cat_block = $cat_block_taxonomies.$color_type.$custom_color_field.$show_image;
    echo $cat_block;
}
function cat_slider_set_values($row_index){

    if($_POST['cat_slider_'.$row_index.'_color_type']=='custom' && ($_POST['cat_slider_'.$row_index.'_custom_text_color']==''  || $_POST['cat_slider_'.$row_index.'_custom_bg_color']=='')){
        return;
    }
    $color_types = array('app_color','green','custom');
    if(!in_array($_POST['cat_slider_'.$row_index.'_color_type'],$color_types)){
        return;
    }
    $list = array(
        'type'          => 'cat_slider',
        'category_id'     => (int) $_POST['cat_slider_'.$row_index.'_category_id'][0],
        'color_type' => $_POST['cat_slider_'.$row_index.'_color_type'],
        'custom_text_color' => sanitize_text_field($_POST['cat_slider_'.$row_index.'_custom_text_color']),
        'custom_bg_color' =>sanitize_text_field($_POST['cat_slider_'.$row_index.'_custom_bg_color']),
        'show_image' => ($_POST['cat_slider_'.$row_index.'_show_image'] == 1) ? true : false,
    );
    return $list;
}

function grid_get_values($row,$row_index){
    $link_options = array(
        'categories_page' => __('categories page', 'appchar'),
        'single_category' => __('single category', 'appchar'),
        'product' => __('product id', 'appchar'),
        'telegram' => __('telegram id', 'appchar'),
        'instagram' => __('instagram id', 'appchar'),
        'link' => __('url address', 'appchar'),
        'phone_number' => __('Phone number','appchar'),

    );
    if (AppcharExtension::extensionIsActive('blog')) {
        $link_options['post']=__('post id', 'appchar');
        $link_options['blog_categories_page'] = __('blog categories page','appchar');
        $link_options['blog_single_category'] = __('blog category page','appchar');
    }
    if (AppcharExtension::extensionIsActive('lottery')) {
        $link_options['lottery']=__('lottery id', 'appchar');
    }
    global $appchar_card;
    $grid_options = array(
        'name' => '',
        'class' => '',
        'options' => array(
            'full' => __('full width', 'appchar'),
            'half' => __('two grid in width', 'appchar'),
            'third' => __('three grid in width', 'appchar'),
        ),
        'custom_attributes'=> 'onchange="appchar_append_gridcard(this)"',
        'selected' => $row['grid_column_type'],
        'show_option_none' => __('none')
    );
    echo '<div class="grid-type"><input type="hidden" value="'.$row_index.'">'.$appchar_card->generate_select($grid_options,'grid_'.$row_index.'_column_type').'</div>';
    foreach ($row['items'] as $item) {
        $display_input = '';
        $display_tax   = '';
        if($item['link_type']=='single_category'){
            $display_input = ' style="display:none"';
        }elseif($item['link_type']=='categories_page' || $item['link_type']=='blog_categories_page'){
            $display_input = ' style="display:none"';
            $display_tax   = ' style="display:none"';
        }else{
            $display_tax   = ' style="display:none"';
        }
        $arg = array(
            'name' => 'grid_'.$row_index,
            'up' => array(
                'tag'   => 'text',
                'text' => __('select your image', 'appchar_wp'),
            ),
            'image' => array(
                'image_can_remove' => false,
                'url' => $item['image'],
            ),
            'down' => array(
                array(
                    'tag'   => 'select',
                    'name' => "_type[]",
                    'class' => "banner_type_select",
                    'options' => $link_options,
                    'custom_attributes'=>'onchange="link_type_selector(this)"',
                    'selected' => $item['link_type'],
                ),
                array(
                    'tag'   => 'input',
                    'type'  => "text",
                    'name'  => "_link[]",
                    'class' => "txt_id",
                    'custom_attributes'=> 'placeholder="'.__('insert your id','appchar').'"'.$display_input,
                    'value' => $item['link'],

                ),
                array(
                    'tag'   => 'taxonomy',
                    'class' => 'category',
                    'custom_attributes'=> $display_tax,
                    'selected' => $item['category_id'],
                    'show_option_none' => __('None'),
                ),
            )
        );
        $grid_card = new appchar_Card($arg);
        echo $grid_card->view_card();
    }
}
function grid_set_values($row_index){
    $grid_items = array();
    if($_POST['grid_'.$row_index.'_type']==-1){
        return null;
    }
    foreach ($_POST['grid_'.$row_index.'_id'] as $key=>$unused){
        $img = $_POST['grid_'.$row_index.'_image'][$key];
        $cat_id= $_POST['grid_'.$row_index.'_category_id'][$key];
        if($img == ''){
            return null;
        }
        if($_POST['grid_'.$row_index.'_type'][$key]=='product'){
            $pdt = wc_get_product($_POST['grid_'.$row_index.'_link'][$key]);
            if(!$pdt){
                return null;
            }
        }elseif ($_POST['grid_'.$row_index.'_type'][$key]=='post'){
            if(!AppcharExtension::extensionIsActive('blog')){
                return null;
            }
            $pdt = get_post($_POST['grid_'.$row_index.'_link'][$key]);
            if(!$pdt){
                return null;
            }
        }elseif($_POST['grid_'.$row_index.'_type'][$key]=='blog_single_category'){
            if(!AppcharExtension::extensionIsActive('blog')){
                return;
            }
            $link = (int) $_POST['grid_'.$row_index.'_link'][$key];

            $category = get_term( $link, 'category' );
            if(is_wp_error( $category )){
                return;
            }
            if(!$category){
                return;
            }
        }
        $grid_items[] = array(
            'image'         => $img,
            'link_type'     => $_POST['grid_'.$row_index.'_type'][$key],
            'category_id'   => $cat_id,
            'link'          => $_POST['grid_'.$row_index.'_link'][$key],
        );
    }
    $grid = array(
        'type'          => 'grid',
        'grid_column_type'   => $_POST['grid_'.$row_index.'_column_type'],
        'items'   => $grid_items,
    );
    return $grid;
}


function special_offer_get_values($row,$row_index){
    if (!AppcharExtension::extensionIsActive('special_offer')) {
        echo '<p>'.__('this feature is not active','appchar').'</p>';
        return;
    }
    if(!$row['items']){
        echo '';
        return;
    }
    $items = implode(',',$row['items']);
    $special_offer = '<input class="specialOfferpid" value="'.$items.'" name="special_offer_'.$row_index.'_pids" placeholder="12,14,16"  style="float: right;" />this product id must be specail offer';
    $special_offer .= '<select onchange="changedImageUrl()" name="special_offer_theming_options_' . $row_index . '">';
    $special_offer_theming_options = array('type_1' => __('type 1', 'appchar'), 'type_2' => __('type 2', 'appchar'));
    foreach ($special_offer_theming_options as $key => $special_offer_theming_option) {
        if ($row['special_offer_type'] == $key) {
          if($key == 'type_2') {
            $special_offer .= '<option id="'. $key . '" value="' . $key . '" selected>' . $special_offer_theming_option . '</option>';
          }else {
            $special_offer .= '<option id="'. $key . '" value="' . $key . '" selected>' . $special_offer_theming_option . '</option>';
          }
        } else {
            $special_offer .= '<option id="'. $key . '" value="' . $key . '">' . $special_offer_theming_option . '</option>';
        }
    }
    $special_offer .= '</select>';
    if($row['special_offer_type_value']=='') {
        $special_offer .= '<div class="card2 ui-sortable-handle" id="specialImageOffer">
            <a onclick="appchar_remove_image(this, \'logo_image_id\')" class="remove_image" style="display: none;"><img src="' . APPCHAR_IMG_URL . 'home_config/close.png"></a>
            <div class="banners-img">
                <a id="slide_upload" onclick="appchar_upload_win(this)" class="slide_upload um-cover-add um-manual-trigger atag" style="width:100%; height: 370px;" data-parent=".um-cover" data-child=".um-btn-auto-width">
                    <img class="thumbnail2 imgtag" src="" style="display: none;" width="300" height="60">
                    <span class="dashicons dashicons-plus-alt banner-icon"></span>
              </a>
                <input type="text" class="logo_image_id" name="special_offer_theming_option_value_' . $row_index . '" style="display: none;" id="logo_image_id" value="" />
            </div>
            <div style="text-align: center;">';
            _e('Your photo should be 60x300 or less','appchar');
            $special_offer .= '</div>
        </div>';
    }else{
        $special_offer .= '<div class="card2 ui-sortable-handle" id="specialImageOffer">
            <a onclick="appchar_remove_image(this, \'logo_image_id\')" class="remove_image"><img src="' . APPCHAR_IMG_URL . 'home_config/close.png"></a>
            <div class="banners-img">
                <a id="slide_upload" onclick="appchar_upload_win(this)" class="slide_upload um-cover-add um-manual-trigger atag" style="width:100%; height: 370px;" data-parent=".um-cover" data-child=".um-btn-auto-width">';
                    if (is_int($row['special_offer_type_value'])) {
                      $logo_image = wp_get_attachment_image_src( $row['special_offer_type_value'], 'appcahr-60' );
                      $special_offer .= '<img class="thumbnail2 imgtag" src="'.$logo_image[0].'" width="'.$logo_image[1].'" height="'.$logo_image[2].'">';
                    }else {
                      $special_offer .= '<img class="thumbnail2 imgtag" src="' . $row['special_offer_type_value'] . '">';
                    }

                    $special_offer .= '<span class="dashicons dashicons-plus-alt banner-icon" style="display: none;"></span>
                </a>
                <input type="text" class="logo_image_id" name="special_offer_theming_option_value_' . $row_index . '" style="display: none;" id="logo_image_id" value="' . $row['special_offer_type_value'] . '" />
            </div>
            <div style="text-align: center;">
            </div>
        </div>';
    }
    echo $special_offer;
}
function special_offer_set_values($row_index){
    if (!AppcharExtension::extensionIsActive('special_offer')) {
        return;
    }elseif(isset($_POST['special_offer_'.$row_index.'_pids'])){
        $items = explode(',',$_POST['special_offer_'.$row_index.'_pids']);
        $items2 = array();
        foreach ($items as $item){
            $product = wc_get_product( $item );
            if($product->is_on_sale()){
                $items2[] = $item;
            }
        }

        if(isset($_POST['special_offer_theming_options_' . $row_index])) {
          $special_offer_type = $_POST['special_offer_theming_options_' . $row_index];

        }else {
          $special_offer_type = 'type_1';
        }
        if($special_offer_type == 'type_1') {
          $special_offer_type_value = '';
        }else {

          if (isset($_POST['special_offer_theming_option_value_' . $row_index]) && $_POST['special_offer_theming_option_value_' . $row_index] != '') {
            $special_offer_type_value = $_POST['special_offer_theming_option_value_' . $row_index];
          }else {
            $special_offer_type_value = "https://www.digikala.com/static/files/b6c724a0.png";
          }
        }


        $special_offer = array(
            'type'  => 'special_offer',
            'items' => $items2,
            'special_offer_type' => $special_offer_type,
            'special_offer_type_value' => wp_get_attachment_image_src($special_offer_type_value,'full')[0] ? wp_get_attachment_image_src($special_offer_type_value,'full')[0] : $special_offer_type_value,
        );
        return $special_offer;
    }else{
        return;
    }
}

function post_list_get_values($row,$row_index){
    if (!AppcharExtension::extensionIsActive('blog')) {
        echo '<p>'.__('this feature is not active','appchar').'</p>';
        return;
    }
    global $appchar_card;
    $input_args = array(
        'custom_attributes' => 'placeholder="'.__("insert your title",'appchar').'"',
        'value'             => $row['title'],
    );
    $post_list_type = $appchar_card->generate_input($input_args,'post_list_'.$row_index.'_list_title');
    echo $post_list_type;
}
function post_list_set_values($row_index){
    if (!AppcharExtension::extensionIsActive('blog')) {
        return;
    }
    $list = array(
        'type' => 'post_list',
        'post_list_type' => 'recent',
        'sortby' => 'recent',
        'order' => 'desc',
        'count' => 15,
        'title' => (isset($_POST['post_list_' . $row_index . '_list_title'])) ? trim($_POST['post_list_' . $row_index . '_list_title']) : __('recent post', 'appchar'),
    );
    return $list;
}

function lottery_leader_board_get_values($row,$row_index){
    if (!AppcharExtension::extensionIsActive('lottery')) {
        echo '<p>'.__('this feature is not active','appchar').'</p>';
        return;
    }
    global $appchar_card;
    $input_args = array(
        'custom_attributes' => 'placeholder="'.__("lottery id",'appchar').'"',
        'value'             => $row['lottery_id'],
    );
    $lottery_leader_board = $appchar_card->generate_input($input_args,'lottery_id_'.$row_index);
    echo $lottery_leader_board;
}
function lottery_leader_board_set_values($row_index){
    if (!AppcharExtension::extensionIsActive('lottery')) {
        return;
    }
    $lottery = array(
        'type' => 'lottery_leader_board',
        'lottery_id' => (isset($_POST['lottery_id_' . $row_index])) ? trim($_POST['lottery_id_' . $row_index]) : __('recent post', 'appchar'),
    );
    return $lottery;
}


function filter_get_values($row,$row_index){
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
function filter_set_values($row_index){
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


/*
function _get_values($row,$row_index){}
function _set_values($row_index){}
*/
?>
