<?php

namespace Krga\Blog\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Post extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        // Retrieve post ID from the URL
        $postId = (int) $this->getRequest()->getParam('id');

        if ($postId) {
            // You can load the blog post data here using your model
            // Example: $post = $this->loadBlogPost($postId);

            // Return a CMS page rendering
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        } else {
            // Redirect to the main blog page or show a 404 if the ID is missing
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            // $resultRedirect->setPath('blog');
            return $resultRedirect;
        }
    }
}
