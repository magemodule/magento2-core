<?php

namespace MageModule\Core\Model\ResourceModel;

use MageModule\Core\Model\AbstractExtensibleModel;
use Magento\Store\Model\Store;

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
     * AbstractEntity constructor.
     *
     * @param \MageModule\Core\Helper\Data                   $helper
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\Eav\Model\Entity\Context              $context
     * @param array                                          $data
     */
    public function __construct(
        \MageModule\Core\Helper\Data $helper,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Eav\Model\Entity\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper        = $helper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Magento\Framework\DataObject|\MageModule\Core\Model\AbstractExtensibleModel $object
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\DataObject $object)
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

        $useDefaults = $object->getData('use_defaults');
        if (is_array($useDefaults)) {
            $this->helper->boolify($useDefaults);
            $this->helper->removeFalse($useDefaults);
            if (!empty($useDefaults)) {
                $useDefaults = array_fill_keys(array_keys($useDefaults), false);
            }

            $object->addData($useDefaults);
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel|\MageModule\Core\Model\AbstractExtensibleModel $object
     *
     * @return $this|\MageModule\Core\Model\ResourceModel\AbstractEntity|\Magento\Eav\Model\Entity\AbstractEntity
     * @throws \Exception
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entityManager->save($object);

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel|\MageModule\Core\Model\AbstractExtensibleModel $object
     * @param integer                                                                               $entityId
     * @param array|null                                                                            $attributes
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
