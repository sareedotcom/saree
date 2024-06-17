<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Observer;

class SetAmount implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     *
     * @var request
     */
    protected $request;

    /**
     * __construct function
     *
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
    }

    /**
     * CustomPrice execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $postValues = $this->request->getPostValue();

        if (isset($postValues['wallet-amount'])) {

            $item = $observer->getEvent()->getData('quote_item');
            $item = ($item->getParentItem() ? $item->getParentItem() : $item );

            $amountPrice = (isset($postValues['wallet-amount']) && $postValues['wallet-amount']) ?
            $postValues['wallet-amount'] : $item->getProduct()->getPrice();

            // $item = $observer->getEvent()->getData('quote_item');
            // $item = ($item->getParentItem() ? $item->getParentItem() : $item );
            $price = $amountPrice;
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $quote = $this->quoteRepository->get($item->getQuoteId());
            $this->quoteRepository->save($quote);

            return $this;
        }
        return $this;
    }
}
