<?php

namespace MageModule\Core\Controller\Adminhtml\Media\Gallery;

use MageModule\Core\Model\MediaGalleryConfigInterface;
use Magento\Backend\App\Action\Context;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use Magento\MediaStorage\Model\File\UploaderFactory as FileUploaderFactory;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\Controller\Result\RawFactory as RawResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * @var MediaGalleryConfigInterface
     */
    protected $mediaGalleryConfig;

    /**
     * @var FileUploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var ImageAdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var RawResultFactory
     */
    protected $resultRawFactory;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var array
     */
    protected $allowedExtensions;

    public function __construct(
        Context $context,
        MediaGalleryConfigInterface $mediaGalleryConfig,
        FileUploaderFactory $uploaderFactory,
        ImageAdapterFactory $adapterFactory,
        RawResultFactory $resultRawFactory,
        Filesystem $fileSystem,
        array $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png']
    ) {
        parent::__construct($context);
        $this->mediaGalleryConfig = $mediaGalleryConfig;
        $this->uploaderFactory    = $uploaderFactory;
        $this->adapterFactory     = $adapterFactory;
        $this->resultRawFactory   = $resultRawFactory;
        $this->fileSystem         = $fileSystem;
        $this->allowedExtensions  = $allowedExtensions;
    }

    /**
     * @return RawResult
     */
    public function execute()
    {
        try {
            /** @var FileUploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions($this->allowedExtensions);

            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->adapterFactory->create();
            //TODO add validation callback
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            /** @var Read $directory */
            $directory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $result    = $uploader->save(
                $directory->getAbsolutePath(
                    $this->mediaGalleryConfig->getBaseTmpMediaPath()
                )
            );

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url']  = $this->mediaGalleryConfig->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
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
