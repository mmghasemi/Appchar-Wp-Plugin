<?php
/**
 * extension name: class productCustomFields
 * description: اضافه کردن فرم به صفحه محصول در اپ
 * Created by PhpStorm.
 * author: ali shojaei
 * Date: 5/29/18
 * Time: 9:11 AM
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
if ( ! class_exists( 'productCustomFields' ) ) :

class productCustomFields
{
    protected static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_filter('add_meta_to_product_endpoint',array($this,'insert_product_custom_fields'),10,1);
    }


    public function insert_product_custom_fields($product_data)
    {
        $product_data['product']['product_custom_fields'] = [];
        if(get_post_meta($product_data['product']['id'],'_wcpa_product_meta',true)){
            $wcpa_pt_forms_id = get_post_meta($product_data['product']['id'],'_wcpa_product_meta',true);
        }
        $term_list = wp_get_post_terms($product_data['product']['id'],'product_cat',array('fields'=>'ids'));
        foreach ($term_list as $term) {
            $args = array(
                'post_type' => 'wcpa_pt_forms',
                'post_status' => 'publish',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $term
                    )
                )
            );
            $postslist = get_posts($args);
            foreach ($postslist as $post){
                $wcpa_pt_forms_id[] = $post->ID;
            }
            if(isset($wcpa_pt_forms_id)){
                $wcpa_pt_forms_id = array_unique($wcpa_pt_forms_id);
            }
        }
        if(isset($wcpa_pt_forms_id)) {
            foreach ($wcpa_pt_forms_id as $item) {
                $form = (json_decode(get_post_meta($item, '_wcpa_fb-editor-data', true)));
                foreach ($form as $element)
                    $product_data['product']['product_custom_fields'][] = $element;
            }
        }
        return $product_data;
    }
}

endif;


function wooCustomFields() {
    return productCustomFields::instance();
}

wooCustomFields();
