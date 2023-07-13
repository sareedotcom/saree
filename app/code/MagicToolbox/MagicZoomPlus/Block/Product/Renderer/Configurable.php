<?php

namespace MagicToolbox\MagicZoomPlus\Block\Product\Renderer;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * Action name for ajax request
     */
    const MAGICTOOLBOX_MEDIA_CALLBACK_ACTION = 'magiczoomplus/ajax/media';

    /**
     * @var \MagicToolbox\MagicZoomPlus\Helper\ConfigurableData
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

        $this->helper = $objectManager->get(\MagicToolbox\MagicZoomPlus\Helper\ConfigurableData::class);
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
     * Returns additional values for js config
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        $config = parent::_getAdditionalConfig();
        $data = $this->helper->getRegistry()->registry('magictoolbox');
        if ($data && $data['current'] != 'product.info.media.image') {
            $standaloneMode = isset($data['standalone-mode']) && $data['standalone-mode'];
            $magentoVersion = $this->helper->getMagicToolboxHelper()->getMagentoVersion();
            $config['magictoolbox'] = [
                'useOriginalGallery' => $this->helper->useOriginalGallery(),
                'galleryData' => $this->helper->getGalleryData(),
                'standaloneMode' => $standaloneMode,
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
        $data = $this->helper->getRegistry()->registry('magictoolbox');
        $url = self::MEDIA_CALLBACK_ACTION;
        if ($data && $data['current'] != 'product.info.media.image') {
            $url = self::MAGICTOOLBOX_MEDIA_CALLBACK_ACTION;
        }
        return $this->getUrl($url, ['_secure' => $this->getRequest()->isSecure()]);
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
