<?php

namespace App\Http\Controllers;

use App\Models\Kanban;
use App\Models\Project;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('project_name')->get();
        return view('admin.kanban.index', compact('projects'));
    }

    public function table()
    {
        return view('admin.kanban.table');
    }

    public function list(Request $request)
    {
        $query = Kanban::with('project')->orderBy('id_kanban', 'desc');
        if ($request->has('project_id') && $request->integer('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id_project',
            'kanban_name' => 'required|string|max:20',
            'item' => 'nullable|integer',
        ]);
        $kanban = Kanban::create($validated);
        return response()->json(['success' => true, 'data' => $kanban]);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.project_id' => 'required|integer|exists:projects,id_project',
            'items.*.kanban_name' => 'required|string|max:20',
        ]);
        $created = [];
        foreach ($validated['items'] as $item) {
            $created[] = Kanban::create([
                'project_id' => $item['project_id'],
                'kanban_name' => $item['kanban_name'],
            ]);
        }
        return response()->json(['success' => true, 'data' => $created]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id_project',
            'kanban_name' => 'required|string|max:20',
            'item' => 'nullable|integer',
        ]);
        $kanban = Kanban::findOrFail($id);
        $kanban->update($validated);
        return response()->json(['success' => true, 'data' => $kanban]);
    }

    public function destroy(int $id)
    {
        $kanban = Kanban::findOrFail($id);
        $kanban->delete();
        return response()->json(['success' => true]);
    }
}
