<?php

namespace Meetanshi\PayGlocal\Gateway\Http;

/**
 * Class TransferFactory
 * @package Meetanshi\PayGlocal\Gateway\Http
 */
class TransferFactory extends AbstractTransferFactory
{
    /**
     *
     */
    const POST = 'POST';

    /**
     * @param array $request
     * @return \Magento\Payment\Gateway\Http\TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setMethod(self::POST)
            ->setBody($request)
            ->build();
    }
}
