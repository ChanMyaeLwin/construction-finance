<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\AccountCode;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function summary()
    {
        $totals = [
            'fixed' => (float) Project::sum('fixed_amount'),
            'expense' => (float) Project::withSum('expenses as expense_sum', 'amount')->get()->sum('expense_sum'),
        ];
        $totals['profit'] = $totals['fixed'] - $totals['expense'];
        $projects = Project::withSum('expensesOnly as total_expense', 'amount')->orderBy('name')->get();
        return view('reports.summary', compact('totals', 'projects'));
    }

    public function cashbook(Request $request)
    {
        $projectId       = $request->integer('project_id');
        $from            = $request->date('from');
        $to              = $request->date('to');
        $openingBalance  = (float) $request->input('opening_balance', 0);

        $query = Expense::with(['accountCode', 'project'])
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->when($from, fn($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('expense_date', '<=', $to))
            ->orderBy('expense_date')
            ->orderBy('id');

        $rows = $query->get();

        $balance = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $items = $rows->map(function ($e) use (&$balance, &$totalDebit, &$totalCredit) {
            $code   = optional($e->accountCode)->code;
            $name   = optional($e->accountCode)->name;
            $isDebit = ($code === 'AC-40001'); // your rule
            $debit  = $isDebit ? (float) $e->amount : 0.0;
            $credit = $isDebit ? 0.0 : (float) $e->amount;

            $totalDebit  += $debit;
            $totalCredit += $credit;

            $balance = $balance + $debit - $credit;

            return [
                'date'        => optional($e->expense_date)->format('Y-m-d'),
                'code'        => $code ?? '—',
                'name'        => $name ?? '—',
                'description' => $e->description,
                'debit'       => $debit,
                'credit'      => $credit,
                'balance'     => $balance,
            ];
        });

        $finalBalance = $items->last()['balance'] ?? $openingBalance;

        $projects = Project::orderBy('name')->get();

        return view('reports.cashbook', [
            'items'          => $items,
            'projects'       => $projects,
            'openingBalance' => $openingBalance,
            'totalDebit'     => $totalDebit,
            'totalCredit'    => $totalCredit,
            'finalBalance'   => $finalBalance,
        ]);
    }
    public function notes(Request $request)
    {
        $projectId = $request->integer('project_id');
        $from      = $request->date('from');
        $to        = $request->date('to');

        // Aggregate per account_code per month, with its type (nullable -> 'Uncategorized')
        $rows = Expense::query()
            ->leftJoin('account_codes', 'account_codes.id', '=', 'expenses.account_code_id')
            ->leftJoin('account_code_types', 'account_code_types.id', '=', 'account_codes.account_code_type_id')
            ->when($projectId, fn($q) => $q->where('expenses.project_id', $projectId))
            ->when($from, fn($q) => $q->whereDate('expenses.expense_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('expenses.expense_date', '<=', $to))
            ->selectRaw("
                expenses.account_code_id,
                COALESCE(account_codes.code, '—') as ac_code,
                COALESCE(account_codes.name, '—') as ac_name,
                COALESCE(account_code_types.name, 'Uncategorized') as type_name,
                DATE_FORMAT(expenses.expense_date, '%Y-%m-01') as ym,
                SUM(expenses.amount) as total
            ")
            ->groupBy('expenses.account_code_id','ac_code','ac_name','type_name','ym')
            ->orderBy('type_name')->orderBy('ac_code')->orderBy('ym')
            ->get();

        // Build month range
        if ($rows->isNotEmpty()) {
            $minYm = $from ? Carbon::parse($from)->startOfMonth() : Carbon::parse($rows->min('ym'));
            $maxYm = $to   ? Carbon::parse($to)->startOfMonth()   : Carbon::parse($rows->max('ym'));
        } else {
            $minYm = $maxYm = Carbon::now()->startOfMonth();
        }
        $months = collect(CarbonPeriod::create($minYm, '1 month', $maxYm))
            ->map(fn(Carbon $c) => $c->format('Y-m-01'));
        $monthLabels = $months->mapWithKeys(fn($ym) => [$ym => Carbon::parse($ym)->format('M y')]);

        // Reshape into: groups[type_name] = [ rows... ], and compute per-type subtotals
        $byType = [];
        $totalsPerMonth = array_fill_keys($months->all(), 0.0);
        $grandTotal = 0.0;

        foreach ($rows->groupBy('type_name') as $typeName => $groupRows) {
            // index code-month totals
            $codeMonth = [];
            foreach ($groupRows as $r) {
                $codeMonth[$r->account_code_id]['code'] = $r->ac_code;
                $codeMonth[$r->account_code_id]['name'] = $r->ac_name;
                $codeMonth[$r->account_code_id]['months'][$r->ym] = (float) $r->total;
            }

            // build rows and type subtotal
            $typeRows = [];
            $typeTotals = array_fill_keys($months->all(), 0.0);
            $typeGrand = 0.0;

            foreach ($codeMonth as $acId => $info) {
                $row = [
                    'code' => $info['code'],
                    'name' => $info['name'],
                    'months' => [],
                    'row_total' => 0.0,
                ];
                foreach ($months as $ym) {
                    $val = (float) ($info['months'][$ym] ?? 0.0);
                    $row['months'][$ym] = $val;
                    $row['row_total'] += $val;
                    $typeTotals[$ym] += $val;
                    $totalsPerMonth[$ym] += $val;
                }
                $typeGrand += $row['row_total'];
                $grandTotal += $row['row_total'];
                $typeRows[] = $row;
            }

            $byType[$typeName] = [
                'rows' => $typeRows,
                'totals' => $typeTotals,
                'type_total' => $typeGrand,
            ];
        }

        // For filters
        $projects = Project::orderBy('name')->get(['id','name']);

        return view('reports.notes', [
            'groups'         => $byType,      // NEW: grouped result
            'monthKeys'      => $months,
            'monthLabels'    => $monthLabels,
            'totalsPerMonth' => $totalsPerMonth,
            'grandTotal'     => $grandTotal,
            'projects'       => $projects,
        ]);
    }


    public function pnl(Request $request)
    {
        $projectId = $request->integer('project_id'); // optional project filter
        $projects  = Project::orderBy('name')->get(['id','name','start_date','expected_end_date']);

        // ---- Build month range based on project dates (or system-wide if none selected) ----
        if ($projectId) {
            $project = $projects->firstWhere('id', $projectId);

            // Start: project start_date month; fallback to first expense month of the project; else current month
            $start = optional($project?->start_date)->copy()->startOfMonth();
            if (!$start) {
                $firstExpense = Expense::where('project_id', $projectId)->orderBy('expense_date')->value('expense_date');
                $start = optional($firstExpense)->copy()->startOfMonth();
            }
            if (!$start) {
                $start = now()->startOfMonth();
            }

            // End: min(expected end month, current month); if no expected end -> current month
            $expectedEnd = optional($project?->expected_end_date)->copy()->startOfMonth();
            $end = now()->startOfMonth();
            if ($expectedEnd && $expectedEnd->lt($end)) {
                $end = $expectedEnd;
            }
        } else {
            // No project selected: show from earliest expense month (any project) to current month
            $firstExpense = Expense::orderBy('expense_date')->value('expense_date');
            $start = optional($firstExpense)->copy()->startOfMonth() ?: now()->startOfMonth();
            $end   = now()->startOfMonth();
        }

        // Ensure start <= end (clamp to one month if inverted)
        if ($start->gt($end)) {
            $start = $end->copy();
        }

        // Build months list/labels
        $months = collect(CarbonPeriod::create($start, '1 month', $end))
            ->map(fn(Carbon $c) => $c->format('Y-m-01'));
        $monthLabels = $months->mapWithKeys(fn($ym) => [$ym => Carbon::parse($ym)->format('M y')]);

        // Pull totals by month + account code within date window (and project filter if given)
        $rows = Expense::query()
            ->leftJoin('account_codes', 'account_codes.id', '=', 'expenses.account_code_id')
            ->when($projectId, fn($q) => $q->where('expenses.project_id', $projectId))
            ->whereDate('expenses.expense_date', '>=', $start->toDateString())
            ->whereDate('expenses.expense_date', '<=', $end->copy()->endOfMonth()->toDateString())
            ->selectRaw("
                DATE_FORMAT(expenses.expense_date, '%Y-%m-01') as ym,
                TRIM(COALESCE(account_codes.code, '')) as ac_code,
                SUM(expenses.amount) as total
            ")
            ->groupBy('ym', 'ac_code')
            ->orderBy('ym')->orderBy('ac_code')
            ->get();

        // Index for quick lookup: $byMonthCode[ym][code] = total
        $byMonthCode = [];
        foreach ($rows as $r) {
            $byMonthCode[$r->ym][$r->ac_code] = (float) $r->total;
        }

        // ---- Category mapping (adjust anytime) ----
        $map = [
            // Income
            'revenue'         => ['AC-40001'],      // main revenue
            'other_income'    => ['AC-40002'],      // discount or misc income (move to revenue- as negative- if you prefer)

            // Overhead groups
            'direct_ops'      => ['AC-50001','AC-50002','AC-50003','AC-50004','AC-50005','AC-50006'],
            'general_admin'   => [
                'AC-60001','AC-60002','AC-60003','AC-60005','AC-60006','AC-60007','AC-60008','AC-60009','AC-60010',
                'AC-60011','AC-60012','AC-60013','AC-60014','AC-60017','AC-60020','AC-60021','AC-60023','AC-60024',
                'AC-60028','AC-60029','AC-60101','AC-60102'
            ],
            'staff_labour'    => ['AC-60015','AC-60016'],
            'management_fees' => [],
            'interest_exp'    => [],                // keep AC-60020 inside G&A above unless you split it
            'depr_amort'      => ['AC-60201','AC-60202'],
        ];

        // Helper to sum a set of codes for a month
        $sumFor = function (string $ym, array $codes) use ($byMonthCode): float {
            $sum = 0.0;
            foreach ($codes as $code) {
                $sum += $byMonthCode[$ym][$code] ?? 0.0;
            }
            return $sum;
        };

        // Build table rows
        $table = [
            'Revenue'                         => [],
            'Other Income'                    => [],
            'Net income'                      => [],
            '—divider—'                       => [],
            'Direct operations'               => [],
            'General & administrative'        => [],
            'Staff/labour costs'              => [],
            'Management fees'                 => [],
            'Interest expense'                => [],
            'Depreciation & amortization'     => [],
            'Total overhead'                  => [],
            'Net profit/(loss)'               => [],
        ];

        $rowTotals = array_fill_keys(array_keys($table), 0.0);

        foreach ($months as $ym) {
            $revenue      = $sumFor($ym, $map['revenue']);
            $otherIncome  = $sumFor($ym, $map['other_income']);
            $netIncome    = $revenue + $otherIncome;

            $directOps    = $sumFor($ym, $map['direct_ops']);
            $ga           = $sumFor($ym, $map['general_admin']);
            $staff        = $sumFor($ym, $map['staff_labour']);
            $mgmt         = $sumFor($ym, $map['management_fees']);
            $interest     = $sumFor($ym, $map['interest_exp']);
            $depr         = $sumFor($ym, $map['depr_amort']);

            $totalOver    = $directOps + $ga + $staff + $mgmt + $interest + $depr;
            $netProfit    = $netIncome - $totalOver;

            $table['Revenue'][$ym]                     = $revenue;
            $table['Other Income'][$ym]                = $otherIncome;
            $table['Net income'][$ym]                  = $netIncome;
            $table['—divider—'][$ym]                   = null;
            $table['Direct operations'][$ym]           = $directOps;
            $table['General & administrative'][$ym]    = $ga;
            $table['Staff/labour costs'][$ym]          = $staff;
            $table['Management fees'][$ym]             = $mgmt;
            $table['Interest expense'][$ym]            = $interest;
            $table['Depreciation & amortization'][$ym] = $depr;
            $table['Total overhead'][$ym]              = $totalOver;
            $table['Net profit/(loss)'][$ym]           = $netProfit;

            foreach ($table as $rowName => $values) {
                if ($rowName === '—divider—') continue;
                $rowTotals[$rowName] += $table[$rowName][$ym];
            }
        }

        return view('reports.pnl', [
            'projects'    => $projects,
            'projectId'   => $projectId,
            'months'      => $months,
            'monthLabels' => $monthLabels,
            'table'       => $table,
            'rowTotals'   => $rowTotals,
            'start'       => $start,
            'end'         => $end,
        ]);
    }
}
