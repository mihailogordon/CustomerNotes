<?php

namespace Mastering\SampleModule\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use \Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DeleteItem implements ResolverInterface {
    
    private $resouceConnection;

    public function __construct(ResourceConnection $resouceConnection)
    {
        $this->resouceConnection = $resouceConnection;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $id = $args['id'];
        
        $connection = $this->resouceConnection->getConnection();
        $tableName = $this->resouceConnection->getTableName('mastering_sample_item');

        if(!empty($id)) {
            try {
                $connection->delete($tableName, ['id = ?' => $id]);
                return true; // Return true if deletion was successful
            } catch (\Exception $e) {
                // Handle any errors that occur during deletion
                return false;
            }
        }

        return false;
    }
}