<?php


namespace Logicrays\PaymentLink\Plugin\Magento\Sales\Block\Adminhtml\Order;

/**
 * Class View
 *
 * @package Logicrays\PaymentLink\Plugin\Magento\Sales\Block\Adminhtml\Order
 */
class View
{

    public function beforeSetLayout(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $layout
    ) {
        $order = $subject->getOrder();
        if($order->getBaseTotalDue() && $order->getStatus() != "canceled" && $order->getStatus() != "closed"){
            $subject->addButton(
                'sendordersms',
                [
                    'label' => __('Send Payment Reminder'),
                    'onclick' => "",
                    'class' => 'action-default action-sendpayment-link',
                ]
            );
        }
        return [$layout];
    }

    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {
        if($subject->getNameInLayout() == 'sales_order_edit'){
            $customBlockHtml = $subject->getLayout()->createBlock(
                \Logicrays\PaymentLink\Block\Adminhtml\Order\ModalBox::class,
                $subject->getNameInLayout().'_modal_box'
            )->setOrder($subject->getOrder())
                ->setTemplate('Logicrays_PaymentLink::order/modalbox.phtml')
                ->toHtml();
            return $result.$customBlockHtml;
        }
        return $result;
    }
}

