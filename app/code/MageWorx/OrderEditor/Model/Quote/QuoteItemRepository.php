<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\Quote;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;
use MageWorx\OrderEditor\Model\Quote\Item as Entity;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\OrderEditor\Model\Quote\ItemFactory as EntityFactory;
use MageWorx\OrderEditor\Api\QuoteItemRepositoryInterface as RepositoryInterface;
use MageWorx\OrderEditor\Model\ResourceModel\Quote\Item as EntityResource;
use MageWorx\OrderEditor\Model\ResourceModel\Quote\Item\CollectionFactory as EntityCollectionFactory;
use MageWorx\OrderEditor\Model\ResourceModel\Quote\Item\Collection as EntityCollection;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Class QuoteItemRepository
 */
class QuoteItemRepository implements RepositoryInterface
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
     * @var EntityCollectionFactory
     */
    protected $entityCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CartItemOptionsProcessor
     */
    protected $cartItemOptionsProcessor;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * QuoteItemRepository constructor.
     *
     * @param EntityResource $resource
     * @param ItemFactory $entityFactory
     * @param EntityCollectionFactory $entityCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CartItemOptionsProcessor $cartItemOptionsProcessor
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        EntityResource $resource,
        EntityFactory $entityFactory,
        EntityCollectionFactory $entityCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CartItemOptionsProcessor $cartItemOptionsProcessor,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->resource                     = $resource;
        $this->entityFactory                = $entityFactory;
        $this->entityCollectionFactory      = $entityCollectionFactory;
        $this->searchResultsFactory         = $searchResultsFactory;
        $this->dataObjectHelper             = $dataObjectHelper;
        $this->dataObjectProcessor          = $dataObjectProcessor;
        $this->storeManager                 = $storeManager;
        $this->cartItemOptionsProcessor     = $cartItemOptionsProcessor;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Save Entity data
     *
     * @param CartItemInterface $entity
     * @return Entity
     * @throws CouldNotSaveException
     */
    public function save(CartItemInterface $entity): Entity
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
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var EntityCollection $collection */
        $collection = $this->entityCollectionFactory->create();

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [
                        $condition => $filter->getValue()
                    ]
                );
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $entities = [];
        /** @var Entity|AbstractModel $entityModel */
        foreach ($collection as $item) {
            $item       = $this->cartItemOptionsProcessor->addProductOptions($item->getProductType(), $item);
            $entities[] = $this->cartItemOptionsProcessor->applyCustomOptions($item);
        }
        $searchResults->setItems($entities);

        return $searchResults;
    }

    /**
     * Delete Entity by given Entity Identity
     *
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * Delete Entity
     *
     * @param CartItemInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CartItemInterface $entity): bool
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
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): Entity
    {
        /** @var Entity $entity */
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $entityId);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Entity with id "%1" does not exist.', $entityId));
        }

        // Load child items if exists
        $entity = $this->loadChildItems($entity);

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
     * Load child items for item if exists
     *
     * @param Entity $item
     * @return Entity
     * @throws LocalizedException
     */
    public function loadChildItems(Entity $item): Entity
    {
        $entityId = $item->getId();
        if (!$entityId) {
            throw new LocalizedException(__('Item must have id before loading child items'));
        }

        // Add child items
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter('parent_item_id', $entityId);
        $searchCriteria = $searchCriteriaBuilder->create();
        $childItemsList = $this->getList($searchCriteria);
        if ($childItemsList->getTotalCount() > 0) {
            /** @var \MageWorx\OrderEditor\Model\Quote\Item $childItem */
            foreach ($childItemsList->getItems() as $childItem) {
                $item->addChild($childItem);
            }
        }

        return $item;
    }
}
