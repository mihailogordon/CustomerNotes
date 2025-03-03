<?php

namespace Krga\Blog\Controller\Adminhtml\Tags;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Backend\App\Action\Context;
use Krga\Blog\Model\TagFactory;
use Krga\Blog\Model\ResourceModel\Tag as TagResource;

class Delete extends Action
{
    private $tagFactory;
    private $tagResource;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        TagFactory $tagFactory,
        TagResource $tagResource,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $tagId = $this->getRequest()->getParam('tag_id');

        if (!$tagId) {
            $this->messageManager->addErrorMessage(__('Invalid tag ID.'));
            return $this->resultRedirectFactory->create()->setPath('notes/tags/index');
        }

        $tag = $this->tagFactory->create();
        $this->tagResource->load($tag, $tagId);

        if (!$tag->getId()) {
            $this->messageManager->addErrorMessage(__('The tag no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('notes/tags/index');
        }

        try {
            $this->tagResource->delete($tag);
            $this->messageManager->addSuccessMessage(__('The tag has been deleted permanently.'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage(__('Could not delete the tag: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('posts/tags/index');
    }
}
