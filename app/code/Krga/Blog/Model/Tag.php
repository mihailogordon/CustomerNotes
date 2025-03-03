<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Krga\Blog\Model\ResourceModel\Tag as TagResource;

class Tag extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(TagResource::class);
    }
}
