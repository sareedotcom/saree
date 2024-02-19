<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Mageants\GiftCard\Model\Account as Giftaccount;
use \Magento\Customer\Model\Session;

/**
 * Account class for customer account
 */
class Account extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $_giftorders;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param Context $context
     * @param Giftaccount $giftAccount
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Giftaccount $giftAccount,
        Session $customerSession,
        array $data = []
    ) {
        $this->_giftorders = $giftAccount;
        $this->_customerSession = $customerSession;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }

    /**
     * Return reorder url
     *
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }
    
    /**
     * Return Gift order
     *
     * @return collection object
     */
    public function getGiftOrder()
    {
        $collection = $this->_giftorders->getCollection();
        $joinConditions = 'main_table.order_id = gift_code_customer.customer_id';
        $collection->getSelect()->joinLeft(
            ['gift_code_customer'],
            $joinConditions,
            []
        )->columns("gift_code_customer.*");

        // var_dump($collection->getSelect()->__toString());
        // exit();
        return $collection->addFieldToFilter('recipient_email', $this->_customerSession->getCustomerData()->getEmail());
        // return $collection;
    }
}
