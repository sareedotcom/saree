<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Controller\Adminhtml\Form;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Helper\Product\Composite as CompositeProductHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartItemRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface as CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory as QuoteItemOptionCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\Collection as QuoteItemOptionCollection;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\Quote\Item;
use Magento\Sales\Controller\Adminhtml\Order\Create;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;

/**
 * Class ConfigureQuoteItems
 */
class ConfigureQuoteItems extends Create
{
    /**
     * @var CompositeProductHelper
     */
    protected $productCompositeHelper;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var CartItemRepositoryInterface
     */
    protected $cartItemRepository;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var QuoteItemOptionCollectionFactory
     */
    protected $quoteItemOptionCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CartItemOptionsProcessor
     */
    protected $cartItemOptionsProcessor;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * ConfigureQuoteItems constructor.
     *
     * @param Action\Context $context
     * @param ProductHelper $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param CompositeProductHelper $productCompositeHelper
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param QuoteItemOptionCollectionFactory $quoteItemOptionCollectionFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param CartItemOptionsProcessor $cartItemOptionsProcessor
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        ProductHelper $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        CompositeProductHelper $productCompositeHelper,
        OrderItemRepositoryInterface $orderItemRepository,
        CartItemRepositoryInterface $cartItemRepository,
        DataObjectFactory $dataObjectFactory,
        QuoteItemOptionCollectionFactory $quoteItemOptionCollectionFactory,
        CartRepositoryInterface $quoteRepository,
        CartItemOptionsProcessor $cartItemOptionsProcessor,
        OrderRepositoryInterface $orderRepository,
        Data $helper
    ) {
        $this->productCompositeHelper           = $productCompositeHelper;
        $this->orderItemRepository              = $orderItemRepository;
        $this->cartItemRepository               = $cartItemRepository;
        $this->dataObjectFactory                = $dataObjectFactory;
        $this->quoteItemOptionCollectionFactory = $quoteItemOptionCollectionFactory;
        $this->quoteRepository                  = $quoteRepository;
        $this->cartItemOptionsProcessor         = $cartItemOptionsProcessor;
        $this->orderRepository                  = $orderRepository;
        $this->helper                           = $helper;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
    }

    /**
     * @return Layout
     */
    public function execute(): Layout
    {
        // Prepare data
        $configureResult = $this->dataObjectFactory->create();
        try {
            $quoteItem   = $this->getQuoteItem();
            $quoteItemId = $quoteItem->getItemId();

            $configureResult->setOk(true);

            $optionsCollection = $this->quoteItemOptionCollectionFactory->create();
            $options           = $optionsCollection
                ->addItemFilter([$quoteItemId])
                ->getOptionsByItem($quoteItem);
            $quoteItem->setOptions($options);

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            
        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        return $this->productCompositeHelper
            ->renderConfigureResult($configureResult);
    }

    /**
     * @return CartItemInterface
     * @throws LocalizedException
     */
    protected function getQuoteItem(): CartItemInterface
    {
        $orderItemId = $this->getRequest()->getParam('id');
        if (!$orderItemId) {
            throw new LocalizedException(__('Order item id is not received.'));
        }

        $prefixIdLength = strlen(Item::PREFIX_ID);
        if (substr($orderItemId, 0, $prefixIdLength) == Item::PREFIX_ID) {
            $quoteItemId = substr(
                $orderItemId,
                $prefixIdLength,
                strlen($orderItemId)
            );
            $orderId = $this->getRequest()->getParam('order_id');
            /** @var \MageWorx\OrderEditor\Model\Order $order */
            $order = $this->orderRepository->getById($orderId);
            $quoteId = $order->getQuoteId();
        } else {
            $orderItem   = $this->loadOrderItem($orderItemId);
            $quoteId     = (int)$orderItem->getOrder()->getQuoteId();
            $quoteItemId = (int)$orderItem->getQuoteItemId();
        }

        return $this->loadQuoteItem($quoteId, $quoteItemId);
    }

    /**
     * @param int $quoteId
     * @param int $quoteItemId
     * @return CartItemInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function loadQuoteItem(
        int $quoteId,
        int $quoteItemId
    ): CartItemInterface {
        /**
         * Need to prevent errors during loading quote items for old quote
         *
         * @see \Magento\Quote\Model\QuoteRepository::getActive()
         */
        $quote      = $this->quoteRepository->getById($quoteId);
        $quoteItems = [];

        /** @var  \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var QuoteItemOptionCollection $optionsCollection */
            $optionsCollection = $this->quoteItemOptionCollectionFactory->create();
            $options           = $optionsCollection->addItemFilter([$quoteItemId])
                                                   ->getOptionsByItem($item);
            $item->setOptions($options);
            $quoteItems[] = $item;
        }

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getItemId() == $quoteItemId) {
                return $quoteItem;
            }
        }

        $orderItemId = $this->getRequest()->getParam('id');
        if (!$orderItemId) {
            throw new LocalizedException(__('Order item id is not received.'));
        }
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->loadOrderItem($orderItemId);
        $quoteItem = $this->helper->initFromOrderItem($orderItem, $quote);
        if ($quoteItem) {
            if (!$quoteItem->getId()) {
                $this->cartItemRepository->save($quoteItem);
            }

            return $quoteItem;
        }

        throw new NoSuchEntityException(__('Quote item is not loaded.'));
    }

    /**
     * @param int $orderItemId
     * @return OrderItemInterface|\Magento\Sales\Model\Order\Item
     * @throws NoSuchEntityException
     */
    protected function loadOrderItem(int $orderItemId): OrderItemInterface
    {
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->orderItemRepository->get($orderItemId);

        if (!$orderItem->getId()) {
            throw new NoSuchEntityException(__('Order item is not loaded.'));
        }

        return $orderItem;
    }
}
