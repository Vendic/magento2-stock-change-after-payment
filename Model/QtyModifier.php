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
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Api\StockStateInterface;

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
    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * QtyModifier constructor.
     * @param StockItemRepository $stockItemRepository
     * @param StockRegistryInterface $stockRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockItemRepository $stockItemRepository,
        StockRegistryInterface $stockRegistry,
        LoggerInterface $logger
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->logger = $logger;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param  string $productId
     * @param  string $sku
     * @param  float $modifier
     * @return $this|void
     */
    public function modify(string $productId, string $sku, float $modifier)
    {
        try {
            $stockItem = $this->stockItemRepository->get($productId);
        } catch (\Exception $e) {
            return;
        }

        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        if (!$stockItem->getManageStock()) {
            return; // We're not managing stock for this product, skipping
        }

        $currentQty = $stockItem->getQty();
        $newQty = $currentQty + $modifier;
        $stockItem->setQty($newQty);

        // Set stock status
        $stockItem->setIsInStock(true);
        if ($newQty <= 0) {
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