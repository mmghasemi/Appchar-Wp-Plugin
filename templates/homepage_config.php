<?php
include_once APPCHAR_INC_DIR . 'appchar_Card.php';
global $appchar_card;
$appchar_card = new appchar_Card();

//-------------------------------------------------------------------------------------------------------

$main_type_args = array(
    'custom_attributes'=>'onchange="appchar_set_row_options(this)" class="inputTypeValidation"',
    'options'=> array(
        'none' => __('none', 'appchar'),
        'slider' => __('slider', 'appchar'),
        'button' => __('button', 'appchar'),
        'banner' => __('full width image', 'appchar'),
 //       'category_list' => __('category list', 'appchar'),
        'product_list' => __('product list', 'appchar'),
        'grid' => __('grid view', 'appchar'),
        'cat_slider' => __('Category Slider', 'appchar'),
    ),
);
if (AppcharExtension::extensionIsActive('special_offer')) {
    $main_type_args['options']['special_offer']=__('special offer','appchar');
}
$filter_block='';
if (AppcharExtension::extensionIsActive('hierarchical_filter')) {
    $main_type_args['options']['filter']=__('Hierarchical Filter','appchar');
    $input_args = array(
        'custom_attributes' => 'placeholder="'.__("insert your title",'appchar').'"'
    );
    $filter_title = $appchar_card->generate_input($input_args,'filter_title');

//$post_list_block = $post_list_type.$taxonomy;
    $filter_block = for_js_file($filter_title);
}
if (AppcharExtension::extensionIsActive('blog')) {
    $main_type_args['options']['post_list']=__('post list', 'appchar');
}
if (AppcharExtension::extensionIsActive('lottery')) {
    $main_type_args['options']['lottery_leader_board']=__('lottery leader board', 'appchar');
}
$selectbox = $appchar_card->generate_select($main_type_args,'row-type[]');
$selectbox = for_js_file($selectbox);

//---------------------------------------------------------------------------------------------------------

$taxonomy_args = array(
    'name' => '',
    'class' => 'category',
    'custom_attributes'=> 'style="display:none"',
    'selected' => '',
    'show_option_none'  => '',
);

$taxonomy = $appchar_card->generate_taxonomy_select($taxonomy_args,'');

//--------------------------------------------------------------------------------------------------------
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
$link_args = array(
    'name' => '',
    'class' => '',
    'options' => $link_options,
    'custom_attributes' => 'onchange="link_type_selector(this)"',
    'selected' => '',
);
$link_type = $appchar_card->generate_select($link_args,'_link_type');

$input_args = array(
    'custom_attributes' => 'placeholder="'.__("insert your text",'appchar').'" style="display:none"'
);
$link_block = $link_type.$taxonomy.$appchar_card->generate_input($input_args,'_link_input');
$link_block = for_js_file($link_block);

//-------------------------------------------------------------------------------------------------------

$taxonomy_args = array(
    'name' => 'category_list',
    'class' => 'category',
    'custom_attributes'=> 'multiple',
    'selected' => '',
    'show_option_none'  => '',
);
$category_list = $appchar_card->generate_taxonomy_select($taxonomy_args,'');
$category_list = for_js_file($category_list);
//-------------------------------------------------------------------------------------------------------
$taxonomy_args = array(
    'name' => 'cat_slider',
    'class' => 'category',
    'selected' => '',
    'show_option_none'  => 'دسته های اصلی',
);

$cat_block_taxonomies = $appchar_card->generate_has_child_taxonomy_select($taxonomy_args,'');
$list_args = array(
    'name' => '',
    'class' => '',
    'options' => array(
        'app_color' => __('App color', 'appchar'),
        'green' => __('Green', 'appchar'),
        'custom' => __('custom', 'appchar'),
    ),
    'custom_attributes' => 'onchange="show_custom_color_field(this)"',
    'selected' => '',
);
$color_type = $appchar_card->generate_select($list_args,'cat_slider_color_type');
$custom_color_field = '<input name="cat_slider_custom_text_color" placeholder="'.__('text color example:','appchar').'#000000" class="custom-text-color" style="display: none;min-width: 230px;">
<input name="cat_slider_custom_bg_color" placeholder="'.__('background color example:','appchar').'#ffffff" class="custom-bg-color" style="display: none;min-width: 230px;">';
$show_image =  '<input type="checkbox" name="cat_slider_show_image" value="1" ><label for="cat_slider_show_image">نمایش تصویر دسته بندی</label>';

$cat_block = $cat_block_taxonomies.$color_type.$custom_color_field.$show_image;
$cat_slider = for_js_file($cat_block);

$list_args = array(
    'name' => '',
    'class' => '',
    'options' => array(
        'recent' => __('recent product', 'appchar'),
        'bestseller' => __('bestseller product', 'appchar'),
        'special_category' => __('special category', 'appchar'),
    ),
    'custom_attributes' => 'onchange="list_type_selector(this)"',
    'selected' => '',
);
$list_type = $appchar_card->generate_select($list_args,'product_list_type');
$title_field = '<input name="product_list_title">';


$list_block = $title_field.$list_type.$taxonomy;
$list_block = for_js_file($list_block);

//-----------------------------------------------------------------------------------------------

$input_args = array(
    'custom_attributes' => 'placeholder="'.__("insert your title",'appchar').'"'
);
$post_list_title = $appchar_card->generate_input($input_args,'post_list_title');

//$post_list_block = $post_list_type.$taxonomy;
$post_list_block = for_js_file($post_list_title);

//--------------------------------------------------------------------------------------------


$input_args = array(
    'custom_attributes' => 'placeholder="'.__("lottery id",'appchar').'"'
);
$lottery_leader_board = $appchar_card->generate_input($input_args,'lottery_id');

//$post_list_block = $post_list_type.$taxonomy;
$lottery_leader_board = for_js_file($lottery_leader_board);

//-----------------------------------------------------------------------------------------------

$grid_options = array(
    'name' => '',
    'class' => '',
    'options' => array(
        'full' => __('full width', 'appchar'),
        'half' => __('two grid in width', 'appchar'),
        'third' => __('three grid in width', 'appchar'),
    ),
    'custom_attributes'=> 'onchange="appchar_append_gridcard(this)"',
    'selected' => '',
    'show_option_none' => __('none')
);
$grid_type = $appchar_card->generate_select($grid_options,'grid_column_type');
$grid_type = for_js_file($grid_type);

$arg = array(
    'name' => 'grid',
    'up' => array(
            'tag'=>'text',
        'text' => __('select your image', 'appchar_wp'),
    ),
    'image' => array(
        'image_can_remove' => false,
        'url' => '',
        'main_obj' => '',
    ),
    'down' => array(
        array(
            'tag'=>'select',
            'name' => "_type[]",
            'class' => "banner_type_select",
            'options' => $link_options,
            'custom_attributes'=>'onchange="link_type_selector(this)"',
            'selected' => '',
        ),
        array(
            'tag'=>'input',
            'type' => "text",
            'name' => "_link[]",
            'class' => "txt_id",
            'custom_attributes'=> 'placeholder="'.__('insert your id','appchar').'" style="display:none"',
            'value' => "",
        ),
        array(
            'tag'=>'taxonomy',
            'class' => 'category',
            'custom_attributes'=> 'style="display:none"',
            'selected' => -1,
            'show_option_none' => __('None'),
        ),
    )
);
$grid_card = new appchar_Card($arg);
$grid = $grid_card->view_card();
$grid = for_js_file($grid);

//----------------------------------------------------------------------------------------------------------------

$arg = array(
    'name' => 'slider',
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
            'value' => "",

        ),
        array(
            'tag'=>'taxonomy',
            'class' => 'category',
            'custom_attributes'=> 'style="display:none"',
            'selected' => -1,
            'show_option_none' => __('None'),
        ),
    )
);
$slider_card = new appchar_Card($arg);
$slider =  '<div class="slider-type" style="float: right;"><label for="slider_rotate">قابلیت چرخش</label><input type="checkbox" name="slider_rotate" value="1" ><br>';
$slider .= '<label for="slider_rotate_time">زمان مکث چرخش</label><input type="number" name="slider_rotate_time" min="500" max="5000" style="width:50px;">ms</div>';

$slider .= $slider_card->view_card();
$slider = for_js_file($slider);

//----------------------------------------------------------------------------------------------------------------

$arg = array(
    'name' => 'banner',
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
            'value' => "",

        ),
        array(
            'tag'=>'taxonomy',
            'class' => 'category',
            'custom_attributes'=> 'style="display:none"',
            'selected' => -1,
            'show_option_none' => __('None'),
        ),
    )
);
$banner_card = new appchar_Card($arg);
$banner = $banner_card->view_card();
$banner = for_js_file($banner);

//----------------------------------------------------------------------------------------------------------------
if (AppcharExtension::extensionIsActive('special_offer')) {

    $special_offer = '<input class="specialOfferpid" name="special_offer_pids" placeholder="12,14,16" style="float: right;" />this product id must be specail offer';
    $special_offer = for_js_file($special_offer);
}else{
    $special_offer = '<p>'.__('this feature is not active','appchar').'</p>';
}
//----------------------------------------------------------------------------------------------------------------
?>
<script>
    var remove_title            = "<?php _e('remove','appchar'); ?>";
    var assets_url              = "<?php echo APPCHAR_URL; ?>assets/";
    var selectbox               = "<?php echo $selectbox; ?>";
    var new_slider_card         = "<?php echo $slider; ?>";
    var grid_type               = "<?php echo $grid_type; ?>";
    var banner                  = "<?php echo $banner; ?>";
    var grid_card               = "<?php echo $grid; ?>";
    var special_offer           = "<?php echo $special_offer;?>";
    var lottery_leader_board    = "<?php echo $lottery_leader_board;?>";
    var link_block              = "<?php echo $link_block; ?>";
    var category_list           = "<?php echo '<p>'.__('By holding down the Control key and select the categories you can choose several options','appchar').'</p>'.$category_list; ?>";
    var list_block              = "<?php echo $list_block; ?>";
    var post_list_block         = "<?php echo $post_list_block; ?>";
    var filter_block            = "<?php echo $filter_block; ?>";
    var cat_slider               = "<?php echo $cat_slider; ?>";
</script>
<?php

require APPCHAR_INC_DIR.'homepage_Config_Functions.php';

if(isset($_POST['save_homepage_config'])){
    $row_type = $_POST['row-type'];
    $appchar_homepage_config2 = array();
    foreach ($row_type as $index=>$item) {
        if(isset($_POST['homepage_type']) && $_POST['homepage_type']=='full-screen'){
            if($item!='banner'){
                continue;
            }
        }
        $row_index = $_POST['row_index'][$index];
        $function = $item.'_set_values';
        if(function_exists($function)){
            $row = $function($row_index);
            if($row != null) {
                $appchar_homepage_config2[]= $row;
              }
        }
    }
    if(AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
        if(isset($_GET['lang'])){
            $lang = $_GET['lang'];
        }else{
            if(defined('ICL_LANGUAGE_CODE')) {
                $lang = ICL_LANGUAGE_CODE;
            }else{
                $lang = 'fa';
            }
        }
        update_option('appchar_homepage_type_'.$lang, $_POST['homepage_type']);
        update_option('appchar_homepage_type', $_POST['homepage_type']);
        update_option('appchar_homepage_config2_'.$lang, $appchar_homepage_config2);
        update_option('appchar_homepage_config2', $appchar_homepage_config2);
    }else {
        update_option('appchar_homepage_type', $_POST['homepage_type']);
        update_option('appchar_homepage_config2', $appchar_homepage_config2);
    }
}
if(AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
    if(isset($_GET['lang'])){
        $lang = $_GET['lang'];
    }else{
        if(defined('ICL_LANGUAGE_CODE')) {
            $lang = ICL_LANGUAGE_CODE;
        }else{
            $lang = 'fa';
        }
    }
    $homepage_type = get_option('appchar_homepage_type_'.$lang, false);
    $homepage_config2 = get_option('appchar_homepage_config2_'.$lang, false);
}else {
    $homepage_type = get_option('appchar_homepage_type', false);
    $homepage_config2 = get_option('appchar_homepage_config2', false);
}
?>
<form method="post" onsubmit="return checkValidationForm();">
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row">نوع نمایش دسته بندی ها در صفحه دسته بندی ها</th>
            <td>
                <?php
                $full_screen = 'checked="checked"';
                $normal = '';
                if($homepage_type!='full-screen'){
                    $full_screen = '';
                    $normal = 'checked="checked"';
                }
                ?>
                <input name="homepage_type" type="radio" value="full-screen" <?php echo $full_screen ?>>تمام صفحه
                <input name="homepage_type" type="radio" value="normal" <?php echo $normal; ?>>عادی
            </td>
            <?php
            if($homepage_type=='full-screen'){
                echo '<td style="color:red;">شما فقط باید از المان <span style="font-weight: bold;">تصویر تمام عرض</span> در این حالت نمایش استفاده کنید </td>';
            }
            ?>
        </tr>
        </tbody>
    </table>
    <div class="postbox">
        <div class="row sortable">
        <?php
        if($homepage_config2){
            if(is_array($homepage_config2)) {
                foreach ($homepage_config2 as $key => $row) {
                    $show_func = $row['type'] . '_get_values';
                    if (function_exists($show_func)) {
                        $index_row = $key + 1;
                        echo '<div class="app-row ui-sortable-handle" id="index' . $index_row . '"><input type="hidden" name="row_index[]" value="' . $index_row . '"><div class="column type-column">';
                        $main_type_args['selected'] = $row['type'];
                        $main_type_args['custom_attributes'] = "class='inputTypeValidation'";
                        echo $appchar_card->generate_select($main_type_args, 'row-type[]');;//selectbox
                        echo '</div><div class="column content-column sortable">';
                        $show_func($row,$index_row);
                        echo '</div><div class="del del-column"><a class="button" onclick="appchar_del_row(this)">'.__('remove','appchar').'</a></div></div>';
                    }
                }
            }
        }
        ?>
        </div>
        <div class="add" style="padding: 10px"><a class="button" onclick="appchar_add_row(this)"><?php _e('add', 'appchar'); ?></a></div>
    </div>
<?php
submit_button('','','save_homepage_config');
?>
</form>
<script>
<?php
$general_setting = get_option('appchar_general_setting', array());
if(!$general_setting['optional_page_builder_verification']) {
  ?>

  function checkValidationForm() {
  	var inputTypes = document.getElementsByClassName("inputTypeValidation");
  	for (i = 0; i < inputTypes.length; i++) {
          var logicType = inputTypes[i].value;
          switch (logicType) {
              case 'none':
                  // console.log("None CANNOT be selected.");
                  return false;
                  break;
              case 'slider':
                  // console.log("Slider has been selected.");
                  break;
              case 'button':
                  // console.log("Button has been selected.");
                  break;
              case 'banner':
                  // console.log("Banner has been selected.");
                  break;
              case 'product_list':
                  // console.log("Product List has been selected.");
                  break;
              case 'grid':
                  var images = document.getElementsByClassName('thumbnail2');
                  for (i = 0; i < images.length; i++) {
                      if (images[i].getAttribute("src") === "" && images[i].parentElement.parentElement.parentElement.parentElement.className.search('grid') != -1) {
                          alert("لطفا تمامی عکس ها را در بخش گرید انتخاب کنید.");
                          return false;
                      }
                  }
                  console.log("Grid has been selected.");
                  break;
              default:
                  break;
          }
      }
  	return true;
  }

  <?php
}else {

}
?>

window.addEventListener('load', (event) => {
  changedImageUrl();
});
function changedImageUrl() {
  if (document.getElementById("type_2").selected == true) {
    document.getElementById("specialImageOffer").style.visibility = "visible";
  }else {
    document.getElementById("specialImageOffer").style.visibility = "hidden";
  }
}
</script>
