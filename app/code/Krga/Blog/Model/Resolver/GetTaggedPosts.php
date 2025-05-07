<?php

namespace Krga\Blog\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Krga\Blog\Model\PostFactory;
use Krga\Blog\Model\ResourceModel\Post as PostResourceModel;
use Krga\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class GetTaggedPosts implements ResolverInterface
{
    protected $postFactory;
    protected $postResourceModel;
    protected $tagCollectionFactory;
    protected $tagRelationCollectionFactory;

    public function __construct(
        PostFactory $postFactory,
        PostResourceModel $postResourceModel,
        TagCollectionFactory $tagCollectionFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
    ) {
        $this->postFactory = $postFactory;
        $this->postResourceModel = $postResourceModel;
        $this->tagCollectionFactory = $tagCollectionFactory;
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
        $tagName = $args['tagName'] ?? null;

        if($tagName) {
            $tag = $this->tagCollectionFactory->create()
                ->addFieldToFilter('tag_name', ['eq' => $tagName])
                ->getFirstItem();

            if (!$tag || !$tag->getTagId()) {
                throw new GraphQlNoSuchEntityException(__('Tag not found.'));
            }

            $tagId = $tag->getTagId();
            $tagRelations = $this->tagRelationCollectionFactory->create()
                ->addFieldToFilter('tag_id', ['eq' => $tagId])
                ->getItems();

            if(count($tagRelations)>0) {
                foreach($tagRelations as $tagRelation) {
                    $postId = $tagRelation->getPostId();
                    $post = $this->postFactory->create();
                    $this->postResourceModel->load($post, $postId);
                    $result[] = $post;
                }
            }
        }

        return $result;
    }
}
