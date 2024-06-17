<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;
use Zend\Mime\Mime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Logicrays\CustomerWallet\Helper\Data;

class Mail
{
    /**
     * HelperData variable
     *
     * @var Data
     */
    protected $helperData;

    /**
     * ScopeConfig variable
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * __construct function
     *
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager = null
    ) {

        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Send function
     *
     * @param array $sendEmailData
     * @param string $templates
     * @param string $sendTo
     * @return array
     */
    public function send($sendEmailData, $templates, $sendTo)
    {
        $this->inlineTranslation->suspend();
        $sender = $this->helperData->senderEmail();

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templates)
                ->setTemplateOptions($this->helperData->setTemplateOptions())
                ->setTemplateVars($sendEmailData)
                ->setFrom($sender)
                ->addTo($sendTo);
            $transport->getTransport()->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
