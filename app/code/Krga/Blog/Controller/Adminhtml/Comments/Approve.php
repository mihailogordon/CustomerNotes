<?php

namespace Krga\Blog\Controller\Adminhtml\Comments;

use Magento\Framework\Exception\CouldNotDeleteException;

class Approve extends CommentActionsHandlerFactory
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
            $comment->setIsApproved(1);
            $this->commentResource->save($comment);
            $this->flushCache();
            $this->messageManager->addSuccessMessage(__('The comment has been approved.'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage(__('Could not approve the comment: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('posts/comments/index');
    }
}
