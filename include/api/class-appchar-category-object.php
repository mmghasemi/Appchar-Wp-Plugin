<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/18/17
 * Time: 12:19 PM
 */
class class_appchar_category_object
{
    public $id;
    public $name;
    public $slug;
    public $parent;
    public $description;
    public $display;
    public $image;
    public $count;

    public function __construct($term_id)
    {
        $cat_obj = get_category($term_id,ARRAY_A);;
        $this->id = $cat_obj['term_id'];
        $this->name = $cat_obj['name'];
        $this->slug = $cat_obj['slug'];
        $this->parent = $cat_obj['parent'];
        $this->description = $cat_obj['description'];
        $this->display = 'default';
        $this->image = '';
        $this->count = $cat_obj['count'];
    }

}