<x-app-layout>
    <div class="max-w-md mx-auto p-4 sm:p-6">
        <h1 class="text-xl font-bold mb-3">Edit Account Code</h1>
        <form method="POST" action="{{ route('account-codes.update', $code) }}" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm">Type</label>
                <select name="account_code_type_id" class="mt-1 w-full rounded-xl border-gray-300 select2">
                    <option value="">— None —</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(old('account_code_type_id', $code->account_code_type_id) == $t->id)>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm">Code</label><input name="code" value="{{ old('code', $code->code) }}" class="mt-1 w-full rounded-xl border-gray-300" required /></div>
            <div><label class="block text-sm">Name</label><input name="name" value="{{ old('name', $code->name) }}" class="mt-1 w-full rounded-xl border-gray-300" required /></div>
            <div><label class="block text-sm">Description</label><input name="description" value="{{ old('description', $code->description) }}" class="mt-1 w-full rounded-xl border-gray-300" /></div>
            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Update</button>
            <a href="{{ route('account-codes.index') }}" class="ml-2 text-gray-600">Cancel</a>
        </form>
    </div>
</x-app-layout>