<?php

namespace Krga\Blog\Controller\Adminhtml\Comments;

use Magento\Framework\Exception\CouldNotDeleteException;

class Delete extends CommentActionsHandlerFactory
{
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
            $this->commentResource->delete($comment);
            $this->flushCache();
            $this->messageManager->addSuccessMessage(__('The comment has been deleted permanently.'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage(__('Could not delete the comment: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('posts/comments/index');
    }
}
