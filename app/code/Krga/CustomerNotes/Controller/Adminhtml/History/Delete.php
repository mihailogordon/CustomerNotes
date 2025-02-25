<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\History;

use Krga\CustomerNotes\Model\HistoryFactory;
use Krga\CustomerNotes\Model\ResourceModel\History as HistoryResource;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete extends Action
{
    private $historyFactory;
    private $historyResource;
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        HistoryFactory $historyFactory,
        HistoryResource $historyResource,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $historyId = $this->getRequest()->getParam('history_id');

        if (!$historyId) {
            $this->messageManager->addErrorMessage(__('Invalid history ID.'));
            return $this->resultRedirectFactory->create()->setPath('notes/history/index');
        }

        $history = $this->historyFactory->create();
        $this->historyResource->load($history, $historyId);

        if (!$history->getId()) {
            $this->messageManager->addErrorMessage(__('The history no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('notes/history/index');
        }

        try {
            $this->historyResource->delete($history);
            $this->messageManager->addSuccessMessage(__('The history has been deleted permanently.'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage(__('Could not delete the history: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('notes/history/index');
    }
}
