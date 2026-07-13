@php
    $fields = [
        ['name' => 'name', 'label' => 'Supplier Name', 'required' => true],
        ['name' => 'company', 'label' => 'Company'],
        ['name' => 'phone', 'label' => 'Phone'],
        ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
        ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
    ];
@endphp
<x-supply-layout :title="isset($supplier) ? 'Edit Supplier' : 'Add Supplier'" :subtitle="'Vendor details for procurement.'">
    <form method="POST" action="{{ isset($supplier) ? route('supply.suppliers.update', $supplier) : route('supply.suppliers.store') }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4 max-w-2xl">
        @csrf
        @if(isset($supplier)) @method('PUT') @endif
        @foreach($fields as $field)
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ $field['label'] }}</label>
                @if(($field['type'] ?? '') === 'textarea')
                    <textarea name="{{ $field['name'] }}" class="w-full rounded-xl border-gray-200" rows="3">{{ old($field['name'], $supplier->{$field['name']} ?? '') }}</textarea>
                @else
                    <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" value="{{ old($field['name'], $supplier->{$field['name']} ?? '') }}" class="w-full rounded-xl border-gray-200" @if($field['required'] ?? false) required @endif>
                @endif
            </div>
        @endforeach
        @if(isset($supplier))
            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active))> Active
            </label>
        @endif
        <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold">Save Supplier</button>
    </form>
</x-supply-layout>
