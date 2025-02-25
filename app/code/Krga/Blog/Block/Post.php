<?php

namespace Krga\Blog\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Krga\Blog\Model\ResourceModel\View\CollectionFactory as ViewCollectionFactory;

class Post extends Template {
    protected $_viewCollectionFactory = null;

    public function __construct(
        Context $context, 
        ViewCollectionFactory $viewCollectionFactory,
        array $data = [])
    {
        $this->_viewCollectionFactory = $viewCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getCollection() {
        $viewCollection = $this->_viewCollectionFactory->create();
        $viewCollection->addFieldToSelect('*')->load();

        // $viewCollection->addFieldToFilter('post_title', array('like' => 'post%'));
        // $viewCollection->getSelect()->order('post_content DESC');
        // var_dump($viewCollection->getItems()); exit;
        return $viewCollection->getItems();
    }
    
    public function getPostCategories() {
        $postId = (int) $this->getRequest()->getParam('id');
        $result = false;
    
        if ($postId) {
            $viewCollection = $this->_viewCollectionFactory->create();
    
            $viewCollection->getSelect()
                ->join(
                    ['blog_category_rel' => 'blog_blog_category'],
                    'main_table.post_id = blog_category_rel.post_id',
                    []
                )
                ->join(
                    ['categories' => 'blog_categories'],
                    'blog_category_rel.category_id = categories.category_id',
                    ['name']
                )
                ->where('main_table.post_id = ?', $postId)
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns(['categories.name']);
    
            $result = $viewCollection->getData();
        }

        var_dump($result); exit;
    
        return $result;
    }
    
    
    
    public function getPostData() {
        $postId = (int) $this->getRequest()->getParam('id');
        $result = false;

        if( $postId ) {
            $viewCollection = $this->_viewCollectionFactory->create();
            $viewCollection->addFieldToFilter('post_id', $postId);
            $result = $viewCollection->getItems();
        }
        
        return $result;
    }

    public function getPostUrl($postId) {
        return $this->getUrl('blog/index/post', array('_secure' => true, 'id' => $postId));
    }
}