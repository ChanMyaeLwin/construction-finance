<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Worker;
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
        $q             = trim((string) $request->input('q', ''));   // NEW: description contains

        $query = Expense::with(['project', 'accountCode', 'user'])
            ->latest('expense_date')
            ->when($accountCodeId, fn($q2) => $q2->where('account_code_id', $accountCodeId))
            ->when($projectId,     fn($q2) => $q2->where('project_id', $projectId))
            ->when($from,          fn($q2) => $q2->whereDate('expense_date', '>=', $from))
            ->when($to,            fn($q2) => $q2->whereDate('expense_date', '<=', $to))
            ->when($q !== '',      fn($q2) => $q2->where('description', 'like', "%{$q}%")); // NEW

        $totalExpense = (clone $query)->sum('amount');
        $expenses     = $query->paginate(20)->withQueryString();
        $accountCodes = AccountCode::orderBy('code')->get();
        $projects     = Project::orderBy('name')->get(['id','name']);
        $workers      = Worker::where('is_active', true)->orderBy('name')->get(['id','name']);

        return view('expenses.index', compact('expenses', 'accountCodes', 'projects', 'totalExpense', 'workers'))
            ->with('filters', [
                'account_code_id' => $accountCodeId,
                'project_id'      => $projectId,
                'from'            => $from?->format('Y-m-d'),
                'to'              => $to?->format('Y-m-d'),
                'q'               => $q, // NEW
            ]);
    }

    // Add expense under a project
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'account_code_id' => 'required|exists:account_codes,id',
            'expense_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:255',
            'worker_id'       => 'nullable|exists:workers,id',
        ]);

        $ac = AccountCode::findOrFail($data['account_code_id']);
        $data['user_id']    = \Illuminate\Support\Facades\Auth::id();
        $data['project_id'] = $project->id;
        // Block Revenue (12) in expenses, if you want to keep this rule:
        if ((int)$ac->account_code_type_id === 12) {
            $data['income_date'] = $data['expense_date'];
            Income::create($data);
            return back()->with('success', 'Income added.');
        }

        // If worker type (15) => require worker_id and copy name to description if blank
        if ((int)$ac->account_code_type_id === 15) {
            $request->validate(['worker_id' => 'required|exists:workers,id']);
            $worker = Worker::findOrFail($request->integer('worker_id'));
            if (empty($data['description'])) $data['description'] = $worker->name;
            $data['worker_id'] = $worker->id;
        } else {
            $data['worker_id'] = null;
        }

        
       

        Expense::create($data);
        return back()->with('success', 'Expense added.');
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'account_code_id' => 'required|exists:account_codes,id',
            'expense_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:255',
            'worker_id'       => 'nullable|exists:workers,id',
        ]);
   
        $ac = AccountCode::findOrFail($data['account_code_id']);

        if ((int)$ac->account_code_type_id === 12) {
            $data['income_date'] = $data['expense_date'];
            Income::create($data);
            return back()->with('success', 'Income added.');
        }

        if ((int)$ac->account_code_type_id === 15) {
            $request->validate(['worker_id' => 'required|exists:workers,id']);
            $worker = Worker::findOrFail($request->integer('worker_id'));
            if (empty($data['description'])) $data['description'] = $worker->name;
            $data['worker_id'] = $worker->id;
        } else {
            $data['worker_id'] = null;
        }

        $expense->update($data);
        return back()->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {   
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}
