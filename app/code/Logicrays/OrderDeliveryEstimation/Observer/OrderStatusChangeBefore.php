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
        $moduleEnable = $this->helper->isEnable();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/AfterOrderPlacePaymentIssue.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('===== Start =====');
        
        if ($moduleEnable == 1) {
            $order = $observer->getEvent()->getOrder();
            $logger->info($order->getIncrementId());
            $logger->info("111111");
             $logger->info($order->getStatus());
            if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                $currentPaymentMethod = $order->getPayment()->getMethod();
                $paymentMethods = ["cashondelivery", "checkmo"];
                if (in_array($currentPaymentMethod, $paymentMethods)) {
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
                                            $optionTitle = $value['value'];
                                            if (str_contains($optionTitle, 'To')) {
                                                $optionDays = explode('To', $optionTitle);
                                                $optionDay = strtok($optionDays[1], " ");
                                            } elseif (str_contains($optionTitle, 'to')) {
                                                $optionDays = explode('to', $optionTitle);
                                                $optionDay = strtok($optionDays[1], " ");
                                            } else {
                                                if (preg_match('/\((\d+)\s/', $optionTitle, $matches)) {
                                                    $optionDay = $matches[1];
                                                }
                                            }
                                            $dispatchDate = $this->helper->getOptionDeliveryDay($product, $optionDay);
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
            }
            $logger->info("22222");
            $logger->info('===== End =====');
            return $this;
        }
    }
}
