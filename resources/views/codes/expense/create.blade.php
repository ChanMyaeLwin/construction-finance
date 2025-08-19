<x-app-layout>
    <div class="max-w-md mx-auto p-4 sm:p-6">
        <h1 class="text-xl font-bold mb-3">New Expense Code</h1>
        <form method="POST" action="{{ route('expense-codes.store') }}" class="space-y-3">
            @csrf
            <div><label class="block text-sm">Code</label><input name="code" class="mt-1 w-full rounded-xl border-gray-300" required /></div>
            <div><label class="block text-sm">Name</label><input name="name" class="mt-1 w-full rounded-xl border-gray-300" required /></div>
            <div><label class="block text-sm">Description</label><input name="description" class="mt-1 w-full rounded-xl border-gray-300" /></div>
            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Save</button>
            <a href="{{ route('expense-codes.index') }}" class="ml-2 text-gray-600">Cancel</a>
        </form>
    </div>
</x-app-layout>