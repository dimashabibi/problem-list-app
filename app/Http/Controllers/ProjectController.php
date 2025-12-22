<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return view('projects.index');
    }

    public function list(Request $request)
    {
        return response()->json(Project::orderBy('id_project', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        $project = Project::create($validated);
        return response()->json(['success' => true, 'data' => $project]);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.project_name' => 'required|string|max:20',
            'items.*.description' => 'nullable|string',
        ]);
        $created = [];
        foreach ($validated['items'] as $item) {
            $created[] = Project::create($item);
        }
        return response()->json(['success' => true, 'data' => $created]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        $project = Project::findOrFail($id);
        $project->update($validated);
        return response()->json(['success' => true, 'data' => $project]);
    }

    public function destroy(int $id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return response()->json(['success' => true]);
    }
}
