<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function hasOpeningInventory(Product $product): bool
    {
        return StockMovement::where('shop_id', $product->shop_id)
            ->where('product_id', $product->id)
            ->where('document_type', 'opening_inventory')
            ->exists();
    }

    public function apply(
        Product $product,
        string $direction,
        int $quantity,
        string $reference,
        string $reason,
        ?int $userId = null,
        ?string $documentType = null,
        ?int $documentId = null,
        ?int $locationId = null,
    ): StockMovement {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        if (! in_array($direction, ['in', 'out'], true)) {
            throw new InvalidArgumentException('Direction must be in or out.');
        }

        $userId ??= Auth::id();
        $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
        $previousStock = $product->stock_quantity;

        if ($direction === 'out' && $quantity > $previousStock) {
            throw new InvalidArgumentException("Insufficient stock for {$product->name}. Available: {$previousStock}");
        }

        $currentStock = $direction === 'in'
            ? $previousStock + $quantity
            : $previousStock - $quantity;

        $movement = StockMovement::create([
            'shop_id' => $product->shop_id,
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $direction,
            'reason' => $reason,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'current_stock' => $currentStock,
            'reference' => $reference,
            'document_type' => $documentType,
            'document_id' => $documentId,
            'location_id' => $locationId,
        ]);

        $product->update(['stock_quantity' => $currentStock]);

        return $movement;
    }

    /**
     * POS / website sale — deducts sellable stock and logs type "sale".
     */
    public function recordSale(
        Product $product,
        int $quantity,
        string $reference,
        ?int $userId = null,
        ?string $documentType = null,
        ?int $documentId = null,
    ): StockMovement {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        $userId ??= Auth::id();
        $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
        $previousStock = $product->stock_quantity;

        if ($quantity > $previousStock) {
            throw new InvalidArgumentException("Insufficient stock for {$product->name}. Available: {$previousStock}");
        }

        $currentStock = $previousStock - $quantity;

        $movement = StockMovement::create([
            'shop_id' => $product->shop_id,
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => 'sale',
            'reason' => 'sale',
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'current_stock' => $currentStock,
            'reference' => $reference,
            'document_type' => $documentType ?? 'order',
            'document_id' => $documentId,
            'location_id' => $this->defaultStore($product->shop_id)?->id,
        ]);

        $product->update(['stock_quantity' => $currentStock]);

        return $movement;
    }

    /**
     * Restore stock from refund / return / cancel — skips if already restocked for this document.
     */
    public function restockForDocument(
        Product $product,
        int $quantity,
        string $reference,
        string $documentType,
        int $documentId,
        string $reason = 'order_return',
        ?int $userId = null,
    ): ?StockMovement {
        $alreadyRestocked = StockMovement::where('shop_id', $product->shop_id)
            ->where('product_id', $product->id)
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->where('type', 'in')
            ->exists();

        if ($alreadyRestocked) {
            return null;
        }

        return $this->apply(
            $product,
            'in',
            $quantity,
            $reference,
            $reason,
            $userId,
            $documentType,
            $documentId,
            $this->defaultStore($product->shop_id)?->id,
        );
    }

    public function setOpeningStock(Product $product, int $quantity, ?int $userId = null): StockMovement
    {
        if ($this->hasOpeningInventory($product)) {
            throw new InvalidArgumentException('Opening inventory already recorded. Use Stock Adjustment.');
        }

        if ($product->stock_quantity !== 0) {
            throw new InvalidArgumentException(
                "{$product->name} already has {$product->stock_quantity} units on hand. Use Stock Adjustment instead."
            );
        }

        if ($quantity < 1) {
            throw new InvalidArgumentException('Opening quantity must be at least 1.');
        }

        return $this->apply(
            $product,
            'in',
            $quantity,
            'Opening inventory set to ' . $quantity,
            'opening_inventory',
            $userId,
            'opening_inventory',
            null,
            $this->defaultStore($product->shop_id)?->id,
        );
    }

    public function adjustWarehouseStock(StockLocation $location, Product $product, int $delta): void
    {
        if ($location->type !== 'warehouse') {
            return;
        }

        $row = WarehouseStock::firstOrCreate(
            ['location_id' => $location->id, 'product_id' => $product->id],
            ['quantity' => 0],
        );

        $newQty = $row->quantity + $delta;
        if ($newQty < 0) {
            throw new InvalidArgumentException("Insufficient warehouse stock for {$product->name}.");
        }

        $row->update(['quantity' => $newQty]);
    }

    public function transferBetweenLocations(
        StockLocation $from,
        StockLocation $to,
        Product $product,
        int $quantity,
        string $reference,
        ?int $userId = null,
        ?string $documentType = null,
        ?int $documentId = null,
    ): void {
        if ($from->shop_id !== $to->shop_id || $from->id === $to->id) {
            throw new InvalidArgumentException('Invalid transfer locations.');
        }

        $userId ??= Auth::id();

        if ($from->type === 'warehouse' && $to->type === 'store') {
            $this->adjustWarehouseStock($from, $product, -$quantity);
            $this->apply($product, 'in', $quantity, $reference, 'stock_transfer', $userId, $documentType, $documentId, $to->id);
            return;
        }

        if ($from->type === 'store' && $to->type === 'warehouse') {
            $this->apply($product, 'out', $quantity, $reference, 'stock_transfer', $userId, $documentType, $documentId, $from->id);
            $this->adjustWarehouseStock($to, $product, $quantity);
            return;
        }

        if ($from->type === 'warehouse' && $to->type === 'warehouse') {
            $this->adjustWarehouseStock($from, $product, -$quantity);
            $this->adjustWarehouseStock($to, $product, $quantity);
            return;
        }

        throw new InvalidArgumentException('Store-to-store transfers are not supported yet.');
    }

    public function ensureDefaultLocations(int $shopId): void
    {
        if (! StockLocation::where('shop_id', $shopId)->where('type', 'store')->exists()) {
            StockLocation::create([
                'shop_id' => $shopId,
                'name' => 'Main Store',
                'type' => 'store',
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        if (! StockLocation::where('shop_id', $shopId)->where('type', 'warehouse')->exists()) {
            StockLocation::create([
                'shop_id' => $shopId,
                'name' => 'Main Warehouse',
                'type' => 'warehouse',
                'is_default' => true,
                'is_active' => true,
            ]);
        }
    }

    public function defaultStore(int $shopId): ?StockLocation
    {
        return StockLocation::where('shop_id', $shopId)
            ->where('type', 'store')
            ->orderByDesc('is_default')
            ->first();
    }

    public function defaultWarehouse(int $shopId): ?StockLocation
    {
        return StockLocation::where('shop_id', $shopId)
            ->where('type', 'warehouse')
            ->orderByDesc('is_default')
            ->first();
    }

    public function generateNumber(int $shopId, string $prefix): string
    {
        return $prefix . '-' . $shopId . '-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    public function receivePurchaseItem(Product $product, int $quantity, string $poNumber, ?int $userId = null, ?int $documentId = null): void
    {
        $this->apply(
            $product,
            'in',
            $quantity,
            'PO received: ' . $poNumber,
            'purchase_receive',
            $userId,
            'purchase_order',
            $documentId,
            $this->defaultStore($product->shop_id)?->id,
        );
    }

    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
