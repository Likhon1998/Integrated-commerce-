<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\StockLocation;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockLocationController extends Controller
{
    use ShopScoped;

    public function __construct(protected StockService $stock) {}

    public function stores()
    {
        $this->stock->ensureDefaultLocations($this->shopId());
        $locations = StockLocation::where('shop_id', $this->shopId())->where('type', 'store')->latest()->get();
        return view('supply.stores.index', compact('locations'));
    }

    public function storeCreate()
    {
        return view('supply.stores.create');
    }

    public function storeSave(Request $request)
    {
        return $this->saveLocation($request, 'store', 'supply.stores.index', 'Store');
    }

    public function storeEdit(StockLocation $location)
    {
        $this->authorizeShop($location);
        abort_unless($location->type === 'store', 404);
        return view('supply.stores.edit', compact('location'));
    }

    public function storeUpdate(Request $request, StockLocation $location)
    {
        $this->authorizeShop($location);
        abort_unless($location->type === 'store', 404);
        return $this->updateLocation($request, $location, 'supply.stores.index', 'Store');
    }

    public function warehouses()
    {
        $this->stock->ensureDefaultLocations($this->shopId());
        $locations = StockLocation::where('shop_id', $this->shopId())->where('type', 'warehouse')->with('warehouseStocks.product')->latest()->get();
        return view('supply.warehouses.index', compact('locations'));
    }

    public function warehouseCreate()
    {
        return view('supply.warehouses.create');
    }

    public function warehouseSave(Request $request)
    {
        return $this->saveLocation($request, 'warehouse', 'supply.warehouses.index', 'Warehouse');
    }

    public function warehouseEdit(StockLocation $location)
    {
        $this->authorizeShop($location);
        abort_unless($location->type === 'warehouse', 404);
        return view('supply.warehouses.edit', compact('location'));
    }

    public function warehouseUpdate(Request $request, StockLocation $location)
    {
        $this->authorizeShop($location);
        abort_unless($location->type === 'warehouse', 404);
        return $this->updateLocation($request, $location, 'supply.warehouses.index', 'Warehouse');
    }

    protected function saveLocation(Request $request, string $type, string $route, string $label)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if (! empty($data['is_default'])) {
            StockLocation::where('shop_id', $this->shopId())->where('type', $type)->update(['is_default' => false]);
        }

        StockLocation::create([
            'shop_id' => $this->shopId(),
            'name' => $data['name'],
            'type' => $type,
            'address' => $data['address'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'is_active' => true,
        ]);

        return redirect()->route($route)->with('success', "{$label} created successfully.");
    }

    protected function updateLocation(Request $request, StockLocation $location, string $route, string $label)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if (! empty($data['is_default'])) {
            StockLocation::where('shop_id', $this->shopId())->where('type', $location->type)->where('id', '!=', $location->id)->update(['is_default' => false]);
        }

        $location->update($data);
        return redirect()->route($route)->with('success', "{$label} updated.");
    }
}
