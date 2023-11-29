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

namespace Pits\GiftWrap\Model\ResourceModel;

use Exception;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Pits\GiftWrap\Api\Data\WrapInterface;
use Pits\GiftWrap\Api\WrapRepositoryInterface;
use Pits\GiftWrap\Model\ResourceModel\Wrap as WrapResource;
use Pits\GiftWrap\Model\ResourceModel\Wrap\CollectionFactory;
use Pits\GiftWrap\Model\WrapFactory;

/**
 * Class WrapRepository
 *
 * @package Pits\GiftWrap\Model\ResourceModel
 */
class WrapRepository implements WrapRepositoryInterface
{
    /**
     * @var WrapFactory
     */
    protected $wrapFactory;

    /**
     * @var WrapResource
     */
    protected $wrapResource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsInterfaceFactory;

    /**
     * WrapRepository constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Wrap $wrapResource
     * @param CollectionFactory $collectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsInterfaceFactory
     * @return void
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        WrapResource $wrapResource,
        CollectionFactory $collectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsInterfaceFactory
    ) {
        $this->wrapResource = $wrapResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsInterfaceFactory = $searchResultsInterfaceFactory;
    }

    /**
     * Get gift wrap data by id
     *
     * @param int $entityId
     * @return ExtensibleDataInterface|mixed|WrapInterface
     * @throws NoSuchEntityException
     */
    public function getById($entityId)
    {
        $this->searchCriteriaBuilder->addFilter(WrapInterface::GIFT_WRAP_ID_COLUMN, $entityId, 'eq');
        $giftWraps = $this->getGiftWrapCollection();
        if (empty($giftWraps)) {
            throw new NoSuchEntityException(__('ERROR: No gift wrap found.'));
        }

        return array_shift($giftWraps);
    }

    /**
     * Get purchase order collection
     *
     * @return array|ExtensibleDataInterface[]
     */
    public function getGiftWrapCollection()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $giftWraps = $this->getList($searchCriteria);

        return $giftWraps ? $giftWraps->getItems() : [];
    }

    /**
     * Get list of purchase orders based on filters
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, WrapInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Save gift wrap data
     *
     * @param WrapInterface $object
     * @return WrapInterface
     * @throws CouldNotSaveException
     */
    public function save(WrapInterface $object)
    {
        try {
            $this->wrapResource->save($object);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $object;
    }

    /**
     * Delete gift wrap data
     *
     * @param WrapInterface $object
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(WrapInterface $object)
    {
        try {
            $this->wrapResource->delete($object);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * Delete gift wrap data by id
     *
     * @param int $entityId
     * @return bool|WrapInterface
     * @throws CouldNotDeleteException
     */
    public function deleteById($entityId)
    {
        try {
            return $this->delete($this->getById($entityId));
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }
}
