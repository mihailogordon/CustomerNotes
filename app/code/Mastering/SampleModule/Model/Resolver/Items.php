<?php

namespace Mastering\SampleModule\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Items implements ResolverInterface
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
        array $value = null,
        array $args = null
    ) {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('mastering_sample_item');

        $query = "SELECT id, name, description FROM {$tableName}";
        $results = $connection->fetchAll($query);

        return $results;
    }
}
