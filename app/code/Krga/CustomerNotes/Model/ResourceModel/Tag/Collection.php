<?php

namespace Krga\CustomerNotes\Model\ResourceModel\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Krga\CustomerNotes\Model\Tag as Model;
use Krga\CustomerNotes\Model\ResourceModel\Tag as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
