<?php
global $wpdb;
$done = 0;
$message = null;
// -- request for saving credentials
if (isset($_GET['delete_banner'])) {
    if(!filter_var($_GET['delete_banner'])){
        $message = __('this category is not valid','appchar');
    }else{
        update_term_meta(trim($_GET['delete_banner']), 'appchar_banners', 1);
    }
}
if (isset($_POST['submit'])) {
    if (!empty($_POST['slide']) && !empty($_POST['category_id']) && $_POST['category_id'] != 0) {
        $slide[] = $_POST['slide'];
        update_term_meta($_POST['category_id'], 'appchar_banners', $slide);
    }
}
?>

<?php if(isset($message)){ echo '<div class="notice notice-error is-dismissible"><p>'. $message .'</p></div>'; } ?>
<div class="wrap">
    <h2><?php _e('category banner', 'appchar'); ?></h2>
    <p><?php _e('The photos will be displayed on the page categories of applications','appchar'); ?></p>
    <ul><li style="color: red;"><?php _e('The standard size for photos should be 640x360 pixel with 10 pixel padding','appchar') ?></li></ul>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-1">
            <div id="post-body-content">
                <form method="post">
                    <div class="cards">

                    <?php
                    $args = array(
                        'hide_empty' => 0,
                    );
                    $product_categories = get_terms('product_cat', $args);
                    foreach ($product_categories as $key => $cat_list) {
                        $image = '';
                        if (get_term_meta($cat_list->term_id, 'appchar_banners')) {
                            if(get_term_meta($cat_list->term_id, 'appchar_banners')!=Array ( 0 => 1 )) {
                                foreach (get_term_meta($cat_list->term_id, 'appchar_banners') as $img_url) {
                                    $image = $img_url;
                                }
                                ?>
                                <div class="card2">
                                    <div class="container center"><p><?php echo $cat_list->name; ?></p></div>
                                        <div class="banners-img">
                                        <img class="thumbnail2" src="<?php echo $image[0]; ?>" alt="Avatar">
                                    </div>
                                    <div class="container center">
                                        <p><a class="button" href="<?php echo add_query_arg(array('delete_banner' => $cat_list->term_id));?>" onclick="return confirm('آیا نسبت به حذف این فایل اطمینان دارید ?')"><?php _e('Delete','appchar'); ?></a></p>
                                    </div>
                                </div>
                                <?php
                                unset($product_categories[$key]);
                            }
                        }
                    }
                        if($product_categories) {
                            ?>
                            <div class="card2">
                                <div class="container center"><?php _e('select banner','appchar'); ?></div>
                                <div class="banners-img">
                                    <a href="#" id="slide_upload" class="um-cover-add um-manual-trigger atag"
                                       style="width:100%;" data-parent=".um-cover" data-child=".um-btn-auto-width"
                                       style="height: 370px;">
                                        <img class="thumbnail2 imgtag" style="display: none;">
                                        <span class="dashicons dashicons-plus-alt banner-icon"></span>
                                    </a>
                                    <input type="text" name="slide" style="display: none;" id="slide" value=""
                                           readonly/>
                                </div>
                                <div class="container center">

                                    <select name="category_id" style="margin: 26px 0;">
                                        <option value="">انتخاب دسته بندی</option>
                                        <?php
                                        foreach ($product_categories as $cat_list) {
                                            echo "<option value=\"$cat_list->term_id\">$cat_list->name</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <?php submit_button();
                        }
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>