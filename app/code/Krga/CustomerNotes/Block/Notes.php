<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template\Context;
use Krga\CustomerNotes\Helper\Config;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

class Notes extends \Magento\Framework\View\Element\Template
{
    protected $configHelper;
    private $collectionFactory;
    private $customerCollectionFactory;
    private $tagCollectionFactory;

    public function __construct(
        Context $context,
        Config $configHelper,
        CollectionFactory $collectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
        $this->collectionFactory = $collectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
    }

    public function getListPageSize() {
        return $this->configHelper->getListPageSize();
    }
    
    public function isListPaginationEnabled() {
        return $this->configHelper->isListPaginationEnabled();
    }
    
    public function isListTagsEnabled() {
        return $this->configHelper->isListTagsEnabled();
    }
    
    public function isListAddNoteFormEnabled() {
        return $this->configHelper->isListAddNoteFormEnabled();
    }

    public function getNotesCollection() {
        $page = (int)$this->getRequest()->getParam('p', 1);

        $collection = $this->collectionFactory->create()
        ->addFieldToFilter('is_deleted', ['eq' => 0])
        ->setOrder('created_at', 'DESC')
        ->setPageSize($this->getListPageSize())
        ->setCurPage($page);

        return $collection;
    }

    public function getNotes() {
        $collection = $this->getNotesCollection();
        
        return $collection->getItems();
    }
    
    public function getCustomers()
    {
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->addAttributeToSelect(['entity_id', 'firstname', 'lastname', 'email']);

        return $customerCollection->getItems();
    }

    public function getTags() {
        return $this->tagCollectionFactory->create()->getItems();
    }
}
