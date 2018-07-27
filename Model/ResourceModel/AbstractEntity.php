<?php

namespace MageModule\Core\Model\ResourceModel;

use MageModule\Core\Model\AbstractExtensibleModel;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Framework\Stdlib\ArrayManager;
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
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $websiteStoreIds = [];

    /**
     * AbstractEntity constructor.
     *
     * @param \MageModule\Core\Helper\Data                   $helper
     * @param \Magento\Eav\Model\Entity\Context              $context
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\Framework\Stdlib\ArrayManager         $arrayManager
     * @param \Magento\Store\Model\StoreManagerInterface     $storeManager
     * @param array                                          $data
     */
    public function __construct(
        \MageModule\Core\Helper\Data $helper,
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper        = $helper;
        $this->entityManager = $entityManager;
        $this->arrayManager  = $arrayManager;
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
     * @param array                     &$delete
     * @param AbstractAttribute         $attribute
     * @param AbstractEntity|DataObject $object
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _aggregateDeleteData(&$delete, $attribute, $object)
    {
        foreach ($attribute->getBackend()->getAffectedFields($object) as $tableName => $valuesData) {
            if (!isset($delete[$tableName])) {
                $delete[$tableName] = [];
            }
            $delete[$tableName] = array_merge((array)$delete[$tableName], $valuesData);
        }
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $newObject
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _collectSaveData($newObject)
    {
        if (!$newObject instanceof AbstractExtensibleModel) {
            return parent::_collectSaveData($newObject);
        }

        $newData  = $newObject->getData();
        $entityId = $newObject->getData($this->getEntityIdField());

        $entityRow = [];
        $insert    = [];
        $update    = [];
        $delete    = [];

        if (!empty($entityId)) {
            $origData = $newObject->getOrigData();
            if (empty($origData)) {
                $origData = $this->_getOrigObject($newObject)->getOrigData();
            }

            if ($origData === null) {
                $origData = [];
            }

            foreach ($origData as $k => $v) {
                if (!array_key_exists($k, $newData)) {
                    unset($origData[$k]);
                }
            }
        } else {
            $origData = [];
        }

        $staticFields   = $this->getConnection()->describeTable($this->getEntityTable());
        $staticFields   = array_keys($staticFields);
        $attributeCodes = array_keys($this->_attributesByCode);

        $useDefault = $newObject->getData('use_default');
        if (!is_array($useDefault)) {
            $useDefault = [];
        }

        foreach ($newData as $k => $v) {
            if (!in_array($k, $staticFields) && !in_array($k, $attributeCodes)) {
                continue;
            }

            $attribute = $this->getAttribute($k);
            if (empty($attribute)) {
                continue;
            }

            if ((!$attribute->isInSet($newObject->getAttributeSetId()) && !in_array($k, $staticFields)) ||
                (array_key_exists($attribute->getAttributeCode(), $useDefault) && $newObject->getStoreId())
            ) {
                $this->_aggregateDeleteData($delete, $attribute, $newObject);
                continue;
            }

            $attrId = $attribute->getAttributeId();

            if (!$attribute->getBackend()->isScalar()) {
                continue;
            }

            if ($this->isAttributeStatic($k)) {
                $entityRow[$k] = $this->_prepareStaticValue($k, $v);
                continue;
            }

            if ($this->_canUpdateAttribute($attribute, $v, $origData)) {
                if ($this->_isAttributeValueEmpty($attribute, $v)) {
                    $this->_aggregateDeleteData($delete, $attribute, $newObject);
                } else {
                    $update[$attrId] = [
                        'value_id' => $attribute->getBackend()->getEntityValueId($newObject),
                        'value'    => is_array($v) ? array_shift($v) : $v
                    ];
                }
            } elseif (!$this->_isAttributeValueEmpty($attribute, $v)) {
                $insert[$attrId] = is_array($v) ? array_shift($v) : $v;
            }
        }

        $result = compact('newObject', 'entityRow', 'insert', 'update', 'delete');

        return $result;
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     * @param AbstractAttribute                     $attribute
     * @param mixed                                 $value
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveAttribute($object, $attribute, $value)
    {
        if ($attribute instanceof ScopedAttributeInterface) {
            $table = $attribute->getBackend()->getTable();
            if (!isset($this->_attributeValuesToSave[$table])) {
                $this->_attributeValuesToSave[$table] = [];
            }

            $entityIdField = $attribute->getBackend()->getEntityIdField();

            $storeId = $object->getStoreId();
            if ($attribute->isScopeWebsite()) {
                $storeIds = $this->_getStoreIdsForWebsite($storeId);
            } elseif ($attribute->isScopeStore()) {
                $storeIds = [$storeId];
            } else {
                $storeIds = [Store::DEFAULT_STORE_ID];
            }

            foreach ($storeIds as $storeId) {
                $data = [
                    $entityIdField                         => $object->getId(),
                    ScopedAttributeInterface::ATTRIBUTE_ID => $attribute->getId(),
                    ScopedAttributeInterface::STORE_ID     => $storeId,
                    ScopedAttributeInterface::VALUE        => $this->_prepareValueForSave($value, $attribute)
                ];

                if (!$this->getEntityTable() ||
                    $this->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE
                ) {
                    $data['entity_type_id'] = $object->getEntityTypeId();
                }

                $this->_attributeValuesToSave[$table][] = $data;
            }

            return $this;
        }

        return parent::_saveAttribute($object, $attribute, $value);
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     * @param string                                $table
     * @param array                                 $info
     *
     * @return $this|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        if ($object instanceof AbstractExtensibleModel) {
            $connection = $this->getConnection();
            $valueIds   = [];
            foreach ($info as $itemData) {
                /** @var ScopedAttributeInterface $attribute */
                $attribute = $this->getAttribute($itemData['attribute_id']);
                $storeId   = $object->getStoreId();
                if ($attribute->isScopeWebsite()) {
                    $storeIds = $this->_getStoreIdsForWebsite($storeId);
                } elseif ($attribute->isScopeStore()) {
                    $storeIds = [$storeId];
                } else {
                    $storeIds = [Store::DEFAULT_STORE_ID];
                }

                $select = $connection->select()->from($table, ScopedAttributeInterface::VALUE_ID);
                $select->where($attribute->getEntityIdField() . ' =?', $object->getEntityId());
                $select->where(ScopedAttributeInterface::ATTRIBUTE_ID . ' =?', $itemData['attribute_id']);
                $select->where(ScopedAttributeInterface::STORE_ID . ' IN(?)', $storeIds);

                $valueIds = array_merge($valueIds, $connection->fetchCol($select));
            }

            if (empty($valueIds)) {
                return $this;
            }

            if (isset($this->_attributeValuesToDelete[$table])) {
                $this->_attributeValuesToDelete[$table] =
                    array_merge($this->_attributeValuesToDelete[$table], $valueIds);
            } else {
                $this->_attributeValuesToDelete[$table] = $valueIds;
            }

            return $this;
        }

        return parent::_deleteAttributes($object, $table, $info);
    }

    /**
     * @param int $storeId
     *
     * @return int[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStoreIdsForWebsite($storeId)
    {
        if (!isset($this->websiteStoreIds[$storeId])) {
            $this->websiteStoreIds[$storeId] = $this->storeManager
                ->getStore($storeId)
                ->getWebsite()
                ->getStoreIds();
        }

        return $this->websiteStoreIds[$storeId];
    }

    /**
     * @param DataObject|AbstractModel|AbstractExtensibleModel $object
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws \Exception
     */
    protected function _afterSave(DataObject $object)
    {
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
            $useDefaults = $object->getData('use_default');
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
            $object->addData(['use_default' => $eligibleAttributes]);
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
        $storeId = $object->getStoreId();
        if ($storeId) {
            $websiteStoreIds = $this->_getStoreIdsForWebsite($object->getStoreId());
            if (!$websiteStoreIds) {
                return $this;
            }

            $useDefaults = $object->getData('use_default');
            if (is_array($useDefaults)) {
                $connection = $this->getConnection();

                /** @var ScopedAttributeInterface|AbstractAttribute $attribute */
                foreach ($useDefaults as $attribute) {
                    if ($attribute->isScopeWebsite()) {
                        $storeIds = $this->_getStoreIdsForWebsite($storeId);
                    } elseif ($attribute->isScopeStore()) {
                        $storeIds = [$storeId];
                    } else {
                        $storeIds = [Store::DEFAULT_STORE_ID];
                    }

                    $connection->delete(
                        $attribute->getBackendTable(),
                        [
                            $attribute->getEntityIdField() . ' =?'         => $object->getId(),
                            ScopedAttributeInterface::ATTRIBUTE_ID . ' =?' => $attribute->getAttributeId(),
                            ScopedAttributeInterface::STORE_ID . ' IN(?)'  => $storeIds
                        ]
                    );
                }
            }
        }

        return $this;
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
        foreach ($object->getData() as $key => $value) {
            $object->setOrigData($key, $value);
        }

        return $this;
    }
}
