<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;

class Boolean extends \MageModule\Core\Model\Eav\Entity\Attribute\Backend\AbstractBackend
{
    const DELETE_FLAG = 2;

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return \MageModule\Core\Model\Eav\Entity\Attribute\Backend\AbstractBackend
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value    = $object->getData($attrCode);

        if ($value === false || $object->getData('use_defaults/' . $attrCode)) {
            $object->setData($attrCode, self::DELETE_FLAG);
        }

        return parent::beforeSave($object);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return \MageModule\Core\Model\Eav\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave($object)
    {
        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getAttributeCode();
        $value     = $object->getData($attrCode);

        if ($value === false ||
            $value == self::DELETE_FLAG ||
            $object->getData('use_defaults/' . $attrCode)
        ) {
            if ($this->deleteAttributeValue($object)) {
                $object->unsetData($attrCode);
            }
        }

        return parent::afterSave($object);
    }
}
