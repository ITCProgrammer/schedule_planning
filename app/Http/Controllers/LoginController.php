<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Session::has('user')) {
            return redirect()->route('poList');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $user = DB::connection('sqlsrv')->table('user')
            ->where('username', $credentials['username'])
            ->where('rec_status', 1)
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Session::put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'dept' => $user->dept,
                'role' => $user->role
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau password salah'
        ], 401);
    }

    public function createAccount(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|min:6',
            'name'     => 'required|string|max:100',
            'dept'     => 'required|string|max:100',
            'role'     => 'required|string|max:50',
        ]);
    
        $hashedPassword = Hash::make($request->password);
    
        $userData = [
            'username' => $request->username,
            'password' => $hashedPassword,
            'name'     => $request->name,
            'dept'     => $request->dept,
            'role'     => $request->role,
        ];
    
        return response()->json([
            'success' => true,
            'message' => 'User created (simulasi)',
            'data'    => $userData,
        ]);
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
