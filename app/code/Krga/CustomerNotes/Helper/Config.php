<?php

namespace Krga\CustomerNotes\Helper;

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

    public function getListPageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'customer_notes_settings/list_settings/page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isListPaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/list_settings/show_pagination_on_list',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isListTagsEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/list_settings/show_tags_on_list',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function isListAddNoteFormEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/list_settings/show_add_note_form_on_list',
            ScopeInterface::SCOPE_STORE
        );
    }
}
