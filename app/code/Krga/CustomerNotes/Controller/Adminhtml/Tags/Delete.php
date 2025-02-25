<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\Tags;

use Krga\CustomerNotes\Model\TagFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag as TagResource;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete extends Action
{
    private $tagFactory;
    private $tagResource;
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
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

        return $this->resultRedirectFactory->create()->setPath('notes/tags/index');
    }
}
