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

    public function isMenuItemEnabled() {
        return $this->scopeConfig->getValue(
            'customer_notes_settings/general_settings/show_in_main_menu',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getMenuItemLabel() {
        return $this->scopeConfig->getValue(
            'customer_notes_settings/general_settings/menu_item_label',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getListPageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'customer_notes_settings/list_settings/page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isListTagsFilterEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/list_settings/show_tags_filter_on_list',
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

    public function isListPaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/list_settings/show_pagination_on_list',
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

    public function getTagListPageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'customer_notes_settings/tag_list_settings/tag_page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isTagListPaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/tag_list_settings/show_pagination_on_tag_list',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getTagSinglePageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'customer_notes_settings/tag_single_settings/tag_page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isTagSinglePaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/tag_single_settings/show_pagination_on_tag_single',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getTrashListPageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'customer_notes_settings/trash_settings/trash_page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isTrashListPaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/trash_settings/show_pagination_on_trash_list',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getHistoriesPerNoteLimit()
    {
        return (int) $this->scopeConfig->getValue(
            'customer_notes_settings/history_settings/histories_per_note',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getHistoryListPageSize()
    {
        return (int) $this->scopeConfig->getValue(
            'customer_notes_settings/history_settings/history_page_size',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isHistoryListPaginationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_notes_settings/history_settings/show_pagination_on_history_list',
            ScopeInterface::SCOPE_STORE
        );
    }
}
