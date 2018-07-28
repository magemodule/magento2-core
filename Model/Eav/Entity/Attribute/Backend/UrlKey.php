<?php

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

//TODO test once more with url_key as global, website, and store view, just for sanity
use MageModule\Core\Model\ResourceModel\Entity\UrlKeyGenerator;
use MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;

class UrlKey extends \MageModule\Core\Model\Eav\Entity\Attribute\Backend\UrlKeyFormat
{
    /**
     * @var UrlKeyGenerator
     */
    private $urlKeyGenerator;

    /**
     * @var UrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * UrlKey constructor.
     *
     * @param UrlKeyGenerator     $urlKeyGenerator
     * @param UrlRewriteGenerator $urlRewriteGenerator
     * @param StorageInterface    $storage
     * @param ResourceConnection  $resource
     * @param FilterManager       $filterManager
     */
    public function __construct(
        UrlKeyGenerator $urlKeyGenerator,
        UrlRewriteGenerator $urlRewriteGenerator,
        StorageInterface $storage,
        ResourceConnection $resource,
        FilterManager $filterManager
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
     * @throws LocalizedException
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
     * @throws LocalizedException
     * @throws UrlAlreadyExistsException
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
