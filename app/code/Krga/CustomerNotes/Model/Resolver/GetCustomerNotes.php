<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory as NoteCollectionFactory;

class GetCustomerNotes implements ResolverInterface
{
    protected $noteCollectionFactory;

    public function __construct(
        NoteCollectionFactory $noteCollectionFactory
    ) {
        $this->noteCollectionFactory = $noteCollectionFactory;
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

        $results = $this->noteCollectionFactory->create()->addFieldToFilter('customer_id', ['eq' => $customerId])->getItems();

        if( $results ) {
            return $results;
        }

        throw new \Exception(__('Failed to fetch customer notes.'));
    }
}
