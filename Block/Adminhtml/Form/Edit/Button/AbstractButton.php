<?php

namespace MageModule\Core\Block\Adminhtml\Form\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Model\AbstractModel;

abstract class AbstractButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    protected $registryKey;

    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var string|null
     */
    protected $cssClass;

    /**
     * @var int|null
     */
    protected $sortOrder;

    /**
     * @var string|null
     */
    protected $route;

    /**
     * @var null|string
     */
    protected $aclResource;

    /**
     * AbstractButton constructor.
     *
     * @param Registry               $registry
     * @param AuthorizationInterface $authorization
     * @param UrlInterface           $urlBuilder
     * @param string|null            $registryKey
     * @param string|null            $label
     * @param string|null            $cssClass
     * @param int|null               $sortOrder
     * @param string|null            $route
     * @param string|null            $aclResource
     */
    public function __construct(
        Registry $registry,
        AuthorizationInterface $authorization,
        UrlInterface $urlBuilder,
        $registryKey = null,
        $label = null,
        $cssClass = null,
        $sortOrder = null,
        $route = null,
        $aclResource = null
    ) {
        $this->registry      = $registry;
        $this->authorization = $authorization;
        $this->urlBuilder    = $urlBuilder;
        $this->registryKey   = $registryKey;
        $this->label         = $label;
        $this->cssClass      = $cssClass;
        $this->sortOrder     = $sortOrder;
        $this->route         = $route;
        $this->aclResource   = $aclResource;
    }

    /**
     * @return AbstractModel
     */
    public function getDataObject()
    {
        return $this->registry->registry($this->registryKey);
    }

    /**
     * @return int|null
     */
    public function getDataObjectId()
    {
        if ($this->getDataObject() instanceof AbstractModel) {
            return $this->getDataObject()->getId();
        }

        return null;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * @param string $route
     * @param array  $params
     *
     * @return string
     */
    protected function getUrl($route, array $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @param string $resource
     *
     * @return bool
     */
    protected function isAuthorized($resource)
    {
        return $this->authorization->isAllowed($resource);
    }
}
