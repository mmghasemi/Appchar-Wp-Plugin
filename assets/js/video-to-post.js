/**
 * Created by alishojaei on 7/12/17.
 */
function appchar_upload_win(obj) {
    var _this = jQuery(obj);
    custom_uploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Image',
        button: {
            text: 'Choose Video'
        },
        library: {
            type: 'video' // limits the frame to show only images
        },
        multiple: false
    });
    custom_uploader.undelegate().on('select', function () {
        attachment = custom_uploader.state().get('selection').first().toJSON();
        _this.parent().children('.slide').attr('value', attachment.url);
        _this.parent().children('.imgtag').css('display', 'block');
        _this.parent().children(".imgtag").attr('src', attachment.url);
    });
    custom_uploader.open();
}