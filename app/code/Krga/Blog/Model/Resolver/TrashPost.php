<?php

namespace Krga\Blog\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Krga\Blog\Model\PostFactory;
use Krga\Blog\Model\ResourceModel\Post as PostResourceModel;

class TrashPost implements ResolverInterface
{
    protected $postFactory;
    protected $postResourceModel;

    public function __construct(
        PostFactory $postFactory,
        PostResourceModel $postResourceModel,
    ) {
        $this->postFactory = $postFactory;
        $this->postResourceModel = $postResourceModel;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null ) 
    {
        $result = false;
        $postId = $args['postId'] ?? null;

        if($postId) {
            $post = $this->postFactory->create();
            $this->postResourceModel->load($post, $postId);

            if (!$post || !$post->getId()) {
                throw new GraphQlNoSuchEntityException(__('Post not found.'));
            } else {
                $post->setIsDeleted(1);
                $this->postResourceModel->save($post);

                if($post->getIsDeleted() == 1) {
                    $result = true;
                }
            }
        }

        return $result;
    }
}
