<?php

namespace MageModule\Core\Model\Config\Backend\Url\Rewrite;

class Suffix extends \Magento\Framework\App\Config\Value
{
    /**
     * @var string
     */
    private $entityTypeCode;

    /**
     * @var string
     */
    private $urlKeyAttributeCode;

    /**
     * @var \MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    private $collection;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $eavAttributeRepository;

    /**
     * @var \Magento\UrlRewrite\Helper\UrlRewrite
     */
    private $urlRewriteHelper;

    /**
     * @var \Magento\Framework\App\Config
     */
    private $appConfig;

    /**
     * Suffix constructor.
     *
     * @param string                                                          $entityTypeCode
     * @param string                                                          $urlKeyAttributeCode
     * @param \MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator $urlRewriteGenerator
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection         $collection
     * @param \Magento\UrlRewrite\Helper\UrlRewrite                           $urlRewriteHelper
     * @param \Magento\Eav\Api\AttributeRepositoryInterface                   $eavAttributeRepository
     * @param \Magento\Framework\Model\Context                                $context
     * @param \Magento\Framework\Registry                                     $registry
     * @param \Magento\Framework\App\Config                                   $appConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $config
     * @param \Magento\Framework\App\Cache\TypeListInterface                  $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null    $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null              $resourceCollection
     * @param array                                                           $data
     */
    public function __construct(
        $entityTypeCode,
        $urlKeyAttributeCode,
        \MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator $urlRewriteGenerator,
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection,
        \Magento\UrlRewrite\Helper\UrlRewrite $urlRewriteHelper,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config $appConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );

        $this->entityTypeCode         = $entityTypeCode;
        $this->urlKeyAttributeCode    = $urlKeyAttributeCode;
        $this->urlRewriteGenerator    = $urlRewriteGenerator;
        $this->urlRewriteHelper       = $urlRewriteHelper;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->collection             = $collection;
        $this->appConfig              = $appConfig;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $this->urlRewriteHelper->validateSuffix($this->getValue());

        return $this;
    }

    /**
     * @return \Magento\Framework\App\Config\Value
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->updateUrlRewrites();
        }

        return parent::afterSave();
    }

    /**
     * @return \Magento\Framework\App\Config\Value
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function afterDeleteCommit()
    {
        $this->updateUrlRewrites();

        return parent::afterDeleteCommit();
    }

    /**
     * Updates url keys for all objects in the collection
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    private function updateUrlRewrites()
    {
        $attribute = $this->eavAttributeRepository->get(
            $this->entityTypeCode,
            $this->urlKeyAttributeCode
        );

        $this->appConfig->clean();
        $this->urlRewriteGenerator->setAttribute($attribute);
        $this->collection->addAttributeToSelect($attribute->getAttributeCode());

        /** @var \Magento\Framework\Model\AbstractModel $item */
        foreach ($this->collection as $item) {
            $this->urlRewriteGenerator->generate($item, true);
        }

        return $this;
    }
}
