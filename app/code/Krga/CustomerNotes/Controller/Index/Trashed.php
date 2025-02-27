<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Trashed implements HttpGetActionInterface
{
    protected $resultFactory;

    public function __construct(
        ResultFactory $resultFactory
    ) {
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        return $this->resultFactory->create( ResultFactory::TYPE_PAGE);
    }
}
