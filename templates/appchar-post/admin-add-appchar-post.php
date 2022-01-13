<?php

if (isset($_POST['publish'])) {

    $my_post = array(
        'post_title' => trim($_POST["title"]),
        'post_content' => trim($_POST["content"]),
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
        'post_type' => 'appchar_post',
    );
    $post = wp_insert_post($my_post);
    if(isset($_POST["icon-picked"]))
        add_post_meta($post, 'post_material_icon', trim($_POST["icon-picked"]));

    echo '<div class="notice notice-success is-dismissible">
        <p>' . __('your page published', 'appchar') . '</p></div>';
}
if (isset($_POST['update'])) {
    $my_appchar_post = array(
        'ID' => trim($_POST["id"]),
        'post_title' => trim($_POST["title"]),
        'post_content' => trim($_POST["content"])
    );
    wp_update_post($my_appchar_post);
    if(isset($_POST["icon-picked"]))
        update_post_meta(trim($_POST["id"]), 'post_material_icon', trim($_POST["icon-picked"]));
    echo '<div class="notice notice-success is-dismissible">
        <p>' . __('your page updated', 'appchar') . '</p></div>';
}
$title = '';
$content = '';
$icon = '';
$button = '<input type="submit" name="publish" class="button" value="' . __('publish', 'appchar') . '">';
if (isset($_GET['post_id'])) {
    $id = trim($_GET['post_id']);
    $post = get_post($id);
    $title = $post->post_title;
    $content = $post->post_content;
    $button = '<input type="submit" name="update" class="button" value="' . __('update', 'appchar') . '">';
    if(get_post_meta(trim($_GET['post_id'],'post_material_icon'))){
        $posticon = get_post_meta(trim($_GET['post_id'],'post_material_icon'));
    };
    $pickedicon = $posticon["post_material_icon"][0];
}

?>
<form action="" method="post">
    <?php
    if (isset($id)) {
        echo '<input type="hidden" value="' . $id . '" name="id">';
    }
    ?>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-1">
            <div id="post-body-content">
                <div id="titlediv">
                    <div id="titlewrap">
                        <input type="text" name="title" size="30" value="<?php if ($title != '') {
                            echo $title;
                        } ?>" id="title" spellcheck="true" placeholder="<?php _e('title', 'appchar') ?>"
                               autocomplete="off">
                    </div>
                    <div class="inside">
                        <div id="edit-slug-box" class="hide-if-no-js">
                        </div>
                    </div>
                    <div id="postdivrich" class="postarea wp-editor-expand">
                        <?php wp_editor($content, 'content', array('textarea_name' => 'content', 'teeny' => false, 'textarea_rows' => 8, 'media_buttons' => true)); ?>
                        <br/>
 
                        <!--
                        <div class="icon-picker" data-pickerid="gi" data-iconsets='{"mdi":"Pick Genericon"}'>
                            <input type="text" name="icon-picked" value="<?php echo $pickedicon; ?>">
                        </div>
                        <div class="mdi-set icon-set">
                            <ul>
                                <?php
//                                $icon->add_buttons();
                                ?>
                            </ul>
                        </div>
                        <br/>
                        -->
                        <?php echo $button; ?>
                        <br/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>