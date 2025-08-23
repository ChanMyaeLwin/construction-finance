<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6">
        <h1 class="text-xl font-bold mb-3">Expenses</h1>

        <form method="GET" class="grid gap-3 sm:grid-cols-5 mb-4">
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Project</label>
                <select name="project_id" class="select2 mt-1 w-full rounded-xl border-gray-300">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(request('project_id')==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Account Code</label>
                <select name="account_code_id" class="select2 mt-1 w-full rounded-xl border-gray-300">
                    <option value="">All</option>
                    @foreach($accountCodes as $ac)
                    <option value="{{ $ac->id }}" @selected(request('account_code_id')==$ac->id)>{{ $ac->code }} — {{ $ac->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Description contains</label>
                <input type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="e.g. diesel, supplier name"
                    class="mt-1 w-full rounded-xl border-gray-300" />
            </div>
            <div>
                <label class="block text-sm text-gray-600">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-xl border-gray-300" />
            </div>
            <div>
                <label class="block text-sm text-gray-600">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-xl border-gray-300" />
            </div>
            <div class="flex items-end">
                <button class="w-full px-4 py-2 rounded-xl bg-gray-900 text-white">Filter</button>
            </div>
        </form>

        <div class="mb-4">
            <strong>Total Expense:</strong> {{ number_format($totalExpense, 2) }}
        </div>

        <div class="overflow-x-auto rounded-2xl border bg-white">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr>
                        <th class="py-2 px-3">Date</th>
                        <th class="px-3">Project</th>
                        <th class="px-3">Account Code</th>
                        <th class="px-3">Description</th>
                        <th class="px-3 text-right">Amount</th>
                        <th class="px-3 text-right">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $e)
                    <tr class="border-t">
                        <td class="py-2 px-3">{{ $e->expense_date->format('Y-m-d') }}</td>
                        <td class="px-3">{{ $e->project->name }}</td>
                        <td class="px-3">{{ $e->accountCode->code }} <br> {{ $e->accountCode->name }}</td>
                        <td class="px-3">{{ $e->description }}</td>
                        <td class="px-3 text-right">{{ number_format($e->amount,2) }}</td>
                        <td class="px-3 text-right">{{ $e->user->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-gray-500">No data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $expenses->links() }}</div>
    </div>
    <script>
$(function(){
  $('.select2').select2({ placeholder: 'Choose…', allowClear: true, width: '100%' });
});
</script>
</x-app-layout>