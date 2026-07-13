@if (session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl font-semibold text-sm">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-800 rounded-xl font-semibold text-sm">
        {{ session('error') }}
    </div>
@endif
