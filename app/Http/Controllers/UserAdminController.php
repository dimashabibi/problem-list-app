<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    public function index()
    {
        return view('admin.user.index');
    }

    public function table()
    {
        return view('admin.user.table');
    }

    public function list(Request $request)
    {
        return response()->json(
            User::orderBy('id_user', 'desc')
                ->selectRaw('id_user as id, username, fullname as name, email, status')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'status' => 'required|in:admin,user',
        ]);
        $user = User::create([
            'username' => $validated['username'],
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);
        return response()->json(['success' => true, 'data' => [
            'id' => $user->id_user,
            'username' => $user->username,
            'name' => $user->fullname,
            'email' => $user->email,
            'status' => $user->status,
        ]]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id_user,
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id_user,
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:admin,user',
        ]);
        $data = [
            'username' => $validated['username'],
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ];
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }
        $user->update($data);
        return response()->json(['success' => true, 'data' => [
            'id' => $user->id_user,
            'username' => $user->username,
            'name' => $user->fullname,
            'email' => $user->email,
            'status' => $user->status,
        ]]);
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['success' => true]);
    }
}
