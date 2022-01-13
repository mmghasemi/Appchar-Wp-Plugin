<?php

defined('ABSPATH') or exit(__('You do not have direct access to this page.', 'appchar'));

global $appchar_db_version;
$appchar_db_version = '2.1';

function appchar_create_posttype() {

    register_post_type( 'appchar_post',
        // CPT Options
        array(
            'labels' => array(
                'name' => 'appchar_post',
                'singular_name' => 'appchar_post',
            ),
            'public' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => 'appchar_post'),
        )
    );
}
// Hooking up our function to theme setup
add_action( 'init', 'appchar_create_posttype' );

function appchar_remove_menu_items() {
        remove_menu_page( 'edit.php?post_type=appchar_post' );
        remove_menu_page( 'post-new.php?post_type=appchar_post' );
}
add_action( 'admin_menu', 'appchar_remove_menu_items' );


function appchar_create_tables()
{
    global $wpdb;
    global $jal_db_version;

    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'appchar_banners';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
              banner_id int(11) NOT NULL AUTO_INCREMENT,
              category_id int(11) NULL DEFAULT NULL,
              banner_collection_id int(11) NOT NULL,
              banner_type VARCHAR(30) NOT NULL,
              enable_date datetime NOT NULL,
              disable_date datetime NOT NULL,
              image text NOT NULL,
              link text NOT NULL,
              new_window int(11) NOT NULL,
              sequence int(11) NOT NULL,
              PRIMARY KEY (banner_id)
          )  $charset_collate;";

        dbDelta($sql);
    } else{
        $query = "ALTER TABLE `{$table_name}` CHANGE `name` `banner_type` VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL;";
        $wpdb->query($query);
        
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = $table_name AND column_name in 'category_id'");
        if (empty($row)) {
            $wpdb->query("ALTER TABLE $table_name ADD category_id int(11) NULL DEFAULT NULL");
        }
    }
    $table_name2 = $wpdb->prefix . 'appchar_pages';
    if ($wpdb->get_var("show tables like '{$table_name2}'") != $table_name2) {
        $sql2 = "CREATE TABLE  IF NOT EXISTS  {$table_name2} (
                      id int(11) NOT NULL AUTO_INCREMENT,
                      page_id int(11) NOT NULL,
                      title VARCHAR(128) NOT NULL,
                      position int(11) NOT NULL,
                      language VARCHAR(5),
                      icon VARCHAR(5),
                      PRIMARY KEY (id)
                  )  $charset_collate;";

        dbDelta($sql2);
    }else{
        $query = "ALTER TABLE `{$table_name2}` ADD `language` VARCHAR(5) NULL AFTER `position`, ADD  `icon` VARCHAR(5) NULL AFTER `language`;";
        $wpdb->query($query);
    }
    $table_name3 = $wpdb->prefix . 'appchar_user_devices';
    if ($wpdb->get_var("show tables like '{$table_name3}'") != $table_name3) {

        $sql3 = "CREATE TABLE  IF NOT EXISTS  {$table_name3} (
                      id int(11) NOT NULL AUTO_INCREMENT,
                      user_device_id VARCHAR(50) UNIQUE,
                      user_id int(11),
                      PRIMARY KEY (id)
                  )  $charset_collate;";


        dbDelta($sql3);
    }
    $table_name4 = $wpdb->prefix . 'appchar_notification_log';
    if ($wpdb->get_var("show tables like '{$table_name4}'") != $table_name4) {
// target is FOREIGN KEY that 0 for all device and id of device for each device
        $sql4 = "CREATE TABLE  IF NOT EXISTS  {$table_name4} (
                      id BIGINT(11) NOT NULL AUTO_INCREMENT,
                      headings VARCHAR (50),
                      contents VARCHAR (200),
                      user_id BIGINT (20),
                      notification_time DATE,
                      PRIMARY KEY (id)
                  )  $charset_collate;";

        dbDelta($sql4);
    }else{
        $query = "ALTER TABLE `{$table_name4}` CHANGE `notification_time` `notification_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;";
        $wpdb->query($query);
    }
    
    $table_name5 = $wpdb->prefix . 'appchar_user_wallet_log';
    if ($wpdb->get_var("show tables like '{$table_name5}'") != $table_name5) {
// target is FOREIGN KEY that 0 for all device and id of device for each device
        $sql5 = "CREATE TABLE  IF NOT EXISTS  {$table_name5} (
                      id BIGINT(11) NOT NULL AUTO_INCREMENT,
                      price VARCHAR (50),
                      user_id BIGINT(11),
                      transaction_type VARCHAR (50),
                      how VARCHAR (200),
                      by_who BIGINT (11),
                      order_id BIGINT(11),
                      old_credit VARCHAR(20),
                      new_credit VARCHAR(20),
                      created_at DATETIME,
                      PRIMARY KEY (id)
                  )  $charset_collate;";
        //transaction_type has two value -> add , subtract , update
        dbDelta($sql5);
    }
    add_option('appchar_db_version', $jal_db_version);

}

function appchar_on_activate($network_wide)
{
    global $wpdb,$appcharEndpoint;
    update_option('redirect_to_appchar_about_page_check', 'no');

    if (is_multisite() && $network_wide) {
        $current_blog = $wpdb->blogid;
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            appchar_create_tables();
            restore_current_blog();
            $appcharEndpoint->appchar_flush_rewrites();
        }
    } else {
        appchar_create_tables();
        appchar_delete_woocommerce_token();
        $appcharEndpoint->appchar_flush_rewrites();
    }
}

function appchar_on_deactivate()
{
    update_option('redirect_to_appchar_about_page_check', 'no');
}

function appchar_on_create_blog($blog_id, $user_id, $domain, $path, $site_id, $meta)
{
    global $appcharEndpoint;
    if (is_plugin_active_for_network('plugin-name/plugin-name.php')) {
        switch_to_blog($blog_id);
        appchar_create_tables();
        restore_current_blog();
        $appcharEndpoint->appchar_flush_rewrites();
    }
}
add_action('wpmu_new_blog', 'appchar_on_create_blog', 10, 6);

function appchar_on_delete_blog($tables)
{
    global $wpdb;
    $tables[] = $wpdb->prefix . 'appchar_banners';
    $tables[] = $wpdb->prefix . 'appchar_pages';
    $tables[] = $wpdb->prefix . 'appchar_user_devices';
    return $tables;
}
add_filter('wpmu_drop_tables', 'appchar_on_delete_blog');

function appchar_admin_scripts()
{
    wp_enqueue_media();
    wp_register_script('appchar-admin-js', APPCHAR_JS_URL . 'my-admin.js', array('jquery'), '', true);
    wp_enqueue_script('appchar-admin-js');
}
add_action('admin_enqueue_scripts', 'appchar_admin_scripts');




register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
register_activation_hook( __FILE__, 'appchar_flush_rewrites_init' );

function appchar_plugins_loaded()
{
    appchar_update_db_check();
}
add_action('plugins_loaded', 'appchar_plugins_loaded');

function appchar_update_db_check()
{
    global $wpdb;
    global $appchar_db_version;
    $current_version = get_option('appchar_db_version', $appchar_db_version);
    if (version_compare($current_version, $appchar_db_version) < 0) {
        //appchar_flush_rewrites();
        $charset_collate = $wpdb->get_charset_collate();
        $table_name2 = $wpdb->prefix . 'appchar_pages';

        if ($wpdb->get_var("show tables like '{$table_name2}'") != $table_name2) {
            $posts_table_name = $wpdb->prefix . 'posts';
            $sql2 = "CREATE TABLE {$table_name2} (
                      id int(11) NOT NULL AUTO_INCREMENT,
                      page_id int(11),
                      title VARCHAR(128),
                      position int(11) NOT NULL,
                      PRIMARY KEY (id)
                  )  $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql2);
        }

        update_option('appchar_db_version', $appchar_db_version);
    }
}

function get_product_data($product)
{
    return array(
        'title' => $product->get_title(),
        'id' => (int)$product->is_type('variation') ? $product->get_variation_id() : $product->id,
        'created_at' => format_datetime($product->get_post_data()->post_date_gmt),
        'updated_at' => format_datetime($product->get_post_data()->post_modified_gmt),
        'type' => $product->product_type,
        'status' => $product->get_post_data()->post_status,
        'downloadable' => $product->is_downloadable(),
        'virtual' => $product->is_virtual(),
        'permalink' => $product->get_permalink(),
        'sku' => $product->get_sku(),
        'price' => $product->get_price(),
        'regular_price' => $product->get_regular_price(),
        'sale_price' => $product->get_sale_price() ? $product->get_sale_price() : null,
        'price_html' => $product->get_price_html(),
        'taxable' => $product->is_taxable(),
        'tax_status' => $product->get_tax_status(),
        'tax_class' => $product->get_tax_class(),
        'managing_stock' => $product->managing_stock(),
        'stock_quantity' => $product->get_stock_quantity(),
        'in_stock' => $product->is_in_stock(),
        'backorders_allowed' => $product->backorders_allowed(),
        'backordered' => $product->is_on_backorder(),
        'sold_individually' => $product->is_sold_individually(),
        'purchaseable' => $product->is_purchasable(),
        'featured' => $product->is_featured(),
        'visible' => $product->is_visible(),
        'catalog_visibility' => $product->visibility,
        'on_sale' => $product->is_on_sale(),
        'product_url' => $product->is_type('external') ? $product->get_product_url() : '',
        'button_text' => $product->is_type('external') ? $product->get_button_text() : '',
        'weight' => $product->get_weight() ? $product->get_weight() : null,
        'dimensions' => array(
            'length' => $product->length,
            'width' => $product->width,
            'height' => $product->height,
            'unit' => get_option('woocommerce_dimension_unit'),
        ),
        'shipping_required' => $product->needs_shipping(),
        'shipping_taxable' => $product->is_shipping_taxable(),
        'shipping_class' => $product->get_shipping_class(),
        'shipping_class_id' => (0 !== $product->get_shipping_class_id()) ? $product->get_shipping_class_id() : null,
        'description' => str_replace(array("\r\n", "\n"), "", wpautop(do_shortcode($product->get_post_data()->post_content))),
        'short_description' => str_replace(array("\r\n", "\n"), "", apply_filters('woocommerce_short_description', $product->get_post_data()->post_excerpt)),
        'reviews_allowed' => ('open' === $product->get_post_data()->comment_status),
        'average_rating' => wc_format_decimal($product->get_average_rating(), 2),
        'rating_count' => (int)$product->get_rating_count(),
        'related_ids' => array_map('absint', array_values($product->get_related())),
        'upsell_ids' => array_map('absint', $product->get_upsells()),
        'cross_sell_ids' => array_map('absint', $product->get_cross_sells()),
        'parent_id' => $product->post->post_parent,
        'categories' => wp_get_post_terms($product->id, 'product_cat', array('fields' => 'names')),
        'tags' => wp_get_post_terms($product->id, 'product_tag', array('fields' => 'names')),
        'images' => get_product_images($product),
        'featured_src' => (string)wp_get_attachment_url(get_post_thumbnail_id($product->is_type('variation') ? $product->variation_id : $product->id)),
        'attributes' => get_product_attributes($product),
        'downloads' => get_product_downloads($product),
        'download_limit' => (int)$product->download_limit,
        'download_expiry' => (int)$product->download_expiry,
        'download_type' => $product->download_type,
        'purchase_note' => wpautop(do_shortcode(wp_kses_post($product->purchase_note))),
        'total_sales' => metadata_exists('post', $product->id, 'total_sales') ? (int)get_post_meta($product->id, 'total_sales', true) : 0,
        'variations' => array(),
        'parent' => array(),
        'grouped_products' => array(),
        'menu_order' => $product->post->menu_order,
    );
}

function format_datetime($timestamp, $convert_to_utc = false)
{

    if ($convert_to_utc) {
        $timezone = new DateTimeZone(wc_timezone_string());
    } else {
        $timezone = new DateTimeZone('UTC');
    }

    try {

        if (is_numeric($timestamp)) {
            $date = new DateTime("@{$timestamp}");
        } else {
            $date = new DateTime($timestamp, $timezone);
        }

        // convert to UTC by adjusting the time based on the offset of the site's timezone
        if ($convert_to_utc) {
            $date->modify(-1 * $date->getOffset() . ' seconds');
        }

    } catch (Exception $e) {

        $date = new DateTime('@0');
    }

    return $date->format('Y-m-d\TH:i:s\Z');
}

function get_product_images($product)
{

    $images = $attachment_ids = array();

    if ($product->is_type('variation')) {

        if (has_post_thumbnail($product->get_variation_id())) {

            // Add variation image if set
            $attachment_ids[] = get_post_thumbnail_id($product->get_variation_id());

        } elseif (has_post_thumbnail($product->id)) {

            // Otherwise use the parent product featured image if set
            $attachment_ids[] = get_post_thumbnail_id($product->id);
        }

    } else {

        // Add featured image
        if (has_post_thumbnail($product->id)) {
            $attachment_ids[] = get_post_thumbnail_id($product->id);
        }

        // Add gallery images
        $attachment_ids = array_merge($attachment_ids, $product->get_gallery_attachment_ids());
    }

    // Build image data
    foreach ($attachment_ids as $position => $attachment_id) {

        $attachment_post = get_post($attachment_id);

        if (is_null($attachment_post)) {
            continue;
        }

        $attachment = wp_get_attachment_image_src($attachment_id, 'full');

        if (!is_array($attachment)) {
            continue;
        }

        $images[] = array(
            'id' => (int)$attachment_id,
            'created_at' => format_datetime($attachment_post->post_date_gmt),
            'updated_at' => format_datetime($attachment_post->post_modified_gmt),
            'src' => current($attachment),
            'title' => get_the_title($attachment_id),
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
            'position' => (int)$position,
        );
    }

    // Set a placeholder image if the product has no images set
    if (empty($images)) {

        $images[] = array(
            'id' => 0,
            'created_at' => format_datetime(time()), // Default to now
            'updated_at' => format_datetime(time()),
            'src' => wc_placeholder_img_src(),
            'title' => __('Placeholder', 'woocommerce'),
            'alt' => __('Placeholder', 'woocommerce'),
            'position' => 0,
        );
    }

    return $images;
}

function get_product_attributes($product)
{

    $attributes = array();

    if ($product->is_type('variation')) {

        // variation attributes
        foreach ($product->get_variation_attributes() as $attribute_name => $attribute) {

            // taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`
            $attributes[] = array(
                'name' => wc_attribute_label(str_replace('attribute_', '', $attribute_name)),
                'slug' => str_replace('attribute_', '', str_replace('pa_', '', $attribute_name)),
                'option' => $attribute,
            );
        }

    } else {

        foreach ($product->get_attributes() as $attribute) {

            // taxonomy-based attributes are comma-separated, others are pipe (|) separated
            if ($attribute['is_taxonomy']) {
                $options = explode(',', $product->get_attribute($attribute['name']));
            } else {
                $options = explode('|', $product->get_attribute($attribute['name']));
            }

            $attributes[] = array(
                'name' => wc_attribute_label($attribute['name']),
                'slug' => str_replace('pa_', '', $attribute['name']),
                'position' => (int)$attribute['position'],
                'visible' => (bool)$attribute['is_visible'],
                'variation' => (bool)$attribute['is_variation'],
                'options' => array_map('trim', $options),
            );
        }
    }

    return $attributes;
}

function get_product_downloads($product)
{

    $downloads = array();

    if ($product->is_downloadable()) {

        foreach ($product->get_files() as $file_id => $file) {

            $downloads[] = array(
                'id' => $file_id, // do not cast as int as this is a hash
                'name' => $file['name'],
                'file' => $file['file'],
            );
        }
    }

    return $downloads;
}

function appchar_get_product_variation_data($product)
{
    $variations = array();

    foreach ($product->get_children() as $child_id) {

        $variation = $product->get_child($child_id);

        if (!$variation->exists()) {
            continue;
        }

        $variations[] = array(
            'id' => $variation->get_variation_id(),
            'created_at' => format_datetime($variation->get_post_data()->post_date_gmt),
            'updated_at' => format_datetime($variation->get_post_data()->post_modified_gmt),
            'downloadable' => $variation->is_downloadable(),
            'virtual' => $variation->is_virtual(),
            'permalink' => $variation->get_permalink(),
            'sku' => $variation->get_sku(),
            'price' => $variation->get_price(),
            'regular_price' => $variation->get_regular_price(),
            'sale_price' => $variation->get_sale_price() ? $variation->get_sale_price() : null,
            'taxable' => $variation->is_taxable(),
            'tax_status' => $variation->get_tax_status(),
            'tax_class' => $variation->get_tax_class(),
            'managing_stock' => $variation->managing_stock(),
            'stock_quantity' => $variation->get_stock_quantity(),
            'in_stock' => $variation->is_in_stock(),
            'backordered' => $variation->is_on_backorder(),
            'purchaseable' => $variation->is_purchasable(),
            'visible' => $variation->variation_is_visible(),
            'on_sale' => $variation->is_on_sale(),
            'weight' => $variation->get_weight() ? $variation->get_weight() : null,
            'dimensions' => array(
                'length' => $variation->length,
                'width' => $variation->width,
                'height' => $variation->height,
                'unit' => get_option('woocommerce_dimension_unit'),
            ),
            'shipping_class' => $variation->get_shipping_class(),
            'shipping_class_id' => (0 !== $variation->get_shipping_class_id()) ? $variation->get_shipping_class_id() : null,
            'image' => get_product_images($variation),
            'attributes' => get_product_attributes($variation),
            'downloads' => get_product_downloads($variation),
            'download_limit' => (int)$product->download_limit,
            'download_expiry' => (int)$product->download_expiry,
        );
    }

    return $variations;
}

function get_grouped_products_data($product)
{
    $products = array();

    foreach ($product->get_children() as $child_id) {
        $_product = $product->get_child($child_id);

        if (!$_product->exists()) {
            continue;
        }

        $products[] = get_product_data($_product);

    }

    return $products;
}
