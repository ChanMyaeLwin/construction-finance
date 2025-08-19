<?php
// app/Http/Controllers/ProjectStepController.php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectStep;
use Illuminate\Http\Request;

class ProjectStepController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
           'step_no' => [
                'required',
                'integer',
                'min:1',
                // enforce uniqueness at app-level
                function ($attribute, $value, $fail) use ($project) {
                    if ($project->steps()->where('step_no', $value)->exists()) {
                        $fail("Step no $value already exists for this project.");
                    }
                }
            ],
            'name'    => 'required|string|max:255',
        ]);
        $project->steps()->create($data + ['is_done' => false]);
        return back()->with('success', 'Step added.');
    }

    public function update(Request $request, ProjectStep $step)
    {
        $data = $request->validate([
            'step_no' => 'sometimes|integer|min:1',
            'name'    => 'sometimes|string|max:255',
            'is_done' => 'sometimes|boolean',
        ]);
        $step->update($data);
        return back()->with('success', 'Step updated.');
    }

    public function destroy(ProjectStep $step)
    {
        $step->delete();
        return back()->with('success', 'Step deleted.');
    }
}