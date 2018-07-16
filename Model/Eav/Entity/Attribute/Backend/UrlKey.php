<?php

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlKey extends \MageModule\Core\Model\Eav\Entity\Attribute\Backend\UrlKeyFormat
{
    /**
     * @var \MageModule\Core\Helper\Eav\Attribute
     */
    private $attributeHelper;

    /**
     * @var \MageModule\Core\Model\ResourceModel\UrlRewrite
     */
    private $urlRewriteResource;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Model\StorageInterface
     */
    private $storage;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $targetPathBase;

    /**
     * @var string|null
     */
    private $targetPathIdKey;

    /**
     * @var string|null
     */
    protected $requestPathSuffix;

    /**
     * UrlKey constructor.
     *
     * @param \MageModule\Core\Helper\Eav\Attribute              $attributeHelper
     * @param \MageModule\Core\Model\ResourceModel\UrlRewrite    $urlRewriteResource
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory        $urlRewriteFactory
     * @param \Magento\UrlRewrite\Model\StorageInterface         $storage
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Filter\FilterManager            $filterManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string                                             $entity
     * @param string                                             $targetPathBase
     * @param string|null                                        $targetPathIdKey
     * @param string|null                                        $requestPathSuffix
     */
    public function __construct(
        \MageModule\Core\Helper\Eav\Attribute $attributeHelper,
        \MageModule\Core\Model\ResourceModel\UrlRewrite $urlRewriteResource,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\UrlRewrite\Model\StorageInterface $storage,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $entity,
        $targetPathBase,
        $targetPathIdKey = null,
        $requestPathSuffix = null
    ) {
        parent::__construct($filterManager);

        $this->attributeHelper    = $attributeHelper;
        $this->urlRewriteResource = $urlRewriteResource;
        $this->urlRewriteFactory  = $urlRewriteFactory;
        $this->storage            = $storage;
        $this->storeManager       = $storeManager;
        $this->scopeConfig        = $scopeConfig;
        $this->entity             = $entity;
        $this->targetPathBase     = $targetPathBase;
        $this->targetPathIdKey    = $targetPathIdKey;
        $this->requestPathSuffix  = $requestPathSuffix;
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
                $value,
                $this->getSuffix($object)
            );

            $suffix = $this->getSuffix($object);
            if ($suffix) {
                $value = preg_replace('#' . preg_quote($suffix) . '$#i', '', $value, 1);
            }

            $object->setData($attributeCode, $value);
        }

        return parent::beforeSave($object);
    }

    /**
     * @param \Magento\Framework\DataObject|\MageModule\Core\Model\AbstractExtensibleModel $object
     *
     * @return string
     */
    protected function getTargetPath($object)
    {
        $targetPath = $this->targetPathBase;
        if ($this->targetPathIdKey) {
            $targetPath .= '/' . $this->targetPathIdKey . '/' . $object->getId();
        }

        return $targetPath;
    }

    /**
     * @param \Magento\Framework\DataObject|\MageModule\Core\Model\AbstractExtensibleModel $object
     *
     * @return null|string
     */
    protected function getSuffix($object)
    {
        return $this->requestPathSuffix;
    }

    /**
     * @param \Magento\Framework\DataObject|\MageModule\Core\Model\AbstractExtensibleModel $object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value         = $object->getData($attributeCode);
        $storeId       = (int)$object->getData($object::STORE_ID);

        if ($value) {
            //TODO make sure to only deal with rewrites when values have changed
            //TODO verify the behavior when value is changed at default scope, website scope, store scope

            /** Determine which stores we need to save URL rewrite value for */
            if ($storeId === \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                $storeIds        = array_keys($this->storeManager->getStores());
                $storesWithValue = $this->attributeHelper
                    ->getStoreIdsHavingAttributeValue(
                        $this->getAttribute(),
                        $object
                    );

                if ($object->hasData($object::STORE_ID)) {
                    $key = array_search($storeId, $storesWithValue);
                    if ($key !== false) {
                        unset($storesWithValue[$key]);
                    }
                }

                $storeIds = array_diff($storeIds, $storesWithValue);
            } elseif ($value) {
                $storeIds = [$storeId];
            }

            foreach ($storeIds as $storeId) {
                $storageRewrite = $this->storage->findOneByData(
                    [
                        UrlRewrite::ENTITY_TYPE => $this->entity,
                        UrlRewrite::ENTITY_ID   => $object->getId(),
                        UrlRewrite::STORE_ID    => $storeId
                    ]
                );

                /** @var \Magento\UrlRewrite\Model\UrlRewrite $rewrite */
                $rewrite = $this->urlRewriteFactory->create();
                if ($storageRewrite) {
                    $rewrite->setId($storageRewrite->getUrlRewriteId());
                }
                $rewrite->setStoreId($storeId);
                $rewrite->setEntityType($this->entity);
                $rewrite->setEntityId($object->getId());
                $rewrite->setRequestPath($value . $this->getSuffix($object));
                $rewrite->setTargetPath($this->getTargetPath($object));
                $rewrite->setRedirectType(0);
                $this->urlRewriteResource->save($rewrite);
            }

            //TODO insert/update url rewrite
            //TODO if update url rewrite, create 301 redirect from old to new
        } elseif ($value === false) {
            //TODO delete url rewrite for store id if value empty
        }

        return parent::afterSave($object);
    }

    public function afterDelete($object)
    {
        //TODO delete URL rewrite
        return parent::afterDelete($object);
    }
}
