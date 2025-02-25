<?php

namespace Krga\CustomerNotes\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tag extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('customer_notes_tags', 'tag_id');
    }
}
