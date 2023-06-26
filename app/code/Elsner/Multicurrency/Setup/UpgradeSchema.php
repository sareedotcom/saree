<?php
namespace Elsner\Multicurrency\Setup;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements UpgradeSchemaInterface
{
   
    private $pageFactory;
    
    private $blockFactory;
   
    public function __construct(
        PageFactory $pageFactory, 
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\App\State $state
    )
    {
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $state->setAreaCode('frontend');
    }
    
    public function upgrade(SchemaSetupInterface $setup,
        ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1','<')) {
            $installer = $setup;
            $installer->startSetup();
            $table = $installer->getConnection()->newTable(
            $installer->getTable('elsner_multicurrency')
            )
            ->addColumn(
                'multicurrency_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'multicurrency_id'
            )
            ->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'order_increment_id'
            )

           ->addColumn(
                'paypal_currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'paypal_currency_code'
            )

            ->addColumn(
                'authorize_transaction_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'authorize_transaction_id'
            )

            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'order_id'
            )
            
            ->addColumn(
                'date_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                '10,2',
                ['nullable' => true],
                'date_time'
            )

            ->addColumn(
                'discription',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'discription'
            )
            
            ->setComment(
                'Elsner Multicurrency'
            );
            
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
