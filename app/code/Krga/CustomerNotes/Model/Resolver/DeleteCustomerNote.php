<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DeleteCustomerNote implements ResolverInterface
{
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null ) 
    {
        $noteId = $args['noteId'] ?? null;

        if (empty($noteId)) {
            throw new \Exception(__('Note ID is required.'));
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('customer_notes');

        $select = $connection->select()
            ->from($tableName, ['note_id'])
            ->where('note_id = ?', $noteId);

        $noteExists = $connection->fetchOne($select);

        if (!$noteExists) {
            throw new \Exception(__('Customer note with ID %1 does not exist.', $noteId));
        }

        $result = $connection->delete($tableName, ['note_id = ?' => $noteId]);

        if ($result) {
            return true;
        }

        throw new \Exception(__('Failed to delete customer note.'));
    }
}
