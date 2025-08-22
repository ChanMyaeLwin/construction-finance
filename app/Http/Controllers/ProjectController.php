<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectType;
use App\Models\AccountCode;
use App\Models\ExpenseCode;
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
            'fixed_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'progress_percent' => 'nullable|integer|min:0|max:100',
        ]);

        Project::create($validated);
        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load([
            'projectType',
            'steps',
            'expenses.accountCode','expenses.user',
            'incomes.accountCode','incomes.user',
        ])->loadSum('expenses', 'amount')
        ->loadSum('incomes', 'amount');

        // For selects in the Add form:
        $accountCodes = AccountCode::orderBy('code')->get(['id','code','name','account_code_type_id']);

        return view('projects.show', compact('project','accountCodes'));
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
            'fixed_amount' => 'required|numeric|min:0',
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
