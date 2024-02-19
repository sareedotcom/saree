<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Block\Adminhtml\Codeset\Edit\Tabs\View;

class Deletelinks extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
     /**
      * Delete link create
      *
      * @param  object $row
      * @return string
      */
    public function render(\Magento\Framework\DataObject $row)
    {
        $link="<a href='".$this->getUrl('giftcertificate/index/delete/', ['id'=>$row->getId()])."' >Delete </a>";
        return htmlspecialchars_decode($link, ENT_QUOTES);

        // $a = htmlentities('<a href="'.$this->urlBuilder->getUrl(
        //                 self::ORDER_URL_PATH,
        //                 ['order_id' => $orderid]
        //             ).'">'.$item['orderid'].'</a>');
        // $item[$name] = htmlspecialchars_decode($a);
    }
}
