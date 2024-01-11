<?php
namespace Logicrays\OrderCancellation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config;
use Logicrays\OrderCancellation\Helper\Data;

class CancelOrder extends Template
{
    protected $historyCollectionFactory;
    protected $helper;

    public function __construct(
        Context $context,
        Config $pageConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory,
        Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->pageConfig = $pageConfig;
        $this->helper = $helper;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->date = $date;
    }

    public function getOrderNumber()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        return $order_id;
    }

    public function modEnable()
    {
        $value = $this->helper->modEnable();
        return $value;
    }

    public function getDateForComplete()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $collection = $this->historyCollectionFactory->create();
        $collection->addFieldToFilter('parent_id', $order_id)
        ->addFieldToFilter('status', 'complete');
        return $collection->getData();
    }

    public function getOrderStatus()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->orderRepository->get($orderId);
        $state = $order->getStatus();
        return $state;
    }

    public function getLinkText()
    {
        return 'Cancellation Request';
    }
    public function getAssetUrl($asset)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $assetRepository = $objectManager->get('Magento\Framework\View\Asset\Repository');
        return $assetRepository->createAsset($asset)->getUrl();
    }

    public function getHourDiffAfterPlace()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($order_id);

        $currentDate = date_create($this->date->date());
        $updateData = date_create($this->date->date($order->getCreatedAt()));
        $interval = date_diff($updateData, $currentDate);
        $diffDate = (int) $interval->days;
        $hoursDiff = (int) $interval->h;

        if($diffDate <= 0 && $hoursDiff <= 2){
            return true;
        }
        return false;
    }
}
?>