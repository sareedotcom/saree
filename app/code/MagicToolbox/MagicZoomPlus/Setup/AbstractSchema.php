<?php

namespace MagicToolbox\MagicZoomPlus\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * DB schema installs/upgrades
 *
 * @codeCoverageIgnore
 */
abstract class AbstractSchema
{
    /**
     * Config table name
     */
    const MAGICZOOMPLUS_CONFIG_TABLE = 'magiczoomplus_config';

    /**
     * Create config table
     *
     * @param SchemaSetupInterface $setup
     * @param bool $skipIfExists
     * @return void
     */
    protected function createConfigTable(SchemaSetupInterface $setup, $skipIfExists = true)
    {
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection */
        $connection = $setup->getConnection();

        $tableName = $setup->getTable(self::MAGICZOOMPLUS_CONFIG_TABLE);

        if ($setup->tableExists(self::MAGICZOOMPLUS_CONFIG_TABLE)) {
            if ($skipIfExists) {
                return;
            }
            $connection->dropTable($tableName);
        }

        $table = $connection->newTable(
            $tableName
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'platform',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => '0'],
            'Platform'
        )->addColumn(
            'profile',
            Table::TYPE_TEXT,
            64,
            ['nullable'  => false],
            'Profile'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            64,
            ['nullable'  => false],
            'Name'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            null,
            ['nullable'  => false],
            'Value'
        )->addColumn(
            'status',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => '0'],
            'Status'
        )->setComment(
            'Magic Zoom Plus configuration'
        );

        $connection->createTable($table);
    }
}
