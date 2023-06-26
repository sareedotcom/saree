<?php

namespace Meetanshi\PayGlocal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface;
use Meetanshi\PayGlocal\Helper\Data;

/**
 * Class PayGlocalConfigProvider
 * @package Meetanshi\PayGlocal\Model
 */
class PayGlocalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     *
     */
    const CODE = 'payglocal';

    /**
     * PayGlocalConfigProvider constructor.
     * @param Data $helper
     * @param CheckoutSession $checkoutSession
     * @param SessionManagerInterface $coreSession
     */
    public function __construct(Data $helper, CheckoutSession $checkoutSession, SessionManagerInterface $coreSession)
    {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->coreSession = $coreSession;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $showLogo = $this->helper->showLogo();
        $imageUrl = $this->helper->getPaymentLogo();
        $instructions = $this->helper->getInstructions();

        $config['payglocal_isactive'] = 1;
        $config['payglocal_imageurl'] = ($showLogo) ? $imageUrl : '';
        $config['payglocal_instructions'] = ($instructions) ? $instructions : '';
        $config['payglocal_scriptUrl'] = $this->helper->getScriptUrl();
        $config['payglocal_cdid'] = $this->helper->getCdID();
        $config['payglocal_mode'] = $this->helper->getDisplayMode();
        $config['iframe_width'] = $this->helper->getIframeWidth()."px";

        return $config;
    }
}
