<?php

namespace Krga\CustomerNotes\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Krga\CustomerNotes\Model\HistoryFactory;
use Krga\CustomerNotes\Model\ResourceModel\History as HistoryResource;

class Deletehistory implements HttpGetActionInterface
{
    protected $request;
    protected $messageManager;
    protected $historyFactory;
    protected $historyResource;
    protected $resultRedirectFactory;
    protected $cacheTypeList;
    protected $cacheFrontendPool;

    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        HistoryFactory $historyFactory,
        HistoryResource $historyResource,
        RedirectFactory $resultRedirectFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function execute()
    {
        $historyId = $this->request->getParam('history_id');

        if (!$historyId) {
            $this->messageManager->addErrorMessage(__('Invalid history ID.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/index');
        }

        $history = $this->historyFactory->create();
        $this->historyResource->load($history, $historyId);

        if (!$history->getId()) {
            $this->messageManager->addErrorMessage(__('The history no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('notes/index/index');
        }

        try {
            $noteId = $history->getNoteId();
            $this->historyResource->delete($history);

            $this->flushCache();

            $this->messageManager->addSuccessMessage(__('The history has been deleted permanently.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not delete the history: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('notes/index/history/note_id/'.$noteId);
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
