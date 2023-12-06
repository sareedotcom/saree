<?php
namespace MageWorx\ExtendedOrdersGrid\Plugin;

/**
 * Class AddDataToOrdersGrid
 */
class AddDataToOrdersGrid
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AddDataToOrdersGrid constructor.
     *
     * @param \Psr\Log\LoggerInterface $customLogger
     * @param array $data
     */
    public function __construct(
        array $data = []
    ) {
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $collection
     * @param $requestName
     * @return mixed
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'lrdes_order_listing_data_source') {
            return $collection;
        }

        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            try {
                $orderAddressTableName = $collection->getResource()->getTable('sales_order_address');
                $directoryCountryRegionTableName = $collection->getResource()->getTable('directory_country_region');
                $collection->getSelect()->joinLeft(
                    ['soa' => $orderAddressTableName],
                    'soa.parent_id = main_table.entity_id AND soa.address_type = \'shipping\'',
                    ['country_id','telephone']
                );

                $collection->getSelect()->joinLeft(
                    ['so' => 'sales_order'],
                    'so.increment_id = main_table.increment_id',
                    ['so.entity_id']
                );

                // $collection->getSelect()->joinLeft(
                //     ['soi' => 'sales_order_item'],
                //     'soi.order_id = so.entity_id',
                //     ['min_date' => new \Zend_Db_Expr('MIN(STR_TO_DATE(soi.estd_dispatch_date, "%W, %d %M %Y"))')]
                // );
                // $collection->getSelect()->group('soi.order_id');
                // $collection->setOrder('min_date','ASC');

            } catch (\Zend_Db_Select_Exception $selectException) {

            }
        }

        return $collection;
    }
}