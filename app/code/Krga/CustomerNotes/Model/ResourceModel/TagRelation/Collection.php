<?php

namespace Krga\CustomerNotes\Model\ResourceModel\TagRelation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Krga\CustomerNotes\Model\TagRelation as Model;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
