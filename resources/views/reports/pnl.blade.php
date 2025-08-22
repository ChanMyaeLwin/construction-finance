<x-app-layout>
  <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-4">
    @if(session('success'))
      <div class="rounded-lg bg-green-50 text-green-800 p-3">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="rounded-lg bg-red-50 text-red-700 p-3">
        <ul class="list-disc ml-5">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">P&L (Monthly)</h1>
      <a href="{{ route('reports.summary') }}" class="text-sm text-gray-600">← Back to Reports</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="grid gap-3 sm:grid-cols-6 rounded-2xl border bg-white p-4">
      <div class="sm:col-span-2">
        <label class="block text-sm text-gray-600">Project</label>
        <select name="project_id" class="mt-1 w-full rounded-xl border-gray-300">
          <option value="">All Projects</option>
          @foreach($projects as $p)
            <option value="{{ $p->id }}" @selected((string)request('project_id')===(string)$p->id)>
              {{ $p->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600">From</label>
        <input type="date" name="from" value="{{ $from ?? '' }}" class="mt-1 w-full rounded-xl border-gray-300" />
      </div>
      <div>
        <label class="block text-sm text-gray-600">To</label>
        <input type="date" name="to" value="{{ $to ?? '' }}" class="mt-1 w-full rounded-xl border-gray-300" />
      </div>
      <div class="flex items-end">
        <button class="w-full px-4 py-2 rounded-xl bg-gray-900 text-white">Apply</button>
      </div>
    </form>

    {{-- Table --}}
    <div class="rounded-2xl border bg-white overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2 px-3 sticky left-0 bg-white z-10">Category</th>
            @foreach($months as $ym)
              <th class="px-3 text-right">{{ $monthLabels[$ym] }}</th>
            @endforeach
            <th class="px-3 text-right">Total</th>
          </tr>
        </thead>

        <tbody>
          @foreach($table as $rowName => $values)
            @if($rowName === '—divider—')
              <tr class="border-t">
                <td class="py-2 px-3 sticky left-0 bg-white" colspan="{{ 1 + count($months) + 1 }}">
                  <div class="border-t my-1"></div>
                </td>
              </tr>
              @continue
            @endif
            @if($rowName === 'Net profit/(loss)')
                @continue  {{-- skip showing in body, footer will handle --}}
            @endif

            @php $rowTotal = 0.0; @endphp
            <tr class="border-t">
              <td class="py-2 px-3 sticky left-0 bg-white font-medium">{{ $rowName }}</td>
              @foreach($months as $ym)
                @php $val = (float) ($values[$ym] ?? 0); $rowTotal += $val; @endphp
                <td class="px-3 text-right">{{ $val ? number_format($val,2) : '—' }}</td>
              @endforeach
              <td class="px-3 text-right font-semibold">{{ number_format($rowTotal, 2) }}</td>
            </tr>
          @endforeach
        </tbody>

        <tfoot>
  <tr class="border-t bg-gray-50 font-semibold">
    <td class="py-2 px-3 sticky left-0 bg-gray-50">Net profit/(loss)</td>
    @php $netGrand = 0.0; @endphp
    @foreach($months as $ym)
      @php
        $net = (float) ($table['Net profit/(loss)'][$ym] ?? 0);
        $netGrand += $net;
      @endphp
      <td class="px-3 text-right {{ $net >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
        {{ number_format($net, 2) }}
      </td>
    @endforeach
    <td class="px-3 text-right {{ $netGrand >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
      {{ number_format($netGrand, 2) }}
    </td>
  </tr>
</tfoot>
    </div>
  </div>
</x-app-layout>