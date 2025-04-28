<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Krga\Blog\Model\ResourceModel\Comment as CommentResource;

class Comment extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(CommentResource::class);
    }
}
