<x-supply-layout title="Suppliers" subtitle="Manage vendors for purchase orders." :action-url="route('supply.suppliers.create')" action-label="+ Add Supplier">
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr>
                    <th class="p-4">Name</th>
                    <th class="p-4">Company</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Email</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                @forelse($suppliers as $supplier)
                    <tr>
                        <td class="p-4 font-semibold">{{ $supplier->name }}</td>
                        <td class="p-4 text-gray-500">{{ $supplier->company ?? '—' }}</td>
                        <td class="p-4">{{ $supplier->phone ?? '—' }}</td>
                        <td class="p-4">{{ $supplier->email ?? '—' }}</td>
                        <td class="p-4 text-right space-x-3">
                            <a href="{{ route('supply.suppliers.edit', $supplier) }}" class="text-indigo-600 font-bold">Edit</a>
                            <form action="{{ route('supply.suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Delete supplier?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 font-bold">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400">No suppliers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-supply-layout>
