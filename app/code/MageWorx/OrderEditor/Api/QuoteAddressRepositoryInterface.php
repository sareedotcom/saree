<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface as Entity;

/**
 * Interface QuoteRepositoryInterface
 *
 * @package MageWorx\OrderEditor\Api
 */
interface QuoteAddressRepositoryInterface
{
    const ENTITY_DATA_INTERFACE = Entity::class;

    /**
     * Save entity.
     *
     * @param Entity $entity
     * @return Entity
     * @throws LocalizedException
     */
    public function save(Entity $entity): Entity;

    /**
     * Retrieve entity.
     *
     * @param int $entityId
     * @return Entity
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): Entity;

    /**
     * Delete entity.
     *
     * @param Entity $entity
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(Entity $entity): bool;

    /**
     * Delete entity by ID.
     *
     * @param int $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $entityId): bool;

    /**
     * Get empty Entity
     *
     * @return Entity
     */
    public function getEmptyEntity(): Entity;
}
