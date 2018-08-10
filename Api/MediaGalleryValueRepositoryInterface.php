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

namespace MageModule\Core\Api;

interface MediaGalleryValueRepositoryInterface
{
    //TODO return value declaration should be updated
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $id
     *
     * @return \MageModule\Core\Api\Data\MediaGalleryValueInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * @param \MageModule\Core\Api\Data\MediaGalleryValueInterface $object
     *
     * @return \MageModule\Core\Api\Data\MediaGalleryValueInterface
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\MageModule\Core\Api\Data\MediaGalleryValueInterface $object);

    /**
     * @param \MageModule\Core\Api\Data\MediaGalleryValueInterface $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\MageModule\Core\Api\Data\MediaGalleryValueInterface $object);

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);
}
