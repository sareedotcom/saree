<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\Total;

use \Magento\Checkout\Model\Session;
use \Magento\Framework\Pricing\PriceCurrencyInterface;

class Gift extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @var String
     */
    protected $_fees;

    /**
     * @param Session $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Session $checkoutSession,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_checkoutSession=$checkoutSession;
        $this->_priceCurrency = $priceCurrency;
        $this->_fees=0;

        if ($this->_checkoutSession->getGift()!=''):

            $this->_fees=$this->_checkoutSession->getGift();
        endif;
    }

    /**
     * To call Collect data
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $this->_fees=0;

        if ($this->_checkoutSession->getGift()!=''):

            $this->_fees=$this->_checkoutSession->getGift();
        endif;

        $discount = $this->_fees;
        $total->addTotalAmount('giftcertificate', - $discount);
        $total->addBaseTotalAmount('giftcertificate', -$discount);
        //$total->setBaseGrandTotal($total->getBaseGrandTotal() - $discount);
        $quote->setCustomDiscount(-$discount);
        return $this;
    }

    /**
     * To clear values
     *
     * @param Magento\Quote\Model\Quote\Address $total
     */
    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
    
    /**
     * Fetch the data
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        
        return [
            'code' => 'giftcertificate',
            'title' => 'giftcertificate',
            'value' => -$this->_fees
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Gift Card');
    }
}
