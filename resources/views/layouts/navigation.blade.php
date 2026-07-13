<div x-show="sidebarOpen" 
     x-transition.opacity 
     class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden" 
     @click="sidebarOpen = false"></div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
       class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto flex flex-col">
    
    <div class="flex items-center justify-center h-16 bg-slate-950 border-b border-slate-800 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-white font-black text-xl tracking-tight">
            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Nexa POS
        </a>
    </div>

    <nav class="p-4 space-y-1.5 flex-1 overflow-y-auto" x-data="{ inventoryOpen: {{ request()->routeIs('brands.*', 'categories.*', 'products.*') && !request()->routeIs('supply.*', 'stock.*') ? 'true' : 'false' }}, supplyOpen: {{ request()->routeIs('supply.*', 'stock.*') ? 'true' : 'false' }}, analyticsOpen: {{ request()->routeIs('analytics.*') ? 'true' : 'false' }}, accountsOpen: {{ request()->routeIs('accounts.*') ? 'true' : 'false' }} }">

        @can('view dashboard')
        <a href="{{ route('dashboard') }}" 
           class="{{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }} flex items-center px-3 py-2.5 rounded-lg transition-all font-medium text-sm">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>
        @endcan

        @can('manage inventory')
        <div class="pt-2">
            <button @click="inventoryOpen = !inventoryOpen"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-all font-medium text-sm {{ request()->routeIs('brands.*', 'categories.*', 'products.*') && !request()->routeIs('supply.*', 'stock.*') ? 'text-white bg-slate-800' : 'hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('brands.*', 'categories.*', 'products.*') && !request()->routeIs('supply.*', 'stock.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Inventory</span>
                </div>
                <svg :class="inventoryOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="inventoryOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">

                <a href="{{ route('brands.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('brands.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Brand</a>
                <a href="{{ route('categories.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('categories.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Category</a>
                <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('products.index', 'products.edit') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Product List</a>
                <a href="{{ route('products.create') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('products.create') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Add Product</a>
                <a href="{{ route('products.import') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('products.import*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Add CSV Product</a>
                <a href="{{ route('products.barcodes') }}" target="_blank" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('products.barcodes') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Print Bar Code</a>
            </div>
        </div>

        <div class="pt-2">
            <button @click="supplyOpen = !supplyOpen" 
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-all font-medium text-sm {{ request()->routeIs('supply.*', 'stock.*') ? 'text-white bg-slate-800' : 'hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('supply.*', 'stock.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Stock & Supply</span>
                </div>
                <svg :class="supplyOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="supplyOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">

                <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors text-slate-400 hover:text-white hover:bg-slate-800">Product Inventory</a>
                <a href="{{ route('supply.opening-inventory.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.opening-inventory.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Opening Inventory</a>
                <a href="{{ route('supply.purchase-orders.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.purchase-orders.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Purchase Order</a>
                <a href="{{ route('supply.reorder-levels.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.reorder-levels.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Reorder Level</a>
                <a href="{{ route('supply.purchase-returns.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.purchase-returns.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Purchase Return</a>
                <a href="{{ route('supply.sales-returns.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.sales-returns.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Sales Return</a>
                <a href="{{ route('supply.adjustments.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.adjustments.*', 'stock.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Stock Adjustment</a>
                <a href="{{ route('supply.stock-transfers.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.stock-transfers.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Stock Transfer</a>
                <a href="{{ route('supply.damage-products.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.damage-products.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Damage Product</a>
                <a href="{{ route('supply.stores.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.stores.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Store</a>
                <a href="{{ route('supply.warehouses.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.warehouses.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Warehouse</a>
                <a href="{{ route('supply.suppliers.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('supply.suppliers.*') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Supplier</a>
            </div>
        </div>
        @endcan

       @can('view sales ledger')
        <a href="{{ route('sales.index') }}" 
           class="{{ request()->routeIs('sales.*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }} flex items-center px-3 py-2.5 rounded-lg transition-all font-medium text-sm">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('sales.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Sales Ledger
        </a>

        @php
            $pendingWebOrders = \App\Models\Order::where('shop_id', Auth::user()->shop_id)
                                ->whereNull('counter_id')
                                ->where('status', 'pending')
                                ->count();
        @endphp
        <a href="{{ route('online-orders.index') }}" 
           class="{{ request()->routeIs('online-orders.*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }} flex items-center justify-between px-3 py-2.5 rounded-lg transition-all font-medium text-sm mt-1.5">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('online-orders.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                Online Orders
            </div>
            @if($pendingWebOrders > 0)
                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">{{ $pendingWebOrders }}</span>
            @endif
        </a>

        <div class="pt-2">
            <button @click="accountsOpen = !accountsOpen" 
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-all font-medium text-sm {{ request()->routeIs('accounts.*') ? 'text-white bg-slate-800' : 'hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('accounts.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>Accounts</span>
                </div>
                <svg :class="accountsOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="accountsOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">

                <a href="{{ route('accounts.opening-balance') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts.opening-balance') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Opening Balance
                </a>
                <a href="{{ route('accounts.chart') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts.chart') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Chart of Accounts
                </a>
                <a href="{{ route('accounts.ledger') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts.ledger') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Ledger
                </a>
                <a href="{{ route('accounts.cash-book') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts.cash-book') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Cash Book
                </a>
                <a href="{{ route('accounts.daily-summary') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts.daily-summary') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Daily Summary
                </a>
                <a href="{{ route('accounts.petty-cash') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts.petty-cash') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Petty Cash
                </a>
                <a href="{{ route('accounts.transfer') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('accounts.transfer') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Account Transfer
                </a>
            </div>
        </div>

        <div class="pt-2">
            <button @click="analyticsOpen = !analyticsOpen" 
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-all font-medium text-sm {{ request()->routeIs('analytics.*') ? 'text-white bg-slate-800' : 'hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('analytics.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Analytics</span>
                </div>
                <svg :class="analyticsOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="analyticsOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="mt-1 ml-4 pl-4 border-l border-slate-700 space-y-1">
                
                <a href="{{ route('analytics.overview') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('analytics.overview') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Overview
                </a>
                <a href="{{ route('analytics.orders') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('analytics.orders') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Orders
                </a>
                <a href="{{ route('analytics.revenue') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('analytics.revenue') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Revenue
                </a>
                <a href="{{ route('analytics.expense') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('analytics.expense') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Expense
                </a>
                <a href="{{ route('analytics.inventory') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('analytics.inventory') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Stock Overview
                </a>
                <a href="{{ route('analytics.balance') }}" 
                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('analytics.balance') ? 'text-white bg-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    Balance
                </a>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-800 mt-2">
            <a href="{{ route('customers.index') }}" 
               class="{{ request()->routeIs('customers.*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }} flex items-center px-3 py-2.5 rounded-lg transition-all font-medium text-sm">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('customers.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Customers
            </a>
        </div>
        @endcan

        @if(Auth::user()->can('manage roles') || Auth::user()->can('manage staff') || Auth::user()->can('manage counters'))
            <div class="pt-2 border-t border-slate-800 mt-2">
                
                @can('manage roles')
                <div class="mb-1.5">
                    <a href="{{ route('roles.index') }}" 
                       class="{{ request()->routeIs('roles.*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }} flex items-center px-3 py-2.5 rounded-lg transition-all font-medium text-sm">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('roles.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Roles
                    </a>
                </div>
                @endcan

                @can('manage staff')
                <div class="mb-1.5">
                    <a href="{{ route('staff.index') }}" 
                       class="{{ request()->routeIs('staff.*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }} flex items-center px-3 py-2.5 rounded-lg transition-all font-medium text-sm">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('staff.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Staff
                    </a>
                </div>
                @endcan

                @can('manage counters')
                <div class="mb-1.5">
                    <a href="{{ route('counters.index') }}" 
                       class="{{ request()->routeIs('counters.*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }} flex items-center px-3 py-2.5 rounded-lg transition-all font-medium text-sm">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('counters.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Counters
                    </a>
                </div>
                @endcan

            </div>
        @endif
    </nav>
</aside>

<header class="fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white border-b border-gray-200 z-30 flex items-center justify-between px-4 sm:px-6">
    
    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>

    <div class="flex items-center gap-3 ml-auto">
        
        @can('process pos sales')
            <a href="{{ route('pos.index') }}" 
               class="hidden sm:flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-sm transition-all hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                POS Terminal
            </a>
        @endcan

        @if(Auth::check())
            <a href="{{ route('home') }}" target="_blank" 
               class="hidden sm:flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-sm transition-all hover:shadow-md">
                🛍️ View My Store
            </a>
        @endif

        <div class="hidden sm:flex sm:items-center pl-2 border-l border-gray-200">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-gray-600 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mr-2">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        {{ __('Profile') }}
                    </x-dropdown-link>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>