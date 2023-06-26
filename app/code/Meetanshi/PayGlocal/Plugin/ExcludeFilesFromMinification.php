<?php

namespace Meetanshi\PayGlocal\Plugin;
use Magento\Framework\View\Asset\Minification;

/**
 * Class ExcludeFilesFromMinification
 * @package Meetanshi\PayGlocal\Plugin
 */
class ExcludeFilesFromMinification
{
    /**
     * @param Minification $subject
     * @param callable $proceed
     * @param $contentType
     * @return array
     */
    public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    {
        $result = $proceed($contentType);
        if ($contentType != 'js') {
            return $result;
        }
        $result[] = 'https://codedrop.uat.payglocal.in/simple.js';

        return $result;
    }
}