<?php

namespace Krga\CustomerNotes\Model;

use Magento\Framework\Model\AbstractModel;
use Krga\CustomerNotes\Model\ResourceModel\Tag as TagResource;

class Tag extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(TagResource::class);
    }
}
