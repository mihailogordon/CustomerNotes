<?php

namespace Krga\Blog\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Message\ManagerInterface;
use Krga\Blog\Model\CommentFactory;
use Krga\Blog\Model\ResourceModel\Comment as CommentResource;

class Addcomment implements HttpPostActionInterface
{
    protected $request;
    protected $commentFactory;
    protected $commentResource;
    protected $resultRedirectFactory;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $tagRelationFactory;
    protected $messageManager;

    public function __construct(
        RequestInterface $request,
        CommentFactory $commentFactory,
        CommentResource $commentResource,
        RedirectFactory $resultRedirectFactory,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->commentFactory = $commentFactory;
        $this->commentResource = $commentResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        $postId = intval($this->request->getParam('post_id'));
        $content = trim($this->request->getParam('content'));
        $authorName = trim($this->request->getParam('author_name'));
        $authorEmail = trim($this->request->getParam('author_email'));

        if (!empty($postId) && !empty($content) && !empty($authorName) && !empty($authorEmail)) {
            try {
                $comment = $this->commentFactory->create();
                $comment->setPostId($postId);
                $comment->setContent($content);
                $comment->setAuthorName($authorName);
                $comment->setAuthorEmail($authorEmail);
                $comment->setIsApproved(0);
                $this->commentResource->save($comment);
                $this->flushCache();
                $this->messageManager->addSuccessMessage(__('The comment has been submited. Please wait until it is approved by admin. Thank you for your patience!'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while adding the note.'));
            }
        }

        return $this->resultRedirectFactory->create()->setPath('posts/post/index', ['post_id' => $postId]);
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
