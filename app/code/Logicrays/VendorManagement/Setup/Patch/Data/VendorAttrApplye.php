<?php
namespace Logicrays\VendorManagement\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class VendorAttrApplye implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
      }
      public function apply()
      {   
          $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
          $eavSetup->updateAttribute(
          Product::ENTITY,
          'vendor',
            [
                'apply_to' => 'simple,grouped,bundle,configurable,virtual,giftcertificate',
            ]
          );
      }
      public static function getDependencies()
      {
          return [];
      }
      public function getAliases()
      {
           return [];
      }     
}