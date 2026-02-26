<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    // list users (admin and user)
    public function index()
    {
        $users = User::whereIn('role', ['admin','user'])->orderBy('created_at', 'desc')->paginate(20);
        return view('superadmin.users.index', compact('users'));
    }

    // show create form
    public function create()
    {
        return view('superadmin.users.create');
    }

    // store new admin/user
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('superadmin.users.index')->with('success', 'User created successfully.');
    }

    // delete user
    public function destroy(User $user)
    {
        if ($user->isAdmin() || $user->role === 'user') {
            $user->delete();
            return back()->with('success', 'User deleted.');
        }

        return back()->with('error', 'Cannot delete this user.');
    }
}
