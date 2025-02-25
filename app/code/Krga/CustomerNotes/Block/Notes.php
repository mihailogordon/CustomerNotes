<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template\Context;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Krga\CustomerNotes\Model\Settings;

class Notes extends \Magento\Framework\View\Element\Template
{

    private $collectionFactory;
    private $customerCollectionFactory;
    private $tagCollectionFactory;
    private $settings;

    private $pageSize;
    private $showPaginationOnList;
    private $showTagsOnList;
    private $showAddNoteFormOnList;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        Settings $settings,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->settings = $settings;
    }

    public function getPageSize() {
        if ($this->pageSize === null) {
            $this->pageSize = $this->settings->getOption('page_size', 6);
        }

        return $this->pageSize;
    }
    
    public function getShowPaginationOnList() {
        if ($this->showPaginationOnList === null) {
            $this->showPaginationOnList = $this->settings->getOption('show_pagination_on_list', 'yes') !== 'no';
        }

        return $this->showPaginationOnList;
    }
    
    public function getShowTagsOnList() {
        if ($this->showTagsOnList === null) {
            $this->showTagsOnList = $this->settings->getOption('show_tags_on_list', 'yes') !== 'no';
        }

        return $this->showTagsOnList;
    }
    
    public function getShowAddNoteFormOnList() {
        if ($this->showAddNoteFormOnList === null) {
            $this->showAddNoteFormOnList = $this->settings->getOption('show_add_note_form_on_list', 'yes') !== 'no';
        }

        return $this->showAddNoteFormOnList;
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
