<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CounterController extends Controller
{
    public function __construct(protected AccountService $accounts) {}
    public function index()
    {
        $shop = Auth::user()->shop;
        // Fetch counters only for this shop
        $counters = Counter::where('shop_id', $shop->id)->latest()->get();
        
        return view('counters.index', compact('counters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $counter = Counter::create([
            'shop_id' => Auth::user()->shop_id,
            'name' => $request->name,
            'is_active' => true,
        ]);

        $this->accounts->ensureCounterCashAccount($counter);

        return redirect()->route('counters.index')->with('success', 'New POS Terminal added successfully!');
    }

    public function update(Request $request, Counter $counter)
    {
        // Security: Ensure this counter belongs to this shop
        if ($counter->shop_id !== Auth::user()->shop_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $counter->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('counters.index')->with('success', 'Terminal updated successfully!');
    }

    public function destroy(Counter $counter)
    {
        if ($counter->shop_id !== Auth::user()->shop_id) {
            abort(403);
        }

        // Note: Because we set 'nullOnDelete()' in the users migration, 
        // deleting a counter will safely set the employee's counter_id to null instead of deleting the employee!
        $counter->delete();

        return redirect()->route('counters.index')->with('success', 'Terminal removed.');
    }
}