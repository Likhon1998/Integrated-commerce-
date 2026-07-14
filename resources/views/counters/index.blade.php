<x-app-layout>
    <div class="max-w-6xl mx-auto pt-4 pb-12 px-4 sm:px-6 lg:px-8"
         x-data="{
            isEditModalOpen: false,
            editName: '',
            editStatus: true,
            editUrl: '',
            newName: @js(old('name', '')),
            openEdit(name, status, url) {
                this.editName = name;
                this.editStatus = status;
                this.editUrl = url;
                this.isEditModalOpen = true;
            }
         }"
         @keydown.escape.window="isEditModalOpen = false">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Manage Counters</h2>
                <p class="mt-1 text-sm text-slate-500">Create checkout terminals and assign them to staff later.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('counters.sessions.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Cash Sessions
                </a>
                <a href="{{ route('staff.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-bold text-white hover:bg-indigo-700 transition shadow-sm">
                    Assign to Staff
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
        @endif

        @if(($staffWithoutCounter ?? 0) > 0)
            <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                <p class="text-sm font-semibold text-amber-900">
                    {{ $staffWithoutCounter }} staff still need a counter — their POS is locked.
                </p>
                <a href="{{ route('staff.index') }}" class="text-sm font-bold text-indigo-700 hover:underline whitespace-nowrap">Assign now →</a>
            </div>
        @endif

        {{-- Add counter (primary action) --}}
        <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
            <div class="flex items-start gap-4 mb-4">
                <div class="hidden sm:flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Add a new counter</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Give it a clear name your team will recognize on the floor.</p>
                </div>
            </div>

            <form action="{{ route('counters.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <label for="counter-name" class="sr-only">Counter name</label>
                        <input
                            id="counter-name"
                            type="text"
                            name="name"
                            x-model="newName"
                            required
                            maxlength="255"
                            autocomplete="off"
                            placeholder="e.g. Front Desk, Counter 1, Pharmacy"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-3 px-4 font-semibold text-slate-900 placeholder:text-slate-400 placeholder:font-normal"
                        >
                        @error('name')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-sm font-bold px-6 py-3 transition shadow-sm active:scale-[0.98] whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Add Counter
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium text-slate-400 mr-1">Quick names:</span>
                    @foreach(['Counter 1', 'Counter 2', 'Front Desk', 'Drive-Thru', 'Pharmacy'] as $suggestion)
                        <button type="button"
                                @click="newName = '{{ $suggestion }}'"
                                class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-indigo-300 hover:text-indigo-700 hover:bg-indigo-50 transition">
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- Summary chips --}}
        @php
            $total = $counters->count();
            $active = $counters->where('is_active', true)->count();
            $openSessions = $counters->sum('sessions_count');
        @endphp
        <div class="mb-4 flex flex-wrap items-center gap-3 text-sm">
            <span class="font-bold text-slate-800">{{ $total }} {{ Str::plural('counter', $total) }}</span>
            <span class="text-slate-300">·</span>
            <span class="text-slate-500">{{ $active }} active</span>
            @if($openSessions > 0)
                <span class="text-slate-300">·</span>
                <span class="text-emerald-700 font-semibold">{{ $openSessions }} cash session open</span>
            @endif
        </div>

        {{-- Counter cards --}}
        @if($counters->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-400 shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <p class="text-base font-bold text-slate-800">No counters yet</p>
                <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">Add your first terminal above, then assign it to a cashier from Staff Management.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($counters as $counter)
                    <div class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-200 hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $counter->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-900 truncate text-base">{{ $counter->name }}</h4>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                        @if($counter->is_active)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-emerald-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                                                Offline
                                            </span>
                                        @endif

                                        @if(($counter->sessions_count ?? 0) > 0)
                                            <span class="inline-flex items-center rounded-md bg-indigo-50 border border-indigo-100 px-2 py-0.5 text-[11px] font-bold text-indigo-700">
                                                Session open
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button"
                                        @click="openEdit(@js($counter->name), {{ $counter->is_active ? 'true' : 'false' }}, @js(route('counters.update', $counter->id)))"
                                        class="rounded-lg p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                        title="Edit">
                                    <svg class="w-4.5 h-4.5 w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('counters.destroy', $counter->id) }}" method="POST"
                                      onsubmit="return confirm('Delete “{{ addslashes($counter->name) }}”? Staff assigned here will need a new counter before using POS.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 transition disabled:opacity-40 disabled:pointer-events-none"
                                            title="{{ ($counter->sessions_count ?? 0) > 0 ? 'Close cash session first' : 'Delete' }}"
                                            @disabled(($counter->sessions_count ?? 0) > 0)>
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                            <div class="text-sm text-slate-600">
                                <span class="font-bold text-slate-900">{{ $counter->users_count ?? 0 }}</span>
                                {{ Str::plural('staff', $counter->users_count ?? 0) }} assigned
                            </div>
                            <a href="{{ route('staff.index') }}"
                               class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                Manage staff
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Edit modal --}}
        <div x-show="isEditModalOpen"
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden"
                 @click.away="isEditModalOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-3 scale-95">

                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Edit counter</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rename or take this terminal offline.</p>
                    </div>
                    <button type="button" @click="isEditModalOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="editUrl" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">Counter name</label>
                            <input type="text" name="name" x-model="editName" required
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3 font-semibold text-slate-900">
                        </div>

                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3.5 cursor-pointer hover:border-indigo-200 transition">
                            <input type="checkbox" name="is_active" value="1" x-model="editStatus"
                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>
                                <span class="block text-sm font-bold text-slate-900">Counter is active</span>
                                <span class="block text-xs text-slate-500 mt-0.5">Turn off if this register is temporarily unused.</span>
                            </span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 px-5 py-4 bg-slate-50 border-t border-slate-100">
                        <button type="button" @click="isEditModalOpen = false"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit"
                                class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700 shadow-sm">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
