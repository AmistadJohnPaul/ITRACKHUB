<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw("FIELD(role, 'admin', 'staff', 'customer')")->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,staff',
        ]);

        User::create($data); // password is hashed automatically by the User model

        return back()->with('success', 'Account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:admin,staff,customer',
            'password' => 'nullable|string|min:6',
        ]);

        // An admin cannot demote themselves (would lock everyone out)
        if ($user->id === $request->user()->id) {
            $data['role'] = $user->role;
        }

        $user->name = $data['name'];
        $user->role = $data['role'];
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', 'Account updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', "You can't delete your own account.");
        }

        $user->delete();

        return back()->with('success', 'Account removed.');
    }
}