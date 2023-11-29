<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model;

use \Psr\Log\LoggerInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Payment\Model\Checks\ZeroTotal;
use \Magento\Payment\Model\MethodList;

/**
 * PaymentMethodManagement  Model class for gift card
 */
class PaymentMethodManagement extends \Magento\Quote\Model\PaymentMethodManagement
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Session
     */
    protected $session;
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Session $session
     * @param CartRepositoryInterface $quoteRepository
     * @param ZeroTotal $zeroTotalValidator
     * @param MethodList $methodList
     */
    public function __construct(
        LoggerInterface $logger,
        Session $session,
        CartRepositoryInterface $quoteRepository,
        ZeroTotal $zeroTotalValidator,
        MethodList $methodList
    ) {
        $this->logger = $logger;
        $this->session = $session;
        parent::__construct(
            $quoteRepository,
            $zeroTotalValidator,
            $methodList
        );
    }
    
    /**
     * @inheritdoc
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        $method->setChecks([
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ]);
        $payment = $quote->getPayment();
        $this->logger->debug("tet123".$payment->getMethod());
        $data = $method->getData();
        $payment->importData($data);

        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod($payment->getMethod());
        } else {
            // check if shipping address is set
            if ($quote->getShippingAddress()->getCountryId() === null) {
                throw new InvalidTransitionException(__('Shipping address is not set'));
            }
            $quote->getShippingAddress()->setPaymentMethod($payment->getMethod());
        }
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
        $_checkoutSession = $this->session;
        if ($_checkoutSession->getAccountid()=='' && $_checkoutSession->getGift()=='') {
            if (!$this->zeroTotalValidator->isApplicable($payment->getMethodInstance(), $quote)) {
                throw new InvalidTransitionException(__('The requested Payment Method is not available.'));
            }
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
        return $quote->getPayment()->getId();
    }
}
