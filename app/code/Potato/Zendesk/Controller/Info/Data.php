<?php

namespace Potato\Zendesk\Controller\Info;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Potato\Zendesk\Api\CustomerManagementInterface;
use Potato\Zendesk\Api\OrderRecentManagementInterface;
use Potato\Zendesk\Model\Authorization;
use Psr\Log\LoggerInterface;
use Magento\Framework\Oauth\Helper\Request;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Potato\Zendesk\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey;

class Data extends Action
{
    /** @var Authorization  */
    protected $authorization;

    /** @var CustomerManagementInterface  */
    protected $customerManagement;

    /** @var OrderRecentManagementInterface  */
    protected $orderRecentManagement;

    /** @var null  */
    private $postData = null;

    /** @var LoggerInterface  */
    protected $logger;

    /** @var Config  */
    protected $config;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param Authorization $authorization
     * @param CustomerManagementInterface $customerManagement
     * @param OrderRecentManagementInterface $orderRecentManagement
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param FormKey $formKey
     */
    public function __construct(
        Context $context,
        Authorization $authorization,
        CustomerManagementInterface $customerManagement,
        OrderRecentManagementInterface $orderRecentManagement,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        Config $config,
        FormKey $formKey
    ) {
        parent::__construct($context);
        $this->_request->setParam('form_key', $formKey->getFormKey());
        $this->authorization = $authorization;
        $this->customerManagement = $customerManagement;
        $this->orderRecentManagement = $orderRecentManagement;
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed|null
     */
    private function getPostData()
    {
        if (null !== $this->postData) {
            return $this->postData;
        }
        $this->postData = file_get_contents('php://input');
        if (false === $this->postData) {
            $this->logger->error(__('Invalid POST data'));
            return $this->postData = null;
        }
        $this->postData = json_decode($this->postData, true);
        if (null === $this->postData) {
            $this->logger->error(__('Invalid JSON'));
        }
        return $this->postData;
    }
    
    /**
     * Check authorization with Zendesk account
     * @return bool
     */
    private function authorise()
    {
        return $this->authorization->isAuth($this->getPostData());
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $scope = $this->authorise();
        if (null === $scope) {
            $resultJson->setHttpResponseCode(Request::HTTP_UNAUTHORIZED);
            return $resultJson->setData($scope);
        }
        try {
            if ($this->config->isSeparateInfoForWebsite()) {
                $data = ['websites' => $this->getSeparateInfoForWebsites()];
            } else {
                $customerInfo = $this->getCustomerInfo($scope);
                $recentOrderInfo = $this->getRecentOrderInfo($scope);
                $data = array_merge($customerInfo, $recentOrderInfo);
            }
        } catch (\Exception $e) {
            $resultJson->setHttpResponseCode(500);
            return $resultJson->setData([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        if (!$data) {
            $resultJson->setHttpResponseCode(Request::HTTP_BAD_REQUEST);
            return $resultJson->setData([]);
        }
        return $resultJson->setData($data);
    }

    /**
     * @return array
     */
    private function getSeparateInfoForWebsites()
    {
        $data = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $customerInfo = $this->getCustomerInfo($website);
            if ($this->config->isSeparateInfoForStoreView()) {
                $recentOrderInfo = ['stores' => $this->getRecentOrderInfoForStores($website)];
            } else {
                $recentOrderInfo = $this->getRecentOrderInfo($website);
            }
            $data[] = array_merge(["name" => $website->getName()], $customerInfo, $recentOrderInfo);
        }
        return $data;
    }

    /**
     * @param integer|Website|Store $scope
     * @return array
     */
    private function getCustomerInfo($scope)
    {
        $result = [];
        $postData = $this->getPostData();
        if (null === $postData || !isset($postData['email'])) {
            return $result;
        }
        $customerInfo = null;
        if (isset($postData['order_id'])) {
            $customerInfo = $this->customerManagement->getInfoFromOrder($postData['order_id'], $scope);
        }
        if (!$customerInfo) {
            $customerInfo = $this->customerManagement->getInfo($postData['email'], $scope);
        }
        if ($customerInfo) {
            $result = ['customer_list' => $customerInfo];
        }
        return $result;
    }

    /**
     * @param Website $website
     * @return array
     */
    private function getRecentOrderInfoForStores($website)
    {
        $data = [];
        $postData = $this->getPostData();
        /** @var Store $store */
        foreach ($website->getStores() as $store) {
            $orderItemInfo = $this->getRecentOrderInfo($store);
            if (!$orderItemInfo && isset($postData['order_id'])) {
                $orderItemInfo = $this->orderRecentManagement->getInfoFromOrder($postData['order_id'], $store);
            }
            $data[] = array_merge(["name" => $store->getName()], $orderItemInfo);
        }
        return $data;
    }

    /**
     * @param integer|Website|Store $scope
     * @return array
     */
    private function getRecentOrderInfo($scope)
    {
        $result = [];
        $postData = $this->getPostData();
        if (null === $postData || !isset($postData['email'])) {
            return $result;
        }

        $orderItemInfo = null;
        if (isset($postData['order_id'])) {
            $orderItemInfo = $this->orderRecentManagement->getInfoFromOrder($postData['order_id'], $scope);
        }
        if (!$orderItemInfo) {
            $orderItemInfo = $this->orderRecentManagement->getInfo($postData['email'], $scope);
        }
        if ($orderItemInfo) {
            $result = ['order_list' => $orderItemInfo];
        }
        return $result;
    }
}