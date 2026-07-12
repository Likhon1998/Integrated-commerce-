<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Counter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            abort(403, 'You do not have a shop assigned.');
        }

        $staff = User::where('shop_id', $shop->id)->with(['roles', 'counter'])->latest()->get();
        $roles = Role::whereNotIn('name', ['Shop Owner'])->get();
        $counters = Counter::where('shop_id', $shop->id)->where('is_active', true)->get();

        return view('staff.index', compact('staff', 'shop', 'roles', 'counters'));
    }

    public function create()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            abort(403, 'No shop assigned.');
        }

        $counters = Counter::where('shop_id', $shop->id)->where('is_active', true)->get();
        $roles = Role::whereNotIn('name', ['Shop Owner'])->get();

        return view('staff.create', compact('roles', 'counters', 'shop'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($request->shop_id != $user->shop_id) {
            abort(403, 'Unauthorized shop assignment.');
        }

        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'counter_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value && !Counter::where('id', $value)->where('shop_id', $user->shop_id)->exists()) {
                        $fail('The selected counter does not belong to this shop.');
                    }
                },
            ],
        ]);

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'shop_id' => $user->shop_id,
            'role' => $request->role,
            'counter_id' => $request->counter_id ?: null,
        ]);

        $staff->assignRole($request->role);

        return redirect()->route('staff.index')->with('success', 'Staff member added successfully!');
    }

    public function edit(User $staff)
    {
        if ($staff->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::whereNotIn('name', ['Shop Owner'])->get();
        $counters = Counter::where('shop_id', $staff->shop_id)->where('is_active', true)->get();

        return view('staff.edit', compact('staff', 'roles', 'counters'));
    }

    public function update(Request $request, User $staff)
    {
        if ($staff->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'counter_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($staff) {
                    if ($value && !Counter::where('id', $value)->where('shop_id', $staff->shop_id)->exists()) {
                        $fail('The selected counter does not belong to this shop.');
                    }
                },
            ],
        ]);

        $staff->update([
            'role' => $request->role,
            'counter_id' => $request->counter_id ?: null,
        ]);

        $staff->syncRoles([$request->role]);

        return redirect()->route('staff.index')->with('success', 'Staff access updated successfully!');
    }

    public function destroy(User $staff)
    {
        $user = Auth::user();

        if ($staff->shop_id !== $user->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($staff->id === $user->id) {
            return redirect()->route('staff.index')->with('error', 'You cannot delete your own account.');
        }

        if ($staff->hasRole('Shop Owner') || $staff->role === 'Shop Owner') {
            return redirect()->route('staff.index')->with('error', 'Action Denied: You cannot delete a Shop Owner.');
        }

        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff member access revoked.');
    }

    public function toggleSuspend(User $staff)
    {
        $user = Auth::user();

        if ($staff->shop_id !== $user->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($staff->id === $user->id) {
            return back()->with('error', 'You cannot suspend your own account!');
        }

        $staff->update(['is_suspended' => !$staff->is_suspended]);

        $status = $staff->is_suspended ? 'suspended and locked out' : 'reactivated';

        return back()->with('success', "Staff member {$staff->name} has been successfully {$status}.");
    }
}
