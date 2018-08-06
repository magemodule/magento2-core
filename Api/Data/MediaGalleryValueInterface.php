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

namespace MageModule\Core\Api\Data;

interface MediaGalleryValueInterface
{
    const VALUE_ID  = 'value_id';
    const STORE_ID  = 'store_id';
    const ENTITY_ID = 'entity_id';
    const LABEL     = 'label';
    const POSITION  = 'position';
    const DISABLED  = 'disabled';

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
    public function setStoreId($id);

    /**
     * @return int
     */
    public function getStoreId();

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
     * @param string|null|bool $label
     *
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int|bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled);

    /**
     * @return bool
     */
    public function getDisabled();
}
