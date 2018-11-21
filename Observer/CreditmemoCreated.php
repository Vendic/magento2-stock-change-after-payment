<?php
declare(strict_types=1);

/**
 * @author tjitse (Vendic)
 * Created on 07/11/2018 11:20
 */

namespace Vendic\StockChangeAfterPayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Vendic\StockChangeAfterPayment\Api\QtyModifierInterface;

class CreditmemoCreated implements ObserverInterface
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
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order\Creditmemo $creditMemo
         */
        $creditMemo = $observer->getData('creditmemo');

        foreach ($creditMemo->getOrder()->getItems() as $item) {

            $orderedQty = (float)$item->getQtyOrdered();
            $qtyMutation = (float)$orderedQty * 1;
            $id = (string)$item->getProductId();
            $sku = (string)$item->getSku();

            try {
                $this->qtyModifier->modify($id, $sku, $qtyMutation);
                $this->logger->debug(
                    sprintf('%d items creedited of ID %s, Mutation: %d', $orderedQty, $id, $qtyMutation)
                );
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}