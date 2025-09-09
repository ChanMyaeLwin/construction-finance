<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6" x-data="incomeModal()">
        <h1 class="text-xl font-bold mb-3">Received</h1>

        {{-- Filters --}}
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
                        <option value="{{ $ac->id }}" @selected(request('account_code_id')==$ac->id)>
                            {{ $ac->code }} — {{ $ac->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600">Description contains</label>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="e.g. client, source"
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

        {{-- Totals --}}
        <div class="mb-4">
            <strong>Total Received:</strong> {{ number_format($totalIncome, 2) }}
        </div>

        {{-- Table --}}
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
                        <th class="px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomes as $inc)
                        <tr class="border-t">
                            <td class="py-2 px-3">{{ \Carbon\Carbon::parse($inc->income_date)->format('Y-m-d') }}</td>
                            <td class="px-3">{{ $inc->project->name ?? '—' }}</td>
                            <td class="px-3">
                                {{ optional($inc->accountCode)->code }} <br>
                                {{ optional($inc->accountCode)->name }}
                            </td>
                            <td class="px-3">{{ $inc->description ?: '—' }}</td>
                            <td class="px-3 text-right">{{ number_format($inc->amount,2) }}</td>
                            <td class="px-3 text-right">{{ $inc->user->name ?? '—' }}</td>
                            <td class="px-3 text-right whitespace-nowrap">
                                <button type="button"
                                        @click="$dispatch('open-income', {
                                            id: {{ $inc->id }},
                                            date: '{{ \Carbon\Carbon::parse($inc->income_date)->format('Y-m-d') }}',
                                            account_code_id: {{ (int) $inc->account_code_id }},
                                            amount: '{{ $inc->amount }}',
                                            description: @js($inc->description ?? '')
                                        })"
                                        class="text-indigo-600">Edit</button>
                                <span class="mx-1">·</span>
                                <form method="POST" action="{{ route('incomes.destroy', $inc) }}" class="inline"
                                      onsubmit="return confirm('Delete this income?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-center text-gray-500">No incomes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $incomes->links() }}</div>

        {{-- =================== GLOBAL EDIT MODAL (centered) =================== --}}
        <div x-show="open"
             x-cloak
             class="fixed inset-0 z-[99999] flex items-center justify-center"
             x-on:open-income.window="openModal($event.detail)">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>

            <!-- Centered modal -->
            <div id="income-global-modal"
                 class="relative z-[100000] w-full max-w-lg bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold">Edit Income</h3>
                    <button type="button" @click="closeModal()" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <form method="POST" :action="formAction()" class="grid gap-3">
                    @csrf @method('PUT')

                    <div>
                        <label class="block text-sm text-gray-700">Date</label>
                        <input type="date" name="income_date" x-model="date"
                               class="mt-1 w-full rounded-xl border-gray-300" required />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700">Account Code</label>
                        <select name="account_code_id" x-ref="acSelect"
                                class="mt-1 w-full rounded-xl border-gray-300">
                            @foreach($accountCodes as $ac)
                                @if($ac->account_code_type_id == 12)
                                    <option value="{{ $ac->id }}">{{ $ac->code }} — {{ $ac->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700">Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" x-model="amount"
                               class="mt-1 w-full rounded-xl border-gray-300" required />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700">Description</label>
                        <input name="description" x-model="description"
                               class="mt-1 w-full rounded-xl border-gray-300" />
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="closeModal()" class="px-3 py-2 rounded-xl bg-gray-100">Cancel</button>
                        <button type="submit" class="px-3 py-2 rounded-xl bg-indigo-600 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ================= END GLOBAL EDIT MODAL ================= --}}
    </div>

    {{-- Assets --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>[x-cloak]{ display:none !important; }</style>

    <script>
        $(function(){
          $('.select2').select2({ placeholder: 'Choose…', allowClear: true, width: '100%' });
        });

        window.incomeModal = function () {
            return {
                open:false,id:null,date:'',account_code_id:'',amount:'',description:'',
                updateBase:'{{ url('/incomes') }}',
                formAction(){ return this.updateBase + '/' + this.id; },
                openModal(detail){
                    this.id=detail.id; this.date=detail.date; this.account_code_id=String(detail.account_code_id||'');
                    this.amount=detail.amount; this.description=detail.description||''; this.open=true;
                    this.$nextTick(()=>{
                        const $sel=$(this.$refs.acSelect);
                        if ($.fn && $.fn.select2) {
                            if(!$sel.data('select2')){
                                $sel.select2({ placeholder:'Choose…', allowClear:true, width:'100%', dropdownParent:$('#income-global-modal') });
                                $sel.on('change',(e)=>{ this.account_code_id=$(e.target).val(); });
                            }
                            $sel.val(this.account_code_id).trigger('change');
                        } else {
                            this.$refs.acSelect.value = this.account_code_id;
                        }
                    });
                },
                closeModal(){ this.open=false; }
            }
        }
    </script>
</x-app-layout>