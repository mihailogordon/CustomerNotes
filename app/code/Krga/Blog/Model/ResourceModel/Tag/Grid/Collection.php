<?php

namespace Krga\Blog\Model\ResourceModel\Tag\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'blog_tags',
        $resourceModel = 'Krga\Blog\Model\ResourceModel\Tag'
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['relation' => $this->getTable('blog_tag_relation')],
                'main_table.tag_id = relation.tag_id',
                []
            )
            ->columns([
                'count' => new \Zend_Db_Expr('COUNT(relation.post_id)')
            ])
            ->group('main_table.tag_id');

        return $this;
    }
}
