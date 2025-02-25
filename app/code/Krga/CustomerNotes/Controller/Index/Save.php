<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Krga\CustomerNotes\Model\TagRelationFactory;

class Save extends Action
{
    protected $request;
    protected $noteFactory;
    protected $noteResourceModel;
    protected $tagRelationFactory;
    protected $messageManager;
    protected $cacheTypeList;
    protected $cacheFrontendPool;

    public function __construct(
        Context $context,
        RequestInterface $request,
        NoteFactory $noteFactory,
        NoteResourceModel $noteResourceModel,
        TagRelationFactory $tagRelationFactory,
        ManagerInterface $messageManager,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->noteFactory = $noteFactory;
        $this->noteResourceModel = $noteResourceModel;
        $this->tagRelationFactory = $tagRelationFactory;
        $this->messageManager = $messageManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function execute()
    {
        // Get parameters from the form
        $noteId = (int) $this->request->getParam('note_id');
        $noteContent = $this->request->getParam('note');
        $tagIds = is_array($this->request->getParam('tags')) ? $this->request->getParam('tags') : [];

        if (!$noteContent) {
            $this->messageManager->addErrorMessage(__('Note content cannot be empty.'));
            return $this->resultRedirectFactory->create()->setPath('notes', ['note_id' => $noteId]);
        }

        try {
            // Load existing note or create a new one
            $note = $this->noteFactory->create();
            
            if ($noteId) {
                $this->noteResourceModel->load($note, $noteId);
                
                // Check if note exists before updating
                if (!$note->getId()) {
                    $this->messageManager->addErrorMessage(__('Note does not exist.'));
                    return $this->resultRedirectFactory->create()->setPath('notes', ['note_id' => $noteId]);
                }
            }

            // Set or update note content
            $note->setNote($noteContent);
            $this->noteResourceModel->save($note);

            $this->tagRelationFactory->create()->assignTags($noteId, $tagIds);

            $types = ['block_html', 'full_page', 'layout', 'translate'];
            foreach ($types as $type) {
                $this->cacheTypeList->cleanType($type);
            }
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }

            // Success message
            $this->messageManager->addSuccessMessage(__('Note has been saved successfully.'));

            // Redirect back to edit page
            return $this->resultRedirectFactory->create()->setPath('notes');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error saving the note: %1', $e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('notes', ['note_id' => $noteId]);
        }
    }
}
