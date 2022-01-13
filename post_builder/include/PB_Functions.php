<?php

function get_element_list(){
    $element_list = array();
    return apply_filters('pb_element_list_array',$element_list);
}

?>