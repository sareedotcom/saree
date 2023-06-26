<?php

namespace MagicToolbox\MagicZoomPlus\Block\Product\View;

/**
 * Magic Zoom Plus layouts template block
 *
 */
class Layouts extends \Magento\Framework\View\Element\Template
{
    /**
     * Default template
     *
     * @var string
     */
    protected $defaultTemplate = 'MagicToolbox_MagicZoomPlus::product/view/layouts/bottom.phtml';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        if (isset($data['layout'])) {
            $this->setTemplate('MagicToolbox_MagicZoomPlus::product/view/layouts/' . $data['layout'] . '.phtml');
        } else {
            $this->setTemplate($this->defaultTemplate);
        }

        parent::__construct($context, $data);
    }
}
