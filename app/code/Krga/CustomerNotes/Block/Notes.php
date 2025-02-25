<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template\Context;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Krga\CustomerNotes\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;

class Notes extends \Magento\Framework\View\Element\Template
{

    private $collectionFactory;
    private $customerCollectionFactory;
    private $tagCollectionFactory;
    private $settingsCollectionFactory;
    private $pageSize = 6;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        SettingsCollectionFactory $settingsCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->settingsCollectionFactory = $settingsCollectionFactory;
        $this->setPageSize();
    }

    public function setPageSize() {
        $pageSizeOptionObject = $this->settingsCollectionFactory->create()->addFieldToFilter('option_name', ['eq' => 'page_size'])->getFirstItem();
        if (is_object($pageSizeOptionObject)) {
            $this->pageSize = intval($pageSizeOptionObject->getOptionValue());
        }
    }
    
    public function getPageSize() {
        return $this->pageSize;
    }

    public function getNotesCollection() {
        $page = (int)$this->getRequest()->getParam('p', 1);

        $collection = $this->collectionFactory->create()
        ->addFieldToFilter('is_deleted', ['eq' => 0])
        ->setOrder('created_at', 'DESC')
        ->setPageSize($this->getPageSize())
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
