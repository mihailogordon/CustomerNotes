<?php

namespace Krga\CustomerNotes\Model;

use Magento\Framework\Model\AbstractModel;
use Krga\CustomerNotes\Model\ResourceModel\Settings as SettingsResource;

class Settings extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(SettingsResource::class);
    }
}
