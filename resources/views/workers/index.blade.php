<x-app-layout>
  <div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">Workers</h1>
      <a href="{{ route('workers.create') }}" class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm">+ New Worker</a>
    </div>

    @if(session('success'))
      <div class="rounded-lg bg-green-50 text-green-800 p-3">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl border bg-white overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2 px-3">Name</th>
            <th class="px-3">Phone</th>
            <th class="px-3">Role</th>
            <th class="px-3 text-right">Basic Salary</th>
            <th class="px-3">Status</th>
            <th class="px-3 text-right w-32">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($workers as $w)
          <tr class="border-t">
            <td class="py-2 px-3">{{ $w->name }}</td>
            <td class="px-3">{{ $w->phone ?: '—' }}</td>
            <td class="px-3">{{ $w->role ?: '—' }}</td>
            <td class="px-3 text-right">{{ $w->basic_salary ? number_format($w->basic_salary,2) : '—' }}</td>
            <td class="px-3">
              <span class="text-xs rounded-full px-2 py-0.5 {{ $w->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $w->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-3 text-right">
              <a href="{{ route('workers.edit', $w) }}" class="text-gray-700">Edit</a>
              <span class="mx-1">·</span>
              <form action="{{ route('workers.destroy', $w) }}" method="POST" class="inline" onsubmit="return confirm('Delete worker?');">
                @csrf @method('DELETE')
                <button class="text-red-600">Delete</button>
              </form>
            </td>
          </tr>
          @empty
            <tr><td colspan="6" class="py-6 text-center text-gray-500">No workers yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div>{{ $workers->links() }}</div>
  </div>
</x-app-layout>