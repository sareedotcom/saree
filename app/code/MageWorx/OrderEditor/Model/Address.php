<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Model\Order\Address as OrderAddressModel;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteAddressRepositoryInterface;
use MageWorx\OrderEditor\Helper\Data as Herlper;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class Address
 */
class Address extends AbstractModel
{
    /**
     * @var $address OrderAddressInterface|OrderAddressModel
     */
    protected $address;

    /**
     * @var $oldAddress OrderAddressInterface|OrderAddressModel
     */
    protected $oldAddress;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var OrderAddressRepositoryInterface
     */
    protected $orderAddressRepository;

    /**
     * @var Herlper
     */
    private $helper;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var array
     */
    private $dataKeysToUpdate;

    /**
     * @var QuoteAddressRepositoryInterface
     */
    private $quoteAddressRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param RegionFactory $regionFactory
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param Herlper $helper
     * @param MessageManagerInterface $messageManager
     * @param QuoteAddressRepositoryInterface $quoteAddressRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param array $dataKeysToUpdate
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        RegionFactory $regionFactory,
        OrderAddressRepositoryInterface $orderAddressRepository,
        Herlper $helper,
        MessageManagerInterface $messageManager,
        QuoteAddressRepositoryInterface $quoteAddressRepository,
        OrderRepositoryInterface $orderRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        array $dataKeysToUpdate = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->regionFactory          = $regionFactory;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->helper                 = $helper;
        $this->messageManager         = $messageManager;
        $this->quoteAddressRepository = $quoteAddressRepository;
        $this->orderRepository        = $orderRepository;
        $this->dataKeysToUpdate       = $dataKeysToUpdate;
    }

    /**
     * @param string[] $addressData
     * @return void
     * @throws LocalizedException
     */
    public function updateAddress(array &$addressData)
    {
        if ($this->address === null) {
            throw new LocalizedException(__('Address must be loaded before.'));
        }

        $addressData      = $this->prepareRegion($addressData);
        $this->oldAddress = $this->address->getData();

        $this->address->addData($addressData);
        $this->orderAddressRepository->save($this->address);

        $this->syncQuoteAddress($this->address);

        $this->_eventManager->dispatch(
            'admin_sales_order_address_update',
            ['order_id' => $this->address->getParentId()]
        );
    }

    /**
     * @param OrderAddressInterface|OrderAddressModel $orderAddress
     */
    private function syncQuoteAddress(OrderAddressInterface $orderAddress)
    {
        /** @var int $quoteAddressId */
        $quoteAddressId = (int)$orderAddress->getQuoteAddressId();
        if (!$quoteAddressId) {
            return;
        }

        try {
            /** @var \Magento\Quote\Model\Quote\Address|QuoteAddressInterface $quoteAddress */
            $quoteAddress = $this->getQuoteAddressById($quoteAddressId);
        } catch (NoSuchEntityException $noSuchEntityException) {
            try {
                $mageworxOrder = $this->orderRepository->getById($orderAddress->getOrder()->getId());
                /** @var \Magento\Quote\Model\Quote $quote */
                $quote        = $this->helper->recreateEmptyQuote($mageworxOrder);
                $quoteAddress = $orderAddress->getAddressType() === 'shipping' ?
                    $quote->getShippingAddress() :
                    $quote->getBillingAddress();
                $orderAddress->setQuoteAddressId($quoteAddress->getId());
                $this->orderAddressRepository->save($orderAddress);
            } catch (LocalizedException $localizedException) {
                $this->_logger->critical($localizedException);
                $this->messageManager->addErrorMessage($localizedException);

                return;
            }
        }

        foreach ($this->dataKeysToUpdate as $key) {
            $value = $orderAddress->getData($key);
            if ($value !== null) {
                $quoteAddress->setData($key, $value);
            }
        }

        try {
            $this->saveQuoteAddress($quoteAddress);
        } catch (\Exception $exception) {
            $this->_logger->critical($exception);
            $this->messageManager->addErrorMessage($exception);
        } finally {
            return;
        }
    }

    /**
     * @param QuoteAddressInterface $address
     * @throws LocalizedException
     */
    private function saveQuoteAddress(QuoteAddressInterface $address)
    {
        $this->quoteAddressRepository->save($address);
    }

    /**
     * @param int $id
     * @return QuoteAddressInterface
     * @throws NoSuchEntityException
     */
    private function getQuoteAddressById(int $id): QuoteAddressInterface
    {
        return $this->quoteAddressRepository->getById($id);
    }

    /**
     * @param string[] &$addressData
     * @return string[]
     */
    protected function prepareRegion(array &$addressData): array
    {
        if (!empty($addressData['region_id'])
            && empty($addressData['region'])
        ) {
            $addressData['region'] = $this->regionFactory->create()
                                                         ->load($addressData['region_id'])
                                                         ->getName();
        }

        return $addressData;
    }

    /**
     * @param int $addressId
     * @return OrderAddressInterface|OrderAddressModel
     */
    public function loadAddress(int $addressId): OrderAddressInterface
    {
        /**
         * @var OrderAddressInterface|OrderAddressModel $address
         */
        $this->address = $this->orderAddressRepository->get($addressId);

        return $this->address;
    }
}
