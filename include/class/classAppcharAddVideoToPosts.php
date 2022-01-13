<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 7/12/17
 * Time: 2:56 PM
 */
defined('ABSPATH') or exit(__('You do not have direct access to this page.', 'appchar'));

class Appchar_Add_Video_To_Posts
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_video_meta_boxes'));
        add_action('save_post', array($this, 'save_video_url_on_post_meta'), 10, 1);
        if (AppcharExtension::extensionIsActive('video_in_product')) {
            add_filter('add_meta_to_product_endpoint', array($this, 'add_video_field_to_product_endpoint'), 20, 1);
        }
        if (AppcharExtension::extensionIsActive('video_in_post')) {
            add_filter('add_meta_to_post_endpoint', array($this, 'add_video_field_to_post_endpoint'), 20, 1);
        }

    }

    public function add_video_meta_boxes()
    {
        if (AppcharExtension::extensionIsActive('video_in_post')) {
            add_meta_box('appchar_add_video_in_post', __('video in app', 'appchar'), array($this, 'add_video_content'), 'post', 'side', 'core');

        }
        if (AppcharExtension::extensionIsActive('video_in_product')) {
            add_meta_box('appchar_add_video_in_product', __('video in app', 'appchar'), array($this, 'add_video_content'), 'product', 'side', 'core');
        }
    }

    public function add_video_content()
    {
        global $post;
        $video_url = get_post_meta($post->ID,'appchar_video_post',true);
        wp_register_script('appchar-video-to-post', APPCHAR_JS_URL.'video-to-post.js', array(), '1.0.0',true);
        wp_enqueue_script('appchar-video-to-post');
        echo '<input type="text" class="slide" name="appchar-video-to-post" id="slide" value="'.$video_url.'" />';
        echo '<a href="#" onclick="appchar_upload_win(this)">افزودن ویدیو</a>';

    }

    public function save_video_url_on_post_meta($post_id)
    {
        if(isset($_POST['appchar-video-to-post'])){
            update_post_meta($post_id, 'appchar_video_post', trim($_POST['appchar-video-to-post']));
        }
    }
    public function add_video_field_to_product_endpoint($get_product){
        $video_post =  get_post_meta($get_product['product']['id'], 'appchar_video_post' , true ) ;
        $get_product['product']['appchar_video_post']= $video_post;
        return $get_product;
    }
    public function add_video_field_to_post_endpoint($get_post){
        $video_post =  get_post_meta($get_post->id, 'appchar_video_post' , true ) ;
        $get_post->appchar_video_post= $video_post;
        return $get_post;
    }
}

new Appchar_Add_Video_To_Posts();