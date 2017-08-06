<?php

namespace MageModule\Core\Block\Adminhtml\Backend\Widget;

class Tab extends \Magento\Backend\Block\Widget\Tab
{
    /**
     * @var string|array|null
     */
    private $resources;

    /**
     * Any string|array|null resource strings passed in will be check for ACL perms before allowing tab to display
     *
     * @param $resources
     *
     * @return $this
     */
    public function setAclResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $canShow = parent::canShowTab();
        if (is_string($this->resources) && $canShow) {
            $canShow = $this->_authorization->isAllowed($this->resources);
        }

        if (is_array($this->resources) && $canShow) {
            foreach ($this->resources as $resource) {
                if (!$this->_authorization->isAllowed($resource)) {
                    return false;
                }
            }
        }

        return $canShow;
    }
}
