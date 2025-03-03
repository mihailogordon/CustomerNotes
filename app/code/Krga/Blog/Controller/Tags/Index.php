<?php

namespace Krga\Blog\Controller\Tags;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

class Index implements HttpGetActionInterface
{
    protected $request;
    protected $resultFactory;
    protected $messageManager;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
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
