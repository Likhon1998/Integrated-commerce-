@extends('website.layout')

@section('title', 'Edit Profile — ' . ($settings->store_name ?? 'Store'))

@section('content')
@php
    $avatarUrl = $user->avatarUrl();
    $avatarInitials = $user->avatarInitials();
    $countryCode = old('phone_country_code', $customer?->phone_country_code ?? '+880');
    $phoneNumber = old('phone', $customer?->phone ?? '');
    $gender = old('gender', $customer?->gender ?? 'male');
    $dob = old('date_of_birth', $customer?->date_of_birth?->format('Y-m-d') ?? '');
    $previewName = trim(old('first_name', $firstName).' '.old('last_name', $lastName)) ?: ($customer?->name ?? $user->name);
    $previewEmail = old('email', $user->email);
    $previewPhone = trim($countryCode.' '.$phoneNumber);
@endphp

<div class="max-w-[1280px] mx-auto px-4 md:px-5 py-5"
     x-data="{
        preview: @js($avatarUrl),
        initials: @js($avatarInitials),
        previewName: @js($previewName),
        previewEmail: @js($previewEmail),
        previewPhone: @js($previewPhone),
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        deleteOpen: @json($errors->has('password')),
        scrollToPassword() {
            document.getElementById('change-password')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
     }">
    <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-[12px] text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
        <span>›</span>
        <a href="{{ route('website.account') }}" class="hover:text-blue-600">My Account</a>
        <span>›</span>
        <span class="font-semibold text-slate-800">Edit Profile</span>
    </nav>

    <div class="mb-5">
        <h1 class="text-[28px] font-extrabold tracking-tight text-slate-900">Edit Profile</h1>
        <p class="mt-1 text-[13px] text-slate-500">Update your personal information and account details.</p>
    </div>

    @if(session('profile_success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-semibold text-emerald-700">
            {{ session('profile_success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[12px] text-rose-700">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 xl:grid-cols-[250px_minmax(0,1fr)_300px]">
        @include('website.partials.account-sidebar', ['activeMenu' => 'edit-profile', 'customer' => $customer, 'activeOrder' => $activeOrder])

        <div>
            <form id="profile-form" method="POST" action="{{ route('website.account.profile.update') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PUT')
                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden"
                    @change="const file = $event.target.files[0]; if (file) preview = URL.createObjectURL(file);">

                <div class="border-b border-slate-100 pb-5">
                    <h2 class="text-[16px] font-bold text-slate-900">Personal Information</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="first_name" class="mb-1.5 block text-[12px] font-semibold text-slate-700">First Name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $firstName) }}" required
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] focus:border-blue-400 focus:ring-blue-400"
                                @input="previewName = ($event.target.value + ' ' + (document.getElementById('last_name')?.value || '')).trim()">
                        </div>
                        <div>
                            <label for="last_name" class="mb-1.5 block text-[12px] font-semibold text-slate-700">Last Name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $lastName) }}"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] focus:border-blue-400 focus:ring-blue-400"
                                @input="previewName = ((document.getElementById('first_name')?.value || '') + ' ' + $event.target.value).trim()">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="email" class="mb-1.5 block text-[12px] font-semibold text-slate-700">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] focus:border-blue-400 focus:ring-blue-400"
                            @input="previewEmail = $event.target.value">
                    </div>

                    <div class="mt-4">
                        <label for="phone" class="mb-1.5 block text-[12px] font-semibold text-slate-700">Phone Number</label>
                        <div class="flex overflow-hidden rounded-lg border border-slate-200 focus-within:border-blue-400 focus-within:ring-1 focus-within:ring-blue-400">
                            <select name="phone_country_code" id="phone_country_code"
                                class="border-0 bg-slate-50 px-2.5 py-2.5 text-[13px] text-slate-700 focus:ring-0"
                                @change="previewPhone = ($event.target.value + ' ' + (document.getElementById('phone')?.value || '')).trim()">
                                <option value="+880" @selected($countryCode === '+880')>🇧🇩 +880</option>
                                <option value="+1" @selected($countryCode === '+1')>🇺🇸 +1</option>
                                <option value="+44" @selected($countryCode === '+44')>🇬🇧 +44</option>
                                <option value="+91" @selected($countryCode === '+91')>🇮🇳 +91</option>
                                <option value="+971" @selected($countryCode === '+971')>🇦🇪 +971</option>
                            </select>
                            <input id="phone" name="phone" type="text" value="{{ $phoneNumber }}" required placeholder="Phone number"
                                class="min-w-0 flex-1 border-0 px-3 py-2.5 text-[13px] focus:ring-0"
                                @input="previewPhone = ((document.getElementById('phone_country_code')?.value || '') + ' ' + $event.target.value).trim()">
                        </div>
                        @error('phone')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4">
                        <label for="date_of_birth" class="mb-1.5 block text-[12px] font-semibold text-slate-700">Date of Birth</label>
                        <div class="relative">
                            <input id="date_of_birth" name="date_of_birth" type="date" value="{{ $dob }}"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-10 text-[13px] focus:border-blue-400 focus:ring-blue-400">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="mb-2 text-[12px] font-semibold text-slate-700">Gender</p>
                        <div class="flex flex-wrap gap-5 text-[13px] text-slate-700">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="gender" value="male" @checked($gender === 'male') class="text-blue-600 focus:ring-blue-500">
                                Male
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="gender" value="female" @checked($gender === 'female') class="text-blue-600 focus:ring-blue-500">
                                Female
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="gender" value="prefer_not_to_say" @checked($gender === 'prefer_not_to_say') class="text-blue-600 focus:ring-blue-500">
                                Prefer not to say
                            </label>
                        </div>
                    </div>
                </div>

                <div id="change-password" class="border-b border-slate-100 py-5">
                    <h2 class="text-[16px] font-bold text-slate-900">Change Password <span class="text-[12px] font-medium text-slate-400">(Optional)</span></h2>
                    <div class="mt-4">
                        <label for="current_password" class="mb-1.5 block text-[12px] font-semibold text-slate-700">Current Password</label>
                        <div class="relative">
                            <input id="current_password" name="current_password" :type="showCurrent ? 'text' : 'password'"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-10 text-[13px] focus:border-blue-400 focus:ring-blue-400">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" @click="showCurrent = !showCurrent">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        @error('current_password')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="password" class="mb-1.5 block text-[12px] font-semibold text-slate-700">New Password</label>
                            <div class="relative">
                                <input id="password" name="password" :type="showNew ? 'text' : 'password'"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-10 text-[13px] focus:border-blue-400 focus:ring-blue-400">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" @click="showNew = !showNew">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-[12px] font-semibold text-slate-700">Confirm New Password</label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation" :type="showConfirm ? 'text' : 'password'"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-10 text-[13px] focus:border-blue-400 focus:ring-blue-400">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" @click="showConfirm = !showConfirm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Password must be at least 8 characters long
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-5">
                    <button type="submit" class="gaget-btn-primary px-6 py-2.5 text-[13px]">Save Changes</button>
                    <a href="{{ route('website.account') }}" class="gaget-btn-outline px-6 py-2.5 text-[13px]">Cancel</a>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm text-center">
                <div class="relative mx-auto h-28 w-28">
                    <div class="h-28 w-28 overflow-hidden rounded-full border-4 border-white bg-blue-100 shadow-md">
                        <template x-if="preview">
                            <img :src="preview" alt="Profile preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <div class="flex h-full w-full items-center justify-center text-2xl font-extrabold text-blue-700" x-text="initials"></div>
                        </template>
                    </div>
                    <button type="button" @click="document.getElementById('avatar').click()"
                        class="absolute bottom-1 right-1 flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white shadow hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
                <h3 class="mt-4 text-[16px] font-bold text-slate-900" x-text="previewName"></h3>
                <p class="mt-1 text-[12px] text-slate-500" x-text="previewEmail"></p>
                <p class="mt-0.5 text-[12px] text-slate-600" x-text="previewPhone"></p>
                <p class="mt-3 inline-flex items-center gap-1.5 text-[11px] text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Member since {{ $memberSince }}
                </p>
                <button type="button" @click="document.getElementById('avatar').click()"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">
                    Change Profile Picture
                </button>
                @if($avatarUrl)
                    <label class="mt-2 inline-flex items-center gap-2 text-[11px] text-slate-500">
                        <input type="checkbox" form="profile-form" name="remove_avatar" value="1" class="rounded border-slate-300 text-blue-600" @change="preview = $event.target.checked ? null : @js($avatarUrl)">
                        Remove photo
                    </label>
                @endif
                @error('avatar')<p class="mt-2 text-[11px] text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-[15px] font-bold text-slate-900">Account Security</h2>
                <p class="mt-1 text-[11px] text-slate-500">Manage your account security settings</p>
                <div class="mt-3 divide-y divide-slate-100">
                    <button type="button" @click="scrollToPassword()" class="flex w-full items-center justify-between py-3 text-left text-[13px] font-medium text-slate-700 hover:text-blue-600">
                        <span class="inline-flex items-center gap-2.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Change Password
                        </span>
                        <span class="text-slate-300">›</span>
                    </button>
                    <div class="flex items-center justify-between py-3 text-[13px] text-slate-400">
                        <span class="inline-flex items-center gap-2.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Two-Factor Authentication
                        </span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold">Soon</span>
                    </div>
                    <div class="py-3">
                        <p class="inline-flex items-center gap-2.5 text-[13px] font-medium text-slate-700">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Login Activity
                        </p>
                        <p class="mt-2 pl-6 text-[11px] text-slate-500">Last profile update: {{ $user->updated_at->format('M j, Y g:i A') }}</p>
                        <p class="pl-6 text-[11px] text-slate-500">Account created: {{ $memberSince }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-rose-100 bg-rose-50/40 p-5 shadow-sm">
                <p class="text-[12px] leading-relaxed text-rose-700">
                    Once you delete your account, there is no going back. Your profile will be removed and you will lose access to order history in your dashboard.
                </p>
                <button type="button" @click="deleteOpen = true"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-rose-300 bg-white px-4 py-2.5 text-[13px] font-semibold text-rose-600 hover:bg-rose-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete My Account
                </button>
            </div>
        </div>
    </div>

    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" @keydown.escape.window="deleteOpen = false">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl" @click.outside="deleteOpen = false">
            <h3 class="text-[18px] font-bold text-slate-900">Delete Account</h3>
            <p class="mt-2 text-[13px] text-slate-600">Enter your password to permanently delete your account.</p>
            <form method="POST" action="{{ route('website.account.profile.destroy') }}" class="mt-4 space-y-3">
                @csrf
                @method('DELETE')
                <input type="password" name="password" required placeholder="Current password"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] focus:border-rose-400 focus:ring-rose-400">
                @error('password')<p class="text-[11px] text-rose-600">{{ $message }}</p>@enderror
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 rounded-lg bg-rose-600 px-4 py-2.5 text-[13px] font-semibold text-white hover:bg-rose-700">Delete Account</button>
                    <button type="button" @click="deleteOpen = false" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-[13px] font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
