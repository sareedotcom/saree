<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\Order;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface as SearchResultsInterface;
use MageWorx\OrderEditor\Model\Order\Item as Entity;
use MageWorx\OrderEditor\Api\Data\OrderItemSearchResultInterfaceFactory as SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use MageWorx\OrderEditor\Model\Order\ItemFactory as EntityFactory;
use MageWorx\OrderEditor\Api\OrderItemRepositoryInterface as RepositoryInterface;
use MageWorx\OrderEditor\Model\ResourceModel\Order\Item as EntityResource;
use MageWorx\OrderEditor\Model\ResourceModel\Order\Item\Collection as EntityCollection;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface as OriginalOrderItemRepositoryInterface;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class OrderItemRepository
 */
class OrderItemRepository implements RepositoryInterface, OriginalOrderItemRepositoryInterface
{
    /**
     * @var EntityResource
     */
    protected $resource;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    protected $productOptionExtensionFactory;

    /**
     * @var array
     */
    protected $processorPool;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * @param EntityResource $resource
     * @param EntityFactory $entityFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $productOptionExtensionFactory
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param array $processorPool
     */
    public function __construct(
        EntityResource $resource,
        EntityFactory $entityFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $productOptionExtensionFactory,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        array $processorPool = []
    ) {
        $this->resource                      = $resource;
        $this->entityFactory                 = $entityFactory;
        $this->searchResultsFactory          = $searchResultsFactory;
        $this->collectionProcessor           = $collectionProcessor;
        $this->productOptionFactory          = $productOptionFactory;
        $this->productOptionExtensionFactory = $productOptionExtensionFactory;
        $this->searchCriteriaBuilderFactory  = $searchCriteriaBuilderFactory;
        $this->processorPool                 = $processorPool;
    }

    /**
     * Save Entity data
     *
     * @param OrderItemInterface $entity
     * @return OrderItemInterface
     * @throws CouldNotSaveException
     */
    public function save(OrderItemInterface $entity): OrderItemInterface
    {
        try {
            /** @var Entity $entity */
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the entity: %1',
                    $exception->getMessage()
                )
            );
        }

        return $entity;
    }

    /**
     * Load Entity data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultsInterface
    {
        /** @var EntityCollection $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($criteria);
        $this->collectionProcessor->process($criteria, $searchResult);
        /** @var Entity $orderItem */
        foreach ($searchResult->getItems() as $orderItem) {
            $this->addProductOption($orderItem);
        }

        return $searchResult;
    }

    /**
     * Delete Entity by given Entity Identity
     *
     * @param string $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * Delete Entity
     *
     * @param OrderItemInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(OrderItemInterface $entity): bool
    {
        try {
            /** @var Entity $entity */
            $this->resource->delete($entity);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the entity: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * Load Entity data by given Entity Identity
     *
     * @param int $entityId
     * @return Entity
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getById(int $entityId): Entity
    {
        if (!$entityId) {
            throw new InputException(__('ID required'));
        }

        /** @var Entity $entity */
        $entity = $this->getEmptyEntity();
        $this->resource->load($entity, $entityId);
        if (!$entity->getItemId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        }

        $this->addProductOption($entity);
        $this->addParentItem($entity);
        $this->addChildItems($entity);

        return $entity;
    }

    /**
     * Get empty Entity
     *
     * @return Entity
     */
    public function getEmptyEntity(): Entity
    {
        /** @var Entity $entity */
        $entity = $this->entityFactory->create();

        return $entity;
    }

    /**
     * Loads a specified order item.
     *
     * @param int $id The order item ID.
     * @return Entity.
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function get($id)
    {
        return $this->getById($id);
    }

    /**
     * Add product option data
     *
     * @param OrderItemInterface $orderItem
     * @return $this
     */
    protected function addProductOption(OrderItemInterface $orderItem)
    {
        /** @var DataObject $request */
        $request = $orderItem->getBuyRequest();
        $productType = $orderItem->getProductType();
        if (isset($this->processorPool[$productType])
            && !$orderItem->getParentItemId()) {
            $data = $this->processorPool[$productType]->convertToProductOption($request);
            if ($data) {
                $this->setProductOption($orderItem, $data);
            }
        }
        if (isset($this->processorPool['custom_options'])
            && !$orderItem->getParentItemId()) {
            $data = $this->processorPool['custom_options']->convertToProductOption($request);
            if ($data) {
                $this->setProductOption($orderItem, $data);
            }
        }
        return $this;
    }

    /**
     * Set product options data
     *
     * @param OrderItemInterface $orderItem
     * @param array $data
     * @return $this
     */
    protected function setProductOption(OrderItemInterface $orderItem, array $data)
    {
        $productOption = $orderItem->getProductOption();
        if (!$productOption) {
            $productOption = $this->productOptionFactory->create();
            $orderItem->setProductOption($productOption);
        }
        $extensionAttributes = $productOption->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->productOptionExtensionFactory->create();
            $productOption->setExtensionAttributes($extensionAttributes);
        }
        $extensionAttributes->setData(key($data), current($data));
        return $this;
    }

    /**
     * Set parent item.
     *
     * @param OrderItemInterface $orderItem
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function addParentItem(OrderItemInterface $orderItem)
    {
        if ($parentId = $orderItem->getParentItemId()) {
            $orderItem->setParentItem($this->get($parentId));
        }
    }

    /**
     * @param OrderItemInterface $orderItem
     */
    private function addChildItems(OrderItemInterface $orderItem)
    {
        /** @var \MageWorx\OrderEditor\Model\Order\Item $orderItem */
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter('parent_item_id', $orderItem->getItemId());
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResult   = $this->getList($searchCriteria);

        $childItems = $searchResult->getItems();

        foreach ($childItems as $childItem) {
            $orderItem->addChildItem($childItem);
        }
    }

    /**
     * @inheritDoc
     */
    public function getByQuoteItemId(int $entityId): Entity
    {
        if (!$entityId) {
            throw new InputException(__('ID required'));
        }

        /** @var Entity $entity */
        $entity = $this->getEmptyEntity();
        $this->resource->load($entity, $entityId, 'quote_item_id');
        if (!$entity->getItemId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        }

        $this->addProductOption($entity);
        $this->addParentItem($entity);
        $this->addChildItems($entity);

        return $entity;
    }
}
