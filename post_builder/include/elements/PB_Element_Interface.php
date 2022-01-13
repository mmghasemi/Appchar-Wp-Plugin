<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/4/17
 * Time: 10:25 AM
 */
interface PB_Element_Interface{

    //this function retrun default object
    function add_default_object($default_element);

    function save_element($index);


    function generate_html_field($row_num,$value);

//    function set_value();

}