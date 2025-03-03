<?php

namespace Krga\Blog\Controller\Adminhtml\Tags;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Krga\Blog\Model\TagFactory;
use Krga\Blog\Model\ResourceModel\Tag as TagResourceModel;

class Save extends Action
{
    private $tagFactory;
    private $tagResourceModel;

    public function __construct(
        Context $context,
        TagFactory $tagFactory,
        TagResourceModel $tagResourceModel
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagResourceModel = $tagResourceModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $tagId = $data['tag_id'] ?? null;
        $tag = $this->tagFactory->create();
    
        if ($tagId) {
            $this->tagResourceModel->load($tag, $tagId);
        }

        $tag->setData($data);
        $this->tagResourceModel->save($tag);
    
        $this->messageManager->addSuccessMessage(__('The tag has been saved.'));
        return $this->resultRedirectFactory->create()->setPath('posts/tags/index');
    }
}
