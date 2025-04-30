<?php

namespace Krga\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
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

    protected $postCollectionFactory;
    protected $tagFactory;
    protected $tagResource;
    protected $tagRelationCollectionFactory;
    protected $commentCollectionFactory;
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        PostCollectionFactory $postCollectionFactory,
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
        $this->postCollectionFactory = $postCollectionFactory;
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
    
    public function getPostCommentCountInfo()
    {
        $commentsCount = count($this->getPostComments());
        $output = '<span class="comments-number">' . $commentsCount;
        $output .= $commentsCount === 1 ? ' comment' : ' comments';
        $output .= '</span>';

        return $output;
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

    public function getReadTime()
    {
        return max(1, (int) ceil(str_word_count($this->getPostContent()) / 200));
    }
}
