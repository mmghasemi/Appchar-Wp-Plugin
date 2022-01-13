<?php

?>

<?php
if(!get_option('appchar_homepage_config2',false)){
    if(get_option('appchar_homepage_config',false)) {

        $home_config = array();
        $homepage_config = get_option('appchar_homepage_config', false);
        if ($homepage_config['banner_status'] == 'enable') {

            global $wpdb;
            $bannners = array();
            foreach ($wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "appchar_banners ORDER BY sequence,banner_id ") as $key => $row) {
                $banners[] = $row;
            }
            $items = array();
            foreach ($banners as $banner) {
                $items[] = array(
                    'image' => $banner->image,
                    'link_type' => $banner->banner_type,
                    'category_id' => $banner->category_id,
                    'link' => $banner->link,
                );
            }
            $home_config[] = array(
                'type' => 'slider',
                'items' => $items,
            );
        }
        if ($homepage_config['category_status'] == 'enable' && get_option('appchar_display_categories', 'random') == 'cat_button') {
            $home_config[] = array(
                'type' => 'button',
                'link_type' => 'categories_page',
                'category_id' => -1,
                'link' => '',
                'text' => get_option('appchar_category_button_text', __('View All Categories', 'appchar')),
            );
        }
        if ($homepage_config['recent_product']['image'] != '') {
            $home_config[] = array(
                "type" => "banner",
                "image" => $homepage_config['recent_product']['image'],
                "link_type" => "-1",
                "category_id" => "-1",
                "link" => ""
            );
        }
        if ($homepage_config['recent_product']['status'] == 'enable') {
            $home_config[] = array(
                'type' => 'product_list',
                "product_list_type" => "recent",
                "category_id" => "120",
                "sortby" => "recent",
                "order" => "desc",
                "count" => 15,
                "title" => "recent product",
            );
        }
        if ($homepage_config['bestseller_product']['image'] != '') {
            $home_config[] = array(
                "type" => "banner",
                "image" => $homepage_config['bestseller_product']['image'],
                "link_type" => "-1",
                "category_id" => "-1",
                "link" => ""
            );
        }
        if ($homepage_config['bestseller_product']['status'] == 'enable') {
            $home_config[] = array(
                'type' => 'product_list',
                "product_list_type" => "bestseller",
                "category_id" => "120",
                "sortby" => "bestseller",
                "order" => "desc",
                "count" => 15,
                "title" => "recent product",
            );
        }
        if ($homepage_config['special_categories']) {
            foreach ($homepage_config['special_categories'] as $special_category) {
                if ($special_category['image'] != '') {
                    $home_config[] = array(
                        "type" => "banner",
                        "image" => $special_category['image'],
                        "link_type" => "-1",
                        "category_id" => "-1",
                        "link" => ""
                    );
                }
                if(get_option( 'woocommerce_default_catalog_orderby') == 'menu_order') {
                    $home_config[] = array(
                        "type" => "product_list",
                        "product_list_type" => "special_category",
                        "category_id" => $special_category['id'],
                        "sortby" => "default",
                        "order" => "asc",
                        "count" => 15,
                        "title" => $special_category['name'],
                    );
                }else {
                    $home_config[] = array(
                        "type" => "product_list",
                        "product_list_type" => "special_category",
                        "category_id" => $special_category['id'],
                        "sortby" => "default",
                        "order" => "desc",
                        "count" => 15,
                        "title" => $special_category['name'],
                    );
                }
                
            }
        }
        update_option('appchar_homepage_config2', $home_config);
    }
}

?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('home page setting', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <?php include('homepage_config.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery( function() {
        jQuery( ".sortable" ).sortable();
        jQuery( ".sortable" ).disableSelection();
    } );
</script>
