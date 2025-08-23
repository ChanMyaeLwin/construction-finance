<x-app-layout>
  <div class="max-w-xl mx-auto p-4 sm:p-6 space-y-4">
    <h1 class="text-xl font-bold">Edit Worker</h1>

    <form method="POST" action="{{ route('workers.update', $worker) }}" class="space-y-3 rounded-2xl border bg-white p-4">
      @csrf @method('PUT')

      <div>
        <label class="block text-sm text-gray-600">Name</label>
        <input name="name"
               value="{{ old('name', $worker->name) }}"
               class="mt-1 w-full rounded-xl border-gray-300"
               required />
      </div>

      <div class="grid sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm text-gray-600">Phone</label>
          <input name="phone"
                 value="{{ old('phone', $worker->phone) }}"
                 class="mt-1 w-full rounded-xl border-gray-300" />
        </div>
        <div>
          <label class="block text-sm text-gray-600">Role</label>
          <input name="role"
                 value="{{ old('role', $worker->role) }}"
                 class="mt-1 w-full rounded-xl border-gray-300" />
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm text-gray-600">Basic Salary</label>
          <input type="number" step="0.01" min="0" name="basic_salary"
                 value="{{ old('basic_salary', $worker->basic_salary) }}"
                 class="mt-1 w-full rounded-xl border-gray-300" />
        </div>
        <div class="flex items-end gap-2">
          <input type="checkbox" name="is_active" value="1"
                 {{ old('is_active', $worker->is_active) ? 'checked' : '' }} />
          <label class="text-sm text-gray-700">Active</label>
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <a href="{{ route('workers.index') }}" class="px-3 py-2 rounded-xl bg-gray-100">Cancel</a>
        <button class="px-3 py-2 rounded-xl bg-indigo-600 text-white">Update</button>
      </div>
    </form>
  </div>
</x-app-layout>