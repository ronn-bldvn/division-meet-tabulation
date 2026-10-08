<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('sport')->orderBy('role')->orderBy('name')->get();
        $sports = Sport::orderBy('name')->get();
        return view('admin.users.index', compact('users', 'sports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,facilitator',
            'sport_id' => 'nullable|exists:sports,id|required_if:role,facilitator',
        ]);

        if ($data['role'] === 'super_admin') {
            $data['sport_id'] = null;
        }

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return back()->with('status', 'Account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:super_admin,facilitator',
            'sport_id' => 'nullable|exists:sports,id|required_if:role,facilitator',
        ]);

        if ($data['role'] === 'super_admin') {
            $data['sport_id'] = null;
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('status', 'Account updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('status', 'Account removed.');
    }
}
