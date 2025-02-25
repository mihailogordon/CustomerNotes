<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template\Context;
use Krga\CustomerNotes\Helper\Config;
use Krga\CustomerNotes\Model\ResourceModel\History\CollectionFactory;
use Magento\Framework\App\RequestInterface;

class History extends \Magento\Framework\View\Element\Template
{
    protected $request;
    protected $configHelper;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        RequestInterface $request,
        Config $configHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);$this->request = $request;
        $this->request = $request;
        $this->configHelper = $configHelper;
        $this->collectionFactory = $collectionFactory;
    }

    public function getHistoryListPageSize()
    {
        return $this->configHelper->getHistoryListPageSize();
    }
    
    public function isHistoryListPaginationEnabled()
    {
        return $this->configHelper->isHistoryListPaginationEnabled();
    }

    public function getNoteId()
    {
        return (int) $this->request->getParam('note_id');
    }

    public function getNoteHistoryCollection()
    {
        $page = (int)$this->getRequest()->getParam('p', 1);
        $noteId = $this->getNoteId();
        
        $collection = $this->collectionFactory->create()
        ->addFieldToFilter('note_id', ['eq' => $noteId])
        ->setOrder('modified_at', 'DESC')
        ->setPageSize($this->getHistoryListPageSize())
        ->setCurPage($page);

        return $collection;
    }
    
    public function getNoteHistoryItems()
    {
        $collection = $this->getNoteHistoryCollection();

        return $collection->getItems();
    }
}
