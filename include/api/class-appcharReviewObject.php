<?php

/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/18/17
 * Time: 12:19 PM
 */
class appcharReviewObject
{
    public $id;
    public $created_at;
    public $review;
    public $rating;
    public $reviewer_name;
    public $reviewer_email;
    public $verified;
    public $review_parent;
    public $child = array();

    public function __construct($comment)
    {
        $type = "";
        $numbers = array();
        if(preg_match('/^[1-9][0-1][0-9]{8,8}$/', $comment->comment_author)) {
            $type = "number";
            $numbers = str_split($comment->comment_author);
            for($i = 0; $i < count($numbers); $i++) {
                if($i == 3 || $i == 4 || $i == 5 || $i == 6) {
                    $numbers[$i] = "*";
                }
            }
        }
        $this->id               = intval( $comment->comment_ID );
        $this->created_at       = $comment->comment_date_gmt;
        $this->review           = $comment->comment_content;
        $this->rating           = get_comment_meta( $comment->comment_ID, 'rating', true );
        $this->reviewer_name    = $type === "number" ? implode("", $numbers) : $comment->comment_author;
        $this->reviewer_email   = $comment->comment_author_email;
        $this->verified         = wc_review_is_from_verified_owner( $comment->comment_ID );
        $this->review_parent    = $comment->comment_parent;
        $comments = get_approved_comments( $comment->comment_post_ID , array('parent'=>$comment->comment_ID) );
        foreach ($comments as $comment)
        $this->child[]            = new self($comment);
    }

    private function get_child()
    {

    }

}