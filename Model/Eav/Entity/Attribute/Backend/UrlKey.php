<?php

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

//TODO test in single store mode
//TODO test in store scope and website scope
//TODO test deletion of store
//TODO test changing of suffix
use MageModule\Core\Model\AbstractExtensibleModel;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class UrlKey
 *
 * @package MageModule\Core\Model\Eav\Entity\Attribute\Backend
 */
class UrlKey extends \MageModule\Core\Model\Eav\Entity\Attribute\Backend\UrlKeyFormat
{
    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Model\StorageInterface
     */
    private $storage;

    /**
     * @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite
     */
    private $urlRewriteResource;

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
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\UrlRewrite\Model\StorageInterface            $storage
     * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite    $urlRewriteResource
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Framework\Filter\FilterManager               $filterManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection             $resource
     * @param string                                                $entity
     * @param string                                                $targetPathBase
     * @param null|string                                           $targetPathIdKey
     * @param null|string                                           $requestPathSuffix
     */
    public function __construct(
        \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory,
        \Magento\UrlRewrite\Model\StorageInterface $storage,
        \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $urlRewriteResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        $entity,
        $targetPathBase,
        $targetPathIdKey = null,
        $requestPathSuffix = null
    ) {
        parent::__construct($resource, $filterManager);

        $this->urlRewriteFactory  = $urlRewriteFactory;
        $this->storage            = $storage;
        $this->urlRewriteResource = $urlRewriteResource;
        $this->storeManager       = $storeManager;
        $this->scopeConfig        = $scopeConfig;
        $this->entity             = $entity;
        $this->targetPathBase     = $targetPathBase;
        $this->targetPathIdKey    = $targetPathIdKey;
        $this->requestPathSuffix  = $requestPathSuffix;
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
        $attrCode  = $attribute->getName();
        $value     = $object->getData($attrCode);

        $storeIdField = 'store_id';
        if ($object instanceof AbstractExtensibleModel) {
            $storeIdField = AbstractExtensibleModel::STORE_ID;
        }

        if ($value && $object->dataHasChangedFor($attrCode)) {
            if ($attribute instanceof ScopedAttributeInterface) {
                $storeId = $object->getData($storeIdField);

                /**
                 * check to make sure desired url key is available in the specified scope.
                 * if url key is not available, append some sort of uniqueness to it
                 */
                if ($attribute->isScopeWebsite() && $storeId) {
                    /** if website scope and not saving to default scope */
                    $storeIds = $this->storeManager
                        ->getStore($storeId)
                        ->getWebsite()
                        ->getStoreIds();
                } elseif ($attribute->isScopeStore() && $storeId) {
                    /** if store scope and not saving to default store */
                    $storeIds = [$storeId];
                } elseif ($attribute->isScopeGlobal()) {
                    /** if global scope, need to check availablility for all store ids */
                    $storeIds = array_keys($this->storeManager->getStores(false));
                }
            } else {
                //TODO test further when using a non-scoped object/attribute. Currently, we aren't using any
                /** if not scoped attribute, then it's global */
                $storeIds = array_keys($this->storeManager->getStores(false));
            }

            $urlKeys = $this->getProjectedUrlKeys($object, $value, $storeIds);

            $i = 1;
            while (!$this->checkUrlKeyAvailability($urlKeys) && $i <= 100) {
                $newValue = $this->makeUnique($value);
                $urlKeys  = $this->getProjectedUrlKeys($object, $newValue, $storeIds);
                $value    = $newValue;
                $i++;
            }

            $object->setData($attrCode, $value);
        }

        $this->validate($object);

        return $this;
    }

    /**
     * @param DataObject|AbstractModel $object
     * @param string|null
     *
     * @return string|null
     */
    protected function getSuffix($object, $storeId = null)
    {
        return $this->requestPathSuffix;
    }

    /**
     * @param DataObject|AbstractModel $object
     * @param string                   $value
     * @param int[]                    $storeIds
     *
     * @return array
     */
    protected function getProjectedUrlKeys($object, $value, array $storeIds)
    {
        $objectId = $this->getObjectId($object);

        $suffixes = [];
        foreach ($storeIds as $storeId) {
            $suffixes[$storeId] = $this->getSuffix($object, $storeId);
        }

        $urlKeys = [];
        foreach ($storeIds as $storeId) {
            $suffix     = $suffixes[$storeId];
            $rawValue   = preg_replace('#' . preg_quote($suffix) . '$#i', '', $value, 1);
            $finalValue = $rawValue . $suffix;

            $urlKeyData = [
                UrlRewrite::STORE_ID     => $storeId,
                UrlRewrite::ENTITY_ID    => $objectId,
                UrlRewrite::ENTITY_TYPE  => $this->entity,
                UrlRewrite::REQUEST_PATH => $finalValue
            ];

            $urlKeys[] = array_filter($urlKeyData, 'strlen');
        }

        return $urlKeys;
    }

    /**
     * @param array $urlKeyData
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkUrlKeyAvailability(array $urlKeyData)
    {
        $connection = $this->urlRewriteResource->getConnection();

        $excludeValueIds = [];
        $requestPaths    = [];
        $storeIds        = [];
        foreach ($urlKeyData as &$urlKeyDatum) {
            $rewrite = $this->storage->findOneByData($urlKeyDatum);
            if ($rewrite instanceof UrlRewrite) {
                $excludeValueIds[] = $rewrite->getUrlRewriteId();
            }

            $requestPaths[] = $urlKeyDatum[UrlRewrite::REQUEST_PATH];
            $storeIds[]     = $urlKeyDatum[UrlRewrite::STORE_ID];
        }

        $excludeValueIds = array_unique($excludeValueIds);
        $requestPaths    = array_unique($requestPaths);
        $storeIds        = array_unique($storeIds);

        $select = $connection->select()->from(
            $this->urlRewriteResource->getMainTable(),
            UrlRewrite::URL_REWRITE_ID
        );
        $select->where(UrlRewrite::REQUEST_PATH . ' IN(?)', $requestPaths);
        $select->where(UrlRewrite::STORE_ID . ' IN(?)', $storeIds);

        if ($excludeValueIds) {
            $select->where(UrlRewrite::URL_REWRITE_ID . ' NOT IN(?)', $excludeValueIds);
        }

        return $connection->fetchOne($select) ? false : true;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function makeUnique($string)
    {
        return $string . '-' . substr(uniqid(rand(), true), 0, 4);
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return \MageModule\Core\Model\Eav\Entity\Attribute\Backend\UrlKeyFormat
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function afterSave($object)
    {
        $objectId = $this->getObjectId($object);
        $urlKeys  = $this->getFinalUrlKeys($object);

        foreach ($urlKeys as $storeId => $urlKey) {
            $requestPath = $this->getRequestPath($object, $urlKey, $storeId);
            if (!$requestPath) {
                continue;
            }

            $targetPath = $this->getTargetPath($objectId);
            if (!$targetPath) {
                continue;
            }

            $rewrite = $this->storage->findOneByData(
                [
                    UrlRewrite::STORE_ID      => $storeId,
                    UrlRewrite::ENTITY_ID     => $objectId,
                    UrlRewrite::ENTITY_TYPE   => $this->entity,
                    UrlRewrite::REDIRECT_TYPE => 0
                ]
            );

            $redirect = $this->storage->findOneByData(
                [
                    UrlRewrite::STORE_ID      => $storeId,
                    UrlRewrite::ENTITY_ID     => $objectId,
                    UrlRewrite::ENTITY_TYPE   => $this->entity,
                    UrlRewrite::REDIRECT_TYPE => 301,
                    UrlRewrite::REQUEST_PATH  => $requestPath
                ]
            );

            /**
             * if there is a 301 for the SAME object with the SAME
             * request path, we just need to flip flop objects. This
             * loop fires when updating to a request path that used to
             * exist for the object and the user wants to restore that
             * request path
             */
            if ($rewrite instanceof UrlRewrite &&
                $redirect instanceof UrlRewrite &&
                $redirect->getRequestPath() === $requestPath
            ) {
                $save = [];

                $existingRedirects = $this->storage->findAllByData(
                    [
                        UrlRewrite::STORE_ID      => $storeId,
                        UrlRewrite::ENTITY_ID     => $objectId,
                        UrlRewrite::ENTITY_TYPE   => $this->entity,
                        UrlRewrite::REDIRECT_TYPE => 301
                    ]
                );

                foreach ($existingRedirects as $existingRedirect) {
                    if ($existingRedirect->getRequestPath() !== $requestPath) {
                        $existingRedirect->setTargetPath($requestPath);
                        $save[] = $existingRedirect;
                    }
                }

                $redirect->setRequestPath($rewrite->getRequestPath());
                $redirect->setTargetPath($requestPath);
                $save[] = $redirect;

                $rewrite->setRequestPath($requestPath);
                $save[] = $rewrite;

                $this->storage->replace($save);
                continue;
            }

            if (!$rewrite instanceof UrlRewrite) {
                $rewrite = $this->urlRewriteFactory->create();
            }

            $oldPath = $rewrite->getRequestPath();
            $newPath = $requestPath;

            $dataHasChanged = $oldPath && ($rewrite->getRequestPath() !== $requestPath);

            $rewrite->setEntityType($this->entity);
            $rewrite->setEntityId($objectId);
            $rewrite->setRequestPath($requestPath);
            $rewrite->setTargetPath($targetPath);
            $rewrite->setRedirectType(0);
            $rewrite->setStoreId($storeId);
            $save = [$rewrite];

            if ($dataHasChanged) {
                $existingRedirects = $this->storage->findAllByData(
                    [
                        UrlRewrite::STORE_ID      => $storeId,
                        UrlRewrite::ENTITY_ID     => $objectId,
                        UrlRewrite::ENTITY_TYPE   => $this->entity,
                        UrlRewrite::REDIRECT_TYPE => 301
                    ]
                );

                foreach ($existingRedirects as $existingRedirect) {
                    if ($existingRedirect->getRequestPath() !== $oldPath) {
                        $existingRedirect->setTargetPath($requestPath);
                        $save[] = $existingRedirect;
                    }
                }

                $redirect = $this->urlRewriteFactory->create();
                $redirect->setUrlRewriteId(null);
                $redirect->setEntityType($this->entity);
                $redirect->setEntityId($objectId);
                $redirect->setRequestPath($oldPath);
                $redirect->setTargetPath($newPath);
                $redirect->setRedirectType(301);
                $redirect->setStoreId($storeId);
                $save[] = $redirect;
            }

            $this->storage->replace($save);
        }

        return parent::afterSave($object);
    }

    /**
     * Returns $storeId => $urlKey pairs
     *
     * @param DataObject|AbstractModel $object
     *
     * @return array
     */
    protected function getFinalUrlKeys($object)
    {
        $pairs    = $this->getStoreIdValuePairs($object);
        $storeIds = array_keys($this->storeManager->getStores(false));

        $defaultValue = null;
        if (isset($pairs[Store::DEFAULT_STORE_ID])) {
            $defaultValue = $pairs[Store::DEFAULT_STORE_ID];
            unset($pairs[Store::DEFAULT_STORE_ID]);
        }

        $finalValues = [];
        foreach ($storeIds as $storeId) {
            $finalValues[$storeId] = isset($pairs[$storeId]) ?
                $pairs[$storeId] :
                $defaultValue;
        }

        return $finalValues;
    }

    /**
     * @param int   $objectId
     * @param array $additionalParams
     *
     * @return string
     */
    protected function getTargetPath($objectId, $additionalParams = [])
    {
        $path = $this->targetPathBase;
        $path .= '/' . $this->targetPathIdKey . '/' . $objectId;

        foreach ($additionalParams as $key => $value) {
            $path .= '/' . $key . '/' . $value;
        }

        return $path;
    }

    /**
     * @param DataObject|AbstractModel $object
     * @param string                   $urlKey
     * @param int                      $storeId
     *
     * @return string|null
     */
    protected function getRequestPath($object, $urlKey, $storeId)
    {
        $urlKey = trim($urlKey);
        $suffix = trim($this->getSuffix($object, $storeId));

        return $urlKey ? $urlKey . $suffix : null;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return \MageModule\Core\Model\Eav\Entity\Attribute\Backend\UrlKeyFormat
     */
    public function afterDelete($object)
    {
        $this->storage->deleteByData(
            [
                UrlRewrite::ENTITY_TYPE => $this->entity,
                UrlRewrite::ENTITY_ID => $this->getObjectId($object)
            ]
        );

        return parent::afterDelete($object);
    }
}
