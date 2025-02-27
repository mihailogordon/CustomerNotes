<?php
namespace Krga\CustomerNotes\Model\ResourceModel\Note;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'note_id';
    protected $_eventPrefix = 'krga_customernotes_note_collection';
    protected $_eventObject = 'note_collection';

    protected function _construct()
    {
        $this->_init('Krga\CustomerNotes\Model\Note', 'Krga\CustomerNotes\Model\ResourceModel\Note');
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->columns(['is_deleted' => 'main_table.is_deleted']);

        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['firstname', 'lastname']
        );
    }
}
