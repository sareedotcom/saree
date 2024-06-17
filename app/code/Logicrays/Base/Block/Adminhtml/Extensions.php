<?php
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_Base
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\Base\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Logicrays\Base\Helper\Module;

class Extensions extends Field
{
    protected $_template = 'Logicrays_Base::modules.phtml';

    public function __construct(
        Template\Context $context,
        Module $helperModule,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleHelper = $helperModule;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getModuleList()
    {
        return $this->moduleHelper->getAllModules();
    }

    /**
     * @return array
     */
    public function getModuleData($moduleCode)
    {
        return $this->moduleHelper->getModuleInfo($moduleCode);
    }

    /**
     * @return array
     */
    public function getFinalModules()
    {
        return $this->moduleHelper->getModuleExistingInComposer();
    }
}
