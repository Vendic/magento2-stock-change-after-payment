# Magento 2: Stock Change After Payment
A simple Magento 2 extension that uses the `sales_order_invoice_pay` and `sales_order_creditmemo_refund` events to decrease/increase product quantity. It also adjusts the product stock status.

## Default Magento 2
1. An order is placed
2. The product stock quantity is decreased with the ordered quantity
3. Payment is made or canceled. 
4. Depending on this event it stays decreased (succesfull payment) or is added again (unsuccesfull payment)

## Changes after using this module
1. An order is placed
2. The order is paid
2. The product stock quantity is decreased with the ordered quantity

Result: stock is not decreased (reserved) for unpaid orders.

## Installation
In vanilla Magento 2.2, the product stock is 'reserved' (substracted) when an order is placed, so we'll have to disable this system so we can replace it wit our own.

1. Set 'Decrease Stock When Order is Placed' to 'No' to disable. You can find it here: Stores > Inventory > Stock Options

2. Install via composer:
```bash
composer require vendic/magento2-invoiceqty
```
