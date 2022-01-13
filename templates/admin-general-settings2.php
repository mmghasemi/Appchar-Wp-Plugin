<?php
// -- request for saving credentials

$options = get_option('appchar_general_setting');
if (isset($_POST['save_general_setting'])) {
    if (isset($_POST['appchar_general_setting']['abd'])) {
        $options['abd'] = trim($_POST['appchar_general_setting']['abd']);
    } else $options['abd'] = 0;

    if (isset($_POST['appchar_general_setting']['debug'])) {
        $options['debug'] = trim($_POST['appchar_general_setting']['debug']);
    } else $options['debug'] = 0;

    update_option('appchar_general_setting',$options);

    if (isset($_POST['update_message'])) {

        update_option('appchar_update_message', trim($_POST['update_message']));
        update_option('appchar_update_link', trim($_POST['update_link']));

    }
    if (isset($_POST['custom_message'])) {
        switch ($_POST['bgcolor']) {
            case 'blue':
                $pinned = '#2ba6cb';
                break;
            case 'green':
                $pinned = '#5da423';
                break;
            case 'yellow':
                $pinned = '#e3b000';
                break;
            case 'red':
                $pinned = '#c60f13';
                break;
            default:
                $pinned = '#2ba6cb';
        }
        update_option('appchar_custom_message', trim($_POST['custom_message']));
        update_option('appchar_custom_message_backgroundcolor', $pinned);
    }
    if (isset($_POST['in-app-payment'])) {
        if ($_POST['in-app-payment'] == 'in-app') {
            update_option('appchar_in_app_payment', true);
        } else {
            update_option('appchar_in_app_payment', false);
        }
    }
    if (isset($_POST['product-display-short-description'])) {
        if ($_POST['product-display-short-description'] == 'enable') {
            update_option('appchar_product_display_short_description', true);
        } else {
            update_option('appchar_product_display_short_description', false);
        }
    }
    if (isset($_POST['custom-tab-count']) && $_POST['custom-tab-count'] > 0) {
        update_option('appchar_custom_tab_count', $_POST['custom-tab-count']);
    } else {
        update_option('appchar_custom_tab_count', 0);
    }

    if (isset($_POST['add-to-cart-button'])) {
        update_option('appchar_add_to_cart_button', $_POST['add-to-cart-button']);
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
            update_option('appchar_add_to_cart_button_'.$lang, $_POST['add-to-cart-button']);
        }
    } else {
        update_option('appchar_add_to_cart_button', __('add to cart', 'appchar'));
    }

    if (isset($_POST['google-analytics-tracking-id'])) {
        update_option('appchar_google_analytics_tracking_id', $_POST['google-analytics-tracking-id']);
    } else {
        update_option('appchar_google_analytics_tracking_id', '');
    }
    if (isset($_POST['appchar-force-login']) && $_POST['appchar-force-login'] == 1) {
        update_option('appchar_force_login', true);
    } else {
        update_option('appchar_force_login', false);
    }
    if (isset($_POST['appchar_user_approve']) && $_POST['appchar_user_approve'] == 1) {
        update_option('appchar_user_approve', true);
    } else {
        update_option('appchar_user_approve', false);
    }

    if (isset($_POST['appchar-catalog-mode']) && $_POST['appchar-catalog-mode'] == 1) {
        update_option('appchar_catalog_mode', true);
    } else {
        update_option('appchar_catalog_mode', false);
    }
    if (isset($_POST['appchar-blog-title'])) {
        update_option('appchar_blog_title', trim($_POST['appchar-blog-title']));
    } else {
        update_option('appchar_blog_title', '');
    }
    if (isset($_POST['appchar-lottery-title'])) {
        update_option('appchar_lottery_title', trim($_POST['appchar-lottery-title']));
    } else {
        update_option('appchar_lottery_title', '');
    }


    if (isset($_POST['appchar_bestseller_product_is_visible']) && $_POST['appchar_bestseller_product_is_visible'] == 1) {
        update_option('appchar_bestseller_product_is_visible', true);
    } else {
        update_option('appchar_bestseller_product_is_visible', false);
    }

    if (isset($_POST['appchar_recent_product_is_visible']) && $_POST['appchar_recent_product_is_visible'] == 1) {
        update_option('appchar_recent_product_is_visible', true);
    } else {
        update_option('appchar_recent_product_is_visible', false);
    }

    if (isset($_POST['appchar_product_rate_is_visible']) && $_POST['appchar_product_rate_is_visible'] == 1) {
        update_option('appchar_product_rate_is_visible', true);
    } else {
        update_option('appchar_product_rate_is_visible', false);
    }
    if (AppcharExtension::extensionIsActive('easy_shopping_cart')){
        if (isset($_POST['appchar_toggle_product_to_cart']) && $_POST['appchar_toggle_product_to_cart'] == 1) {
            update_option('appchar_toggle_product_to_cart', true);
        } else {
            update_option('appchar_toggle_product_to_cart', false);
        }
        // NOTE By Iman Mokhtari Aski on 11/12/2019
        if (isset($_POST['appchar_unit_price_calculator']) && $_POST['appchar_unit_price_calculator'] == 1) {
            update_option('appchar_unit_price_calculator', true);
        } else {
            update_option('appchar_unit_price_calculator', false);
        }

        if (isset($_POST['appchar_sync_cart_to_site']) && $_POST['appchar_sync_cart_to_site'] == 1) {
            update_option('appchar_sync_cart_to_site', true);
        } else {
            update_option('appchar_sync_cart_to_site', false);
        }
    }


}

?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('general setting', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form method="post">
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th scope="row"><?php _e('View in the menu bar at the top','appchar') ?></th>
                                    <td><input type="checkbox" id="abd" name="appchar_general_setting[abd]" value="1" <?php if($options['abd']==1){echo 'checked';} ?>></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Output send notifications','appchar')?></th>
                                    <td><input type="checkbox" id="debug" name="appchar_general_setting[debug]" value="1" <?php if($options['debug']==1){echo 'checked';} ?>></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('insert your update message','appchar')?></th>
                                    <td><textarea id="debug" name="update_message"><?php echo get_option('appchar_update_message','');?></textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('insert your update app link','appchar')?></th>
                                    <td><textarea id="debug" name="update_link"><?php echo get_option('appchar_update_link','');?></textarea></td>

                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('insert your custom message','appchar')?></th>
                                    <td><textarea id="debug" name="custom_message"><?php echo get_option('appchar_custom_message',''); ?></textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Choose the background color of your message','appchar')?></th>
                                    <td>
                                        <div class="radio bgcolor" style="background-color: #2ba6cb"><input type="radio" name="bgcolor" value="blue" <?php if(get_option('appchar_custom_message_backgroundcolor','2ba6cb')=='#2ba6cb'){echo 'checked';} ?>><?php _e('blue(info)','appchar') ?></div>
                                        <div class="radio bgcolor" style="background-color: #5da423"><input type="radio" name="bgcolor" value="green" <?php if(get_option('appchar_custom_message_backgroundcolor','2ba6cb')=='#5da423'){echo 'checked';} ?>><?php _e('green(success)','appchar') ?></div>
                                        <div class="radio bgcolor" style="background-color: #e3b000"><input type="radio" name="bgcolor" value="yellow" <?php if(get_option('appchar_custom_message_backgroundcolor','2ba6cb')=='#e3b000'){echo 'checked';} ?>><?php _e('yellow(warning)','appchar') ?></div>
                                        <div class="radio bgcolor" style="background-color: #c60f13"><input type="radio" name="bgcolor" value="red" <?php if(get_option('appchar_custom_message_backgroundcolor','2ba6cb')=='#c60f13'){echo 'checked';} ?>><?php _e('red(alert)','appchar') ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('payments:','appchar')?></th>
                                    <td><div class="radio"><input type="radio" name="in-app-payment" value="out-app" <?php if(!get_option('appchar_in_app_payment',false)){echo 'checked';}?>><?php _e('out-app','appchar') ?></div><div class="radio"><input type="radio" name="in-app-payment" value="in-app" <?php if(get_option('appchar_in_app_payment',false)){echo 'checked';}?>><?php _e('in-app','appchar') ?></div>(نسخه آزمایشی)</td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('short description for products:','appchar')?></th>
                                    <td>
                                        <div class="radio"><input type="radio" name="product-display-short-description" value="enable" <?php if(get_option('appchar_product_display_short_description',false)){echo 'checked';}?>><?php _e('enable','appchar') ?></div>
                                        <div class="radio"><input type="radio" name="product-display-short-description" value="disable" <?php if(!get_option('appchar_product_display_short_description',false)){echo 'checked';}?>><?php _e('disable','appchar') ?></div></td>
                                </tr>
                                <?php
                                if(AppcharExtension::extensionIsActive('custom_tab')):
                                ?>
                                <tr>
                                    <th scope="row"><?php _e('The number of custom tabs:','appchar')?></th>
                                    <td>
                                        <div class=""><input type="number" name="custom-tab-count" value="<?php if(get_option('appchar_custom_tab_count',false)){echo get_option('appchar_custom_tab_count',0);}?>"></div>
                                    </td>
                                </tr>
                                <?php
                                endif;
                                ?>
                                <tr>
                                    <th scope="row"><?php _e('change add to cart button text','appchar')?>(آزمایشی)</th>
                                    <td>
                                        <?php
                                        $add_to_cart_button = get_option('appchar_add_to_cart_button',__('add to cart','appchar'));
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
                                            $add_to_cart_button = get_option('appchar_add_to_cart_button_'.$lang,__('add to cart','appchar'));
                                        }
                                        ?>
                                        <div class=""><input type="text" name="add-to-cart-button" value="<?php echo $add_to_cart_button;?>"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('google analytics tracking id','appchar')?>(آزمایشی)</th>
                                    <td>

                                        <div class=""><input type="text" name="google-analytics-tracking-id" value="<?php echo get_option('appchar_google_analytics_tracking_id','');?>"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('bestseller products is visible','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar_bestseller_product_is_visible" value="1" <?php if(get_option('appchar_bestseller_product_is_visible',true)){echo 'checked';} ?>></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('recent products is visible','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar_recent_product_is_visible" value="1" <?php if(get_option('appchar_recent_product_is_visible',true)){echo 'checked';} ?>></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('product rate is visible','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar_product_rate_is_visible" value="1" <?php if(get_option('appchar_product_rate_is_visible',true)){echo 'checked';} ?>></td>
                                </tr>
                                <?php
                                if(AppcharExtension::extensionIsActive('force_login')):
                                ?>
                                <tr>
                                    <th scope="row"><?php _e('force login','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar-force-login" value="1" <?php if(get_option('appchar_force_login',false)){echo 'checked';} ?>></td>
                                </tr>
                                <?php
                                endif;
                                if(AppcharExtension::extensionIsActive('user_approve')):
                                ?>
                                <tr>
                                    <th scope="row"><?php _e('User Approve','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar_user_approve" value="1" <?php if(get_option('appchar_user_approve',false)){echo 'checked';} ?>></td>
                                </tr>
                                <?php
                                endif;
                                if(AppcharExtension::extensionIsActive('catalog_mode')):
                                ?>
                                <tr>
                                    <th scope="row"><?php _e('catalog mode','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar-catalog-mode" value="1" <?php if(get_option('appchar_catalog_mode',false)){echo 'checked';} ?>></td>
                                </tr>
                                <?php
                                endif;
                                if(AppcharExtension::extensionIsActive('easy_shopping_cart')):
                                ?>
                                <tr>
                                    <th scope="row"><?php _e('toggle product to cart','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar_toggle_product_to_cart" value="1" <?php if(get_option('appchar_toggle_product_to_cart',false)){echo 'checked';} ?>></td>
                                </tr>
                                <!-- NOTE By Iman Mokhtari Aski on 11/12/2019 --->
                                <tr>
                                    <th scope="row"><?php _e('Custom Weight Sale','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar_unit_price_calculator" value="1" <?php if(get_option('appchar_unit_price_calculator',false)){echo 'checked';} ?>></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('sync cart in app with site','appchar') ?></th>
                                    <td><input type="checkbox" name="appchar_sync_cart_to_site" value="1" <?php if(get_option('appchar_sync_cart_to_site',false)){echo 'checked';} ?>></td>
                                </tr>
                                <?php
                                endif;
                                if(AppcharExtension::extensionIsActive('blog')):
                                    ?>
                                    <tr>
                                        <th scope="row"><?php _e('blog title','appchar') ?></th>
                                        <td><input type="text" name="appchar-blog-title" value="<?php echo get_option('appchar_blog_title','');?>"></td>
                                    </tr>
                                    <?php
                                endif;
                                if(AppcharExtension::extensionIsActive('lottery')):
                                    ?>
                                    <tr>
                                        <th scope="row"><?php _e('lottery title','appchar') ?></th>
                                        <td><input type="text" name="appchar-lottery-title" value="<?php echo get_option('appchar_lottery_title','');?>"></td>
                                    </tr>
                                    <?php
                                endif;
                                ?>
                                </tbody>
                            </table>

                            <?php submit_button('','','save_general_setting'); ?>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
