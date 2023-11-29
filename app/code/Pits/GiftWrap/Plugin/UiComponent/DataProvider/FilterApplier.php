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

namespace Pits\GiftWrap\Plugin\UiComponent\DataProvider;

use Exception;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Psr\Log\LoggerInterface;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class FilterApplier
 *
 * @package Pits\GiftWrap\Plugin\UiComponent\DataProvider
 */
class FilterApplier
{
    /**
     * Name space of the sales order grid that passes to the mui render call.
     */
    const SALES_ORDER_GRID_NAMESPACE = 'sales_order_grid';

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * FilterApplier constructor.
     *
     * @param Http $request
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(
        Http $request,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * This plugin will be executed before a filter is applied to the collection.
     * So each time a filter is applied in the sales order grid, we will check and if it is an ID filter
     * then we will change the condition of that filter from 'like' to 'in'. Also in the value there will be
     * %% symbols appended in the beginning and end of the value, we will remove that also.
     *
     * @param FilterApplierInterface $subject
     * @param Collection $collection
     * @param Filter $filter
     * @return array
     */
    public function beforeApply(FilterApplierInterface $subject, Collection $collection, Filter $filter): array
    {
        try {
            // Get the namespace parameter from the request.
            // When we filter the grid, the request url will be mui/index/render.
            $namespace = $this->request->getParam('namespace');
            if ($namespace == self::SALES_ORDER_GRID_NAMESPACE) {
                if ($filter->getField() == Wrap::GIFT_WRAP_FIELD_NAME) {
                    $filter->setValue($filter->getValue() . '%');
                    $filter->setConditionType('like');
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return [$collection, $filter];
    }
}
