<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Checkout\Model\Cart;
use \Magento\Checkout\Model\Session;
use \Psr\Log\LoggerInterface;
use Mageants\GiftCard\Helper\Data;

class GiftCardConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helperdata;

    /**
     * @param Data $helperdata
     * @param Cart $cart
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helperdata,
        Cart $cart,
        Session $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->helperdata = $helperdata;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->logger = $logger;
    }

    /**
     * Get Config value
     *
     * @return array
     */
    public function getConfig()
    {
        $_gcstatus=$this->helperdata->getStatus();
        $status = ($_gcstatus == 1) ? true : false;

        $GiftCard = [];
        $GiftCard['giftcertificatestatus'] = $status;
        $GiftCard['giftcategoryids'] = $this->getCategories();
        $GiftCard['giftcertificatecode'] = $this->checkoutSession->getGiftCardCode();
        return $GiftCard;
    }

    /**
     * Get item categories from cart
     */
    public function getCategories()
    {
        $items = $this->cart->getQuote()->getAllVisibleItems();
        $cat_ids = [];
        if ($items) {
            foreach ($items as $item) {
                $cat_id = "";
                foreach ($item->getProduct()->getCategoryIds() as $categoryid) {
                    if ($cat_id == "") {
                        $cat_id = $categoryid;
                    } else {
                        $cat_id = $cat_id.",".$categoryid;
                    }
                }
                $cat_ids[] = $cat_id;
            }
        }
        return $cat_ids;
    }
}
