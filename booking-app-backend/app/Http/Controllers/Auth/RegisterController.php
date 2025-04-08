<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Walidacja, aby sprawdzić, czy pole 'phone_number' istnieje
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'second_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:user,owner,admin',
            'city' => 'required|string',
            'phone_number' => 'required|string',
        ]);
    
        $user = new User([
            'first_name' => $validatedData['first_name'],
            'second_name' => $validatedData['second_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => $validatedData['role'],
            'city' => $validatedData['city'],
            'phone_number' => $validatedData['phone_number'],
        ]);
        
        $user->save();
    
        return response()->json(['message' => 'User registered successfully']);
    }    
}
