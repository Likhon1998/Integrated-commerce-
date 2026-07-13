<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ShopScoped;

    public function __construct(protected StockService $stock) {}

    public function index()
    {
        $suppliers = Supplier::where('shop_id', $this->shopId())->latest()->get();
        return view('supply.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('supply.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        Supplier::create(array_merge($data, ['shop_id' => $this->shopId(), 'is_active' => true]));
        return redirect()->route('supply.suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function edit(Supplier $supplier)
    {
        $this->authorizeShop($supplier);
        return view('supply.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizeShop($supplier);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $supplier->update($data);
        return redirect()->route('supply.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorizeShop($supplier);
        $supplier->delete();
        return redirect()->route('supply.suppliers.index')->with('success', 'Supplier removed.');
    }
}
