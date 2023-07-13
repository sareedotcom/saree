<?php

namespace MagicToolbox\MagicScroll\Block\Product\Renderer\Listing;

/**
 * Swatch renderer block in Category page
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{
    /**
     * Action name for ajax request
     */
    const MAGICTOOLBOX_MEDIA_CALLBACK_ACTION = 'magicscroll/ajax/media';

    /**
     * @var \MagicToolbox\MagicScroll\Helper\ConfigurableData
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager = null;

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->helper = $objectManager->get(\MagicToolbox\MagicScroll\Helper\ConfigurableData::class);
        $this->moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);

        //NOTE: for versions 2.3.x (x >=4)
        $configurableViewModel = $this->getConfigurableViewModel();
        if (!$configurableViewModel) {
            if (class_exists('\Magento\Swatches\ViewModel\Product\Renderer\Configurable')) {
                $configurableViewModel = $objectManager->get(\Magento\Swatches\ViewModel\Product\Renderer\Configurable::class);
                $this->setData('configurable_view_model', $configurableViewModel);
            }
        }
    }

    /**
     * Helper getter
     *
     * @return string
     */
    public function getMagicToolboxHelper()
    {
        return $this->helper->getMagicToolboxHelper();
    }

    /**
     * Returns additional values for js config
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        $config = parent::_getAdditionalConfig();
        $data = $this->helper->getRegistry()->registry('magictoolbox_category');
        if ($data && $data['current-renderer'] == 'configurable.magicscroll') {
            $magentoVersion = $this->helper->getMagicToolboxHelper()->getMagentoVersion();
            $config['magictoolbox'] = [
                'useOriginalGallery' => $this->helper->useOriginalGallery(),
                'galleryData' => $this->helper->getGalleryData(),
                'standaloneMode' => false,
                'overrideUseAjaxOption' => version_compare($magentoVersion, '2.4.1', '=')
            ];
        }
        return $config;
    }

    /**
     * @return string
     */
    public function getMediaCallback()
    {
        $data = $this->helper->getRegistry()->registry('magictoolbox_category');
        $route = self::MEDIA_CALLBACK_ACTION;
        if ($data && $data['current-renderer'] == 'configurable.magicscroll') {
            $route = self::MAGICTOOLBOX_MEDIA_CALLBACK_ACTION;
        }
        return $this->getUrl($route, ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->moduleManager->isEnabled('Magento_Swatches')) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        if (!$this->moduleManager->isEnabled('Magento_Swatches')) {
            return '';
        }
        return parent::_afterToHtml($html);
    }
}
