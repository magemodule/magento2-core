<?php

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

//TODO test in single store mode
//TODO test deletion of store
//TODO test adding store
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlKey extends \MageModule\Core\Model\Eav\Entity\Attribute\Backend\UrlKeyFormat
{
    /**
     * @var \MageModule\Core\Model\ResourceModel\Entity\UrlKeyGenerator
     */
    private $urlKeyGenerator;

    /**
     * @var \MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\StorageInterface
     */
    private $storage;

    /**
     * UrlKey constructor.
     *
     * @param \MageModule\Core\Model\ResourceModel\Entity\UrlKeyGenerator     $urlKeyGenerator
     * @param \MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator $urlRewriteGenerator
     * @param \Magento\UrlRewrite\Model\StorageInterface                      $storage
     * @param \Magento\Framework\App\ResourceConnection                       $resource
     * @param \Magento\Framework\Filter\FilterManager                         $filterManager
     */
    public function __construct(
        \MageModule\Core\Model\ResourceModel\Entity\UrlKeyGenerator $urlKeyGenerator,
        \MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator $urlRewriteGenerator,
        \Magento\UrlRewrite\Model\StorageInterface $storage,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($resource, $filterManager);

        $this->urlKeyGenerator     = $urlKeyGenerator;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
        $this->storage             = $storage;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);

        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getAttributeCode();
        $value     = $object->getData($attrCode);

        if ($value && $object->dataHasChangedFor($attrCode)) {
            $this->urlKeyGenerator->setAttribute($this->getAttribute());
            $value = $this->urlKeyGenerator->generate($object);
            if ($value) {
                $object->setData($attrCode, $value);
            }
        }

        $this->validate($object);

        return $this;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function afterSave($object)
    {
        parent::afterSave($object);

        $this->urlRewriteGenerator->setAttribute($this->getAttribute());
        $this->urlRewriteGenerator->generate($object, true);

        return $this;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     */
    public function afterDelete($object)
    {
        $this->storage->deleteByData(
            [
                UrlRewrite::ENTITY_TYPE => $this->getAttribute()->getEntityType()->getEntityTypeCode(),
                UrlRewrite::ENTITY_ID   => $this->getObjectId($object)
            ]
        );

        return parent::afterDelete($object);
    }
}
