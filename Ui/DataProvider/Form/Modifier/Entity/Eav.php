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

namespace MageModule\Core\Ui\DataProvider\Form\Modifier\Entity;

/**
 * A re-usable class that creates forms from attribute groups
 *
 * Class Eav
 *
 * @package MageModule\Core\Ui\DataProvider\Form\Modifier\Entity
 */
class Eav implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    const CONTAINER_PREFIX      = 'container_';
    const SORT_ORDER_MULTIPLIER = 10;

    /**
     * @var \MageModule\Core\Ui\Component\Form\Rule\Eav\Validation\RulesBuilder
     */
    private $rulesBuilder;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Eav\Api\AttributeGroupRepositoryInterface
     */
    private $attributeGroupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var string
     */
    private $registryKey;

    /**
     * @var \Magento\Ui\DataProvider\Mapper\FormElement
     */
    private $formElementMapper;

    /**
     * @var string
     */
    private $entityTypeCode;

    /**
     * @var string
     */
    private $dataScopeKey;

    /**
     * @var array|string[]
     */
    private $nonCollapsibleFieldsets;

    /**
     * @var \Magento\Eav\Api\Data\AttributeGroupInterface[]
     */
    private $attributeGroups;

    /**
     * @var \MageModule\Core\Api\Data\ScopedAttributeInterface[]
     */
    private $attributes;

    /**
     * Eav constructor.
     *
     * @param \MageModule\Core\Ui\Component\Form\Rule\Eav\Validation\RulesBuilder $rulesBuilder
     * @param \Magento\Eav\Api\AttributeRepositoryInterface                       $attributeRepository
     * @param \Magento\Eav\Api\AttributeGroupRepositoryInterface                  $attributeGroupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                        $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder                             $sortOrderBuilder
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Ui\DataProvider\Mapper\FormElement                         $formElementMapper
     * @param string                                                              $entityTypeCode
     * @param string                                                              $registryKey
     * @param string                                                              $dataScopeKey
     * @param string[]                                                            $nonCollapsibleFieldsets
     */
    public function __construct(
        \MageModule\Core\Ui\Component\Form\Rule\Eav\Validation\RulesBuilder $rulesBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Api\AttributeGroupRepositoryInterface $attributeGroupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Registry $registry,
        \Magento\Ui\DataProvider\Mapper\FormElement $formElementMapper,
        $entityTypeCode,
        $registryKey,
        $dataScopeKey,
        array $nonCollapsibleFieldsets = []
    ) {
        $this->rulesBuilder             = $rulesBuilder;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeRepository      = $attributeRepository;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->sortOrderBuilder         = $sortOrderBuilder;
        $this->registry                 = $registry;
        $this->formElementMapper        = $formElementMapper;
        $this->entityTypeCode           = $entityTypeCode;
        $this->registryKey              = $registryKey;
        $this->dataScopeKey             = $dataScopeKey;
        $this->nonCollapsibleFieldsets  = $nonCollapsibleFieldsets;
    }

    /**
     * @param array $meta
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modifyMeta(array $meta)
    {
        $formElementMappings = $this->formElementMapper->getMappings();
        $attributesByGroup   = $this->getAttributes();
        $sortOrder           = 0;
        foreach ($this->getAttributeGroups() as $groupCode => $group) {
            $attributes = !empty($attributesByGroup[$groupCode]) ? $attributesByGroup[$groupCode] : [];
            if ($attributes) {
                $isStaticFieldset = in_array($groupCode, $this->nonCollapsibleFieldsets) ||
                    array_key_exists($groupCode, $this->nonCollapsibleFieldsets);

                $fieldset                  = &$meta[$groupCode]['arguments']['data']['config'];
                $fieldset['componentType'] = \Magento\Ui\Component\Form\Fieldset::NAME;
                $fieldset['label']         = $isStaticFieldset ? null : __($group->getAttributeGroupName());
                $fieldset['collapsible']   = $isStaticFieldset ? false : true;
                $fieldset['dataScope']     = $this->dataScopeKey;
                $fieldset['sortOrder']     = $sortOrder * self::SORT_ORDER_MULTIPLIER;

                /** @var \MageModule\Core\Api\Data\ScopedAttributeInterface $attribute */
                foreach ($attributes as $attribute) {
                    $container = &$meta[$groupCode]['children']
                    [self::CONTAINER_PREFIX . $attribute->getAttributeCode()];

                    $container['arguments']['data']['config'] = [
                        'formElement'   => \Magento\Ui\Component\Container::NAME,
                        'componentType' => \Magento\Ui\Component\Container::NAME
                    ];

                    $formElement = isset($formElementMappings[$attribute->getFrontendInput()]) ?
                        $formElementMappings[$attribute->getFrontendInput()] :
                        $attribute->getFrontendInput();

                    $field = [
                        //'scopeLabel'    => $storeViewLabel,
                        'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                        'formElement'   => $formElement,
                        'label'         => __($attribute->getDefaultFrontendLabel()),
                        'dataScope'     => $attribute->getAttributeCode()
                    ];

                    if ($formElement === \Magento\Ui\Component\Form\Element\Checkbox::NAME) {
                        $field['prefer']   = 'toggle';
                        $field['valueMap'] = [
                            'true'  => '1',
                            'false' => '0',
                        ];
                    }

                    if ($attribute->getNote()) {
                        $field['notice'] = __($attribute->getNote());
                    }

                    if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute) {
                        if ($attribute->usesSource()) {
                            $field['options'] = $attribute->getSource()->toOptionArray();
                        }
                    }

                    if ($attribute instanceof \MageModule\Core\Api\Data\AttributeInterface) {
                        if ($attribute->getIsWysiwygEnabled()) {
                            $field['formElement']       = \Magento\Ui\Component\Form\Element\Wysiwyg::NAME;
                            $field['wysiwyg']           = true;
                            $field['wysiwygConfigData'] = [
                                'add_variables'   => true,
                                'add_widgets'     => true,
                                'add_directives'  => true,
                                'use_container'   => false,
                                'container_class' => 'hor-scroll',
                            ];
                        }
                    }

                    $field['validation'] = $this->rulesBuilder->build($attribute, $field);

                    $container['children'][$attribute->getAttributeCode()]['arguments']['data']['config'] = $field;
                }
            }

            $sortOrder++;
        }

        return $meta;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @return \MageModule\Core\Model\AbstractExtensibleModel
     */
    private function getDataObject()
    {
        return $this->registry->registry($this->registryKey);
    }

    /**
     * Return current attribute set id
     *
     * @return int|null
     */
    private function getAttributeSetId()
    {
        return $this->getDataObject()->getAttributeSetId();
    }

    /**
     * @return \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private function prepareAttributeGroupSearchCriteria()
    {
        return $this->searchCriteriaBuilder->addFilter(
            \Magento\Eav\Api\Data\AttributeGroupInterface::ATTRIBUTE_SET_ID,
            $this->getAttributeSetId()
        );
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributeGroups()
    {
        if (!$this->attributeGroups) {
            $searchCriteria             = $this->prepareAttributeGroupSearchCriteria()->create();
            $attributeGroupSearchResult = $this->attributeGroupRepository->getList($searchCriteria);
            foreach ($attributeGroupSearchResult->getItems() as $key => $group) {
                if ($group instanceof \Magento\Framework\DataObject) {
                    $key = $group->getData('attribute_group_code');
                }

                $this->attributeGroups[$key] = $group;
            }
        }

        return $this->attributeGroups;
    }

    /**
     * @return \MageModule\Core\Api\Data\ScopedAttributeInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributes()
    {
        if (!$this->attributes) {
            foreach ($this->getAttributeGroups() as $key => $group) {
                if ($group instanceof \Magento\Framework\DataObject) {
                    $key = $group->getData('attribute_group_code');
                }

                $this->attributes[$key] = $this->loadAttributes($group);
            }
        }

        return $this->attributes;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface $group
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    private function loadAttributes(\Magento\Eav\Api\Data\AttributeGroupInterface $group)
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setAscendingDirection()
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($group::GROUP_ID, $group->getAttributeGroupId())
            ->addFilter(\MageModule\Core\Api\Data\AttributeInterface::IS_VISIBLE, 1)
            ->addSortOrder($sortOrder)
            ->create();

        $attributes = $this->attributeRepository->getList($this->entityTypeCode, $searchCriteria)->getItems();

        return $attributes;
    }
}
