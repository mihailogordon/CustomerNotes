<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Krga\Blog\Model\ResourceModel\TagRelation as TagRelationResource;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class TagRelation extends AbstractModel
{
    protected $tagRelationResource;
    protected $tagRelationCollectionFactory;

    public function __construct(
        TagRelationResource $tagRelationResource,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
    )
    {
        $this->tagRelationResource = $tagRelationResource;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init(TagRelationResource::class);
    }

    public function assignTags($postId, array $tagIds)
    {
        $existingTags = $this->tagRelationCollectionFactory->create()
            ->addFieldToFilter('post_id', $postId)
            ->getColumnValues('tag_id');

        $tagsToRemove = array_diff($existingTags, $tagIds);
        if (!empty($tagsToRemove)) {
            $this->tagRelationResource->deleteTags($postId, $tagsToRemove);
        }

        $tagsToAdd = array_diff($tagIds, $existingTags);
        if (!empty($tagsToAdd)) {
            $this->tagRelationResource->insertTags($postId, $tagsToAdd);
        }
    }
}
