<?php

namespace Krga\Blog\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Krga\Blog\Helper\Config;
use Krga\Blog\Model\PostFactory;
use Krga\Blog\Model\ResourceModel\Post as PostResourceModel;

class Post extends Template
{
    protected $configHelper;
    protected $postFactory;
    protected $postResourceModel;
    protected $tagRelationCollectionFactory;

    public function __construct(
        Context $context,
        Config $configHelper,
        PostFactory $postFactory,
        PostResourceModel $postResourceModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
        $this->postFactory = $postFactory;
        $this->postResourceModel = $postResourceModel;
    }

    public function getPost() {
        $postId = (int)$this->getRequest()->getParam('post_id');
        $post = false;

        if (!empty($postId)) {
            $post = $this->postFactory->create();
            $this->postResourceModel->load($post, $postId);
        }
                
        return $post;
    }
}
