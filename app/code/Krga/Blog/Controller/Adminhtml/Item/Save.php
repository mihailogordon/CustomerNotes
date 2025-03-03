<?php

namespace Krga\Blog\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Krga\Blog\Model\PostFactory;
use Krga\Blog\Model\TagRelationFactory;
use Krga\Blog\Model\ResourceModel\Post as PostResourceModel;

class Save extends Action
{
    private $postFactory;
    private $tagRelationFactory;
    private $postResourceModel;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        TagRelationFactory $tagRelationFactory,
        PostResourceModel $postResourceModel
    ) {
        $this->postFactory = $postFactory;
        $this->tagRelationFactory = $tagRelationFactory;
        $this->postResourceModel = $postResourceModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();

        if (!$data) {
            $this->messageManager->addErrorMessage(__('Invalid data.'));
            return $this->resultRedirectFactory->create()->setPath('posts/index/index');
        }

        try {
            $post = $this->postFactory->create();

            if (!empty($data['post_id'])) {
                $this->postResourceModel->load($post, $data['post_id']);
            }

            $post->setData($data);
            $this->postResourceModel->save($post);

            if (!empty($data['tag_ids']) && is_array($data['tag_ids'])) {
                $this->tagRelationFactory->create()->assignTags($post->getPostId(), $data['tag_ids']);
            }

            $this->messageManager->addSuccessMessage(__('The post has been saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error saving post: ') . $e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('posts/index/index');
    }

}
