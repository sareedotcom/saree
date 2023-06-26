<?php
namespace Elsnertech\Customization\Observer;
   
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class AddHandle implements ObserverInterface
{
    
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
     protected $_storeManager;    
    // private $helper;
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        
    ) {
        $this->_storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
    }
    public function execute(Observer $observer)
    {
        $country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];
        $layout = $observer->getEvent()->getLayout();
        $storeList = $this->storeRepository->getList();
        $storeid = 7;
        foreach ($storeList as $store) {
            if (($country_code == "IN") && ($store->getCode() == "in")){
            $storeid = $store->getStoreId();
            }elseif(($country_code == "AU") && ($store->getCode() == "aud")){
                $storeid = $store->getStoreId();
            }elseif(($country_code == "GB") && ($store->getCode() == "gbp")){
                $storeid = $store->getStoreId();
            }elseif(($country_code == "CA") && ($store->getCode() == "cad")){
                $storeid = $store->getStoreId();
            }elseif(($country_code == "EU") && ($store->getCode() == "eur")){
                $storeid = $store->getStoreId();
            }elseif(($country_code == "SG") && ($store->getCode() == "sgd")){
                $storeid = $store->getStoreId();
            }elseif(($country_code == "US") && ($store->getCode() == "usd")){
                $storeid = $store->getStoreId();
            }elseif(($country_code == "AE") && ($store->getCode() == "aed")){
                $storeid = $store->getStoreId();
            }
            break;
        }
        $this->_storeManager->setCurrentStore($storeid);
    }
    
}