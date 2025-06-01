<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function homepage()
    {
        // if (Auth::check()) {
        //     // Se l'utente è autenticato, reindirizza ad una pagina specifica (es. dashboard)
        //     if (Auth::user()->is_admin) {
        //         return redirect()->route('admin.dashboard');
        //     }
        //     if (Auth::user()->is_trainer) {
        //         return redirect()->route('trainer.dashboard');
        //     }
        //     if (Auth::user()->is_corsista) {
        //         return redirect()->route('student.dashboard');
        //     }
        // }

        // Mostra la home per gli utenti non autenticati
        return view('welcome');
    }
}
