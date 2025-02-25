<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\Item;

use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\TagRelationFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;

class Save extends \Magento\Backend\App\Action
{
    private $itemFactory;
    private $tagRelationFactory;
    private $noteResourceModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        NoteFactory $itemFactory,
        TagRelationFactory $tagRelationFactory,
        NoteResourceModel $noteResourceModel
    ) {
        $this->itemFactory = $itemFactory;
        $this->tagRelationFactory = $tagRelationFactory;
        $this->noteResourceModel = $noteResourceModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $noteId = $data['note_id'] ?? null;
        $tagIds = is_array($data['tag_ids']) ? $data['tag_ids'] : [];

        $note = $this->itemFactory->create();
        if ($noteId) {
            $this->noteResourceModel->load($note, $noteId);
        }

        $this->noteResourceModel->save($note);
        $noteId = $note->getId();

        $this->tagRelationFactory->create()->assignTags($noteId, $tagIds);

        $this->messageManager->addSuccessMessage(__('The note has been saved.'));
        return $this->resultRedirectFactory->create()->setPath('notes/index/index');
    }

}
