<?php

namespace Krga\Blog\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Krga\Blog\Model\ResourceModel\Post\CollectionFactory;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class PostsDataProviderEditForm extends AbstractDataProvider
{
    protected $loadedData;
    protected $request;
    protected $tagRelationCollectionFactory;
    protected $urlBuilder;

    public function __construct(
        CollectionFactory $collectionFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $data = [];
        $postId = $this->request->getParam('post_id');

        if ($postId) {
            $post = $this->collection->getItemById($postId);
            if ($post) {
                $postData = $post->getData();

                if (!empty($postData['post_image'])) {
                    $imagePath = ltrim($postData['post_image'], '/');
                    $fullImagePath = BP . '/pub/media/posts/' . $imagePath;

                    if (file_exists($fullImagePath) && is_readable($fullImagePath)) {
                        $fileSize = filesize($fullImagePath);
                        $mimeType = mime_content_type($fullImagePath);

                        if ($fileSize > 0) {
                            $postData['post_image'] = [[
                                'name' => basename($imagePath),
                                'file' => $imagePath,
                                'url' => $this->_getImageUrl($imagePath),
                                'size' => $fileSize,
                                'type' => $mimeType,
                            ]];
                        } else {
                            $postData['post_image'] = [[
                                'name' => basename($imagePath),
                                'file' => $imagePath,
                                'url' => $this->_getImageUrl($imagePath),
                                'size' => 12345,
                                'type' => 'image/png',
                            ]];
                        }
                    }
                }

                $data[$postId] = $postData;
            }

            $data[$postId]['tag_ids'] = array();
        }

        $relatedTags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('post_id', ['eq' => $postId])->getitems();
        if (is_array($relatedTags) && count($relatedTags) > 0) {
            foreach ($relatedTags as $relatedTag) {
                $data[$postId]['tag_ids'][] = $relatedTag->getTagId();
            }
        }

        return $data;
    }

    protected function _getImageUrl($imagePath)
    {
        return $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'posts/' . ltrim($imagePath, '/');
    }


    protected function _getFileSize($imagePath)
    {
        $mediaDirectory = BP . '/pub/media/';
        $fullPath = $mediaDirectory . $imagePath;
        return file_exists($fullPath) ? filesize($fullPath) : 0;
    }

    protected function _getFileMimeType($imagePath)
    {
        $mediaDirectory = BP . '/pub/media/';
        $fullPath = $mediaDirectory . $imagePath;
        return file_exists($fullPath) ? mime_content_type($fullPath) : 'image/jpeg';
    }
}