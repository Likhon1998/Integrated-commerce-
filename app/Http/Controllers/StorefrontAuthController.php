<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\WebsiteService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StorefrontAuthController extends Controller
{
    public function __construct(private WebsiteService $website) {}

    public function account(): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user?->isStorefrontCustomer()) {
            return redirect()->route('home');
        }

        $shopId = $this->website->shopId();
        $customer = $user->customerProfile;

        $orders = $customer
            ? $customer->orders()
                ->where('shop_id', $shopId)
                ->whereNull('counter_id')
                ->with(['items.product', 'statusLogs'])
                ->latest()
                ->take(20)
                ->get()
            : collect();

        $trackingService = app(\App\Services\OnlineOrderTrackingService::class);
        $orderTracking = $orders->mapWithKeys(fn ($order) => [
            $order->id => $trackingService->trackingPayload($order),
        ]);

        $totalOrders = $orders->count();
        $inTransitOrders = $orders->where('status', 'shipped')->count();
        $deliveredOrders = $orders->where('status', 'completed')->count();
        $refundOrders = $orders->whereIn('status', ['returned', 'refunded'])->count();
        $activeOrder = $orders->first(fn ($order) => in_array($order->status, ['pending', 'processing', 'shipped'], true))
            ?? $orders->first();
        $activeTracking = $activeOrder ? ($orderTracking[$activeOrder->id] ?? null) : null;
        $recentOrders = $orders->take(5);
        $memberSince = ($customer?->created_at ?? $user->created_at)?->format('M j, Y');

        return view('website.account', array_merge($this->website->homepageData(), [
            'customer' => $customer,
            'orders' => $orders,
            'orderTracking' => $orderTracking,
            'totalOrders' => $totalOrders,
            'inTransitOrders' => $inTransitOrders,
            'deliveredOrders' => $deliveredOrders,
            'refundOrders' => $refundOrders,
            'activeOrder' => $activeOrder,
            'activeTracking' => $activeTracking,
            'recentOrders' => $recentOrders,
            'memberSince' => $memberSince,
        ]));
    }

    public function editProfile(): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user?->isStorefrontCustomer()) {
            return redirect()->route('home');
        }

        $shopId = $this->website->shopId();
        $customer = $user->customerProfile;
        $activeOrder = $customer
            ? $customer->orders()
                ->where('shop_id', $shopId)
                ->whereNull('counter_id')
                ->latest()
                ->first()
            : null;
        $memberSince = ($customer?->created_at ?? $user->created_at)?->format('M j, Y');
        $fullName = trim($customer?->name ?? $user->name ?? '');
        $nameParts = preg_split('/\s+/', $fullName, 2) ?: [];
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        return view('website.account-profile', array_merge($this->website->homepageData(), [
            'customer' => $customer,
            'user' => $user,
            'activeOrder' => $activeOrder,
            'memberSince' => $memberSince,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user?->isStorefrontCustomer()) {
            return redirect()->route('home');
        }

        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['nullable', 'string', 'max:60'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone_country_code' => ['required', 'string', 'max:8'],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,prefer_not_to_say'],
            'address' => ['nullable', 'string', 'max:1000'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $fullName = trim($data['first_name'].' '.($data['last_name'] ?? ''));
        $normalizedPhone = preg_replace('/\D+/', '', $data['phone']) ?: $data['phone'];

        $phoneTaken = Customer::where('shop_id', $shopId)
            ->where('phone', $normalizedPhone)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($phoneTaken) {
            return back()->withErrors(['phone' => 'This phone number is already used by another account.'])->withInput();
        }

        $userPayload = [
            'name' => $fullName,
            'email' => $data['email'],
            ...(filled($data['password'] ?? null) ? ['password' => $data['password']] : []),
        ];

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $userPayload['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $userPayload['avatar_path'] = $request->file('avatar')->store('storefront/avatars', 'public');
        }

        $user->update($userPayload);

        $customer = Customer::firstOrCreate(
            ['shop_id' => $shopId, 'user_id' => $user->id],
            [
                'name' => $fullName,
                'email' => $data['email'],
                'phone' => $normalizedPhone,
                'phone_country_code' => $data['phone_country_code'],
                'address' => $data['address'] ?? null,
            ]
        );

        $customer->update([
            'name' => $fullName,
            'email' => $data['email'],
            'phone' => $normalizedPhone,
            'phone_country_code' => $data['phone_country_code'],
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
        ]);

        return redirect()->route('website.account.profile.edit')->with('profile_success', 'Your profile was updated successfully.');
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user?->isStorefrontCustomer()) {
            return redirect()->route('home');
        }

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $customer = $user->customerProfile;
        if ($customer) {
            $customer->update(['user_id' => null]);
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->syncRoles([]);
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('profile_success', 'Your account has been deleted.');
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureLoginNotRateLimited($data['email']);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], true)) {
            RateLimiter::hit($this->throttleKey($data['email']));

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if ($user->is_suspended) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account is suspended. Please contact the store.',
            ]);
        }

        if (! $user->isStorefrontCustomer()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This is a staff account. Please use the admin login instead.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($data['email']));
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => $this->profilePayload($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', Password::defaults()],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $existing = User::where('email', $data['email'])->first();
        if ($existing) {
            $message = $existing->isStorefrontCustomer()
                ? 'You already have an account. Please sign in.'
                : 'This email is already used by a staff account. Use another email.';

            throw ValidationException::withMessages(['email' => $message]);
        }

        if (Customer::where('shop_id', $shopId)->where('phone', $data['phone'])->whereNotNull('user_id')->exists()) {
            throw ValidationException::withMessages(['phone' => 'This phone number is already registered. Please sign in.']);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'shop_id' => $shopId,
            'role' => 'customer',
        ]);

        if (\Spatie\Permission\Models\Role::where('name', 'Customer')->exists()) {
            $user->assignRole('Customer');
        }

        Customer::updateOrCreate(
            ['shop_id' => $shopId, 'phone' => $data['phone']],
            [
                'user_id' => $user->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'address' => $data['address'] ?? null,
            ]
        );

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => $this->profilePayload($user->fresh()),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    protected function profilePayload(User $user): array
    {
        $profile = $user->customerProfile;

        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $profile?->phone ?? '',
            'address' => $profile?->address ?? '',
            'avatar' => $user->avatarUrl() ?? '',
        ];
    }

    protected function ensureLoginNotRateLimited(string $email): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($email), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($email));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(string $email): string
    {
        return Str::transliterate(Str::lower($email).'|storefront|'.request()->ip());
    }
}
