<?php

namespace MageModule\Core\Helper;

class Layout extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    private $view;

    /**
     * Layout constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ViewInterface  $view
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ViewInterface $view
    ) {
        parent::__construct($context);
        $this->view = $view;
    }

    /**
     * @return \Magento\Framework\App\ViewInterface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->getView()->getLayout();
    }

    /**
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    public function getUpdate()
    {
        return $this->getLayout()->getUpdate();
    }

    /**
     * @return array
     */
    public function getHandles()
    {
        $handles = $this->getUpdate()->getHandles();
        $handles[] = $this->getView()->getDefaultLayoutHandle();

        return $handles;
    }

    /**
     * @param string $handle
     *
     * @return bool
     */
    public function isCurrentHandle($handle)
    {
        return in_array($handle, $this->getHandles());
    }

    /**
     * @param string $handle
     *
     * @return $this
     */
    public function addHandle($handle)
    {
        $this->getUpdate()->addHandle($handle);

        return $this;
    }
}
