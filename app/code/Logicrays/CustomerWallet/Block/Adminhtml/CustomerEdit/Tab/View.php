<?php
namespace Logicrays\CustomerWallet\Block\Adminhtml\CustomerEdit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Logicrays\CustomerWallet\Model\CustomerWallet;
use Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory;
use Magento\Customer\Controller\RegistryConstants;

class View extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * Template variable
     *
     * @var string
     */
    protected $_template = 'tab/customer_view.phtml';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * __construct function
     *
     * @param Context $context
     * @param Registry $registry
     * @param CustomerWallet $customerWalletCollection
     * @param CollectionFactory $collectionFactory
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerWallet $customerWalletCollection,
        CollectionFactory $collectionFactory,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->customerWalletCollection = $customerWalletCollection;
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Get Customer ID
     *
     * @return string || null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get Label of Customer TAB
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Wallet History');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Wallet History');
    }

    /**
     * Tab can show or not
     *
     * @return boolean
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * Check tab is hidden or not
     *
     * @return boolean
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Get Customer Wallet Collection function
     *
     * @return array
     */
    public function getCustomerWalletCollection()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $collection = $this->customerWalletCollection->getCollection()->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('created_at', 'desc');
        return $collection;
    }

    /**
     * Get Customer Wallet Amount function
     *
     * @return string
     */
    public function getWalletAmount()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $creditedAmountData = $this->collectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', '1');
        $creditedAmount = 0;
        foreach ($creditedAmountData as $amount) {
            $creditedAmount += $amount->getAmount();
        }
        return $creditedAmount;
    }

    /**
     * Get Currency Symbol function
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->helperData->getCurrentCurrencySymbol();
    }

    /**
     * Get Customer Wallet Amount function
     *
     * @return string
     */
    public function getRemainWalletAmount()
    {
        $walletAmount = $this->getCreditedAmount() - $this->getDebitedAmount();
        if ($walletAmount <= 0) {
            return 0.00;
        }
        return $walletAmount;
    }

    /**
     * Get Credited Wallet Amount function
     *
     * @return float
     */
    public function getCreditedAmount()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $creditedAmountData = $this->collectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', '1');
        $creditedAmount = 0;
        foreach ($creditedAmountData as $amount) {
            $creditedAmount += $amount->getAmount();
        }
        return $creditedAmount;
    }

    /**
     * Get Debited Wallet Amount function
     *
     * @return float
     */
    public function getDebitedAmount()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $debitedAmountData = $this->collectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', '4');
        $debitedAmount = 0;
        foreach ($debitedAmountData as $amount) {
            $debitedAmount += $amount->getAmount();
        }
        return $debitedAmount;
    }

    /**
     * Get View Order Link function
     *
     * @param string $orderIncrementId
     * @return string
     */
    public function getViewOrderLink($orderIncrementId)
    {
        $orderId = $this->helperData->getOrderId($orderIncrementId);
        $url = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $orderId]);
        return $url;
    }
}
