<?php

namespace Krga\CustomerNotes\Controller\Tags;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;

class Index extends Action
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
        $tagId = (int) $this->request->getParam('tag_id');

        if ($tagId) {
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        } else {
            $this->messageManager->addErrorMessage(__('No Tag ID passed.'));
        }   
    }
}
