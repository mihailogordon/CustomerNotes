<?php

namespace Krga\Blog\Controller\Adminhtml\Comments;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Krga\Blog\Model\CommentFactory;
use Krga\Blog\Model\ResourceModel\Comment as CommentResource;

abstract class CommentActionsHandlerFactory extends Action
{
    protected $commentFactory;
    protected $commentResource;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        CommentFactory $commentFactory,
        CommentResource $commentResource,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->commentFactory = $commentFactory;
        $this->commentResource = $commentResource;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    protected function flushCache()
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
