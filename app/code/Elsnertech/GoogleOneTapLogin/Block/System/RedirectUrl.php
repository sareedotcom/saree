<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd(https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Block\System;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Elsnertech\GoogleOneTapLogin\Helper\Data as DataHelper;
use Elsnertech\GoogleOneTapLogin\Helper\Social as SocialHelper;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * Class RedirectUrl
 *
 * Elsnertech\GoogleOneTapLogin\Block\System
 */
class RedirectUrl extends FormField
{
    /**
     *
     * @var dataHeler
     */
    protected $dataHelper;

    /**
     * @var socialHelper
     */
    protected $socialHelper;

    /**
     * @var PhpCookieManager
     */
    protected $phpCookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * For __construct function
     *
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param SocialHelper $socialHelper
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $phpCookieManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        SocialHelper $socialHelper,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $phpCookieManager,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->socialHelper = $socialHelper;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->phpCookieManager = $phpCookieManager;
        parent::__construct($context, $data);
    }
    
    /**
     * For data helper function
     *
     * @return string
     */
    public function dataHelper()
    {
        
        return $this->dataHelper;
    }

    /**
     * For social helper function
     *
     * @return string
     */
    public function socialHelper()
    {
        
        return $this->socialHelper;
    }

    /**
     * For _getElementHtml function
     *
     * @param AbstractElement $element
     * @return void
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId   = explode('_', $element->getHtmlId());
        $redirectUrl = $this->socialHelper->getAuthUrl($elementId[1]);
        $html        = '<input style="opacity:1;" readonly id="' . $element->
        getHtmlId() . '" class="input-text admin__control-text" value="' . $redirectUrl .
        '" onclick="this.select()" type="text">';
        return $html;
    }

     /**
      * For delete cookies function
      *
      * @return void
      */
    public function deleteCookies()
    {

        if ($this->getCookieManager()->getCookie('g_state')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('g_state', $metadata);
        }
    }
  
    /**
     * For get cookie manager function
     *
     * @return string
     */
    private function getCookieManager()
    {
        if (!$this->phpCookieManager) {
            $this->phpCookieManager = $phpCookieManager;
        }

        return $this->phpCookieManager;
    }

    /**
     * For get cookie metadata factory function
     *
     * @return string
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = $cookieMetadataFactory;
        }

        return $this->cookieMetadataFactory;
    }
}
