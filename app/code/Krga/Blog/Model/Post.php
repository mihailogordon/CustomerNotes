<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Krga\Blog\Model\ResourceModel\Post as PostResource;
use Krga\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory; 
use Krga\Blog\Model\TagFactory;
use Krga\Blog\Model\ResourceModel\Tag as TagResource;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;

class Post extends AbstractModel
{
    const TAGS_PATH = 'posts/tags/index';
    const COMMENTS_DEPTH_LIMIT = 4;
    const RECENT_POSTS_LIMIT = 3;
    const RELATED_POSTS_LIMIT = 3;
    const POPULAR_POSTS_LIMIT = 3;

    protected $postCollectionFactory;
    protected $tagFactory;
    protected $tagResource;
    protected $tagRelationCollectionFactory;
    protected $commentCollectionFactory;
    protected $urlBuilder;
    protected $urlRewriteFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        PostCollectionFactory $postCollectionFactory,
        TagFactory $tagFactory,
        TagResource $tagResource,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        CommentCollectionFactory $commentCollectionFactory,
        UrlInterface $urlBuilder,
        UrlRewriteFactory $urlRewriteFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $registry, null, null, $data);
        $this->postCollectionFactory = $postCollectionFactory;
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storeManager = $storeManager;
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

    public function getPostTags() {
        $postId = $this->getPostId();
        $tags = array();

        if($postId) {
            $tagRelations = $this->tagRelationCollectionFactory->create()->addFieldToFilter('post_id', array('eq' => $postId))->getItems();

            if (is_array($tagRelations) && count($tagRelations) > 0) {
                foreach ($tagRelations as $tagRelation) {
                    $tagId = $tagRelation->getTagId();
                    $tagObject = $this->tagFactory->create();
                    $this->tagResource->load($tagObject, $tagId);
                    $tags[] = $tagObject;
                }
            }
        }

        return $tags;
    }

    public function getReadTime()
    {
        return max(1, (int) ceil(str_word_count($this->getPostContent()) / 200));
    }

    public function getPostComments()
    {
        $postId = $this->getPostId();
        $postComments = array();

        if (!empty($postId)) {
            $postComments = $this->commentCollectionFactory->create()
                ->addFieldToFilter('post_id', array('eq' => $postId))
                ->addFieldToFilter('is_approved', array('eq' => 1))
                ->setOrder('created_at', 'ASC')
                ->getItems();
        }
        
        return $postComments;
    }

    public function getGroupedPostComments()
    {
        $postComments = $this->getPostComments();

        $groupedComments = [];

        foreach ($postComments as $comment) {
            $parentId = $comment->getParentId() ?: 0;
            $groupedComments[$parentId][] = $comment;
        }

        return $groupedComments;
    }
    
    public function getPostCommentCount()
    {
        return count($this->getPostComments());
    }

    public function renderComments($grouped, $parentId = 0, $depth = 0)
    {
        if (!isset($grouped[$parentId])) {
            return;
        }
        
        foreach ($grouped[$parentId] as $comment) {
            echo '<div class="post-comment">';
            echo '<h4 class="comment-author-name">' . $comment->getAuthorName() . '</h4>';
            echo '<h5 class="comment-author-mail">' . $comment->getAuthorEmail() . '</h5>';
            echo '<p class="comment-date">Commented at ' . date('F j, Y', strtotime($comment->getCreatedAt())) . '</p>';
            echo '<p class="comment-author-text">' . $comment->getContent() . '</p>';
            if ($depth < self::COMMENTS_DEPTH_LIMIT) {
                echo '<a href="#" class="comment-reply">Reply</a>';
            }
            echo '<div class="post-comment-form post-comment-reply-form">';
            echo '<h4 class="post-comment-form-title">Write a reply:</h2>';
            echo '<form class="add-post-comment-form" action="' . $this->urlBuilder->getUrl("posts/index/addcomment") . '" method="post">';
            echo '<label for="author_name">Name:</label>';
            echo '<input type="text" class="author_name" name="author_name" required />';
            echo '<br/>';
            echo '<br/>';
            echo '<label for="author_email">Email:</label>';
            echo '<input type="email" class="author_email" name="author_email" required />';
            echo '<br/>';
            echo '<br/>';
            echo '<label for="content">Reply:</label>';
            echo '<textarea class="content" name="content" required></textarea>';
            echo '<br/>';
            echo '<br/>';
            echo '<input type="hidden" name="post_id" value="' . $this->getPostId() . '" />';
            echo '<input type="hidden" name="parent_id" value="' . $comment->getCommentId() . '"/>';
            echo '<button class="action primary" type="submit">Submit</button>';
            echo '<br/>';
            echo '<br/>';
            echo '<a href="#" class="comment-reply comment-reply-cancel">Cancel Reply</a>';
            echo '</form>';
            echo '</div>';
            if ($depth < self::COMMENTS_DEPTH_LIMIT) {
                $this->renderComments($grouped, $comment->getId(), $depth + 1);
            }
            echo '</div>';
        }
    }

    public function getRecentPosts()
    {
        $postId = $this->getPostId();
        $collection = $this->postCollectionFactory->create()
            ->addFieldToFilter('is_deleted', ['eq' => 0])
            ->setOrder('created_at', 'DESC')
            ->setPageSize(self::RECENT_POSTS_LIMIT);

        if (!empty($postId)) {
            $collection->addFieldToFilter('post_id', ['neq' => $postId]);
        }

        return $collection->getItems();
    }
    
    public function getRelatedPosts()
    {
        $postId = $this->getPostId();
        $tagRelationIds = array();
        $allRelatedPostIds = array();
        $relatedPostIds = array();
        $relatedPosts = array();
        
        $tagRelations = $this->tagRelationCollectionFactory->create()
            ->addFieldToFilter('post_id', array('eq' => $postId))
            ->getItems();

        if (count($tagRelations)>0) {
            foreach ($tagRelations as $relation) {
                $tagRelationIds[] = $relation->getTagId();
            }
        }

        $newTagRelations = $this->tagRelationCollectionFactory->create()
                ->addFieldToFilter('tag_id', array('in' => $tagRelationIds))
                ->addFieldToFilter('post_id', array('neq' => $postId))
                ->getItems();

        if (count($newTagRelations)>0) {
            foreach ($newTagRelations as $newTagRelation) {
                $relatedPostId = $newTagRelation->getPostId();
                
                if (!in_array($relatedPostId, $allRelatedPostIds)) {
                    $allRelatedPostIds[] = $relatedPostId;
                }
            }
        }

        if (count($allRelatedPostIds)>0) {
            if (count($allRelatedPostIds) >= self::RELATED_POSTS_LIMIT) {
                $chosenKeys = array_rand($allRelatedPostIds, self::RELATED_POSTS_LIMIT);
                foreach($chosenKeys as $chosenKey) {
                    $relatedPostIds[] = $allRelatedPostIds[$chosenKey];
                }
            } else {
                $relatedPostIds = $allRelatedPostIds;
            }
        }

        if (count($relatedPostIds)>0) {
            $relatedPosts = $this->postCollectionFactory->create()
                ->addFieldToFilter('post_id', array('in', $relatedPostIds))
                ->getItems();
        }

        return $relatedPosts;
    }

    public function getPopularPosts()
    {
        $postId = $this->getPostId();
        $collection = $this->postCollectionFactory->create()
            ->addFieldToFilter('is_deleted', ['eq' => 0])
            ->setOrder('views', 'DESC')
            ->setPageSize(self::POPULAR_POSTS_LIMIT);

        if (!empty($postId)) {
            $collection->addFieldToFilter('post_id', ['neq' => $postId]);
        }

        return $collection->getItems();
    }

    public function afterSave()
    {
        parent::afterSave();

        $slug = trim($this->getPostSlug(), '/');

        // If slug is empty, auto-generate it from the title
        if (!$slug) {
            $title = $this->getPostTitle();
            if ($title) {
                // Convert title to slug: lowercase, spaces to -, remove special chars
                $slug = strtolower(trim($title));
                $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // Remove non-alphanum
                $slug = preg_replace('/[\s-]+/', '-', $slug); // Spaces/double-dashes to single dash

                $this->setData('post_slug', $slug);

                // Save it directly using the ResourceModel connection
                /** @var \Krga\Blog\Model\ResourceModel\Post $postResource */
                $postResource = $this->_getResource();
                $postResource->getConnection()->update(
                    $postResource->getMainTable(),
                    ['post_slug' => $slug],
                    ['post_id = ?' => $this->getId()]
                );

            }
        }

        if ($slug) {
            $requestPath = $slug;
            $targetPath = 'posts/post/index/post_id/' . $this->getId();
            $storeId = $this->storeManager->getStore()->getId();

            // Clean old URL rewrites (if any)
            $urlRewriteCollection = $this->urlRewriteFactory->create()->getCollection()
                ->addFieldToFilter('entity_type', 'custom_post')
                ->addFieldToFilter('entity_id', $this->getId());

            foreach ($urlRewriteCollection as $rewrite) {
                $rewrite->delete();
            }

            // Create new URL rewrite
            $urlRewrite = $this->urlRewriteFactory->create();
            $urlRewrite->setStoreId($storeId)
                ->setIsSystem(0)
                ->setEntityType('custom_post')
                ->setEntityId($this->getId())
                ->setRequestPath($requestPath)
                ->setTargetPath($targetPath)
                ->save();
        }

        return $this;
    }
}
