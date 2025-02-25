<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template\Context;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;

class Trashed extends \Magento\Framework\View\Element\Template
{

    private $collectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    public function getItems() {
        $collection = $this->collectionFactory->create()
        ->addFieldToFilter('is_deleted', ['eq' => 1])
        ->setOrder('created_at', 'DESC');

        return $collection->getItems();
    }
}
