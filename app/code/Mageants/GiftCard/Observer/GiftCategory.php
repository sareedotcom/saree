<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Registry;
use \Mageants\GiftCard\ViewModel\ViewHelperData;
use Magento\Catalog\Helper\Output;
use Mageants\GiftCard\Helper\Data;

/**
 * RemoveBlock Observer before render block
 */
class GiftCategory implements ObserverInterface
{
    /**
     * @var Mageants\GiftCard\Helper\Data
     */
    public $data;
    /**
     * @var ViewHelperData
     */
    public $helpdata;

    /**
     * @var Output
     */
    public $output;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $registry;

    /**
     * Construct
     *
     * @param Registry       $registry
     * @param Output         $output
     * @param ViewHelperData $helpdata
     * @param Data           $data
     */
    public function __construct(
        Registry $registry,
        Output $output,
        ViewHelperData $helpdata,
        Data $data
    ) {
        $this->registry = $registry;
        $this->output = $output;
        $this->helpdata = $helpdata;
        $this->data = $data;
    }

    /**
     * To Set Gift category
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $category = $this->registry->registry('current_category');

        if ($category) {
            if ($category->getName()=='Gift Card') {
                $this->data->reIndexing();
                $layout = $observer->getLayout();
                /**
                 * For Product list page
                 */
                $blocklist = $layout->getBlock('category.products.list');
                if ($blocklist) {
                    $blocklist->setTemplate('Mageants_GiftCard::product/list.phtml');
                }
            }
        }
    }
}
