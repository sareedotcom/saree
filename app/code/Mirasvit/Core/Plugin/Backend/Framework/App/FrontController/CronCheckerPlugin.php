<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-core
 * @version   1.2.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\Core\Plugin\Backend\Framework\App\FrontController;


use Magento\Framework\App\RequestInterface;
use Mirasvit\Core\Api\Service\CronServiceInterface;

class CronCheckerPlugin
{
    /** @var CronServiceInterface */
    private $cronService;

    public function __construct(
        CronServiceInterface $cronService
    ){
        $this->cronService = $cronService;
    }

    /**
     * @param $subject
     * @param RequestInterface $request
     */
    public function beforeDispatch($subject, RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $moduleName = $request->getControllerModule();

        if (!$request->isAjax() && strpos($moduleName, 'Mirasvit_') !== false) {
            $this->cronService->outputCronStatus($moduleName);
        }
    }
}