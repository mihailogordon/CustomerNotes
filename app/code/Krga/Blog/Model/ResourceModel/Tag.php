<?php

namespace Krga\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tag extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('blog_tags', 'tag_id');
    }
}
