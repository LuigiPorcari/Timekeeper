<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showTimekeeperRegistrationForm()
    {
        return view('auth.register-timekeeper');
    }
    public function registerTimekeeper(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'date_of_birth' => $request->date_of_birth,
            'residence' => $request->residence,
            'domicile' => $request->domicile,
            'transfer' => $request->transfer,
            'auto' => $request->auto,
            'password' => Hash::make($request->password),
            'is_timekeeper' => true,
        ]);
        return redirect()->route('login');
    }
    public function showAdminRegistrationForm()
    {
        return view('auth.register-admin');
    }
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'date_of_birth' => $request->date_of_birth,
            'residence' => $request->residence,
            'domicile' => $request->domicile,
            'transfer' => $request->transfer,
            'auto' => $request->auto,
            'password' => Hash::make($request->password),
            'is_admin' => true,
        ]);
        return redirect()->route('login');
    }
    public function showSecretariatRegistrationForm()
    {
        return view('auth.register-secretariat');
    }
    public function registerSecretariat(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'date_of_birth' => $request->date_of_birth,
            'residence' => $request->residence,
            'domicile' => $request->domicile,
            'transfer' => $request->transfer,
            'auto' => $request->auto,
            'password' => Hash::make($request->password),
            'is_secretariat' => true,
        ]);
        return redirect()->route('login');
    }
}
