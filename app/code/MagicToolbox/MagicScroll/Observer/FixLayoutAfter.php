<?php

namespace MagicToolbox\MagicScroll\Observer;

/**
 * MagicScroll Observer
 *
 */
class FixLayoutAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Flag, is layout already fixed
     *
     * @var bool
     */
    protected $isLayoutFixed = false;

    /**
     * Helper
     *
     * @var \MagicToolbox\MagicScroll\Helper\Data
     */
    protected $magicToolboxHelper = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \MagicToolbox\MagicScroll\Helper\Data $magicToolboxHelper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \MagicToolbox\MagicScroll\Helper\Data $magicToolboxHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
        $this->coreRegistry = $registry;
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

        if ($this->isLayoutFixed) {
            $data = $this->coreRegistry->registry('magictoolbox');
            if (is_array($data)) {
                $magicscroll = $this->magicToolboxHelper->getToolObj();
                $isEnabled = !$magicscroll->params->checkValue('enable-effect', 'No', 'product');
                if ($isEnabled) {
                    foreach ($data['blocks'] as $name => $block) {
                        if ($block && $name != $data['current']) {
                            $layout->unsetElement($name);
                        }
                    }
                } else {
                    $layout->unsetElement('product.info.media.magicscroll');
                }
            }
            return $this;
        }

        $block = $layout->getBlock('product.info.media.magicscroll');
        if ($block) {
            $data = $this->coreRegistry->registry('magictoolbox');
            if (is_null($data)) {
                $data = [
                    'current' => '',
                    'blocks' => [
                        'product.info.media.magic360' => null,
                        'product.info.media.magicslideshow' => null,
                        'product.info.media.magicscroll' => null,
                        'product.info.media.magiczoomplus' => null,
                        'product.info.media.magiczoom' => null,
                        'product.info.media.magicthumb' => null,
                        'product.info.media.image' => null,
                    ],
                    'cooperative-mode' => false,
                    'standalone-mode' => false,
                    'additional-block-name' => '',
                    'magicscroll' => '',
                    'mgs-mode' => false,
                ];
            } else {
                isset($data['mgs-mode']) || ($data['mgs-mode'] = false);
            }

            if (empty($data['current'])) {
                $original = $layout->getBlock('product.info.media.image');
                if ($original) {
                    $data['current'] = 'product.info.media.image';
                    $data['blocks']['product.info.media.image'] = $original;
                    if (preg_match(
                        '#^MagicToolbox_[a-zA-Z0-9]++::product/view/gallery_wrapper\.phtml#',
                        $original->getTemplate()
                    )) {
                        $data['mgs-mode'] = true;
                        $data['blocks']['product.info.media.image'] = $layout->getBlock('product.info.media.image.default');
                    }
                }
            }

            $magicscroll = $this->magicToolboxHelper->getToolObj();
            $isEnabled = !$magicscroll->params->checkValue('enable-effect', 'No', 'product');

            if ($isEnabled) {
                $unsetCurrent = !$data['mgs-mode'];
                $unsetCurrent && $layout->unsetElement($data['current']);
                $data['current'] = 'product.info.media.magicscroll';
                $data['blocks']['product.info.media.magicscroll'] = $block;
                //NOTE: to determine which module will display magicscroll headers on the product page
                $data['magicscroll'] = 'magicscroll';

                $this->isLayoutFixed = true;
            } else {
                $layout->unsetElement('product.info.media.magicscroll');
            }
            $this->coreRegistry->unregister('magictoolbox');
            $this->coreRegistry->register('magictoolbox', $data);
        }

        $block = $layout->getBlock('configurable.magicscroll');
        if ($block) {

            $data = $this->coreRegistry->registry('magictoolbox_category');
            if (is_null($data)) {
                $data = [
                    'current-renderer' => '',
                    'renderers' => [
                        'configurable.magic360' => null,
                        'configurable.magicslideshow' => null,
                        'configurable.magicscroll' => null,
                        'configurable.magiczoomplus' => null,
                        'configurable.magiczoom' => null,
                        'configurable.magicthumb' => null,
                        //'configurable' => null,
                    ],
                ];
            }

            /** @var $rendererList \Magento\Framework\View\Element\RendererList */
            $rendererList = $layout->getBlock('category.product.type.details.renderers');

            if (empty($data['current-renderer'])) {
                if ($rendererList) {
                    /** @var $renderer \Magento\Swatches\Block\Product\Renderer\Listing\Configurable */
                    $renderer = $rendererList->getChildBlock('configurable');
                    if ($renderer) {
                        $name = $renderer->getNameInLayout();
                        $data['current-renderer'] = $name;
                        $data['renderers'][$name] = $renderer;
                    }
                }
            }

            $magicscroll = $this->magicToolboxHelper->getToolObj();
            $isEnabled = $magicscroll->params->checkValue('enable-effect', 'Yes', 'category');
            if ($isEnabled) {
                if ($rendererList) {
                    $rendererList->setChild('configurable', $block);
                }
                $data['current-renderer'] = 'configurable.magicscroll';
                $data['renderers']['configurable.magicscroll'] = $block;
            } else {
                $layout->unsetElement('configurable.magicscroll');
            }
            $this->coreRegistry->unregister('magictoolbox_category');
            $this->coreRegistry->register('magictoolbox_category', $data);
        }

        return $this;
    }
}
