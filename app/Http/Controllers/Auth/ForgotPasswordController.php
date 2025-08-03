<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.passwords.email-custom'); // la tua vista personalizzata
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = app('auth.password.broker')->createToken($user);
            $link = url(route('password.reset', ['token' => $token, 'email' => $user->email]));

            $mailer = new BrevoMailer();
            $mailer->sendEmail(
                $user->email,
                'Reimposta la tua password',
                'emails.password.reset',
                ['nome' => $user->name, 'url' => $link]
            );

            return back()->with('status', 'Email inviata!');
        }

        return back()->withErrors(['email' => 'Email non trovata.']);
    }
}
