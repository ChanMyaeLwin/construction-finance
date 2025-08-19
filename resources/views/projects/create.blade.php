<x-app-layout>
    <div class="max-w-xl mx-auto p-4 sm:p-6">
        <h1 class="text-xl font-bold mb-4">Create Project</h1>

        <form method="POST" action="{{ route('projects.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium">Project Name</label>
                <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500" />
                @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Project Type</label>
                <select name="project_type_id" class="mt-1 w-full rounded-xl border-gray-300">
                    <option value="">Select…</option>
                    @foreach($projectTypes as $t)
                        <option value="{{ $t->id }}" @selected(old('project_type_id')==$t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Location</label>
                <input name="location" value="{{ old('location') }}" class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium">Fixed Amount</label>
                    <input type="number" step="0.01" min="0" name="fixed_amount" value="{{ old('fixed_amount') }}" required class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Progress %</label>
                    <input type="number" min="0" max="100" name="progress_percent" value="{{ old('progress_percent', 0) }}" class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Expected End Date</label>
                    <input type="date" name="expected_end_date" value="{{ old('expected_end_date') }}" class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500" />
                </div>
            </div>

            <div class="pt-2">
                <button class="w-full sm:w-auto px-4 py-2 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">Save</button>
                <a href="{{ route('projects.index') }}" class="ml-2 text-gray-600">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>