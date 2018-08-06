<?php

namespace MageModule\Core\Model\Entity;

class ScopedAttribute extends \MageModule\Core\Model\Entity\Attribute implements
    \MageModule\Core\Api\Data\ScopedAttributeInterface,
    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface
{
    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        switch ($scope) {
            case self::SCOPE_GLOBAL_TEXT:
                $this->setData(self::IS_GLOBAL, self::SCOPE_GLOBAL);
                break;
            case self::SCOPE_WEBSITE_TEXT:
                $this->setData(self::IS_GLOBAL, self::SCOPE_WEBSITE);
                break;
            case self::SCOPE_STORE_TEXT:
                $this->setData(self::IS_GLOBAL, self::SCOPE_STORE);
                break;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getScope()
    {
        $scope = $this->getData(self::IS_GLOBAL);
        switch ($scope) {
            case self::SCOPE_GLOBAL:
                $result = self::SCOPE_GLOBAL_TEXT;
                break;
            case self::SCOPE_WEBSITE:
                $result = self::SCOPE_WEBSITE_TEXT;
                break;
            case self::SCOPE_STORE:
                $result = self::SCOPE_STORE_TEXT;
                break;
            default:
                $result = null;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getScope() === self::SCOPE_GLOBAL_TEXT;
    }

    /**
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getScope() === self::SCOPE_WEBSITE_TEXT;
    }

    /**
     * @return bool
     */
    public function isScopeStore()
    {
        return $this->getScope() === self::SCOPE_STORE_TEXT;
    }
}
