<?php

namespace Krga\Blog\Model\ResourceModel\Post\Grid;

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
        $mainTable = 'blog_posts',
        $resourceModel = 'Krga\Blog\Model\ResourceModel\Post'
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

        $connection = $this->getConnection();

        $this->getSelect()
            // Join blog_tag_relation + blog_tags for tags
            ->joinLeft(
                ['tag_relation' => $this->getTable('blog_tag_relation')],
                'main_table.post_id = tag_relation.post_id',
                []
            )
            ->joinLeft(
                ['tag' => $this->getTable('blog_tags')],
                'tag_relation.tag_id = tag.tag_id',
                []
            )
            // Join customer_entity for author
            ->joinLeft(
                ['author' => $this->getTable('customer_entity')],
                'main_table.post_author = author.entity_id',
                []
            )
            ->columns([
                'post_tags' => new \Zend_Db_Expr('GROUP_CONCAT(tag.tag_name SEPARATOR ", ")'),
                'author' => $connection->getConcatSql(
                    ['author.firstname', "' '", 'author.lastname']
                )
            ])
            ->group('main_table.post_id');

        return $this;
    }
}
