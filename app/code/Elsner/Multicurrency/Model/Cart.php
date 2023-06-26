<?php
namespace Elsner\Multicurrency\Model;

class Cart extends \Magento\Paypal\Model\Cart
{
	protected $_helper;
	protected $_currency;

    public function __construct(
        \Magento\Payment\Model\Cart\SalesModel\Factory $salesModelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Directory\Model\Currency $currency,
        \Elsner\Multicurrency\Helper\Data $helper,
        $salesModel
    ) {
        parent::__construct($salesModelFactory, $eventManager, $salesModel);
        $this->_helper = $helper;
        $this->_currency = $currency;
    }

    public function getAmounts()
    {
        $this->_collectItemsAndAmounts();

        if (!$this->_areAmountsValid) {
            $subtotal = $this->getSubtotal() + $this->getTax();

            if (empty($this->_transferFlags[self::AMOUNT_SHIPPING])) {
                $subtotal += $this->getShipping();
            }

            if (empty($this->_transferFlags[self::AMOUNT_DISCOUNT])) {
                $subtotal -= $this->getDiscount();
            }

            return [self::AMOUNT_SUBTOTAL => $subtotal];
        }

        return $this->_amounts;
    }

	protected function _importItemsFromSalesModel()
    {
        $this->_salesModelItems = [];
        foreach ($this->_salesModel->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $amount = $item->getPrice();
            $qty = $item->getQty();

            $subAggregatedLabel = '';

            // workaround in case if item subtotal precision is not compatible with PayPal (.2)
            if ($amount - round($amount, 2)) {
                $amount = $amount * $qty;
                $subAggregatedLabel = ' x' . $qty;
                $qty = 1;
            }

            // aggregate item price if item qty * price does not match row total
            $itemBaseRowTotal = $item->getOriginalItem()->getBaseRowTotal();
            if ($amount * $qty != $itemBaseRowTotal) {
                $amount = (double)$itemBaseRowTotal;
                $subAggregatedLabel = ' x' . $qty;
                $qty = 1;
            }

            $this->_salesModelItems[] = $this->_createItemFromData(
                $item->getName() . $subAggregatedLabel,
                $qty,
                $amount
            );
        }
        //echo $this->_salesModel->getTaxContainer()->getDiscountAmount(); exit;
        if($this->_helper->getEnableModule() && $this->_helper->getToCurrency() == $this->_salesModel->getDataUsingMethod('quote_currency_code')){
        	$this->addSubtotal($this->_salesModel->getTaxContainer()->getSubtotal());
	        $this->addTax($this->_salesModel->getTaxContainer()->getTaxAmount());
	        $this->addShipping($this->_salesModel->getTaxContainer()->getShippingAmount());
	        $this->addDiscount(abs($this->_salesModel->getTaxContainer()->getDiscountAmount()));
        }else{
        	$this->addSubtotal($this->_helper->getConvertedAmount($this->_salesModel->getBaseSubtotal()));
	        $this->addTax($this->_helper->getConvertedAmount($this->_salesModel->getBaseTaxAmount()));
	        $this->addShipping($this->_helper->getConvertedAmount($this->_salesModel->getBaseShippingAmount()));
	        $this->addDiscount(abs($this->_helper->getConvertedAmount($this->_salesModel->getBaseDiscountAmount())));
        }
        if($this->_salesModel->getDataUsingMethod('base_aw_reward_points_amount') < 0 && $this->getDiscount() > 0){
           if($this->_helper->getEnableModule() && $this->_helper->getToCurrency() == $this->_salesModel->getDataUsingMethod('quote_currency_code')){
                $this->addDiscount($this->_salesModel->getDataUsingMethod('base_aw_reward_points_amount'));
                $this->addDiscount(abs($this->_salesModel->getDataUsingMethod('aw_reward_points_amount')));
            }
        }
        
    }

    public function getMulticurrencyTotal()
    {
    	if($this->_helper->getEnableModule() && $this->_helper->getToCurrency() == $this->_salesModel->getDataUsingMethod('quote_currency_code')){
	        return $this->_salesModel->getTaxContainer()->getGrandTotal();
        }else{
        	$amount = $this->_helper->getConvertedAmount($this->_salesModel->getTaxContainer()->getBaseGrandTotal());
        	return $this->_currency->format($amount, ['display'=>\Zend_Currency::NO_SYMBOL], false);
        }
    }

    protected function _validate()
    {
        $areItemsValid = false;
        $this->_areAmountsValid = false;

        $referenceAmount = $this->_salesModel->getDataUsingMethod('base_grand_total');

        if($this->_helper->getEnableModule() && $this->_helper->getToCurrency() == $this->_salesModel->getDataUsingMethod('quote_currency_code')){
    		$referenceAmount = $this->_salesModel->getDataUsingMethod('grand_total');
    	}else{
        	$referenceAmount = $this->_helper->getConvertedAmount($referenceAmount);
        }
    	//echo 'referenceAmount'.$this->_salesModel->getDataUsingMethod('quote_currency_code').'<br>';
        $itemsSubtotal = 0;
        foreach ($this->getAllItems() as $key=>$i) {
        	if($this->_helper->getEnableModule() && is_object($i->getName()) !== true){
        		$itemPrice = $this->_helper->getConvertedAmount($i->getAmount());
        	}else{
        		$itemPrice = $i->getAmount();
                if($this->_salesModel->getDataUsingMethod('base_aw_reward_points_amount') < 0){
                   $itemPrice = $this->_helper->getConvertedAmount($i->getAmount());
                }
                
        	}

            $itemsSubtotal = $itemsSubtotal + ($i->getQty() * $itemPrice);
        }

        $sum = $itemsSubtotal + $this->getTax();

        if (empty($this->_transferFlags[self::AMOUNT_SHIPPING])) {
            $sum += $this->getShipping();
        }

        if (empty($this->_transferFlags[self::AMOUNT_DISCOUNT])) {
            $sum -= $this->getDiscount();
            // PayPal requires to have discount less than items subtotal
            $this->_areAmountsValid = round($this->getDiscount(), 4) < round($itemsSubtotal, 4);
        } else {
            $this->_areAmountsValid = $itemsSubtotal > 0.00001;
        }
        /**
         * numbers are intentionally converted to strings because of possible comparison error
         * see http://php.net/float
         */
        // match sum of all the items and totals to the reference amount
        /*echo sprintf('%.4F', $sum);
        echo "<br>";
        echo sprintf('%.4F', $referenceAmount);
        echo "<br>";*/
        if (sprintf('%.4F', $sum) == sprintf('%.4F', $referenceAmount)) {
            $areItemsValid = true;
        }

        $areItemsValid = $areItemsValid && $this->_areAmountsValid;

        /*if (!$areItemsValid) {
            $this->_salesModelItems = [];
            $this->_customItems = [];
        }*/


    }
}