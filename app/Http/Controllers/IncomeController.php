<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Project;
use App\Models\AccountCode;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $projectId     = $request->integer('project_id');
        $accountCodeId = $request->integer('account_code_id');
        $q             = trim((string) $request->input('q', ''));
        $from          = $request->date('from');
        $to            = $request->date('to');

        $base = Income::with(['project','accountCode','user'])
            ->latest('income_date')
            ->when($projectId,     fn($q2) => $q2->where('project_id', $projectId))
            ->when($accountCodeId, fn($q2) => $q2->where('account_code_id', $accountCodeId))
            ->when($q !== '',      fn($q2) => $q2->where('description', 'like', "%{$q}%"))
            ->when($from,          fn($q2) => $q2->whereDate('income_date', '>=', $from))
            ->when($to,            fn($q2) => $q2->whereDate('income_date', '<=', $to));

        $incomes     = (clone $base)->paginate(20)->withQueryString();
        $totalIncome = (clone $base)->sum('amount');

        $projects     = Project::orderBy('name')->get(['id','name']);
        $accountCodes = AccountCode::where('account_code_type_id', 12)
                        ->orderBy('code')->get(['id','code','name','account_code_type_id']);

        return view('incomes.index', [
            'incomes'      => $incomes,
            'projects'     => $projects,
            'accountCodes' => $accountCodes,   // ← make sure this is passed
            'totalIncome'  => $totalIncome,
            'projectId'    => $projectId,      // optional, blade can use request('project_id') instead
        ]);
    }

    public function update(Request $request, Income $income)
    {
        $data = $request->validate([
            'account_code_id' => 'required|exists:account_codes,id',
            'income_date'     => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:255',
        ]);

        $income->update($data);

        return back()->with('success', 'Received updated.');
    }

    public function destroy(Income $income)
    {
        $income->delete();

        return back()->with('success', 'Received deleted.');
    }
}