<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 space-y-6">
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

        {{-- Header card --}}
        @php
            $totalExpense = (float) ($project->expenses_sum_amount ?? $project->expenses->sum('amount'));
            $totalIncome  = (float) ($project->incomes_sum_amount  ?? $project->incomes->sum('amount'));

            // Net = Income - Expense
            $profit = (float) $totalIncome - $totalExpense;
            $remaining = (float) $project->fixed_amount - $totalIncome;
        @endphp
        <div class="rounded-2xl border p-4 bg-white">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-xl font-bold truncate">{{ $project->name }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ $project->location }}
                        • {{ optional($project->projectType)->name ?? ($project->project_type ?? '—') }}
                    </p>
                </div>
                <div class="text-right text-sm shrink-0">
                    <div>Fixed:
                        <span class="font-semibold">{{ number_format($project->fixed_amount,2) }}</span>
                    </div>
                    <div>Income:
                        <span class="font-semibold text-emerald-700">{{ number_format($totalIncome,2) }}</span>
                    </div>
                    <div>Expense:
                        <span class="font-semibold">{{ number_format($totalExpense,2) }}</span>
                    </div>
                    <div>Remaining:
                        <span class="font-semibold {{ $remaining >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($remaining,2) }}
                        </span>
                    </div>
                    <div>Profit/Loss:
                        <span class="font-semibold {{ $profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($profit,2) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Progress from steps --}}
            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Complete % (from steps)</span><span>{{ $project->progress_percent }}%</span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full mt-1">
                    <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $project->progress_percent }}%"></div>
                </div>
                <div class="mt-1 text-[11px] text-gray-400">Budget used: {{ $project->budget_used_percent }}%</div>
            </div>

            @if($project->description)
                <div class="mt-4 text-sm text-gray-600">{{ $project->description }}</div>
            @endif

            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                <a href="{{ route('projects.edit', $project) }}" class="px-3 py-2 rounded-xl bg-gray-900 text-white">Edit</a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete project?');">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 rounded-xl bg-red-600 text-white">Delete</button>
                </form>
            </div>
        </div>

        {{-- Add Expense --}}
        <div class="rounded-2xl border p-4 bg-white">
            <h2 class="font-semibold mb-3">Add Daily Expense / Income</h2>
            <form method="POST" action="{{ route('projects.expenses.store', $project) }}" class="grid gap-3 sm:grid-cols-6">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium">Account Code</label>
                    <select name="account_code_id" id="account_code_id" class="select2 mt-1 w-full rounded-xl border-gray-300">
                        <option value="">Select…</option>
                        @foreach($accountCodes as $ac)
                            <option value="{{ $ac->id }}" data-type="{{ $ac->account_code_type_id }}">
                                {{ $ac->code }} — {{ $ac->name }}
                                @if($ac->account_code_type_id == 12)
                                    (Income)
                                @else
                                    (Expense)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Tip: AC-40001 is treated as Income (subtracts from totals).</p>
                </div>
               
                <div>
                    <label class="block text-sm font-medium">Date</label>
                    <input type="date" name="expense_date" required class="mt-1 w-full rounded-xl border-gray-300" value="{{ today()->format('Y-m-d') }}"/>
                </div>
                <div>
                    <label class="block text-sm font-medium">Amount</label>
                    <input type="number" step="0.01" min="0" name="amount" required class="mt-1 w-full rounded-xl border-gray-300" />
                </div>
                <div class="sm:col-span-6">
                 {{-- Worker (hidden unless type=15) --}}
                    <div id="worker_wrap" class="mt-3 hidden">
                    <label class="block text-sm font-medium">Worker</label>
                    <select name="worker_id" id="worker_id" class="select2 mt-1 w-full rounded-xl border-gray-300">
                        <option value="">Select worker…</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Selected worker’s name will be saved in description.</p>
                    </div>
                 </div>
                <div class="sm:col-span-6">
                    <label class="block text-sm font-medium">Description</label>
                    <input name="description" class="mt-1 w-full rounded-xl border-gray-300" />
                </div>
                <div class="sm:col-span-6">
                    <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Add</button>
                </div>
            </form>
        </div>

        {{-- Project Steps (CRUD) --}}
        <div class="rounded-2xl border p-4 bg-white">
            <h2 class="font-semibold mb-3">Project Steps</h2>

            {{-- Add Step --}}
            <form method="POST" action="{{ route('projects.steps.store', $project) }}" class="grid grid-cols-5 gap-2 mb-4">
                @csrf
                <div class="col-span-2">
                    <label class="block text-xs text-gray-600">Step No</label>
                    <input type="number" name="step_no" min="1" required class="mt-1 w-full rounded-xl border-gray-300" />
                </div>
                <div class="col-span-3">
                    <label class="block text-xs text-gray-600">Name</label>
                    <input name="name" required class="mt-1 w-full rounded-xl border-gray-300" />
                </div>
                <div class="col-span-5 flex justify-end">
                    <button class="px-3 py-2 rounded-xl bg-gray-900 text-white text-sm">Add Step</button>
                </div>
            </form>

            {{-- Steps list --}}
            @forelse($project->steps as $s)
                <div x-data="{ edit:false }" class="mb-2 last:mb-0 rounded-xl border p-3">
                    <div class="flex items-start justify-between">
                        <div class="text-sm">
                            <div class="text-gray-500">#{{ $s->step_no }}</div>
                            <div class="font-medium">{{ $s->name }}</div>
                        </div>
                        <div class="text-right space-x-2">
                            <form method="POST" action="{{ route('steps.update', $s) }}" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="is_done" value="{{ $s->is_done ? 0 : 1 }}">
                                <button class="px-2 py-1 rounded-lg {{ $s->is_done ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }} text-xs">
                                    {{ $s->is_done ? 'Done' : 'Mark Done' }}
                                </button>
                            </form>

                            <button type="button" @click="edit=true" class="px-2 py-1 rounded-lg bg-indigo-600 text-white text-xs">Edit</button>

                            <form method="POST" action="{{ route('steps.destroy', $s) }}" class="inline"
                                  onsubmit="return confirm('Delete this step?');">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded-lg bg-red-600 text-white text-xs">Delete</button>
                            </form>
                        </div>
                    </div>

                    {{-- Inline editor --}}
                    <div x-show="edit" x-cloak class="mt-3 grid grid-cols-5 gap-2">
                        <form method="POST" action="{{ route('steps.update', $s) }}" class="contents">
                            @csrf @method('PUT')
                            <div class="col-span-2">
                                <label class="block text-xs text-gray-600">Step No</label>
                                <input type="number" name="step_no" min="1" value="{{ $s->step_no }}"
                                       class="mt-1 w-full rounded-xl border-gray-300" />
                            </div>
                            <div class="col-span-3">
                                <label class="block text-xs text-gray-600">Name</label>
                                <input name="name" value="{{ $s->name }}"
                                       class="mt-1 w-full rounded-xl border-gray-300" />
                            </div>
                            <div class="col-span-5 flex justify-end gap-2 mt-2">
                                <button type="button" @click="edit=false" class="px-3 py-2 rounded-xl bg-gray-100 text-sm">Cancel</button>
                                <button class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">No steps yet.</div>
            @endforelse
        </div>

        {{-- Expenses (mobile-first cards + bottom-sheet edit) --}}
        <div class="rounded-2xl border p-4 bg-white">
            <h2 class="font-semibold mb-3">Expenses</h2>

            @forelse($project->expenses->sortByDesc('created_at') as $e)
                @php
                    $isIncome = optional($e->accountCode)->code === 'AC-40001';
                @endphp
                <div x-data="{ open:false }"
                     x-init="
                        $watch('open', v => {
                            const sel = $('#account_code_id_edit_{{ $e->id }}');
                            if (v) {
                                setTimeout(() => {
                                    sel.select2({
                                        placeholder:'Search by code or name',
                                        allowClear:true,
                                        width:'100%',
                                        dropdownParent: $('#edit-modal-{{ $e->id }}')
                                    });
                                }, 0);
                            } else {
                                if (sel.data('select2')) sel.select2('destroy');
                            }
                        });
                     "
                     class="mb-3 last:mb-0">

                    <!-- Card -->
                    <div class="rounded-xl border border-gray-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-500">{{ $e->expense_date->format('Y-m-d') }}</div>
                                <div class="text-sm text-gray-700 truncate">
                                    {{ $e->accountCode->code }} — {{ $e->accountCode->name }}
                                </div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ $e->description ?: '—' }}
                                </div>
                                <span class="inline-block mt-1 text-[11px] rounded-full px-2 py-0.5
                                    {{ $isIncome ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $isIncome ? 'Income (subtracts)' : 'Expense' }}
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-semibold">{{ number_format($e->amount,2) }}</div>
                                <div class="mt-2 space-x-2">
                                    <button type="button"
                                            @click="open=true"
                                            class="px-2 py-1 rounded-lg bg-gray-900 text-white text-xs">Edit</button>

                                    <form method="POST" action="{{ route('expenses.destroy', $e) }}" class="inline"
                                          onsubmit="return confirm('Delete this expense?');">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 rounded-lg bg-red-600 text-white text-xs">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom-sheet modal -->
                    <template x-teleport="body">
                        <div x-show="open" x-cloak class="fixed inset-0 z-50">
                            <!-- Backdrop -->
                            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

                            <!-- Sheet -->
                            <div id="edit-modal-{{ $e->id }}"
                                 class="absolute inset-x-0 bottom-0 md:inset-1/2 md:-translate-x-1/2 md:translate-y-0 md:w-full md:max-w-lg 
                                        bg-white rounded-t-2xl md:rounded-2xl shadow-lg p-4"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="translate-y-8 opacity-0"
                                 x-transition:enter-end="translate-y-0 opacity-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="translate-y-0 opacity-100"
                                 x-transition:leave-end="translate-y-8 opacity-0" style="height: fit-content;">

                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-base font-semibold">Edit Expense</h3>
                                    <button type="button" @click="open=false" class="text-gray-500 hover:text-gray-700">✕</button>
                                </div>

                                <form id="exp-edit-{{ $e->id }}" method="POST" action="{{ route('expenses.update', $e) }}" class="grid gap-3">
                                    @csrf @method('PUT')

                                    <div>
                                        <label class="block text-sm text-gray-700">Date</label>
                                        <input type="date" name="expense_date" value="{{ $e->expense_date->format('Y-m-d') }}"
                                               class="mt-1 w-full rounded-xl border-gray-300" required />
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-700">Account Code</label>
                                        <select name="account_code_id" id="account_code_id_edit_{{ $e->id }}"
                                                class="mt-1 w-full rounded-xl border-gray-300">
                                            @foreach($accountCodes as $ac)
                                                <option value="{{ $ac->id }}" data-type="{{ $ac->account_code_type_id }}" @selected($e->account_code_id == $ac->id)>
                                                    {{ $ac->code }} — {{ $ac->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-700">Amount</label>
                                        <input type="number" step="0.01" min="0" name="amount" value="{{ $e->amount }}"
                                               class="mt-1 w-full rounded-xl border-gray-300" required />
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-700">Description</label>
                                        <input name="description" value="{{ $e->description }}"
                                               class="mt-1 w-full rounded-xl border-gray-300" />
                                    </div>
                                </form>

                                <div class="mt-3 flex justify-end gap-2">
                                    <button type="button" @click="open=false" class="px-3 py-2 rounded-xl bg-gray-100">Cancel</button>
                                    <button type="submit" form="exp-edit-{{ $e->id }}" class="px-3 py-2 rounded-xl bg-indigo-600 text-white">Save</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            @empty
                <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">No expenses yet.</div>
            @endforelse
        </div>

        {{-- Incomes (mobile-first cards similar to Expenses) --}}
        <div class="rounded-2xl border p-4 bg-white">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold mb-3">Incomes</h2>
            </div>

            @forelse($project->incomes->sortByDesc('created_at') as $inc)
                <div x-data="{ open:false }" class="mb-3 last:mb-0">
                    <!-- Card -->
                    <div class="rounded-xl border border-gray-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-500">{{ \Illuminate\Support\Carbon::parse($inc->income_date)->format('Y-m-d') }}</div>
                                <div class="text-sm text-gray-700 truncate">
                                    {{ optional($inc->accountCode)->code }} — {{ optional($inc->accountCode)->name }}
                                </div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ $inc->description ?: '—' }}
                                </div>
                                <span class="inline-block mt-1 text-[11px] rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700">
                                    Income
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-semibold">{{ number_format($inc->amount,2) }}</div>
                                <div class="mt-2 space-x-2">
                                    {{-- If you have Income routes, keep Edit/Delete; otherwise remove these buttons --}}
                                    <button type="button"
                                            @click="open=true"
                                            class="px-2 py-1 rounded-lg bg-gray-900 text-white text-xs">Edit</button>

                                    <form method="POST" action="{{ route('incomes.destroy', $inc) }}" class="inline"
                                        onsubmit="return confirm('Delete this income?');">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 rounded-lg bg-red-600 text-white text-xs">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Optional bottom-sheet editor for Income -->
                    <template x-teleport="body">
                        <div x-show="open" x-cloak class="fixed inset-0 z-50">
                            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

                            <div id="edit-income-{{ $inc->id }}"
                                class="absolute inset-x-0 bottom-0 md:inset-1/2 md:-translate-x-1/2 md:translate-y-0 md:w-full md:max-w-lg 
                                        bg-white rounded-t-2xl md:rounded-2xl shadow-lg p-4"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="translate-y-8 opacity-0"
                                x-transition:enter-end="translate-y-0 opacity-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="translate-y-0 opacity-100"
                                x-transition:leave-end="translate-y-8 opacity-0">

                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-base font-semibold">Edit Income</h3>
                                    <button type="button" @click="open=false" class="text-gray-500 hover:text-gray-700">✕</button>
                                </div>

                                <form id="inc-edit-{{ $inc->id }}" method="POST" action="{{ route('incomes.update', $inc) }}" class="grid gap-3">
                                    @csrf @method('PUT')

                                    <div>
                                        <label class="block text-sm text-gray-700">Date</label>
                                        <input type="date" name="income_date" value="{{ \Illuminate\Support\Carbon::parse($inc->income_date)->format('Y-m-d') }}"
                                            class="mt-1 w-full rounded-xl border-gray-300" required />
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-700">Account Code</label>
                                        <select name="account_code_id" class="mt-1 w-full rounded-xl border-gray-300">
                                            @foreach($accountCodes as $ac)
                                                @if($ac->account_code_type_id == 12) {{-- Revenue only for incomes --}}
                                                    <option value="{{ $ac->id }}"  data-type="{{ $ac->account_code_type_id }}" @selected($inc->account_code_id == $ac->id)>
                                                        {{ $ac->code }} — {{ $ac->name }} (Income)
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-700">Amount</label>
                                        <input type="number" step="0.01" min="0" name="amount" value="{{ $inc->amount }}"
                                            class="mt-1 w-full rounded-xl border-gray-300" required />
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-700">Description</label>
                                        <input name="description" value="{{ $inc->description }}"
                                            class="mt-1 w-full rounded-xl border-gray-300" />
                                    </div>
                                </form>

                                <div class="mt-3 flex justify-end gap-2">
                                    <button type="button" @click="open=false" class="px-3 py-2 rounded-xl bg-gray-100">Cancel</button>
                                    <button type="submit" form="inc-edit-{{ $inc->id }}" class="px-3 py-2 rounded-xl bg-indigo-600 text-white">Save</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            @empty
                <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">No incomes yet.</div>
            @endforelse
        </div>
    </div>

    {{-- If your layout doesn't already include these, keep them here. Otherwise you can remove. --}}
    <!-- jQuery (short URL) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script>
        $(document).ready(function() {
            // Enhance the Add Expense select
            $('.select2').select2({
                placeholder: "Search by code or name",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script>
    $(function () {
        const TYPE_WORKER = 15;

        function toggleWorker() {
            const typeId = parseInt($('#account_code_id option:selected').data('type')) || 0;
            console.log(typeId);
        if (typeId === TYPE_WORKER) {
            $('#worker_wrap').removeClass('hidden');
        } else {
            $('#worker_wrap').addClass('hidden');
            $('#worker_id').val(null).trigger('change');
        }
        }

        $('#account_code_id').on('change', toggleWorker);
        toggleWorker();

        // On submit: if worker chosen & description empty, fill with worker name
        $('form[action*="projects/"][action$="/expenses"]').on('submit', function () {
        const typeId = parseInt($('#account_code_id option:selected').data('type')) || 0;
        if (typeId === TYPE_WORKER) {
            const workerText = $('#worker_id option:selected').text().trim();
            const desc = $('#description').val().trim();
            if (!desc && workerText) {
            $('#description').val(workerText);
            }
        }
        });

        // Enhance selects
        $('.select2').select2({ placeholder: "Search…", allowClear: true, width: '100%' });
    });
    </script>
</x-app-layout>