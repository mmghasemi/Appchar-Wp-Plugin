<?php
/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 7/2/18
 * Time: 10:15 AM
 */


add_filter( 'woocommerce_product_data_tabs', 'appchar_add_product_data_tab' );
add_action( 'woocommerce_product_data_panels', 'appchar_add_product_data_fields' );
add_action( 'woocommerce_process_product_meta', 'appcahr_product_meta_fields_save' );
add_filter('add_meta_to_product_endpoint','appchar_insert_product_meta',10,1);




function appchar_add_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['my-custom-tab'] = array(
        'label' => __( 'Appchar', 'appchar' ),
        'target' => 'appchar_custom_product_data',
    );
    return $product_data_tabs;
}

function appchar_add_product_data_fields() {
    global $woocommerce, $post,$extension;
    ?>
    <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
    <div id="appchar_custom_product_data" class="panel woocommerce_options_panel">
        <?php
        if(AppcharExtension::extensionIsActive('special_offer')) {
            woocommerce_wp_checkbox(array(
                'id' => '_appchar_is_special_offer',
                'wrapper_class' => 'special_offer',
                'label' => __('special offer product', 'appchar'),
                'description' => '',
                'default' => '0',
                ''
            ));
        }
        if(AppcharExtension::extensionIsActive('easy_shopping_cart')) {
            woocommerce_wp_text_input(
                array(
                    'id' => '_appchar_step_product_count_order',
                    'label' => __('Step of product count to get order', 'woocommerce'),
                    'placeholder' => '1',
                    'type' => 'number',
                    'description' => ''
                )
            );
        }
        do_action('appchar_custom_product_data');
        ?>
    </div>
    <?php
}

function appcahr_product_meta_fields_save( $post_id ){
    if( isset( $_POST['_appchar_is_special_offer'] ) )
        update_post_meta( $post_id, '_appchar_is_special_offer', "yes");
    else{
        update_post_meta( $post_id, '_appchar_is_special_offer', 'no');
    }
    if( isset( $_POST['_appchar_step_product_count_order'] ) && $_POST['_appchar_step_product_count_order']>0 )
        update_post_meta( $post_id, '_appchar_step_product_count_order', $_POST['_appchar_step_product_count_order']);
    // save the text field data

    do_action('save_appchar_custom_product_data',$post_id);
}

function appchar_insert_product_meta($product_data){
    if(AppcharExtension::extensionIsActive('easy_shopping_cart')) {
        $step_count = (int) get_post_meta($product_data['product']['id'], '_appchar_step_product_count_order', true);
    }else{
        $step_count = 1;
    }
    $product_data['product']['_appchar_step_product_count_order'] = ($step_count)?$step_count:1;
    return $product_data;
}
add_filter('woocommerce_api_product_response','appchar_add_step_to_cart_item',10);
function appchar_add_step_to_cart_item($product_data){
    if(AppcharExtension::extensionIsActive('easy_shopping_cart')) {
        $step_count = (int) get_post_meta($product_data['id'], '_appchar_step_product_count_order', true);
    }else{
        $step_count = 1;
    }
    $product_data['_appchar_step_product_count_order'] = ($step_count)?$step_count:1;
    return $product_data;
}
add_filter('woocommerce_api_product_response','appchar_change_price_in_multi_currency',10,2);
function appchar_change_price_in_multi_currency($product_data,$product){
    global $woocommerce_wpml;
    if(AppcharExtension::extensionIsActive('multi_language') && isset($_GET['locale']) && $_GET['locale']=='en' && $woocommerce_wpml->settings[ 'enable_multi_currency' ] == WCML_MULTI_CURRENCIES_INDEPENDENT){
        if ( $product->is_type( 'variable' ) && $product->has_child() ) {
            foreach ($product_data['variations'] as $variation){
                $variation['price'] = $woocommerce_wpml->multi_currency->prices->convert_price_amount($variation['price'], "USD");
                $variation['regular_price'] = $woocommerce_wpml->multi_currency->prices->convert_price_amount($variation['regular_price'], "USD");
                $variation['sale_price'] = $woocommerce_wpml->multi_currency->prices->convert_price_amount($variation['sale_price'], "USD");
            }
        } else {
            $product_data['price'] = $woocommerce_wpml->multi_currency->prices->convert_price_amount($product_data['price'], "USD");
            $product_data['regular_price'] = $woocommerce_wpml->multi_currency->prices->convert_price_amount($product_data['regular_price'], "USD");
            $product_data['sale_price'] = $woocommerce_wpml->multi_currency->prices->convert_price_amount($product_data['sale_price'], "USD");
        }
    }
    return $product_data;
}

