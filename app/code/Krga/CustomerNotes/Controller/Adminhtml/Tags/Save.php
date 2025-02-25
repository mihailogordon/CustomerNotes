<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\Tags;

use Krga\CustomerNotes\Model\TagFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag as TagResourceModel;

class Save extends \Magento\Backend\App\Action
{
    private $tagFactory;
    private $tagResourceModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        TagFactory $tagFactory,
        TagResourceModel $tagResourceModel
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagResourceModel = $tagResourceModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $tagId = $data['tag_id'] ?? null;
        $tag = $this->tagFactory->create();
    
        if ($tagId) {
            $this->tagResourceModel->load($tag, $tagId);
        }

        $this->tagResourceModel->save($tag);
    
        $this->messageManager->addSuccessMessage(__('The tag has been saved.'));
        return $this->resultRedirectFactory->create()->setPath('notes/tags/index');
    }
}
