<?php

namespace Krga\CustomerNotes\Model\ResourceModel\Settings;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Krga\CustomerNotes\Model\Settings as Model;
use Krga\CustomerNotes\Model\ResourceModel\Settings as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
