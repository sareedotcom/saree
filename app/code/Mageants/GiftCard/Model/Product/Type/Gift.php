<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\Product\Type;

use \Magento\Catalog\Model\Product;
use \Mageants\GiftCard\Model\Giftquote;
use \Mageants\GiftCard\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Catalog\Model\Product\Option;
use \Magento\Eav\Model\Config;
use \Magento\Catalog\Model\Product\Type;
use \Magento\Framework\Event\ManagerInterface;
use \Magento\MediaStorage\Helper\File\Storage\Database;
use \Magento\Framework\Filesystem;
use \Magento\Framework\Registry;
use \Psr\Log\LoggerInterface;

/**
 * Gift class for create Gift Type product
 */
class Gift extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * @var Product
     */
    protected $giftproduct;

    /**
     * @var Giftquote
     */
    protected $giftquote;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Product $giftproduct
     * @param Giftquote $giftquote
     * @param Data $data
     * @param Option $catalogProductOption
     * @param Config $eavConfig
     * @param Type $catalogProductType
     * @param ManagerInterface $eventManager
     * @param Database $fileStorageDb
     * @param Filesystem $filesystem
     * @param Registry $coreRegistry
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     */

    public function __construct(
        Product $giftproduct,
        Giftquote $giftquote,
        Data $data,
        Option $catalogProductOption,
        Config $eavConfig,
        Type $catalogProductType,
        ManagerInterface $eventManager,
        Database $fileStorageDb,
        Filesystem $filesystem,
        Registry $coreRegistry,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository
    ) {
        $this->giftproduct = $giftproduct;
        $this->giftquote = $giftquote;
        $this->data = $data;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
    }
    /**
     * To set Giftcard Product
     *
     * @param object $product
     */
    public function isVirtual($product)
    {
        return false;
        $gifttype = $this->giftproduct->load($product->getId());

        $giftquote = $this->giftquote;

        $customer_id = $this->data->getCustomerId();
        if ($customer_id != null) {
            $gift_data = $giftquote->getCollection()->addFieldToFilter('customer_id', $customer_id)->getData();
        } else {
            $gift_data = $giftquote->getCollection()->addFieldToFilter('customer_id', 0)->getData();
        }
        $giftdata_cardtype = 'test';
        if (!empty($product->getId())) {
            foreach ($gift_data as $key => $value) {
                if ($value['product_id'] == $product->getId()) {
                    $giftdata_cardtype = $value['card_types'];
                }
            }
        }

        /* Checked Gift Type is Virtual */
        if ($gifttype->getGifttype() == 0) {
            return true;
        } elseif (isset($giftdata_cardtype)) {
            if ($giftdata_cardtype == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return true;
    }
    
    /**
     * Delete specific type of data
     *
     * @param Product $product
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product) // @codingStandardsIgnoreLine
    {
      /*Delete data for specific type @product*/
    }
}
