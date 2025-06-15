<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $this->validateLogin($request);
        if (Auth::attempt($this->credentials($request), $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->is_timekeeper) {
                return redirect()->route('timekeeper.dashboard');
            }
        }
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }
    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }
    public function destroy(Request $request)
    {
        $user = $request->user();

        Auth::logout();
        $user->availabilities()->detach();
        $user->races()->detach();
        $user->records()->delete();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Account eliminato con successo.');
    }
}
