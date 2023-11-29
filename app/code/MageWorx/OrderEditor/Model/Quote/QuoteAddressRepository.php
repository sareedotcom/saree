<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\Quote;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\AddressInterface as Entity;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\AddressFactory as EntityFactory;
use MageWorx\OrderEditor\Api\QuoteAddressRepositoryInterface as RepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address as EntityResource;

/**
 * Class QuoteAddressRepository
 */
class QuoteAddressRepository implements RepositoryInterface
{
    /**
     * @var \MageWorx\OrderEditor\Model\ResourceModel\Order\Item
     */
    protected $resource;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @param EntityResource $resource
     * @param EntityFactory $entityFactory
     */
    public function __construct(
        EntityResource $resource,
        EntityFactory $entityFactory
    ) {
        $this->resource                      = $resource;
        $this->entityFactory                 = $entityFactory;
    }

    /**
     * Save Entity data
     *
     * @param Entity $entity
     * @return Entity
     * @throws CouldNotSaveException
     */
    public function save(Entity $entity): Entity
    {
        try {
            /** @var \MageWorx\OrderEditor\Model\Order\Item $entity */
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
     * Delete Entity by given Entity Identity
     *
     * @param int $entityId
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
     * @param Entity $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Entity $entity): bool
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
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        }

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
}
