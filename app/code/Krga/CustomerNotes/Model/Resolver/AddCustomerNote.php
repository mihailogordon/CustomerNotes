<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AddCustomerNote implements ResolverInterface {
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
        ?array $args = null)
    {
        $customer_id = $args['customerId'] ?? null;
        $note = $args['note'] ?? null;

        if (empty($customer_id) || empty($note)) {
            throw new \Exception(__('Customer ID and note are required.'));
        }

        $connection = $this->resourceConnection->getConnection();
        $customerTable = $this->resourceConnection->getTableName('customer_entity');
        $notesTable = $this->resourceConnection->getTableName('customer_notes');

        $customerExists = $connection->fetchOne(
            $connection->select()
                ->from($customerTable, ['entity_id'])
                ->where('entity_id = ?', $customer_id)
        );

        if (!$customerExists) {
            throw new \Exception(__('Customer with ID %1 does not exist.', $customer_id));
        }

        $data = [
            'customer_id' => $customer_id,
            'note' => $note
        ];

        $result = $connection->insert($notesTable, $data);

        if ($result) {
            $note_id = $connection->lastInsertId($notesTable);

            if ($note_id) {
                $select = $connection->select()->from($notesTable)->where('note_id = ?', $note_id);
                $updated_item = $connection->fetchRow($select);

                return $updated_item;
            }
        }

        throw new \Exception(__('Failed to add customer note.'));
    }
}
