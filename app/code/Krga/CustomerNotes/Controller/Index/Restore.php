<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResource;

class Restore implements HttpGetActionInterface
{
    protected $request;
    protected $messageManager;
    protected $noteFactory;
    protected $noteResource;
    protected $resultRedirectFactory;
    protected $cacheTypeList;
    protected $cacheFrontendPool;

    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        NoteFactory $noteFactory,
        NoteResource $noteResource,
        RedirectFactory $resultRedirectFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->noteFactory = $noteFactory;
        $this->noteResource = $noteResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function execute()
    {
        $noteId = $this->request->getParam('note_id');

        if (!$noteId) {
            $this->messageManager->addErrorMessage(__('Invalid note ID.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/trashed');
        }

        $note = $this->noteFactory->create();
        $this->noteResource->load($note, $noteId);

        if (!$note->getId()) {
            $this->messageManager->addErrorMessage(__('The note no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/trashed');
        }

        try {
            $note->setIsDeleted(0);
            $this->noteResource->save($note);

            $this->flushCache();

            $this->messageManager->addSuccessMessage(__('The note has been restored successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while restoring the note: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('notes/index/trashed');
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
