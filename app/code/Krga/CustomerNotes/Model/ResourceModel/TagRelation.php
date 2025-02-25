<?php

namespace Krga\CustomerNotes\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TagRelation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('customer_notes_tag_relation', 'relation_id');
    }

    public function deleteTags($noteId, array $tagIds)
    {
        $connection = $this->getConnection();
        $where = [
            'note_id = ?' => $noteId,
            'tag_id IN (?)' => $tagIds
        ];
        $connection->delete($this->getMainTable(), $where);
    }

    public function insertTags($noteId, array $tagIds)
    {
        $connection = $this->getConnection();
        $data = [];

        foreach ($tagIds as $tagId) {
            $data[] = ['note_id' => $noteId, 'tag_id' => $tagId];
        }

        if (!empty($data)) {
            $connection->insertMultiple($this->getMainTable(), $data);
        }
    }
}
