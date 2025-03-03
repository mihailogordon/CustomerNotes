<?php

namespace Krga\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isMenuItemEnabled() {
        return $this->scopeConfig->getValue(
            'blog_settings/general_settings/show_in_main_menu',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getMenuItemLabel() {
        return $this->scopeConfig->getValue(
            'blog_settings/general_settings/menu_item_label',
            ScopeInterface::SCOPE_STORE
        );
    }
}
