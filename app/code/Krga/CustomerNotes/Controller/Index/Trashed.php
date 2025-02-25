<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Trashed extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        return $this->resultFactory->create( ResultFactory::TYPE_PAGE);
    }
}
