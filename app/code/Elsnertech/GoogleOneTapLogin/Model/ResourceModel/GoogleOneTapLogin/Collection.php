<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) Elsner Technologies Pvt. Ltd(https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Model\ResourceModel\GoogleOneTapLogin;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Contact Resource Model Collection
 *
 * @author  Pierre FAY
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Elsnertech\GoogleOneTapLogin\Model\GoogleOneTapLogin::class,
            \Elsnertech\GoogleOneTapLogin\Model\ResourceModel\GoogleOneTapLogin::class
        );
    }
}
