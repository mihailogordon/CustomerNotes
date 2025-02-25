<?php 

namespace Mastering\SampleModule\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use \Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AddItem implements ResolverInterface {
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
        $name = $args['name'] ?? null;
        $description = $args['description'] ?? null;

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('mastering_sample_item');

        if(!empty($name)) {
            $data['name'] = $name;

            if(!empty($description)) {
                $data['description'] = $description;
            }

            $result = $connection->insert($tableName, $data);

            if( $result ) {
                $id = $connection->lastInsertId($tableName);
                if($id) {
                    $select = $connection->select()->from($tableName)->where('id = ?', $id);
                    $updatedItem = $connection->fetchRow($select);

                    return $updatedItem;
                }
            }
        }

        return false;
    }
}