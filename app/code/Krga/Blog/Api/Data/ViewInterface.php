<?php 

namespace Krga\Blog\Api\Data;

interface ViewInterface {
    const POST_ID = 'post_id';
    const POST_TITLE = 'post_title';
    const POST_CONTENT = 'post_content';
    const CREATED_AT = 'created_at';

    public function getPostId();

    public function setPostId($post_id);

    public function getPostTitle();

    public function setPostTitle($post_title);

    public function getPostContent();

    public function setPostContent($post_content);

    public function getPostCreatedAt();
    
    public function setPostCreatedAt($created_at);
}