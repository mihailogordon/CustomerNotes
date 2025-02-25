<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\Item;

use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResource;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class Trash extends Action
{
    private $noteFactory;
    private $noteResource;
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        NoteFactory $noteFactory,
        NoteResource $noteResource,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->noteFactory = $noteFactory;
        $this->noteResource = $noteResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $noteId = $this->getRequest()->getParam('note_id');

        if (!$noteId) {
            $this->messageManager->addErrorMessage(__('Invalid note ID.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/index');
        }

        $note = $this->noteFactory->create();
        $this->noteResource->load($note, $noteId);

        if (!$note->getId()) {
            $this->messageManager->addErrorMessage(__('The note no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/index');
        }

        try {
            $note->setIsDeleted(1);
            $this->noteResource->save($note);

            $this->messageManager->addSuccessMessage(__('The note has been moved to trash.'));
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage(__('Could not move the note to trash: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('notes/index/index');
    }
}
