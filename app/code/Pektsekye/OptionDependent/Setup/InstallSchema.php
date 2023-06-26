<?php

namespace Pektsekye\OptionDependent\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        

        $installer->getConnection()->dropTable($installer->getTable('optiondependent_option'));
        $table = $installer->getConnection()
            ->newTable($installer->getTable('optiondependent_option'))
            ->addColumn('od_option_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,    
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true
                ), 'OptionDependent Option Id')
            ->addColumn('option_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false
                ), 'Option Id')      
            ->addColumn('product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false
                ), 'Product Id')  
            ->addColumn('row_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => true,
                'default'   => null        
                ), 'Row Id')                   
            ->addIndex($installer->getIdxName('optiondependent_option', array('option_id'), true),
                array('option_id'), array('type' => 'unique'))        
            ->addIndex($installer->getIdxName('optiondependent_option', array('product_id')), array('product_id'))
            ->addForeignKey(
                $installer->getFkName('optiondependent_option', 'option_id', 'catalog_product_option', 'option_id'),
                'option_id', $installer->getTable('catalog_product_option'), 'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)       
            ->setComment('OptionDependent Option');
        $installer->getConnection()->createTable($table);


        $installer->getConnection()->dropTable($installer->getTable('optiondependent_value'));
        $table = $installer->getConnection()
            ->newTable($installer->getTable('optiondependent_value'))
            ->addColumn('od_value_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,    
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true
                ), 'OptionDependent Value Id')
            ->addColumn('option_type_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false
                ), 'Option Type Id')      
            ->addColumn('product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false
                ), 'Product Id')  
            ->addColumn('row_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => true,
                'default'   => null        
                ), 'Row Id')  
            ->addColumn('children', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', array(
                'nullable'  => false,
                'default'   => ''        
                ), 'Children')                          
            ->addIndex($installer->getIdxName('optiondependent_value', array('option_type_id'), true),
                array('option_type_id'), array('type' => 'unique'))        
            ->addIndex($installer->getIdxName('optiondependent_value', array('product_id')), array('product_id'))
            ->addForeignKey(
                $installer->getFkName('optiondependent_value', 'option_type_id', 'catalog_product_option_type_value', 'option_type_id'),
                'option_type_id', $installer->getTable('catalog_product_option_type_value'), 'option_type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)       
            ->setComment('OptionDependent Value');
        $installer->getConnection()->createTable($table);
   
   
        $setup->endSetup();

    }
}
