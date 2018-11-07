<?php
declare(strict_types=1);

/**
 * @author tjitse (Vendic)
 * Created on 07/11/2018 10:46
 */

namespace Vendic\StockChangeAfterPayment\Model;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Psr\Log\LoggerInterface;
use Vendic\StockChangeAfterPayment\Api\QtyModifierInterface;

class QtyModifier implements QtyModifierInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    public function __construct(
        StockRegistryInterface $stockRegistry,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param  string $productId
     * @param  string $sku
     * @param  float  $modifier
     * @return $this|void
     */
    public function modify(string $productId, string $sku, float $modifier)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        if (!$stockItem->getManageStock()) {
            return; // We're not managing stock for this product, skipping
        }

        $currentQty = $stockItem->getQty();
        $newQty = $currentQty + $modifier;
        $stockItem->setQty($newQty);

        // Set stock status
        $stockItem->setIsInStock(true);
        if($newQty <= 0) {
            $stockItem->setIsInStock(false);
        }

        try {
            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
            $this->logger->info(sprintf('%s QTY modified to %d', $sku, $newQty));
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $this;
    }
}