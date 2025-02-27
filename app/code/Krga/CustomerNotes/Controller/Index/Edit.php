<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

class Edit implements HttpGetActionInterface
{
    protected $request;
    protected $messageManager;
    protected $resultFactory;

    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $noteId = (int) $this->request->getParam('note_id');

        if ($noteId) {
            $this->messageManager->addSuccessMessage(__('Editing Note ID: %1', $noteId));
        } else {
            $this->messageManager->addErrorMessage(__('No Note ID passed.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
