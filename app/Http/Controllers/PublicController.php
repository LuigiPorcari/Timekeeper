<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    public function homepage()
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Se l'utente è autenticato, reindirizza ad una pagina specifica (es. dashboard)
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->is_timekeeper) {
                return redirect()->route('timekeeper.dashboard');
            }
        }
        // Mostra la home per gli utenti non autenticati
        return view('welcome');
    }
}
