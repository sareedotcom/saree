<?php

namespace Logicrays\OrderDeliveryEstimation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Logicrays\OrderDeliveryEstimation\Helper\Data;

class OrderStatusChangeBefore implements ObserverInterface
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param ProductRepository $productRepository
     * @param Data $helper
     */
    public function __construct(
        ProductRepository $productRepository,
        Data $helper
    ) {
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * Update dispatch date
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return string
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof \Magento\Framework\Model\AbstractModel) {
            if ($order->getState() == 'processing' && $order->getStatus() == 'processing') {
                foreach ($order->getAllVisibleItems() as $item) {
                    $product = $this->productRepository->getById($item->getProductId());
                    $options = $item->getProductOptions();
                    if (isset($options['options'])) {
                        $optionData = $options['options'];
                        foreach ($optionData as $value) {
                            $optionType = $value['option_type'];
                            if ($optionType == 'drop_down') {
                                $optionValue = $value['value'];
                                if (str_contains($optionValue, 'Days') || str_contains($optionValue, 'days')) {
                                    $extraWorkingDays = 0;
                                    $dispatchDate = $this->helper->getOptionDeliveryDay($product, $extraWorkingDays);
                                } else {
                                    $extraWorkingDays = 0;
                                    $dispatchDate = $this->helper->getDeliveryEstimationDate($product, $extraWorkingDays);
                                }
                            } else {
                                $dispatchDate = $this->helper->getDeliveryEstimationDate($product);
                            }
                        }
                    } else {
                        $dispatchDate = $this->helper->getDeliveryEstimationDate($product);
                    }
                    if ($dispatchDate) {
                        $item->setEstdDispatchDate($dispatchDate);
                        $item->save();
                    }
                }
            }
        }
        return $this;
    }
}
