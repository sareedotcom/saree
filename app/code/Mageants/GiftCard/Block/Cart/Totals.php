<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\GiftCard\Block\Cart;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Customer\Model\Session as CustomerSession;
use \Magento\Checkout\Model\Session;
use \Magento\Sales\Model\Config;

/**
 * Total class for cart Totals
 */
class Totals extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * Return to layout
     *
     * @return $this
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return parent::getJsLayout();
    }
}
