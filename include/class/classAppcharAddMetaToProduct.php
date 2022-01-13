<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 4/20/17
 * Time: 9:57 AM
 */
require_once 'classAppcharAdditionalFieldsProduct.php';
class classAppcharAddMetaTOProduct
{
    private $fields;
    public function __construct(){
        $options = get_option('appchar_general_setting');
        $custom_tab_count = (isset($options['custom_tab_count']))?$options['custom_tab_count']:get_option('appchar_custom_tab_count',0);
        $this->fields = array(
            array(
                'extension_name' => 'custom_tab',
                'name'  => 'text',
                'title' => __('Custom Tab','appchar'),
                'is_extension'  => true,
                'field_count'   => $custom_tab_count,
                'elements'      => array(
                    array(
                        'type'  => "text",
                        'lable' => "عنوان",
                        'name'  => "title",
                        'require'=>true,
                    ),
                    array(
                        'type'  => "textarea",
                        'lable' => "توضیحات",
                        'name'  => "desc",
                        'require'=>true,
                    ),

                )
            ),
            array(
                'extension_name' => 'address_seller',
                'name'  => 'address',
                'title' => __('Address Seller','appchar'),
                'is_extension'  => true,
                'field_count'   => 1,
                'elements'      => array(
                    array(
                        'type'  => "text",
                        'lable' => __("title","appchar"),
                        'name'  => "title",
                        'require'=>true,
                    ),
                    array(
                        'type'  => "textarea",
                        'lable' => __("description",'appchar'),
                        'name'  => "desc",
                        'require'=>true,
                    ),
                    array(
                        'type'  => "text",
                        'lable' => __("tel (please seperate each tel with ',')",'appchar'),
                        'name'  => "tel",
                        'require'=>true,
                    ),
                    array(
                        'type'  => "text",
                        'lable' => __("Geo Coordinates (for Example: 32.676649,51.7140628)",'appchar'),
                        'name'  => "geo_coordinates",
                        'require'=>false,

                    ),
                )
            ),
        );
        foreach ($this->fields as $field){
            new classAppcharAdditionalFieldsProduct($field);
        }
        add_filter('add_meta_to_product_endpoint',array($this,'appchar_add_extra_field_to_product_endpoint'),20,1);

    }
    public function appchar_add_extra_field_to_product_endpoint($get_product)
    {
        classAppcharAdditionalFieldsProduct::convert_from_old_version($get_product['product']['id']);
        $extra_fields =  get_post_meta($get_product['product']['id'], 'APPCHAR_EXTRA_FIELDS' , true ) ;
        $custom_fields = array();
        if($extra_fields) {
            foreach ($extra_fields as $extra_field) {
                foreach ($extra_field as $field) {
                    if ((isset($field['type']) && $field['type'] == 'address') && (isset($field['geo_coordinates']) && $field['geo_coordinates'] != '')) {
                        $geo = explode(",", $field['geo_coordinates']);
                        unset($field['geo_coordinates']);
                        $field['lat'] = floatval($geo[0]);
                        $field['lng'] = floatval($geo[1]);
                    }
                    if ((isset($field['type']) && $field['type'] == 'address') && (isset($field['tel']) && $field['tel'] != '')) {
                        $telephone = explode(",", $field['tel']);
                        $tels = array();
                        unset($field['tel']);
                        foreach ($telephone as $tel) {
                            if (preg_match("/^0[0-9]{10}$/", $tel)) { //for validate telephone
                                $tels[] = $tel;
                            }
                        }
                        $field['tel'] = $tels;
                    }
                    $field['desc'] = (isset($field['desc']))?html_entity_decode($field['desc']):'';
                    $custom_fields[] = $field;
                }
            }
        }

        $get_product['product']['custom_tabs'] = (!empty($custom_fields)) ? $custom_fields : array();
        //$get_product['product']['appchar_extra_fields']= $extra_fields;

        return $get_product;
    }
}