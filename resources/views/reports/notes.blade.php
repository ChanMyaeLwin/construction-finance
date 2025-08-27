<x-app-layout>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Notes (Monthly by Account Code)</h1>
            <a href="{{ route('reports.summary') }}" class="text-sm text-gray-600">← Back to Reports</a>
        </div>

        {{-- Filters (unchanged) --}}
        <form method="GET" class="grid gap-3 sm:grid-cols-6 rounded-2xl border bg-white p-4">
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Project</label>
                <select name="project_id" class="mt-1 w-full rounded-xl border-gray-300">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
        <label class="block text-sm text-gray-600">Account Code</label>
        <select name="account_code_id"
                class="mt-1 w-full rounded-xl border-gray-300 js-select2"
                data-placeholder="All Accounts">
            <option value=""></option>
            @foreach($accountCodes as $ac)
                <option value="{{ $ac->id }}" @selected((string)request('account_code_id') === (string)$ac->id)>
                    {{ $ac->code }} — {{ $ac->name }}
                </option>
            @endforeach
        </select>
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
                <button class="w-full px-4 py-2 rounded-xl bg-gray-900 text-white">Apply</button>
            </div>
        </form>

        {{-- Desktop / Tablet --}}
        <div class="hidden md:block rounded-2xl border bg-white overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2 px-3 sticky left-0 bg-white z-10">A/Code</th>
                        <th class="px-3 sticky left-28 bg-white z-10">A/C Name</th>
                        @foreach($monthKeys as $ym)
                            <th class="px-3 text-right">{{ $monthLabels[$ym] }}</th>
                        @endforeach
                        <th class="px-3 text-right">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($groups as $typeName => $group)
                        {{-- Type header row --}}
                        <tr class="border-t bg-gray-50">
                            <td class="py-2 px-3 font-semibold sticky left-0 bg-gray-50" colspan="{{ 2 + count($monthKeys) + 1 }}">
                                {{ $typeName }}
                            </td>
                        </tr>

                        {{-- Rows for this type --}}
                        @foreach($group['rows'] as $r)
                            <tr class="border-t">
                                <td class="py-2 px-3 sticky left-0 bg-white">{{ $r['code'] }}</td>
                                <td class="px-3 sticky left-28 bg-white">{{ $r['name'] }}</td>
                                @foreach($monthKeys as $ym)
                                    <td class="px-3 text-right">
                                        {{ $r['months'][$ym] ? number_format($r['months'][$ym], 2) : '' }}
                                    </td>
                                @endforeach
                                <td class="px-3 text-right font-semibold">{{ number_format($r['row_total'], 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Type subtotal --}}
                        <tr class="border-t bg-indigo-50/60">
                            <td class="py-2 px-3 font-medium sticky left-0 bg-indigo-50/60">Subtotal — {{ $typeName }}</td>
                            <td class="px-3 sticky left-28 bg-indigo-50/60"></td>
                            @foreach($monthKeys as $ym)
                                <td class="px-3 text-right">{{ number_format($group['totals'][$ym] ?? 0, 2) }}</td>
                            @endforeach
                            <td class="px-3 text-right font-semibold">{{ number_format($group['type_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 2 + count($monthKeys) + 1 }}" class="py-4 text-center text-gray-500">No data.</td></tr>
                    @endforelse
                </tbody>

             
            </table>
        </div>

        {{-- Mobile (cards) --}}
        <div class="md:hidden space-y-4">
            @forelse($groups as $typeName => $group)
                <div class="rounded-2xl border bg-white">
                    <div class="px-3 py-2 border-b font-semibold">{{ $typeName }}</div>

                    <div class="p-3 space-y-3">
                        @foreach($group['rows'] as $r)
                            <div class="rounded-xl border p-3">
                                <div class="flex justify-between">
                                    <div class="font-medium">{{ $r['code'] }}</div>
                                    <div class="font-semibold">{{ number_format($r['row_total'], 2) }}</div>
                                </div>
                                <div class="text-sm text-gray-600">{{ $r['name'] }}</div>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    @foreach($monthKeys as $ym)
                                        @php $v = $r['months'][$ym]; @endphp
                                        @if($v)
                                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-2 py-1">
                                                <div class="text-xs text-gray-500">{{ $monthLabels[$ym] }}</div>
                                                <div class="text-sm font-medium">{{ number_format($v, 2) }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        {{-- Type subtotal (mobile) --}}
                        <div class="rounded-xl border bg-indigo-50/60 p-3">
                            <div class="font-medium mb-2">Subtotal — {{ $typeName }}</div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($monthKeys as $ym)
                                    @if(($group['totals'][$ym] ?? 0) != 0)
                                        <div class="flex items-center justify-between rounded-lg bg-white px-2 py-1">
                                            <div class="text-xs text-gray-500">{{ $monthLabels[$ym] }}</div>
                                            <div class="text-sm font-medium">{{ number_format($group['totals'][$ym], 2) }}</div>
                                        </div>
                                    @endif
                                @endforeach
                                <div class="col-span-2 text-right mt-2">
                                    <span class="text-gray-500 text-sm">Type Total:</span>
                                    <span class="font-semibold">{{ number_format($group['type_total'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">No data.</div>
            @endforelse

            
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    $('.js-select2').select2({
        placeholder: function(){
            return $(this).data('placeholder') || 'Select an option';
        },
        allowClear: true,
        width: '100%',
    });
});
</script>
</x-app-layout>