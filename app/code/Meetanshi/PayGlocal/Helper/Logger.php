<?php

namespace Meetanshi\PayGlocal\Helper;

use Psr\Log\LoggerInterface;

/**
 * Class Logger
 * @package Meetanshi\PayGlocal\Helper
 */
class Logger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Logger constructor.
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(LoggerInterface $logger, Data $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param $message
     * @param array $context
     * @throws \Zend_Log_Exception
     */
    public function debug($message, array $context = [])
    {
        if ($this->helper->isLoggerEnabled()) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/PayGlocal.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r($message,true));
            $logger->info(print_r($context,true));
        }
    }
}
