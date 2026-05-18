<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Language implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'auto', 'label' => __('Auto')],
            ['value' => 'sr', 'label' => __('Serbian')],
            ['value' => 'en', 'label' => __('English')],
        ];
    }
}
