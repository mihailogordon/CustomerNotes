<?php

namespace Krga\Blog\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;
use Krga\Blog\Helper\Config;
use Krga\Blog\Model\ResourceModel\Post\CollectionFactory;
use Krga\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class Posts extends Template
{
    protected $configHelper;
    protected $collectionFactory;
    protected $tagCollectionFactory;
    protected $tagRelationCollectionFactory;

    public function __construct(
        Context $context,
        Config $configHelper,
        CollectionFactory $collectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
        $this->collectionFactory = $collectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
    }

    public function getPageSize() {
        return $this->configHelper->getListPageSize();
    }
    
    public function isPaginationEnabled() {
        return $this->configHelper->isListPaginationEnabled();
    }

    public function isListFeaturedImageEnabled() {
        return $this->configHelper->isListFeaturedImageEnabled();
    }
    
    public function isListTagsFilterEnabled() {
        return $this->configHelper->isListTagsFilterEnabled();
    }
    
    public function isListTagsEnabled() {
        return $this->configHelper->isListTagsEnabled();
    }

    public function getCollection() {
        $page = (int)$this->getRequest()->getParam('p', 1);
        $postIds = array();
        $tagId = (int)$this->getRequest()->getParam('tag_id');

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('is_deleted', ['eq' => 0])
            ->setOrder('created_at', 'DESC')
            ->setPageSize($this->getPageSize())
            ->setCurPage($page);

        if ($tagId) {
            $tagRelationItems = $this->tagRelationCollectionFactory->create()
            ->addFieldToFilter('tag_id', ['eq' => $tagId])->getItems();
            if (is_array($tagRelationItems) && count($tagRelationItems)) {
                foreach ($tagRelationItems as $tagRelationItem) {
                    $postIds[] = $tagRelationItem->getPostId();
                }
            }

            $collection->addFieldToFilter('post_id', ['in' => $postIds]);
        }

        return $collection;
    }

    public function getPosts() {
        $collection = $this->getCollection();
        
        return $collection->getItems();
    }

    public function getAllTags() {
        return $this->tagCollectionFactory->create()->getItems();
    }

    public function getImageUrl($imagePath)
    {
        if (!$imagePath) {
            return '';
        }

        $imagePath = ltrim($imagePath, '/'); // Clean up leading slash if present
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'posts/' . $imagePath;
    }
}
