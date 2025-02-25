<?php

namespace Krga\CustomerNotes\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class YesNo implements OptionSourceInterface
{
    
    public function toOptionArray()
    {
        return [
            ['value' => 'yes', 'label' => __('Yes')],
            ['value' => 'no', 'label' => __('No')]
        ];
    }
}
