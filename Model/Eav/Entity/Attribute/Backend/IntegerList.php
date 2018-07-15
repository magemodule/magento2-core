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
 *  @author        MageModule admin@magemodule.com
 *  @copyright    2018 MageModule, LLC
 *  @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;

class IntegerList extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value         = $object->getData($attributeCode);
        if (is_array($value)) {
            asort($value);
            $value = str_replace(' ', '', implode(',', array_unique($value)));
            $object->setData($attributeCode, $value);
        } elseif (is_string($value)) {
            $value = explode(',', $value);
            $object->setData($attributeCode, $value);

            return $this->beforeSave($object);
        }

        $this->validate($object);

        return parent::beforeSave($object);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value         = $object->getData($attributeCode);
        $values = explode(',', $value);

        $invalidValues = [];
        foreach ($values as $value) {
            if (!ctype_digit($value)) {
                $invalidValues[] = $value;
            }
        }

        if (!empty($invalidValues)) {
            if (count($invalidValues) === 1) {
                throw new LocalizedException(
                    __(
                        '%1 is an invalid number list value. Only integers are allowed.',
                        implode(', ', $invalidValues)
                    )
                );
            } else {
                throw new LocalizedException(
                    __(
                        '%1 are invalid number list values. Only integers are allowed.',
                        implode(', ', $invalidValues)
                    )
                );
            }
        }

        return parent::validate($object);
    }
}
