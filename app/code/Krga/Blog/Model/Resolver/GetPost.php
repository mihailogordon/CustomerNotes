<?php

namespace Krga\Blog\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Krga\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class GetPost implements ResolverInterface
{
    protected $postCollectionFactory;

    public function __construct(
        PostCollectionFactory $postCollectionFactory,
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null ) 
    {
        $result = array();
        $postId = $args['postId'] ?? null;

        if($postId) {
            $result = $this->postCollectionFactory->create()
                ->addFieldToFilter('post_id', ['eq' => $postId])
                ->getFirstItem();

            if (!$result || !$result->getId()) {
                throw new GraphQlNoSuchEntityException(__('Post not found.'));
            }
        }

        return $result;
    }
}
