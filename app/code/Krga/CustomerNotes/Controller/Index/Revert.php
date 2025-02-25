<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResource;
use Krga\CustomerNotes\Model\HistoryFactory;
use Krga\CustomerNotes\Model\ResourceModel\History as HistoryResource;

class Revert extends Action
{
    protected $noteFactory;
    protected $noteResource;
    protected $historyFactory;
    protected $historyResource;
    protected $resultRedirectFactory;
    protected $cacheTypeList;
    protected $cacheFrontendPool;

    public function __construct(
        Context $context,
        NoteFactory $noteFactory,
        NoteResource $noteResource,
        HistoryFactory $historyFactory,
        HistoryResource $historyResource,
        RedirectFactory $resultRedirectFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        parent::__construct($context);
        $this->noteFactory = $noteFactory;
        $this->noteResource = $noteResource;
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function execute()
    {
        $noteId = $this->getRequest()->getParam('note_id');

        if (!$noteId) {
            $this->messageManager->addErrorMessage(__('Invalid note ID.'));
            return $this->resultRedirectFactory->create()->setPath('notes');
        }

        $note = $this->noteFactory->create();
        $this->noteResource->load($note, $noteId);

        if (!$note->getId()) {
            $this->messageManager->addErrorMessage(__('The note no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('notes');
        }
        
        $historyId = $this->getRequest()->getParam('history_id');

        if (!$historyId) {
            $this->messageManager->addErrorMessage(__('Invalid history ID.'));
            return $this->resultRedirectFactory->create()->setPath('notes');
        }

        $history = $this->historyFactory->create();
        $this->historyResource->load($history, $historyId);

        if (!$history->getId()) {
            $this->messageManager->addErrorMessage(__('The history no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('notes');
        }

        try {
            $note->setNote($history->getPreviousNote());
            $this->noteResource->save($note);

            // Ensure cache is fully cleared
            $types = ['block_html', 'full_page', 'layout', 'translate'];
            foreach ($types as $type) {
                $this->cacheTypeList->cleanType($type);
            }
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }

            $this->messageManager->addSuccessMessage(__('The note has been restored successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while restoring the note: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('notes');
    }
}
