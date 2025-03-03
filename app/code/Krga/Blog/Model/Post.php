<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Krga\Blog\Model\ResourceModel\Post as PostResource;

class Post extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(PostResource::class);
    }
}
