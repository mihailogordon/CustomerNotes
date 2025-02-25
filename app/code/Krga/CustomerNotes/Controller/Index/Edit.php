<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;

class Edit extends Action
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
        $noteId = (int) $this->request->getParam('note_id');

        if ($noteId) {
            $this->messageManager->addSuccessMessage(__('Editing Note ID: %1', $noteId));
        } else {
            $this->messageManager->addErrorMessage(__('No Note ID passed.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
