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

    public function getListPageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'blog_settings/list_settings/page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isListTagsFilterEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'blog_settings/list_settings/show_tags_filter_on_list',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function isListTagsEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'blog_settings/list_settings/show_tags_on_list',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isListPaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'blog_settings/list_settings/show_pagination_on_list',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getTagListPageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'blog_settings/tag_list_settings/tag_page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isTagListPaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'blog_settings/tag_list_settings/show_pagination_on_tag_list',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getTagSinglePageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'blog_settings/tag_single_settings/tag_page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isTagSinglePaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'blog_settings/tag_single_settings/show_pagination_on_tag_single',
            ScopeInterface::SCOPE_STORE
        );
    }
}
