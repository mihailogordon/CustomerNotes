<?php

namespace Krga\Blog\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\Blog\Model\TagFactory;
use Krga\Blog\Model\ResourceModel\Tag as TagResourceModel;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class GetPostTags implements ResolverInterface
{
    protected $tagFactory;
    protected $tagResourceModel;
    protected $tagRelationCollectionFactory;

    public function __construct(
        TagFactory $tagFactory,
        TagResourceModel $tagResourceModel,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagResourceModel = $tagResourceModel;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
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
            $tagRelations = $this->tagRelationCollectionFactory->create()
                ->addFieldToFilter('post_id', ['eq' => $postId])
                ->getItems();

            if(count($tagRelations)>0) {
                foreach($tagRelations as $tagRelation) {
                    $tagId = $tagRelation->getTagId();
                    $tag = $this->tagFactory->create();
                    $this->tagResourceModel->load($tag, $tagId);
                    $result[] = $tag;
                }
            }
        }

        return $result;
    }
}
