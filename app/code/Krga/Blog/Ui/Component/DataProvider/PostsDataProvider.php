<?php

namespace Krga\BLog\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Krga\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\Blog\Model\TagFactory;
use Krga\Blog\Model\ResourceModel\Tag as TagResource;

class PostsDataProvider extends DataProvider {
    protected $postCollectionFactory;
    protected $tagRelationCollectionFactory;
    protected $tagFactory;
    protected $tagResource;
    protected $customerRepository;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $reporting;
    protected $meta = [];
    protected $data = [];

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        PostCollectionFactory $postCollectionFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        TagFactory $tagFactory,
        TagResource $tagResource,
        CustomerRepositoryInterface $customerRepository,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    public function getData()
    {
        $collection = $this->postCollectionFactory->create();

        foreach ($this->getSearchCriteria()->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $field = $filter->getField();
                if ($field) {
                    $condition = $filter->getConditionType() ?: 'eq';
                    $collection->addFieldToFilter($field, [$condition => $filter->getValue()]);
                }
            }
        }

        $sortApplied = false;
        foreach ($this->getSearchCriteria()->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            if ($field) {
                $collection->setOrder($field, $sortOrder->getDirection());
                $sortApplied = true;
            }
        }

        if (!$sortApplied) {
            $collection->setOrder('post_id', 'ASC');
        }

        $collection->setCurPage($this->getSearchCriteria()->getCurrentPage());
        $collection->setPageSize($this->getSearchCriteria()->getPageSize());

        $items = [];
        foreach ($collection as $post) {
            $items[] = [
                'post_id' => $post->getPostId(),
                'post_title' => $post->getPostTitle(),
                'post_author' => $post->getPostAuthor(),
                'author' => $this->getCustomerNicename($post->getPostAuthor()),
                'created_at' => date('F j, Y', strtotime($post->getCreatedAt())),
                'is_deleted' => $post->getIsDeleted(),
                'post_tags' => $this->getPostTags($post)
            ];
        }

        return [
            'items' => $items,
            'totalRecords' => $collection->getSize(),
        ];
    }


    private function getCustomerNicename($authorId)
    {
        try {
            $author = $this->customerRepository->getById($authorId);
            return trim($author->getFirstname() . ' ' . $author->getLastname());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return __('Unknown Customer')->render();
        }
    }

    private function getPostTags($post) {
        $postTags = array();

        $currentTags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('post_id', ['eq' => $post->getId()])->getItems();
        if (is_array($currentTags) && count($currentTags) > 0) {
            foreach ($currentTags as $currentTag) {
                $currentTagId = $currentTag->getTagId();
                $tagItem = $this->tagFactory->create();
                $this->tagResource->load($tagItem, $currentTagId);
                $postTags[] = strtolower($tagItem->getTagName());
            }
        }

        return implode(', ', $postTags);
    }
}