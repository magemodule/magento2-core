<?php

namespace MageModule\Core\Model\ResourceModel;

use MageModule\Core\Model\AbstractExtensibleModel;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Store\Model\Store;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;

abstract class AbstractEntity extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * @var \MageModule\Core\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * AbstractEntity constructor.
     *
     * @param \MageModule\Core\Helper\Data                   $helper
     * @param \Magento\Eav\Model\Entity\Context              $context
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\Store\Model\StoreManagerInterface     $storeManager
     * @param array                                          $data
     */
    public function __construct(
        \MageModule\Core\Helper\Data $helper,
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper        = $helper;
        $this->entityManager = $entityManager;
        $this->storeManager  = $storeManager;
    }

    /**
     * @param DataObject|AbstractModel|AbstractExtensibleModel $object
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(DataObject $object)
    {
        if ($object->isObjectNew() &&
            !$object->getData(AbstractExtensibleModel::ATTRIBUTE_SET_ID)
        ) {
            $object->setData(
                AbstractExtensibleModel::ATTRIBUTE_SET_ID,
                $this->getEntityType()->getDefaultAttributeSetId()
            );
        }

        if (!$object->hasData(AbstractExtensibleModel::STORE_ID)) {
            $object->setData(
                AbstractExtensibleModel::STORE_ID,
                Store::DEFAULT_STORE_ID
            );
        }

        $this->prepareUseDefaults($object);

        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);

        return $this;
    }

    /**
     * @param DataObject|AbstractModel|AbstractExtensibleModel $object
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws \Exception
     */
    protected function _afterSave(DataObject $object)
    {
        $this->saveWebsiteValues($object);
        $this->processUseDefaults($object);

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function prepareUseDefaults(AbstractModel $object)
    {
        if ($object->getStoreId()) {
            $useDefaults = $object->getData('use_defaults');
            if (!is_array($useDefaults)) {
                $useDefaults = [];
            }

            foreach ($object->getData() as $key => $value) {
                if ($value === false) {
                    $useDefaults[$key] = 1;
                }
            }

            $eligibleAttributes = [];

            $attributes = $this->getAttributesByCode();

            /** @var ScopedAttributeInterface|AbstractAttribute $attribute */
            foreach ($attributes as $attributeCode => $attribute) {
                if (!$attribute->isStatic() && !$attribute->isScopeGlobal()) {
                    $eligibleAttributes[$attributeCode] = $attribute;
                }
            }

            $this->helper->boolify($useDefaults);
            $this->helper->removeFalse($useDefaults);
            $eligibleAttributes = array_intersect_key($eligibleAttributes, $useDefaults);

            if (!empty($eligibleAttributes)) {
                $useDefaults = array_fill_keys(array_keys($eligibleAttributes), false);
            }

            $object->addData($useDefaults);
            $object->addData(['use_defaults' => $eligibleAttributes]);
        }

        return $this;
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return $this
     * @throws \Exception
     */
    protected function processUseDefaults(AbstractModel $object)
    {
        if ($object->getStoreId()) {
            $websiteStoreIds = $this->storeManager
                ->getStore($object->getStoreId())
                ->getWebsite()
                ->getStoreIds(true);

            if (!$websiteStoreIds) {
                return $this;
            }

            $useDefaults = $object->getData('use_defaults');
            if (is_array($useDefaults)) {
                $connection = $this->getConnection();

                /** @var ScopedAttributeInterface|AbstractAttribute $attribute */
                foreach ($useDefaults as $attribute) {
                    $entityIdField = $attribute->getEntityIdField();
                    $connection->beginTransaction();
                    try {
                        if ($attribute->isScopeWebsite()) {
                            $connection->delete(
                                $attribute->getBackendTable(),
                                [
                                    $entityIdField . ' =?' => $object->getId(),
                                    'attribute_id =?'      => $attribute->getAttributeId(),
                                    'store_id IN(?)'       => $websiteStoreIds
                                ]
                            );
                        } else {
                            $connection->delete(
                                $attribute->getBackendTable(),
                                [
                                    $entityIdField . ' =?' => $object->getId(),
                                    'attribute_id =?'      => $attribute->getAttributeId(),
                                    'store_id =?'          => $object->getStoreId()
                                ]
                            );
                        }
                        $connection->commit();
                    } catch (\Exception $e) {
                        $connection->rollBack();
                        throw $e;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return $this
     * @throws \Exception
     */
    protected function saveWebsiteValues(AbstractModel $object)
    {
        if ($object->getStoreId()) {
            $websiteStoreIds = $this->storeManager
                ->getStore($object->getStoreId())
                ->getWebsite()
                ->getStoreIds(true);

            $key = array_search($object->getStoreId(), $websiteStoreIds);
            if ($key !== false) {
                unset($websiteStoreIds[$key]);
            }

            if (!$websiteStoreIds) {
                return $this;
            }

            $attributes = $this->getAttributesByCode();
            $connection = $this->getConnection();

            foreach ($object->getData() as $key => $value) {
                if (isset($attributes[$key])) {
                    /** @var ScopedAttributeInterface|AbstractAttribute $attribute */
                    $attribute = $attributes[$key];
                    if ($attribute->isScopeWebsite()) {
                        $value = $this->getAttributeRawValue($object, $attribute);

                        $connection->beginTransaction();

                        try {
                            foreach ($websiteStoreIds as $storeId) {
                                $connection->insertOnDuplicate(
                                    $attribute->getBackendTable(),
                                    [
                                        $attribute->getEntityIdField() => $object->getId(),
                                        'attribute_id'                 => $attribute->getAttributeId(),
                                        'store_id'                     => $storeId,
                                        'value'                        => $value
                                    ],
                                    ['value']
                                );
                            }
                            $connection->commit();
                        } catch (\Exception $e) {
                            $connection->rollBack();
                            throw $e;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel      $object
     * @param ScopedAttributeInterface|AbstractAttribute $attribute
     * @param int|null                                   $storeId
     *
     * @return string|bool|int|float|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAttributeRawValue(
        AbstractModel $object,
        AbstractAttribute $attribute,
        $storeId = null
    ) {
        if ($storeId === null) {
            $storeId = $object->getStoreId();
        }

        if (!$attribute->isStatic()) {
            $select = $this->getConnection()->select()->from(
                $attribute->getBackendTable(),
                'value'
            );

            $select->where($attribute->getEntityIdField() . ' =?', $object->getId());
            $select->where('attribute_id =?', $attribute->getAttributeId());
            $select->where('store_id =?', $storeId);
        } else {
            $select = $this->getConnection()->select()->from(
                $attribute->getBackendTable(),
                $attribute->getAttributeCode()
            );

            $select->where($attribute->getEntityIdField() . ' =?', $object->getId());
        }

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     * @param int                                   $entityId
     * @param array|null                            $attributes
     *
     * @return $this
     */
    public function load($object, $entityId, $attributes = [])
    {
        $select = $this->_getLoadRowSelect($object, $entityId);
        $row    = $this->getConnection()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        $this->loadAttributesForObject($attributes, $object);
        $this->entityManager->load($object, $entityId);

        return $this;
    }
}
