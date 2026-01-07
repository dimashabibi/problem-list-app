<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        return view('admin.item.index');
    }

    public function table()
    {
        return view('admin.item.table');
    }

    public function list(Request $request)
    {
        return response()->json(Item::orderBy('id_item', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:50',
        ]);

        $item = Item::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:50'
        ]);
        $created = [];
        foreach ($validated['items'] as $item) {
            $created[] = Item::create($item);
        }
        return response()->json(['success' => true, 'data' => $created]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $item = Item::findOrFail($id);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(int $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:items,id_item',
        ]);

        Item::whereIn('id_item', $validated['ids'])->delete();

        return response()->json(['success' => true]);
    }
}
