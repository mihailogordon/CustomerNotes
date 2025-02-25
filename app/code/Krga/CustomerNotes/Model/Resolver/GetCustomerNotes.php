<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetCustomerNotes implements ResolverInterface
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
        $customerId = $args['customerId'] ?? null;

        if (empty($customerId)) {
            throw new \Exception(__('Customer ID is required.'));
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('customer_notes');

        $select = $connection->select()
            ->from($tableName)
            ->where('customer_id = ?', $customerId);

        $results = $connection->fetchAll($select);

        if( $results ) {
            return $results;
        }

        throw new \Exception(__('Failed to fetch customer notes.'));
    }
}
