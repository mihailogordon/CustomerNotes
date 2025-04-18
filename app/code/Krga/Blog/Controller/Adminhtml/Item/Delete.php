<?php

namespace Krga\Blog\Controller\Adminhtml\Item;

use Krga\Blog\Model\PostFactory;
use Krga\Blog\Model\ResourceModel\Post as PostResource;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete extends Action
{
    private $postFactory;
    private $postResource;
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PostFactory $postFactory,
        PostResource $postResource,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->postFactory = $postFactory;
        $this->postResource = $postResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $postId = $this->getRequest()->getParam('post_id');

        if (!$postId) {
            $this->messageManager->addErrorMessage(__('Invalid post ID.'));
            return $this->resultRedirectFactory->create()->setPath('posts/index/index');
        }

        $post = $this->postFactory->create();
        $this->postResource->load($post, $postId);

        if (!$post->getPostId()) {
            $this->messageManager->addErrorMessage(__('The post no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('posts/index/index');
        }

        try {
            $this->postResource->delete($post);
            $this->messageManager->addSuccessMessage(__('The post has been deleted permanently.'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage(__('Could not delete the post: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('posts/index/index');
    }
}
