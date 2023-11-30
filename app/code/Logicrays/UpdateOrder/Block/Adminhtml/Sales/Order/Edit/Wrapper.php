<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Logicrays\UpdateOrder\Block\Adminhtml\Sales\Order\Edit;

use Magento\Backend\Block\Template;

class Wrapper extends \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Wrapper
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \MageWorx\OrderEditor\Helper\Data
     */
    protected $helperData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        \MageWorx\OrderEditor\Helper\Data $helperData,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->authorization = $authorization;
        $this->helperData     = $helperData;
        $this->coreRegistry      = $registry;
        parent::__construct($context, 
                            $authorization,
                            $helperData,
                            $registry,
                            $data
        );
    }

    /**
     * @return string
     */
    public function getJsonParamsItems()
    {
        $isAllow = 0;
        
        if($this->authorization->isAllowed('all')){
            $isAllow = 1;
        }
        else if($this->coreRegistry->registry('current_order')->getStatus() == 'pending_measurement'){
            
            if($this->authorization->isAllowed('Logicrays_UpdateOrder::pending') && $this->authorization->isAllowed('Logicrays_UpdateOrder::under_procurement')){
                $isAllow = 1;
            }
        }
        else if($this->coreRegistry->registry('current_order')->getStatus() == 'processing'){
            if($this->authorization->isAllowed('Logicrays_UpdateOrder::processing') && $this->authorization->isAllowed('Logicrays_UpdateOrder::under_procurement')){
                $isAllow = 1;
            }
        }
        else if($this->coreRegistry->registry('current_order')->getStatus() == 'under_procurement'){
            if($this->authorization->isAllowed('Logicrays_UpdateOrder::under_procurement')){
                $isAllow = 1;
            }
        }

        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/items'),
            'isAllowed'   => $isAllow
        ];

        return json_encode($data);
    }
}
