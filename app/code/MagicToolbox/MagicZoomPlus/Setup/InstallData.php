<?php

namespace MagicToolbox\MagicZoomPlus\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

/**
 * Data installs
 *
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Config table name
     */
    const MAGICZOOMPLUS_CONFIG_TABLE = 'magiczoomplus_config';

    /**
     * Module configuration file reader
     *
     * @var ModuleDirReader
     */
    protected $moduleDirReader;

    /**
     * Constructor
     *
     * @param ModuleDirReader $modulesReader
     * @return void
     */
    public function __construct(
        ModuleDirReader $modulesReader
    ) {
        $this->moduleDirReader = $modulesReader;
    }

    /**
     * Installs data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($setup->tableExists(self::MAGICZOOMPLUS_CONFIG_TABLE)) {
            $moduleEtcPath = $this->moduleDirReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                'MagicToolbox_MagicZoomPlus'
            );

            $useErrors = libxml_use_internal_errors(true);
            $xml = simplexml_load_file($moduleEtcPath . '/defaults.xml');
            libxml_use_internal_errors($useErrors);

            $data = [];
            if ($xml) {
                $params = $xml->xpath('/defaults/param');
                foreach ($params as $param) {
                    $data[] = [
                        'platform' => (int)$param['platform'],
                        'profile' => (string)$param['profile'],
                        'name' => (string)$param['name'],
                        'value' => (string)$param['value'],
                        'status' => (int)$param['status']
                    ];
                }
                unset($xml);
            }

            if (!empty($data)) {
                /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection */
                $connection = $setup->getConnection();

                $tableName = $setup->getTable(self::MAGICZOOMPLUS_CONFIG_TABLE);

                //NOTE: make sure that the table is empty before inserting new data
                $connection->truncateTable($tableName);

                $connection->insertMultiple($tableName, $data);
            }
        }

        $setup->endSetup();
    }
}
