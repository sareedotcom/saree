<?php

namespace MagicToolbox\MagicZoomPlus\Observer;

/**
 * MagicZoomPlus Observer
 *
 */
class FixLayoutBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicZoomPlus\Helper\Data
     */
    protected $magicToolboxHelper = null;

    /**
     * Constructor
     *
     * @param \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
     */
    public function __construct(
        \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
    }

    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     *
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        /** @var \Magento\Framework\View\Layout\Element $layoutXMLElement */
        $layoutXMLElement = $layout->getNode(null);
        //$xmlCode = $layoutXMLElement->asNiceXml();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        //NOTE: for cases when Magento_Swatches module is not installed or disabled
        $moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);
        if (!$moduleManager->isEnabled('Magento_Swatches')) {
            $pathes = [
                //NOTE: product page
                '/layout/body/referenceBlock[@name="product.info.options.wrapper"]/block[@class="MagicToolbox\MagicZoomPlus\Block\Product\Renderer\Configurable"]',
                //NOTE: category page
                '/layout/body/referenceBlock[@name="category.product.type.details.renderers"]/block[@name="configurable.magiczoomplus"]',
            ];
            foreach ($pathes as $searchPath) {
                $nodes = $layoutXMLElement->xpath($searchPath);
                if ($nodes) {
                    foreach ($nodes as $node) {
                        $node->unsetSelf();
                    }
                }
            }
        }

        $pathes = [
            //NOTE: product page media block
            '/layout/body/referenceContainer[@name="product.info.media"]' => 'block[@name="product.info.media.magiczoomplus"]',
            //NOTE: product page layout instruction
            '/layout/body/move[@element="product.info.media.video"]' => 'self::*[@destination="product.info.media"]',
            //NOTE: product page configurable options and swatches blocks
            '/layout/body/referenceBlock[@name="product.info.options.wrapper"]' => 'block[@class="MagicToolbox\MagicZoomPlus\Block\Product\View\Type\Configurable"]',
            //NOTE: category page configurable (swatches) renderer block
            '/layout/body/referenceBlock[@name="category.product.type.details.renderers"]' => 'block[@name="configurable.magiczoomplus"]',
            //NOTE: container for headers
            '/layout/body/referenceBlock[@name="head.additional"]' => 'container[@name="head.additional.magictoolbox"]',
        ];

        $magiczoomplus = $this->magicToolboxHelper->getToolObj();
        $isDisabled = $magiczoomplus->params->checkValue('enable-effect', 'No', 'product');

        foreach ($pathes as $searchPath => $checkPath) {
            $nodes = $layoutXMLElement->xpath($searchPath);
            if ($nodes) {
                foreach ($nodes as $node) {
                    if ($node->xpath($checkPath)) {
                        //NOTE: to remove product page options blocks if effect is disabled
                        if ($isDisabled && 'block[@class="MagicToolbox\MagicZoomPlus\Block\Product\View\Type\Configurable"]' == $checkPath) {
                            $node->unsetSelf();
                            continue;
                        }
                        $body = $layoutXMLElement->addChild('body');
                        $body->appendChild($node);
                        $node->unsetSelf();
                    }
                }
            }
        }

        //NOTE: fix for MGS theme
        if (!$isDisabled) {
            $searchPath = '/layout/body/referenceContainer[@name="content"]/block[@name="product.detail.info"]/block[@name="product.info.media.image"]';
            $nodes = $layoutXMLElement->xpath($searchPath);
            if ($nodes) {
                $layoutUpdate = '
                    <referenceBlock name="product.detail.info">
                        <block class="Magento\Framework\View\Element\Template"
                            name="product.info.media.image"
                            template="MagicToolbox_MagicZoomPlus::product/view/gallery_wrapper.phtml" >
                            <block class="MagicToolbox\MagicZoomPlus\Block\Product\View\GalleryOriginal"
                                name="product.info.media.image.default"
                                template="MagicToolbox_MagicZoomPlus::product/view/gallery_original.phtml" />
                        </block>
                        <move element="product.info.media.magiczoomplus" destination="product.detail.info" after="product.info.media.image"/>
                    </referenceBlock>';
                $node = new \Magento\Framework\Simplexml\Element($layoutUpdate);
                $body = $layoutXMLElement->addChild('body');
                $body->appendChild($node);

                $scopeConfig = $this->magicToolboxHelper->getScopeConfig();
                $storeId = $this->magicToolboxHelper->getStoreManager()->getStore()->getId();
                $galleryPopup = $scopeConfig->getValue(
                    'mpanel/product_details/popup_gallery',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                    $storeId
                );
                if ($galleryPopup) {
                    $configWriter = $this->magicToolboxHelper->getConfigWriter();
                    $configWriter->save(
                        'mpanel/product_details/popup_gallery',
                        0,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                        $storeId
                    );
                }
            }
        }

        return $this;
    }
}
