<x-app-layout>
    <div class="max-w-4xl mx-auto pt-8 pb-12 px-4 sm:px-6">
        
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-3xl font-black text-slate-900">Add Staff Credential</h2>
            <a href="{{ route('staff.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">Back to List</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-6 py-5 rounded-2xl shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="font-black text-sm uppercase tracking-widest text-red-800">Please fix these errors:</p>
                </div>
                <ul class="list-disc pl-6 text-sm font-bold text-red-600">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-[24px] shadow-sm border border-gray-200 p-8">
            <form action="{{ route('staff.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-100">
                    
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-2">Shop Assignment (Locked) *</label>
                        <div class="relative group">
                            <div class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 font-bold text-gray-500 flex items-center gap-3 cursor-not-allowed">
                                <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                                {{ $shop->name }}
                            </div>
                            <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-2">Assign to Counter</label>
                        <select name="counter_id" class="w-full rounded-xl border-gray-300 font-bold focus:ring-indigo-500 py-3.5">
                            <option value="">No Specific Counter (Floating)</option>
                            @foreach($counters as $counter)
                                <option value="{{ $counter->id }}" {{ old('counter_id') == $counter->id ? 'selected' : '' }}>
                                    📍 {{ $counter->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-2">Employee Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} py-3.5 font-bold focus:ring-indigo-500" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-2">System Role *</label>
                        <select name="role" required class="w-full rounded-xl {{ $errors->has('role') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} py-3.5 font-bold focus:ring-indigo-500">
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-2">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} py-3.5 font-bold focus:ring-indigo-500" placeholder="staff@example.com">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-2">Password *</label>
                        <input type="password" name="password" required class="w-full rounded-xl {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} py-3.5 font-bold focus:ring-indigo-500" placeholder="••••••••">
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="bg-indigo-600 text-white px-8 py-3.5 rounded-xl font-black shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 transition active:scale-95 flex items-center gap-2 ml-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create Staff Credential
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>