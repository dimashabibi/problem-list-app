<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        return view('admin.machine.index');
    }

    public function list(Request $request)
    {
        return response()->json(Machine::orderBy('id_machine', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_machine' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $machine = Machine::create($validated);
        return response()->json(['success' => true, 'data' => $machine]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name_machine' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $machine = Machine::findOrFail($id);
        $machine->update($validated);
        return response()->json(['success' => true, 'data' => $machine]);
    }

    public function destroy(int $id)
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();
        return response()->json(['success' => true]);
    }
}
