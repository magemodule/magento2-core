<?php

namespace MageModule\Core\Block\Adminhtml\Media\Gallery;

use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\Serialize\Serializer\Json as JsonEncoder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Media\Uploader;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Helper\Image as ImageHelper;

class Content extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'MageModule_Core::media/gallery.phtml';

    /**
     * @var Config
     */
    protected $mediaConfig;

    /**
     * @var JsonEncoder
     */
    protected $jsonEncoder;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * Content constructor.
     *
     * @param Context     $context
     * @param JsonEncoder $jsonEncoder
     * @param Config      $mediaConfig
     * @param ImageHelper $imageHelper
     * @param array       $data
     */
    public function __construct(
        Context $context,
        JsonEncoder $jsonEncoder,
        Config $mediaConfig,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->jsonEncoder = $jsonEncoder;
        $this->mediaConfig = $mediaConfig;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild('uploader', Uploader::class);

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->addSessionParam()->getUrl('catalog/product_gallery/upload')
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader block
     *
     * @return Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    /**
     * @return string
     */
    public function getImagesJson()
    {
        $value = $this->getElement()->getImages();
        if (is_array($value) &&
            array_key_exists('images', $value) &&
            is_array($value['images']) &&
            count($value['images'])
        ) {
            $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $images   = $this->sortImagesByPosition($value['images']);
            foreach ($images as &$image) {
                $image['url'] = $this->mediaConfig->getMediaUrl($image['file']);
                try {
                    $fileHandler   = $mediaDir->stat($this->mediaConfig->getMediaPath($image['file']));
                    $image['size'] = $fileHandler['size'];
                } catch (FileSystemException $e) {
                    $image['url']  = $this->imageHelper->getDefaultPlaceholderUrl('small_image');
                    $image['size'] = 0;
                    $this->_logger->warning($e);
                }
            }
            return $this->jsonEncoder->encode($images);
        }
        return '[]';
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     *
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort(
                $images,
                function ($imageA, $imageB) {
                    return ($imageA['position'] < $imageB['position']) ? -1 : 1;
                }
            );
        }
        return $images;
    }

    /**
     * @return string
     */
    public function getImagesValuesJson()
    {
        $values = [];
        /** @var AttributeInterface|ScopedAttributeInterface $attribute */
        foreach ($this->getMediaAttributes() as $attribute) {
            $values[$attribute->getAttributeCode()] = $this->getElement()
                ->getDataObject()
                ->getData($attribute->getAttributeCode());
        }
        return $this->jsonEncoder->encode($values);
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];

        /** @var AttributeInterface|ScopedAttributeInterface $attribute */
        foreach ($this->getMediaAttributes() as $attribute) {
            $imageTypes[$attribute->getAttributeCode()] = [
                'code'  => $attribute->getAttributeCode(),
                'value' => $this->getElement()->getDataObject()->getData($attribute->getAttributeCode()),
                'label' => $attribute->getFrontend()->getLabel(),
                'scope' => __($this->getElement()->getScopeLabel($attribute)),
                'name'  => $this->getElement()->getAttributeFieldName($attribute),
            ];
        }
        return $imageTypes;
    }

    /**
     * Retrieve default state allowance
     *
     * @return bool
     */
    public function hasUseDefault()
    {
        foreach ($this->getMediaAttributes() as $attribute) {
            if ($this->getElement()->canDisplayUseDefault($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve media attributes
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        $result = $this->getElement()->getDataObject()->getMediaAttributes();
        if (!$result) {
            $result = [];
        }

        return $result;
    }

    /**
     * Retrieve JSON data
     *
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->jsonEncoder->serialize($this->getImageTypes());
    }
}
