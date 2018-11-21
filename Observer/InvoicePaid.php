<?php
declare(strict_types=1);

/**
 * @author tjitse (Vendic)
 * Created on 07/11/2018 10:21
 */

namespace Vendic\StockChangeAfterPayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Vendic\StockChangeAfterPayment\Api\QtyModifierInterface;

class InvoicePaid implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var QtyModifierInterface
     */
    protected $qtyModifier;

    public function __construct(
        QtyModifierInterface $qtyModifier,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->qtyModifier = $qtyModifier;
    }

    /**
     * Decrease Qty when an invoice is paid
     *
     * @param  Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order\Invoice $invoice
         */
        $invoice = $observer->getData('invoice');

        foreach ($invoice->getOrder()->getAllVisibleItems() as $item) {
            /** @var \Magento\Sales\Order\Item $item */
            $orderedQty = (float)$item->getQtyInvoiced();
            $qtyMutation = (float)$orderedQty * -1;
            $id = (string)$item->getProductId();
            $sku = (string)$item->getSku();

            try {
                $this->qtyModifier->modify($id, $sku, $qtyMutation);
                $this->logger->debug(
                    sprintf('%d items ordered of ID %s, Mutation: %d', $orderedQty, $id, $qtyMutation)
                );
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}
