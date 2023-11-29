<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Cart;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Pricing\Helper\Data;
use \Magento\Catalog\Model\Category;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\GiftCard\Model\Account;
use \Mageants\GiftCard\Model\Codelist;
use \Magento\Sales\Model\OrderFactory;

/**
 * Check gift code Details
 */
class Check extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderFactory
     */
    public $orderFactory;

    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_categories;
    /**
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $modelAccount;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Mageants\GiftCard\Model\Codelist
     */
    protected $_codelist;

    /**
     * @param Context $context
     * @param Data $helper
     * @param Category $categories
     * @param JsonFactory $resultJsonFactory
     * @param Account $modelAccount
     * @param Codelist $_codelist
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        Category $categories,
        JsonFactory $resultJsonFactory,
        Account $modelAccount,
        Codelist $_codelist,
        OrderFactory $orderFactory
    ) {
        $this->_helper=$helper;
        $this->modelAccount=$modelAccount;
        $this->_categories = $categories;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_codelist = $_codelist;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     *  Chek gift code and return detail of the code
     */
    public function execute() // @codingStandardsIgnoreLine
    {
        $result_return = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            $availableCode=$this->_codelist->getCollection()
            ->addFieldToFilter('code', trim($data['gift_code']))
            ->addFieldToFilter('allocate', '1');
        
            if (empty($availableCode->getData())):

                $error = "<span style='color:#f00'>Invalid Gift Card</span>";
                return $result_return->setData($error);

            else:
                $account = $this->modelAccount->getCollection()->addFieldToFilter(
                    'gift_code',
                    trim($data['gift_code'])
                )->addFieldToFilter('status', 1);
                if (!empty($account->getData())):
                    $html='';
                    foreach ($account as $certifiate) {
                        $orderIncrementId = $certifiate['order_increment_id'];
                       
                        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
                        if ($order->getStatus() == "canceled" || $order->getStatus() == "closed") {
                            $error= "<span style='color:#f00'>Invalid Gift Card Code</span>";
                            return $result_return->setData($error);
                        }
                        if ($certifiate->getExpireAt()!='1970-01-01' && $certifiate->getExpireAt()!='0000-00-00'):
                            $currentDate= date('Y-m-d');
                            if ($currentDate > $certifiate->getExpireAt()):
                                $error = "<span style='color:#f00'>Sorry, This Gift Card Has Been Expired</span>";
                                return $result_return->setData($error);
                            endif;
                        endif;
                        $category_ids=explode(",", $certifiate->getCategories());
                        $category_name='';
                        foreach ($category_ids as $id) {
                            $cat=$this->_categories->load($id);
                            $category_name=$category_name.",".$cat->getName();
                            $category_name=substr($category_name, 1);
                        }
                       
                        if ($certifiate->getDiscountType() == 'percent') {
                            if ($certifiate->getCurrentBalance()==0):
                                $type='used';

                            else:
                                $type='Avalaiable';

                            endif;
                            $html.="<div><span>Status:
                        </span><span style='font-weight:bold'>"
                            .$type."</span></div><div><span>
                        Available Discount :</span><span style='font-weight:bold'>"
                            .$certifiate->getCurrentBalance()."</span> </div>";
                            return $result_return->setData($html);
                        } else {
                            if ($certifiate->getCurrentBalance()==0):
                                $type='used';

                            else:
                                $type='Avalaiable';

                            endif;
                            $html.="<div><span>Status:
                        </span><span style='font-weight:bold'>"
                            .$type."</span></div><div><span>
                        Current Balance:</span><span style='font-weight:bold'>".
                            $this->_helper->currency($certifiate->getCurrentBalance(), true, false)."</span> </div>";
                            return $result_return->setData($html);
                        }
                    }
                    return;
                else:
                    $error = "<span style='color:#f00'>Invalid Gift Card</span>";
                    return $result_return->setData($error);
                endif;
            endif;
        }
    }
}
