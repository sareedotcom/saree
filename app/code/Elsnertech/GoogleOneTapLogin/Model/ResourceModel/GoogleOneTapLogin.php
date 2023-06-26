<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) Elsner Technologies Pvt. Ltd(https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Contact Resource Model
 *
 * @author Pierre FAY
 */
class GoogleOneTapLogin extends AbstractDb
{
    /**
     * For _construct function
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('elsnertech_google_customer', 'google_customer_id');
    }
}
