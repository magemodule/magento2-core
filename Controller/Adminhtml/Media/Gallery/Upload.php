<?php

namespace MageModule\Core\Controller\Adminhtml\Media\Gallery;

use MageModule\Core\Model\MediaGalleryConfigInterface;
use MageModule\Core\Model\MediaGalleryConfigPoolInterface;
use Magento\Backend\App\Action\Context;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use Magento\MediaStorage\Model\File\UploaderFactory as FileUploaderFactory;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\Controller\Result\RawFactory as RawResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * @var MediaGalleryConfigPoolInterface
     */
    private $configPool;

    /**
     * @var FileUploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var RawResultFactory
     */
    private $resultRawFactory;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * Upload constructor.
     *
     * @param Context                         $context
     * @param MediaGalleryConfigPoolInterface $configPool
     * @param FileUploaderFactory             $uploaderFactory
     * @param RawResultFactory                $resultRawFactory
     * @param Filesystem                      $fileSystem
     */
    public function __construct(
        Context $context,
        MediaGalleryConfigPoolInterface $configPool,
        FileUploaderFactory $uploaderFactory,
        RawResultFactory $resultRawFactory,
        Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->configPool       = $configPool;
        $this->uploaderFactory  = $uploaderFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileSystem       = $fileSystem;
    }

    /**
     * @return RawResult
     */
    public function execute()
    {
        try {
            $attributeCode = $this->getRequest()->getParam('attribute_code');
            $fileField     = $this->getRequest()->getParam('field_name');

            /** @var MediaGalleryConfigInterface $config */
            $config = $this->configPool->getConfig($attributeCode);

            /**
             * if a validator is needed, there is a function $uploader->addValidateCallback();
             */
            /** @var FileUploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => $fileField]);
            $uploader->setAllowedExtensions($config->getAllowedExtensions());
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            /** @var Read $directory */
            $directory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $result    = $uploader->save($directory->getAbsolutePath($config->getBaseTmpMediaPath()));

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $config->getTmpMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }
}
