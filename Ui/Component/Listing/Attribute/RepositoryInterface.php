<?php

namespace MageModule\Core\Ui\Component\Listing\Attribute;

interface RepositoryInterface
{
    /**
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList();
}
