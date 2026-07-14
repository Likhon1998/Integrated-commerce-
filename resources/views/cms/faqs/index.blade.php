<x-cms-layout title="FAQ" subtitle="Q&A accordion on the public /faq page." actionUrl="{{ route('cms.faqs.create') }}" actionLabel="+ Add FAQ" previewUrl="{{ route('website.faqs') }}">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Question</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Order</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($faqs as $faq)
                    <tr>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $faq->question }}</td>
                        <td class="px-5 py-4 text-center text-slate-500">{{ $faq->category ?: '—' }}</td>
                        <td class="px-5 py-4 text-center">{{ $faq->sort_order }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="rounded-full px-2 py-1 text-xs font-bold {{ $faq->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $faq->is_published ? 'Live' : 'Hidden' }}</span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <a href="{{ route('cms.faqs.edit', $faq) }}" class="font-semibold text-indigo-600">Edit</a>
                            <form action="{{ route('cms.faqs.destroy', $faq) }}" method="POST" class="inline" onsubmit="return confirm('Delete FAQ?')">@csrf @method('DELETE')<button class="font-semibold text-rose-600">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">No FAQs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cms-layout>
