<?php

namespace Mageants\GiftCard\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryFactory;

class AddAttribute implements DataPatchInterface
{
    /**
     * @var CategoryFactory
     */
    public $categoryFactory;

    /**
     * @var StoreManagerInterface
     */
    public $storeManagerInterface;

    /**
     * @var Category
     */
    public $modelCategory;

   /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

   /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param Category $modelCategory
     * @param StoreManagerInterface $storeManagerInterface
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        Category $modelCategory,
        StoreManagerInterface $storeManagerInterface,
        CategoryFactory $categoryFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->modelCategory     = $modelCategory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->categoryFactory = $categoryFactory;
    }

   /**
    * @inheritdoc
    */
    public function apply()
    {
       /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'gifttype',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 50,
                'label' => 'Gift Type',
                'input' => 'select',
                'class' => 'required',
                'source' => \Mageants\GiftCard\Model\Config\Source\Gifttype::class,
                'global' =>  \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=>'giftcertificate'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'giftcerticodeset',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 60,
                'label' => 'Select Gift Card Code Set',
                'input' => 'select',
                'class' => 'required',
                'source' => \Mageants\GiftCard\Model\Config\Source\Codeset::class,
                'global' =>  \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => false,
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'searchable' => true,
                'filterable' => true,
                'required' => true,
                'comparable' => true,
                'default' => '',
                'apply_to'=>'giftcertificate'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'minprice',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'frontend_class' => 'validate-number',
                'label' => 'Set Minimum Price of GiftCard',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'maxprice',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'frontend_class' => 'validate-number',
                'label' => 'Set Maximum Price of GiftCard',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'validity',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'frontend_class' => 'validate-number',
                'label' => 'Gift Card Validity',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'giftcard_price_dropdown',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'frontend_class' => 'validate-number',
                'label' => 'Gift Card Dropdown Price',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'allowmessage',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Allow Message',
                'input' => 'select',
                'class' => '',
                'source' => \Mageants\GiftCard\Model\Config\Source\Message::class,
                'global' => true,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );
        
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'giftimages',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'text',
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'frontend' => '',
                'label' => 'Choose gift Card images ',
                'input' => 'multiselect',
                'class' => '',
                'source' => \Mageants\GiftCard\Model\Config\Source\Giftimages::class,
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'allowcategory',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'text',
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'frontend' => '',
                'label' => 'Choose Categories (To apply this certificate)',
                'input' => 'multiselect',
                'class' => '',
                'source' => \Mageants\GiftCard\Model\Config\Source\Categories::class,
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'minprice');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'minprice',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'frontend_class' => 'validate-number',
                'label' => 'Set Minimum Price of GiftCard',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'maxprice');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'maxprice',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'frontend_class' => 'validate-number',
                'label' => 'Set Maximum Price of GiftCard',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'validity');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'validity',
            [
                'group' => 'Gift Card Informtion',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'frontend_class' => 'validate-number',
                'label' => 'Gift Card Validity',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => true,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to'=>'giftcertificate'
            ]
        );
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);
        /**
         * Remove previous attributes
         */
        $attributes =['order_gift'];
        foreach ($attributes as $attr_to_remove) {
            $salesSetup->removeAttribute(\Magento\Sales\Model\Order::ENTITY, $attr_to_remove);
        }

        /**
         * Add 'NEW_ATTRIBUTE' attributes for order
         */
        // $options = ['type' => 'decimal', 'length'=> '10,4', 'visible' => false, 'required' => false];
        // $salesSetup->addAttribute('order', 'order_gift', $options);
        
        $fieldList = [
           'price',
          'tier_price',
          'cost',
          'weight',
        ];
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array('giftcertificate', $applyTo)) {
                $applyTo[] = 'giftcertificate';
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        $storeManager = $this->storeManagerInterface;
        $websiteId = $storeManager->getWebsite()->getWebsiteId();
        $store = $storeManager->getStore();
        $storeId = $store->getStoreId();
        $rootNodeId = $store->getRootCategoryId();
        $rootCat = $this->modelCategory;
        try {
            $cat_info = $rootCat->load($rootNodeId);
            $categoryfactory = $this->categoryFactory;
            $category = $this->modelCategory;
            $cate=$category->getCollection()->addAttributeToFilter('url_key', 'giftcard')->getFirstItem();
            
            if (!$cate->getId()) {
                $categoryTmp = $categoryfactory->create();
                $categoryTmp->setName('Gift Card');
                $categoryTmp->setIsActive(false);
                $categoryTmp->setUrlKey('giftcard');
                $categoryTmp->setData('description', 'description');
                $categoryTmp->setParentId($rootNodeId);
                $categoryTmp->setStoreId($storeId);
                $categoryTmp->setPath($rootCat->getPath());
                $categoryTmp->save();
            }
        } catch (Exception $e) {
            ?> <?= __('Category exist already'); ?><?php
        }
    }
    
   /**
    * @inheritdoc
    */
    public static function getDependencies()
    {
        return [];
    }

   /**
    * @inheritdoc
    */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    // public static function getVersion()
    // {
    //     return '2.0.8';
    // }
}
