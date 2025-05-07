<?php

namespace Krga\Blog\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class GetPosts implements ResolverInterface
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

        $result = $this->postCollectionFactory->create()
            ->addFieldToFilter('is_deleted', ['eq' => 0])
            ->getItems();

        return $result;
    }
}
