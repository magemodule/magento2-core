<?php

namespace MageModule\Core\Api\Data;

interface ScopedAttributeInterface extends \MageModule\Core\Api\Data\AttributeInterface
{
    const IS_GLOBAL          = 'is_global';
    const SCOPE_STORE_TEXT   = 'store';
    const SCOPE_GLOBAL_TEXT  = 'global';
    const SCOPE_WEBSITE_TEXT = 'website';

    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope);

    /**
     * @return string|null
     */
    public function getScope();

    /**
     * @return bool
     */
    public function isScopeGlobal();

    /**
     * @return bool
     */
    public function isScopeWebsite();

    /**
     * @return bool
     */
    public function isScopeStore();
}
