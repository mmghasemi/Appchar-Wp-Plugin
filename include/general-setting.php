<?php
function appchar_setting()
{
    if (isset($_GET['tab'])) {
        $current_tab = $_GET['tab'];
    } else {
        $current_tab = 'general-settings';
    }
    $tabs = array('general-settings' => __('general settings', 'appchar'), 'notification-settings' => __('notification settings', 'appchar'));
    ?>
    <div class="wrap">

        <h2><?php _e('setting', 'appchar'); ?></h2>
        <h2 class="nav-tab-wrapper">

            <?php
            foreach ($tabs as $tab => $title):

                $class = ($tab == $current_tab) ? ' nav-tab-active' : '';

                echo "<a class='nav-tab $class' href='?page=appchar-setting&tab=$tab'>$title</a>";

            endforeach;
            ?>
        </h2>
        <?php
        switch ($current_tab) {
            case 'general-settings':
                require_tmp('admin-general-settings');
                break;
            case 'notification-settings':
                require_tmp('admin-notification-settings');
                break;
        }
        ?>
    </div>
    <?php
}

function display_theme_panel_fields()
{
    add_settings_section("appchar_general_section", __('General Settings', 'appchar'), null, "appchar-setting");
    add_settings_field("categories_display_type", __('Type of display categories in Categories Page:', 'appchar'), "categories_display_type_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("percentage_discount_view_type", __('View type of percentage discount:', 'appchar'), "percentage_discount_view_type_callback", "appchar-setting", "appchar_general_section");
//    add_settings_field("product_list_display_type", __('Type of display products:', 'appchar'), "product_list_display_type_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("in_app_payment", __('payments:', 'appchar'), "in_app_payment_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("product_display_short_description", __('short description for products:', 'appchar'), "product_display_short_description_callback", "appchar-setting", "appchar_general_section");
    if (AppcharExtension::extensionIsActive('google_analytics')) {
        add_settings_field("google_analytics_tracking_id", __('google analytics tracking id', 'appchar'), "google_analytics_tracking_id_callback", "appchar-setting", "appchar_general_section");
    }
    add_settings_field("optional_page_builder_verification", __('برای جلوگیری از اعتبار سنجی المان های صفحه ساز فعال کنید', 'appchar'), "optional_page_builder_verification_callback", "appchar-setting", "appchar_general_section");


    add_settings_field("region_cities_source_option", __('Choose the cities source', 'appchar'), "region_cities_source_option_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("add_to_cart_button", __('change add to cart button text', 'appchar'), "add_to_cart_button_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("featured_label_text", __('change featured label text', 'appchar'), "featured_label_text_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("hide_signup_page", __('Hide signup page', 'appchar'), "hide_signup_page_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("hide_user_info_in_drawer", __('Hide User Info In Drawer', 'appchar'), "hide_user_info_in_drawer_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("custom_product_addon", __('Custom Product Addon', 'appchar'), "custom_product_addon_callback", "appchar-setting", "appchar_general_section");
    add_settings_field("open_child_page_for_single_category_homepage_builder", __('open child categories page for single category in homepage builder', 'appchar'), "open_child_page_for_single_category_homepage_builder_callback", "appchar-setting", "appchar_general_section");


    add_settings_section("appchar_visibility_section", '<hr>' . __("Visibility Setting", 'appchar'), null, "appchar-setting");
    add_settings_field("bestseller_product_is_visible", __('bestseller products is visible', 'appchar'), "bestseller_product_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("recent_product_is_visible", __('recent products is visible', 'appchar'), "recent_product_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("product_rate_is_visible", __('product rate is visible', 'appchar'), "product_rate_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("search_is_visible", __('search is visible', 'appchar'), "search_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("sort_is_visible", __('sort is visible', 'appchar'), "sort_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("out_stock_products_end", __('Show out of stock products at the end', 'appchar'), "out_stock_products_end_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("categories_is_visible", __('Categories is visible', 'appchar'), "categories_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("products_is_visible", __('Products is visible', 'appchar'), "products_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("hamburger_menu_is_visible", __('Hamburger menu is visible', 'appchar'), "hamburger_menu_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("profile_is_visible", __('Profile is visible', 'appchar'), "profile_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("blog_categories_is_visible", __('Blog categories is visible', 'appchar'), "blog_categories_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("hide_product_images_in_shopping_cart", __('Hide Product Images in Shopping cart', 'appchar'), "hide_product_images_in_shopping_cart_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("sidebar_profile_is_visible", __('Sidebar profile is visible', 'appchar'), "sidebar_profile_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("sidebar_favorite_is_visible", __('Sidebar favorite is visible', 'appchar'), "sidebar_favorite_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("related_products_is_visible", __('Related products is visible', 'appchar'), "related_products_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("material_shape_type", __('Material Shape Type', 'appchar'), "material_shape_type_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("material_shape_value", __('Material Shape Value', 'appchar'), "material_shape_value_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("material_shape_position", __('Material Shape Position', 'appchar'), "material_shape_position_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("bottom_navigation", __('Bottom Navigation', 'appchar'), "bottom_navigation_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("price_ranger", __('Variation Products Price', 'appchar'), "price_ranger_callback", "appchar-setting", "appchar_general_section");

    add_settings_field("add_logo_image", __('Add logo image instead of your app text', 'appchar'), "add_logo_image_callback", "appchar-setting", "appchar_visibility_section");
    add_settings_field("add_order_status_icon", __('Add gif for order status', 'appchar'), "add_order_status_icon_callback", "appchar-setting", "appchar_visibility_section");

    add_settings_section("appchar_pinned_section", '<hr>' . __("Pinned Message Setting", 'appchar'), null, "appchar-setting");
    add_settings_field("update_message", __('insert your update message', 'appchar'), "update_message_callback", "appchar-setting", "appchar_pinned_section");
    add_settings_field("update_link", __('insert your update app link', 'appchar'), "update_link_callback", "appchar-setting", "appchar_pinned_section");
    add_settings_field("force_update", __('Enable Force Update', 'appchar'), "force_update_callback", "appchar-setting", "appchar_pinned_section");
    add_settings_field("custom_message", __('insert your custom message', 'appchar'), "custom_message_callback", "appchar-setting", "appchar_pinned_section");
    add_settings_field("pinned_bgcolor", __('Choose the background color of your message', 'appchar'), "pinned_bgcolor_callback", "appchar-setting", "appchar_pinned_section");

    add_settings_section("appchar_extension_section", '<hr>' . __('Extensions setting', 'appchar'), null, "appchar-setting");
    if (AppcharExtension::extensionIsActive('custom_tab')) {
        add_settings_field("custom_tab_count", __('The number of custom tabs:', 'appchar'), "custom_tab_count_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('address_seller')) {
        add_settings_field("cedarmaps_access_token", __('Enter your cedarmaps access token:', 'appchar'), "cedarmaps_access_token_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('easy_shopping_cart')) {
        add_settings_section("appchar_easy_shopping_cart_section", '<hr>' . __("Easy Shopping Cart Setting", 'appchar'), null, "appchar-setting");
        add_settings_field("cart_button_type", __('Cart button type', 'appchar'), "cart_button_type_callback", "appchar-setting", "appchar_easy_shopping_cart_section");
        add_settings_field("toggle_product_to_cart", __('toggle product to cart', 'appchar'), "toggle_product_to_cart_callback", "appchar-setting", "appchar_easy_shopping_cart_section");
        // NOTE By Iman Mokhtari Aski on 11/12/2019
        add_settings_field("unit_price_calculator", __('unit price calculator', 'appchar'), "unit_price_calculator_callback", "appchar-setting", "appchar_easy_shopping_cart_section");
        add_settings_field("sync_cart_to_site", __('sync cart in app with site', 'appchar'), "sync_cart_to_site_callback", "appchar-setting", "appchar_easy_shopping_cart_section");
        add_settings_field("call_to_price_button", __('change call to price button text', 'appchar'), "call_to_price_button_callback", "appchar-setting", "appchar_easy_shopping_cart_section");
    }
    if (AppcharExtension::extensionIsActive('force_login')) {
        add_settings_field("force_login", __('force login', 'appchar'), "force_login_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('user_approve')) {
        add_settings_field("user_approve", __('User Approve', 'appchar'), "user_approve_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('catalog_mode')) {
        add_settings_field("catalog_mode", __('catalog mode', 'appchar'), "catalog_mode_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('blog')) {
        add_settings_field("blog_title", __('blog title', 'appchar'), "blog_title_callback", "appchar-setting", "appchar_extension_section");
        add_settings_field("blog_type", __('blog type', 'appchar'), "blog_type_callback", "appchar-setting", "appchar_extension_section");
        add_settings_field("sidebar_blog_is_visible", __('Sidebar blog is visible', 'appchar'), "sidebar_blog_is_visible_callback", "appchar-setting", "appchar_visibility_section");
    }
    if (AppcharExtension::extensionIsActive('lottery')) {
        add_settings_field("lottery_title", __('lottery title', 'appchar'), "lottery_title_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('edit_checkout_fields')) {
        add_settings_field("city_type", __('city type', 'appchar'), "city_type_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('hierarchical_filter')) {
        add_settings_field("appchar_filter_custom_label", __('appchar filter custom label', 'appchar'), "appchar_filter_custom_label_callback", "appchar-setting", "appchar_extension_section");
    }
    if (AppcharExtension::extensionIsActive('appstore_distribution')) {
        add_settings_field("appstore_distribution_enable", __('appstore distribution enable', 'appchar'), "appstore_distribution_enable_callback", "appchar-setting", "appchar_extension_section");
        add_settings_field("appstore_distribution_allowed_countries", __('ios allowed countries', 'appchar'), "appstore_distribution_allowed_countries_callback", "appchar-setting", "appchar_extension_section");
        add_settings_field("appstore_distribution_alternative_url", __('Alternative base url', 'appchar'), "appstore_distribution_alternative_url_callback", "appchar-setting", "appchar_extension_section");
    }
    register_setting("appchar_general_section"   , "appchar_general_setting", "appchar_general_settings_validate");
    register_setting("appchar_visibility_section", "appchar_general_setting", "appchar_general_settings_validate");
    register_setting("appchar_pinned_section"    , "appchar_general_setting", "appchar_general_settings_validate");
    register_setting("appchar_easy_shopping_cart_section"    , "appchar_general_setting", "appchar_general_settings_validate");
    register_setting("appchar_extension_section" , "appchar_general_setting", "appchar_general_settings_validate");
}

add_action("admin_init", "display_theme_panel_fields");

function appstore_distribution_enable_callback()
{
    $options = get_option('appchar_general_setting');
    $appstore_distribution_enable = (isset($options['appstore_distribution_enable'])) ? $options['appstore_distribution_enable'] : false;
    $check = ($appstore_distribution_enable) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[appstore_distribution_enable]" value="1" ' . $check . '>';
}
function appstore_distribution_alternative_url_callback()
{
    $options = get_option('appchar_general_setting');
    $appstore_distribution_alternative_url = (isset($options['appstore_distribution_alternative_url'])) ? $options['appstore_distribution_alternative_url'] : '';
    echo '<input name="appchar_general_setting[appstore_distribution_alternative_url]" value="'.$appstore_distribution_alternative_url.'">';
}
function appstore_distribution_allowed_countries_callback()
{
    $options = get_option('appchar_general_setting');
    $appstore_distribution_allowed_countries = (isset($options['appstore_distribution_allowed_countries'])) ? $options['appstore_distribution_allowed_countries'] : [];
    echo '<select multiple name="appchar_general_setting[appstore_distribution_allowed_countries][]">';
    $all_countries = new WC_Countries();
    foreach ($all_countries->get_countries() as $key=>$country){
        $selected = "";
        if(in_array($key,$appstore_distribution_allowed_countries)){
            $selected = "selected";
        }
        echo "<option value='$key' $selected>$country</option>";
    }
    echo '</select>';
}
function categories_display_type_callback()
{
    $options = get_option('appchar_general_setting');
    $options['categories_display_type'] = (isset($options['categories_display_type'])) ? $options['categories_display_type'] : get_option('categories_page_display_type', '');
    ?>
    <select name="appchar_general_setting[categories_display_type]">

        <?php
        $cats_type = array('v1' => __('One-level Categories', 'appchar'), 'v2' => __('Two-level Categories', 'appchar'), 'v3' => __('One-level Categories with tab-bar subcategory', 'appchar'));
        foreach ($cats_type as $key => $cat_type) {
            if ($options['categories_display_type'] == $key) {
                echo '<option value="' . $key . '" selected>' . $cat_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $cat_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
    if ($options['categories_display_type'] == 'v2')
        echo '<p style="color: red;">' . __('در این حالت تمام دسته بندی هایی تک سطحی نمایش داده نمی شود', 'appchar') . '</p>';
}
function percentage_discount_view_type_callback()
{
    $options = get_option('appchar_general_setting');
    $options['percentage_discount_view_type'] = (isset($options['percentage_discount_view_type'])) ? $options['percentage_discount_view_type'] : '';
    ?>
    <select name="appchar_general_setting[percentage_discount_view_type]">

        <?php
        $pd_types = array('hide' => __('Hide percentage discount', 'appchar'), 'v1' => __('Display percentage discount as a circle on product photo', 'appchar'), 'v2' => __('Display discount percentage as a red ribbon under the product photo', 'appchar'));
        foreach ($pd_types as $key => $pd_type) {
            if ($options['percentage_discount_view_type'] == $key) {
                echo '<option value="' . $key . '" selected>' . $pd_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $pd_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
}
function product_list_display_type_callback()
{
    $options = get_option('appchar_general_setting');
    $options['product_list_display_type'] = (isset($options['product_list_display_type'])) ? $options['product_list_display_type'] : get_option('product_list_display_type', '');
    ?>
    <select name="appchar_general_setting[product_list_display_type]">

        <?php
        $cats_type = array('two-col' => __('Two Columns', 'appchar'),'one-col' => __('One Column', 'appchar'));
        foreach ($cats_type as $key => $cat_type) {
            if ($options['product_list_display_type'] == $key) {
                echo '<option value="' . $key . '" selected>' . $cat_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $cat_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
}

function in_app_payment_callback()
{
    $options = get_option('appchar_general_setting');
    $options['in_app_payment'] = (isset($options['in_app_payment'])) ? $options['in_app_payment'] : get_option('appchar_in_app_payment', false);

    ?>
    <div class="radio"><input type="radio" name="appchar_general_setting[in_app_payment]"
                              value="0" <?php checked(false, $options['in_app_payment'], true); ?>><?php _e('External payment', 'appchar') ?>
    </div>
    <div class="radio"><input type="radio" name="appchar_general_setting[in_app_payment]"
                              value="1" <?php checked(true, $options['in_app_payment'], true); ?>><?php _e('In-app payment', 'appchar') ?>
    </div>
    <?php
}

function product_display_short_description_callback()
{
    $options = get_option('appchar_general_setting');
    $options['product_display_short_description'] = (isset($options['product_display_short_description'])) ? $options['product_display_short_description'] : get_option('appchar_product_display_short_description', false);
    ?>
    <div class="radio"><input type="radio" name="appchar_general_setting[product_display_short_description]"
                              value="1" <?php checked(true, $options['product_display_short_description'], true); ?>><?php _e('Enable', 'appchar') ?>
    </div>
    <div class="radio"><input type="radio" name="appchar_general_setting[product_display_short_description]"
                              value="0" <?php checked(false, $options['product_display_short_description'], true); ?>><?php _e('Disable', 'appchar') ?>
    </div>
    <?php
}

function custom_tab_count_callback()
{
    $options = get_option('appchar_general_setting');
    $options['custom_tab_count'] = (isset($options['custom_tab_count'])) ? $options['custom_tab_count'] : get_option('appchar_custom_tab_count', '');
    echo '<input type="number" min="0" name="appchar_general_setting[custom_tab_count]" id="custom_tab_count" value="' . $options['custom_tab_count'] . '">';
}

function cedarmaps_access_token_callback(){
    $options = get_option('appchar_general_setting');
    $cedarmaps_access_token = (isset($options['cedarmaps_access_token'])) ? $options['cedarmaps_access_token'] : "";
    echo '<input type="text" name="appchar_general_setting[cedarmaps_access_token]" value="' . $cedarmaps_access_token . '">';
    echo "<br>".__("you can get this token from <a href='http://cedarmaps.com/'>http://cedarmaps.com</a>","appchar");
}

function google_analytics_tracking_id_callback()
{
    $options = get_option('appchar_general_setting');
    $google_analytics_tracking_id = (isset($options['google_analytics_tracking_id'])) ? $options['google_analytics_tracking_id'] : get_option('appchar_google_analytics_tracking_id', '');

    echo '<input type="text" name="appchar_general_setting[google_analytics_tracking_id]" value="' . $google_analytics_tracking_id . '">';
}

function add_to_cart_button_callback()
{
    $options = get_option('appchar_general_setting');
    $add_to_cart_button = (isset($options['add_to_cart_button'])) ? $options['add_to_cart_button'] : get_option('appchar_add_to_cart_button', __('add to cart', 'appchar'));
    if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } else {
            if (defined('ICL_LANGUAGE_CODE')) {
                $lang = ICL_LANGUAGE_CODE;
            } else {
                $lang = 'fa';
            }
        }
        $add_to_cart_button = (isset($options['add_to_cart_button_' . $lang])) ? $options['add_to_cart_button_' . $lang] : get_option('appchar_add_to_cart_button_' . $lang, __('add to cart', 'appchar'));
    }
    echo '<input type="text" name="appchar_general_setting[add_to_cart_button]" value="' . $add_to_cart_button . '">';
}

function region_cities_source_option_callback()
{
    $options = get_option('appchar_general_setting');
    $options['region_cities_source_option'] = (isset($options['region_cities_source_option'])) ? $options['region_cities_source_option'] : get_option('appchar_region_cities_source_option', false);
    ?>
    <div class="radio"><input type="radio" name="appchar_general_setting[region_cities_source_option]"
                              value="0" <?php checked('woocommerce', $options['region_cities_source_option'], true); ?>><?php _e('Woocommerce', 'appchar') ?>
    </div>
    </br>
    <div class="radio"><input type="radio" name="appchar_general_setting[region_cities_source_option]"
                              value="2" <?php checked('persian-woocommerce-shipping', $options['region_cities_source_option'], true); ?>><?php _e('Persian Woocommerce Shipping', 'appchar') ?>
    </div>
    </br>
    <?php
}
function call_to_price_button_callback()
{
    $options = get_option('appchar_general_setting');
    $call_to_price_button = (isset($options['call_to_price_button'])) ? $options['call_to_price_button'] : __('Call To Price!', 'appchar');
    if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } else {
            if (defined('ICL_LANGUAGE_CODE')) {
                $lang = ICL_LANGUAGE_CODE;
            } else {
                $lang = 'fa';
            }
        }
        $call_to_price_button = (isset($options['call_to_price_button_' . $lang])) ? $options['call_to_price_button_' . $lang] :  __('Call To Price!', 'appchar');
    }
    $call_to_price_status = (isset($options['call_to_price_status'])) ? $options['call_to_price_status'] : false;
    $check = ($call_to_price_status) ? 'checked' : '';
    echo '<div><input type="checkbox" name="appchar_general_setting[call_to_price_status]" value="enable" '.$check.' ><label>'.__('enable call to price','appchar').'</label><span style="width: 20px;height: 20px;display: inline-block;"></span> <input type="text" name="appchar_general_setting[call_to_price_button]" value="' . $call_to_price_button . '"></div>';
}

function featured_label_text_callback()
{
    $options = get_option('appchar_general_setting');
    $featured_label_text = (isset($options['featured_label_text'])) ? $options['featured_label_text']:'';
    if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } else {
            if (defined('ICL_LANGUAGE_CODE')) {
                $lang = ICL_LANGUAGE_CODE;
            } else {
                $lang = 'fa';
            }
        }
        $featured_label_text = (isset($options['featured_label_text_' . $lang])) ? $options['featured_label_text_' . $lang] :'';
    }
    echo '<input type="text" name="appchar_general_setting[featured_label_text]" value="' . $featured_label_text . '">';
}

function hide_signup_page_callback()
{
    $options = get_option('appchar_general_setting');
    $hide_signup_page = (isset($options['hide_signup_page'])) ? $options['hide_signup_page'] : false;
    $check = ($hide_signup_page) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[hide_signup_page]" value="1" ' . $check . '>';
}


function optional_page_builder_verification_callback()
{
    $options = get_option('appchar_general_setting');
    $optional_page_builder_verification = (isset($options['optional_page_builder_verification'])) ? $options['optional_page_builder_verification'] : false;
    $check = ($optional_page_builder_verification) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[optional_page_builder_verification]" value="1" ' . $check . '>';
}


function hide_user_info_in_drawer_callback()
{
    $options = get_option('appchar_general_setting');
    $hide_user_info_in_drawer = (isset($options['hide_user_info_in_drawer'])) ? $options['hide_user_info_in_drawer'] : false;
    $check = ($hide_user_info_in_drawer) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[hide_user_info_in_drawer]" value="1" ' . $check . '>';
}

function custom_product_addon_callback()
{
    if(is_plugin_active('woo-custom-product-addons/start.php')) {
        $options = get_option('appchar_general_setting');
        $custom_product_addon = (isset($options['custom_product_addon'])) ? $options['custom_product_addon'] : false;
        $check = ($custom_product_addon) ? 'checked' : '';
        echo '<input type="checkbox" name="appchar_general_setting[custom_product_addon]" value="1" ' . $check . '>';
    }else {
        echo '<input type="checkbox" name="appchar_general_setting[custom_product_addon]" value="1" disabled >';
        echo '<h4> لطفا پلاگین مربوطه را فعال نمایید </h4>';
    }

}
function open_child_page_for_single_category_homepage_builder_callback()
{
    $options = get_option('appchar_general_setting');
    $open_child_page = (isset($options['open_child_page'])) ? $options['open_child_page'] : false;
    $check = ($open_child_page) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[open_child_page]" value="1" ' . $check . '>';
}

function bestseller_product_is_visible_callback()
{

    $options = get_option('appchar_general_setting');
    $bestseller_product_is_visible = (isset($options['bestseller_product_is_visible'])) ? $options['bestseller_product_is_visible'] : get_option('appchar_bestseller_product_is_visible', true);
    $check = ($bestseller_product_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[bestseller_product_is_visible]" value="1" ' . $check . '>';

}

function recent_product_is_visible_callback()
{

    $options = get_option('appchar_general_setting');
    $recent_product_is_visible = (isset($options['recent_product_is_visible'])) ? $options['recent_product_is_visible'] : get_option('appchar_recent_product_is_visible', true);
    $check = ($recent_product_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[recent_product_is_visible]" value="1" ' . $check . '>';

}

function product_rate_is_visible_callback()
{

    $options = get_option('appchar_general_setting');
    $product_rate_is_visible = (isset($options['product_rate_is_visible'])) ? $options['product_rate_is_visible'] : get_option('appchar_product_rate_is_visible', true);
    $check = ($product_rate_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[product_rate_is_visible]" value="1" ' . $check . '>';

}

function search_is_visible_callback()
{

    $options = get_option('appchar_general_setting');
    $search_is_visible = (isset($options['search_is_visible'])) ? $options['search_is_visible'] : true;
    $check = ($search_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[search_is_visible]" value="1" ' . $check . '>';

}
function sort_is_visible_callback()
{

    $options = get_option('appchar_general_setting');
    $sort_is_visible = (isset($options['sort_is_visible'])) ? $options['sort_is_visible'] : true;
    $check = ($sort_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[sort_is_visible]" value="1" ' . $check . '>';

}
function out_stock_products_end_callback()
{
    if(get_option('woocommerce_default_catalog_orderby') == 'menu_order') {
        $options = get_option('appchar_general_setting');
        $out_stock_products_end = (isset($options['out_stock_products_end'])) ? $options['out_stock_products_end'] : true;
        $check = ($out_stock_products_end) ? 'checked' : '';
        echo '<input type="checkbox" name="appchar_general_setting[out_stock_products_end]" value="1" ' . $check . '>';
    }else {
        echo '<input type="checkbox" name="appchar_general_setting[out_stock_products_end]" value="1" disabled>';
        echo __('For Enabling please Change the Default product sorting from Default sorting(Custom ordering + name) option in Woocommerce setting.', 'appchar');
    }


}

function categories_is_visible_callback()
{

    $options = get_option('appchar_general_setting');
    $categories_is_visible = (isset($options['categories_is_visible'])) ? $options['categories_is_visible'] : true;
    $check = ($categories_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[categories_is_visible]" value="1" ' . $check . '>';

}

function products_is_visible_callback()
{

    $options = get_option('appchar_general_setting');
    $products_is_visible = (isset($options['products_is_visible'])) ? $options['products_is_visible'] : true;
    $check = ($products_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[products_is_visible]" value="1" ' . $check . '>';

}

function hamburger_menu_is_visible_callback()
{
    $options = get_option('appchar_general_setting');
    $hamburger_menu_is_visible = (isset($options['hamburger_menu_is_visible'])) ? $options['hamburger_menu_is_visible'] : true;
    $check = ($hamburger_menu_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[hamburger_menu_is_visible]" value="1" ' . $check . '>';
}

function blog_categories_is_visible_callback()
{
    $options = get_option('appchar_general_setting');
    $blog_categories_is_visible = (isset($options['blog_categories_is_visible'])) ? $options['blog_categories_is_visible'] : false;
    $check = ($blog_categories_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[blog_categories_is_visible]" value="1" ' . $check . '>';
}

function profile_is_visible_callback()
{
    $options = get_option('appchar_general_setting');
    $profile_is_visible = (isset($options['profile_is_visible'])) ? $options['profile_is_visible'] : true;
    $check = ($profile_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[profile_is_visible]" value="1" ' . $check . '>';
}

function hide_product_images_in_shopping_cart_callback()
{

    $options = get_option('appchar_general_setting');
    $hide_product_images_in_shopping_cart = (isset($options['hide_product_images_in_shopping_cart'])) ? $options['hide_product_images_in_shopping_cart'] : false;
    $check = ($hide_product_images_in_shopping_cart) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[hide_product_images_in_shopping_cart]" value="1" ' . $check . '>';

}
function sidebar_profile_is_visible_callback()
{
    $options = get_option('appchar_general_setting');
    $sidebar_profile_is_visible = (isset($options['sidebar_profile_is_visible'])) ? $options['sidebar_profile_is_visible'] : true;
    $check = ($sidebar_profile_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[sidebar_profile_is_visible]" value="1" ' . $check . '>';
}
function sidebar_blog_is_visible_callback()
{
    $options = get_option('appchar_general_setting');
    $sidebar_blog_is_visible = (isset($options['sidebar_blog_is_visible'])) ? $options['sidebar_blog_is_visible'] : true;
    $check = ($sidebar_blog_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[sidebar_blog_is_visible]" value="1" ' . $check . '>';
}
function sidebar_favorite_is_visible_callback()
{
    $options = get_option('appchar_general_setting');
    $sidebar_favorite_is_visible = (isset($options['sidebar_favorite_is_visible'])) ? $options['sidebar_favorite_is_visible'] : true;
    $check = ($sidebar_favorite_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[sidebar_favorite_is_visible]" value="1" ' . $check . '>';
}
function related_products_is_visible_callback()
{
    $options = get_option('appchar_general_setting');
    $related_products_is_visible = (isset($options['related_products_is_visible'])) ? $options['related_products_is_visible'] : true;
    $check = ($related_products_is_visible) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[related_products_is_visible]" value="1" ' . $check . '>';
}

function add_logo_image_callback()
{
    $options = get_option('appchar_general_setting');
    $logo_image_id = (isset($options['logo_image_id'])) ? $options['logo_image_id'] : '';
//    print_r($logo_image_id);
//    exit();
    if($logo_image_id==''):
        ?>
        <div class="card2 ui-sortable-handle" >
            <a onclick="appchar_remove_image(this, 'logo_image_id')" class="remove_image" style="display: none;"><img src="<?php echo APPCHAR_IMG_URL; ?>home_config/close.png"></a>
            <div class="banners-img">
                <a id="slide_upload" onclick="appchar_upload_win(this)" class="slide_upload um-cover-add um-manual-trigger atag" style="width:100%; height: 370px;" data-parent=".um-cover" data-child=".um-btn-auto-width">
                    <img class="thumbnail2 imgtag" src="" style="display: none;" width="300" height="60">
                    <span class="dashicons dashicons-plus-alt banner-icon"></span>
                </a>
                <input type="text" class="logo_image_id" name="appchar_general_setting[logo_image_id]" style="display: none;" id="logo_image_id" value="" readonly="">
            </div>
            <div style="text-align: center;"><?php _e('Your photo should be 60x300 or less','appchar'); ?></div>
        </div>
        <?php
    else:
        ?>
        <div class="card2 ui-sortable-handle" >
            <a onclick="appchar_remove_image(this, 'logo_image_id')" class="remove_image"><img src="<?php echo APPCHAR_IMG_URL; ?>home_config/close.png"></a>
            <div class="banners-img">
                <a id="slide_upload" onclick="appchar_upload_win(this)" class="slide_upload um-cover-add um-manual-trigger atag" style="width:100%; height: 370px;" data-parent=".um-cover" data-child=".um-btn-auto-width">
                    <?php
                    $logo_image = wp_get_attachment_image_src( $logo_image_id, 'appcahr-60' );
                    echo '<img class="thumbnail2 imgtag" src="'.$logo_image[0].'" width="'.$logo_image[1].'" height="'.$logo_image[2].'">';
                    ?>
                    <span class="dashicons dashicons-plus-alt banner-icon" style="display: none;"></span>
                </a>
                <input type="text" class="logo_image_id" name="appchar_general_setting[logo_image_id]" style="display: none;" id="logo_image_id" value="<?php echo $logo_image_id; ?>" readonly="">
            </div>
            <div style="text-align: center;"><?php _e('Your photo should be 60x300 or less','appchar'); ?></div>
        </div>
        <?php
    endif;
}


function add_order_status_icon_callback()
{
    $options = get_option('appchar_general_setting');
    $order_status_icon = (isset($options['order_status_icon'])) ? $options['order_status_icon'] : '';
    $statuses = array_keys(wc_get_order_statuses());
    // foreach ($statuses as $status){
    //     $status = str_replace("wc-",'',$status);
    // }
    foreach ($statuses as $status){
        $status = str_replace("wc-",'',$status);
        if(!isset($order_status_icon[$status]) || $order_status_icon[$status]==''):
            ?>
            <div class="card2 ui-sortable-handle" >
                <a onclick="appchar_remove_image(this, 'logo_image_id_<?php echo $status; ?>')" class="remove_image" style="display: none;"><img src="<?php echo APPCHAR_IMG_URL; ?>home_config/close.png"></a>
                <div class="banners-img">
                    <a id="slide_upload" onclick="appchar_upload_win(this)" class="slide_upload um-cover-add um-manual-trigger atag" style="width:100%; height: 370px;" data-parent=".um-cover" data-child=".um-btn-auto-width">
                        <img class="thumbnail2 imgtag" src="" style="display: none;" width="300" height="60">
                        <span class="dashicons dashicons-plus-alt banner-icon"></span>
                    </a>
                    <input type="text" class="logo_image_id" name="appchar_general_setting[order_status_icon][<?php echo $status; ?>]" style="display: none;" id="logo_image_id_<?php echo $status; ?>"  value="" readonly="">
                </div>
                <div style="text-align: center;"><?php echo __('Upload a square gif file for: ','appchar').$status; ?></div>
            </div>
        <?php
        else:
            ?>
            <div class="card2 ui-sortable-handle" >
                <a onclick="appchar_remove_image(this, 'logo_image_id_<?php echo $status; ?>')" class="remove_image"><img src="<?php echo APPCHAR_IMG_URL; ?>home_config/close.png"></a>
                <div class="banners-img">
                    <a id="slide_upload" onclick="appchar_upload_win(this)" class="slide_upload um-cover-add um-manual-trigger atag" style="width:100%; height: 370px;" data-parent=".um-cover" data-child=".um-btn-auto-width">
                        <?php
                        $osi = wp_get_attachment_image_src( $order_status_icon[$status] );
                        echo '<img class="thumbnail2 imgtag" src="'.$osi[0].'" width="'.$osi[1].'" height="'.$osi[2].'">';
                        ?>
                        <span class="dashicons dashicons-plus-alt banner-icon" style="display: none;"></span>
                    </a>
                    <input type="text" class="logo_image_id" name="appchar_general_setting[order_status_icon][<?php echo $status; ?>]" style="display: none;" id="logo_image_id_<?php echo $status; ?>"  value="<?php echo $order_status_icon[$status]; ?>" readonly="">
                </div>
                <div style="text-align: center;"><?php echo __('Upload a square gif file for: ','appchar').$status; ?></div>
            </div>
        <?php
        endif;
        ?>
        <script>
        function appchar_remove_image(obj, id) {
    var _this = jQuery(obj).parent('.card2');//.outerHTML;
    jQuery(obj).css('display','none');
    jQuery('input[type=text]#'+id).val('');
    _this.children('.banners-img').children('#slide_upload').children('.imgtag').attr('src', '');
    _this.children('.banners-img').children('#slide_upload').children('.imgtag').css('display', 'none');
    _this.children('.banners-img').children('#slide_upload').children('.banner-icon').css('display', '');
}
</script>
<?php
    }
}

function update_message_callback()
{
    $options = get_option('appchar_general_setting');
    $options['update_message'] = (isset($options['update_message'])) ? $options['update_message'] : get_option('appchar_update_message', '');
    echo '<textarea name="appchar_general_setting[update_message]" id="update_message">' . $options['update_message'] . '</textarea>';
}

function update_link_callback()
{
    $options = get_option('appchar_general_setting');
    $options['update_link'] = (isset($options['update_link'])) ? $options['update_link'] : get_option('appchar_update_link', '');
    echo '<textarea name="appchar_general_setting[update_link]" id="update_link">' . $options['update_link'] . '</textarea>';
}

function force_update_callback()
{
    $options = get_option('appchar_general_setting');
    $force_update = (isset($options['force_update'])) ? $options['force_update'] : get_option('appchar_force_update', true);
    $check = ($force_update) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[force_update]" value="1" ' . $check . '>';
}

function custom_message_callback()
{
    $options = get_option('appchar_general_setting');
    $options['custom_message'] = (isset($options['custom_message'])) ? $options['custom_message'] : get_option('appchar_custom_message', '');
    echo '<textarea name="appchar_general_setting[custom_message]" id="custom_message">' . $options['custom_message'] . '</textarea>';
}

function pinned_bgcolor_callback()
{
    $options = get_option('appchar_general_setting');
    $options['pinned_bgcolor'] = (isset($options['pinned_bgcolor'])) ? $options['pinned_bgcolor'] : get_option('appchar_custom_message_backgroundcolor', '#2ba6cb');
    $bgcolors = array(
        '#2ba6cb' => __('blue(info)', 'appchar'),
        '#5da423' => __('green(success)', 'appchar'),
        '#e3b000' => __('yellow(warning)', 'appchar'),
        '#c60f13' => __('red(alert)', 'appchar'),
    );
    foreach ($bgcolors as $bgcolorkey => $bgcolor) {
        if ($options['pinned_bgcolor'] == $bgcolorkey) {//TODO add checked(true, $options['bgcolor'], true) function
            echo '<div class="radio bgcolor" style="background-color:' . $bgcolorkey . '"><input type="radio" name="appchar_general_setting[pinned_bgcolor]" value="' . $bgcolorkey . '" checked="checked">' . $bgcolor . '</div>';
        } else {
            echo '<div class="radio bgcolor" style="background-color:' . $bgcolorkey . '"><input type="radio" name="appchar_general_setting[pinned_bgcolor]" value="' . $bgcolorkey . '">' . $bgcolor . '</div>';
        }
    }
}

function cart_button_type_callback(){
    $options = get_option('appchar_general_setting');
    $options['cart_button_type'] = (isset($options['cart_button_type'])) ? $options['cart_button_type'] : 'simple_button';
    ?>
    <select name="appchar_general_setting[cart_button_type]">
        <?php
        $card_types = array('none' => __('None', 'appchar'), 'simple_button' => __('Simple button', 'appchar'), 'toggle' => __('Toggle', 'appchar'),'keyboard'=>__('Keyboard','appchar'));
        foreach ($card_types as $key => $card_type) {
            if ($options['cart_button_type'] == $key) {
                echo '<option value="' . $key . '" selected>' . $card_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $card_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
}

function price_ranger_callback(){
    $options = get_option('appchar_general_setting');
    $options['price_ranger'] = (isset($options['price_ranger'])) ? $options['price_ranger'] : 'variation';
    ?>
    <select name="appchar_general_setting[price_ranger]">
        <?php
        $card_types = array('lower_price' => __('Lower Price', 'appchar'), 'higher_price' => __('Higher price', 'appchar'),'variation'=>__('Variation','appchar'));
        foreach ($card_types as $key => $card_type) {
            if ($options['price_ranger'] == $key) {
                echo '<option value="' . $key . '" selected>' . $card_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $card_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
}

function material_shape_type_callback(){
    $options = get_option('appchar_general_setting');
    $options['material_shape_type'] = (isset($options['material_shape_type'])) ? $options['material_shape_type'] : 'round_shaped';
    ?>
    <select name="appchar_general_setting[material_shape_type]">
        <?php
        //https://material.io/design/material-theming/implementing-your-theme.html#shape
        $material_theming_shape_types = array('round_shaped' => __('Round-shaped', 'appchar'), 'cut_shaped' => __('cut-shaped', 'appchar'));
        foreach ($material_theming_shape_types as $key => $material_theming_shape_type) {
            if ($options['material_shape_type'] == $key) {
                echo '<option value="' . $key . '" selected>' . $material_theming_shape_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $material_theming_shape_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
}

function material_shape_value_callback(){
    $options = get_option('appchar_general_setting');
    $options['material_shape_value'] = (isset($options['material_shape_value'])) ? $options['material_shape_value'] : '0';
    ?>
    <select name="appchar_general_setting[material_shape_value]">
        <?php
        $material_theming_shape_values = array('0dp' => __('0dp', 'appchar'), '2dp' => __('2dp', 'appchar'), '4dp' => __('4dp','appchar'), '8dp' => __('8dp','appchar'), '12dp' => __('12dp','appchar'), '16dp' => __('16dp','appchar'), '24dp' => __('24dp','appchar'));
        foreach ($material_theming_shape_values as $key => $material_theming_shape_value) {
            if ($options['material_shape_value'] == $key) {
                echo '<option value="' . $key . '" selected>' . $material_theming_shape_value . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $material_theming_shape_value . '</option>';
            }
        }
        ?>
    </select>
    <?php
}

function material_shape_position_callback() {
  $options = get_option('appchar_general_setting');
  $options['material_shape_position'] = (isset($options['material_shape_position'])) ? $options['material_shape_position'] : array();
      $material_theming_shape_positions = array('upper_right' => __('بالا راست', 'appchar'), 'upper_left' => __('بالا چپ', 'appchar'), 'bottom_right' => __('پایین راست','appchar'), 'bottom_left' => __('پایین چپ','appchar'));
      foreach ($material_theming_shape_positions as $key => $material_theming_shape_position) {
          if (in_array($key, $options['material_shape_position'])) {
              echo '<input type="checkbox" value="' . $key . '" name="appchar_general_setting[material_shape_position][]" checked>' . $material_theming_shape_position;
          } else {
              echo '<input type="checkbox" value="' . $key . '" name="appchar_general_setting[material_shape_position][]">' . $material_theming_shape_position;
          }
      }
}

function bottom_navigation_callback() {
  $options = get_option('appchar_general_setting');
  $bottom_navigation = (isset($options['bottom_navigation'])) ? $options['bottom_navigation'] : false;
  $check = ($bottom_navigation) ? 'checked' : '';
  echo '<input type="checkbox" name="appchar_general_setting[bottom_navigation]" value="1" ' . $check . '>';
}

function toggle_product_to_cart_callback()
{

    $options = get_option('appchar_general_setting');
    $toggle_product_to_cart = (isset($options['toggle_product_to_cart'])) ? $options['toggle_product_to_cart'] : get_option('appchar_toggle_product_to_cart', true);
    $check = ($toggle_product_to_cart) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[toggle_product_to_cart]" value="1" ' . $check . '>';

}

// NOTE By Iman Mokhtari Aski on 11/12/2019
function unit_price_calculator_callback() {

  if(is_plugin_active('woocommerce-measurement-price-calculator/woocommerce-measurement-price-calculator.php')) {
    $options = get_option('appchar_general_setting');
    $unit_price_calculator = (isset($options['unit_price_calculator'])) ? $options['unit_price_calculator'] : get_option('appchar_unit_price_calculator', true);
    $check = ($unit_price_calculator) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[unit_price_calculator]" value="1" ' . $check . '>';
  } else {
    echo '<input type="checkbox" name="appchar_general_setting[unit_price_calculator]" value="1" disabled>';
    echo '<h4> لطفا پلاگین وزن مربوطه را فعال نمایید </h4>';
  }


}

function sync_cart_to_site_callback()
{

    $options = get_option('appchar_general_setting');
    $sync_cart_to_site = (isset($options['sync_cart_to_site'])) ? $options['sync_cart_to_site'] : get_option('appchar_sync_cart_to_site', false);
    $check = ($sync_cart_to_site) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[sync_cart_to_site]" value="1" ' . $check . '>';

}

function force_login_callback()
{

    $options = get_option('appchar_general_setting');
    $force_login = (isset($options['force_login'])) ? $options['force_login'] : get_option('appchar_force_login', false);
    $check = ($force_login) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[force_login]" value="1" ' . $check . '>';

}

function user_approve_callback()
{

    $options = get_option('appchar_general_setting');
    $user_approve = (isset($options['user_approve'])) ? $options['user_approve'] : get_option('appchar_user_approve', false);
    $check = ($user_approve) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[user_approve]" value="1" ' . $check . '>';

}

function catalog_mode_callback()
{

    $options = get_option('appchar_general_setting');
    $catalog_mode = (isset($options['catalog_mode'])) ? $options['catalog_mode'] : get_option('appchar_catalog_mode', false);
    $check = ($catalog_mode) ? 'checked' : '';
    echo '<input type="checkbox" name="appchar_general_setting[catalog_mode]" value="1" ' . $check . '>';

}

function blog_title_callback()
{
    $options = get_option('appchar_general_setting');
    $blog_title = (isset($options['blog_title'])) ? $options['blog_title'] : get_option('appchar_blog_title', '');
    echo '<input type="text" name="appchar_general_setting[blog_title]" value="' . $blog_title . '">';
}

function blog_type_callback(){
    $options = get_option('appchar_general_setting');
    $options['blog_type'] = (isset($options['blog_type'])) ? $options['blog_type'] : 'posts';
    ?>
    <select name="appchar_general_setting[blog_type]">

        <?php
        $cats_type = array('posts' => __('Posts', 'appchar'),'categories' => __('Categories', 'appchar'));
        foreach ($cats_type as $key => $cat_type) {
            if ($options['blog_type'] == $key) {
                echo '<option value="' . $key . '" selected>' . $cat_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $cat_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
}

function lottery_title_callback()
{
    $options = get_option('appchar_general_setting');
    $lottery_title = (isset($options['lottery_title'])) ? $options['lottery_title'] : get_option('appchar_lottery_title', '');
    echo '<input type="text" name="appchar_general_setting[lottery_title]" value="' . $lottery_title . '">';

}
function appchar_filter_custom_label_callback()
{
    $options = get_option('appchar_general_setting');
    $lottery_title = (isset($options['appchar_filter_custom_label'])) ? $options['appchar_filter_custom_label'] : "";
    echo '<input type="text" name="appchar_general_setting[appchar_filter_custom_label]" value="' . $lottery_title . '" placeholder="مثال : برند | مدل | سال">';
    echo "<br>".__("در این قسمت نام فیلدهای فیلترها را با <span style=\"color: red;\"> | </span> از یکدیگر جداسازی کنید","appchar");

}
function city_type_callback()
{
    $options = get_option('appchar_general_setting');
    $options['city_type'] = (isset($options['city_type'])) ? $options['city_type'] : 'text';
    ?>
    <select name="appchar_general_setting[city_type]">

        <?php
        $cities_type = array('text' => __('text', 'appchar'),'select' => __('select', 'appchar'));
        foreach ($cities_type as $key => $city_type) {
            if ($options['city_type'] == $key) {
                echo '<option value="' . $key . '" selected>' . $city_type . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $city_type . '</option>';
            }
        }
        ?>
    </select>
    <?php
}


function appchar_general_settings_validate($arr_input)
{
    $options = get_option('appchar_general_setting');

    if (isset($arr_input['categories_display_type'])) {
        $options['categories_display_type'] = trim($arr_input['categories_display_type']);
    }
    if (isset($arr_input['percentage_discount_view_type'])) {
        $options['percentage_discount_view_type'] = trim($arr_input['percentage_discount_view_type']);
    }
    if (isset($arr_input['product_list_display_type'])) {
        $options['product_list_display_type'] = trim($arr_input['product_list_display_type']);
    }
    if (isset($arr_input['in_app_payment'])) {
        $options['in_app_payment'] = ($arr_input['in_app_payment'] == 1) ? true : false;
    }
    if (isset($arr_input['region_cities_source_option'])) {
        $chosen_option = $arr_input['region_cities_source_option'];
        switch($chosen_option) {
            case 0:
                $options['region_cities_source_option'] = 'woocommerce';
                break;
            // case 1:
            //     $options['region_cities_source_option'] = 'persian-woocommerce';
            //     break;
            case 2:
                $options['region_cities_source_option'] = 'persian-woocommerce-shipping';
                break;
            default:
                $options['region_cities_source_option'] = 'woocommerce';
                break;
        }
    }

    if (isset($arr_input['product_display_short_description'])) {
        $options['product_display_short_description'] = ($arr_input['product_display_short_description'] == 1) ? true : false;
    }
    if (isset($arr_input['custom_tab_count'])) {
        $options['custom_tab_count'] = trim($arr_input['custom_tab_count']);
    }
    if (isset($arr_input['cedarmaps_access_token'])) {
        $options['cedarmaps_access_token'] = trim($arr_input['cedarmaps_access_token']);
    }
    if (isset($arr_input['google_analytics_tracking_id'])) {
        $options['google_analytics_tracking_id'] = trim($arr_input['google_analytics_tracking_id']);
    }
    if (isset($arr_input['bestseller_product_is_visible'])) {
        $options['bestseller_product_is_visible'] = true;
    } else {
        $options['bestseller_product_is_visible'] = false;
    }
    if (isset($arr_input['recent_product_is_visible'])) {
        $options['recent_product_is_visible'] = true;
    } else {
        $options['recent_product_is_visible'] = false;
    }
    if (isset($arr_input['product_rate_is_visible'])) {
        $options['product_rate_is_visible'] = true;
    } else {
        $options['product_rate_is_visible'] = false;
    }
    if (isset($arr_input['search_is_visible'])) {
        $options['search_is_visible'] = true;
    } else {
        $options['search_is_visible'] = false;
    }
    if (isset($arr_input['sort_is_visible'])) {
        $options['sort_is_visible'] = true;
    } else {
        $options['sort_is_visible'] = false;
    }
    if (isset($arr_input['out_stock_products_end'])) {
        $options['out_stock_products_end'] = true;
    } else {
        $options['out_stock_products_end'] = false;
    }
    if (isset($arr_input['categories_is_visible'])) {
        $options['categories_is_visible'] = true;
    } else {
        $options['categories_is_visible'] = false;
    }
    if (isset($arr_input['products_is_visible'])) {
        $options['products_is_visible'] = true;
    } else {
        $options['products_is_visible'] = false;
    }
    if (isset($arr_input['hamburger_menu_is_visible'])) {
        $options['hamburger_menu_is_visible'] = true;
    } else {
        $options['hamburger_menu_is_visible'] = false;
    }
    if (isset($arr_input['profile_is_visible'])) {
        $options['profile_is_visible'] = true;
    } else {
        $options['profile_is_visible'] = false;
    }
    if (isset($arr_input['blog_categories_is_visible'])) {
        $options['blog_categories_is_visible'] = true;
    } else {
        $options['blog_categories_is_visible'] = false;
    }
    if (isset($arr_input['hide_product_images_in_shopping_cart'])) {
        $options['hide_product_images_in_shopping_cart'] = true;
    } else {
        $options['hide_product_images_in_shopping_cart'] = false;
    }
    if (isset($arr_input['sidebar_profile_is_visible'])) {
        $options['sidebar_profile_is_visible'] = true;
    } else {
        $options['sidebar_profile_is_visible'] = false;
    }
    if (isset($arr_input['sidebar_blog_is_visible'])) {
        $options['sidebar_blog_is_visible'] = true;
    } else {
        $options['sidebar_blog_is_visible'] = false;
    }
    if (isset($arr_input['sidebar_favorite_is_visible'])) {
        $options['sidebar_favorite_is_visible'] = true;
    } else {
        $options['sidebar_favorite_is_visible'] = false;
    }
    if (isset($arr_input['related_products_is_visible'])) {
        $options['related_products_is_visible'] = true;
    } else {
        $options['related_products_is_visible'] = false;
    }
    if (isset($arr_input['logo_image_id'])) {
        $options['logo_image_id'] = $arr_input['logo_image_id'];
    } else {
        $options['logo_image_id'] = '';
    }
    if (isset($arr_input['order_status_icon'])) {
        $options['order_status_icon'] = $arr_input['order_status_icon'];
    } else {
        $options['order_status_icon'] = array();
    }

    if (isset($arr_input['add_to_cart_button'])) {
        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            if (isset($_GET['lang'])) {
                $lang = $_GET['lang'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            $options['add_to_cart_button_' . $lang] = trim($arr_input['add_to_cart_button']);
        } else {
            $options['add_to_cart_button'] = trim($arr_input['add_to_cart_button']);
        }
    }
    if (isset($arr_input['call_to_price_button'])) {
        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            if (isset($_GET['lang'])) {
                $lang = $_GET['lang'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            $options['call_to_price_button_' . $lang] = trim($arr_input['call_to_price_button']);
        } else {
            $options['call_to_price_button'] = trim($arr_input['call_to_price_button']);
        }
    }
    if (isset($arr_input['call_to_price_status'])) {
        $options['call_to_price_status'] = true;
    } else {
        $options['call_to_price_status'] = false;
    }

    if (isset($arr_input['featured_label_text'])) {
        if (AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            if (isset($_GET['lang'])) {
                $lang = $_GET['lang'];
            } else {
                if (defined('ICL_LANGUAGE_CODE')) {
                    $lang = ICL_LANGUAGE_CODE;
                } else {
                    $lang = 'fa';
                }
            }
            $options['featured_label_text_' . $lang] = trim($arr_input['featured_label_text']);
        } else {
            $options['featured_label_text'] = trim($arr_input['featured_label_text']);
        }
    }
    if (isset($arr_input['hide_signup_page'])) {
        $options['hide_signup_page'] = true;
    } else {
        $options['hide_signup_page'] = false;
    }
    if (isset($arr_input['optional_page_builder_verification'])) {
        $options['optional_page_builder_verification'] = true;
    } else {
        $options['optional_page_builder_verification'] = false;
    }
    if (isset($arr_input['hide_user_info_in_drawer'])) {
        $options['hide_user_info_in_drawer'] = true;
    } else {
        $options['hide_user_info_in_drawer'] = false;
    }
    if (isset($arr_input['custom_product_addon'])) {
        $options['custom_product_addon'] = true;
    } else {
        $options['custom_product_addon'] = false;
    }

    if (isset($arr_input['open_child_page'])) {
        $options['open_child_page'] = true;
    } else {
        $options['open_child_page'] = false;
    }

    if (isset($arr_input['update_message'])) {
        $options['update_message'] = trim($arr_input['update_message']);
    }
    if (isset($arr_input['update_link'])) {
        $options['update_link'] = trim($arr_input['update_link']);
    }
    if (isset($arr_input['force_update'])) {
        $options['force_update'] = true;
    } else {
      $options['force_update'] = false;
    }
    if (isset($arr_input['bottom_navigation'])) {
        $options['bottom_navigation'] = true;
    } else {
      $options['bottom_navigation'] = false;
    }
    if (isset($arr_input['custom_message'])) {
        $options['custom_message'] = trim($arr_input['custom_message']);
    }
    if (isset($arr_input['pinned_bgcolor'])) {
        $options['pinned_bgcolor'] = trim($arr_input['pinned_bgcolor']);
    }
    if (isset($arr_input['pinned_bgcolor'])) {
        $options['pinned_bgcolor'] = trim($arr_input['pinned_bgcolor']);
    }
    if (isset($arr_input['cart_button_type'])) {
        $options['cart_button_type'] = trim($arr_input['cart_button_type']);
    }
    if (isset($arr_input['price_ranger'])) {
        $options['price_ranger'] = trim($arr_input['price_ranger']);
    }
    if (isset($arr_input['material_shape_type'])) {
        $options['material_shape_type'] = trim($arr_input['material_shape_type']);
    }
    if (isset($arr_input['material_shape_value'])) {
        $options['material_shape_value'] = trim($arr_input['material_shape_value']);
    }
    if (isset($arr_input['material_shape_position'])) {
        $options['material_shape_position'] = $arr_input['material_shape_position'];
    }else{
      $options['material_shape_position'] = array();
    }
    if (isset($arr_input['toggle_product_to_cart'])) {
        $options['toggle_product_to_cart'] = true;
    } else {
        $options['toggle_product_to_cart'] = false;
    }
    // NOTE By Iman Mokhtari Aski on 11/12/2019
    if (isset($arr_input['unit_price_calculator'])) {
      $options['unit_price_calculator'] = true;
    } else {
      $options['unit_price_calculator'] = false;
    }
    // NOTE By Iman Mokhtari Aski on 11/12/2019
    if (isset($arr_input['weight_unit'])) {
      $options['eight_unit'] = true;
    } else {
      $options['weight_unit'] = false;
    }
    if (isset($arr_input['sync_cart_to_site'])) {
        $options['sync_cart_to_site'] = true;
    } else {
        $options['sync_cart_to_site'] = false;
    }
    if (isset($arr_input['force_login'])) {
        $options['force_login'] = true;
    } else {
        $options['force_login'] = false;
    }
    if (isset($arr_input['user_approve'])) {
        $options['user_approve'] = true;
    } else {
        $options['user_approve'] = false;
    }
    if (isset($arr_input['catalog_mode'])) {
        $options['catalog_mode'] = true;
    } else {
        $options['catalog_mode'] = false;
    }
    if (isset($arr_input['appstore_distribution_enable'])) {
        $options['appstore_distribution_enable'] = true;
    } else {
        $options['appstore_distribution_enable'] = false;
    }
    if (isset($arr_input['appstore_distribution_allowed_countries'])) {
        $options['appstore_distribution_allowed_countries'] = $arr_input['appstore_distribution_allowed_countries'];
    } else {
        $options['appstore_distribution_allowed_countries'] = [];
    }
    if (isset($arr_input['appstore_distribution_alternative_url'])) {
        $options['appstore_distribution_alternative_url'] = trim($arr_input['appstore_distribution_alternative_url']);
    }
    if (isset($arr_input['blog_title'])) {
        $options['blog_title'] = trim($arr_input['blog_title']);
    }
    if (isset($arr_input['blog_type'])) {
        $options['blog_type'] = trim($arr_input['blog_type']);
    }
    if (isset($arr_input['lottery_title'])) {
        $options['lottery_title'] = trim($arr_input['lottery_title']);
    }
    if (isset($arr_input['appchar_filter_custom_label'])) {
        $options['appchar_filter_custom_label'] = trim($arr_input['appchar_filter_custom_label']);
    }
    if (isset($arr_input['city_type'])) {
        if($arr_input['city_type']=='select'){
            $options['city_type'] = 'select';
        }else{
            $options['city_type'] = 'text';
        }
    }

    return $options;
}

?>
