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

namespace MageModule\Core\Ui\Component\Listing\Attribute;

class Repository implements \MageModule\Core\Ui\Component\Listing\Attribute\RepositoryInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var string
     */
    private $entityTypeCode;

    /**
     * Repository constructor.
     *
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder  $searchCriteriaBuilder
     * @param string                                        $entityTypeCode
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        $entityTypeCode
    ) {
        $this->attributeRepository   = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->entityTypeCode        = $entityTypeCode;
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList()
    {
        //TODO can i replace with non-generic attribute repository, such as one of my virtual types?
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(\MageModule\Core\Api\Data\AttributeInterface::IS_USED_IN_GRID, 1)
            ->create();

        return $this->attributeRepository->getList($this->entityTypeCode, $searchCriteria);
    }
}
