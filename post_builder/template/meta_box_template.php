<?php
/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/2/17
 * Time: 9:56 AM
 */
//so, dont ned to use esc_attr in front of get_post_meta
$tab_value = get_post_meta($post->ID, 'appchar_post_builder', true);
$options = get_option('appchar_general_setting');
$tab_count = (isset($options['custom_tab_count']))?$options['custom_tab_count']:get_option('appchar_custom_tab_count',0);
$tab_count = intval($tab_count);

$default_elements_list = get_element_list();

//$default_elements_list = array(
//    'text' => array(
//        'type'  => 'text',
//        'title' => 'المان متن',
//        'dashicon' => 'dashicons-welcome-write-blog',
//        'values' => '<textarea name="text-element-fileds"></textarea>',
//    ),
//    'video'=>array(
//        'type'  => 'video',
//        'title' => 'المان ویدیو',
//        'dashicon' => 'dashicons-editor-video',
//        'values' => '<input name="video-element-fileds">',
//    ),
//    'slider'=>array(
//        'type'  => 'slider',
//        'title' => 'اسلایدر',
//        'dashicon' => 'dashicons-images-alt2',
//        'values' => '<input name="slider-element-fileds">',
//    ),
//    'button'=>array(
//        'type'  => 'button',
//        'title' => 'دکمه',
//        'dashicon' => 'dashicons-editor-removeformatting',
//        'values' => '<input name="button-element-fileds">',
//    ),
//    'grid1'=>array(
//        'type'  => 'grid1',
//        'title' => 'گرید کامل',
//        'dashicon' => 'dashicons-format-image',
//        'values' => '<input name="grid1-element-fileds">',
//    ),
//    'grid2'=>array(
//        'type'  => 'grid2',
//        'title' => 'گرید 1/2',
//        'dashicon' => 'dashicons-format-gallery',
//        'values' => '<input name="grid2-element-fileds">',
//    ),
//    'grid3'=>array(
//        'type'  => 'grid3',
//        'title' => 'گرید 1/3',
//        'dashicon' => 'dashicons-images-alt',
//        'values' => '<input name="grid3-element-fileds">',
//    ),
//
//
//);//TODO این تابع باید از جای دیگری گرفته شود ، این فعلا تست است.
?>
<script>
    <?php
    foreach ($default_elements_list as $element) {
        echo 'var '.$element->type.'_obj = '.json_encode($element).';';
    }
    ?>
</script>

<div id="meta-box-content" class="row sortable ui-sortable">
    <div id="all-elements-content"></div>
    <div class="add_element">
        <div>
            <a onclick="pb_open_popup_window(this)"><span class="pb-dashicon-100 dashicons dashicons-plus-alt"></span></a>
        </div>


        <div id="select-element-window" class="modal">

            <!-- Modal content -->
            <div class="modal-content">
                <div class="popup-window-header">
                    <span onclick="close_popup_window(this)" class="close">&times;</span>
                </div>
                <div class="popup-window-body">
                    <ul class="elmnt-list">

                        <?php
                        foreach ($default_elements_list as  $value){
                            ?>
                            <li>
                                <a onclick="add_row_function(<?php echo $value->type;?>_obj)">
                                    <div class="">
                                        <div class="elmnt-icon">
                                            <span class="pb-dashicon-50 dashicons <?php echo $value->dashicon; ?>"></span>
                                        </div>
                                        <div class="elmnt-name"> <?php echo $value->title; ?></div>
                                    </div>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="popup-window-footer">

                </div>
            </div>

        </div>


    </div>
</div>


<div id="loading" class="modal2">
    <div style="margin: 10% auto;position: relative;width: 50px; height: 50px;"><img width="50px" src="<?php echo APPCHAR_IMG_URL.'loading.gif'; ?>"></div>
</div>

<script>
    <?php


    $saved_element = get_post_meta($post->ID,'post_builder_elements',true);
    ?>

    var obj = <?php echo json_encode($saved_element); ?>;
    var i = 0;
    if(obj.elements){
    var array = obj.elements;

    //    console.log(obj.elements[1].type + ' - ' + obj.elements[1].dashicon);


    array.forEach(function (object) {
        var defaultObject = eval(object.type+'_obj');
        var temp = Object.assign({},object,defaultObject);
        add_row_function(temp);
    });
    }

//    // Get the modal
//    var modal = document.getElementById('select-element-window');
//
//    // Get the button that opens the modal
//    var btn = document.getElementById("myBtn");
//    // Get the <span> element that closes the modal
//    var span = document.getElementsByClassName("close")[0];
//
//    // When the user clicks on the button, open the modal
//    btn.onclick = function () {
//        modal.style.display = "block";
//    }


//    function textarea_to_tinymce(id){
////        if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
//            tinyMCE.execCommand('mceAddEditor', false, id);
//            tinyMCE.execCommand('mceAddControl', false, id);
//
////        }
//    }
    function add_row_function(obj) {
        jQuery(".modal2").css('display','block');
        var data = {
            'action': 'pb_get_fields',
            'element_obj': obj,
            'row_number': i,

        };
        jQuery.ajax({
            type:"POST",
            url:ajaxurl,
            data:data,
            async:false,
            success:function(response) {
                var elmnt_block = '<div class="content-block app-row ui-sortable-handle">' +
                    '<div class="elmnt-action"><div onclick="appchar_remove_elmnt(this)" class="action-div remove-elmnt">' + pb_get_dashicon('dashicons-trash') + '</span></div><div onclick="pb_open_popup_window(this)" class="action-div edit-elmnt">' + pb_get_dashicon('dashicons-edit') + '</div></div>' +
                    '<div class="body-elmnt">' + '<div>' + pb_get_dashicon(obj.dashicon) + '</div><div>' + obj.title + '</div></div>' +
                    '<div class="modal" ><div class="modal-content"><div class="popup-window-header"><a onclick="close_popup_window(this)" class="close">&times;</a><h1 style="text-align: center;">' + obj.title + '</h1></div><div class="popup-window-body">' + response + '</div><div class="popup-window-footer"><a onclick="close_popup_window(this)" class="button button-primary button-large">ذخیره تغییرات</a></div></div></div>' +
                    '</div>';
                jQuery('#all-elements-content').append(elmnt_block);
                jQuery('#select-element-window').css("display", "none");
            },
            error:function(err){
                alert('اطلاعات به درستی بارگذاری نشد لطفا صفحه را دوباره رفرش کنید و در صورت مواجه شدن با این ارور با پشتیبانی اپچار تماس حاصل فرمایید.');
            }
        });
        i++;
        jQuery(".modal2").css('display','none');

    }


    function appchar_remove_elmnt(_this) {
        jQuery(_this).parent().parent().remove();
    }

    function tinymce_init(id) {

    }
    function close_popup_window(_this) {
        jQuery(_this).parent().parent().parent().css('display', 'none');
    }

    function pb_open_popup_window(_this) {
        jQuery(_this).parent().parent().children(".modal").css('display', 'block');
        tinymce.init({
            selector: "#"+jQuery(_this).parent().parent().children(".modal").children(".modal-content").children(".popup-window-body").children("textarea").attr("id")
        });

    }

    function pb_get_dashicon(item) {
        var dashicon = '<span class=\' dashicons ' + item + ' \'></span>';
        return dashicon
    }

    function open_library_window(_this) {
//        if (mediaUploader) {
//            mediaUploader.open();
//            return;
//        }
        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            }, multiple: false });

        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function() {
            attachment = mediaUploader.state().get('selection').first().toJSON();
            jQuery(_this).attr('src',attachment.url);
            jQuery(_this).parent().children('.input-hidden-image').attr('value',attachment.url);
        });
        // Open the uploader dialog
        mediaUploader.open();
    }


    //    var a ="test";
    //    alert(a);
    //    alert(window["a"]);
    //    alert(eval("a"));

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (jQuery(event.target).hasClass("modal")) {
            jQuery(event.target).css("display", "none");
        }
    }

    jQuery(function () {
        jQuery("#all-elements-content").sortable();
        jQuery("#all-elements-content").disableSelection();
    });

    <?php do_action('add_script_to_postbuilder_meta_box') ?>


</script>


