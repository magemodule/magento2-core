<?php

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

class UrlKey extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \MageModule\Core\Model\ResourceModel\UrlRewrite
     */
    private $urlRewriteResource;

    /**
     * UrlKey constructor.
     *
     * @param \MageModule\Core\Model\ResourceModel\UrlRewrite $urlRewriteResource
     */
    public function __construct(
        \MageModule\Core\Model\ResourceModel\UrlRewrite $urlRewriteResource
    ) {
        $this->urlRewriteResource = $urlRewriteResource;
    }

    /**
     * @param \Magento\Framework\DataObject|\MageModule\Core\Model\AbstractExtensibleModel $object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value         = $object->getData($attributeCode);
        if ($value) {
            $value = $this->urlRewriteResource->getUniqueUrlKey(
                $object->getId(),
                $object->getStoreId(),
                $value
            );

            $object->setData($attributeCode, $value);
        }

        return parent::beforeSave($object);
    }

    /**
     * @param \Magento\Framework\DataObject|\MageModule\Core\Model\AbstractExtensibleModel $object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function afterSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value         = $object->getData($attributeCode);
        if ($value) {
            //TODO insert/update url rewrite
            //TODO if update url rewrite, create 301 redirect from old to new
        } else {
            //TODO delete url rewrite for store id if value empty
        }

        return parent::afterSave($object);
    }
}
