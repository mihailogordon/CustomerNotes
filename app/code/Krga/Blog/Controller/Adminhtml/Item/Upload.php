<?php

namespace Krga\Blog\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;

class Upload extends Action
{
    protected $jsonFactory;
    protected $uploaderFactory;
    protected $filesystem;
    protected $urlBuilder;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $fileId = 'post_image';
            $fileData = $this->getRequest()->getFiles($fileId);

            if (!$fileData || !isset($fileData['name']) || empty($fileData['name'])) {
                throw new \Exception(__('No file uploaded.'));
            }

            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $uploadPath = $mediaDirectory->getAbsolutePath('posts/');
            $resultData = $uploader->save($uploadPath);
            $fileUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . 'posts' . $resultData['file'];

            return $result->setData([
                'file' => $resultData['file'],
                'url' => $fileUrl,
                'name' => $resultData['file'],
                'size' => $resultData['size'],
                'type' => $resultData['type'],
                'error' => false
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'error' => true,
                'message' => __('File upload failed: %1', $e->getMessage())
            ]);
        }
    }
}