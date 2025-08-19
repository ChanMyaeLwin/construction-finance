<x-app-layout>
    <div class="max-w-3xl mx-auto p-4 sm:p-6">
        <div class="flex items-center justify-between mb-3">
            <h1 class="text-xl font-bold">Expense Codes</h1>
            <a href="{{ route('expense-codes.create') }}" class="px-3 py-2 rounded-xl bg-indigo-600 text-white">+ New</a>
        </div>
        <div class="overflow-x-auto rounded-2xl border bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="p-2">Code</th>
                        <th>Name</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($codes as $c)
                    <tr class="border-t">
                        <td class="p-2">{{ $c->code }}</td>
                        <td>{{ $c->name }}</td>
                        <td class="text-right p-2">
                            <a href="{{ route('expense-codes.edit', $c) }}" class="text-indigo-600">Edit</a>
                            <form action="{{ route('expense-codes.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                @csrf @method('DELETE')
                                <button class="text-red-600 ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $codes->links() }}</div>
    </div>
</x-app-layout>