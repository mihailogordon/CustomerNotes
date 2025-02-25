<?php

namespace Krga\CustomerNotes\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Settings extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('customer_notes_settings', 'setting_id');
    }
}
