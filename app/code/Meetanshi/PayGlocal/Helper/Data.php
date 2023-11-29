<?php

namespace Meetanshi\PayGlocal\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Backend\Model\Session\Quote as AdminCheckoutSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class Data
 * @package Meetanshi\PayGlocal\Helper
 */
class Data extends AbstractHelper
{
    const CONFIG_SANDBOX_MODE = 'payment/payglocal/mode';
    const CONFIG_PAYMENT_ACTION = 'payment/payglocal/payment_action';
    const CONFIG_PAYGLOCAL_INSTRUCTION = 'payment/payglocal/instructions';
    const CONFIG_PAYGLOCAL_DEBUG = 'payment/payglocal/debug';
    const CONFIG_PAYGLOCAL_DISPLAY_MODE = 'payment/payglocal/display_mode';

    const CONFIG_SCRIPT_URL = 'https://codedrop.payglocal.in/simple.js';

    const CONFIG_PAYGLOCAL_SANDBOX_API_KEY = 'payment/payglocal/sandbox_api_key';
    const CONFIG_PAYGLOCAL_LIVE_API_KEY = 'payment/payglocal/live_api_key';

    const CONFIG_PAYGLOCAL_SANDBOX_CD_ID = 'payment/payglocal/sandbox_cd_id';
    const CONFIG_PAYGLOCAL_LIVE_CD_ID = 'payment/payglocal/live_cd_id';

    const CONFIG_PAYGLOCAL_SANDBOX_GATEWAY_URL = 'payment/payglocal/sandbox_gateway_url';
    const CONFIG_PAYGLOCAL_LIVE_GATEWAY_URL = 'payment/payglocal/live_gateway_url';

    const CONFIG_PAYGLOCAL_IFRAME_WIDTH = 'payment/payglocal/iframe_width';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var CurlFactory
     */
    protected $curlFactory;
    /**
     * @var StoreResolver
     */
    protected $storeResolver;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Repository
     */
    protected $repository;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var WriterInterface
     */
    protected $configWriter;
    /**
     * @var AdminCheckoutSession
     */
    protected $adminCheckoutSession;
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param CurlFactory $curlFactory
     * @param StoreResolver $storeResolver
     * @param StoreManagerInterface $storeManager
     * @param WriterInterface $configWriter
     * @param Repository $repository
     * @param AdminCheckoutSession $adminCheckoutSession
     * @param RequestInterface $request
     * @param Filesystem $fileSystem
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        CurlFactory $curlFactory,
        StoreResolver $storeResolver,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter,
        Repository $repository,
        AdminCheckoutSession $adminCheckoutSession,
        RequestInterface $request,
        Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->curlFactory = $curlFactory;
        $this->storeResolver = $storeResolver;
        $this->storeManager = $storeManager;
        $this->repository = $repository;
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->adminCheckoutSession = $adminCheckoutSession;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return mixed
     */
    public function getCdID()
    {
        if ($this->getMode()) {
            return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_SANDBOX_CD_ID,
                ScopeInterface::SCOPE_STORE);
        } else {
            return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_LIVE_CD_ID,
                ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * @return mixed
     */
    public function getDisplayMode()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_DISPLAY_MODE,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getIframeWidth()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_IFRAME_WIDTH,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGatewayUrl()
    {
        if ($this->getMode()) {
            return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_SANDBOX_GATEWAY_URL,
                ScopeInterface::SCOPE_STORE);
        } else {
            return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_LIVE_GATEWAY_URL, ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        if ($this->getMode()) {
            return $this->encryptor->decrypt($this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_SANDBOX_API_KEY,
                ScopeInterface::SCOPE_STORE));
        } else {
            return $this->encryptor->decrypt($this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_LIVE_API_KEY,
                ScopeInterface::SCOPE_STORE));
        }
    }

    /**
     * @return mixed
     */
    public function showLogo()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getPaymentLogo()
    {
        $params = ['_secure' => $this->request->isSecure()];
        return $this->repository->getUrlWithParams('Meetanshi_PayGlocal::images/payglocal.png', $params);
    }

    /**
     * @return mixed
     */
    public function getInstructions()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_INSTRUCTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->scopeConfig->getValue(self::CONFIG_SANDBOX_MODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPaymentSubject()
    {
        $subject = trim($this->scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE));
        if (!$subject) {
            return "Magento 2 order";
        }

        return $subject;
    }

    /**
     * @return string
     */
    public function getMediaPath()
    {
        return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * @return mixed
     */
    public function isLoggerEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PAYGLOCAL_DEBUG, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getScriptUrl()
    {
        return self::CONFIG_SCRIPT_URL;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCallbackUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl . "payglocal/payment/success";
    }

    /**
     * @return mixed
     */
    public function getCurrentQuote()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        return $checkoutSession->getQuote();
    }

    /**
     * @param int $length
     * @return string
     */
    public function generateRandomString($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}