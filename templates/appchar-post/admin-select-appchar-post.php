<?php
global $wpdb;
$done = 0;
$message = null;
if (isset($_POST['save_address'])) {

    for ($i = 0; $i < 10; $i++) {
        if(AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish') && defined('ICL_LANGUAGE_CODE')) {
            if(defined('ICL_LANGUAGE_CODE')) {
                $lang = ICL_LANGUAGE_CODE;
            }else{
                $lang = 'fa';
            }
            if (!empty($_POST['page_' . $i . '_page_id'])) {
                $title = !empty($_POST['page_' . $i . '_title']) ? $_POST['page_' . $i . '_title'] : get_post($_POST['page_' . $i . '_page_id'])->post_title;

                if (count($wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "appchar_pages where position = $i and language = \"$lang\""))) {
                    $wpdb->update($wpdb->prefix . 'appchar_pages', array('page_id' => $_POST['page_' . $i . '_page_id'], 'title' => $title), array('position' => $i,'language'=>$lang));
                } else {
                    $wpdb->insert($wpdb->prefix . 'appchar_pages', array('page_id' => $_POST['page_' . $i . '_page_id'], 'position' => $i, 'title' => $title,'language' => $lang));
                }
            } else {
                $wpdb->delete($wpdb->prefix . 'appchar_pages', array('position' => $i,'language' => $lang),array('%d','%s'));
            }
        }else{
            if (!empty($_POST['page_' . $i . '_page_id'])) {

                $title = !empty($_POST['page_' . $i . '_title']) ? $_POST['page_' . $i . '_title'] : get_post($_POST['page_' . $i . '_page_id'])->post_title;

                if (count($wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "appchar_pages where position = $i"))) {
                    $wpdb->update($wpdb->prefix . 'appchar_pages', array('page_id' => $_POST['page_' . $i . '_page_id'], 'title' => $title,'language'=>'fa'), array('position' => $i));
                } else {
                    $wpdb->insert($wpdb->prefix . 'appchar_pages', array('page_id' => $_POST['page_' . $i . '_page_id'], 'position' => $i, 'title' => $title,'language'=>'fa'));
                }

            } else {
                $wpdb->delete($wpdb->prefix . 'appchar_pages', array('position' => $i));
            }
        }
    }
}

isset($message) ? nl2br($message) : null ?>



<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div class="postbox">
                <h3 class="title"><?php _e('page address', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form action="" method="post" enctype="multipart/form-data">
                            <table class="wp-list-table widefat fixed striped posts">
                                <tbody>
                                <?php
                                for ($i = 0; $i < 10; $i++) {
                                    if(AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish') && defined('ICL_LANGUAGE_CODE')) {
                                        if(defined('ICL_LANGUAGE_CODE')) {
                                            $lang = ICL_LANGUAGE_CODE;
                                        }else{
                                            $lang = 'fa';
                                        }
                                        $page_results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "appchar_pages where position = $i and language = \"$lang\"");
                                    }else{
                                        $page_results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "appchar_pages where position = $i");
                                    }
                                    $page_title = $page_results && count($page_results) > 0 ? $page_results[0]->title : "" ?>
                                    <tr>
                                        <td>
                                            <?php
                                            _e(' page', 'appchar'); echo str_repeat("&nbsp;", 1);echo $i
                                            ?>
                                        </td>
                                        <td>
                                            <input type="hidden" id="pageTitle<?php echo $i; ?>" value="<?php echo $page_title; ?>">
                                            <input type="hidden" name="page_<?php echo $i ?>_title" id="finalTitle<?php echo $i; ?>" value="">
                                            <input class="emojiable-option emoji-picker" style="height: 30px; text-align: left; width: 70px"
                                                   id="emojiText_<?php echo $i; ?>"
                                                   value=""
                                                   onkeyup="updateFinalTitle(this.id);">
                                            <input title="<?php _e('page title', 'appchar') ?>" type="text"
                                                   placeholder="<?php _e('title', 'appchar') ?>"
                                                   style="height: 30px; text-align: left; width: 150px"
                                                   id="titleText_<?php echo $i; ?>"
                                                   value=""
                                                   onkeyup="updateFinalTitle(this.id);">

                                        </td>
                                        <td>
                                            <!-- لطفا بعد از استفاده از ایموجی ۵ فاصله بگذارید تا با ایکونهای داخل اپ هماهنگ گردد -->
                                            <input type="text" value="<?php echo $page_title; ?>" disabled>
                                        </td>
                                        <td>
                                            <select name="page_<?php echo $i ?>_page_id">
                                                <option
                                                    value=""><?php echo esc_attr(__('-- select page --', 'appchar')); ?></option>
                                                <?php
                                                $pages = get_posts(array('post_type' => 'page', 'numberposts'      => -1));
                                                foreach ($pages as $page) {
                                                    if ($page_results && count($page_results) && $page_results[0]->page_id == $page->ID) {
                                                        $option = '<option value="' . $page->ID . '" selected>';
                                                    } else {
                                                        $option = '<option value="' . $page->ID . '" >';
                                                    }
                                                    $option .= $page->post_title;
                                                    $option .= '</option>';
                                                    echo $option;
                                                }
                                                ?>

                                            </select>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>

                            <br/>
                            <input type="submit" onclick="finalSubmit()" name='save_address' class="button"
                                   value="<?php echo __('update', 'appchar') ?>">
                            <br/>


                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
for (var i = 0; i < 10; i++) {
  var source = document.getElementById('pageTitle'+i).value;
  var emojiInit = document.getElementById('emojiText_'+i);
  var textInit = document.getElementById('titleText_'+i);
  var hid = document.getElementById('finalTitle'+i);
  if(source.split('     ').length < 2) {
    var pattern = /\u00a9|\u00ae|[\u2000-\u3300]|\ud83c[\ud000-\udfff]|\ud83d[\ud000-\udfff]|\ud83e[\ud000-\udfff]/;
    var result = source.match(pattern);
    if(result != null) {
      emojiInit.value = result[0].trim();
      hid.value = emojiInit.value;
      console.log('1');
    }else {
      textInit.value = source.split('     ')[0].trim();
      hid.value = textInit.value;
      console.log('2');
    }
  }
  if(source.split('     ').length > 1) {
    emojiInit.value = source.split('     ')[0].trim();
    textInit.value = source.split('     ')[1].trim();
    hid.value = emojiInit.value + "\xa0\xa0\xa0\xa0\xa0" + textInit.value;
    console.log('3');
  }
}

</script>
<script>
function updateFinalTitle(i) {
    var number = i.split("_")[1];
    var emoji = document.getElementById('emojiText_'+number);
    var text = document.getElementById('titleText_'+number);
    var hid = document.getElementById('finalTitle'+number);
    var temp = emoji.value + "\xa0\xa0\xa0\xa0\xa0" + text.value;
    hid.value = temp.trim();
    // console.log(message);
}
</script>
