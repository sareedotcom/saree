<?php

namespace Meetanshi\PayGlocal\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AbstractDataBuilder
 * @package Meetanshi\PayGlocal\Gateway\Request
 */
abstract class AbstractDataBuilder implements BuilderInterface
{
    /**
     *
     */
    const PAYMENT = 'Payment';

    /**
     *
     */
    const REFUND = 'Refund';
}
