<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 7/1/17
 * Time: 4:59 PM
 */
class AppcharPostType
{
    public $id;
    public $url;
    public $title;
    public $content;
    public $date;
    public $categories;
    public $tags;
    public $author;
    public $thumbnail;

    public function __construct($id){
        $the_post = get_post($id);
        $this->id = $id;
        $this->title = $the_post->post_title;
        $this->content = nl2br($the_post->post_content);
        $this->date = get_post_time("d M Y , H:i", false, $the_post, true);
        $this->url = get_permalink($id);
        $this->categories = get_the_category($id);
        $this->tags = (get_the_tags($id))?get_the_tags($id):array();
        $this->author = $this->get_author($the_post->post_author);
        $this->thumbnail = (get_the_post_thumbnail_url($id))?get_the_post_thumbnail_url($id):'';
    }

    public function get_author($author_id)
    {
        $author = get_userdata($author_id);
        $author_obj = array(
            "id"            => $author_id,
            "slug"          => $author->user_nicename,
            "name"          => $author->display_name,
            "first_name"    => $author->first_name,
            "last_name"     => $author->last_name,
            "nickname"      => $author->nickname,
            "url"           => $author->user_url,
            "description"   => $author->description
        );
        return $author_obj;
    }

}