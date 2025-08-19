<x-app-layout>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Statement of Profit or Loss (Monthly)</h1>
            <a href="{{ route('reports.summary') }}" class="text-sm text-gray-600">← Back to Reports</a>
        </div>

        {{-- Filters (project drives month range) --}}
        <form method="GET" class="grid gap-3 sm:grid-cols-6 rounded-2xl border bg-white p-4">
            <div class="sm:col-span-3">
                <label class="block text-sm text-gray-600">Project (optional)</label>
                <select name="project_id" class="mt-1 w-full rounded-xl border-gray-300">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Range</label>
                <div class="mt-2 text-sm text-gray-700">
                    {{ optional($start)->format('M Y') }} → {{ optional($end)->format('M Y') }}
                </div>
                <div class="text-[11px] text-gray-500">
                    Start = project start month (or first expense). End = min(expected end, current).
                </div>
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
                        <th class="py-2 px-3 sticky left-0 bg-white z-10">Line item</th>
                        @foreach($months as $ym)
                            <th class="px-3 text-right">{{ $monthLabels[$ym] }}</th>
                        @endforeach
                        <th class="px-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($table as $rowName => $data)
                    @if($rowName === '—divider—')
                        <tr><td colspan="{{ 1 + count($months) + 1 }}" class="py-2"></td></tr>
                        <tr><td colspan="{{ 1 + count($months) + 1 }}" class="text-xs text-gray-500 px-3">Overhead</td></tr>
                        @continue
                    @endif

                    @php $isEmphasis = in_array($rowName, ['Net income','Total overhead','Net profit/(loss)']); @endphp
                    <tr class="border-t {{ $isEmphasis ? 'bg-gray-50 font-medium' : '' }}">
                        <td class="py-2 px-3 sticky left-0 bg-white">{{ $rowName }}</td>
                        @foreach($months as $ym)
                            <td class="px-3 text-right">
                                {{ isset($data[$ym]) ? number_format($data[$ym], 2) : '0.00' }}
                            </td>
                        @endforeach
                        <td class="px-3 text-right {{ $rowName === 'Net profit/(loss)' ? 'font-semibold' : '' }}">
                            {{ number_format($rowTotals[$rowName] ?? 0, 2) }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile (one card per month) --}}
        <div class="md:hidden space-y-3">
            @foreach($months as $ym)
                <div class="rounded-xl border bg-white p-3">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">{{ $monthLabels[$ym] }}</div>
                    </div>
                    <div class="mt-2 space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Revenue</span><span>{{ number_format($table['Revenue'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Other Income</span><span>{{ number_format($table['Other Income'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between font-medium"><span>Net income</span><span>{{ number_format($table['Net income'][$ym] ?? 0, 2) }}</span></div>
                        <hr class="my-1">
                        <div class="flex justify-between"><span class="text-gray-600">Direct operations</span><span>{{ number_format($table['Direct operations'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">G&A</span><span>{{ number_format($table['General & administrative'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Staff/labour</span><span>{{ number_format($table['Staff/labour costs'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Management fees</span><span>{{ number_format($table['Management fees'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Interest</span><span>{{ number_format($table['Interest expense'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Depreciation</span><span>{{ number_format($table['Depreciation & amortization'][$ym] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between font-medium"><span>Total overhead</span><span>{{ number_format($table['Total overhead'][$ym] ?? 0, 2) }}</span></div>
                        @php $np = $table['Net profit/(loss)'][$ym] ?? 0; @endphp
                        <div class="flex justify-between font-semibold">
                            <span>Net profit/(loss)</span>
                            <span class="{{ $np >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ number_format($np, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>