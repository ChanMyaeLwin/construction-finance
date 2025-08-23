<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Worker::orderBy('is_active','desc')->orderBy('name')->paginate(20);
        return view('workers.index', compact('workers'));
    }

    public function create()
    {
        return view('workers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'role'       => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active'  => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool)($data['is_active'] ?? true);
        Worker::create($data);
        return redirect()->route('workers.index')->with('success', 'Worker created.');
    }

    public function edit(Worker $worker)
    {
        return view('workers.edit', compact('worker'));
    }

    public function update(Request $request, Worker $worker)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'role'       => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active'  => 'nullable|boolean',
        ]);
        $worker->update($data);
        return redirect()->route('workers.index')->with('success', 'Worker updated.');
    }

    public function destroy(Worker $worker)
    {
        $worker->delete();
        return redirect()->route('workers.index')->with('success', 'Worker deleted.');
    }
}
