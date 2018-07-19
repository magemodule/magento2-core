<?php

namespace MageModule\Core\Model\ResourceModel;

abstract class AbstractEntity extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * AbstractEntity constructor.
     *
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\Eav\Model\Entity\Context              $context
     * @param array                                          $data
     */
    public function __construct(
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Eav\Model\Entity\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\DataObject $object)
    {
        if (!$object->getId() && !$object->getData('attribute_set_id')) {
            $object->setData('attribute_set_id', $this->getEntityType()->getDefaultAttributeSetId());
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param integer $entityId
     * @param array|null $attributes
     *
     * @return $this
     */
    public function load($object, $entityId, $attributes = [])
    {
        $select = $this->_getLoadRowSelect($object, $entityId);
        $row = $this->getConnection()->fetchRow($select);

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
