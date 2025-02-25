<?php

namespace Krga\Blog\Model;

use Krga\Blog\Api\Data\ViewInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class View extends AbstractModel implements ViewInterface, IdentityInterface {
    const CACHE_TAG = 'krga_blog_view';

    public function _construct() {
        $this->_init('Krga\Blog\Model\ResourceModel\View');
    }

    public function getPostId() {
        return $this->getData(self::POST_ID);
    }
    
    public function setPostId($post_id) {
        return $this->setData(self::POST_ID, $post_id);
    }
    
    public function getPostTitle() {
        return $this->getData(self::POST_TITLE);
    }
    
    public function setPostTitle($post_title) {
        return $this->setData(self::POST_TITLE, $post_title);
    }
    
    public function getPostContent() {
        return $this->getData(self::POST_CONTENT);
    }
    
    public function setPostContent($post_content) {
        return $this->setData(self::POST_CONTENT, $post_content);
    }
    
    public function getPostCreatedAt() {
        return $this->getData(self::CREATED_AT);
    }
    
    public function setPostCreatedAt($created_at) {
        return $this->setData(self::CREATED_AT, $created_at);
    }

    public function getIdentities() {
        return array(self::CACHE_TAG . '_' . $this->getPostId());
    }
}