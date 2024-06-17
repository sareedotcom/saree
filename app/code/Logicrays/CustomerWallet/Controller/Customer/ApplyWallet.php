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

class ApplyWallet extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Logicrays\CustomerWallet\Helper\Data $helperData
    ) {
        $this->_pageFactory = $pageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->jsonFactory = $jsonFactory;
        $this->helperData = $helperData;
        return parent::__construct($context);
    }

    /**
     * View page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $isChecked = $this->getRequest()->getParam('isChecked');
        // $data = $this->getRequest()->getParams();

        $quote = $this->checkoutSession->getQuote();
        if ($isChecked) {
            $walletAmount = $this->helperData->getRemainWalletAmount();
            $amountToPay = $quote->getBaseGrandTotal();

            if ($amountToPay > $walletAmount) {
                $amountToPay = $walletAmount;
            }
            $quote->setWalletamount($amountToPay);
        } else {
            $quote->setWalletamount(0);
        }
        $quote->save();

        return $resultJson->setData([
            'success' => true
        ]);
    }
}
