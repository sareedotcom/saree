<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\Config\Source;

use \Magento\Framework\Registry;
use \Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use \Magento\Email\Model\Template\Config;

/**
 * Templates classs
 */
class Template extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;
    
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;
    
    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_templatesFactory;
    
    /**
     * @param Registry $coreRegistry
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_coreRegistry = $coreRegistry;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
    }

    /**
     * To option array set
     *
     * @return Array
     */
    public function toOptionArray()
    {
        if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
            $collection = $this->_templatesFactory->create();
            $collection->load();
            $this->_coreRegistry->register('config_system_email_template', $collection);
        }
        
        $options = $collection->toOptionArray();
        $templateId = str_replace('/', '_', $this->getPath());
        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);

        return $options;
    }
}
