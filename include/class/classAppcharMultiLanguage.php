<?php

/**
 * Created by PhpStorm.
 * User: alishojaee
 * Date: 4/5/17
 * Time: 12:39 PM
 */
class classAppcharMultiLanguage
{
    public function __construct()
    {
        $this->set_default_to_homepage_builder();
        if(AppcharExtension::extensionIsActive('multi_language') || AppcharExtension::extensionIsActive('ios_for_publish')) {
            add_filter('appchar_get_options_v2',array($this,'get_language_for_endpoint'));
        }
    }
    //wp_icl_translations
    //wp_icl_locale_map
    public function get_language_for_endpoint($json)
    {
        if ( function_exists('icl_object_id') ) {
            foreach ($this->get_language_list() as $language){
                $language_list[]= array(
                    'code'  => $language,
                    'name'  => $this->get_language_name($language),
                );
            }
            $json['languages_list']= $language_list;
            $json['default_language']= ICL_LANGUAGE_CODE;
        }
        return $json;
    }
    public function get_language_name($fromlag,$tolang=ICL_LANGUAGE_CODE)
    {
        global $wpdb;
        $tbl_name  = $wpdb->prefix.'icl_languages_translations';
        if ($wpdb->get_var("show tables like '{$tbl_name}'") == $tbl_name) {
            $query = "SELECT * FROM {$tbl_name} WHERE language_code='$fromlag' AND display_language_code='$tolang'";
            $results = $wpdb->get_results($query);
            $lng_name = $results[0]->name;
        }else{
            $lng_name = 'fa';
        }
        return $lng_name;
    }
    public function get_language_list(){
        global $wpdb;
        $tbl_name  = $wpdb->prefix.'icl_locale_map';
        if ($wpdb->get_var("show tables like '{$tbl_name}'") == $tbl_name) {
            $query = "select * from {$tbl_name}";
            $language_lists = $wpdb->get_results($query);
            $lag_list = array();
            foreach ($language_lists as $language_list) {
                $lag_list[] = $language_list->code;
            }
        }else{
            $lag_list = array();
        }
        return $lag_list;
    }
    function appchar_get_translated_term($term_id, $taxonomy, $language) {
        global $sitepress;

        $translated_term_id = icl_object_id(intval($term_id), $taxonomy, true, $language);

        remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
        $translated_term_object = get_term_by('id', intval($translated_term_id), $taxonomy);
        add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );

        return $translated_term_object;
    }

    public function set_default_to_homepage_builder()
    {
        foreach ($this->get_language_list() as $language){
            if(!get_option('appchar_homepage_config2_'.$language, false) && get_option('appchar_homepage_config2', false)){
                update_option('appchar_homepage_config2_'.$language,get_option('appchar_homepage_config2', false));
            }
        }
    }

}