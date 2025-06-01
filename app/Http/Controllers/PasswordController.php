<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function showChangePasswordForm()
    {
        return view('auth.passwords.change');
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        // Verifica che la vecchia password sia corretta
        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return back()->withErrors(['old_password' => 'La vecchia password non è corretta']);
        }
        // Aggiorna la password
        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);
        // if (Auth::user()->is_corsista) {
        //     return redirect()->route('student.dashboard')->with('success', 'Password cambiata con successo!');
        // }
        // if (Auth::user()->is_admin) {
        //     return redirect()->route('admin.dashboard')->with('success', 'Password cambiata con successo!');
        // }
        return view('welcome');
    }
}
