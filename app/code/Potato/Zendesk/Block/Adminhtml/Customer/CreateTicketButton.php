<?php
namespace Potato\Zendesk\Block\Adminhtml\Customer;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Potato\Zendesk\Model\Config;

class CreateTicketButton implements ButtonProviderInterface
{
    /** @var Config  */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }
    
    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonData = [];
        if ($this->config->isSupportCustomerSection()) {
            $buttonData = [
                'label'   => __('Create Ticket'),
                'class'   => 'zendesk-create-ticket',
                'on_click' => '',
            ];
        }
        return $buttonData;
    }
}
