@php
    $isWarehouse = str_contains(request()->route()->getName(), 'warehouses');
    $label = $isWarehouse ? 'Warehouse' : 'Store';
    $canAdd = $locations->isEmpty();
@endphp
<x-supply-layout
    :title="$label . ' Settings'"
    :subtitle="$isWarehouse ? 'Single back-room / storage location for this business.' : 'Your one retail store — this system supports a single shop only.'"
    :action-url="$canAdd ? route($isWarehouse ? 'supply.warehouses.create' : 'supply.stores.create') : null"
    :action-label="$canAdd ? '+ Add ' . $label : null">
    @if (! $canAdd)
        <div class="mb-4 rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 text-sm text-indigo-900">
            One {{ strtolower($label) }} per business. Edit the details below — you cannot add another {{ strtolower($label) }}.
        </div>
    @endif
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
                        <td class="p-4"><span class="text-emerald-600 font-bold">Yes</span></td>
                        <td class="p-4">{{ $location->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route($isWarehouse ? 'supply.warehouses.edit' : 'supply.stores.edit', $location) }}" class="text-indigo-600 font-bold">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400">No {{ strtolower($label) }} configured yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-supply-layout>
