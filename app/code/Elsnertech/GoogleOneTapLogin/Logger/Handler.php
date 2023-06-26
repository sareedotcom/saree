<?php
  /**
   * @author Elsner Team
   * @copyright Copyright (c) Elsner Technologies Pvt. Ltd(https://www.elsner.com/)
   * @package Elsnertech_GoogleOneTapLogin
   */
declare(strict_types=1);
 
/**
 * Class RedirectUrl
 *
 * Elsnertech\GoogleOneTapLogin\Logger
 */
namespace Elsnertech\GoogleOneTapLogin\Logger;
 
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;
 
/**
 * Class RedirectUrl
 *
 * Elsnertech\GoogleOneTapLogin\Logger
 */
class Handler extends Base
{
    /**
     *
     * @var loggerType
     */
    protected $loggerType = Logger::INFO;
 
    /**
     *
     * @var fileName
     */
    protected $fileName = '/var/log/googleonetaplogin.log';
}
