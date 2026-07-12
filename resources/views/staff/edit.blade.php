<x-app-layout>
    <div class="max-w-4xl mx-auto pt-8 pb-12 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <a href="{{ route('staff.index') }}" class="text-sm font-bold text-indigo-500 hover:text-indigo-600 mb-2 inline-flex items-center gap-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Staff
                </a>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-lg uppercase shadow-inner">
                        {{ substr($staff->name, 0, 1) }}
                    </div>
                    Edit Access: {{ $staff->name }}
                </h2>
            </div>
            <span class="bg-gray-100 text-gray-600 text-xs font-black px-3 py-1.5 rounded-lg uppercase tracking-widest border border-gray-200">
                {{ $staff->email }}
            </span>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-50 p-8">
            <form action="{{ route('staff.update', $staff->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    
                    <div>
                        <label for="role" class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">System Role</label>
                        
                        @if($staff->hasRole('Shop Owner') || $staff->role === 'Shop Owner')
                            <input type="hidden" name="role" value="Shop Owner">
                            <select disabled class="w-full bg-gray-100 border border-gray-200 text-gray-500 text-sm rounded-xl block p-3.5 shadow-sm font-medium cursor-not-allowed">
                                <option value="Shop Owner" selected>Shop Owner</option>
                            </select>
                            <p class="mt-2 text-xs text-amber-600 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                The Shop Owner role cannot be changed.
                            </p>
                        @else
                            <select name="role" id="role" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 shadow-sm font-medium transition-colors" required>
                                @foreach($roles as $role)
                                    @if($role->name !== 'Shop Owner')
                                        <option value="{{ $role->name }}" {{ $staff->hasRole($role->name) || $staff->role === $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-gray-500 font-medium">Determines what permissions this employee has within the POS.</p>
                        @endif
                    </div>

                    <div>
                        <label for="counter_id" class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Assigned Terminal</label>
                        <select name="counter_id" id="counter_id" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 shadow-sm font-medium transition-colors">
                            <option value="">No Counter Assigned (POS Locked)</option>
                            @foreach($counters as $counter)
                                <option value="{{ $counter->id }}" {{ $staff->counter_id == $counter->id ? 'selected' : '' }}>
                                    {{ $counter->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-indigo-600 font-bold">⚠️ A counter MUST be selected to access the POS terminal and make sales.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                    <a href="{{ route('staff.index') }}" class="px-6 py-3 rounded-xl font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 transition border border-gray-200">
                        Cancel
                    </a>
                    <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black text-sm hover:bg-indigo-500 transition shadow-lg shadow-indigo-200 active:scale-95">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>