<x-app-layout>
    <div class="max-w-5xl mx-auto p-4 sm:p-6 space-y-4">
        <h1 class="text-xl font-bold">System Profit / Loss</h1>

        <div class="grid sm:grid-cols-3 gap-3">
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-gray-500 text-sm">Total Accounts Receivable</div>
                <div class="text-2xl font-bold">{{ number_format($totals['accounts_receivable'],2) }}</div>
            </div>
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-gray-500 text-sm">Total Received</div>
                <div class="text-2xl font-bold">{{ number_format($totals['income'],2) }}</div>
            </div>
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-gray-500 text-sm">Total Expenses</div>
                <div class="text-2xl font-bold">{{ number_format($totals['expense'],2) }}</div>
            </div>
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-gray-500 text-sm">Cash Balance</div>
                <div class="text-2xl font-bold {{ $totals['cash_balance'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($totals['cash_balance'],2) }}</div>
            </div>
            <div class="rounded-2xl border p-4 bg-white">
                <div class="text-gray-500 text-sm">Profit / Loss</div>
                <div class="text-2xl font-bold {{ $totals['profit_loss'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($totals['profit_loss'],2) }}</div>
            </div>
        </div>

        <div class="rounded-2xl border p-4 bg-white">
            <h2 class="font-semibold mb-2">By Project</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2">Project</th>
                            <th>A/C Receivable</th>
                            <th>Received</th>
                            <th>Expense</th>
                            <th>Cash Balance</th>
                            <th>Profit/Loss</th>
                            <th>Complete %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $p)
                        @php
                            $projIncome  = (float) ($p->total_income ?? 0);
                            $projExpense = (float) ($p->total_expense ?? 0);
                            $projCashBalance  = $projIncome - $projExpense;
                            $projProfitLoss  = $p->accounts_receivable - $projExpense;
                        @endphp
                        <tr class="border-t">
                            <td class="py-2">{{ $p->name }}</td>
                            <td>{{ number_format($p->accounts_receivable,2) }}</td>
                            <td>{{ number_format($p->total_income ?? 0,2) }}</td>
                            <td>{{ number_format($p->total_expense ?? 0,2) }}</td>
                            <td class="{{ $projCashBalance >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($projCashBalance,2) }}</td>
                            <td class="{{ $projProfitLoss >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($projProfitLoss,2) }}</td>
                            <td>{{ $p->progress_percent }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>