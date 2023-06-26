<?php
namespace Elsner\Multicurrency\Model;
 
class Multicurrency extends \Magento\Framework\Model\AbstractModel
{
    protected $_storeManager;
    
    protected $_countryFactory;
    
    protected $messageManager;
    
    const CACHE_TAG = 'elsner_multicurrency';
    
    protected $_cacheTag = 'elsner_multicurrency';
   
    protected $_eventPrefix = 'elsner_multicurrency';
    
    protected function _construct()
    {
        $this->_init('Elsner\Multicurrency\Model\ResourceModel\Multicurrency');
    }
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Directory\Model\CountryFactory $CountryFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->_storeManager = $store;
        $this->_countryFactory = $CountryFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context , $registry);
    }

    public function getRowByIncrementId($incrementId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('order_increment_id',$incrementId);
        $data = $collection->getFirstItem();
        return $data;
    }

    public function getRowByTransection($incrementId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('authorize_transaction_id',$incrementId);
        $data = $collection->getFirstItem();
        return $data;
    }

    public function addRow($incrementId,$currency,$discription)
    {
        $data = $this->getRowByIncrementId($incrementId)->getData();
        $setData = $this;
        $addArray = array('order_increment_id'=>$incrementId,
                          'paypal_currency_code'=>$currency,
                          'order_id'=>0,
                          'date_time'=>date('Y-m-d H:i:s'),
                          'discription'=>$discription);
        $setData->setData($addArray);
        if(empty($data) !== true){
            $setData->setId($data['multicurrency_id']);
        }
        $setData->save();
        return $setData;
    }
    
}