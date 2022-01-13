<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
      <?php
          //Core media script
          wp_enqueue_media();

          // Your custom js file
          wp_register_script( 'media-lib-uploader-js', plugins_url( 'media-lib-uploader.js' , __FILE__ ), array('jquery') );
          wp_enqueue_script( 'media-lib-uploader-js' );
       ?>
        <!-- main content -->
        <div id="post-body-content">
            <div class="postbox">
                <h3><?php _e('send message', 'appchar'); ?></h3>
                <div class="inside">
                    <?php
                    if($userid){
                        echo '<p>';
                        _e('Your message will be sent to', 'appchar');
                        echo '</p><p>';
                        foreach ($userid as $ui) {
                            echo $ui . '<br>';
                        }
                        echo '</p>';
                    }?>
                    <form method="post" action="#">
                        <p><?php _e('message title:', 'appchar'); ?></p>
                        <input id="message" name="msg-title" type="text" cols="50" rows="5" required>
                        <p><?php _e('insert your message', 'appchar'); ?></p>
                        <textarea id="message" name="message" type="text" cols="50" rows="5" required></textarea>
                        <p><?php _e('* please don\'t use any html code', 'appchar'); ?></p>
                        <?php if(AppcharExtension::extensionIsActive('advance_notification')) : ?>
                          <p><?php _e('Choose Your big picture', 'appchar'); ?></p>
                          <img id="image-url" src="" alt="" height="150" width="150"/>
                          <input type="hidden" id="hidden-image-url" name="big_picture" value=""/>
                          <input id="upload-button" type="button" class="button" value="Upload Image" />
                          <p><?php _e('Enter yout URL', 'appchar'); ?></p>
                          <select name="url_options" id ="url_options" onchange="getSelectValue(this);">
                            <option value="none"><?php _e('none', 'appchar'); ?></option>
                            <!-- <option value="url"><?php _e('URL', 'appchar'); ?></option> -->
                            <option value="product"><?php _e('Product', 'appchar'); ?></option>
                            <option value="post"><?php _e('Post', 'appchar'); ?></option>
                          </select>
                          <input type="hidden" name="url-text" id="strUrl" placeholder=""/>
                          <p><?php _e('Send After State', 'appchar'); ?></p>
                          <input type="checkbox" id="sendAfterState" name="sendAfterState" value="1"/>
                          <input type="radio" name="days" value="Sat"> شنبه
                          <input type="radio" name="days" value="Sun"> یکشنبه
                          <input type="radio" name="days" value="Mon"> دوشنبه
                          <input type="radio" name="days" value="Tue">سه شنبه
                          <input type="radio" name="days" value="Wed" > چهارشنبه
                          <input type="radio" name="days" value="Thu"> پنج شنبه
                          <input type="radio" name="days" value="Fri"> جمعه
                          ساعت : <select name="sendAfterStateDay" id="sendAfterStateDay">
                              <option value="01:00:00 AM">1</option>
                              <option value="02:00:00 AM">2</option>
                              <option value="03:00:00 AM">3</option>
                              <option value="04:00:00 AM">4</option>
                              <option value="05:00:00 AM">5</option>
                              <option value="06:00:00 AM">6</option>
                              <option value="07:00:00 AM">7</option>
                              <option value="08:00:00 AM">8</option>
                              <option value="09:00:00 AM">9</option>
                              <option value="10:00:00 AM">10</option>
                              <option value="11:00:00 AM">11</option>
                              <option value="12:00:00 AM">12</option>
                              <option value="01:00:00 PM">13</option>
                              <option value="02:00:00 PM">14</option>
                              <option value="03:00:00 PM">15</option>
                              <option value="04:00:00 PM">16</option>
                              <option value="05:00:00 PM">17</option>
                              <option value="06:00:00 PM">18</option>
                              <option value="07:00:00 PM">19</option>
                              <option value="08:00:00 PM">20</option>
                              <option value="09:00:00 PM">21</option>
                              <option value="10:00:00 PM">22</option>
                              <option value="11:00:00 PM">23</option>
                              <option value="12:00:00 PM">24</option>
                          </select>
                        <?php endif; ?>
                        <?php submit_button(__('send', 'appchar')); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br class="clear">
</div>
<script>
function getSelectValue(selected) {
  var value = selected.value;
  var otpt = document.getElementById('strUrl');
  switch (value) {
    case 'none':
      otpt.setAttribute('type', 'hidden');
      otpt.setAttribute('placeholder', "");
      break;
    case 'url':
      otpt.setAttribute('type', 'text');
      otpt.setAttribute('placeholder', "Enter URL");
      break;
    case 'product':
      otpt.setAttribute('type', 'text');
      otpt.setAttribute('placeholder', "Enter Product ID");
      break;
    case 'post':
      otpt.setAttribute('type', 'text');
      otpt.setAttribute('placeholder', "Enter Post ID");
      break;
    default:

  }
}


//For radios
var radios =  document.getElementsByName("days");
var selectDay = document.getElementById("sendAfterStateDay");
selectDay.disabled = true;
for (var i = 0; i < radios.length; i++) {
  radios[i].disabled = true;
}
document.getElementById('sendAfterState').onchange = function() {
  selectDay.disabled = !this.checked;
  for (var i = 0; i < radios.length; i++) {
    radios[i].disabled = !this.checked;
  }
};

//Image
jQuery(document).ready(function($){

var mediaUploader;

$('#upload-button').click(function(e) {
  e.preventDefault();
  // If the uploader object has already been created, reopen the dialog
    if (mediaUploader) {
    mediaUploader.open();
    return;
  }
  // Extend the wp.media object
  mediaUploader = wp.media.frames.file_frame = wp.media({
    title: 'Choose Image',
    button: {
    text: 'Choose Image'
  }, multiple: false });

  // When a file is selected, grab the URL and set it as the text field's value
  mediaUploader.on('select', function() {
    attachment = mediaUploader.state().get('selection').first().toJSON();
    $('#image-url').attr('src', attachment.url);
    $('#hidden-image-url').attr('value', attachment.url);
  });
  // Open the uploader dialog
  mediaUploader.open();
});

});
</script>
