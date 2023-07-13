<?php

namespace MagicToolbox\MagicZoomPlus\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Data upgrades
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Config table name
     */
    const MAGICZOOMPLUS_CONFIG_TABLE = 'magiczoomplus_config';

    /**
     * Upgrades data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        //NOTE: 'data_version' from `setup_module` table
        $dataVersion = $context->getVersion();

        if (empty($dataVersion)) {
            //NOTE: skip upgrade when install
            return;
        }

        $setup->startSetup();

        if ($setup->tableExists(self::MAGICZOOMPLUS_CONFIG_TABLE)) {
            /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection */
            $connection = $setup->getConnection();

            $tableName = $setup->getTable(self::MAGICZOOMPLUS_CONFIG_TABLE);

            if (version_compare($dataVersion, '1.0.1') < 0) {
                $bind = ['value' => 'Yes'];
                $where = [
                    'name = ?' => 'rightClick',
                    'status = ?' => 2
                ];
                $connection->update($tableName, $bind, $where);
            }
        }

        $setup->endSetup();
    }
}
