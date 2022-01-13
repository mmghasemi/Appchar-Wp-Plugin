/**
 * Created by parvaz on 12/4/2016.
 */
jQuery(document).ready(function ($) {

    $(".selectid").each(function () {
        var selected = $(this).children().filter(":selected").val();
        switch (selected) {
            case 'none':
                $(this).parent().children(".txt_id")[0].style.display = "none";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'search':
                $(this).parent().children(".txt_id")[0].style.display = "none";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'categories':
                $(this).parent().children(".txt_id")[0].style.display = "none";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'category':
                $(this).parent().children(".txt_id")[0].style.display = "none";
                $(this).parent().children(".product_cat")[0].style.display = "";
                break;
            case 'product':
                $(this).parent().children(".txt_id")[0].style.display = "";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'telegram':
                $(this).parent().children(".txt_id")[0].style.display = "";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'instagram':
                $(this).parent().children(".txt_id")[0].style.display = "";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'link':
                $(this).parent().children(".txt_id")[0].style.display = "";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'phone_number':
                $(this).parent().children(".txt_id")[0].style.display = "";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            default:
                break;
        }
    })

    $(".selectid").on('change', function () {
        switch ($(this).val()) {
            case "none":
                $(this).parent().children(".txt_id")[0].style.display = 'none';
                $(this).next()[0].style.display = "none";
                break;
            case 'search':
                $(this).parent().children(".txt_id")[0].style.display = "none";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case 'categories':
                $(this).parent().children(".txt_id")[0].style.display = "none";
                $(this).parent().children(".product_cat")[0].style.display = "none";
                break;
            case "category":
                $(this).parent().children(".txt_id")[0].style.display = 'none';
                $(this).next()[0].style.display = "";
                break;
            case "product":
                $(this).next()[0].style.display = "none";
                $(this).parent().children(".txt_id")[0].style.display = '';
                break;
            case "telegram":
                $(this).next()[0].style.display = "none";
                $(this).parent().children(".txt_id")[0].style.display = '';
                break;
            case "instagram":
                $(this).next()[0].style.display = "none";
                $(this).parent().children(".txt_id")[0].style.display = '';
                break;
            case "link":
                $(this).next()[0].style.display = "none";
                $(this).parent().children(".txt_id")[0].style.display = '';
                break;
            default:
                break;
        }
    });
/*
    var custom_uploader;
    var attachment;


    $('.slide_upload').click(function (e) {
        var _this = $(this);
        e.preventDefault();

        //If the uploader object has already been created, reopen the dialog
        // if (custom_uploader) {
        //     custom_uploader.open();
        //     return;
        // }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.undelegate().on('select', function () {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            //alert(_this.parent().html());
            _this.parent().children('.slide').attr('value', attachment.url);
            _this.children(".imgtag")[0].style.display = "";
            _this.children(".imgtag").attr('src', attachment.url);
            if (_this.children('.banner-icon').length > 0) {
                _this.children('.banner-icon')[0].style.display = "none";
            }
            if (_this.parent().children('.clean_image').length > 0) {
                _this.parent().children('.clean_image')[0].style.display = "";
            }
        });
        //Open the uploader dialog
        custom_uploader.open();
    });
    $('.clean_image').click(function (e) {
        $(this).parent().children(".slide").attr('value', '');
        $(this).parent().children().children(".imgtag").attr('src', '');
        $(this).parent().children().children(".imgtag")[0].style.display = "none";
        $(this).parent().children().children('.banner-icon')[0].style.display = "";
        $(this)[0].style.display = 'none';
    });

*/
});
//--------------------------------- start row -----------------------------------------------
var row_index = 0;
function appchar_add_row(obj) {
    if (jQuery('div.row div.app-row').length > 0) {
        if (row_index == 0){
            row_index = jQuery('div.row div.app-row').length;
        }
    }
    row_index = row_index+1;
    var row = "<div class='app-row ui-sortable-handle' id='index" +row_index+ "'><input type='hidden' name='row_index[]' value='" + row_index + "'><div class='column type-column'>" + selectbox +
        "</div><div class='column content-column sortable'></div><div class='del del-column'><a class='button' onclick='appchar_del_row(this)'>"+remove_title+"</a></div></div>";
    jQuery(obj).parent().parent().children('.row').append(row);
}
function appchar_del_row(obj) {
    jQuery(obj).parent().parent().remove();
}
function appchar_set_row_options(obj) {
    var row_index2 = jQuery(obj).parent().parent().children('input').val();

    switch (jQuery(obj).val()) {
        case "none":
            jQuery(obj).parent().parent().children(".content-column").empty();
            break;
        case "slider":
            var slider_var = new_slider_card.replace(new RegExp('slider_', 'g'), "slider_"+row_index2+"_");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(slider_var);
            // jQuery(obj).parent().parent().children(".content-column").append('<div><a onclick="open_popup(this)">برای مشاهده ویرایش اسلاید کلیک کنید</a></div><div class="hidden"><div class="popupcontent"><a class="hide_popup" onclick="hide_popup(this)"><img src="' + assets_url + 'images/home_config/close_popup2.png"></a><div class="sortable ui-sortable">' + new_slider_card + '</div></div></div>');
            break;
        case "button":
            select_var = link_block.replace("_link_type", "button_"+row_index2+"_link_type");
            select_var = select_var.replace("_category_id", "button_"+row_index2+"_category_id");
            select_var = select_var.replace("_link_input", "button_"+row_index2+"_link_input");
            select_var = select_var.replace("<option value='single_category'", "<option value='search'>search</option><option value='single_category'");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append('<input name="button_'+row_index2+'_title" value="" placeholder="عنوان">'+select_var);
            break;
        case "banner":
            var banner_var = banner.replace(new RegExp('banner_', 'g'), "banner_"+row_index2+"_");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(banner_var);
            break;
        case "category_list":
            var category_list_var = category_list.replace("category_list_category_id", "category_list_"+row_index2+"_category_id");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(category_list_var);
            break;
        case "product_list":
            var list_var = list_block.replace("list_type", "list_"+row_index2+"_list_type");
            list_var = list_var.replace("_category_id", "product_list_"+row_index2+"_category_id");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(list_var);
            break;
        case "post_list":
            var list_var = post_list_block.replace("list_title", "list_"+row_index2+"_list_title");
            // list_var = list_var.replace("_category_id", "post_list_"+row_index2+"_category_id");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(list_var);
            break;
        case "filter":
            var list_var = filter_block.replace("filter_title", "filter_"+row_index2+"_title");
            // list_var = list_var.replace("_category_id", "post_list_"+row_index2+"_category_id");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(list_var);
            break;
        case "grid":
            var grid_type_var = grid_type.replace(new RegExp('grid_', 'g'), "grid_"+row_index2+"_");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append('<div class="grid-type"><input type="hidden" value="' + row_index2 + '">' + grid_type_var + '</div><div class="grid get-cat sortable ui-sortable"></div>');
            break;
        case "special_offer":
            var special_offer_var = special_offer.replace(new RegExp('special_offer_', 'g'), "special_offer_"+row_index2+"_");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(special_offer_var);
            break;
        case "lottery_leader_board":
            var lottery_leader_board_var = lottery_leader_board.replace(new RegExp('lottery_id', 'g'), "lottery_id_"+row_index2);
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(lottery_leader_board_var);
            break;
        case "cat_slider":
            var cat_slider_var = cat_slider.replace(new RegExp("cat_slider_", 'g'), "cat_slider_"+row_index2+"_");
            jQuery(obj).parent().parent().children(".content-column").empty();
            jQuery(obj).parent().parent().children(".content-column").append(cat_slider_var);
            break;
        default:
            break;
    }
}
function append() {

}
//-------------------------end row ------------------------------------
//-------------------------start popup--------------------------------
function open_popup(obj) {
    var hiddenSection = jQuery(obj).parent().parent().children("div.hidden");
    hiddenSection.fadeIn()
    // unhide section.hidden
        .css({'display': 'block'})
        // set to full screen
        .css({width: $(window).width() + 'px', height: $(window).height() + 'px'})
        .css({
            top: ($(window).height() - hiddenSection.height()) / 2 + 'px',
            left: ($(window).width() - hiddenSection.width()) / 2 + 'px'
        })
        // greyed out background
        .css({'background-color': 'rgba(0,0,0,0.5)'})
        .css({'z-index': '9999'})
    // .appendTo('body');
    // console.log($(window).width() + ' - ' + $(window).height());
}
function hide_popup(obj) {
    jQuery(obj).parent().parent().fadeOut();
}
//-------------------------end popup--------------------------------

function appchar_upload_win(obj) {
    var _this = jQuery(obj);
    custom_uploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Image',
        button: {
            text: 'Choose Image'
        },
        multiple: false
    });
    custom_uploader.undelegate().on('select', function () {
        attachment = custom_uploader.state().get('selection').first().toJSON();
        if(_this.parent().children('input').attr("name").indexOf('slider') > -1 || _this.parent().children('input').attr("name").indexOf('special_offer') > -1) {
            if (_this.parent().children('input.slide').val() == '') {
                var card = _this.parent().parent()[0].outerHTML;
                // _this.parent().parent().append("<a onclick='appchar_discard_slide(this)' class='discard_slide'><img src='"+assets_url+"/images/home_config/close.png'></a>");
                _this.parent().parent().children('.hidden').before("<a onclick='appchar_discard_slide(this)' class='discard_slide'><img src='"+assets_url+"/images/home_config/close.png'></a>");
                _this.parent().parent().parent().append(card);
            }
        }
        _this.parent().parent().children('.remove_image').css("display",'');

        _this.parent().children('.logo_image_id').attr('value', attachment.id);
        _this.parent().children('.slide').attr('value', attachment.url);
        _this.children(".imgtag").css('display', '');
        _this.children(".imgtag").attr('src', attachment.url);
        if (_this.children('.banner-icon').length > 0) {
            _this.children('.banner-icon').css('display', 'none');
        }
        if (_this.parent().children('.clean_image').length > 0) {
            _this.parent().children('.clean_image').css('display', '');
        }
    });
    custom_uploader.open();
}
function appchar_discard_slide(obj) {
    var _this = jQuery(obj).parent('.card2');//.outerHTML;
    _this.remove();
}

//------------------------------------start slider banner----------------------------------------
function link_type_selector(obj) {
    _this = jQuery(obj)
    var selected = _this.val();
    switch (selected) {
        case '-1':
            _this.parent().children(".txt_id").css('display', 'none');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'categories_page':
            _this.parent().children(".txt_id").css('display', 'none');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'blog_categories_page':
            _this.parent().children(".txt_id").css('display', 'none');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'single_category':
            _this.parent().children(".txt_id").css('display', 'none');
            _this.parent().children(".category").css('display', '');
            break;
        case 'blog_single_category':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'product':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'telegram':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'instagram':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'link':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;

        case 'phone_number':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;

        case 'post':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'lottery':
            _this.parent().children(".txt_id").css('display', '');
            _this.parent().children(".category").css('display', 'none');
            break;
        default:
            break;
    }
}
function list_type_selector(obj) {
    _this = jQuery(obj)
    var selected = _this.val();
    switch (selected) {
        case 'recent':
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'bestseller':
            _this.parent().children(".category").css('display', 'none');
            break;
        case 'special_category':
            _this.parent().children(".category").css('display', '');
            break;
        default:
            break;
    }
}

function show_custom_color_field(obj) {
    _this = jQuery(obj)
    var selected = _this.val();
    if(selected=="custom") {
        _this.parent().children(".custom-text-color").css('display', '');
        _this.parent().children(".custom-bg-color").css('display', '');
    }else {
        _this.parent().children(".custom-text-color").css('display', 'none');
        _this.parent().children(".custom-bg-color").css('display', 'none');
    }
}

//--------------------------------------end slider banner---------------------------------------------------

function appchar_append_gridcard(obj) {
    var row_index3 = jQuery(obj).parent().children('input').val();
    var grid_card_var = grid_card.replace(new RegExp('grid_', 'g'), "grid_"+row_index3+"_");

    switch (jQuery(obj).val()) {
        case "-1":
            jQuery(obj).parent().parent().children(".get-cat").empty();
            break;
        case "full":
            jQuery(obj).parent().parent().children(".get-cat").empty();
            jQuery(obj).parent().parent().children(".get-cat").append(grid_card_var);
            break;
        case "half":
            jQuery(obj).parent().parent().children(".get-cat").empty();
            jQuery(obj).parent().parent().children(".get-cat").append(grid_card_var);
            jQuery(obj).parent().parent().children(".get-cat").append(grid_card_var);
            break;
        case "third":
            jQuery(obj).parent().parent().children(".get-cat").empty();
            jQuery(obj).parent().parent().children(".get-cat").append(grid_card_var);
            jQuery(obj).parent().parent().children(".get-cat").append(grid_card_var);
            jQuery(obj).parent().parent().children(".get-cat").append(grid_card_var);
            break;
        default:
            break;
    }
}
