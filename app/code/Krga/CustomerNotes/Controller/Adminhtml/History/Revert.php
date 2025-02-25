<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\History;

use Krga\CustomerNotes\Model\HistoryFactory;
use Krga\CustomerNotes\Model\ResourceModel\History as HistoryResource;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResource;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotDeleteException;

class Revert extends Action
{
    private $historyFactory;
    private $historyResource;
    private $noteFactory;
    private $noteResource;
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        HistoryFactory $historyFactory,
        HistoryResource $historyResource,
        NoteFactory $noteFactory,
        NoteResource $noteResource,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->noteFactory = $noteFactory;
        $this->noteResource = $noteResource;
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

        $noteId = $history->getNoteId();
        $note = $this->noteFactory->create();
        $this->noteResource->load($note, $noteId);

        try {
            $note->setNote($history->getPreviousNote());
            $this->noteResource->save($note);
            $this->messageManager->addSuccessMessage(__('The history has been reverted successfully.'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage(__('Could not revert the history: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('notes/history/index');
    }
}
