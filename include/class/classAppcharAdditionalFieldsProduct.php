<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 4/20/17
 * Time: 9:12 AM
 */
class classAppcharAdditionalFieldsProduct
{
    private $field;
    public function __construct($field_array)
    {
        $this->field = $field_array;
        if(AppcharExtension::extensionIsActive($this->field['extension_name'])){
            add_action( 'add_meta_boxes_product' , array($this,'additional_fields_to_product'));
            add_action( 'save_post', array($this,'save_additional_fields_data'));
        }
    }
    public function additional_fields_to_product()
    {
        if($this->field['field_count']!=0){
            add_meta_box('appchar_'.$this->field['name'], $this->field['title'], array($this,'additional_fields_function'));
        }
    }

    public function additional_fields_function( $post )
    {
        $extra_fields =  get_post_meta($post->ID, 'APPCHAR_EXTRA_FIELDS' , true ) ;
        $data = (!empty($extra_fields[$this->field['name']]))?$extra_fields[$this->field['name']]:array();
        for ($i = 0; $i < $this->field['field_count']; $i++) {
            $value = (!empty($data[$i]))?$data[$i]:array();
            foreach ($this->field['elements'] as $element) {
                $this->metabox_elements_creator($element, $i,$value);
            }
        }


    }

    public function save_additional_fields_data( $post_id )
    {
        // pointless if $_POST is empty (this happens on bulk edit
        if ( empty( $_POST ) )
            return $post_id;

        // verify quick edit nonce
        if ( isset( $_POST[ '_inline_edit' ] ) && ! wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) )
            return $post_id;

        // don't save for autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        
        $extra_fields =  get_post_meta($post_id, 'APPCHAR_EXTRA_FIELDS' , true ) ;
        if(!is_array($extra_fields)){
            $extra_fields = array();
        }
        $fields_count = $this->field['field_count'];
        $data = array();
        for ($i=0;$i<$fields_count;$i++){
            $data_not_set = false;
            foreach ($this->field['elements'] as $element) {
                $index = 'appchar'.$this->field['name'].$element['name'].$i;
                if(!empty($_POST[$index])){
                    $datta[$element['name']] = htmlspecialchars($_POST[$index]);
                }else{
                    if($element['require']){
                        $data_not_set = true;
                    }
                }
            }
            if(!$data_not_set){
                $datta['type']=$this->field['name'];
                $data[]= $datta;
            }
        }

        $extra_fields[$this->field['name']]= $data;
        update_post_meta($post_id, 'APPCHAR_EXTRA_FIELDS', $extra_fields );
    }

    public function metabox_elements_creator($element_array,$i,$data)
    {
        $name = 'appchar'.$this->field['name'].$element_array['name'].$i;
        $value = (!empty($data[$element_array['name']]))?htmlspecialchars_decode($data[$element_array['name']]):'';
        switch ($element_array['type']){
            case 'text':
                echo '<div class="metabox"><h3>'.$element_array['lable'].'</h3><div class="metainner"><div class="box-option">
                    <input type="text" id="" name="'.$name.'" value="'.$value.'" size="50%">
                    </div></div></div>';
                break;
            case 'textarea':
                echo '<div class="metabox"><h3>'.$element_array['lable'].'</h3><div class="metainner"><div class="box-option">';
                wp_editor($value, $name, $settings = array('textarea_name'=>$name));
                echo '</div></div></div>';
                break;
        }

    }

    public static function convert_from_old_version($post_id)
    {
        $custom_tab = get_post_meta($post_id, 'APPCHAR_CUSTOM_TAB' , true ) ;
        $extra_fields =  get_post_meta($post_id, 'APPCHAR_EXTRA_FIELDS' , true ) ;

        if($custom_tab){
            $extra_fields['custom_tab'] = $custom_tab;
            delete_post_meta($post_id,'APPCHAR_CUSTOM_TAB');
            update_post_meta($post_id, 'APPCHAR_EXTRA_FIELDS', $extra_fields );
        }

    }



}