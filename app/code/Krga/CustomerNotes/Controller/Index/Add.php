<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResource;
use Krga\CustomerNotes\Model\TagRelationFactory;

class Add extends Action
{
    protected $noteFactory;
    protected $noteResource;
    protected $resultRedirectFactory;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $tagRelationFactory;

    public function __construct(
        Context $context,
        NoteFactory $noteFactory,
        NoteResource $noteResource,
        RedirectFactory $resultRedirectFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        TagRelationFactory $tagRelationFactory
    ) {
        parent::__construct($context);
        $this->noteFactory = $noteFactory;
        $this->noteResource = $noteResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->tagRelationFactory = $tagRelationFactory;
    }

    public function execute()
    {
        $customer = $this->getRequest()->getParam('customer');
        $noteText = trim($this->getRequest()->getParam('note'));
        $tagIds = is_array($this->getRequest()->getParam('tags')) ? $this->getRequest()->getParam('tags') : [];
        
        if (!$customer) {
            $this->messageManager->addErrorMessage(__('Please select a customer.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/index');
        }
        
        if (!$noteText) {
            $this->messageManager->addErrorMessage(__('Please enter a note.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/index');
        }

        try {
            $note = $this->noteFactory->create();
            $note->setCustomerId($customer);
            $note->setNote($noteText);
            $note->setIsDeleted(0);
            $note->setCreatedAt(date('Y-m-d H:i:s'));

            $this->noteResource->save($note);
            $noteId = $note->getId();

            $this->tagRelationFactory->create()->assignTags($noteId, $tagIds);

            $this->flushCache();

            $this->messageManager->addSuccessMessage(__('Your note has been added successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while adding the note.'));
        }

        return $this->resultRedirectFactory->create()->setPath('notes/index/index');
    }

    private function flushCache()
    {
        $types = ['block_html', 'full_page', 'layout', 'translate'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
