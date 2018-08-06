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

namespace MageModule\Core\Block\Adminhtml\Media;

use MageModule\Core\Api\Data\ScopedAttributeInterface;
use MageModule\Core\Model\AbstractExtensibleModel;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Element\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Gallery extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $registryKey;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $formName;

    /**
     * Gallery name
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $attributeCode;

    /**
     * @var string|null
     */
    protected $fieldNameSuffix;

    /**
     * @var string
     */
    protected $htmlId;

    /**
     * Html id for data scope
     *
     * @var string
     */
    protected $image = 'image';

    /**
     * Gallery constructor.
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param Registry              $registry
     * @param Form                  $form
     * @param string                $formName
     * @param string                $name
     * @param string                $registryKey
     * @param string                $attributeCode
     * @param string|null           $fieldNameSuffix
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Form $form,
        $formName,
        $registryKey,
        $attributeCode,
        $name,
        $fieldNameSuffix = null,
        $data = []
    ) {
        parent::__construct($context, $data);

        $this->storeManager    = $storeManager;
        $this->registry        = $registry;
        $this->form            = $form;
        $this->formName        = $formName;
        $this->name            = $name;
        $this->registryKey     = $registryKey;
        $this->htmlId          = $attributeCode;
        $this->attributeCode   = $attributeCode;
        $this->fieldNameSuffix = $fieldNameSuffix;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();

        return $html;
    }

    /**
     * @return array|null
     */
    public function getImages()
    {
        return $this->registry->registry($this->registryKey)->getData($this->attributeCode) ?: null;
    }

    /**
     * Prepares content block
     *
     * @return string
     */
    public function getContentHtml()
    {
        /** @var $content Gallery\Content */
        $content = $this->getChildBlock('content');
        $content->setId($this->getHtmlId() . '_content')->setElement($this);
        $content->setFormName($this->formName);
        $galleryJs = $content->getJsObjectName();
        $content->getUploader()->getConfig()->setMediaGallery($galleryJs);
        return $content->toHtml();
    }

    /**
     * @return string
     */
    protected function getHtmlId()
    {
        return $this->htmlId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFieldNameSuffix()
    {
        return $this->fieldNameSuffix;
    }

    /**
     * @return string
     */
    public function getDataScopeHtmlId()
    {
        return $this->image;
    }

    /**
     * @param AttributeInterface|ScopedAttributeInterface $attribute
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canDisplayUseDefault($attribute)
    {
        if ($attribute instanceof ScopedAttributeInterface) {
            if (!$attribute->isScopeGlobal() && $this->getDataObject()->getStoreId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AttributeInterface|ScopedAttributeInterface $attribute
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function usedDefault($attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        //TODO check these functions
        $defaultValue = $this->getDataObject()->getAttributeDefaultValue($attributeCode);

        if (!$this->getDataObject()->getExistsStoreValueFlag($attributeCode)) {
            return true;
        } elseif ($this->getValue() == $defaultValue &&
                  $this->getDataObject()->getStoreId() != $this->getDefaultStoreId()
        ) {
            return false;
        }
        if ($defaultValue === false && !$attribute->getIsRequired() && $this->getValue()) {
            return false;
        }
        return $defaultValue === false;
    }

    /**
     * @param AttributeInterface|ScopedAttributeInterface $attribute
     *
     * @return string
     */
    public function getScopeLabel($attribute)
    {
        $html = '';
        if ($this->storeManager->isSingleStoreMode() || !$attribute instanceof ScopedAttributeInterface) {
            return $html;
        }

        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }
        return $html;
    }

    /**
     * Retrieve data object related with form
     *
     * @return AbstractExtensibleModel|AbstractModel
     */
    public function getDataObject()
    {
        return $this->registry->registry($this->registryKey);
    }

    /**
     * @param AttributeInterface|ScopedAttributeInterface $attribute
     *
     * @return string
     */
    public function getAttributeFieldName($attribute)
    {
        $name = $attribute->getAttributeCode();
        if ($suffix = $this->getFieldNameSuffix()) {
            $name = $this->form->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getElementHtml();
    }

    /**
     * Default sore ID getter
     *
     * @return integer
     */
    protected function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
