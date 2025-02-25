<?php

namespace Krga\CustomerNotes\Model;

use Magento\Framework\Model\AbstractModel;
use Krga\CustomerNotes\Model\ResourceModel\History as HistoryResource;

class History extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(HistoryResource::class);
    }
}
