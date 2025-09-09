<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectType;
use App\Models\AccountCode;
use App\Models\ExpenseCode;
use App\Models\Worker;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = \App\Models\Project::query()
            ->with('projectType')
            ->withSum('incomes', 'amount')   // -> incomes_sum_amount
            ->withSum('expenses', 'amount')  // -> expenses_sum_amount
            ->orderBy('name')
            ->paginate(20);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $projectTypes = ProjectType::orderBy('name')->get();
        return view('projects.create', compact('projectTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_type_id' => 'nullable|exists:project_types,id',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'accounts_receivable' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'progress_percent' => 'nullable|integer|min:0|max:100',
        ]);

        Project::create($validated);
        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

   public function show(Project $project)
    {
        // Eager-load light relations for the header and steps
        $project->load(['projectType', 'steps']);

        // Latest 5 (by created_at desc)
        $recentExpenses = $project->expenses()
            ->with(['accountCode'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $recentIncomes = $project->incomes()
            ->with(['accountCode'])
            ->latest('created_at')
            ->take(5)
            ->get();

        // For “Add Daily Expense / Income” form
        $accountCodes = AccountCode::orderBy('code')->get(['id','code','name','account_code_type_id']);
        $workers      = Worker::where('is_active', true)->orderBy('name')->get(['id','name']);

        // For header totals
        $project->loadSum('expenses', 'amount');
        $project->loadSum('incomes', 'amount');

        return view('projects.show', compact(
            'project', 'accountCodes', 'workers', 'recentExpenses', 'recentIncomes'
        ));
    }

    public function edit(Project $project)
    {
        $projectTypes = ProjectType::orderBy('name')->get();
        return view('projects.edit', compact('project', 'projectTypes'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_type_id' => 'nullable|exists:project_types,id',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'accounts_receivable' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'progress_percent' => 'nullable|integer|min:0|max:100',
        ]);
        $project->update($validated);
        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
