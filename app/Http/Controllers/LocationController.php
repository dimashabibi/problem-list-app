<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        return view('admin.location.index');
    }

    public function table()
    {
        return view('admin.location.table');
    }

    public function list(Request $request)
    {
        return response()->json(Location::orderBy('id_location', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);
        $loc = Location::create($validated);
        return response()->json(['success' => true, 'data' => $loc]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);
        $loc = Location::findOrFail($id);
        $loc->update($validated);
        return response()->json(['success' => true, 'data' => $loc]);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.location_name' => 'required|string|max:50',
            'items.*.description' => 'nullable|string',
        ]);
        $created = [];
        foreach ($validated['items'] as $item) {
            $created[] = Location::create($item);
        }
        return response()->json(['success' => true, 'data' => $created]);
    }

    public function destroy(int $id)
    {
        $loc = Location::findOrFail($id);
        $loc->delete();
        return response()->json(['success' => true]);
    }
}
