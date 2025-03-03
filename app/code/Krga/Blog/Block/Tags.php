<?php

namespace Krga\Blog\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use Krga\Blog\Helper\Config;
use Krga\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

class Tags extends Template
{
    protected $request;
    protected $configHelper;
    protected $tagCollectionFactory;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        Config $configHelper,
        TagCollectionFactory $tagCollectionFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->configHelper = $configHelper;
        $this->tagCollectionFactory = $tagCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getTagListPageSize()
    {
        return $this->configHelper->getTagListPageSize();
    }
    
    public function isTagListPaginationEnabled()
    {
        return $this->configHelper->isTagListPaginationEnabled();
    }

    public function getTagsCollection() {
        $page = (int)$this->getRequest()->getParam('p', 1);
        
        $collection = $this->tagCollectionFactory->create()
        ->setPageSize($this->getTagListPageSize())
        ->setCurPage($page);

        return $collection;
    }

    public function getAllTags() {
        return $this->getTagsCollection()->getItems();
    }
    
}
