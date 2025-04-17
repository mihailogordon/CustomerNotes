<?php

namespace Krga\Blog\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Krga\Blog\Helper\Config;
use Krga\Blog\Model\TagFactory;
use Krga\Blog\Model\ResourceModel\Tag as TagResourceModel;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\Blog\Model\PostFactory;
use Krga\Blog\Model\ResourceModel\Post as PostResourceModel;

class Tag extends Template
{
    protected $request;
    protected $configHelper;
    protected $tagFactory;
    protected $tagResourceModel;
    protected $tagRelationCollectionFactory;
    protected $postFactory;
    protected $postResourceModel;
    private $tag = null;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        Config $configHelper,
        TagFactory $tagFactory,
        TagResourceModel $tagResourceModel,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        PostFactory $postFactory,
        PostResourceModel $postResourceModel,
        array $data = []
    ) {
        $this->request = $request;
        $this->configHelper = $configHelper;
        $this->tagFactory = $tagFactory;
        $this->tagResourceModel = $tagResourceModel;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->postFactory = $postFactory;
        $this->postResourceModel = $postResourceModel;
        parent::__construct($context, $data);
    }

    public function getTagSinglePageSize()
    {
        return $this->configHelper->getTagSinglePageSize();
    }
    
    public function isTagSinglePaginationEnabled()
    {
        return $this->configHelper->isTagSinglePaginationEnabled();
    }

    public function getTagId()
    {
        return (int) $this->request->getParam('tag_id');
    }

    private function getTag()
    {
        if ($this->tag === null) {
            $tagId = $this->getTagId();

            if ($tagId) {
                $this->tag = $this->tagFactory->create();
                $this->tagResourceModel->load($this->tag, $tagId);
            }
        }

        return $this->tag;
    }

    public function getTagName()
    {
        $tag = $this->getTag();
        return ($tag && $tag->getId()) ? $tag->getTagName() : '';
    }

    public function getTagPostsCollection() {
        $page = (int)$this->getRequest()->getParam('p', 1);
        $tag = $this->getTag();
        $tagRelations = array();

        if ($tag) {
            $tagRelations = $this->tagRelationCollectionFactory->create()
            ->addFieldToFilter('tag_id', array('eq' => $tag->getTagId()))
            ->setPageSize($this->getTagSinglePageSize())
            ->setCurPage($page);
        }

        return $tagRelations;
    }

    public function getTagPosts() {
        $tagRelations = $this->getTagPostsCollection()->getItems();
        $tagPosts = array();

        if (is_array($tagRelations) && count($tagRelations) > 0) {
            foreach($tagRelations as $tagRelation) {
                $post = $this->postFactory->create();
                $this->postResourceModel->load($post, $tagRelation->getPostId());
                $tagPosts[] = $post;
            }
        }

        return $tagPosts;
    }

    public function getImageUrl($imagePath)
    {
        if (!$imagePath) {
            return '';
        }

        $imagePath = ltrim($imagePath, '/');
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'posts/' . $imagePath;
    }
}
