<?php

namespace Krga\CustomerNotes\Controller\Tags;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;

class All extends Action
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
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
