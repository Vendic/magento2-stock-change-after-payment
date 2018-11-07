<?php
/**
 * @author tjitse (Vendic)
 * Created on 07/11/2018 10:48
 */

namespace Vendic\StockChangeAfterPayment\Api;

interface QtyModifierInterface
{
    /**
     * Modify product quantity with product ID and modifier
     *
     * @param  string $productId
     * @param  string $sku
     * @param  float  $modifier
     * @return mixed
     */
    public function modify(string $productId, string $sku, float $modifier);
}