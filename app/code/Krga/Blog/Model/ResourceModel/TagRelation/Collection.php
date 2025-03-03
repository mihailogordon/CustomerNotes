<?php

namespace Krga\Blog\Model\ResourceModel\TagRelation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Krga\Blog\Model\TagRelation as Model;
use Krga\Blog\Model\ResourceModel\TagRelation as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
