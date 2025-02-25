<?php

namespace Krga\CustomerNotes\Model\ResourceModel\History;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Krga\CustomerNotes\Model\History as Model;
use Krga\CustomerNotes\Model\ResourceModel\History as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
