<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\AbstractModel;
use Krga\Blog\Model\ResourceModel\Tag as TagResource;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class Tag extends AbstractModel
{
    protected $tagResource;
    protected $tagRelationCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        TagResource $tagResource,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->tagResource = $tagResource;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init(TagResource::class);
    }

    public function getTagPostsCount() {
        return count($this->tagRelationCollectionFactory->create()->addFieldToFilter('tag_id', ['eq' => $this->getTagId()])->getItems());
    }
}
