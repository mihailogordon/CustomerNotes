<?php

namespace Krga\Blog\Controller\Adminhtml\Comments;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Krga\Blog\Model\CommentFactory;
use Krga\Blog\Model\ResourceModel\Comment as CommentResource;

class Approve extends Action
{
    private $commentFactory;
    private $commentResource;
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

    public function execute()
    {
        $commentId = $this->getRequest()->getParam('comment_id');

        if (!$commentId) {
            $this->messageManager->addErrorMessage(__('Invalid Comment ID.'));
            return $this->resultRedirectFactory->create()->setPath('posts/comments/index');
        }

        $comment = $this->commentFactory->create();
        $this->commentResource->load($comment, $commentId);

        if (!$comment->getId()) {
            $this->messageManager->addErrorMessage(__('The comment no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('posts/comments/index');
        }

        try {
            $comment->setIsApproved(1);
            $this->commentResource->save($comment);
            $this->flushCache();
            $this->messageManager->addSuccessMessage(__('The comment has been approved.'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage(__('Could not approve the comment: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('posts/comments/index');
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
