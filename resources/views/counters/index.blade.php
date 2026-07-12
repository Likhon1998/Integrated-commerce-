<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8"
         x-data="{ isEditModalOpen: false, editName: '', editStatus: false, editUrl: '' }"
         @keydown.escape.window="isEditModalOpen = false">
        
        <div class="mb-8 mt-4">
            <h2 class="text-3xl font-black text-gray-950 tracking-tight">POS Terminals</h2>
            <p class="mt-1 text-sm text-gray-500 font-medium leading-relaxed">Manage your physical checkout counters and registers.</p>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 p-5 mb-8 rounded-2xl shadow-sm flex items-center gap-4">
                <div class="bg-emerald-100/50 p-2 rounded-full text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-bold text-emerald-900">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 p-8 sticky top-8">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">Add New Terminal</h3>
                    <p class="text-xs text-gray-500 font-medium mb-6">Create a new register to assign your staff to.</p>

                    <form action="{{ route('counters.store') }}" method="POST">
                        @csrf
                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Terminal Name</label>
                            <input type="text" name="name" required placeholder="e.g. Register 1, Drive-Thru"
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 transition font-bold text-gray-800">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full py-3 text-sm font-black text-white bg-slate-900 rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-900/20 active:scale-95">
                            Create Terminal
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-50">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] border-b border-gray-100">Terminal Name</th>
                                <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] border-b border-gray-100 text-center">Status</th>
                                <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] border-b border-gray-100 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($counters as $counter)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="p-6 font-black text-gray-900 text-base flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full {{ $counter->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                                        {{ $counter->name }}
                                    </td>
                                    <td class="p-6 text-center">
                                        @if($counter->is_active)
                                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest">Active</span>
                                        @else
                                            <span class="bg-red-50 text-red-700 border border-red-200 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest">Offline</span>
                                        @endif
                                    </td>
                                    <td class="p-6 text-right flex justify-end gap-2">
                                        <button type="button" 
                                                @click="editName = '{{ addslashes($counter->name) }}'; editStatus = {{ $counter->is_active ? 'true' : 'false' }}; editUrl = '{{ route('counters.update', $counter->id) }}'; isEditModalOpen = true;" 
                                                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Terminal">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>

                                        <form action="{{ route('counters.destroy', $counter->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this terminal? Employees assigned here will be switched to Floating access.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Terminal">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-16 text-center">
                                        <p class="text-gray-900 font-bold text-lg mb-1">No Terminals Found</p>
                                        <p class="text-gray-500 font-medium text-sm">Use the form on the left to add your first cash register.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="isEditModalOpen" 
             style="display: none;" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 backdrop-blur-md"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md flex flex-col relative overflow-hidden ring-1 ring-gray-100" 
                 @click.away="isEditModalOpen = false"
                 x-transition:enter="transition ease-out duration-300 delay-100"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                 
                <div class="p-6 md:p-8 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-950 flex items-center gap-2.5">Edit Terminal</h3>
                        <p class="text-xs font-bold text-gray-500 mt-1">Update name or toggle offline status.</p>
                    </div>
                    <button @click="isEditModalOpen = false" type="button" class="p-2 bg-white rounded-full border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all shadow-sm active:scale-95">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                 
                <form :action="editUrl" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 md:p-8">
                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Terminal Name</label>
                            <input type="text" name="name" x-model="editName" required
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 transition font-bold text-gray-800">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input type="checkbox" name="is_active" value="1" :checked="editStatus"
                                           class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 transition shadow-sm cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label class="font-bold text-gray-900">Terminal is Active</label>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">Uncheck this to mark the register as offline/closed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" @click="isEditModalOpen = false" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition shadow-sm">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-black text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20">
                            Save Changes
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-app-layout>