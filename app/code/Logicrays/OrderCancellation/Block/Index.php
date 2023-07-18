<?php
namespace Logicrays\OrderCancellation\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     *
     * @var OrderRepository
     */
    protected $order;

    /**
     *
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     *
     * @var OrderInterfaceFactory
     */
    protected $orderInterfaceFactory;

    /**
     *
     * @var Order
     */
    protected $orders;

    /**
     *
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     *
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     *
     * @var Data
     */
    protected $priceHelper;

    /**
     *
     * @param Context $context
     * @param \Magento\Sales\Model\OrderRepository $order
     * @param \Magento\Sales\Model\Order $orders
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderRepository $order,
        \Magento\Sales\Model\Order $orders,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        $this->order = $order;
        $this->orders = $orders;
        $this->orderRepository = $orderRepository;
        $this->countryFactory = $countryFactory;
        $this->timezone = $timezone;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Gets Page Heading
     */
    public function getText()
    {
        return 'Request Cancellation Page';
    }

    /**
     * Gets Order Id
     */
    public function getOrderNumber()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        return $order_id;
    }

    /**
     * Gets Billing Address
     */
    public function getCustomerBillingAdd()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->orders->load($orderId);
        $billingaddress_tmp = $order->getBillingAddress();
        return $billingaddress_tmp;
    }

    /**
     * Gets Order Increment Id
     */
    public function getOrder()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->order->get($orderId);
        return $order->getIncrementId();
    }

    /**
     *
     * Gets Order Status
     */
    public function getOrderStatus()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->order->get($orderId);
        return $order->getStatus();
    }

    /**
     *
     * Gets Order Data
     */
    public function getOrderInfo()
    {
        $incrementId = $this->getOrder();
        $orderId = $this->getOrderNumber();
        $order = $this->orders->loadByIncrementId($incrementId);
        return $order;
    }

    /**
     *
     * Gets Order Created Date
     */
    public function getOrderCreateDate()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->orders->load($orderId);
        $created = $order->getCreatedAt();
        $created = $this->timezone->date(new \DateTime($created));
        $dateAsString = $created->format('Y-m-d H:i:s');
        return $dateAsString;
    }

    /**
     *
     * Gets Order Items
     */
    public function getOrderItem()
    {
        $incrementId = $this->getOrder();
        $order = $this->orders->loadByIncrementId($incrementId);
        $orderItems = $order->getAllItems();
        return $orderItems;
    }

    /**
     *
     * Gets Shipping Address
     */
    public function getCustomerShippingAdd()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->orders->load($orderId);
        $shippingaddress_tmp = $order->getShippingAddress();
        return $shippingaddress_tmp;
    }

    /**
     *
     * Gets Country Name
     */
    public function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     *
     * Gets Order Cancellation Reason
     */
    public function getReasons()
    {
        $options[0] =  ['value' => '', 'label' => 'Please Select Reason'];
        $options[1] =  ['value' => 'mistakely_placed', 'label' => 'Mistakely Placed'];
        $options[2] =  ['value' => 'product_alteration', 'label' => 'Product Alteration'];
        $options[3] =  ['value' => 'wrong_item_shipped', 'label' => 'Wrong Item Shipped'];
        $options[4] =  ['value' => 'damaged_item_shipped', 'label' => 'Damaged Item Shipped'];
        $options[5] =  ['value' => 'other', 'label' => 'Other'];
        return $options;
    }

    /**
     *
     * Gets Price Format
     */
    public function getPriceFormat($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
