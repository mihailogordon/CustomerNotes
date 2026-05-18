<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Environment implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'test', 'label' => __('Test')],
            ['value' => 'prod', 'label' => __('Production')],
        ];
    }
}
