/**
jQuery(document).ready(function($){


    var custom_uploader;
    var attachment;


    $('.slide_upload').click(function(e) {
        var _this= $(this);
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
        custom_uploader.undelegate().on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            //alert(_this.parent().html());
            _this.parent().children('.slide').attr('value', attachment.url);
            _this.children(".imgtag")[0].style.display = "";
            _this.children(".imgtag").attr('src', attachment.url);
            if(_this.children('.banner-icon').length>0){
                _this.children('.banner-icon')[0].style.display = "none";
            }
            if(_this.parent().children('.clean_image').length > 0) {
                _this.parent().children('.clean_image')[0].style.display = "";
            }
        });
        //Open the uploader dialog
        custom_uploader.open();
    });
    $('.clean_image').click(function (e){
        $(this).parent().children(".slide").attr('value', '');
        $(this).parent().children().children(".imgtag").attr('src', '');
        $(this).parent().children().children(".imgtag")[0].style.display = "none";
        $(this).parent().children().children('.banner-icon')[0].style.display = "";
        $(this)[0].style.display = 'none';
    });

});
*/
/*-------------------------admin receive order-------------------------------*/

function select_lmnt_func(day_key, from_to, select=1) {
    var select_lmnt = '<select name="' + day_key + '_' + from_to + '[]" class="select-time">';
    for (var i = 1; i <= 24; i++) {
        if (select == i) {
            select_lmnt += '<option value="' + i + '" selected>' + i + '</option>';
        } else {
            select_lmnt += '<option value="' + i + '">' + i + '</option>';
        }
    }
    select_lmnt += '</select>';
    return select_lmnt;
}

function remove_select_time_block(_this) {
    jQuery(_this).parent().parent().remove();
}

function add_select_time_block(day_key, _this) {
    var select_block = '<div>' +
        select_lmnt_func(day_key, 'from') +
        ' <?php _e(" to ","appchar") ?> ' +
        select_lmnt_func(day_key, 'to') +
        '<div class="edit-block"><a onclick="remove_select_time_block(this)"><span class="dashicons dashicons-trash"></span></a></div></div>';
    jQuery(_this).parent().children('.time').append(select_block);
}

/*-------------------------admin send order-------------------------------*/


function row_append() {
    $row = $('.category-row').html();
    $row = '<tr>'+$row+'</tr>';
    $('#categories-tbody').append($row);
}
function delete_row(obj) {
    jQuery(obj).parent().parent().remove();
}
