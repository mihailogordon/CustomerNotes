<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
use Krga\Blog\Model\ResourceModel\Post as PostResource;
use Krga\Blog\Model\TagFactory;
use Krga\Blog\Model\ResourceModel\Tag as TagResource;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;
use Magento\TestFramework\Utility\ChildrenClassesSearch\A;

class Post extends AbstractModel
{
    const TAGS_PATH = 'posts/tags/index';

    protected $tagFactory;
    protected $tagResource;
    protected $tagRelationCollectionFactory;
    protected $commentCollectionFactory;
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        TagFactory $tagFactory,
        TagResource $tagResource,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        CommentCollectionFactory $commentCollectionFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->urlBuilder = $urlBuilder;
    }

    protected function _construct()
    {
        $this->_init(PostResource::class);
    }

    public function getPostAuthorFullInfo($authorId)
    {
        try {
            $customerRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);

            $author = $customerRepository->getById($authorId);

            return [
                'id' => $author->getId(),
                'email' => $author->getEmail(),
                'firstname' => $author->getFirstname(),
                'lastname' => $author->getLastname()
            ];
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    public function getPostTagsHtml() {
        $postId = $this->getPostId();
        $output = '';
        $outputItems = array();

        if($postId) {
            $tags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('post_id', array('eq' => $postId))->getItems();

            if (is_array($tags) && count($tags) > 0) {
                $output .= '<span class="post-tags">Tagged as: ';
                
                foreach ($tags as $tag) {
                    $tagId = $tag->getTagId();
                    $tagObject = $this->tagFactory->create();
                    $this->tagResource->load($tagObject, $tagId);
                    $outputItems[] = '<a class="post-tag" href="' . $this->urlBuilder->getUrl(self::TAGS_PATH, ['tag_id' => $tagId]) . '">' . $tagObject->getTagName() . '</a>';
                }

                $output .= implode(', ', $outputItems);
                $output .= '</span>';
            }
        }

        return $output;
    }

    public function getPostComments()
    {
        $postId = $this->getPostId();
        $postComments = array();

        if (!empty($postId)) {
            $postComments = $this->commentCollectionFactory->create()
                ->addFieldToFilter('post_id', array('eq' => $postId))
                ->addFieldToFilter('is_approved', array('eq' => 1))
                ->getItems();
        }
        
        return $postComments;
    }
}
