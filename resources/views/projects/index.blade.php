<x-app-layout>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Projects</h1>
            <a href="{{ route('projects.create') }}" class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm">
                + New Project
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="rounded-lg bg-green-50 text-green-800 p-3">{{ session('success') }}</div>
        @endif

        {{-- Desktop / Tablet --}}
        <div class="hidden md:block rounded-2xl border bg-white overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2 px-3">Project</th>
                        <th class="px-3">Type</th>
                        <th class="px-3">Location</th>
                        <th class="px-3 text-right">Fixed Amount</th>
                        <th class="px-3 text-right">Income</th>     {{-- NEW --}}
                        <th class="px-3 text-right">Expense</th>    {{-- RENAMED --}}
                        <th class="px-3 text-right">Profit / Loss</th>
                        <th class="px-3 text-right">Complete %</th>
                        <th class="px-3 w-40 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $p)
                        @php
                            $income  = (float) ($p->incomes_sum_amount  ?? 0);
                            $expense = (float) ($p->expenses_sum_amount ?? 0);
                            $profit  = $income - $expense;
                        @endphp
                        <tr class="border-t">
                            <td class="py-2 px-3">
                                <a href="{{ route('projects.show', $p) }}" class="font-medium text-gray-900 hover:underline">
                                    {{ $p->name }}
                                </a>
                                <div class="text-xs text-gray-500">
                                    {{ optional($p->start_date)->format('Y-m-d') }} →
                                    {{ optional($p->expected_end_date)->format('Y-m-d') ?: '—' }}
                                </div>
                            </td>
                            <td class="px-3">{{ optional($p->projectType)->name ?? ($p->project_type ?? '—') }}</td>
                            <td class="px-3">{{ $p->location ?: '—' }}</td>

                            <td class="px-3 text-right">{{ number_format($p->fixed_amount, 2) }}</td>
                            <td class="px-3 text-right text-emerald-700">{{ number_format($income, 2) }}</td>   {{-- NEW --}}
                            <td class="px-3 text-right">{{ number_format($expense, 2) }}</td>                   {{-- RENAMED --}}
                            <td class="px-3 text-right">
                                <span class="font-semibold {{ $profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ number_format($profit, 2) }}
                                </span>
                            </td>
                            <td class="px-3 text-right">
                                {{ $p->progress_percent }}%
                                <div class="w-full h-1.5 bg-gray-100 rounded mt-1">
                                    <div class="h-1.5 rounded bg-indigo-600" style="width: {{ $p->progress_percent }}%"></div>
                                </div>
                            </td>
                            <td class="px-3 text-right">
                                <a href="{{ route('projects.show', $p) }}" class="text-indigo-600">Open</a>
                                <span class="mx-1">·</span>
                                <a href="{{ route('projects.edit', $p) }}" class="text-gray-700">Edit</a>
                                <span class="mx-1">·</span>
                                <form action="{{ route('projects.destroy', $p) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete project?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-6 text-center text-gray-500">No projects yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile (cards) --}}
        <div class="md:hidden space-y-3">
            @forelse($projects as $p)
                @php
                    $income  = (float) ($p->incomes_sum_amount  ?? 0);
                    $expense = (float) ($p->expenses_sum_amount ?? 0);
                    $profit  = $income - $expense;
                @endphp
                <div class="rounded-xl border bg-white p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('projects.show', $p) }}" class="font-medium text-gray-900 hover:underline">
                                {{ $p->name }}
                            </a>
                            <div class="text-xs text-gray-500">
                                {{ $p->location ?: '—' }} • {{ optional($p->projectType)->name ?? ($p->project_type ?? '—') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Fixed</div>
                            <div class="font-semibold">{{ number_format($p->fixed_amount, 2) }}</div>
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-3 gap-2 text-sm">
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-[11px] text-gray-500">Income</div>
                            <div class="font-medium text-emerald-700">{{ number_format($income, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-[11px] text-gray-500">Expense</div>
                            <div class="font-medium">{{ number_format($expense, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-[11px] text-gray-500">Profit/Loss</div>
                            <div class="font-semibold {{ $profit >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ number_format($profit, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="w-full h-2 bg-gray-100 rounded-full">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $p->progress_percent }}%"></div>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-end gap-2 text-sm">
                        <a href="{{ route('projects.show', $p) }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white">Open</a>
                        <a href="{{ route('projects.edit', $p) }}" class="px-3 py-1.5 rounded-lg bg-gray-100">Edit</a>
                        <form action="{{ route('projects.destroy', $p) }}" method="POST" onsubmit="return confirm('Delete project?');">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-lg bg-red-600 text-white">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">No projects yet.</div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div>
            {{ $projects->links() }}
        </div>
    </div>
</x-app-layout>