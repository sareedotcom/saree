<?php

namespace Logicrays\OrderDeliveryEstimation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Logicrays\OrderDeliveryEstimation\Helper\Data;

class SetItemCustomAttribute implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param CheckoutSession $checkoutSession
     * @param SerializerInterface $serializer
     * @param ProductRepository $productRepository
     * @param RequestInterface $request
     * @param Data $helper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        RequestInterface $request,
        Data $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
        $this->_request = $request;
        $this->helper = $helper;
    }

    /**
     * Get order delivery estimation
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return string
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $moduleEnable = $this->helper->isEnable();
        if ($moduleEnable == 1) {
            $product = $this->_request->getParam('product');
            if ($product) {
                if ($this->_request->getFullActionName() == 'checkout_cart_add') {
                    $items = $observer->getEvent()->getData('items');
                    $items = $observer->getQuoteItem();
                    $options = $items->getProduct()->getTypeInstance(true)->getOrderOptions($items->getProduct());
                    // echo "<pre/>";
                    // print_r($options);exit;

                    $productQuoteData = $observer->getEvent()->getDataByKey('product');
                    $QuoteItem = $this->checkoutSession->getQuote()->getItemByProduct($productQuoteData);
                    foreach ($items as $item) {
                        // $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                        // echo "<pre/>";
                        // print_r($options);exit;
                        $product = $this->productRepository->getById($item->getProductId());
                        if (isset($options['options'])) {
                            $optionData = $options['options'];
                            foreach ($optionData as $value) {
                                $optionType = $value['option_type'];
                                if ($optionType == 'drop_down') {
                                    $optionValue = $value['value'];
                                    if (str_contains($optionValue, 'Days') || str_contains($optionValue, 'days')) {
                                        $optionTitle = $value['value'];
                                        if (str_contains($optionTitle, 'Days')) {
                                            if (str_contains($optionTitle, 'To')) {
                                                $optionDays = explode('To', $optionTitle);
                                            } else {
                                                $optionDays = explode('to', $optionTitle);
                                            }
                                            $optionDay = strtok($optionDays[1], " ");
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
                            $additionalOptions = [];
                            $additionalOptions[] = [
                                'label' => 'Est Dispatch Date',
                                'value' => $dispatchDate,
                            ];
                            if (count($additionalOptions) > 0) {
                                $item->addOption([
                                    'product_id' => $item->getProductId(),
                                    'code' => 'additional_options',
                                    'value' => $this->serializer->serialize($additionalOptions),
                                ]);
                            }
                            $item->setEstdDispatchDate($dispatchDate);
                        }
                    }
                }
            } else {
                if ($this->_request->getFullActionName() == 'sales_order_reorder') {
                    if ($this->_request->getParam('order_id')) {
                        $items = $observer->getEvent()->getData('items');
                        foreach ($items as $item) {
                            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                            $product = $this->productRepository->getById($item->getProductId());
                            if (isset($options['options'])) {
                                $optionData = $options['options'];
                                foreach ($optionData as $value) {
                                    $optionType = $value['option_type'];
                                    if ($optionType == 'drop_down') {
                                        $optionValue = $value['value'];
                                        if (str_contains($optionValue, 'Days') || str_contains($optionValue, 'days')) {
                                            $optionTitle = $value['value'];
                                            if (str_contains($optionTitle, 'Days')) {
                                                if (str_contains($optionTitle, 'To')) {
                                                    $optionDays = explode('To', $optionTitle);
                                                } else {
                                                    $optionDays = explode('to', $optionTitle);
                                                }
                                                $optionDay = strtok($optionDays[1], " ");
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
                                $additionalOptions = [];
                                $additionalOptions[] = [
                                    'label' => 'Est Dispatch Date',
                                    'value' => $dispatchDate,
                                ];
                                if (count($additionalOptions) > 0) {
                                    $item->addOption([
                                        'product_id' => $item->getProductId(),
                                        'code' => 'additional_options',
                                        'value' => $this->serializer->serialize($additionalOptions),
                                    ]);
                                }
                                $item->setEstdDispatchDate($dispatchDate);
                                $item->save();
                            }
                        }
                    }
                }
            }
        }
    }
}
