<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function addUser(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,formateur,student',
            'status' => 'string|in:active,inactive,banned',
            'classroom_id' => 'integer|nullable',
        ]);

        $user = User::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'status' => $request->status ?? 'active',
            'points' => 0,
            'classroom_id' => $request->classroom_id,
        ]);

        return response()->json([
            'message' => 'user is created',
            'user' => $user
            ],201);
    }

    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }




    public function banUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'user not found'
                ], 404);
        }

        $user->status = 'banned';
        $user->save();

        return response()->json([
            'message' => 'user is banned'
            ]);
    }
}
