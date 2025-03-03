<?php

namespace Krga\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TagRelation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('blog_tag_relation', 'relation_id');
    }

    public function deleteTags($postId, array $tagIds)
    {
        $connection = $this->getConnection();
        $where = [
            'post_id = ?' => $postId,
            'tag_id IN (?)' => $tagIds
        ];
        $connection->delete($this->getMainTable(), $where);
    }

    public function insertTags($postId, array $tagIds)
    {
        $connection = $this->getConnection();
        $data = [];

        foreach ($tagIds as $tagId) {
            $data[] = ['post_id' => $postId, 'tag_id' => $tagId];
        }

        if (!empty($data)) {
            $connection->insertMultiple($this->getMainTable(), $data);
        }
    }
}
