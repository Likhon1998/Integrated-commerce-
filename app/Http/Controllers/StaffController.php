<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    protected array $adminRoleNames = ['Shop Owner', 'Admin'];

    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (! $shop) {
            abort(403, 'You do not have a shop assigned.');
        }

        // Admins must never stay assigned to a till
        User::where('shop_id', $shop->id)
            ->where(function ($q) {
                $q->whereIn('role', ['admin', 'Admin', 'shop_owner', 'Shop Owner', 'superadmin'])
                    ->orWhereHas('roles', fn ($r) => $r->whereIn('name', $this->adminRoleNames));
            })
            ->whereNotNull('counter_id')
            ->update(['counter_id' => null]);

        $staff = User::where('shop_id', $shop->id)->with(['roles', 'counter'])->latest()->get();
        $roles = $this->assignableRoles();
        $counters = Counter::where('shop_id', $shop->id)->where('is_active', true)->orderBy('name')->get();

        return view('staff.index', compact('staff', 'shop', 'roles', 'counters'));
    }

    public function create()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (! $shop) {
            abort(403, 'No shop assigned.');
        }

        $counters = Counter::where('shop_id', $shop->id)->where('is_active', true)->orderBy('name')->get();
        $roles = $this->assignableRoles();

        return view('staff.create', compact('roles', 'counters', 'shop'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ((int) $request->shop_id !== (int) $user->shop_id) {
            abort(403, 'Unauthorized shop assignment.');
        }

        $data = $this->validatedStaff($request, $user->shop_id);

        if (in_array($data['role'], $this->adminRoleNames, true)) {
            return back()->withInput()->with('error', 'Admin / Shop Owner accounts cannot be created from Staff. They cannot be assigned to a counter.');
        }

        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'shop_id' => $user->shop_id,
            'role' => $data['role'],
            'counter_id' => $data['counter_id'],
        ]);

        $staff->assignRole($data['role']);

        return redirect()->route('staff.index')->with('success', 'Staff created and assigned to counter successfully.');
    }

    public function edit(User $staff)
    {
        if ($staff->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $staff->clearCounterIfAdmin();
        $staff->refresh();

        $roles = $this->assignableRoles();
        $counters = Counter::where('shop_id', $staff->shop_id)->where('is_active', true)->orderBy('name')->get();

        return view('staff.edit', compact('staff', 'roles', 'counters'));
    }

    public function update(Request $request, User $staff)
    {
        if ($staff->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($staff->isAdminUser()) {
            // Admin/owner profile: role locked, always no counter
            $staff->update(['counter_id' => null]);

            return redirect()->route('staff.index')->with('success', 'Admin has no counter assignment (by design).');
        }

        $data = $this->validatedStaff($request, $staff->shop_id, updating: true, staff: $staff);

        if (in_array($data['role'], $this->adminRoleNames, true)) {
            return back()->withInput()->with('error', 'Cannot promote staff to Admin/Shop Owner here.');
        }

        $staff->update([
            'role' => $data['role'],
            'counter_id' => $data['counter_id'],
        ]);

        $staff->syncRoles([$data['role']]);

        return redirect()->route('staff.index')->with('success', 'Staff counter / role updated.');
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

        if ($staff->isAdminUser()) {
            return redirect()->route('staff.index')->with('error', 'Action Denied: You cannot delete an Admin / Shop Owner.');
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

        if ($staff->isAdminUser()) {
            return back()->with('error', 'You cannot suspend an Admin / Shop Owner.');
        }

        $staff->update(['is_suspended' => ! $staff->is_suspended]);

        $status = $staff->is_suspended ? 'suspended and locked out' : 'reactivated';

        return back()->with('success', "Staff member {$staff->name} has been successfully {$status}.");
    }

    protected function assignableRoles()
    {
        return Role::whereNotIn('name', $this->adminRoleNames)->orderBy('name')->get();
    }

    protected function validatedStaff(Request $request, int $shopId, bool $updating = false, ?User $staff = null): array
    {
        $rules = [
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where(fn ($q) => $q->whereNotIn('name', $this->adminRoleNames)),
            ],
            'counter_id' => [
                'required',
                Rule::exists('counters', 'id')->where(fn ($q) => $q->where('shop_id', $shopId)->where('is_active', true)),
            ],
        ];

        if (! $updating) {
            $rules['shop_id'] = 'required|exists:shops,id';
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|string|email|max:255|unique:users,email';
            $rules['password'] = 'required|string|min:8';
        }

        return $request->validate($rules);
    }
}
