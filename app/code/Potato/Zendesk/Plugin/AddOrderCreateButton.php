<?php

namespace Potato\Zendesk\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Framework\UrlInterface;
use Potato\Zendesk\Model\Config;

class AddOrderCreateButton
{
    /** @var Config  */
    protected $config;

    /** @var UrlInterface  */
    protected $urlBuilder;

    /**
     * @param Config $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param View $view
     */
    public function beforeSetLayout(View $view)
    {
        if ($this->config->isSupportOrderSection()) {
            $view->addButton('potato_zendesk',
                [
                    'label'   => __('Create Ticket'),
                    'class'   => 'zendesk-create-ticket',
                    'onclick' => '',
                ]
            );
        }
    }
}