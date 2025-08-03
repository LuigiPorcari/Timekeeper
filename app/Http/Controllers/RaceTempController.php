<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceTemp;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use App\Mail\RaceAcceptedMail;
use App\Mail\RaceRejectedMail;
use Illuminate\Support\Facades\Mail;

class RaceTempController extends Controller
{
    public function createRaceTempShow()
    {
        return view('guest.createRaceTemp');
    }

    public function storeRaceTemp(Request $request)
    {
        // Validazione inclusiva del campo name
        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required|string|max:255',
            'date_of_race' => 'required|date',
            'place' => 'required|string|max:255',
            'specialization_of_race' => 'nullable|array',
            'specialization_of_race.*' => 'string',
        ]);

        // Creazione della nuova gara
        $raceTemp = new RaceTemp();
        $raceTemp->email = $request->input('email');
        $raceTemp->name = $request->input('name');
        $raceTemp->date_of_race = $request->input('date_of_race');
        $raceTemp->place = $request->input('place');
        $raceTemp->specialization_of_race = $request->input('specialization_of_race', []); // salva come array
        $raceTemp->save();

        // Redirect con messaggio di successo
        return redirect()->route('homepage')->with('success', 'Gara temporanea creata con successo!');
    }
    public function accept(RaceTemp $race)
    {
        // Crea la nuova gara definitiva
        Race::create([
            'name' => $race->name,
            'date_of_race' => $race->date_of_race,
            'place' => $race->place,
            'specialization_of_race' => $race->specialization_of_race,
        ]);

        // Invia la mail di notifica all'indirizzo salvato
        $brevo = new BrevoMailer();
        $brevo->sendEmail(
            $race->email,
            'Gara accettata',
            'emails.race.accepted',
            ['raceName' => $race->name]
        );

        // Elimina la gara temporanea
        $race->delete();

        // Redirect con conferma
        return redirect()->back()->with('success', 'Gara accettata e notificata con successo.');
    }
    public function reject(RaceTemp $race)
    {
        // Invia la mail di rifiuto
        $brevo = new BrevoMailer();
        $brevo->sendEmail(
            $race->email,
            'Gara rifiutata',
            'emails.race.rejected',
            ['raceName' => $race->name]
        );

        // Elimina la gara temporanea
        $race->delete();

        return redirect()->back()->with('success', 'Gara rifiutata e notificata.');
    }
}
