<?php

namespace App\Http\Controllers;

use App\Models\ProjectType;
use Illuminate\Http\Request;

class ProjectTypeController extends Controller
{
    // If using the admin-only helper trait:
    // use \App\Http\Controllers\Concerns\UsesAdminAuth;

    public function index()
    {
        $types = ProjectType::orderBy('name')->paginate(20);
        return view('project-types.index', compact('types'));
    }

    public function create()
    {
        return view('project-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:project_types,name',
            'description' => 'nullable|string|max:500',
        ]);

        ProjectType::create($data);
        return redirect()->route('project-types.index')->with('success', 'Project Type created.');
    }

    public function edit(ProjectType $project_type)
    {
        return view('project-types.edit', ['type' => $project_type]);
    }

    public function update(Request $request, ProjectType $project_type)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:project_types,name,' . $project_type->id,
            'description' => 'nullable|string|max:500',
        ]);

        $project_type->update($data);
        return redirect()->route('project-types.index')->with('success', 'Project Type updated.');
    }

    public function destroy(ProjectType $project_type)
    {
        // Prevent deleting if in use
        if ($project_type->projects()->exists()) {
            return back()->with('error', 'Cannot delete: this type is used by one or more projects.');
        }
        $project_type->delete();
        return redirect()->route('project-types.index')->with('success', 'Project Type deleted.');
    }
}