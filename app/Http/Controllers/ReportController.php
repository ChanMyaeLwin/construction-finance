<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\AccountCode;
use App\Models\AccountCodeType;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function summary()
    {
        $totals = [
            'fixed'   => (float) Project::sum('fixed_amount'),
            'expense' => (float) Project::withSum('expenses as expense_sum', 'amount')->get()->sum('expense_sum'),
            'income'  => (float) Project::withSum('incomes',  'amount')->get()->sum('incomes_sum_amount'),
        ];
        // CHANGE: profit = income - expense
        $totals['profit'] = $totals['income'] - $totals['expense'];

        $projects = Project::query()
            ->withSum('expenses as total_expense', 'amount')
            ->withSum('incomes',  'amount')
            ->orderBy('name')
            ->get();
         return view('reports.summary', compact('totals', 'projects'));
    }

    public function cashbook(Request $request)
{
    $projectId        = $request->integer('project_id');
    $from             = $request->date('from');
    $to               = $request->date('to');
    $openingBalance   = (float) $request->input('opening_balance', 0);
    $accountCodeId    = $request->integer('account_code_id');     // NEW: filter by A/C
    $q                = trim((string) $request->input('q', ''));  // NEW: description contains

    // EXPENSES (outflows → credit)
    $expQ = \App\Models\Expense::query()
        ->leftJoin('account_codes', 'account_codes.id', '=', 'expenses.account_code_id')
        ->leftJoin('account_code_types', 'account_code_types.id', '=', 'account_codes.account_code_type_id')
        ->when($projectId, fn($q2) => $q2->where('expenses.project_id', $projectId))
        ->when($from, fn($q2) => $q2->whereDate('expenses.expense_date', '>=', $from))
        ->when($to, fn($q2) => $q2->whereDate('expenses.expense_date', '<=', $to))
        ->when($accountCodeId, fn($q2) => $q2->where('expenses.account_code_id', $accountCodeId))
        ->when($q !== '', fn($q2) => $q2->where('expenses.description', 'like', "%{$q}%"))
        ->selectRaw("
            expenses.expense_date as dt,
            account_codes.code as code,
            account_codes.name as name,
            COALESCE(account_code_types.name, 'Uncategorized') as type_name,
            expenses.description as description,
            expenses.created_at as created_at,
            0 as debit,
            expenses.amount as credit
        ");

    // INCOMES (inflows → debit)
    $incQ = \App\Models\Income::query()
        ->leftJoin('account_codes', 'account_codes.id', '=', 'incomes.account_code_id')
        ->leftJoin('account_code_types', 'account_code_types.id', '=', 'account_codes.account_code_type_id')
        ->when($projectId, fn($q2) => $q2->where('incomes.project_id', $projectId))
        ->when($from, fn($q2) => $q2->whereDate('incomes.income_date', '>=', $from))
        ->when($to, fn($q2) => $q2->whereDate('incomes.income_date', '<=', $to))
        ->when($accountCodeId, fn($q2) => $q2->where('incomes.account_code_id', $accountCodeId))
        ->when($q !== '', fn($q2) => $q2->where('incomes.description', 'like', "%{$q}%"))
        ->selectRaw("
            incomes.income_date as dt,
            account_codes.code as code,
            account_codes.name as name,
            COALESCE(account_code_types.name, 'Uncategorized') as type_name,
            incomes.description as description,
            incomes.created_at as created_at,
            incomes.amount as debit,
            0 as credit
        ");

    // UNION + ordering
    $rows = $incQ->unionAll($expQ)
        ->orderBy('dt')
        ->orderBy('created_at')
        ->orderBy('code')
        ->get();

    // Running balance
    $balance     = $openingBalance;
    $totalDebit  = 0.0;
    $totalCredit = 0.0;

    $items = $rows->map(function ($r) use (&$balance, &$totalDebit, &$totalCredit) {
        $debit        = (float) $r->debit;
        $credit       = (float) $r->credit;
        $totalDebit  += $debit;
        $totalCredit += $credit;
        $balance      = $balance + $debit - $credit;

        return [
            'date'        => \Carbon\Carbon::parse($r->dt)->format('Y-m-d'),
            'code'        => $r->code ?? '—',
            'name'        => $r->name ?? '—',
            'type_name'   => $r->type_name ?? 'Uncategorized',
            'description' => $r->description,
            'debit'       => $debit,
            'credit'      => $credit,
            'balance'     => $balance,
        ];
    });

    $finalBalance = $items->last()['balance'] ?? $openingBalance;

    $projects = \App\Models\Project::orderBy('name')->get(['id','name']);
    $accountCodes = \App\Models\AccountCode::orderBy('code')->get(['id','code','name']); // for dropdown

    return view('reports.cashbook', [
        'items'             => $items,
        'projects'          => $projects,
        'accountCodes'      => $accountCodes,        // NEW
        'openingBalance'    => $openingBalance,
        'totalDebit'        => $totalDebit,
        'totalCredit'       => $totalCredit,
        'finalBalance'      => $finalBalance,
        'selectedProjectId' => $projectId,
        'selectedAccountId' => $accountCodeId,       // NEW
        'q'                 => $q,                   // NEW
        'from'              => $from?->format('Y-m-d'),
        'to'                => $to?->format('Y-m-d'),
    ]);
}

   public function notes(Request $request)
{
    $projectId     = $request->integer('project_id');
    $accountCodeId = $request->integer('account_code_id'); // Select2
    $from          = $request->date('from');
    $to            = $request->date('to');

    // ---------- EXPENSES: aggregate per account_code per month ----------
    $expenseRows = Expense::query()
        ->leftJoin('account_codes', 'account_codes.id', '=', 'expenses.account_code_id')
        ->leftJoin('account_code_types', 'account_code_types.id', '=', 'account_codes.account_code_type_id')
        ->when($projectId, fn($q) => $q->where('expenses.project_id', $projectId))
        ->when($accountCodeId, fn($q) => $q->where('expenses.account_code_id', $accountCodeId))
        ->when($from, fn($q) => $q->whereDate('expenses.expense_date', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('expenses.expense_date', '<=', $to))
        ->selectRaw("
            expenses.account_code_id                           as account_code_id,
            COALESCE(account_codes.code, '—')                  as ac_code,
            COALESCE(account_codes.name, '—')                  as ac_name,
            COALESCE(account_code_types.name, 'Uncategorized') as type_name,
            DATE_FORMAT(expenses.expense_date, '%Y-%m-01')     as ym,
            SUM(expenses.amount)                               as total
        ")
        ->groupBy('account_code_id','ac_code','ac_name','type_name','ym')
        ->get();

    /* ---------- INCOMES: per account_code per month ---------- */
    // All incomes are Revenue (account_code_type_id = 12), show them under 'Revenue'
    $incomeRows = Income::query()
        ->leftJoin('account_codes', 'account_codes.id', '=', 'incomes.account_code_id')
        ->when($projectId, fn($q) => $q->where('incomes.project_id', $projectId))
        ->when($accountCodeId, fn($q) => $q->where('incomes.account_code_id', $accountCodeId))
        ->when($from, fn($q) => $q->whereDate('incomes.income_date', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('incomes.income_date', '<=', $to))
        ->selectRaw("
            incomes.account_code_id                        as account_code_id,
            COALESCE(account_codes.code, '—')              as ac_code,
            COALESCE(account_codes.name, '—')              as ac_name,
            'Revenue'                                      as type_name,
            DATE_FORMAT(incomes.income_date, '%Y-%m-01')   as ym,
            SUM(incomes.amount)                            as total
        ")
        ->groupBy('account_code_id','ac_code','ac_name','type_name','ym')
        ->get();

    /* ---------- Merge both sides ---------- */
    $rows = $expenseRows->concat($incomeRows); 

    // ---------- Build month range ----------
    if ($rows->isNotEmpty()) {
        $minYm = $from
            ? Carbon::parse($from)->startOfMonth()
            : Carbon::parse($rows->min('ym'));
        $maxYm = $to
            ? Carbon::parse($to)->startOfMonth()
            : Carbon::parse($rows->max('ym'));
    } else {
        $minYm = $maxYm = Carbon::now()->startOfMonth();
    }

    $months = collect(CarbonPeriod::create($minYm, '1 month', $maxYm))
        ->map(fn(Carbon $c) => $c->format('Y-m-01'));
    $monthLabels = $months->mapWithKeys(fn($ym) => [$ym => Carbon::parse($ym)->format('M y')]);

    // ---------- Reshape into groups[type_name] with per-type subtotals ----------
    $byType = [];
    $totalsPerMonth = array_fill_keys($months->all(), 0.0);
    $grandTotal = 0.0;

    foreach ($rows->groupBy('type_name') as $typeName => $groupRows) {
        // index code-month totals for this type
        $codeMonth = [];
        foreach ($groupRows as $r) {
            $codeMonth[$r->account_code_id]['code'] = $r->ac_code;
            $codeMonth[$r->account_code_id]['name'] = $r->ac_name;
            $codeMonth[$r->account_code_id]['months'][$r->ym] = (float) $r->total;
        }

        // build rows + type subtotal
        $typeRows   = [];
        $typeTotals = array_fill_keys($months->all(), 0.0);
        $typeGrand  = 0.0;

        foreach ($codeMonth as $acId => $info) {
            $row = [
                'code'      => $info['code'],
                'name'      => $info['name'],
                'months'    => [],
                'row_total' => 0.0,
            ];
            foreach ($months as $ym) {
                $val = (float) ($info['months'][$ym] ?? 0.0);
                $row['months'][$ym] = $val;
                $row['row_total']   += $val;
                $typeTotals[$ym]    += $val;
                $totalsPerMonth[$ym]+= $val;
            }
            $typeGrand   += $row['row_total'];
            $grandTotal  += $row['row_total'];
            $typeRows[]   = $row;
        }

        $byType[$typeName] = [
            'rows'       => $typeRows,
            'totals'     => $typeTotals,
            'type_total' => $typeGrand,
        ];
    }

    // ---------- Filters data ----------
    $projects     = Project::orderBy('name')->get(['id','name']);
    $accountCodes = AccountCode::orderBy('code')->get(['id','code','name']);

    return view('reports.notes', [
        'groups'         => $byType,
        'monthKeys'      => $months,
        'monthLabels'    => $monthLabels,
        'totalsPerMonth' => $totalsPerMonth,
        'grandTotal'     => $grandTotal,
        'projects'       => $projects,
        'accountCodes'   => $accountCodes,
        'filters'        => [
            'project_id'      => $projectId,
            'account_code_id' => $accountCodeId,
            'from'            => $from?->format('Y-m-d'),
            'to'              => $to?->format('Y-m-d'),
        ],
    ]);
}


   public function pnl(Request $request)
{
    $projectId = $request->integer('project_id'); // optional
    $from      = $request->date('from');          // optional override
    $to        = $request->date('to');            // optional override

    // Pull projects for the filter UI
    $projects = Project::orderBy('name')->get(['id','name','start_date','expected_end_date']);

    // ----- Determine month window -----
    // If explicit from/to provided, honor them; otherwise derive from project span or data
    if ($from && $to) {
        $start = (clone $from)->startOfMonth();
        $end   = (clone $to)->startOfMonth();
    } else {
        if ($projectId) {
            $project = $projects->firstWhere('id', $projectId);
            $start = optional($project?->start_date)->copy()?->startOfMonth();
            $end   = now()->startOfMonth();

            // If still missing, derive from earliest tx (income/expense) for the project
            if (!$start) {
                $firstExp = \App\Models\Expense::where('project_id', $projectId)->min('expense_date');
                $firstInc = \App\Models\Income ::where('project_id', $projectId)->min('income_date');
                $firstAny = collect([$firstExp, $firstInc])->filter()->min();
                $start    = $firstAny ? \Carbon\Carbon::parse($firstAny)->startOfMonth() : now()->startOfMonth();
            }

            // Clamp to expected end if earlier than current
            $expectedEnd = optional($project?->expected_end_date)->copy()?->startOfMonth();
            if ($expectedEnd && $expectedEnd->lt($end)) $end = $expectedEnd;
        } else {
            $firstExp = \App\Models\Expense::min('expense_date');
            $firstInc = \App\Models\Income ::min('income_date');
            $firstAny = collect([$firstExp, $firstInc])->filter()->min();
            $start    = $firstAny ? \Carbon\Carbon::parse($firstAny)->startOfMonth() : now()->startOfMonth();
            $end      = now()->startOfMonth();
        }
    }
    if ($start->gt($end)) $start = $end->copy();

    // ----- Build month keys/labels -----
    $months = collect(\Carbon\CarbonPeriod::create($start, '1 month', $end))
        ->map(fn(\Carbon\Carbon $c) => $c->format('Y-m-01'));
    $monthLabels = $months->mapWithKeys(fn($ym) => [$ym => \Carbon\Carbon::parse($ym)->format('M y')]);

    // ----- Pull INCOME totals by month+code -----
    $incomeRows = \DB::table('incomes')
        ->leftJoin('account_codes','account_codes.id','=','incomes.account_code_id')
        ->when($projectId, fn($q) => $q->where('incomes.project_id', $projectId))
        ->whereDate('incomes.income_date', '>=', $start->toDateString())
        ->whereDate('incomes.income_date', '<=', $end->copy()->endOfMonth()->toDateString())
        ->selectRaw("DATE_FORMAT(incomes.income_date, '%Y-%m-01') as ym,
                     TRIM(COALESCE(account_codes.code,'')) as ac_code,
                     SUM(incomes.amount) as total")
        ->groupBy('ym','ac_code')
        ->orderBy('ym')->orderBy('ac_code')
        ->get();

    // ----- Pull EXPENSE totals by month+code -----
    $expenseRows = \DB::table('expenses')
        ->leftJoin('account_codes','account_codes.id','=','expenses.account_code_id')
        ->when($projectId, fn($q) => $q->where('expenses.project_id', $projectId))
        ->whereDate('expenses.expense_date', '>=', $start->toDateString())
        ->whereDate('expenses.expense_date', '<=', $end->copy()->endOfMonth()->toDateString())
        ->selectRaw("DATE_FORMAT(expenses.expense_date, '%Y-%m-01') as ym,
                     TRIM(COALESCE(account_codes.code,'')) as ac_code,
                     SUM(expenses.amount) as total")
        ->groupBy('ym','ac_code')
        ->orderBy('ym')->orderBy('ac_code')
        ->get();

    // Index for quick lookup
    $incByMonthCode = [];
    foreach ($incomeRows as $r) $incByMonthCode[$r->ym][$r->ac_code] = (float) $r->total;

    $expByMonthCode = [];
    foreach ($expenseRows as $r) $expByMonthCode[$r->ym][$r->ac_code] = (float) $r->total;

    // ----- Mapping buckets (adjust codes anytime) -----
    // Income buckets come from the INCOMES table now
    $mapIncome = [
        'Revenue'      => ['AC-40001'], // your primary revenue code(s)
        'Other income' => ['AC-40002'], // optional: interest, discounts, etc.
    ];

    // Expense buckets (examples from your earlier map)
    $mapExpense = [
        'Direct operations'            => ['AC-50001','AC-50002','AC-50003','AC-50004','AC-50005','AC-50006'],
        'General & administrative'     => [
            'AC-60001','AC-60002','AC-60003','AC-60005','AC-60006','AC-60007','AC-60008','AC-60009','AC-60010',
            'AC-60011','AC-60012','AC-60013','AC-60014','AC-60017','AC-60020','AC-60021','AC-60023','AC-60024',
            'AC-60028','AC-60029','AC-60101','AC-60102'
        ],
        'Staff/labour costs'           => ['AC-60015','AC-60016'],
        'Management fees'              => [],
        'Interest expense'             => [],               // or keep AC-60020 in G&A above
        'Depreciation & amortization'  => ['AC-60201','AC-60202'],
    ];

    // Helpers to sum buckets by month
    $sumInc = function (string $ym, array $codes) use ($incByMonthCode): float {
        $s = 0.0; foreach ($codes as $c) $s += $incByMonthCode[$ym][$c] ?? 0.0; return $s;
    };
    $sumExp = function (string $ym, array $codes) use ($expByMonthCode): float {
        $s = 0.0; foreach ($codes as $c) $s += $expByMonthCode[$ym][$c] ?? 0.0; return $s;
    };

    // ----- Build table rows -----
    $table = [
        'Revenue'                        => [],
        'Other income'                   => [],
        'Net income'                     => [],
        '—divider—'                      => [],
        'Direct operations'              => [],
        'General & administrative'       => [],
        'Staff/labour costs'             => [],
        'Management fees'                => [],
        'Interest expense'               => [],
        'Depreciation & amortization'    => [],
        'Total overhead'                 => [],
        'Net profit/(loss)'              => [],
    ];
    $rowTotals = array_fill_keys(array_keys($table), 0.0);

    foreach ($months as $ym) {
        // Income side
        $revenue     = $sumInc($ym, $mapIncome['Revenue']);
        $otherInc    = $sumInc($ym, $mapIncome['Other income']);
        $netIncome   = $revenue + $otherInc;

        // Expense side buckets
        $directOps   = $sumExp($ym, $mapExpense['Direct operations']);
        $ga          = $sumExp($ym, $mapExpense['General & administrative']);
        $staff       = $sumExp($ym, $mapExpense['Staff/labour costs']);
        $mgmt        = $sumExp($ym, $mapExpense['Management fees']);
        $interest    = $sumExp($ym, $mapExpense['Interest expense']);
        $depr        = $sumExp($ym, $mapExpense['Depreciation & amortization']);

        $totalOver   = $directOps + $ga + $staff + $mgmt + $interest + $depr;
        $netProfit   = $netIncome - $totalOver; // profit = income - expense

        // Fill rows
        $table['Revenue'][$ym]                      = $revenue;
        $table['Other income'][$ym]                 = $otherInc;
        $table['Net income'][$ym]                   = $netIncome;
        $table['—divider—'][$ym]                    = null;
        $table['Direct operations'][$ym]            = $directOps;
        $table['General & administrative'][$ym]     = $ga;
        $table['Staff/labour costs'][$ym]           = $staff;
        $table['Management fees'][$ym]              = $mgmt;
        $table['Interest expense'][$ym]             = $interest;
        $table['Depreciation & amortization'][$ym]  = $depr;
        $table['Total overhead'][$ym]               = $totalOver;
        $table['Net profit/(loss)'][$ym]            = $netProfit;

        // Totals
        foreach ($table as $rowName => $vals) {
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
        'from'        => $from?->format('Y-m-d'),
        'to'          => $to?->format('Y-m-d'),
    ]);
}
}
