<x-app-layout>
    <div class="pt-0 pb-5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @php $user = Auth::user(); @endphp

            <div class="relative bg-slate-900 rounded-2xl overflow-hidden">
                <div class="absolute inset-0 opacity-[0.035]" style="background-image: linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:32px 32px;"></div>
                <div class="absolute top-0 right-0 h-full w-64 bg-gradient-to-l from-indigo-600/10 to-transparent pointer-events-none"></div>

                <div class="relative flex flex-col md:flex-row items-center justify-between px-6 py-5 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-[0.15em]">Store Dashboard</span>
                            <h2 class="text-lg font-bold text-white leading-tight">{{ $shop->name ?? 'Nexa POS' }}</h2>
                            <p class="text-xs text-slate-500">Signed in as {{ $user->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$user->counter_id && !$user->hasRole('Shop Owner') && $user->role !== 'Shop Owner')
                <div class="flex items-center justify-between bg-amber-50 border border-amber-200/70 rounded-xl px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-800">No Sales Counter Assigned</p>
                            <p class="text-xs text-amber-600 mt-0.5">You must be assigned to a sales counter before accessing the POS terminal.</p>
                        </div>
                    </div>
                    <a href="{{ route('staff.index') }}" class="text-xs font-bold text-amber-700 bg-amber-100 hover:bg-amber-200 px-3 py-1.5 rounded-lg transition-colors flex-shrink-0 ml-4">
                        Assign Counter
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Revenue Today</p>
                    <p class="text-[22px] font-black text-gray-900 tracking-tight leading-none">৳{{ number_format($todaySales ?? 0, 2) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Total Customers</p>
                    <p class="text-[22px] font-black text-gray-900 tracking-tight leading-none">{{ number_format($totalCustomers ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Low Stock Items</p>
                    <p class="text-[22px] font-black text-gray-900 tracking-tight leading-none">{{ $lowStockCount ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Inventory Value</p>
                    <p class="text-[22px] font-black text-gray-900 tracking-tight leading-none">৳{{ number_format($inventoryValue ?? 0, 2) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 bg-indigo-500 rounded-full"></div>
                    <h3 class="text-sm font-bold text-slate-800 tracking-tight">Actions</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('pos.index') }}" class="group flex items-center gap-3 px-4 py-3.5 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-xl hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">
                        <span class="text-xs font-bold uppercase tracking-widest">Launch POS Terminal</span>
                    </a>
                    <a href="{{ route('products.index') }}" class="group flex items-center gap-3 px-4 py-3.5 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition-all">
                        <span class="text-xs font-bold uppercase tracking-widest">Manage Products</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <div x-data="{ open: false, message: '', loading: false, history: [] }" class="fixed bottom-6 right-6 z-50">
        <button @click="open = !open" class="bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-full shadow-2xl transition-transform hover:scale-105 flex items-center justify-center border-4 border-indigo-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
        </button>

        <div x-show="open" style="display: none;" class="absolute bottom-20 right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col h-[400px]">
            <div class="bg-slate-900 p-4 text-white flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-sm">Nexa AI Assistant</h3>
                    <p class="text-[10px] text-indigo-300">Powered by Gemini</p>
                </div>
                <button @click="open = false" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <div class="flex-1 p-4 overflow-y-auto bg-slate-50 space-y-4" id="ai-chat-box">
                <div class="bg-indigo-100 text-indigo-800 p-3 rounded-2xl text-sm">Ask about sales, products, or stock.</div>
                <template x-for="chat in history">
                    <div class="flex flex-col space-y-1">
                        <div class="self-end bg-indigo-600 text-white p-3 rounded-2xl text-sm max-w-[85%]" x-text="chat.user"></div>
                        <div class="self-start bg-white border text-gray-700 p-3 rounded-2xl text-sm max-w-[85%]" x-html="chat.ai"></div>
                    </div>
                </template>
            </div>
            <div class="p-3 border-t bg-white">
                <form @submit.prevent="
                    if(message.trim() === '') return;
                    history.push({ user: message, ai: '...' });
                    let currentMsg = message;
                    message = '';
                    loading = true;
                    fetch('{{ route('ai.chat') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ message: currentMsg })
                    })
                    .then(res => res.json())
                    .then(data => { history[history.length - 1].ai = data.reply ? data.reply.replace(/\n/g, '<br>') : 'Sorry, something went wrong.'; loading = false; })
                    .catch(() => { history[history.length - 1].ai = 'Access Denied.'; loading = false; });
                " class="flex gap-2">
                    <input x-model="message" type="text" placeholder="Type your message..." class="w-full text-sm border-gray-200 rounded-xl p-3">
                    <button type="submit" class="bg-indigo-600 text-white px-4 rounded-xl">Send</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
