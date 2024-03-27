<?php
namespace Logicrays\TermsAndCondition\Observer\Sales;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
    * @var \Magento\Catalog\Model\ProductRepository
    */
    protected $productRepository;
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }
    /**
    * Execute observer
    *
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    * @throws \Magento\Framework\Exception\NoSuchEntityException
    */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order= $observer->getData('order');
        $order->setTermsAndConditionIsAccepted("Verified & Agreed.");
        $order->save();
    }
}