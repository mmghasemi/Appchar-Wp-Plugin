<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/4/17
 * Time: 10:26 AM
 */
class PB_Video_Element extends PB_Element_Class implements PB_Element_Interface{

    public $type;
    public $dashicon;
    public $title;
    public $values;

    protected static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        $this->type = 'video';
        $this->dashicon = 'dashicons-editor-video';
        $this->title = __('video element','appchar');
        $this->values = '';

        add_filter('pb_element_list_array',array($this,'add_default_object'));
    }

    public function add_default_object($default_element)
    {
        $default_element['video'] = $this;
        return $default_element;
    }

    public function get_download_link_from_aparat_url($url)
    {
        $content = file_get_contents($url);
        $first_step = explode( '<li data-ec="download" data-el="480p" class="action download-link" onclick="setVideoVisit()">' , $content );
        $second_step = explode("</li>" , $first_step[1] );//<li data-ec="download" data-el="480p" class="action download-link" onclick="setVideoVisit()">
        //<a href="https://as10.asset.aparat.com/aparat-video/b097c053ce4b9197e2e56eebb01a5c268092119-480p__18347.mp4" title="دانلود ویدیو اجرای زندهٔ دلبرِ امید حاجیلی" class="" target="_blank" rel="nofollow" onmouseover="this.title ='' ;"><span class="label">دانلود کیفیت 480p </span></a>                                                </li>
        $third_step = explode('href="', $second_step[0] );
        $fourth_step = explode('"', $third_step[1] );
        return $fourth_step[0];
    }

    public function get_thumbnail_from_aparat_url($url)
    {
        $content = file_get_contents($url);
        $first_step = explode( 'og:image:secure_url' , $content );
        $second_step = explode('content="' , $first_step[1] );
        $third_step = explode('"', $second_step[1] );
        return $third_step[0];
    }


    public function save_element($index)
    {
        $arr = array(
            'type'  => 'video',
            'aparat_url' => $_POST['video_'.$index.'_element'],
            'video_url' => $this->get_download_link_from_aparat_url($_POST['video_'.$index.'_element']),
            'thumbnail' => $this->get_thumbnail_from_aparat_url($_POST['video_'.$index.'_element']),
        );
        return $arr;
    }
    public function generate_html_field($row_num,$value)
    {
        $index_field = '<input type="hidden" name="post_builder_index[]" value="'.$row_num.'">';
        $type_field = '<input type="hidden" name="post_builder_type[]" value="video">';
        $input_value = (isset($value['aparat_url']))?$value['aparat_url']:'';
        echo $index_field.$type_field.'<label>لینک آپارات</label><input name="video_'.$row_num.'_element" value="'.$input_value.'">';
    }
}