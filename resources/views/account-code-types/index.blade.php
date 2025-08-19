<x-app-layout>
    <div class="max-w-3xl mx-auto p-4 sm:p-6">
        @if(session('success')) <div class="mb-3 rounded bg-green-50 text-green-700 p-3">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="mb-3 rounded bg-red-50 text-red-700 p-3">{{ session('error') }}</div> @endif

        <div class="flex items-center justify-between mb-3">
            <h1 class="text-xl font-bold">Account Code Types</h1>
            <a href="{{ route('account-code-types.create') }}" class="px-3 py-2 rounded-xl bg-indigo-600 text-white">+ New</a>
        </div>

        <div class="overflow-x-auto rounded-2xl border bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="p-2">Name</th>
                        <th>Description</th>
                        <th class="w-32"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $t)
                        <tr class="border-t">
                            <td class="p-2 font-medium">{{ $t->name }}</td>
                            <td class="p-2 text-gray-600">{{ $t->description }}</td>
                            <td class="p-2 text-right">
                                <a class="text-indigo-600" href="{{ route('account-code-types.edit', $t) }}">Edit</a>
                                <form action="{{ route('account-code-types.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Delete this type?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">No types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $types->links() }}</div>
    </div>
</x-app-layout>