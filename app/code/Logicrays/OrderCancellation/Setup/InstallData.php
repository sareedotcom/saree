<?php
namespace Logicrays\OrderCancellation\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallData implements InstallDataInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $data[] = ['status' => 'request_for_cancellation', 'label' => 'Request For Cancellation'];
        $setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

        $setup->getConnection()->insertArray(
        $setup->getTable('sales_order_status_state'),
        ['status', 'state', 'is_default','visible_on_front'],
        [
            ['request_for_cancellation','processing', '0', '1'],
            ['request_for_cancellation', 'new', '0', '1'],
            ['request_for_cancellation', 'complete', '0', '1'],
            ['request_for_cancellation', 'pending_payment', '0', '1']
        ]
        );

        $setup->endSetup();
    }
}