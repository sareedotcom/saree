<?php
namespace Logicrays\OrderCancellation\Block;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $order;
    protected $orderRepository;
    protected $orderInterfaceFactory;
    protected $orders;
    protected $countryFactory;
    private $timezone;
    protected $helper;

    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderRepository $order,
        \Magento\Sales\Model\Order $orders,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        Data $helper,
        array $data = []
    ) {
        $this->order = $order;
        $this->orders = $orders;
        $this->orderRepository = $orderRepository;
        $this->countryFactory = $countryFactory;
        $this->timezone = $timezone;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getText()
    {
        return 'Request Cancellation Page';
    }

    public function getCustomerBillingAdd()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orders->load($orderId);
        $billingaddress_tmp = $order->getBillingAddress();
        return $billingaddress_tmp;
    }

    public function getOrderNumber()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        return $orderId;
    }

    public function getOrder()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->order->get($orderId);
        return $order->getIncrementId();
    }

    public function getOrderStatus()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->order->get($orderId);
        return $order->getStatus();
    }

    public function getOrderInfo()
    {
        $incrementId = $this->getOrder();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orders->loadByIncrementId($incrementId);
        return $order;
    }

    public function getOrderCreateDate()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orders->load($orderId);
        $created = $order->getCreatedAt();
        $created = $this->timezone->date(new \DateTime($created));
        $dateAsString = $created->format('Y-m-d H:i:s');
        return $dateAsString;
    }

    public function getOrderItem()
    {
        $incrementId = $this->getOrder();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
        $orderItems = $order->getAllItems();
        return $orderItems;
    }

    public function getCustomerShippingAdd()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orders->load($orderId);
        $shippingaddress_tmp = $order->getShippingAddress();
        return $shippingaddress_tmp;
    }

    public function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

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

    public function getAssetUrl($asset)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $assetRepository = $objectManager->get('Magento\Framework\View\Asset\Repository');
        return $assetRepository->createAsset($asset)->getUrl();
    }
}