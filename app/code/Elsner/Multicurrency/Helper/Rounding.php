<?php

namespace Elsner\Multicurrency\Helper;

class Rounding extends \Magento\Framework\App\Helper\AbstractHelper{

	/**
	 * Contain Shipping and Tax etc.
	 *
     * @var array
     */
	protected $_extraPrice;

	/**
	 * Contain Item price and Discount.
	 *
     * @var array
     */
	protected $_itemPrice;

	/**
     * Add Extra price to array
     *
     * @param $key
     * @param $value
     */
	public function addExtraPrice($key,$value){

		$this->_extraPrice[$key] = $value;
	}

	/**
     * Add Item price to array
     *
     * @param $i
     * @param $key
     * @param $value
     */
	public function addItemPrice($i,$key,$value){
		
		$this->_itemPrice[$i][$key] = $value;
	}

	/**
	 * Rounding Amount
     * Convert Request that match paypal requirement. 
     *
     * @param array $request
     */
	public function convertRequest(array &$request){
		
		$itemAmount = 0;
		$extraprice = 0;
		foreach ($this->_itemPrice as $item) {
			$itemAmount = $itemAmount + ((int) $item['qty'] * (float) $item['amount']);
		}
		foreach ($this->_extraPrice as $key => $value) {
            $extraprice = (float) $extraprice + (float) $value;   
        }
        $baseprice = $extraprice + $itemAmount;

        $request['AMT'] = $baseprice;
        $request['ITEMAMT'] = $itemAmount;

        return $request;
	}
}