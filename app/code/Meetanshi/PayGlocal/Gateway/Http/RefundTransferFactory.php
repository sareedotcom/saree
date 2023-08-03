<?php

namespace Meetanshi\PayGlocal\Gateway\Http;

/**
 * Class RefundTransferFactory
 * @package Meetanshi\PayGlocal\Gateway\Http
 */
class RefundTransferFactory extends AbstractTransferFactory
{
    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
