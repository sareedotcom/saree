<?php

namespace Elsner\Discount\Block\Index;


class Index extends \Magento\Framework\View\Element\Template 
{
	protected $collectionFactory;
	protected $request;
	protected $_productloader;  
	protected $_eavAttribute;
	protected $_registry;
    public function __construct(
    	\Magento\Catalog\Block\Product\Context $context, 
    	\Magento\Framework\App\Request\Http $request,
    	\Magento\Catalog\Model\ProductFactory $_productloader,
    	\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
    	\Magento\Framework\Registry $registry,
    	\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
    	 array $data = []) 
    {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->_productloader = $_productloader;
        $this->collectionFactory = $collectionFactory;
        $this->_registry = $registry;
        $this->_eavAttribute = $eavAttribute;
    }


    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    public function getCollection()
	{
	$data = $this->request->getParams();
	$startPrOID = $data['start'];
	$EndPrOID = $data['end'];
	$now = date('Y-m-d H:i:s');
	 if(!empty($startPrOID) && !empty($EndPrOID)){
		$productCollection = $this->collectionFactory->create();
	    $productCollection->addAttributeToSelect('*');
	   $productCollection->addAttributeToSelect('special_from_date');
            $productCollection->addAttributeToSelect('special_to_date');
            $productCollection->addAttributeToFilter('special_price', ['neq' => '']);
	    $productCollection->addAttributeToFilter('entity_id', array(
	    'from' => $startPrOID,
	    'to' => $EndPrOID
	    ));
	    return $productCollection;
	   }
	   return false;
	}
	public function getProduct($id){
	    return $this->_productloader->create()->load($id);
	}
	
	public function getAttributeId(){
		return $this->_eavAttribute->getIdByCode('catalog_product', 'mj_discount');
	}

	public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    }  


}