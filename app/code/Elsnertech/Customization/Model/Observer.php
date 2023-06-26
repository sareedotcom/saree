<?php

namespace Elsnertech\Customization\Model;

use Magento\Framework\Event\ObserverInterface;
// use Elsnertech\Customization\Helper\Data;

/**
 * Class Observer
 * @package Elsnertech\Customization\Model
 */
class Observer implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $httpHeader;

    /**
     * Observer constructor.
     * @param Data $helper
     * @param \Magento\Framework\HTTP\Header   $httpHeader
     */
    public function __construct(
        // Data $helper,
        \Magento\Framework\App\Action\Context $contaxt,
        \Magento\Framework\HTTP\Header $httpHeader
    ) {
        // $this->_helper = $helper;
        $this->getContaxt = $contaxt;
        $this->httpHeader = $httpHeader;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // if (!$this->_helper->isEnabled()) {
        //     return;
        // }
        $request = $this->getContaxt->getRequest();
        $pagename = $request->getFullActionName();

        $response = $observer->getEvent()->getData('response');
        if (!$response) {
            return;
        }
        $html = $response->getBody();
        if ($html == '') {
            return;
        }

        $userAgent = $this->httpHeader->getHttpUserAgent();        
        // $isDesktopDevice = \Zend_Http_UserAgent_Desktop::match($userAgent, $_SERVER);
        $isMobileDevice = \Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
        if ($pagename == "catalog_category_view") {

            if($isMobileDevice){
                //Write your code here for Mobile view

                $conditionalJsPattern = '/\.(jpg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpg?class=1x', $html);
        
                $conditionalJsPattern = '/\.(jpeg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpeg?class=1x', $html);
        
                $conditionalJsPattern = '/\.(png)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.png?class=1x', $html);
        
                // $conditionalJsPattern = '/\.(svg)(?!\?)/i';
                // preg_match_all($conditionalJsPattern, $html, $_matches);
                // $html = preg_replace($conditionalJsPattern, '.svg?class=1x', $html);

            }else{

                $conditionalJsPattern = '/\.(jpg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpg?class=2x', $html);
        
                $conditionalJsPattern = '/\.(jpeg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpeg?class=2x', $html);
        
                $conditionalJsPattern = '/\.(png)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.png?class=2x', $html);
        
                // $conditionalJsPattern = '/\.(svg)(?!\?)/i';
                // preg_match_all($conditionalJsPattern, $html, $_matches);
                // $html = preg_replace($conditionalJsPattern, '.svg?class=2x', $html);

            }
        }elseif($pagename == "catalog_product_view"){

            if($isMobileDevice){
                //Write your code here for Mobile view

                $conditionalJsPattern = '/\.(jpg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpg?class=3x', $html);
        
                $conditionalJsPattern = '/\.(jpeg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpeg?class=3x', $html);
        
                $conditionalJsPattern = '/\.(png)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.png?class=3x', $html);
        
                // $conditionalJsPattern = '/\.(svg)(?!\?)/i';
                // preg_match_all($conditionalJsPattern, $html, $_matches);
                // $html = preg_replace($conditionalJsPattern, '.svg?class=3x', $html);

            }else{

                $conditionalJsPattern = '/\.(jpg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpg?class=4x', $html);
        
                $conditionalJsPattern = '/\.(jpeg)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.jpeg?class=4x', $html);
        
                $conditionalJsPattern = '/\.(png)(?!\?)/i';
                preg_match_all($conditionalJsPattern, $html, $_matches);
                $html = preg_replace($conditionalJsPattern, '.png?class=4x', $html);
        
                // $conditionalJsPattern = '/\.(svg)(?!\?)/i';
                // preg_match_all($conditionalJsPattern, $html, $_matches);
                // $html = preg_replace($conditionalJsPattern, '.svg?class=4x', $html);

            }
        }
        $response->setBody($html);
    }
}
