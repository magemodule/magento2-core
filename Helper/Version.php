<?php

namespace MageModule\Core\Helper;

class Version extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param string|float $value
     *
     * @return bool
     */
    public function isValidVersionNumber($value)
    {
        return strlen($value) &&
               version_compare($value, '0.0.001', '>=') &&
               version_compare($value, '1000.0.0', '<=');
    }

    /**
     * @param string|float $value
     *
     * @return int
     */
    public function getMajorVersion($value)
    {
        $parts = explode('.', $value);

        return isset($parts[0]) ? (int)$parts[0] : 0;
    }

    /**
     * @param string|float $value
     *
     * @return int
     */
    public function getMinorVersion($value)
    {
        $parts = explode('.', $value);

        return isset($parts[1]) ? (int)$parts[1] : 0;
    }

    /**
     * @param string|float $value
     *
     * @return int
     */
    public function getPatchVersion($value)
    {
        $parts = explode('.', $value);

        return isset($parts[2]) ? (int)$parts[2] : 0;
    }
}
