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

namespace MageModule\Core\Observer\Store\Add;

use MageModule\Core\Api\AttributeRepositoryInterface;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaInterfaceFactory;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;

class InsertWebsiteScopeValues implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var SearchCriteriaInterfaceFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var \MageModule\Core\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * Add constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection             $resource
     * @param \Magento\Framework\Api\SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param \MageModule\Core\Api\AttributeRepositoryInterface     $attributeRepository
     */
    public function __construct(
        ResourceConnection $resource,
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->resource              = $resource;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->attributeRepository   = $attributeRepository;
    }

    /**
     * After new store view is added, this observer inserts attribute value row
     * for any attributes that have website scope
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        //TODO: END OF DEV: make sure that store locator group model website scope attributes get properly inserted
        //TODO: END OF DEV: make sure that store locator store model website scope attributes get properly inserted
        /** @var StoreInterface $store */
        $store    = $observer->getEvent()->getStore();
        $website  = $store->getWebsite();
        $storeIds = $website->getStoreIds();
        $storeId  = $store->getStoreId();

        if ($storeId) {
            $connection = $this->resource->getConnection();

            /** @var SearchCriteriaInterface $searchCriteria */
            $searchCriteria = $this->searchCriteriaFactory->create();
            $attributes     = $this->attributeRepository->getList($searchCriteria);

            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($attributes->getItems() as $attribute) {
                if ($attribute instanceof ScopedAttributeInterface && $attribute->isScopeWebsite()) {
                    $entityIdField = 'entity_id';
                    if ($attribute->getEntityIdField()) {
                        $entityIdField = $attribute->getEntityIdField();
                    }

                    $table = $attribute->getBackendTable();

                    //TODO find a way to move this to the AbstractEntity class
                    $select = $connection->select()->from($table);
                    $select->where(ScopedAttributeInterface::ATTRIBUTE_ID . ' =?', $attribute->getAttributeId());
                    $select->where(ScopedAttributeInterface::STORE_ID . ' IN(?)', $storeIds);
                    $select->group($entityIdField);
                    $result = $connection->fetchAll($select);

                    if (is_array($result)) {
                        foreach ($result as $row) {
                            unset($row[ScopedAttributeInterface::VALUE_ID]);
                            $row[ScopedAttributeInterface::STORE_ID] = $storeId;
                            $connection->insertOnDuplicate($table, $row);
                        }
                    }
                }
            }
        }
    }
}
