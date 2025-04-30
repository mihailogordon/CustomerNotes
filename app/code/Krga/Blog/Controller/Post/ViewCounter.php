<?php
namespace Krga\Blog\Controller\Post;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Krga\Blog\Model\PostFactory;
use Krga\Blog\Model\ResourceModel\Post as PostResource;

class ViewCounter implements HttpGetActionInterface
{
    protected $request;
    protected $response;
    protected $postFactory;
    protected $postResource;
    protected $resultJsonFactory;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        PostFactory $postFactory,
        PostResource $postResource,
        JsonFactory $resultJsonFactory
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->postFactory = $postFactory;
        $this->postResource = $postResource;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $postId = (int) $this->request->getParam('post_id');
        $mode = $this->request->getParam('mode');

        $views = 0;

        if ($postId) {
            $post = $this->postFactory->create();
            $this->postResource->load($post, $postId);

            if ($mode === 'get') {
                $views = (int) $post->getViews();
            } else {
                $views = (int) $post->getViews() + 1;
                $post->setViews($views);
                $this->postResource->save($post);
            }
        }

        return $this->resultJsonFactory->create()->setData(['views' => $views]);
    }
}
