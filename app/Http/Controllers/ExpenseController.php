<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use App\Models\AccountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{


    // Filterable list by account code and date range
    public function index(Request $request)
    {
        $accountCodeId = $request->integer('account_code_id');
        $projectId     = $request->integer('project_id');
        $from          = $request->date('from');
        $to            = $request->date('to');

        $query = Expense::with(['project', 'accountCode', 'user'])
            ->latest('expense_date');

        if ($accountCodeId) $query->where('account_code_id', $accountCodeId);
        if ($projectId)     $query->where('project_id', $projectId);
        if ($from)          $query->whereDate('expense_date', '>=', $from);
        if ($to)            $query->whereDate('expense_date', '<=', $to);

        // clone query for total before pagination
        $totalExpense = (clone $query)->sum('amount');

        $expenses     = $query->paginate(20)->withQueryString();
        $accountCodes = AccountCode::where('account_code_type_id', '!=', '12')
            ->orderBy('code')
            ->get();
        $projects = Project::orderBy('name')->get(['id','name']);  

        return view('expenses.index', compact('expenses', 'accountCodes', 'projects', 'totalExpense'));
    }

    // Add expense under a project
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'account_code_id' => 'required|exists:account_codes,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['project_id'] = $project->id;
        $validated['user_id'] = Auth::id();

        Expense::create($validated);
        return back()->with('success', 'Expense added.');
    }

    public function update(Request $request, Expense $expense)
    {
        // Optional: only creator/admin can edit
        // $this->authorize('update', $expense);

        $data = $request->validate([
            'account_code_id' => 'required|exists:account_codes,id',
            'expense_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:255',
        ]);

        $expense->update($data);

        return back()->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {   
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}
