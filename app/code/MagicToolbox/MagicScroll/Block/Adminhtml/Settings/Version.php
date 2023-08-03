<?php

namespace MagicToolbox\MagicScroll\Block\Adminhtml\Settings;

/**
 * Module version block
 *
 */
class Version extends \Magento\Framework\View\Element\Template
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Data helper
     *
     * @var \MagicToolbox\MagicScroll\Helper\Data
     */
    protected $dataHelper = null;

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->dataHelper = $this->objectManager->get(\MagicToolbox\MagicScroll\Helper\Data::class);
    }

    /**
     * Get module version
     *
     * @return string
     */
    public function getModuleVersion()
    {
        $version = $this->dataHelper->getModuleVersion('MagicToolbox_MagicScroll');
        $version = $version ? $version : '';

        return $version;
    }
}
