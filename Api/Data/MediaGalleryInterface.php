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

namespace MageModule\Core\Api\Data;

interface MediaGalleryInterface
{
    const VALUE_ID     = 'value_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const ENTITY_ID    = 'entity_id';
    const VALUE        = 'value';

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setValueId($id);

    /**
     * @return int
     */
    public function getValueId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setAttributeId($id);

    /**
     * @return int
     */
    public function getAttributeId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setEntityId($id);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param string|null|bool $value
     *
     * @return $this
     */
    public function setValue($value);

    /**
     * @return string|null
     */
    public function getValue();
}
