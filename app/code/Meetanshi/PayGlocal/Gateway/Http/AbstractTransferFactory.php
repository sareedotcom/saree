<?php

namespace Meetanshi\PayGlocal\Gateway\Http;

use Magento\Framework\Xml\Generator;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

/**
 * Class AbstractTransferFactory
 * @package Meetanshi\PayGlocal\Gateway\Http
 */
abstract class AbstractTransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;
    /**
     * @var Generator
     */
    protected $generator;
    /**
     * @var
     */
    protected $payglocalHelper;
    /**
     * @var null
     */
    private $action;

    /**
     * AbstractTransferFactory constructor.
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param Generator $generator
     * @param null $action
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        Generator $generator,
        $action = null
    )
    {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->generator = $generator;
        $this->action = $action;
    }
}
