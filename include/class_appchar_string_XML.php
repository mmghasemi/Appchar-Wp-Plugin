<?php

class Appchar_String_XML{
    private $XML_file;
    public function __construct(){
        $this->XML_file = WP_PLUGIN_DIR."/appchar/assets/strings.xml";
    }
    public function load_file(){
        $load_file=simplexml_load_file($this->XML_file) or die(__('Error: Cannot create object','appchar'));
        return $load_file;
    }
    public function get_data_into_array(){
        $elements = $this->load_file();
        $items = array();
        foreach ($elements->string as $child) {
            $role = (string)$child->attributes();
            $items[$role] = (string)$child;
        }
        return $items;
    }
    public function create_setting_page(){
        $elements = $this->get_data_into_array();
        foreach ($elements as $key=>$element){
            echo "<tr><th>$key</th><td><input type='text' name='$key' value='$element'></td></tr>";
        }
    }
    public function update_xml($items){
        $elements = $this->load_file();
        foreach ($elements->string as $key1=>$child){
            $role = (string)$child->attributes();
            foreach ($items as $key2=>$item){
                if($key2 == $role){
                    $child[0] = $item;
                }
            }
        }
        $elements->asXml($this->XML_file);
    }
}