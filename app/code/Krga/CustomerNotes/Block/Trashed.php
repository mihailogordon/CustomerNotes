<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template\Context;
use Krga\CustomerNotes\Helper\Config;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;

class Trashed extends \Magento\Framework\View\Element\Template
{
    protected $configHelper;
    private $collectionFactory;

    public function __construct(
        Context $context,
        Config $configHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
        $this->collectionFactory = $collectionFactory;
    }

    public function getTrashListPageSize()
    {
        return $this->configHelper->getTrashListPageSize();
    }
    
    public function isTrashListPaginationEnabled()
    {
        return $this->configHelper->isTrashListPaginationEnabled();
    }

    public function getItemsCollection() {
        $page = (int)$this->getRequest()->getParam('p', 1);

        $collection = $this->collectionFactory->create()
        ->addFieldToFilter('is_deleted', ['eq' => 1])
        ->setOrder('created_at', 'DESC')
        ->setPageSize($this->getTrashListPageSize())
        ->setCurPage($page);

        return $collection;
    }

    public function getItems() {
        $collection = $this->getItemsCollection();

        return $collection->getItems();
    }
}
