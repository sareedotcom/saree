<?php
/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 * This source file is licenced under Webshop Extensions software license.
 * Once you have purchased the software with PIT Solutions AG or one of its
 * authorised resellers and provided that you comply with the conditions of this contract,
 * PIT Solutions AG grants you a non-exclusive license, unlimited in time for the usage of
 * the software in the manner of and for the purposes specified in the documentation according
 * to the subsequent regulations.
 *
 * @category Pits
 * @package  Pits_GiftWrap
 * @author   Pit Solutions Pvt. Ltd.
 * @copyright Copyright (c) 2021 PIT Solutions AG. (www.pitsolutions.ch)
 * @license https://www.webshopextension.com/en/licence-agreement/
 */

namespace Pits\GiftWrap\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Pits\GiftWrap\Api\Data\WrapInterface;

/**
 * Interface WrapRepositoryInterface
 *
 * @api
 * @package Pits\GiftWrap\Api
 */
interface WrapRepositoryInterface
{
    /**
     * Create or update a gift wrap in table.
     *
     * @param WrapInterface $wrap
     * @return WrapInterface
     */
    public function save(WrapInterface $wrap);

    /**
     * Get a gift wrap by Id
     *
     * @param int $entityId
     * @return WrapInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getById($entityId);

    /**
     * Retrieve gift warps matching a specified criteria.
     *
     * @param SearchCriteriaInterface $criteria
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete a git wrap
     *
     * @param WrapInterface $wrap
     * @return WrapInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function delete(WrapInterface $wrap);

    /**
     * Delete by Id
     *
     * @param int $entityId
     * @return WrapInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($entityId);
}
