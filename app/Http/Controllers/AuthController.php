<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register (Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($validated);

        Auth::login($user);

        return response()->json(['success' => true], 200);
    }

    public function login (Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);


        if (Auth::attempt($validated)) {
            $request->session()->regenerate();
        }

        return response()->json(['success' => true], 200);
    }

    public function logout(Request $request)
{
        Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        return response()->json(['success' => true], 200);
    
}



}
