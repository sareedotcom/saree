<?php

namespace Meetanshi\PayGlocal\Block;

use Magento\Payment\Block\ConfigurableInfo;

/**
 * Class Info
 * @package Meetanshi\PayGlocal\Block
 */
class Info extends ConfigurableInfo
{
    /**
     * @param string $field
     * @return \Magento\Framework\Phrase|string
     */
    protected function getLabel($field)
    {
        switch ($field) {
            case 'status':
                return __('Payment Status');
            case 'transactionCreationTime':
                return __('Transaction Creation Time');
            case 'gid':
                return __('GID');
            case 'payment-method':
                return __('Payment Method');
            case 'Amount':
                return __('Amount');
            case 'paid':
                return __('Paid');
            case 'txnCurrency':
                return __('Txn Currency');
            case 'merchantTxnId':
                return __('Merchant TxnId');
            case 'CardBrand':
                return __('Card Brand');
            case 'detailedMessage':
                return __('Detailed Message');
            case 'CardType':
                return __('Card Type');
            case 'merchantUniqueId':
                return __('Merchant Unique Id');
            case 'reasonCode':
                return __('Reason Code');
            case 'authApprovalCode':
                return __('Auth Approval Code');
            default:
                return parent::getLabel($field);
        }
    }
}