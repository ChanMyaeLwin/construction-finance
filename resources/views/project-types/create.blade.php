<x-app-layout>
    <div class="max-w-md mx-auto p-4 sm:p-6">
        <h1 class="text-xl font-bold mb-3">New Project Type</h1>
        <form method="POST" action="{{ route('project-types.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm">Name</label>
                <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border-gray-300" />
                @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm">Description</label>
                <input name="description" value="{{ old('description') }}" class="mt-1 w-full rounded-xl border-gray-300" />
            </div>
            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Save</button>
            <a href="{{ route('project-types.index') }}" class="ml-2 text-gray-600">Cancel</a>
        </form>
    </div>
</x-app-layout>