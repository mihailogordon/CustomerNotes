<?php

namespace Mastering\SampleModule\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class UpdateItem implements ResolverInterface
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
        $id = $args['id'];
        $name = $args['name'] ?? null;
        $description = $args['description'] ?? null;

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('mastering_sample_item');

        $data = [];
        if ($name !== null) {
            $data['name'] = $name;
        }
        if ($description !== null) {
            $data['description'] = $description;
        }

        if (!empty($data)) {
            $connection->update($tableName, $data, ['id = ?' => $id]);
        }

        $select = $connection->select()->from($tableName)->where('id = ?', $id);
        $updatedItem = $connection->fetchRow($select);

        return $updatedItem;
    }
}
