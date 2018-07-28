<?php

namespace MageModule\Core\Api;

interface AttributeRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $attributeCode
     *
     * @return \MageModule\Core\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($attributeCode);

    /**
     * @param \MageModule\Core\Api\Data\AttributeInterface $attribute
     *
     * @return \MageModule\Core\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\MageModule\Core\Api\Data\AttributeInterface $attribute);

    /**
     * @param \MageModule\Core\Api\Data\AttributeInterface $attribute
     *
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\MageModule\Core\Api\Data\AttributeInterface $attribute);

    /**
     * @param string $attributeCode
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($attributeCode);
}
