<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;

class History extends \Magento\Framework\App\Action\Action
{
    protected $request;

    public function __construct(
        Context $context,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->request = $request;
    }

    public function execute()
    {
        // $noteId = (int) $this->request->getParam('note_id');

        // var_dump($noteId); exit;

        return $this->resultFactory->create( ResultFactory::TYPE_PAGE);
    }
}
