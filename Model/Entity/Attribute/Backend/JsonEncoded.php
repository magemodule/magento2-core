<?php

namespace MageModule\Core\Model\Entity\Attribute\Backend;

/**
 * Class JsonEncoded
 *
 * @package MageModule\Core\Model\Entity\Attribute\Backend
 */
class JsonEncoded extends \Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded
{
    /**
     * Fixes issue in which a value of false does not trigger value deletion
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode) && $object->getData($attrCode) === false) {
            return $this;
        }

        return parent::beforeSave($object);
    }
}
//todo when error in form, use defaults checkbox gets rechecked but new value still present
