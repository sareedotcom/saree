<?php

namespace Meetanshi\PayGlocal\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Meetanshi\PayGlocal\Model\PayGlocalConfigProvider;

/**
 * Class Payment
 * @package Meetanshi\PayGlocal\Block
 */
class Payment extends Template
{
    /**
     *
     */
    const PAYMENT_CODE = 'payglocal';

    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var PayGlocalConfigProvider
     */
    private $configProvider;

    /**
     * Payment constructor.
     * @param Context $context
     * @param ConfigInterface $config
     * @param PayGlocalConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        PayGlocalConfigProvider $configProvider,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getPaymentConfig()
    {
        $payment = $this->configProvider->getConfig();
        $payment['code'] = $this->getCode();
        return json_encode($payment, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return self::PAYMENT_CODE;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return parent::toHtml();
    }
}
