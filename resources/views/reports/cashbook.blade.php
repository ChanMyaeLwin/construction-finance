<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Cash Book</h1>
        </div>
       
        {{-- Filters --}}
        <form method="GET" class="grid gap-3 sm:grid-cols-6 rounded-2xl border bg-white p-4">
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Project</label>
                <select name="project_id" class="mt-1 w-full rounded-xl border-gray-300">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected($selectedProjectId == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- NEW: Account Code --}}
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Account Code</label>
                <select name="account_code_id" class="select2 mt-1 w-full rounded-xl border-gray-300">
                    <option value="">All Codes</option>
                    @foreach($accountCodes as $ac)
                        <option value="{{ $ac->id }}" @selected(($selectedAccountId ?? null) == $ac->id)>
                            {{ $ac->code }} — {{ $ac->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- NEW: Description contains --}}
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Description contains</label>
                <input type="text"
                       name="q"
                       value="{{ $q }}"
                       placeholder="e.g. diesel, client, supplier"
                       class="mt-1 w-full rounded-xl border-gray-300" />
            </div>

            <div>
                <label class="block text-sm text-gray-600">From</label>
                <input type="date" name="from" value="{{ $from }}" class="mt-1 w-full rounded-xl border-gray-300" />
            </div>
            <div>
                <label class="block text-sm text-gray-600">To</label>
                <input type="date" name="to" value="{{ $to }}" class="mt-1 w-full rounded-xl border-gray-300" />
            </div>

            <div class="flex items-end">
                <button class="w-full px-4 py-2 rounded-xl bg-gray-900 text-white">Apply</button>
            </div>
        </form>
        
        {{-- Summary Cards --}}
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-sm text-gray-500">Total Debit (Current)</div>
                <div class="mt-1 text-2xl font-bold">{{ number_format($totalDebit, 2) }}</div>
            </div>
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-sm text-gray-500">Total Credit (Current)</div>
                <div class="mt-1 text-2xl font-bold">{{ number_format($totalCredit, 2) }}</div>
            </div>
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-sm text-gray-500">Final Balance</div>
                <div class="mt-1 text-2xl font-bold {{ $finalBalance >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    {{ number_format($finalBalance, 2) }}
                </div>
                <div class="text-[11px] text-gray-400 mt-1">
                    = Opening ({{ number_format($openingBalance,2) }}) + Debit − Credit
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="rounded-2xl border bg-white">
            {{-- Desktop/tablet table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2 px-3">Date</th>
                        <th class="px-3">A/Code</th>
                        <th class="px-3">A/C Name</th>
                        <th class="px-3">Description</th>
                        <th class="px-3 text-right">Debit</th>
                        <th class="px-3 text-right">Credit</th>
                        <th class="px-3 text-right">Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{-- Opening balance row --}}
                    <tr class="border-t bg-gray-50">
                        <td class="py-2 px-3" colspan="6">Opening Balance</td>
                        <td class="px-3 text-right font-medium">{{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    @forelse($items as $row)
                        <tr class="border-t">
                            <td class="py-2 px-3">{{ $row['date'] }}</td>
                            <td class="px-3">{{ $row['code'] }}</td>
                            <td class="px-3">{{ $row['name'] }}</td>
                            <td class="px-3">{{ $row['description'] }}</td>
                            <td class="px-3 text-right">{{ $row['debit'] ? number_format($row['debit'],2) : '' }}</td>
                            <td class="px-3 text-right">{{ $row['credit'] ? number_format($row['credit'],2) : '' }}</td>
                            <td class="px-3 text-right font-medium">{{ number_format($row['balance'],2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-4 text-center text-gray-500">No entries.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden p-3 space-y-3">
                <div class="rounded-xl border bg-gray-50 p-3">
                    <div class="flex justify-between text-sm">
                        <div class="text-gray-500">Opening Balance</div>
                        <div class="font-semibold">{{ number_format($openingBalance, 2) }}</div>
                    </div>
                </div>

                @forelse($items as $row)
                    <div class="rounded-xl border p-3">
                        <div class="flex justify-between text-sm">
                            <div class="text-gray-500">{{ $row['date'] }}</div>
                            <div class="font-semibold">{{ number_format($row['balance'], 2) }}</div>
                        </div>
                        <div class="mt-1 text-sm">
                            <div class="text-gray-700">{{ $row['code'] }} — {{ $row['name'] }}</div>
                            <div class="text-gray-500">{{ $row['description'] ?: '—' }}</div>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-lg bg-emerald-50 text-emerald-700 px-2 py-1">
                                Debit: {{ $row['debit'] ? number_format($row['debit'],2) : '0.00' }}
                            </div>
                            <div class="rounded-lg bg-red-50 text-red-700 px-2 py-1 text-right">
                                Credit: {{ $row['credit'] ? number_format($row['credit'],2) : '0.00' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">No entries.</div>
                @endforelse
            </div>
        </div>
    </div>
    <script>
         $(function(){
          $('.select2').select2({ placeholder: 'Choose…', allowClear: true, width: '100%' });
        });
    </script>
</x-app-layout>