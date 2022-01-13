$(document).ready(function() {

    $('.emoji-picker').emojiPicker();

    $('#input-custom-size').emojiPicker({
        width: '300px',
        height: '200px'
    });

    $('#input-left-position').emojiPicker({
        position: 'left'
    });

    $('#create').click(function(e) {
        e.preventDefault();
        $('#text-custom-trigger').emojiPicker({
            width: '300px',
            height: '200px',
            button: false
        });
    });

    $('#toggle').click(function(e) {
        e.preventDefault();
        $('#text-custom-trigger').emojiPicker('toggle');
    });

    $('#destroy').click(function(e) {
        e.preventDefault();
        $('#text-custom-trigger').emojiPicker('destroy');
    })

    // keyup event is fired
    $(".emojiable-question, .emojiable-option").on("keyup", function () {
        //console.log("emoji added, input val() is: " + $(this).val());
    });

});

/*-------------------------admin select appchar post-------------------------------*/
