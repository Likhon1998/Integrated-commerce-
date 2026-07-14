<x-cms-layout title="Blogs" subtitle="Articles on the public /blog page and homepage." actionUrl="{{ route('cms.blogs.create') }}" actionLabel="+ New post" previewUrl="{{ route('website.blogs') }}">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Post</th>
                    <th class="px-5 py-3">Published</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($blogs as $blog)
                    <tr>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $blog->title }}</div>
                            <a href="{{ route('website.blog', $blog->slug) }}" target="_blank" class="text-xs text-indigo-600">/blog/{{ $blog->slug }}</a>
                        </td>
                        <td class="px-5 py-4 text-center text-slate-500">{{ optional($blog->published_at)->format('M d, Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="rounded-full px-2 py-1 text-xs font-bold {{ $blog->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $blog->is_published ? 'Live' : 'Draft' }}</span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <a href="{{ route('cms.blogs.edit', $blog) }}" class="font-semibold text-indigo-600">Edit</a>
                            <form action="{{ route('cms.blogs.destroy', $blog) }}" method="POST" class="inline" onsubmit="return confirm('Delete post?')">@csrf @method('DELETE')<button class="font-semibold text-rose-600">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-12 text-center text-slate-400">No blog posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $blogs->links() }}</div>
    </div>
</x-cms-layout>
