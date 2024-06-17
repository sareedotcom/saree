<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Controller\Customer;

use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;

class Paynow extends \Magento\Framework\App\Action\Action
{
    /**
     * _cart variable
     *
     * @var _cart
     */
    protected $_cart;

    /**
     * _product variable
     *
     * @var _product
     */
    protected $_product;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Cart $cart
     * @param Product $product
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Cart $cart,
        Product $product,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_cart = $cart;
        $this->_product = $product;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->jsonFactory = $jsonFactory;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Execute function add to card custom product
     *
     * @return mixed
     */
    public function execute()
    {
        $resultPageFactory = $this->resultRedirectFactory->create();
        if (!$this->customerSession->isLoggedIn()) {
            $resultPageFactory->setPath('customer/account/login');
            return $resultPageFactory;
        }
        $resultJson = $this->jsonFactory->create();
        $productSku = $this->getRequest()->getParam('product-sku-wallet');
        $requestedAmount = $this->getRequest()->getParam('wallet-amount');

        $product = $this->_product->loadByAttribute('sku', $productSku);
        $msg = $this->helperData->otherProductsInCart();
        if (!$msg) {
            $msg = 'You have Other Products in Cart, Proceed to checkout OR Clear it';
        }
        $productId = $product->getId();
        $url = $this->storeManager->getStore()->getBaseUrl().'checkout';
        try {
            $checkoutData = $this->checkoutSession->getQuote()->getAllVisibleItems();
            if (!empty($checkoutData)) {
                foreach ($checkoutData as $checkoutItems) {
                    if ($checkoutItems->getSku() == $this->helperData->walletSKU()) {
                        $this->_cart->truncate();
                    } else {
                        $this->messageManager->addNotice(__("$msg <a href='$url/cart'>shopping cart</a>"));
                        // return $this;
                        return $resultJson->setData([
                            'success' => false
                        ]);
                    }
                }
            }
            if ($this->helperData->getMaxLimit()) {
                if ($requestedAmount > $this->helperData->getMaxLimit()) {
                    $maxLimitMsg = "Amount is not more than ";
                    if ($this->helperData->getMaxLimitMsg()) {
                        $maxLimitMsg = $this->helperData->getMaxLimitMsg();
                    }
                    $this->messageManager->addError(__($maxLimitMsg .
                    $this->helperData->getCurrentCurrencySymbol().''.
                    $this->helperData->getMaxLimit()));
                    return $resultJson->setData([
                        'success' => false
                    ]);
                }
            }
            $qty = 1;
            $params = [
                'product' => $productId,
                'qty' => $qty
            ];
            $quoteId = $this->checkoutSession->getQuote()->getId();
            $this->_cart->addProduct($product, $params);
            $this->_cart->save();
            $quote = $this->quoteRepository->get($quoteId);
            $quote->setData('is_wallet_request', 1);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultJson->setData([
            'success' => true
        ]);
    }
}
