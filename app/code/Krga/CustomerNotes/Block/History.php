<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template\Context;
use Krga\CustomerNotes\Model\ResourceModel\History\CollectionFactory;
use Magento\Framework\App\RequestInterface;

class History extends \Magento\Framework\View\Element\Template
{
    protected $request;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);$this->request = $request;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
    }

    public function getNoteId()
    {
        return (int) $this->request->getParam('note_id');
    }

    public function getNoteHistoryItems()
    {
        $noteId = $this->getNoteId();
        
        $collection = $this->collectionFactory->create()
        ->addFieldToFilter('note_id', ['eq' => $noteId])
        ->setOrder('modified_at', 'DESC');

        return $collection->getItems();
    }
}
