<?php

namespace Krga\Blog\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;

class GetPostComments implements ResolverInterface
{
    protected $commentCollectionFactory;

    public function __construct(
        CommentCollectionFactory $commentCollectionFactory,
    ) {
        $this->commentCollectionFactory = $commentCollectionFactory;
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
            $result = $this->commentCollectionFactory->create()
                ->addFieldToFilter('post_id', ['eq' => $postId])
                ->addFieldToFilter('is_approved', ['eq' => 1])
                ->getItems();
        }

        return $result;
    }
}
