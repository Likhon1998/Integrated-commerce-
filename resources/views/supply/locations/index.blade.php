@php $isWarehouse = str_contains(request()->route()->getName(), 'warehouses'); $type = $isWarehouse ? 'warehouse' : 'store'; $label = $isWarehouse ? 'Warehouse' : 'Store'; @endphp
<x-supply-layout :title="$label . 's'" :subtitle="'Retail and storage locations for stock tracking.'" :action-url="route($isWarehouse ? 'supply.warehouses.create' : 'supply.stores.create')" :action-label="'+ Add ' . $label">
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4">Name</th><th class="p-4">Address</th><th class="p-4">Default</th><th class="p-4">Status</th><th class="p-4 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($locations as $location)
                    <tr>
                        <td class="p-4 font-semibold">{{ $location->name }}</td>
                        <td class="p-4 text-gray-500">{{ $location->address ?? '—' }}</td>
                        <td class="p-4">@if($location->is_default)<span class="text-emerald-600 font-bold">Yes</span>@else — @endif</td>
                        <td class="p-4">{{ $location->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route($isWarehouse ? 'supply.warehouses.edit' : 'supply.stores.edit', $location) }}" class="text-indigo-600 font-bold">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400">No locations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-supply-layout>
