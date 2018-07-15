<?php

namespace MageModule\Core\Ui\Component\Listing\Attribute;

interface RepositoryInterface
{
    /**
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList();
}
