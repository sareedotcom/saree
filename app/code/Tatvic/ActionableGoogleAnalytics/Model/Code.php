<?php

/**
 * Tatvic Software
 *
 * @category Tatvic
 * @package Tatvic_ActionableGoogleAnalytics
 * @author Tatvic
 * @copyright Copyright (c)  Tatvic Analytics LLC (https://www.tatvic.com)
 */

namespace Tatvic\ActionableGoogleAnalytics\Model;

use PHPUnit\Framework\Exception;
use \Magento\Framework\App\Config\Value;

/**
 * Class Code
 * @package Tatvic\ActionableGoogleAnalytics\Model
 */

class Code extends Value
{

    /**
     * @var \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator
     */
    protected $configValidator;

    /**
     * Code constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator $configValidator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator $configValidator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValidator = $configValidator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }


    /**
     * @return \Magento\Framework\App\Config\Value
     * @throws \Exception
     */

    public function beforeSave()
    {
        try {
            $value = $this->getValue();

            if(!preg_match("/^([a-z0-9]{32})$/im", $value)){
                throw new \Exception('Invalid Activation Code,Please enter correct code!');
            }else{
                return parent::beforeSave();
            }
        }catch (Exception $e){
            throw new \Exception('Invalid Activation Code,Please enter correct code!');
        }
    }

}
